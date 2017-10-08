<?php

namespace PortfolioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categorie
 *
 * @ORM\Table(name="categorie")
 * @ORM\Entity(repositoryClass="PortfolioBundle\Repository\CategorieRepository")
 */
class Categorie
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="isFilter", type="boolean")
     */
    private $isFilter;


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
     * Set name
     *
     * @param string $name
     *
     * @return Categorie
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isFilter
     *
     * @param boolean $isFilter
     *
     * @return Categorie
     */
    public function setIsFilter($isFilter)
    {
        $this->isFilter = $isFilter;

        return $this;
    }

    /**
     * Get isFilter
     *
     * @return bool
     */
    public function getIsFilter()
    {
        return $this->isFilter;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        $name = $this->name;
        $name = str_replace("+","plus", $name);
        $name = str_replace("-","minus", $name);
        $name = str_replace(" ","_", $name);

        return $name;
    }
}

