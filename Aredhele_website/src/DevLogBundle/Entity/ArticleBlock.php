<?php

namespace DevLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArticleBlock
 *
 * @ORM\Table(name="article_block")
 * @ORM\Entity(repositoryClass="DevLogBundle\Repository\ArticleBlockRepository")
 */
class ArticleBlock
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
     * @ORM\Column(name="ordering", type="integer")
     */
    private $ordering;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var int
     *
     * @ORM\Column(name="articleId", type="integer")
     */
    private $articleId;


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
     * Get ordering
     *
     * @return int
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * Set ordering
     *
     * @param int $ordering
     *
     * @return ArticleBlock
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
        return $this;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ArticleBlock
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return ArticleBlock
     */
    public function setContent($content)
    {
        $this->content = json_encode($content);

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return json_decode($this->content,false);
    }

    /**
     * Set articleId
     *
     * @param integer $articleId
     *
     * @return ArticleBlock
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
}

