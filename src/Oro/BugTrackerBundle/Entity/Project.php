<?php

namespace Oro\BugTrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Project
 *
 * @ORM\Table(name="bugtracker_project")
 * @ORM\Entity(repositoryClass="Oro\BugTrackerBundle\Repository\ProjectRepository")
 */
class Project
{

    /**
     * Many Customers have Many Projects.
     * @ORM\ManyToMany(targetEntity="Customer", inversedBy="projects")
     * @ORM\JoinTable(name="bugtracker_projects_customer")
     */
    private $customers;

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
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=255, nullable=true)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

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
     * Set label
     *
     * @param string $label
     * @return Project
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return Project
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Project
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->customers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add customers
     *
     * @param \Oro\BugTrackerBundle\Entity\Customer $customer
     * @return Project
     */
    public function addCustomer(\Oro\BugTrackerBundle\Entity\Customer $customer)
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
        }

        return $this;
    }

    /**
     * Remove customer
     *
     * @param \Oro\BugTrackerBundle\Entity\Customer $customer
     */
    public function removeCustomer(\Oro\BugTrackerBundle\Entity\Customer $customer)
    {
        $this->customers->removeElement($customer);
    }

    /**
     * Get customers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * Return any of exist property
     *
     * @param $key
     * @return mixed
     */
    public function getData($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $properties = array_keys(get_class_vars(Project::class));
        $data = [];
        foreach ($properties as $property) {
            if (is_object($this->$property)) {
                if (method_exists($this->$property, '__toString')) {
                    $data[$property] = $this->$property->__toString();
                }
                continue;
            }

            $data[$property] = $this->$property;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
