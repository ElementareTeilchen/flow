<?php
namespace Neos\Flow\Security\Aspect;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Log\PsrSecurityLoggerInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Security\Authentication\AuthenticationManagerInterface;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Exception\NoTokensAuthenticatedException;

/**
 * An aspect which centralizes the logging of security relevant actions.
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class LoggingAspect
{
    /**
     * @var PsrSecurityLoggerInterface
     * @Flow\Inject
     */
    protected $securityLogger;

    /**
     * @var boolean
     */
    protected $alreadyLoggedAuthenticateCall = false;

    /**
     * Logs calls and results of the authenticate() method of the Authentication Manager
     *
     * @Flow\After("within(Neos\Flow\Security\Authentication\AuthenticationManagerInterface) && method(.*->authenticate())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     * @return mixed The result of the target method if it has not been intercepted
     * @throws \Exception
     */
    public function logManagerAuthenticate(JoinPointInterface $joinPoint)
    {
        if ($joinPoint->hasException()) {
            $exception = $joinPoint->getException();
            if (!$exception instanceof NoTokensAuthenticatedException) {
                $this->securityLogger->notice(sprintf('Authentication failed: "%s" #%d', $exception->getMessage(), $exception->getCode()));
            }
            throw $exception;
        }

        if ($this->alreadyLoggedAuthenticateCall) {
            return;
        }

        $this->alreadyLoggedAuthenticateCall = true;
        /** @var AuthenticationManagerInterface $authenticationManager */
        $authenticationManager = $joinPoint->getProxy();
        $logMessage = 'No account authenticated';
        if ($authenticationManager->getSecurityContext()->getAccount() !== null) {
            $logMessage = sprintf('Successfully re-authenticated tokens for account "%s"', $authenticationManager->getSecurityContext()->getAccount()->getAccountIdentifier());
        }

        $this->securityLogger->info($logMessage, LogEnvironment::fromMethodName(__METHOD__));
    }

    /**
     * Logs calls and results of the logout() method of the Authentication Manager
     *
     * @Flow\AfterReturning("within(Neos\Flow\Security\Authentication\AuthenticationManagerInterface) && method(.*->logout())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     * @return mixed The result of the target method if it has not been intercepted
     */
    public function logManagerLogout(JoinPointInterface $joinPoint)
    {
        /** @var AuthenticationManagerInterface $authenticationManager */
        $authenticationManager = $joinPoint->getProxy();
        $securityContext = $authenticationManager->getSecurityContext();
        if (!$securityContext->isInitialized()) {
            return;
        }

        $accountIdentifiers = [];
        foreach ($securityContext->getAuthenticationTokens() as $token) {
            $account = $token->getAccount();
            if ($account !== null) {
                $accountIdentifiers[] = $account->getAccountIdentifier();
            }
        }

        $this->securityLogger->info(sprintf('Logged out %d account(s). (%s)', count($accountIdentifiers), implode(', ', $accountIdentifiers)), LogEnvironment::fromMethodName(__METHOD__));
    }

    /**
     * @param array $collectedIdentifiers
     * @param TokenInterface $token
     * @return array
     */
    protected function reduceTokenToAccountIdentifier(array $collectedIdentifiers, TokenInterface $token): array
    {
        $account = $token->getAccount();
        if ($account !== null) {
            $collectedIdentifiers[] = $account->getAccountIdentifier();
        }

        return $collectedIdentifiers;
    }

    /**
     * Logs calls and results of the authenticate() method of an authentication provider
     *
     * @Flow\AfterReturning("within(Neos\Flow\Security\Authentication\AuthenticationProviderInterface) && method(.*->authenticate())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     * @return mixed The result of the target method if it has not been intercepted
     */
    public function logPersistedUsernamePasswordProviderAuthenticate(JoinPointInterface $joinPoint)
    {
        $token = $joinPoint->getMethodArgument('authenticationToken');

        switch ($token->getAuthenticationStatus()) {
            case TokenInterface::AUTHENTICATION_SUCCESSFUL:
                $this->securityLogger->notice(sprintf('Successfully authenticated token: %s', $token), [
                    'packageKey' => 'Neos.Flow',
                    'className' => $joinPoint->getClassName(),
                    'methodName' => $joinPoint->getMethodName()
                ]);
                $this->alreadyLoggedAuthenticateCall = true;
            break;
            case TokenInterface::WRONG_CREDENTIALS:
                $this->securityLogger->warning(sprintf('Wrong credentials given for token: %s', $token), [
                    'packageKey' => 'Neos.Flow',
                    'className' => $joinPoint->getClassName(),
                    'methodName' => $joinPoint->getMethodName()
                ]);
            break;
            case TokenInterface::NO_CREDENTIALS_GIVEN:
                $this->securityLogger->warning(sprintf('No credentials given or no account found for token: %s', $token), [
                    'packageKey' => 'Neos.Flow',
                    'className' => $joinPoint->getClassName(),
                    'methodName' => $joinPoint->getMethodName()
                ]);
            break;
        }
    }

    /**
     * Logs calls and result of vote() for method privileges
     *
     * @Flow\After("method(Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilege->vote())")
     * @param JoinPointInterface $joinPoint
     * @return void
     */
    public function logJoinPointAccessDecisions(JoinPointInterface $joinPoint)
    {
        $subjectJoinPoint = $joinPoint->getMethodArgument('subject');
        $decision = $joinPoint->getResult() === true ? 'GRANTED' : 'DENIED';
        $message = sprintf('Decided "%s" on method call %s::%s().', $decision, $subjectJoinPoint->getClassName(), $subjectJoinPoint->getMethodName());
        $this->securityLogger->info($message, LogEnvironment::fromMethodName(__METHOD__));
    }

    /**
     * Logs calls and result of isPrivilegeTargetGranted()
     *
     * @Flow\After("method(Neos\Flow\Security\Authorization\PrivilegeManager->isPrivilegeTargetGranted())")
     * @param JoinPointInterface $joinPoint
     * @return void
     */
    public function logPrivilegeAccessDecisions(JoinPointInterface $joinPoint)
    {
        $decision = $joinPoint->getResult() === true ? 'GRANTED' : 'DENIED';
        $message = sprintf('Decided "%s" on privilege "%s".', $decision, $joinPoint->getMethodArgument('privilegeTargetIdentifier'));
        $this->securityLogger->info($message, LogEnvironment::fromMethodName(__METHOD__));
    }
}
