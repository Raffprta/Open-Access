<?php
/**
 * A class that handles the /info action and only calls phpinfo() and exits....
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    class Info extends Siteaction
    {
/**
 * The handler for this class
 *
 * @param object	$context	The context object for the site
 *
 * @return string	An empty string as phpinfo generates the HTML for us....
 */
        public function handle($context)
        {
            if (isset($_SERVER['PHP_AUTH_PW']))
            { # hide the password field!!
                $_SERVER['PHP_AUTH_PW'] = '*******************';
            }
            phpinfo();
            return '';
        }
    }
?>
