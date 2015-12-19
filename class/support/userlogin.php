<?php
/**
 * Definition of Userlogin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * A class to handle the /login, /logout, /register, /forgot and /resend actions
 */
    class Userlogin extends Siteaction
    {
/**
 * Find a user based on either a login or an email address
 *
 * @param string	$lg     A username or email address
 *
 * @return object	The user or NULL
 */
	private function eorl($lg)
	{
	    return R::findOne('user', (filter_var($lg, FILTER_VALIDATE_EMAIL) !== FALSE ? 'email' : 'login').'=?', array($lg));
	}
/**
 * Make a confirmation code and store it in the database
 *
 * @param object	$context The context bean
 * @param object	$bn	 A User bean
 * @param string	$kind
 *
 * @return string
 */
	private function makecode($context, $bn, $kind)
	{
	    R::trashAll(R::find('confirm', 'user_id=?', array($bn->getID())));
	    $code = hash('sha256', $bn->getID.$bn->email.$bn->login.uniqid());
	    $conf = R::dispense('confirm');
	    $conf->code = $code;
	    $conf->issued = $context->utcnow();
	    $conf->kind = $kind;
	    $conf->user = $bn;
	    R::store($conf);
	    return $code;
	}
/**
 * Mail a confirmation code
 *
 * @param object	$context The context object
 * @param object	$bn	 A User bean
 *
 * @return string
 */
	private function sendconfirm($context, $bn)
	{
	    $code = $this->makecode($context, $bn, 'C');
	    mail($bn->email, 'Please confirm your email address for '.Config::SITENAME,
		"Please use this link to confirm your email address\n\n\n".
		Config::SITEURL.'/confirm/'.$code."\n\n\nThank you,\n\n The ".Config::SITENAME." Team\n\n",
		'From: '.Config::SITENOREPLY
	    );
	}
/**
 * Mail a password reset code
 *
 * @param object	$context The context object
 * @param object	$bn	A User bean
 *
 * @return string
 */
	private function sendreset($context, $bn)
	{
	    $code = $this->makecode($context, $bn, 'P');
	    mail($bn->email, 'Reset your '.Config::SITENAME.' password',
		"Please use this link to reset your password\n\n\n".
		Config::SITEURL.'/forgot/'.$code."\n\n\nThank you,\n\n The ".Config::SITENAME." Team\n\n",
		'From: '.Config::SITENOREPLY
	    );
	}
/**
 * Handle a logout
 *
 * Clear all the session material if any and then divert to the /login page
 *
 * Code taken directly from the PHP session_destroy manual page
 *
 * @link	http://php.net/manual/en/function.session-destroy.php
 *
 * @param object	$context	The context object for the site
 */
	public function logout($context)
	{
	    $_SESSION = array(); # Unset all the session variables.

	    # If it's desired to kill the session, also delete the session cookie.
	    # Note: This will destroy the session, and not just the session data!
	    if (ini_get('session.use_cookies'))
	    {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
		    $params["path"], $params["domain"],
		    $params["secure"], $params["httponly"]
		);
	    }
	    if (session_status() == PHP_SESSION_ACTIVE)
	    { # no session started yet
	        session_destroy(); # Finally, destroy the -session.
            }
	    $context->divert('/');
	}
