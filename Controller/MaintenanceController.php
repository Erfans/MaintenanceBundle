<?php

namespace Efi\MaintenanceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MaintenanceController extends Controller
{
    /**
     * @Route("/maintenance", name="efi_maintenance_maintenance")
     */
    public function maintenanceAction()
    {

        $maintenanceMode = $this->getParameter("efi.maintenance.parameters.maintenance_mode");
        $redirectOnNormal = $this->getParameter("efi.maintenance.parameters.redirect_on_normal");
        $dueDateTimestamp = $this->getParameter("efi.maintenance.parameters.due_date");
        $now = new \DateTime('now');

        if ($redirectOnNormal["available"] && (!$maintenanceMode ||
                ($dueDateTimestamp == null || $now->getTimestamp() > $dueDateTimestamp))
        ) {
            if (isset($redirectOnNormal["redirect_route"])) {
                return $this->redirectToRoute($redirectOnNormal["redirect_route"]);
            }

            return $this->redirect($redirectOnNormal["redirect_url"]);
        }

        $view = $this->getParameter("efi.maintenance.parameters.view");

        return $this->render(
            'EfiMaintenanceBundle:Maintenance:maintenance.html.twig',
            [
                "description" => $view ["description"],
                "title" => $view ["title"],
            ]
        );
    }

}
