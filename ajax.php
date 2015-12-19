<?php
/**
 * Ajax entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * The real work is all done in the Ajax class.
 */
    include 'class/support/framework.php';
    Framework::initialise();

    // Ajax on, debug on, load twig, load RB
    Ajax::getinstance()->handle(Context::getinstance()->setup(Local::getinstance()->setup(__DIR__, TRUE, TRUE, TRUE, TRUE)));
?>
