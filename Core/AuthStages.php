<?php
/**
* PHP University Learning Lesson - 3rd Party Authentication Systems
*   Copyright © 2012 PHP Experts, Inc.
*   Author: Theodore R. Smith <theodore@phpexperts.pro>
*           http://users.phpexperts.pro/tsmith/
*/

// TODO: I really want to build this out to handle the _SESSION setting itself.
class AuthStages
{
	const AUTH_STAGE_NOT_STARTED = 'not started';
	const AUTH_STAGE_REDIRECT = 'redirect';
	const AUTH_STAGE_VERIFY = 'verify';
	const AUTH_STAGE_AUTHENTICATED = 'authenticated';
	const AUTH_STAGE_AUTH_FAILED = 'auth failed';
}
