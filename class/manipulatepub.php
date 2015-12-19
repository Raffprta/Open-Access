<?php
/**
 * A class that contains code to handle any requests to edit a publication RESTfully.
 * This handles CRUD Update and Create operations and can be instantiated by both the
 * Upload class and the Editpub class to create and edit publications dynamically.
 *
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Manipulatepub
    {

/**
 * @var object      The publication object
 */
        private $pub;

/**
 * @var object      The error template
 */
        private $err;


/**
 * @var int         The max. character to persist in the DB.
 */
        private $maxname = 50;


/**
 * Construct the edit publication handler /
 *
 * @param object    $pubs    The publications to construct
 *
 */
        function __construct($pub, $err) 
        {
            $this->pub = $pub;
            $this->err = $err;
        }

/**
 * Get the publication object.
 *
 *
 * @return object   The publication
 */
    public function getpub(){
        return $this->pub;
    }

/**
 * Handle publication edit operations and validation /
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name containing any errors or if successful an empty string
 *                   where then you can call $this->getpub();
 */
        public function manipulatepublication($context)
        {
            # Fill out atomic information for the publication - sanitize against HTML entities
            $this->pub->name = strip_tags($context->mustpostpar('titlepub'));
            
            # Ensure category/department is a number.
            $this->pub->category = $context->mustpostpar('category');
            $this->pub->department = $context->mustpostpar('department');

            # Associate description and license.
            $this->pub->description = strip_tags($context->mustpostpar('description'));
            $this->pub->license = strip_tags($context->mustpostpar('license'));

            if (!is_numeric($this->pub->department) || !is_numeric($this->pub->category))
            {
                # Someone was fiddling with the HTML...
                return 'Please use the dropdown to select items.';
            }

            if (strlen($this->pub->name) == 0 || strlen($this->pub->name) > $this->maxname)
            {
                # 0 or too long input
                return 'Input characters for the title must be > 0 or less than ' . $this->maxname;
            }

            if (strlen($this->pub->description) == 0)
            {
                # 0 or too long input
                return 'Input characters for the description must be > 0';
            }

            if (strlen($this->pub->license) == 0)
            {
                # 0 or too long input
                return 'Input characters for the license must be > 0';
            }
            
            # Sanitize the authors array - use $_POST as the framework does
            # not support getpostpar for arrays.
            $authors = $_POST['authors'];
            $authors = filter_var_array($authors, FILTER_SANITIZE_STRING); 

            # Error check the authors
            foreach ($authors as $auth)
            {
                if (strlen($auth) == 0 || strlen($auth) > $this->maxname)
                {
                    # 0 or too long input
                    return 'Ensure your authors names are between 0 and ' . $this->maxname;
                }
            }
            
            # Get the catgory name
            $cat = R::findOne('type', 'id=?', array($this->pub->category));

            if (empty($cat))
            {
                # Hacking the HTML.
                return 'Please do not change the values of the category radio buttons.';
            }

            # Ensure departments do exist before persisting data.
            $dep = R::findOne('department', 'id=?', array($this->pub->department));

            if (empty($dep))
            {
                # Hacking the HTML.
                return 'Please do not change the values of the department selection.';
            }
            
            # For each author dispense a new bean - framework currently has 
            # no array support so use $_POST
            foreach ($authors as $author)
            {
                # Attempt to load whether said author exists in the table.
                $a = R::findOne('author', 'name=?', array($author));
                
                # If it doesn't, then dispense a new author!
                if($a == null)
                {
                    $a = R::dispense('author');
                }
                
                # Set values
                $a->name = $author;
                # Set up many to many relation.
                $a->sharedPublications[] = $this->pub;
                $this->pub->sharedAuthors[] = $a;
            }
            

            # Finally associate the staff member uploading to the publication
            $this->pub->uploader = $context->user();
            # Return empty string to indicate success
            return "";

            
        }
    }
?>