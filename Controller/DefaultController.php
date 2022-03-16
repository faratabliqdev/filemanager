<?php

namespace Adsign\FileManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/manager-home, name=""file_manager_home")
     */
    public function indexAction()
    {
        return $this->render('@AdsignFileManager/Default/index.html.twig');
    }
}
