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

// Uses the Strategy pattern.
class MultiAuthStrategy implements API_AuthStrategy
{
	/** @var AuthServiceLocator */
	protected $serviceLocator;

	/** @var API_LoginLogic */
	protected $authenticator;

	/**
	 * @param string $service
	 * @param array $serviceParams
	 * @param AuthServiceLocator $sl
	 * @param API_LoginLogic $authenticator
	 */
	public function __construct($service, array $serviceParams = null, AuthServiceLocator $sl = null, API_LoginLogic $authenticator = null)
	{
		if ($sl === null) { $sl = new AuthServiceLocator(); }
		$this->serviceLocator = $sl;

		if ($authenticator === null)
		{
			$authenticator = $this->loadServiceAuthenticator($service, $serviceParams);
		}

		$this->authenticator = $authenticator;
	}

	/**
	 * @param $service
	 * @param array $serviceParams
	 * @return mixed
	 * @throws LogicException
	 */
	protected function loadServiceAuthenticator($service, array $serviceParams = null)
	{
		$serviceDetails = $this->getServiceDetails($service, $serviceParams);
		$serviceType = $serviceDetails->authType;

		$serviceClassName = "LoginLogic_$serviceType";
		if (!class_exists($serviceClassName))
		{
			throw new LogicException("No login implementation for $serviceType.");
		}

		$authenticator = new $serviceClassName($serviceDetails->authURL);

		return $authenticator;
	}

	/**
	 * @param $service
	 * @param array $searchParams
	 * @return Model_AuthService
	 */
	protected function getServiceDetails($service, array $searchParams = null)
	{
		return $this->serviceLocator->locate($service, $searchParams);
	}

	public function login()
	{
		$_SESSION['authStage'] = AuthStages::AUTH_STAGE_VERIFY;
		$status = $this->authenticator->authenticate();
	}

	/**
	 * @return bool Whether the user is authenticated or not.
	 */
	public function isAuthenticated()
	{
		$isLoggedIn = $this->authenticator->validate();
		$_SESSION['authStage'] = ($isLoggedIn == true) ? AuthStages::AUTH_STAGE_AUTHENTICATED : AuthStages::AUTH_STAGE_AUTH_FAILED;

		return $isLoggedIn;
	}

	/**
	 * @return string Returns the stage of the authentication process.
	 */
	public function getAuthStage()
	{
		return $_SESSION['authStage'];
	}

	public function getUserIdentity()
	{
		return $this->authenticator->getUserIdentity();
	}
}
