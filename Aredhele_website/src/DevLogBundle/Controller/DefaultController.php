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
     * @Route("/{id}", defaults={"id" :""}, name="homeblog")
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $networks = $em->getRepository("PortfolioBundle:SocialNetwork")
            ->findAll();

        if($id != "")
        {
            $cats = $em->getRepository("PortfolioBundle:Categorie")
                ->findAll();
            $catId = "";
            foreach ($cats as $c)
                if($c->getSlug() == $id)
                {
                    $catId = $c->getId();
                    break;
                }

            $mm = $em->getRepository("DevLogBundle:ArticleCategoriesMm")
                ->findBy(array("categoryId" => $catId));

            $ids = array();
            foreach($mm as $m)
                $ids[] = $m->getArticleId();

            $articles = $em->createQuery('SELECT a 
                FROM DevLogBundle:Article a 
                WHERE a.id IN ('.implode(',',$ids).')
                AND a.createdAt <= CURRENT_DATE() 
                AND a.published = 1 
                ORDER BY a.createdAt DESC')
                ->getResult();
        }
        else
            $articles = $em->createQuery('SELECT a FROM DevLogBundle:Article a WHERE a.createdAt <= CURRENT_DATE() AND a.published = 1 ORDER BY a.createdAt DESC')
                ->getResult();

        $filenamephoto = "photo".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\photo');

        return $this->render('devlog/list.html.twig', [
            "networks" => $networks,
            "articles" => $articles,
            "photo" => $filenamephoto,
        ]);
    }

    /**
     * @Route("/post/{id}", name="post")
     */
    public function postAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $networks = $em->getRepository("PortfolioBundle:SocialNetwork")
            ->findAll();

        $articles = $em->createQuery('SELECT a FROM DevLogBundle:Article a WHERE a.createdAt <= CURRENT_DATE() AND a.published = 1 ORDER BY a.createdAt DESC')
            ->getResult();

        foreach ($articles as $a) {
            if ($a->getSlug() == $id) {
                $article = $a;
                break;
            }
        }

        if(!isset($article))
        {
            // Forward 404
        }
        else
            $id = $article->getId();

        $mm = $em->getRepository("DevLogBundle:ArticleCategoriesMm")
            ->findBy(array("articleId" => $id));

        $categories = array();
        foreach($mm as $m)
            $categories[] = $em->getRepository("PortfolioBundle:Categorie")
                ->findOneBy(array("id" => $m->getCategoryId()));

        $ids = array();

        foreach ($categories as $c)
            $ids[] = $c->getId();

        $otherMm = $em->getRepository("DevLogBundle:ArticleCategoriesMm")
            ->findBy(array("categoryId" => $ids));

        $ids = array();
        foreach($otherMm as $other)
        {
            if ($other->getArticleId() != $article->getId())
                $ids[] = $other->getArticleId();
        }

        if(count($ids) != 0)
            $articles = $em->createQuery('SELECT a FROM DevLogBundle:Article a WHERE a.id IN ('.implode(",",$ids).') AND a.createdAt <= CURRENT_DATE() AND a.published = 1 ORDER BY a.createdAt')
                ->setMaxResults(3)
                ->getResult();
        else
            $articles = null;

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
            "articles" => $articles,
            "categories" => $categories
        ]);
    }



    private function getFileExtension($filename)
    {
        $files = glob($filename.".*");

        return isset($files[0]) ? ".".pathinfo($files[0])["extension"] : false;
    }


    private function d($data)
    {
        echo "<pre>"; print_r($data); echo "</pre>";
    }
}
