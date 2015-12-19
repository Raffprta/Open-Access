<?php
/**
 * A model class for the RedBean object Role
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015 Newcastle University
 *
 */
/**
 * A class implementing a RedBean model for Role beans
 */
    class Model_Role extends RedBean_SimpleModel
    {
/**
 * Return rolenam object
 *
 * @return object
 */
        public function rolename()
        {
	    return $this->bean->rolename;
        }
/**
 * Return rolenam object
 *
 * @return object
 */
        public function rolecontext()
        {
	    return $this->bean->rolecontext;
        }
    }
?>
