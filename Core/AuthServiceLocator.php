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

class AuthServiceLocator
{
	/**
	 * @param $service
	 * @param array $serviceParams
	 * @return Model_AuthService
	 * @throws LogicException
	 */
	public function locate($service, array $serviceParams = null)
	{
		// TODO: Get this data from some sort of data store (DB, XML, etc.).
		$authService = new Model_AuthService;
		switch ($service)
		{
			case "OpenID":
				if (empty($serviceParams) || empty($serviceParams['openID_url']))
				{
					throw new LogicException("For custom OpenID auth, the OpenID auth URL must be provided.");
				}
				$authService->authType = 'OpenID';
				$authService->authURL = $serviceParams['openID_url'];
				break;
			case "Google":
				$authService->authType = 'OpenID';
				$authService->authURL = 'https://www.google.com/accounts/o8/id';
				break;
			case "Yahoo":
				$authService->authType = 'OpenID';
				$authService->authURL = 'https://me.yahoo.com/';
				break;
			case 'Facebook':
				$authService->authType = 'FacebookConnect';
				$authService->authURL = 'blah';
				break;

			default:
				throw new LogicException("No details known for the auth service $service");
		}

		return $authService;
	}
}

