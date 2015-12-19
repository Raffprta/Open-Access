<?php
/**
 * A class that contains code to handle the display of a user profile.
 * This is set in the framework to only allow users who are logged in,
 * thus the framework will handle non-logged in users accessing this page.
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Profile extends Siteaction
    {
/**
 * Handle profile viewing which will display profile information /
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
        public function handle($context)
        {
            # Set up a type variable to see what roles the user are.
            $types = [];
            # If a user has access to this page, they must by default be
            # a normal user, or the page would have already returned 403.
            $types[] = 'Normal User';
            
            if ($context->hasstaff())
            {
                $types[] = 'Staff';
            }
            if ($context->hasadmin())
            {
                $types[] = 'Administrator';
            }
            if ($context->hasdeveloper())
            {
                $types[] = 'Developer';
            }
                        
            # Grab the user from the local context.
            $user = $context->user();
            # Send the user information to the twig template, formatted.
            $context->local()->addval(['user' => $user, 'role' => implode(', ', $types)]);
            # Return the template name to the framework.
            return 'profile.twig';
        }
    }
?>
