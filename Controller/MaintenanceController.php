<?php

namespace Erfans\MaintenanceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceController extends AbstractController{

    /**
     * @var string $params
     */
    private $title;

    /**
     * @var string $description
     */
    private $description;

    /**
     * MaintenanceController constructor.
     *
     * @param string $title
     * @param string $description
     */
    public function __construct($title, $description) {
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * @Route(path="/maintenance", name="erfans_maintenance_maintenance")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function maintenance() {

        return $this->render(
            'ErfansMaintenanceBundle:Maintenance:maintenance.html.twig',
            [
                "title"       => $this->title,
                "description" => $this->description,
            ]
        );
    }
}
