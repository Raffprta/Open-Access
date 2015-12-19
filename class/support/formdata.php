<?php
/**
 * Contains the definition of the Formdata class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015 Newcastle University
 */
/**
 * A class that provides helpers for accessing form data
 */
    class Formdata
    {
        use Singleton;
/*
 *******************************************************************
 * Existence checking functions for _GET, _POST, _COOKIE and _FILES
 *******************************************************************
 */
/**
 * Is the key in the $_GET array?
 *
 * @param string	$name	The key
 *
 * @return boolean
 */
        public function hasget($name)
        {
            return filter_has_var(INPUT_GET, $name);
        }
/**
 * Is the key in the $_POST array?
 *
 * @param string	$name	The key
 *
 * @return boolean
 */
        public function haspost($name)
        {
            return filter_has_var(INPUT_POST, $name);
        }
/**
 * Is the key in the $_COOKIE array?
 *
 * @param string	$name	The key
 *
 * @return boolean
 */
        public function hascookie($name)
        {
            return filter_has_var(INPUT_COOKIE, $name);
        }
/**
 * Is the key in the $_FILES array?
 *
 * Note: no support for FILES in the filter_has_var function
 *
 * @param string	$name	The key
 *
 * @return boolean
 */
        public function hasfile($name)
        {
            return isset($_FILES[$name]);
        }
/*
 ***************************************
 * $_GET fetching methods
 ***************************************
 */
/**
 * Look in the _GET array for a key and return its trimmed value
 *
 * @param string	$name	The key
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return mixed
 */
        public function mustget($name, $fail = TRUE)
        {
            if (filter_has_var(INPUT_GET, $name))
            {
                return trim($_GET[$name]);
            }
            if ($fail)
            {
                (new Web)->bad();
            }
            return NULL;
        }
/**
 * Look in the _GET array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function get($name, $dflt)
        {
            return filter_has_var(INPUT_GET, $name) ? trim($_GET[$name]) : $dflt;
        }
/**
 * Look in the _GET array for a key that is an array and return its trimmed value
 *
 * @param string	$name	The key
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return ArrayIterator
 */
        public function mustgeta($name, $fail = TRUE)
        {
            if (filter_has_var(INPUT_GET, $name) && is_array($_GET[$name]))
            {
                return new ArrayIterator($_GET[$name]);
            }
            if ($fail)
            {
                (new Web)->bad();
            }
            return NULL;
        }
/**
 * Look in the _GET array for a key that is an array and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return ArrayIterator
 */
        public function geta($name, array $dflt = array())
        {
            return new ArrayIterator(filter_has_var(INPUT_GET, $name) && is_array($_GET[$name]) ? $_GET[$name] : $dflt);
        }
/**
 * Look in the _GET array for a key and apply filters
 *
 * @param string	$name		The key
 * @param int		$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filterget($name, $filter, $options = '')
        {
            return filter_input(INPUT_GET, $name, $filter, $options);
        }
/*
 ***************************************
 * $_POST fetching methods
 ***************************************
 */
/**
 * Look in the _POST array for a key and return its trimmed value
 *
 * @param string	$name	The key
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return mixed
 */
        public function mustpost($name, $fail = TRUE)
        {
            if (filter_has_var(INPUT_POST, $name))
            {
                return trim($_POST[$name]);
            }
            if ($fail)
            {
                (new Web)->bad();
            }
            return NULL;
        }

/**
 * Look in the _POST array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function post($name, $dflt)
        {
            return filter_has_var(INPUT_POST, $name) ? trim($_POST[$name]) : $dflt;
        }

/**
 * Look in the _POST array for a key that is an array and return an iterator
 *
 * @param string	$name	The key
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return ArrayIterator
 */
        public function mustposta($name, $fail = TRUE)
        {
            if (filter_has_var(INPUT_POST, $name) && is_array($_POST[$name]))
            {
                return new ArrayIterator($_POST[$name]);
            }
            if ($fail)
            {
                (new Web)->bad();
            }
            return NULL;
        }

/**
 * Look in the _POST array for a key that is an array and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return ArrayIterator
 */
        public function posta($name, array $dflt = array())
        {
            return new ArrayIterator(filter_has_var(INPUT_POST, $name) && is_array($_POST[$name]) ? $_POST[$name] : $dflt);
        }

/**
 * Look in the _POST array for a key and  apply filters
 *
 * @param string	$name		The key
 * @param int		$filter		Filter values - see PHP manual
 * @param mixed		$options	see PHP manual
 *
 * @return mixed
 */
        public function filterpost($name, $filter, $options = '')
        {
            return filter_input(INPUT_POST, $name, $filter, $options);
        }
/*
 ******************************
 * $_COOKIE helper functions
 ******************************
 */
/**
 * Look in the _COOKIE array for a key and return its trimmed value or fail
 *
 * @param string    $name
 * @param boolean    $fail
 *
 * @return mixed
 */
        public function mustcookie($name, $fail = TRUE)
        {
            if (filter_has_var(INPUT_COOKIE, $name))
            {
                return trim($_COOKIE[$name]);
            }
            if ($fail)
            {
                (new Web)->bad();
            }
            return NULL;
        }
/**
 * Look in the _COOKIE array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
        public function cookie($name, $dflt)
        {
            return filter_has_var(INPUT_COOKIE, $name) ? trim($_COOKIE[$name]) : $dflt;
        }
/*
 ******************************
 * $_FILES helper functions
 ******************************
 */
/**
 * Make arrays of files work more like singletons
 *
 * @param string    $name
 * @param string    $key
 *
 * @return array
 */
        public function filedata($name, $key = '')
        {
            $x = $_FILES[$name];
            if ($key !== '')
	    {
                return array(
	            'name'     => $x['name'][$key],
		    'type'     => $x['type'][$key],
		    'size'     => $x['size'][$key],
		    'tmp_name' => $x['tmp_name'][$key],
		    'error'    => $x['error'][$key]
	        );
	    }
            return $x;
        }
    }
?>