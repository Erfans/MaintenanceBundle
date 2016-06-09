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
        return $this->render(
            'ErfansMaintenanceBundle:Maintenance:maintenance.html.twig',
            [
                "description" => $this->getParameter("erfans.maintenance.parameters.view.description"),
                "title" => $this->getParameter("erfans.maintenance.parameters.view.title"),
            ]
        );
    }

}
