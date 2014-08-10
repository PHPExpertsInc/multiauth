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
class LoginLogic_FacebookConnect implements API_LoginLogic
{
	// FB devs really know how to name classes, huh?
	/** @var Facebook */
	protected $fbClient;

	/** @var FB_User */
	protected $fbUser;

	/**
	 * @param string $serviceURL
	 * @param Facebook $fbClient
	 */
	public function __construct($serviceURL, Facebook $fbClient = null)
	{
		// Facebook doesn't have the concept of a "serviceURL".

		if ($fbClient == null)
		{
			// This is my own form of Dependency Injection. I find it offers almost all of the
			// advantages of DI w/o forcing end-devs from having to explicitly include all the
			// classes dependencies.  It also allows the primary API dev more de facto control
			// without having to resort to Factories.
			$fbAppData = new fbAppData;
			$fbClient = new Facebook(array(
			    'appId'  => $fbAppData->appID,
			    'secret' => $fbAppData->secret,
			));
		}

		$this->fbClient = $fbClient;
	}

	protected function sendToFacebookToLogin()
	{
		header("Location: " . $this->fbClient->getLoginUrl());
		exit;
	}

	public function authenticate()
	{
		$this->fbUser = $this->fbClient->getUser();
		if (!$this->fbUser)
		{
			$this->sendToFacebookToLogin();
		}
	}

	/**
	 * @return bool Whether the user is authenticated or not.
	 */
	public function validate()
	{
		$this->fbUser = $this->fbClient->getUser();
		if ($this->fbUser)
		{
			try
			{
				$user_profile = $this->fbClient->api('/me');
				//print_r($user_profile);
				return true;
				// Here : API call succeeded, you have a valid access token
			}
			catch (FacebookApiException $e)
			{
				// Here : API call failed, you don't have a valid access token
				// you have to send him to $facebook->getLoginUrl()
				echo "User is not logged in. An error occured: " . $e->getMessage();
				$this->fbUser = null;
				return false;
			}
		}
		else
		{
			// Redirect to FB to login.
			$this->sendToFacebookToLogin();
			return false;
		}
	}

	/**
	 * @return FB_User
	 */
	public function getUserIdentity()
	{
		return $this->fbUser;
	}
}

