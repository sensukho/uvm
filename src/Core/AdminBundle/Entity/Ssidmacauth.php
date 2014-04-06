<?php

namespace Core\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ssidmacauth
 */
class Ssidmacauth
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $macaddress;

    /**
     * @var string
     */
    private $ssid;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Ssidmacauth
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set macaddress
     *
     * @param string $macaddress
     * @return Ssidmacauth
     */
    public function setMacaddress($macaddress)
    {
        $this->macaddress = $macaddress;
    
        return $this;
    }

    /**
     * Get macaddress
     *
     * @return string 
     */
    public function getMacaddress()
    {
        return $this->macaddress;
    }

    /**
     * Set ssid
     *
     * @param string $ssid
     * @return Ssidmacauth
     */
    public function setSsid($ssid)
    {
        $this->ssid = $ssid;
    
        return $this;
    }

    /**
     * Get ssid
     *
     * @return string 
     */
    public function getSsid()
    {
        return $this->ssid;
    }
}
