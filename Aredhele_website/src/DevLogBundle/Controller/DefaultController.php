<?php

namespace DevLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/devLog")
     */
    public function indexAction()
    {
        return $this->render('DevLogBundle:Default:index.html.twig');
    }
}
