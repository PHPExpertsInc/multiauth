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

/**
 * 	A helper class to make End-Devs' lives easier.
 */
class UserAuthenticator
{
	protected $isAuthenticated = false;

	/** @var API_AuthStrategy */
	protected $authStrategy;

	// TODO: I think serviceDetails might need to be a model class.
	/**
	 * @param string $service
	 * @param array $serviceParams
	 * @param API_AuthStrategy $authStrategy
	 */
	public function __construct($service = null, array $serviceParams = null, API_AuthStrategy $authStrategy = null)
	{
		// It simply doesn't make sense for our auth class to ever work without a session, anyway.
		// So let's create one in the __construct().
		$this->startSessionIfNeeded();

		$serviceDetails = $this->assignService($service, $serviceParams);
		if (empty($serviceDetails['service']))
		{
			$_SESSION['authStage'] = AuthStages::AUTH_STAGE_NOT_STARTED;

			return $this;
		}

		if ($_SESSION['authStage'] == AuthStages::AUTH_STAGE_NOT_STARTED)
		{
			$_SESSION['authStage'] = AuthStages::AUTH_STAGE_REDIRECT;
		}

		// My own brand of "soft" Dependency Injection. All of the pros, few of the cons.
		if ($authStrategy === null)
		{
			$authStrategy = new MultiAuthStrategy($serviceDetails['service'], $serviceDetails['params']);
		}
		$this->authStrategy = $authStrategy;
	}

	private function assignService($service, $serviceParrams)
	{
		if ($service !== null)
		{
			$_SESSION['service'] = $service;
			$_SESSION['serviceParams'] = $serviceParrams;
		}
		else
		{
			if (empty($_SESSION['service']))
			{
				$service = null;
			}
			else
			{
				$service = $_SESSION['service'];
				$serviceParrams = $_SESSION['serviceParams'];
			}
		}

		return array('service' => $service,
		             'params' => $serviceParrams);
	}

	// If we want to abstract out the session stuff, we need to make sure one doesn't already exist...
	// PROTIP: Longer method names that form English verb clauses greatly increase the readability
	//         of your code.
	protected function startSessionIfNeeded()
	{
		if (!session_id())
		{
			session_start();
		}

		if (empty($_SESSION['authStage']) || $_SESSION['authStage'] == AuthStages::AUTH_STAGE_AUTH_FAILED)
		{
			$_SESSION['authStage'] = AuthStages::AUTH_STAGE_REDIRECT;
		}
	}

	/**
	 * @return bool|null
	 * @throws LogicException
	 * @throws AuthenticationException
	 */
	public function attemptToLogin()
	{
		if ($this->authStrategy === null && $_SESSION['authStage'] === AuthStages::AUTH_STAGE_NOT_STARTED)
		{
			return null;
		}
		try
		{
			$authStage = $this->authStrategy->getAuthStage();
			if ($authStage == AuthStages::AUTH_STAGE_REDIRECT)
			{
				// 1. Attempt to login.
				$this->authStrategy->login();
			}
			else if ($authStage == AuthStages::AUTH_STAGE_VERIFY)
			{
				// 2. See if the user has been authenticated.
				$this->authStrategy->isAuthenticated();
			}

			// Recheck after verification.
			$authStage = $this->authStrategy->getAuthStage();
			if ($authStage == AuthStages::AUTH_STAGE_AUTH_FAILED)
			{
				$this->isAuthenticated = false;
			}
			else if ($authStage == AuthStages::AUTH_STAGE_AUTHENTICATED)
			{
				$this->isAuthenticated = true;
			}
			else
			{
				throw new LogicException("This line really shouldn't be reached, i dont think.");
			}
			if ($this->isAuthenticated === true)
			{
				$identity = $this->authStrategy->getUserIdentity();
				if (!empty($identity))
				{
					$_SESSION['identity'] = $identity;
				}
			}
		}
		catch (Exception $e)
		{
			throw new AuthenticationException('Could not log in: ' . $e->getMessage());
		}

		return $this->isAuthenticated;
	}

	/**
	 * @return bool Whether the user is authenticated or not.
	 */
	public function isUserAuthenticated()
	{
		return $this->isAuthenticated;
	}
}
