<?php

namespace DevLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
/**
 * @Route("/blog")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homeblog")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $networks = $em->getRepository("PortfolioBundle:SocialNetwork")
            ->findAll();

        $articles = $em->getRepository("DevLogBundle:Article")
            ->findAll();

        $filenamephoto = "photo".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\photo');

        return $this->render('devlog/base.html.twig', [
            "networks" => $networks,
            "articles" => $articles,
            "photo" => $filenamephoto,
        ]);
    }



    private function getFileExtension($filename)
    {
        $files = glob($filename.".*");

        return isset($files[0]) ? ".".pathinfo($files[0])["extension"] : false;
    }
}
