<?php

namespace Oro\BugTrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Activity
 *
 * @ORM\Table(name="bugtracker_activity")
 * @ORM\Entity(repositoryClass="Oro\BugTrackerBundle\Repository\ActivityRepository")
 */
class Activity
{
    const TYPE_CREATED = 'created';
    const TYPE_UPDATED = 'updated';
    const TYPE_DELETED = 'deleted';

    /**
     * Many Activity has One Project
     * @ORM\ManyToOne(targetEntity="Project")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="Issue")
     */
    private $issue;

    /**
     * Many Activity has One Customer
     * @ORM\ManyToOne(targetEntity="Customer")
     */
    private $customer;

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
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="entity", type="string", length=255)
     */
    private $entity;

    /**
     * @var string
     *
     * @ORM\Column(name="diff_data", type="text")
     */
    private $diff_data;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
     * Set entity
     *
     * @param string $entity
     * @return Activity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string 
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set diffData
     *
     * @param array $diffData
     * @return Activity
     */
    public function setDiffData($diffData)
    {
        $this->diff_data = serialize($diffData);

        return $this;
    }

    /**
     * Get diffData
     *
     * @return string 
     */
    public function getDiffData()
    {
        return unserialize($this->diff_data);
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Activity
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set project
     *
     * @param \Oro\BugTrackerBundle\Entity\Project $project
     * @return Activity
     */
    public function setProject(\Oro\BugTrackerBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \Oro\BugTrackerBundle\Entity\Project 
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Activity
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
     * Set customer
     *
     * @param \Oro\BugTrackerBundle\Entity\Customer $customer
     * @return Activity
     */
    public function setCustomer(\Oro\BugTrackerBundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \Oro\BugTrackerBundle\Entity\Customer 
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set issue
     *
     * @param \Oro\BugTrackerBundle\Entity\Issue $issue
     * @return Activity
     */
    public function setIssue(\Oro\BugTrackerBundle\Entity\Issue $issue = null)
    {
        $this->issue = $issue;

        return $this;
    }

    /**
     * Get issue
     *
     * @return \Oro\BugTrackerBundle\Entity\Issue 
     */
    public function getIssue()
    {
        return $this->issue;
    }
}