/**
 * Handle a login
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function login($context)
	{
	    if ($context->hasuser())
	    { # already logged in
		$context->local()->message('message', 'Please log out before trying to login');
	    }
	    else
	    {
		if (($lg = $context->postpar('login', '')) !== '')
		{
                    $page = $context->postpar('page', '');
		    $pw = $context->postpar('password', '');
		    if ($pw !== '')
		    {
			$user = $this->eorl($lg);
			if (is_object($user) && $user->pwok($pw) && $user->confirm)
			{
			    if (session_status() != PHP_SESSION_ACTIVE)
			    { # no session started yet
				session_start();
			    }
			    $_SESSION['user'] = $user;
			    $context->divert($page === '' ? '/' : $page); # success - divert to home page
			}
		    }
		    $context->local()->message('message', 'Please try again.');
		}
                else
                {
                    $page = $context->getpar('page', '');
                }
                $context->local()->addval('page', $page);
	    }
	    return 'login.twig';
	}
/**
 * handle a registration
 *
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function register($context)
	{
	    $login = $context->postpar('login', '');
	    if ($login !== '')
	    {
                $errmess = array();
		$x = R::findOne('user', 'login=?', array($login));
		if (!is_object($x))
		{
		    $pw = $context->mustpostpar('password');
		    $rpw = $context->mustpostpar('repeat');
		    $email = $context->mustpostpar('email');
                    $errmess = array();
		    if ($pw != $rpw)
		    {
			$errmess[] = 'The passwords do not match';
		    }
		    if (preg_match('/[^a-z0-9]/i', $login))
		    {
			$errmess[] = 'Your username can only contain letters and numbers';
		    }
		    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		    {
			$errmess[] = 'Please provide a valid email address';
		    }
		    if (empty($errmess))
		    {
			$x = R::dispense('user');
			$x->login = $login;
			$x->email = $email;
			$x->confirm = 0;
                        $x->active = 1;
			$x->joined = $context->utcnow();
			R::store($x);
			$x->setpw($pw);
			$this->sendconfirm($context, $x);
			$context->local()->addval('regok', 'A confirmation link has been sent to your email address.');
		    }
		}
		else
		{
		    $errmess[] = 'That user name is already in use';
		}
                if (!empty($errmess))
                {
                    $context->local()->message('errmessage', $errmess);
                }
	    }
	    return 'register.twig';
	}
/**
 * Handle things to do with email address confirmation
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function confirm($context)
	{
	    if ($context->hasuser())
	    { # logged in, so this stupid....
		$context->divert('/');
	    }
            $local = $context->local();
	    $tpl = 'index.twig';
	    $rest = $context->rest();
	    if ($rest[0] === '' || $rest[0] == 'resend')
	    { # asking for resend
		$lg = $context->postpar('eorl', '');
		if ($lg === '')
		{ # show the form
		    $tpl = 'resend.twig';
		}
		else
		{ # handle the form
		    $user = $this->eorl($lg);
		    if (!is_object($user))
		    {
			$local->message('errmessage', 'Sorry, there is no user with that name or email address.');
		    }
		    elseif ($user->confirm)
		    {
			$local->message('warnmessage', 'Your email address has already been confirmed.');
		    }
		    else
		    {
			$this->sendconfirm($context, $user);
			$local->message('message', 'A new confirmation link has been sent to your email address.');
		    }
		}
	    }
	    else
	    { # confirming the email
		$x = R::findOne('confirm', 'code=? and kind=?', array($rest[0], 'C'));
		if (is_object($x))
		{
		    $interval = (new DateTime($context->utcnow()))->diff(new DateTime($x->issued));
		    if ($interval->days <= 3)
		    {
			$x->user->doconfirm();
			R::trash($x);
			$local->message('message', 'Thank you for confirming your email address. You can now login.');
		    }
		    else
		    {
			$local->message('errmessage', 'Sorry, that code has expired!');
		    }
		}
	    }
	    return $tpl;
	}
/**
 * Handle things to do with password reset
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function forgot($context)
	{
	    if ($context->hasuser())
	    { # logged in, so this stupid....
		$context->divert('/');
	    }
            $local = $context->local();
	    $tpl = 'index.twig';
	    $rest = $context->rest();
	    if ($rest[0] === '')
	    {
		$lg = $context->postpar('eorl', '');
		$tpl = 'reset.twig';
		if ($lg != '')
		{
		    $user = $this->eorl($lg);
		    if (is_object($user))
		    {
			$this->sendreset($context, $user);
			$local->message('message', 'A password reset link has been sent to your email address.');
			$tpl = 'index.twig';
		    }
		    else
		    {
			$local->message('errmessage', 'Sorry, there is no user with that name or email address.');
			$tpl = 'reset.twig';
		    }
		}
	    }
	    elseif ($rest[0] === 'reset')
	    {
		$tpl = 'pwreset.twig';
		$user = $context->load('user', $context->mustpostpar('uid'));
		$code = $context->mustpostpar('code');
		$xc = R::findOne('confirm', 'code=? and kind=?', array($code, 'P'));
		if (is_object($xc) && $xc->user_id == $user->getID())
		{
		    $interval = (new DateTime($context->utcnow()))->diff(new DateTime($xc->issued));
		    if ($interval->days <= 1)
		    {
			$pw = $context->mustpostpar('password');
			if ($pw === $context->mustpostpar('repeat'))
			{
			    $xc->user->setpw($pw);
			    R::trash($xc);
			    $local->message('message', 'You have reset your password. You can now login.');
			    $tpl = 'index.twig';
			}
			else
			{
			    $local->message('errmessage', 'Sorry, the passwords do not match!');
			}
		    }
		    else
		    {
			$local->message('errmessage', 'Sorry, that code has expired!');
		    }

		}
		else
		{
		    $context->divert('/');
		}
	    }
	    else
	    {
		$x = R::findOne('confirm', 'code=? and kind=?', array($rest[0], 'P'));
		if (is_object($x))
		{
		    $interval = (new DateTime($context->utcnow()))->diff(new DateTime($x->issued));
		    if ($interval->days <= 1)
		    {
			$local->addval('pwuser', $x->user);
			$local->addval('code', $x->code);
			$tpl = 'pwreset.twig';
		    }
		    else
		    {
			$local->message('errmessage', 'Sorry, that code has expired!');
		    }
		}
	    }
	    return $tpl;
	}
/**
 * Handle /login /logout /register /forgot /confirm
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    $action = $context->action();
	    return $this->$action($context);
	}
    }
?>