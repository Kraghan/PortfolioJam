<?php

namespace PortfolioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Skills
 *
 * @ORM\Table(name="skills")
 * @ORM\Entity(repositoryClass="PortfolioBundle\Repository\SkillsRepository")
 */
class Skills
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="skillProgress", type="integer")
     */
    private $skillProgress;

    /**
     * @var string
     *
     * @ORM\Column(name="picto", type="string", length=255)
     */
    private $picto;


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
     * @return Skills
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
     * Set skillProgress
     *
     * @param integer $skillProgress
     *
     * @return Skills
     */
    public function setSkillProgress($skillProgress)
    {
        $this->skillProgress = $skillProgress;

        return $this;
    }

    /**
     * Get skillProgress
     *
     * @return int
     */
    public function getSkillProgress()
    {
        return $this->skillProgress;
    }

    /**
     * Set picto
     *
     * @param string $picto
     *
     * @return Skills
     */
    public function setPicto($picto)
    {
        $this->picto = $picto;

        return $this;
    }

    /**
     * Get picto
     *
     * @return string
     */
    public function getPicto()
    {
        return $this->picto;
    }
}

