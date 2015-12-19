<?php
/**
 * Class for handling AJAX calls invoked from ajax.php. You could integrate the
 * AJAX handling calls into the normal index.php RESTful route, but sometimes
 * keeping them separate is a good thing to do.
 *
 * It assumes that ajax calls are made to {{base}}/ajax.php via a POST and that
 * they have at least a parameter called 'op' that defines what is to be done.
 *
 * Of course, this is entirely arbitrary and you can do whatever you want!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2014-2015 Newcastle University
 */
/**
 * Handle Ajax operations in this class
 */
    class Ajax
    {
        use Singleton;
/**
 * @var array Allowed operation codes. Values indicate : [needs login, needs admin privileges, needs developer privileges]
 */
        private static $ops = array(
            'addcontext'    => array(TRUE, TRUE, FALSE),
            'addpage'       => array(TRUE, TRUE, FALSE),
            'addbookmark'   => array(TRUE, FALSE, FALSE),
            'delbookmark'   => array(TRUE, FALSE, FALSE),
            'delpub'        => array(TRUE, FALSE, FALSE),
            'delpost'       => array(TRUE, FALSE, FALSE),
            'addrole'       => array(TRUE, TRUE, FALSE),
            'adddepartment' => array(TRUE, TRUE, FALSE),
            'addtype'       => array(TRUE, TRUE, FALSE),
            'adduser'       => array(TRUE, TRUE, FALSE),
            'delbean'       => array(TRUE, TRUE, FALSE),
            'deluser'       => array(TRUE, TRUE, FALSE),
            'toggle'        => array(TRUE, TRUE, FALSE),
        );

/**
 * Add a Bookmarked publication for a user.
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function addbookmark($context)
        {
            # Get the user bean, i.e. many users can have many bookmarked publications.
            $user = $context->user();
            # Get the publication bean, from the postpar
            $pub = R::load('publication', $context->mustpostpar('id'));

            # Set up the association for a bookmark, i.e. it is a many to many between 
            # users and publications.
            $user->sharedPublications[] = $pub;
            $pub->sharedUsers[] = $user;

            # Store the user and the publication.
            R::store($user);
            R::store($pub);

            # Return the user id.
            echo $user->getID();
        }

/**
 * Delete a Bookmarked publication for a user. This is called by
 * any logged in user, hence why delbean will not be used as that could
 * lead to penetration vectors of attack.
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function delbookmark($context)
        {
            $pub = R::load('publication', $context->mustpostpar('id'));

            foreach ($pub->sharedUser as $index => $user)
            {
                # If the users match
                if ($context->user()->id == $user->id)
                {
                    unset($pub->sharedUser[$user->id]);
                }
                
            }   

            R::store($pub);
        }

/**
 * Delete a post on a publication, only available to the staff user.
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function delpost($context)
        {
            # Automtically returns 403 if the user is not staff.
            $context->mustbestaff();

            # Get the publication and post
            $pub = R::load('publication', $context->mustpostpar('id'));
            $post = R::load('post', $context->mustpostpar('postid'));

            # Check if the staff user is the actual one who manages this publication
            if (!($context->user()->id == $pub->uploaderId))
            {
                # If it's not then you can't delete it!
                echo "Error: you are not allowed to delete this post.";
                return;
            }

            # Look up the post and unset it.
            unset($pub->xownPost[$post->id]);

            # Trash the post itself.
            R::trash($post);

            echo "";

        }

/**
 * Delete a publication - must be staff to do this.
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function delpub($context)
        {
            # Automatically returns 403 if the user is not staff.
            $context->mustbestaff();

            # Get the publication.
            $pub = R::load('publication', $context->mustpostpar('id'));

            # Check if the staff user is the actual one who uploaded it
            if (!($context->user()->id == $pub->uploaderId))
            {
                # If it's not then you can't delete it!
                echo "Error: you are not allowed to delete this publication.";
                return;
            }

            foreach ($pub->xownFile as $file)
            {
                # Delete the file from the actual file system
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file->path)) 
                {
                    # Delete from FS.
                    unlink($_SERVER['DOCUMENT_ROOT'] . $file->path);
                } 
                else 
                {
                    echo "Error: filesystem access could not let file be deleted.";
                    return;
                }
                # Finally trash the file.
                R::trash($file);
            }

            # Unset the authors
            foreach ($pub->ownSharedAuthor as $index => $auth)
            {
                unset($pub->ownSharedAuthor[$index]);
            }

            # Unset the bookmarks
            foreach ($pub->ownSharedUser as $index => $user)
            {
                unset($pub->ownSharedUser[$index]);
            }


            # Trash the entire publication - posts are cascaded deleted.
            R::trash($pub);
            
            # Empty return indicates deletion.
            echo "";

        }


/**
 * Add a Publication type
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function addtype($context)
        {
            $t = R::dispense('type');
            $t->name = $context->mustpostpar('name');
            R::store($t);
            echo $t->getID();
        }


/**
 * Add a Department
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function adddepartment($context)
        {
            $d = R::dispense('department');
            $d->name = $context->mustpostpar('name');
            R::store($d);
            echo $d->getID();
        }

/**
 * Add a User
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function adduser($context)
        {
            $now = $context->utcnow(); # make sure time is in UTC
            $u = R::dispense('user');
            $u->login = $context->mustpostpar('login');
            $u->email = $context->mustpostpar('email');
            $u->active = 1;
            $u->confirm = 1;
            $u->joined = $now;
            R::store($u);
            $u->setpw($context->mustpostpar('password'));
            if ($context->postpar('admin', 0) == 1)
            {
                $u->addrole('Site', 'Admin', '', $now);
            }
            if ($context->postpar('devel', 0) == 1)
            {
                $u->addrole('Site', 'Developer', '', $now);
            }
            if ($context->postpar('staff', 0) == 1)
            {
                $u->addrole('Site', 'Staff', '', $now);
            }

            echo $u->getID();
        }
/**
 * Add a Page
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function addpage($context)
        {
            $p = R::dispense('page');
            $p->name = $context->mustpostpar('name');
            $p->kind = $context->mustpostpar('kind');
            $p->source = $context->mustpostpar('source');
            $p->active = $context->mustpostpar('active');
            $p->admin = $context->mustpostpar('admin');
            $p->needlogin = $context->mustpostpar('login');
            $p->mobileonly = $context->mustpostpar('mobile');
            $p->devel = $context->mustpostpar('devel');
            R::store($p);
            echo $p->getID();
        }
/**
 * Add a Rolename
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function addrole($context)
        {
            $p = R::dispense('rolename');
            $p->name = $context->mustpostpar('name');
            $p->fixed = 0;
            R::store($p);
            echo $p->getID();
        }
/**
 * Add a Rolecontext
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function addcontext($context)
        {
            $p = R::dispense('rolecontext');
            $p->name = $context->mustpostpar('name');
            $p->fixed = 0;
            R::store($p);
            echo $p->getID();
        }
/**
 * Delete a bean
 *
 * The type of bean to be deleted is part of the message
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function delbean($context)
        {
            R::trash($context->load($context->mustpostpar('bean'), $context->mustpostpar('id')));
        }
/**
 * Delete a User
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function deluser($context)
        {
            R::trash($context->load('user', $context->mustpostpar('id')));
        }
/**
 * Toggle a flag field in a bean
 *
 * Note that for Roles the toggling is more complex and involves role removal/addition rather than
 * simply changing a value.
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        private function toggle($context)
        {
            $type = $context->mustpostpar('bean');
            $field = $context->mustpostpar('field');

            $bn = $context->load($type, $context->mustpostpar('id'));
            if ($type === 'user' && ctype_upper($field[0]))
            { # not simple toggling...
                if (is_object($bn->hasrole('Site', $field)))
                {
                    $bn->delrole('Site', $field);
                }
                else
                {
                    $bn->addrole('Site', $field, '', $context->utcnow());
                }
            }
            else
            {
                $bn->$field = $bn->$field == 1 ? 0 : 1;
                R::store($bn);
            }
        }
/**
 * Handle AJAX operations
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        public function handle($context)
        {
            if (($lg = $context->getpar('login', '')) != '')
            { # this is a parsley generated username check call
                if (R::count('user', 'login=?', array($lg)) > 0)
                {
                    return (new Web)->notfound(); // error if it exists....
                }
            }
            else
            {
                $op = $context->mustpostpar('op');
                if (isset(self::$ops[$op]))
                { # a valid operation
                    if (self::$ops[$op][0])
                    { # this operation requires a logged in user
                        $context->mustbeuser();
                    }
                    if (self::$ops[$op][1])
                    { # this operation needs admin privileges
                        $context->mustbeadmin();
                    }
                    if (self::$ops[$op][2])
                    { # this operation needs developer privileges
                        $context->mustbedeveloper();
                    }
                    $this->{$op}($context);
                }
                else
                { # return a 400
                    (new Web)->bad();
                }
            }
            exit;
        }
    }
?>