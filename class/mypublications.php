<?php
/**
 * A class that contains code to handle the display of a staff user's publications.
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class MyPublications extends Siteaction
    {
/**
 * Handle the viewing of my publications
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
        public function handle($context)
        {
            # Ensure user must be staff.
            $context->mustbestaff();

            # Get the publications the user owns.
        	$pubs = R::find('publication', 'uploader_id = ?', array($context->user()->id));

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
