<?php

/**
 * Handle publication edit operations /
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
    class Editpub extends Siteaction
    {
        public function handle($context)
        {
        	# Ensure that the user is staff at least
            $context->mustbestaff();

            # Load the publication RESTfully
        	$pub = R::load('publication', $context->rest()[0]);

            # Check if the staff is allowed to edit it.
            if ($context->user()->id != $pub->uploaderId)
            {
                $context->local()->addval('error', 'You must be the staff who uploaded this to edit it.');
                return 'editpub.twig';
            }

            # Check if it's a post 
            if ($_SERVER['REQUEST_METHOD'] === 'POST')
            {
                # Set up a new publication handler.
                $pubcreator = new Manipulatepub($pub, 'editpub.twig');
                $returnerr = $pubcreator->manipulatepublication($context);

                # If it's a non-empty string a error occured.
                if (!empty($returnerr))
                {
                    $context->local()->addval('error', $returnerr);
                    return 'editpub.twig';
                }
                
                $pub = $pubcreator->getpub();

                # Otherwise it is a publication, so store it.
                R::store($pub);
                # The publication has been sucessfully edited, let's divert the user to the view page using REST.
                $context->divert("/viewpub/" . $pub->id);
            }

            $context->local()->addval(['pub'   => $pub,
                                       'auths' => $pub->sharedAuthor]);
            return 'editpub.twig';
        }
    }
?>
