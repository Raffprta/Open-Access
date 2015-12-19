<?php
/**
 * A class that contains code to handle any requests for the 
 * custom source code viewer page.
 *
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Viewsrc extends Siteaction
    {


/**
 * Handle view source operations 
 * Takes a file which is text/* MIME encoded and displays it
 * to the pager. This will by default look in /assets/data/ for security.
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
        public function handle($context)
        {

            # Ensure a file was passed
            if (empty($context->rest()[0]))
            {
                $context->local()->addval('error', 'This page is not available as a standalone.');
                return 'viewsrc.twig';
            }

            # Get the File from the REST 
            $path = $context->local()->base() . '/assets/data/' . implode($context->rest(), '/');

            # Set the title to be the file name.
            $title = $context->rest()[count($context->rest()) - 1];

            # Remove .. (up one) from the path for security
            $path = str_replace('..', '', $path);

            # Finally add the root.
            $path = $_SERVER['DOCUMENT_ROOT'] . $path;

            # Ensure file exists
            if (!file_exists($path))
            {
                $context->local()->addval('error', 'File could not be found');
                return 'viewsrc.twig';
            }

            # Open file
            $fh = fopen($path,'r');

            $lines = '';

            # Get the lines
            while ($line = fgets($fh)) {
                $lines .= $line;
            }

            # Release resources
            fclose($fh);

            $context->local()->addval('code', $lines);
            $context->local()->addval('title', $title);
            return 'viewsrc.twig';
        }
    }
?>
