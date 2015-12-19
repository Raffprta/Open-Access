<?php
/**
 * A class that contains code to return info needed in various places on the site
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2014 Newcastle University
 *
 */
    class SiteInfo
    {
/**
 * Get all the user beans
 *
 * @return array
 */
        public function users()
        {
            return R::findAll('user', 'order by login');
        }
/**
 * Get all the page beans
 *
 * @return array
 */
        public function pages()
        {
            return R::findAll('page', 'order by name');
        }
/**
 * Get all the Rolename beans
 *
 * @return array
 */
        public function roles()
        {
            return R::findAll('rolename', 'order by name');
        }
/**
 * Get all the Rolecontext beans
 *
 * @return array
 */
        public function contexts()
        {
            return R::findAll('rolecontext', 'order by name');
        }
    

/**
 * Get all the Department beans
 *
 * @return array
 */
        public function departments()
        {
            return R::findAll('department', 'order by name');
        }
    

/**
 * Get all the Publication beans
 *
 * @return array
 */
        public function publications()
        {
            return R::findAll('publication', 'order by name');
        }

/**
 * Get all the Authors beans
 *
 * @return array
 */
        public function authors()
        {
            return R::findAll('author', 'order by name');
        }


/**
 * Get all the Publication Type beans
 *
 * @return array
 */
        public function publicationtypes()
        {
            return R::findAll('type', 'order by name');
        }

    }

?>
