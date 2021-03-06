<?php

namespace Erfans\MaintenanceBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class MaintenanceListener {

    /** @var TokenStorage $tokenStorage */
    private $tokenStorage;

    /** @var  AuthorizationChecker */
    private $authorizationChecker;

    /** @var  Router $router */
    private $router;

    /** @var string $currentEnv */
    private $currentEnv;

    /** @var array $configuration */
    private $configuration;

    /**
     * MaintenanceListener constructor.
     *
     * @param TokenStorage         $tokenStorage
     * @param AuthorizationChecker $authorizationChecker
     * @param Router               $router
     * @param string               $currentEnv
     * @param array                $configuration
     */
    public function __construct(
        $tokenStorage,
        $authorizationChecker,
        $router,
        $currentEnv,
        array $configuration
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->currentEnv = $currentEnv;
        $this->configuration = $configuration;
    }

    /**
     * Check maintenance mode config and due date to check current state
     *
     * @return bool
     * @throws \Exception
     */
    public function isInMaintenanceMode() {
        if (!$this->configuration["enabled"]) {
            return false;
        }

        $dueDateTimestamp = $this->configuration["due_date"];
        $now = new \DateTime('now');
        if ($dueDateTimestamp != null && $now->getTimestamp() > $dueDateTimestamp) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed|void
     */
    protected function getUser() {
        if (!$this->tokenStorage) {
            return;
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }

    /**
     * Get maintenance path
     *
     * @return string
     */
    private function getMaintenanceUri() {
        return isset($this->configuration["maintenance_url"]) ? $this->configuration["maintenance_url"] :
            $this->router->generate($this->configuration["maintenance_route"]);
    }

    /**
     * @param $rule
     *
     * @return bool
     */
    private function checkUser($rule) {
        $user = $this->getUser();

        if (!empty($rule["usernames"])) {

            if (!$user) {
                return false;
            }

            $username = is_string($user) ? $user : $user->getUsername();

            if (!in_array($username, $rule["usernames"])) {
                return false;
            }
        }

        if (!empty($rule["roles"])) {

            if ($this->authorizationChecker) {
                foreach ($rule["roles"] as $role) {
                    if ($this->authorizationChecker->isGranted($role)) {
                        return true;
                    }
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     *
     * @throws \Exception
     */
    public function onKernelRequest(RequestEvent $event) {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $currentRoute = $request->attributes->get('_route');
        $requestUri = $request->getRequestUri();
        $maintenanceUri = $this->getMaintenanceUri();

        // check if application is in maintenance mode
        if (!$this->isInMaintenanceMode()) {

            $redirectOnNormal = $this->configuration["redirect_on_normal"];

            if ($requestUri == $maintenanceUri && $redirectOnNormal["enabled"]) {
                $redirectUrl = isset($redirectOnNormal["redirect_route"]) ?
                    $this->router->generate($redirectOnNormal["redirect_route"]) :
                    $redirectOnNormal["redirect_url"];

                $event->setResponse(new RedirectResponse($redirectUrl));
            }

            return;
        }

        // check if requested page is maintenance page
        if ($requestUri == $maintenanceUri) {
            return;
        }

        // return if there is no route (like assets)
        if ($currentRoute == null) {
            return;
        }

        // returns for assetic routes
        if (strpos($currentRoute, 'assetic') !== false) {
            return;
        }

        // get rules
        $rules = isset($this->configuration["rules"]) ? $this->configuration["rules"] : [];

        $include = false;

        // check for each rules to include or exclude
        foreach ($rules as $rule) {
            if ($include && $rule["rule"] == "+" || !$include && $rule["rule"] == "-") {
                continue;
            }

            $requestMatcher = new RequestMatcher(
                isset($rule["path"]) ? $rule["path"] : null,
                isset($rule["host"]) ? $rule["host"] : null,
                isset($rule["methods"]) ? $rule["methods"] : null,
                isset($rule["ips"]) ? $rule["ips"] : null,
                [],
                isset($rule["schemes"]) ? $rule["schemes"] : null
            );

            if (
                (empty($rule["env"]) || in_array($this->currentEnv, $rule["env"])) &&
                (empty($rule["routes"]) || in_array($currentRoute, $rule["routes"])) &&
                $this->checkUser($rule) &&
                $requestMatcher->matches($request)
            ) {
                $include = $rule["rule"] == "+";
            }
        }

        if ($include) {
            // redirect to maintenance page
            $event->setResponse(new RedirectResponse($maintenanceUri));
        }
    }
}
