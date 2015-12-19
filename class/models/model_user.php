<?php
/**
 * A model class for the RedBean object User
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2013-2014 Newcastle University
 *
 */
/**
 * A class implementing a RedBean model for User beans
 */
    class Model_User extends RedBean_SimpleModel
    {
/**
 * Check for a role
 *
 * @param string        $contextname    The name of a context...
 * @param string	$rolename       The name of a role....
 *
 * @return object
 */
        public function hasrole($contextname, $rolename)
        {
            $cname = R::findOne('rolecontext', 'name=?', array($contextname));
            $rname = R::findOne('rolename', 'name=?', array($rolename));
            return R::findOne('role', 'rolecontext_id=? and rolename_id=? and user_id=? and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                array($cname->getID(), $rname->getID(), $this->bean->getID()));
        }
/**
 * Check for a role
 *
 * @param string	$contextname    The name of a context...
 * @param string	$rolename       The name of a role....
 *
 * @return void
 */
        public function delrole($contextname, $rolename)
        {
            $cname = R::findOne('rolecontext', 'name=?', array($contextname));
            $rname = R::findOne('rolename', 'name=?', array($rolename));
            $bn = R::findOne('role', 'rolecontext_id=? and rolename_id=? and user_id=? and start <= UTC_TIMESTAMP() and (end is NULL or end >= UTC_TIMESTAMP())',
                array($cname->getID(), $rname->getID(), $this->bean->getID()));
            if (is_object($bn))
            {
                R::trash($bn);
            }
        }
/**
 *  Add a role
 *
 * @param string	$contextname    The name of a context...
 * @param string	$rolename       The name of a role....
 * @param string	$otherinfo      Any other info that is to be stored with the role
 * @param string	$start		A datetime
 * @param string	$end		A datetime or ''
 *
 * @return object
 */
        public function addrole($contextname, $rolename, $otherinfo, $start, $end = '')
        {
            $cname = R::findOne('rolecontext', 'name=?', array($contextname));
            if (!is_object($cname))
            {
                (new Web)->bad();
            }
            $rname = R::findOne('rolename', 'name=?', array($rolename));
            if (!is_object($rname))
            {
                (new Web)->bad();
            }
            $this->addrolebybean($cname, $rname, $otherinfo, $start, $end);
        }
/**
 *  Add a role
 *
 * @param object	$context        Contextname
 * @param object	$role           Rolename
 * @param string	$otherinfo      Any other info that is to be stored with the role
 * @param string	$start		A datetime
 * @param string	$end		A datetime or ''
 *
 * @return object
 */
        public function addrolebybean($context, $role, $otherinfo, $start, $end = '')
        {
            $r = R::dispense('role');
            $r->user = $this->bean;
            $r->rolecontext = $context;
            $r->rolename = $role;
            $r->otherinfo = $otherinfo;
            $r->start = $start;
            $r->end = $end == '' ? NULL : $end;
            R::store($r);
        }
/**
 * Get all currently valid roles for this user
 *
 * @param boolean	$all	If TRUE then include expired roles
 *
 * @return array
 */
        public function roles($all = FALSE)
        {
	    if ($all)
	    {
	        return $this->bean->with('order by start,end')->ownRole;
	    }
            return $this->bean->withCondition('start <= UTC_TIMESTAMP() and (end is null or end >= UTC_TIMESTAMP()) order by start, end')->ownRole;
        }
/**
 * Is this user an admin?
 *
 * @return boolean
 */
        public function isadmin()
        {
            return is_object($this->hasrole('Site', 'Admin'));
        }

/**
 * Is this user a staff?
 *
 * @return boolean
 */
        public function isstaff()
        {
            return is_object($this->hasrole('Site', 'Staff'));
        }

/**
 * Is this user active?
 *
 * @return boolean
 */
        public function isactive()
        {
            return $this->bean->active;
        }
/**
 * Is this user confirmed?
 *
 * @return boolean
 */
        public function isconfirmed()
        {
            return $this->bean->confirm;
        }
/**
 * Is this user a developer?
 *
 * @return boolean
 */
        public function isdeveloper()
        {
            return is_object($this->hasrole('Site', 'Developer'));
        }
/**
 * Set the user's password
 *
 * @param string	$pw	The password
 *
 * @return void
 */
        public function setpw($pw)
        {
            $this->bean->password = password_hash($pw, PASSWORD_DEFAULT);
            R::store($this->bean);
        }
/**
 * Check a password
 *
 * @param string	$pw The password
 *
 * @return boolean
 */
        public function pwok($pw)
        {
            return password_verify($pw, $this->bean->password);
        }
/**
 * Set the email confirmation flag
 *
 * @return void
 */
        public function doconfirm()
        {
            $this->bean->active = 1;
            $this->bean->confirm = 1;
            R::store($this->bean);
        }
/**
 * Generate a token for this user that can be used as a unique id from a phone.
 *
 * @param string    $device     Currently not used!!
 *
 * @return string
 */
	public function maketoken($device = '')
	{
	    $token = new stdClass;
	    $token->iss = Config::SITEURL;
	    $token->iat = idate('U');
	    $token->sub = $this->bean->getID();
	    return JWT::encode($token, Context::KEY);
	}
/**
 * Handle an edit form for this user
 *
 * @param onbject   $context    The context object
 *
 * @return void
 */
        public function edit($context)
        {
            $change = FALSE;
            foreach (array('email') as $fld)
            { // might eneed more fields for different applications
                $val = $context->postpar($fld, '');
                if ($fld == '')
                { // this is an error as these must be present
                }
                elseif ($val != $this->bean->$fld)
                {
                    $this->bean->$fld = $val;
                    $change = TRUE;
                }
            }
            $pw = $context->postpar('pw', '');
            if ($pw != '' && $pw == $context->postpar('rpw', ''))
            {
                $this->setpw($pw);
                $change = FALSE; // setting the password will do a store...
            }
            if ($change)
            {
                R::store($this->bean);
            }
            $uroles = $this->roles();
	    if (filter_has_var(INPUT_POST, 'exist'))
	    {
                foreach ($_POST['exist'] as $ix => $rid)
                {
                    $rl = $context->load('role', $rid);
                    $start = $_POST['xstart'][$ix];
                    $end = $_POST['xend'][$ix];
                    $other = $_POST['xotherinfo'][$ix];
                    if (strtolower($start) == 'now')
                    {
                        $rl->start = $context->utcnow();
                    }
                    elseif ($start != $rl->start)
                    {
                        $rl->start = $context->utcdate($start);
                    }
                    if (strtolower($end) == 'never' || $end == '')
                    {
                        if ($rl->end != '')
                        {
                            $rl->end = NULL;
                        }
                    }
                    elseif ($end != $rl->end)
                    {
                         $rl->end = $context->utcdate($end);
                    }
                    if ($other != $rl->otherinfo)
                    {
                        $rl->otherinfo = $other;
                    }
                    R::store($rl);
                }
	    }
            foreach ($_POST['role'] as $ix => $rn)
            {
                $cn = $_POST['context'][$ix];
                if ($rn != '' && $cn != '')
                {
                    $end = $_POST['end'][$ix];
                    $start = $_POST['start'][$ix];
                    $this->addrolebybean($context->load('rolecontext', $cn), $context->load('rolename', $rn), $_POST['otherinfo'][$ix],
                        strtolower($start) == 'now' ? $context->utcnow() : $context->utcdate($start),
                        strtolower($end) == 'never' || $end == '' ? '' : $context->utcdate($end)
                    );
                }
            }
            return TRUE;
        }
    }
?>
