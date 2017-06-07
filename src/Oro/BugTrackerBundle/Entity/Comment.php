<?php

namespace Oro\BugTrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comment
 *
 * @ORM\Table(name="bugtracker_comment")
 * @ORM\Entity(repositoryClass="Oro\BugTrackerBundle\Repository\CommentRepository")
 */
class Comment
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
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="comments")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="Issue", inversedBy="comments")
     * @ORM\JoinColumn(name="issue_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $issue;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="comments")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", nullable=true)
     */
    private $body;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;


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
     * Set body
     *
     * @param string $body
     * @return Comment
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Comment
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set customer
     *
     * @param \Oro\BugTrackerBundle\Entity\Customer $customer
     * @return Comment
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
     * Set project
     *
     * @param \Oro\BugTrackerBundle\Entity\Project $project
     * @return Comment
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
     * Set issue
     *
     * @param \Oro\BugTrackerBundle\Entity\Issue $issue
     * @return Comment
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

        return null;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $properties = array_keys(get_class_vars(Comment::class));
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
