<?php
/**
 * A class that contains code to handle the display of a user's booksmark.
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Bookmark extends Siteaction
    {
/**
 * Handle bookmark viewing which will display bookmarked publications /
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
        public function handle($context)
        {
        	# Return to the context a list of publications the users bookmarked
        	# Load is called on the context user id to ensure no stale beans are returned
        	$user = R::load('user',  $context->user()->id);
        	$pubs = $user->sharedPublication;

            # Get the URL for the paginator.
            $url = $context->action() . '?';

            # Other paginator info.
            $pagesize = 5;
            $amount = count($pubs);
            $page = $context->getpar('page', 1); 

            # Slice the publication array by pagination amount (5 per page)
            $pubs = array_slice($pubs, ($page-1) * $pagesize, $pagesize);

        	# Delegate the publishement bean to the results handler.
        	return (new Results($pubs, $url, $pagesize, $amount, $page))->handle($context);
        }
    }
?>
