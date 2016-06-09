<?php
/**
 * Created by PhpStorm.
 * User: Erfan
 * Date: 6/5/2016
 * Time: 13:46
 */

namespace Erfans\MaintenanceBundle\EventListener;

use Erfans\MaintenanceBundle\Maintenance\Maintenance;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class MaintenanceListener
{
    /** @var TokenStorage $tokenStorage */
    private $tokenStorage;

    /** @var  AuthorizationChecker */
    private $authorizationChecker;

    /** @var  Router router */
    private $router;

    /** @var Maintenance maintenance */
    private $maintenance;

    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        Router $router,
        Maintenance $maintenance
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->maintenance = $maintenance;
    }

    /**
     * @return mixed|void
     */
    protected function getUser()
    {
        if (!$this->tokenStorage) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
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


    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $currentRoute = $request->attributes->get('_route');
        $currentPath = $request->getPathInfo();

        // check if application is in maintenance mode
        if (!$this->maintenance->isInMaintenanceMode()) {

            if ($currentPath == $this->maintenance->getMaintenanceUrl() && $this->maintenance->hasRedirectOnNormal()) {
                $event->setResponse(new RedirectResponse($this->maintenance->getRedirectUrl()));
            }

            return;
        }

        // check if requested page is maintenance page
        if ($currentPath == $this->maintenance->getMaintenanceUrl()) {
            return;
        }

        // check authorized IPs
        if (in_array($request->getClientIp(), $this->maintenance->getAuthorizedIPs())) {
            return;
        }

        // check authorized roles for users
        foreach ($this->maintenance->getAuthorizedRoles() as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                return;
            }
        }

        // check authorized usernames for users
        $user = $this->getUser();
        if ($user && method_exists($user, "getUsername")) {
            if (in_array($user->getUsername(), $this->maintenance->getAuthorizedUsernames())) {
                return;
            }
        }

        // return if there is no route (like assets)
        if ($currentRoute == null) {
            return;
        }

        // check if current rout is in authorized routes
        if (in_array($currentRoute, $this->maintenance->getAuthorizedRoutes())) {
            return;
        }

        // returns for assetic routes
        if (strpos($currentRoute, 'assetic') !== false) {
            return;
        }

        // check if current path is in authorized paths
        $path = $request->getPathInfo();
        if (in_array($path, $this->maintenance->getAuthorizedPaths())) {
            return;
        }

        // O.W. redirect to maintenance page
        $event->setResponse(new RedirectResponse($this->maintenance->getMaintenanceUrl()));
    }
}