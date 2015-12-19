<?php
/**
 * A class that contains code to handle any requests for the recent results page
 *
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Recent extends Siteaction
    {
/**
 * Handle publication recent results operations /
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
        public function handle($context)
        {
            $pagesize = 5;
            $amount = R::count('publication');
            $page = $context->getpar('page', 1); 
            # Find all publications and paginate them.
        	$pubs = R::findAll('publication',  'ORDER BY Id desc limit ?,?', [($page-1)*$pagesize, $pagesize]);

            # Get the URL for the paginator.
            $url = $context->action() . '?';

        	# Delegate the publishement bean to the results handler.
        	return (new Results($pubs, $url, $pagesize, $amount, $page))->handle($context);
        }
    }
?>
