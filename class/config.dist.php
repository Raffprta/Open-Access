<?php
/**
 * Configuration constants. Can be auto-created from an installer or
 * editted by  hand....
 *
 * If DBHOST is set to the empty string, no database setup will take place.
 */
    class Config
    {
        const BASEDNAME	    = '/framework'; # directory path within the HTTPD directory

        const DBHOST	    = ''; # The hostname for the database server - usually localhost (see above)
        const DB	    = 'database name'; # The name of the database to use
        const DBUSER	    = 'database username'; # The username required to access the database
        const DBPW	    = 'database password'; # The password for accessing the database

        const SITENAME	    = 'Framework'; # The name of the site that is used when generating messages (see UserLogin and Context classes)
        const SITEURL	    = 'http://site.domain'; # see UserLogin class - used in messages to users
        const SITENOREPLY   = 'noreply@site.domain'; # see UserLogin class - used in messages to users

        const SYSADMIN      = 'email@address'; # a valid email address for the system adminstrator : error messages get sent here...
    }
?>
