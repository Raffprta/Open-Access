<?php
/**
 * A class that contains code to handle any requests for the find page
 *
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Find extends Siteaction
    {

/**
 * @var string      the form input name for the title
 */
        private $titlename           = 'titlepub';   

/**
 * @var string      the form input name for the author
 */
        private $authorname          = 'author';    

/**
 * @var string      the form input name for the department
 */
        private $departmentname      = 'department';    

/**
 * @var string      the form input name for the category
 */
        private $categoryname        = 'category';     


/**
 * Map searches depending on how they are set, to find a bean list of publications.
 * For example if the category and author name is set then the user wants to search
 * by author names which publish under that category, or if all four fields are filled
 * out then the user wants to search for all of these connected by ANDs. 
 *
 * @param array    $searchparams   Which options to search by.
 *
 * @return object  An array of publication beans.
 */
        public function searchusing($searchparams)
        {
            
            $pubs = null;

            # All the possible keys
            $possiblekeys = [$this->authorname, $this->titlename, $this->departmentname, $this->categoryname];

            # Check which keys exist and populate query.
            $query = '';
            $queryParams = [];

            foreach ($searchparams as $key => $value)
            {
                switch ($key)
                {
                    case $this->titlename:
                    # Explode any whitespace (it is already trimmed) to find keywords to search.
                    $keywords = explode(' ', $value);

                    if (!empty($keywords))
                    {
                        $query .= "(";
                    }

                    foreach ($keywords as $id => $keys)
                    {
                        $query .= '(name LIKE ?) OR (description LIKE ?) OR ';
                        $queryParams[] = '%' . $keys . '%';                        
                        $queryParams[] = '%' . $keys . '%';
                    }
                    # Remove last OR from query string.
                    $query = preg_replace('/OR\s+$/', ') AND ', $query);
                    break;

                    case $this->departmentname:
                    $query .= 'department = ? AND ';
                    $queryParams[] = $value;
                    break;

                    case $this->categoryname:
                    $query .= 'category  = ? AND ';
                    $queryParams[] = $value;
                    break;

                    default:
                    break;

                }
            }

            # Remove last AND from query string.
            $query = preg_replace('/AND\s+$/', '', $query);

            # If the query string isn't empty, find publication
            if (!empty($query))
            {
                $pubs = R::find('publication', $query, $queryParams);
            }


            # Authors are mapped many to many, so to find a matching author we have two 
            # remaining cases. $pubs populated by the query and needs trimming.
            # Or $pubs empty and just an author was specified.
            if (empty($pubs) && array_key_exists($this->authorname, $searchparams))
            {

                # For each publication, check which ones match.
                $allpubs = R::findAll('publication');

                foreach ($allpubs as $pub)
                {
                    foreach ($pub->sharedAuthor as $auth)
                    {

                        if (strpos(strtolower($auth->name), $searchparams[$this->authorname]) !== FALSE)
                        {
                            $pubs[] = $pub;
                        }
                    }
                }

            }
            else if (!empty($pubs) && array_key_exists($this->authorname, $searchparams))
            {
                # Search the publications we have instead of all publications as this user
                # wants to search based on some conditions AND the author.
                $allpubs = $pubs;
                $pubs = [];

                foreach ($allpubs as $pub)
                {
                    foreach ($pub->sharedAuthor as $auth)
                    {

                        if (strpos(strtolower($auth->name), $searchparams[$this->authorname]) !== FALSE)
                        {
                            $pubs[] = $pub;
                        }
                    }
                }
            }

            return $pubs;

        }

/**
 * Handle publication find operations /
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle($context)
        {
        	# Check if the request is a GET with params, using POST or other
        	# HTTP methods makes no sense, so we will ignore those as well as the standard GET.
        	if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET))
        	{
        		# Determine which GET Parameters were set.
        		# 1 or more search options may have been specified
        		# Thus check which ones have values to search for.
                $hasbytitle = $context->hasgetpar($this->titlename);
                $hasbycategory = $context->hasgetpar($this->categoryname);
                $hasbyauthor = $context->hasgetpar($this->authorname);
                $hasbydepartment = $context->hasgetpar($this->departmentname);

                # Check if string is not empty.
                $hasbytitle &= !empty($context->getpar($this->titlename, ''));
                $hasbycategory &= !empty($context->getpar($this->categoryname, ''));
                $hasbyauthor &= !empty($context->getpar($this->authorname, ''));
                $hasbydepartment &= !empty($context->getpar($this->departmentname, ''));
                # Returns false if all false
                $hasonegetpar = $hasbytitle || $hasbydepartment || $hasbycategory || $hasbyauthor;

                if (!$hasonegetpar)
                {
                    $context->local()->message('error', 'Ensure you have filled out at least one option to search by.');
                    return 'find.twig';
                }

                $searchparams = [];

                # Get the URL for the paginator.
                $url = $context->action() .'?';

                # Set the search params.
                if ($hasbytitle)
                {
                    $searchparams[$this->titlename] = strtolower($context->getpar($this->titlename, ''));
                    $url .= '&' . $this->titlename . '=' . $context->getpar($this->titlename, '');
                }

                if ($hasbydepartment)
                {
                    $searchparams[$this->departmentname] = $context->getpar($this->departmentname, '');
                    $url .= '&' . $this->departmentname . '=' . $context->getpar($this->departmentname, '');
                }

                if ($hasbyauthor)
                {
                    $searchparams[$this->authorname] = strtolower($context->getpar($this->authorname, ''));
                    $url .= '&' . $this->authorname . '=' . $context->getpar($this->authorname, '');
                }

                if ($hasbycategory)
                {
                    $searchparams[$this->categoryname] = $context->getpar($this->categoryname, '');
                    $url .= '&' . $this->categoryname . '=' . $context->getpar($this->categoryname, '');
                }

                # Otherwise, call the search method mapper.
                $pubs = $this->searchusing($searchparams);

                # Append the pagination queries.
                $url .= '&';

                # Ensure results unique
                $pubs = array_unique($pubs);

                # Other paginator info.
                $pagesize = 5;
                $amount = count($pubs);
                $page = $context->getpar('page', 1); 

                # Slice the publication array by pagination amount (5 per page)
                $pubs = array_slice($pubs, ($page-1) * $pagesize, $pagesize);

                # Finally, use the results handler to populate a results page with the publications!
                return (new Results($pubs, $url, $pagesize, $amount, $page))->handle($context);
        		
        	}

        	# Otherwise just return the form.
            return 'find.twig';
        }
    }
?>
