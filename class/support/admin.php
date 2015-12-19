<?php
/**
 * Contains definition of Admin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * A class that contains code to handle any /admin related requests.
 *
 * Admin status is checked in index.php so does not need to be done here.
 */
    class Admin extends Siteaction
    {
/**
 * Handle various admin operations /admin/xxxx
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    $tpl = 'support/admin.twig';
	    $rest = $context->rest();
	    switch ($rest[0])
	    {
	    case 'pages':
		$tpl = 'support/pages.twig';
		break;

		case 'department':
		$tpl = 'support/department.twig';
		break;

		case 'publication':
		$tpl = 'support/publication.twig';
		break;

	    case 'contexts':
		$tpl = 'support/contexts.twig';
		break;

	    case 'roles':
		$tpl = 'support/roles.twig';
		break;

	    case 'users':
		$tpl = 'support/users.twig';
		break;

	    case 'info':
		$_SERVER['PHP_AUTH_PW'] = '*************'; # hide the password in case it is showing.
	        phpinfo();
		exit;

	    case 'edit' : // Edit something - at the moment just a User
	        if (count($rest) < 3)
		{
		    (new Web)->bad();
		}
	        $kind = $rest[1];
                $obj = $context->load($kind, $rest[2]);
                if (!is_object($obj))
                {
                    (new Web)->bad();
                }
                if (($bid = $context->postpar('bean', '')) != '')
                { # this is a post
                    if ($bid != $obj->getID())
                    { # something odd...
                        (new Web)->bad();
                    }
                    $obj->edit($context);
                    // The edit call might divert to somewhere else so sometimes we may not get here.
                }
		$context->local()->addval($kind, $obj);
		$tpl = 'support/edit'.$kind.'.twig';
		break;

	    default :
		break;
	    }
	    return $tpl;
	}
    }
?>
