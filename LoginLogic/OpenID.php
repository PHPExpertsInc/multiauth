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

// Uses the Composite Pattern.
class LoginLogic_OpenID implements API_LoginLogic
{
	/** @var LightOpenID */
	protected $openID;

	protected $serviceURL;

	/**
	 * @param string $serviceURL
	 * @param LightOpenID $openID
	 */
	public function __construct($serviceURL, LightOpenID $openID = null)
	{
		if ($openID == null)
		{
			$openID = new LightOpenID(ORIGINATING_DOMAIN);
		}

		$this->serviceURL = $serviceURL;
		$this->openID = $openID;
	}

	public function authenticate()
	{
		//throw new DebugException("This is a test");
		$this->openID->identity = $this->serviceURL;
		header('Location: ' . $this->openID->authUrl());
		exit;
	}

	/**
	 * @return bool Whether the user is authenticated or not.
	 */
	public function validate()
	{
		return $this->openID->validate();
	}

	/**
	 * @return mixed
	 */
	public function getUserIdentity()
	{
		return $this->openID->identity;
	}
}
