<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Security\Channel;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package FLOW3
 * @subpackage Security
 * @version $Id$
 */

/**
 * This security interceptor switches the current channel between HTTP and HTTPS protocol.
 *
 * @package FLOW3
 * @subpackage Security
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class HTTPSInterceptor implements \F3\FLOW3\Security\Authorization\InterceptorInterface {

	/**
	 * @var boolean If set to TRUE, the HTTPS protocol will be einforced.
	 * @todo this has to be set by configuration
	 */
	protected $useSSL = FALSE;

	/**
	 * Constructor.
	 *
	 * @param \F3\FLOW3\Security\Context $securityContext The current security context
	 * @param \F3\FLOW3\Security\Authentication\ManagerInterface $authenticationManager The authentication Manager
	 * @param \F3\FLOW3\Log\LoggerInterface $logger A logger to log security relevant actions
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __construct(
					\F3\FLOW3\Security\Context $securityContext,
					\F3\FLOW3\Security\Authentication\ManagerInterface $authenticationManager,
					\F3\FLOW3\Log\LoggerInterface $logger
					) {

	}

	/**
	 * Redirects the current request to HTTP or HTTPS depending on $this->useSSL;
	 *
	 * @return boolean TRUE if the security checks was passed
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function invoke() {

	}
}

?>