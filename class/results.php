<?php
/**
 * A class that contains code to handle any requests for the results page
 * Provides common functionality for "display" pages.
 *
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Results extends Siteaction
    {

/**
 *
 * @var object    $pubs    An array of publications
 *
 */
    	private $pubs;

/**
 *
 * @var string    $url     The url for pagination.
 *
 */
        private $url;

/**
 *
 * @var int       $pagesize The number of publications per page
 *
 */
        private $pagesize;

/**
 *
 * @var int       $amount   The total number of publications 
 *
 */
        private $amount;

/**
 *
 * @var int       $page     The current page.
 */
        private $page;

/**
 * Construct the result handler /
 *
 * @param object    $pubs         The publications to populate
 * @param string    $url          The URL to populate
 * @param int       $pagesize     The number of publications per page
 * @param int       $amount       The total number of publications.
 * @param int       $page         The current page.
 *
 */
    	function __construct($pubs, $url, $pagesize, $amount, $page) 
    	{
    		$this->pubs      = $pubs;
            $this->url       = $url;
            $this->pagesize  = $pagesize;
            $this->amount    = $amount;
            $this->page      = $page;
   		}
/**
 * Handle publication results operations /
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
        public function handle($context)
        {

            # Defensive programming, this will be detected by TWIG and provide an error
            # On the results page.
            if (empty($this->pubs))
            {
                return 'results.twig';
            }

        	$last = '';
        	$img = [];
            $cat = [];

        	foreach ($this->pubs as $pub)
        	{
        		# Set the last file as the image (this is verified at upload time)
        		foreach ($pub->xownFile as $file)
        		{
        			$last = $file->path;
        		}

        		$img[] = $last;

                # Get the category
                $cat[] = R::load('type', $pub->category);

        	}

        	# Pass the publication and image data to the templater.
        	$context->local()->addval(['pubs'     => $this->pubs,
        							   'images'   => $img,
                                       'category' => $cat,
                                       'url'      => $this->url,
                                       'pagesize' => $this->pagesize,
                                       'amount'   => ceil($this->amount/$this->pagesize),
                                       'page'     => $this->page]);
            return 'results.twig';
        }
    }
?>
