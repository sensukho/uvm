<?php

namespace Core\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 */
class Users
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $secondname;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $matricula;

    /**
     * @var string
     */
    private $genpass;

    /**
     * @var string
     */
    private $newpass;

    /**
     * @var string
     */
    private $newpasssecond;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $campus;

    /**
     * @var string
     */
    private $tipo;

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
     * Set firstname
     *
     * @param string $firstname
     * @return Users
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    
        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set secondname
     *
     * @param string $secondname
     * @return Users
     */
    public function setSecondname($secondname)
    {
        $this->secondname = $secondname;
    
        return $this;
    }

    /**
     * Get secondname
     *
     * @return string 
     */
    public function getSecondname()
    {
        return $this->secondname;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Users
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
     * Set matricula
     *
     * @param string $matricula
     * @return Users
     */
    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;
    
        return $this;
    }

    /**
     * Get matricula
     *
     * @return string 
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * Set genpass
     *
     * @param string $genpass
     * @return Users
     */
    public function setGenpass($genpass)
    {
        $this->genpass = $genpass;
    
        return $this;
    }

    /**
     * Get genpass
     *
     * @return string 
     */
    public function getGenpass()
    {
        return $this->genpass;
    }

    /**
     * Set newpass
     *
     * @param string $newpass
     * @return Users
     */
    public function setNewpass($newpass)
    {
        $this->newpass = $newpass;
    
        return $this;
    }

    /**
     * Get newpass
     *
     * @return string 
     */
    public function getNewpass()
    {
        return $this->newpass;
    }

    /**
     * Set newpasssecondf
     *
     * @param string $newpasssecond
     * @return Users
     */
    public function setNewpasssecond($newpasssecond)
    {
        $this->newpasssecond = $newpasssecond;
    
        return $this;
    }

    /**
     * Get newpasssecond
     *
     * @return string 
     */
    public function getNewpasssecond()
    {
        return $this->newpasssecond;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Users
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Users
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set campus
     *
     * @param string $campus
     * @return Users
     */
    public function setCampus($campus)
    {
        $this->campus = $campus;
    
        return $this;
    }

    /**
     * Get campus
     *
     * @return string 
     */
    public function getCampus()
    {
        return $this->campus;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return Users
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    
        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set ssid
     *
     * @param string $ssid
     * @return Users
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
