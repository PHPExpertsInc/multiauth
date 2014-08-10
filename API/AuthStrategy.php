<?php
/**
* PHP University Learning Lesson - 3rd Party Authentication Systems
*   Copyright © 2012 PHP Experts, Inc.
*   Author: Theodore R. Smith <theodore@phpexperts.pro>
*           http://users.phpexperts.pro/tsmith/
*/

interface API_AuthStrategy
{
	/**
	 * @abstract
	 */
	public function login();

	/**
	 * @abstract
	 * @return bool Whether the user is authenticated or not.
	 */
	public function isAuthenticated();

	/**
	 * @abstract
	 * @return string Returns the stage of the authentication process.
	 */
	public function getAuthStage();
}

