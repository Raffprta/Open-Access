<?php
/**
 * Definition of Publication class
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 */
/**
 * A class to handle the /login, /logout, /register, /forgot and /resend actions
 */
    class Publication extends Siteaction
    {
/**
 * Handle Publication setting.
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */

		public function handle($context)
		{

			$action = $context->action();
	    	return $this->$action($context);
		
		}

    }
?>