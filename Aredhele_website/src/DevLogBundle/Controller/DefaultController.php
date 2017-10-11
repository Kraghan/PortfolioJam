<?php

namespace DevLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\BrowserKit\Request;

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

        return $this->render('devlog/list.html.twig', [
            "networks" => $networks,
            "articles" => $articles,
            "photo" => $filenamephoto,
        ]);
    }

    /**
     * @Route("/post/{id}", name="post", requirements={"id": "\d+"})
     */
    public function postAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $networks = $em->getRepository("PortfolioBundle:SocialNetwork")
            ->findAll();

        $articles = $em->getRepository("DevLogBundle:Article")
            ->findAll();

        $article = $em->getRepository("DevLogBundle:Article")
            ->findOneBy(array("id" => $id));

        $blocks = $em->getRepository("DevLogBundle:ArticleBlock")
            ->findBy(array("articleId" => $id), array("ordering" => "ASC"));

        $filenamephoto = "photo".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\photo');

        $contentAuthor = file_get_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt');

        return $this->render('devlog/detail.html.twig', [
            "networks" => $networks,
            "mainArticle" => $article,
            "blocks" => $blocks,
            "photo" => $filenamephoto,
            "contentAuthor" => $contentAuthor,
            "articles" => $articles
        ]);
    }



    private function getFileExtension($filename)
    {
        $files = glob($filename.".*");

        return isset($files[0]) ? ".".pathinfo($files[0])["extension"] : false;
    }
}
