<?php

namespace PortfolioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SocialNetwork
 *
 * @ORM\Table(name="social_network")
 * @ORM\Entity(repositoryClass="PortfolioBundle\Repository\SocialNetworkRepository")
 */
class SocialNetwork
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
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

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
     * @return SocialNetwork
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
     * Set url
     *
     * @param string $url
     *
     * @return SocialNetwork
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set picto
     *
     * @param string $picto
     *
     * @return SocialNetwork
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

