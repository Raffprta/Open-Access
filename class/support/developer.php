<?php
/**
 * Contains definition of abstract Developer class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * Class for developer hacks and helpers...
 */
    Class Developer extends SiteAction
    {
/**
 * Handle various admin operations /devel/xxxx
 *
 * The test for developer status is done in index.php so deos not need to be repeated here.
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    if ($context->hasdeveloper())
	    {
		$tpl = 'support/devel.twig';
                $rest = $context->rest();
		switch ($rest[0])
		{
                case 'hack': # execute some code.
                    R::freeze(FALSE);
                    include('hack.php');
                    break;

                case 'fail': # this lets you test error handling
                    $x = 2 / 0;
                    break;

                case 'throw': # this lets you test exception handling
                    throw new Exception('Unhandled Exception Test');

		case 'mail' : # this lets you test email sending
		    $foo = mail($context->user()->email, 'test', 'test');
		    $context->local()->message('message', 'sent');
		    break;
/*
		case 'errlog' : # this will show you the contents of the PHP error log file.
		    $context->local()->addval('errlog', file_get_contents(Config::PHPLOG));
		    exit;

		case 'clearlog' :
		    fclose(fopen(Config::PHPLOG, 'w'));
		    $context->local()->message('message', 'Log Cleared');
		    break;
*/
                }
	    }
	    else
	    {
                (new Web)->noaccess();
	    }
	    return $tpl;
	}

    }
?>
