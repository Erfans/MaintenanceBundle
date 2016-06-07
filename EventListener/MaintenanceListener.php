<?php
/**
 * Created by PhpStorm.
 * User: Erfan
 * Date: 6/5/2016
 * Time: 13:46
 */

namespace Erfans\MaintenanceBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
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

    /** @var  boolean maintenanceMode */
    private $maintenanceMode;

    /** @var  $dueDate */
    private $dueDateTimestamp;

    /** @var  array authorizedUsers */
    private $authorizedUsers;

    /** @var  array authorizedAreas */
    private $authorizedAreas;

    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        Router $router,
        $maintenanceMode,
        $timestamp = null,
        array $authorizedUsers,
        array $authorizedAreas
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->maintenanceMode = $maintenanceMode;
        $this->authorizedUsers = $authorizedUsers ?: [];
        $this->authorizedAreas = $authorizedAreas ?: [];
        $this->dueDateTimestamp = $timestamp;
    }

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


    public function onKernelController(FilterControllerEvent $event)
    {

        if (!$this->maintenanceMode) {
            return;
        }

        $now = new \DateTime('now');
        if ($this->dueDateTimestamp != null && $now->getTimestamp() > $this->dueDateTimestamp) {
            return;
        }

        if (!$event->isMasterRequest()) {
            return;
        }

        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        // check authorized roles for users
        if (isset($this->authorizedUsers["roles"])) {
            foreach ($this->authorizedUsers["roles"] as $role) {
                if ($this->authorizationChecker->isGranted($role)) {
                    return;
                }
            }
        }

        // check authorized usernames for users
        $user = $this->getUser();
        if (
            isset($this->authorizedUsers["usernames"]) &&
            $user &&
            method_exists($user, "getUsername")
        ) {
            if (in_array($user->getUsername(), $this->authorizedUsers["usernames"])) {
                return;
            }
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // return if there is no route (like assets)
        if ($route == null) {
            return;
        }

        // add maintenance route to authorized routes
        $maintenanceRoute = ["erfans_maintenance_maintenance"];
        $this->authorizedAreas["routes"] = isset($this->authorizedAreas["routes"]) ?
            array_merge($this->authorizedAreas["routes"], $maintenanceRoute) :
            $maintenanceRoute;

        // check if current rout is in authorized routes
        if (in_array($route, $this->authorizedAreas["routes"])) {
            return;
        }

        // returns for assetic routes
        if (strpos($route, 'assetic') !== false) {
            return;
        }

        // check if current path is in authorized paths
        if (isset($this->authorizedAreas["paths"])) {
            $path = $request->getPathInfo();
            if (in_array($path, $this->authorizedAreas["paths"])) {
                return;
            }
        }

        // O.W. redirect to maintenance page
        $redirectUrl = $this->router->generate("erfans_maintenance_maintenance");
        $event->setController(
            function () use ($redirectUrl) {
                return new RedirectResponse($redirectUrl);
            }
        );
    }
}