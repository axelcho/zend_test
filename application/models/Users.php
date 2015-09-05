<?php
/**
 * Doctrine 2 Entity Class with full getters and setters
 *
 * Author: Seong Cho
 */



/**
 * @Entity(repositoryClass="Application_Model_UsersRepository")
 * @Table(name="users")
 *
 * @HasLifecycleCallbacks
 */
class Application_Model_Users
{
    /**
     * @var integer
     *
     * @Id
     * @Column(name="id", type="integer", nullable=false)
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Column(name="firstname", type="string", nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @Column(name="lastname", type="string", nullable=false)
     */
    private $lastname;

    /**
     * @var string
     *
     * @Column(name="password", type="string", nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @Column(name="email", type="string", nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @Column(name="status", type="string", nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @Column(name="country", type="string", nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @Column(name="city", type="string", nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @Column(name="address", type="string", nullable=true)
     */
    private $address;

    /**
     * @var \DateTime
     *
     * @Column(name="added_date", type="datetime", nullable=false)
     */
    private $addedDate;


    /*
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
     * @return Application_Model_Users
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

    /*
     * Set lastname
     *
     * @param string $lastname
     * @return Applicaiton_Model_Users
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Get string
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastname;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Application_Model_users
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Application_Model_Users
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
     * Set status
     *
     * @param string $staus
     * @return Application_Model_Users
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Application_Model_Users
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Application_Model_Users
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Application_Model_Users
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set added date
     *
     * @param DateTime $addedDate
     * @return $this
     */
    public function setAddedDate(\DateTime $addedDate)
    {
        $this->addedDate = $addedDate;
        return $this;
    }

    /**
     * get added date
     *
     * @return DateTime
     */
    public function getAddedDate()
    {
        return $this->addedDate;
    }

    /**
     * @PrePersist
     * @PreUpdate
     */
    public function defaultTime()
    {
        if ($this->getAddedDate() == null){
            $this->setAddedDate(new \DateTime('now'));
        }
    }

}