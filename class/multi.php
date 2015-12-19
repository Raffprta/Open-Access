<?php
/**
 * A class that contains code to implement Multi nested static pages
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    class Multi extends Siteaction
    {
/**
 * Handles static pages that are nested in depth /multi/level/page
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle($context)
        {
            $action = $context->action();
            $rest = $action.'/'.implode(DIRECTORY_SEPARATOR, $context->rest());
            if (!file_exists($context->local()->basedir().'/twigs/'.$rest.'.twig'))
            {
                (new Web)->notfound();
            }
            return $rest.'.twig';
        }
    }
?>
