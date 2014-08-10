<?php

// Set config settings.
define('FORM_AUTH_KEY', 'RANDOM STRING ddddd');
define('SESSION_AUTH_KEY', 'RANDOM STRING asdf2');


// Start a session secured against CSRF:
$sessionGuard = new SecureSessionManager();

// Create the auth token.
$authSecrets = $sessionGuard->createAuthSecrets(1;

?>
<form method="post">
	<input type="hidden" name="service" value="Google"/>
	<input type="hidden" name="<?php echo FORM_AUTH_KEY; ?>" value="<?php echo $authSecrets[0]; ?>"/>
	<input type="submit" name="login" value="Log in via Google"/>
</form>

