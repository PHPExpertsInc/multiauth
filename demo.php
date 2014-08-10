<?php
/**
* PHP University Learning Lesson - 3rd Party Authentication Systems
*   Copyright © 2012 PHP Experts, Inc.
*   Author: Theodore R. Smith <theodore@phpexperts.pro>
*           http://users.phpexperts.pro/tsmith/
*
* The following code is licensed under a modified BSD License.
* All of the terms and conditions of the BSD License apply with one
* exception:
*
* 1. Every one who is not or has not been a registered student of the
*    PHP University (http://www.phpu.cc/) is expressly forbidden
*    from modifing this code or using in an another project, either as a
*    deritvative work or stand-alone .
*
* BSD License: http://www.opensource.org/licenses/bsd-license.php
**/

include 'MultiAuth.php';

new Thrive_Autoloader(realpath(dirname(__FILE__) . '/../'));

$isLoggedIn = null;
$sessionGuard = new SessionGuardian();

$service = null; $serviceParams = null;
if (isset($_POST['service']))
{
	$service = $_POST['service'];
	if (isset($_POST['serviceParams']))
	{
		$serviceParams = $_POST['serviceParams'];
	}
}

// TODO: It'd be nice to be able to add this back someday.
//echo 'Log in process has not started.';


// TODO: Add support for any OpenID URL.
//$ticketMan = new LoginLogic_OpenID('https://www.google.com/accounts/o8/id');
$ticketMan = new UserAuthenticator($service, $serviceParams);

// 1. Attempt to log in the user.
try
{
	$ticketMan->attemptToLogin();
}
catch (Exception $e)
{
	$errorMessage = $e->getMessage();
}

$isLoggedIn = $ticketMan->isUserAuthenticated();
$loginStatus = ($isLoggedIn == true ? 'is' : 'is not');

// I want MultiAuth to handle as much of the implementation details as possible.
// The big one right now is all the session stuff; right now that's pretty much
// totally all in the front controller. I want to abstract it out.

$authSecrets = $sessionGuard->createAuthSecrets(4);
?>
<html>
	<head>
		<title>Multi-System Login</title>
	</head>
	<body>
		<h1>Multi-System Login</h1>
<?php
if (isset($errorMessage))
{
?>
		<div id="error_box">
			<h2>Error:</h2>
			<p><?php echo $errorMessage; ?></p>
		</div>
<?php
}
?>
<?php
if (isset($loginStatus))
{
?>
		<h2>The user <?php echo $loginStatus ?> logged in<?php echo ($isLoggedIn) ? " via $service" : ''; ?>.</h2>
<?php
}
?>
		<div>
			<form method="post">
				<input type="hidden" name="service" value="Google"/>
				<input type="hidden" name="<?php echo FORM_AUTH_KEY; ?>" value="<?php echo $authSecrets[0]; ?>"/>
				<input type="submit" name="login" value="Log in via Google"/>
			</form>
			<form method="post">
				<input type="hidden" name="service" value="Yahoo"/>
				<input type="hidden" name="<?php echo FORM_AUTH_KEY; ?>" value="<?php echo $authSecrets[1]; ?>"/>
				<input type="submit" name="login" value="Log in via Yahoo"/>
			</form>
			<form method="post">
				<input type="hidden" name="service" value="Facebook"/>
				<input type="hidden" name="<?php echo FORM_AUTH_KEY; ?>" value="<?php echo $authSecrets[2]; ?>"/>
				<input type="submit" name="login" value="Log in via Facebook"/>
			</form>
			<form method="post">
				<input type="hidden" name="service" value="OpenID"/>
				<div>
					OpenID URL: <input type="text" name="serviceParams[openID_url]"/>
				</div>
				<input type="hidden" name="<?php echo FORM_AUTH_KEY; ?>" value="<?php echo $authSecrets[3]; ?>"/>
				<input type="submit" name="login" value="Log in via Facebook"/>
			</form>

		</div>
	</body>
</html>

