<?php

namespace Erfans\MaintenanceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MaintenanceController extends Controller
{
    /**
     * @Route("/maintenance", name="erfans_maintenance_maintenance")
     */
    public function maintenanceAction()
    {

        $maintenanceMode = $this->getParameter("erfans.maintenance.parameters.maintenance_mode");
        $redirectOnNormal = $this->getParameter("erfans.maintenance.parameters.redirect_on_normal");
        $dueDateTimestamp = $this->getParameter("erfans.maintenance.parameters.due_date");
        $now = new \DateTime('now');

        if ($redirectOnNormal["available"] && (!$maintenanceMode ||
                ($dueDateTimestamp == null || $now->getTimestamp() > $dueDateTimestamp))
        ) {
            if (isset($redirectOnNormal["redirect_route"])) {
                return $this->redirectToRoute($redirectOnNormal["redirect_route"]);
            }

            return $this->redirect($redirectOnNormal["redirect_url"]);
        }

        $view = $this->getParameter("erfans.maintenance.parameters.view");

        return $this->render(
            'ErfansMaintenanceBundle:Maintenance:maintenance.html.twig',
            [
                "description" => $view ["description"],
                "title" => $view ["title"],
            ]
        );
    }

}
