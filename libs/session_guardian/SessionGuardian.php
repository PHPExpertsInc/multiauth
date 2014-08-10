<?php
// Session Guardian, a PHPExperts.pro Project.
//    Copyright © 2012 PHP Experts, Inc.
//    Author: Theodore R. Smith <theodore@phpexperts.pro>
//            http://users.phpexperts.pro/tsmith/
//
// This file is dually licensed under the terms of the following licenses:
// * Primary License: OSSAL v1.0 - Open Source Software Alliance License
//   * Key points:
//       5.Redistributions of source code in any non-textual form (i.e.
//          binary or object form, etc.) must not be linked to software that is
//          released with a license that requires disclosure of source code
//          (ex: the GPL).
//       6.Redistributions of source code must be licensed under more than one
//          license and must not have the terms of the OSSAL removed.
//   * See LICENSE.ossal for complete details.
//
// * Secondary License: Creative Commons Attribution License v3.0
//   * Key Points:
//       * You are free:
//           * to copy, distribute, display, and perform the work
//           * to make non-commercial or commercial use of the work in its original form
//       * Under the following conditions:
//           * Attribution. You must give the original author credit. You must retain all
//             Copyright notices and you must include the sentence, "Based upon work from
//             PHPExperts.pro (www.phpexperts.pro).", wherever you list contributors.
//   * See LICENSE.cc_by for complete details.

class SessionGuardian
{
	public function __construct()
	{
		if (!isset($_SESSION)) {
			session_start();
		}

		$this->secureTheSession();
	}

	/**
	 * Guards against CSRF and man-in-the-middle attacks.
	 */
	protected function secureTheSession()
	{
		// See if the session has been started.
		if (!isset($_POST[FORM_AUTH_KEY]))
		{
			return true;
		}

		if (isset($_POST['login']) && isset($_POST[FORM_AUTH_KEY]))
		{
			// See if the secrets match.
			if (isset($_SESSION[SESSION_AUTH_KEY]))
			{
				if (in_array($_POST[FORM_AUTH_KEY], $_SESSION[SESSION_AUTH_KEY]))
				{
					return true;
				}
				else
				{
					print '<pre>s' . print_r($_SESSION, true) . '</pre>';
					session_destroy();
					throw new RuntimeException("You do not have the right authorization to be here");
				}
			}
		}

	}

	public function createAuthSecrets($num)
	{
		$secrets = array();
		for ($a = 0; $a < $num; ++$a)
		{
			$secrets[] = uniqid() . uniqid();
		}

		$_SESSION[SESSION_AUTH_KEY] = $authSecrets;

		return $secrets;
	}
}

