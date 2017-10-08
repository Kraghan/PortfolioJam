<?php

namespace DevLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArticleKeywordMm
 *
 * @ORM\Table(name="article_keyword_mm")
 * @ORM\Entity(repositoryClass="DevLogBundle\Repository\ArticleKeywordMmRepository")
 */
class ArticleKeywordMm
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="articleId", type="integer")
     */
    private $articleId;

    /**
     * @var int
     *
     * @ORM\Column(name="categorieId", type="integer")
     */
    private $categorieId;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set articleId
     *
     * @param integer $articleId
     *
     * @return ArticleKeywordMm
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;

        return $this;
    }

    /**
     * Get articleId
     *
     * @return int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * Set categorieId
     *
     * @param integer $categorieId
     *
     * @return ArticleKeywordMm
     */
    public function setCategorieId($categorieId)
    {
        $this->categorieId = $categorieId;

        return $this;
    }

    /**
     * Get categorieId
     *
     * @return int
     */
    public function getCategorieId()
    {
        return $this->categorieId;
    }
}

