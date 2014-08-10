<?php
/**
* PHP University Learning Lesson - 3rd Party Authentication Systems
*   Copyright © 2012 PHP Experts, Inc.
*   Author: Theodore R. Smith <theodore@phpexperts.pro>
*           http://users.phpexperts.pro/tsmith/
*/

interface API_LoginLogic
{
	/**
	 * @param string $serviceURL
	 */
	public function __construct($serviceURL);

	public function authenticate();

	/**
	 * @abstract
	 * @return bool Whether the user is authenticated or not.
	 */
	public function validate();

	/**
	 * @abstract
	 * @return mixed
	 */
	public function getUserIdentity();
}
