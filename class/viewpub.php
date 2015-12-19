<?php
/**
 * A class that contains code to handle any requests for the publication page
 *
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Viewpub extends Siteaction
    {
/**
 * Handle publication view operations /
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
        public function handle($context)
        {
        	# Get the publication object matching this id and pass to twig.
        	$pub = R::load('publication', $context->rest()[0]);

            # Populate some variables to pass to twig.
        	$auths = [];
        	$files = [];
            $posters = [];
            $bookmarksuser = [];
            $setbookmark = false;
        	$publisher = '';
        	$department = '';

        	# If a publication exists, populate the realtionship data.
        	if ($pub->id > 0)
        	{
        		foreach ($pub->sharedAuthor as $auth)
    			{
					$auths[] = $auth;
    			}

    			foreach ($pub->xownFile as $file)
    			{
    				$files[] = $file;
    			}

                foreach ($pub->sharedUser as $user)
                {
                    $bookmarksuser[] = $user;
                }

                # If this user has bookmarked it - only if a user is logged in.
                if ($context->hasuser())
                {
                    foreach ($bookmarksuser as $bookmarker)
                    {
                        $setbookmark = $bookmarker->id == $context->user()->id ? true : false;
                        if($setbookmark)
                        {
                            break;
                        }
                    }    
                }

                foreach ($pub->xownPost as $post)
                {
                    $posters[] = R::load('user', $post->poster)->login;
                }


    			# Redbean compliance requires calling uploader via uploaderId
    			$publisher = R::load('user', $pub->uploaderId);
    			$department = R::load('department', $pub->department);
                $category = R::load('type', $pub->category);

                # When adding values to the Twig, separate the preview image from the files.
                $context->local()->addval(['pub' => $pub,
                                           'auths' => $auths,
                                           'files' => array_slice($files, 0, -1),
                                           'publisher' => $publisher,
                                           'department' => $department,
                                           'image' => $files[count($files) - 1], 
                                           'setbookmark' => $setbookmark,
                                           'category' => $category,
                                           'posts'    => $pub->xownPost,
                                           'posters'  => $posters]);

        	}
        	
            return 'viewpub.twig';
        }
    }
?>
