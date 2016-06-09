<?php
/**
 * Created by PhpStorm.
 * User: Erfan
 * Date: 6/7/2016
 * Time: 15:15
 */

namespace Erfans\MaintenanceBundle\Maintenance;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

class Maintenance
{
    /** @var Router router */
    private $router;

    /** @var  boolean maintenanceMode */
    private $enabled;

    /** @var  $dueDate */
    private $dueDateTimestamp;

    /** @var  string maintenanceUrl */
    private $maintenanceUrl;

    /** @var  array authorizedUsernames */
    private $authorizedUsernames;

    /** @var  array authorizedRoles */
    private $authorizedRoles;

    /** @var  array authorizedIps */
    private $authorizedIPs;

    /** @var  array authorizedPaths */
    private $authorizedPaths;

    /** @var  array authorizedRoutes */
    private $authorizedRoutes;

    /** @var  boolean $redirectOnNormal */
    private $redirectOnNormal;

    /** @var  string redirectUrl */
    private $redirectUrl;

    public function __construct(Router $router, array $config)
    {
        $this->router = $router;
        $this->enabled = $config["enabled"];
        $this->dueDateTimestamp = $config["due_date"];

        $this->maintenanceUrl = isset($config["maintenance_url"]) ?
            $config["maintenance_url"] :
            $router->generate($config["maintenance_route"]);

        $authorizedUsers = $config["authorized_users"];

        $this->authorizedUsernames = isset($authorizedUsers["usernames"]) ? $authorizedUsers["usernames"] : [];
        $this->authorizedRoles = isset($authorizedUsers["roles"]) ? $authorizedUsers["roles"] : [];
        $this->authorizedIPs = isset($authorizedUsers["ip"]) ? $authorizedUsers["ip"] : [];

        $authorizedAreas = $config["authorized_areas"];

        $this->authorizedPaths = isset($authorizedAreas["paths"]) ? $authorizedAreas["paths"] : [];
        $this->authorizedRoutes = isset($authorizedAreas["routes"]) ? $authorizedAreas["routes"] : [];

        $redirectOnNormal = $config["redirect_on_normal"];

        $this->redirectOnNormal = $redirectOnNormal["enabled"];
        $this->redirectUrl = isset($redirectOnNormal["redirect_route"]) ?
            $router->generate($redirectOnNormal["redirect_route"]) :
            $redirectOnNormal["redirect_url"];
    }

    /**
     * Check maintenance mode config and due date to check current state
     *
     * @return bool
     */
    public function isInMaintenanceMode()
    {
        if (!$this->enabled) {
            return false;
        }

        $now = new \DateTime('now');
        if ($this->dueDateTimestamp != null && $now->getTimestamp() > $this->dueDateTimestamp) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getMaintenanceUrl()
    {
        return $this->maintenanceUrl;
    }

    /**
     * @return array
     */
    public function getAuthorizedRoles()
    {
        return $this->authorizedRoles;
    }

    /**
     * @return array
     */
    public function getAuthorizedUsernames()
    {
        return $this->authorizedUsernames;
    }

    /**
     * @return array
     */
    public function getAuthorizedIPs()
    {
        return $this->authorizedIPs;
    }

    /**
     * @return array
     */
    public function getAuthorizedRoutes()
    {
        return $this->authorizedRoutes;
    }

    /**
     * @return array
     */
    public function getAuthorizedPaths()
    {
        return $this->authorizedPaths;
    }

    /**
     * @return bool
     */
    public function hasRedirectOnNormal()
    {
        return $this->redirectOnNormal;
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }


}