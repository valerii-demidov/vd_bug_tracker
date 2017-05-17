<?php

namespace Oro\BugTrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Issue
 *
 * @ORM\Table(name="bugtracker_issue")
 * @ORM\Entity(repositoryClass="Oro\BugTrackerBundle\Repository\IssueRepository")
 */
class Issue
{
    const TYPE_STORY = 'story';
    const TYPE_TASK = 'task';
    const TYPE_SUBTASK = 'subtask';
    const TYPE_BUG = 'bug';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_REOPEN = 'reopen';

    const RESOLUTION_UNRESOLVED = 'unresolved';
    const RESOLUTION_RESOLVED = 'resolved';

    /**
     * Many Customers have Many Projects.
     * @ORM\ManyToMany(targetEntity="Customer")
     * @ORM\JoinTable(name="bugtracker_issue_collaboration")
     */
    private $collaboration;

    /**
     * One Issue has Many Comments.
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="issue")
     */
    private $comments;

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
     * @ORM\Column(name="summary", type="string", length=255)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="priority", type="string", length=255)
     */
    private $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="resolution", type="string", length=255)
     */
    private $resolution;

    /**
     * One Issue has One Customer
     * @ORM\ManyToOne(targetEntity="Customer")
     */
    private $reporter;

    /**
     * One Issue has One Customer.
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="issues")
     * @ORM\JoinColumn(name="assignee_id", referencedColumnName="id")
     */
    private $assignee;

    /**
     * @var int
     *
     * @ORM\Column(name="parent", type="integer", nullable=true)
     */
    private $parent;

    /**
     * One Issue has One Project.
     * @ORM\ManyToOne(targetEntity="Project")
     */
    private $project;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;


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
     * Set summary
     *
     * @param string $summary
     * @return Issue
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
     * @return Issue
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
     * Set description
     *
     * @param string $description
     * @return Issue
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Issue
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
     * Set resolution
     *
     * @param string $resolution
     * @return Issue
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * Get resolution
     *
     * @return string 
     */
    public function getResolution()
    {
        return $this->resolution;
    }


    /**
     * Set parent
     *
     * @param integer $parent
     * @return Issue
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return integer 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set project
     *
     * @param integer $project
     * @return Issue
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return integer 
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Issue
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
     * Set updated
     *
     * @param \DateTime $updated
     * @return Issue
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set priority
     *
     * @param string $priority
     * @return Issue
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return string 
     */
    public function getPriority()
    {
        return $this->priority;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->projects = new \Doctrine\Common\Collections\ArrayCollection();
        $this->collaboration = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add projects
     *
     * @param \Oro\BugTrackerBundle\Entity\Project $projects
     * @return Issue
     */
    public function addProject(\Oro\BugTrackerBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Oro\BugTrackerBundle\Entity\Project $projects
     */
    public function removeProject(\Oro\BugTrackerBundle\Entity\Project $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set assignee
     *
     * @param \Oro\BugTrackerBundle\Entity\Customer $assignee
     * @return Issue
     */
    public function setAssignee(\Oro\BugTrackerBundle\Entity\Customer $assignee = null)
    {
        $this->assignee = $assignee;

        return $this;
    }

    /**
     * Get assignee
     *
     * @return \Oro\BugTrackerBundle\Entity\Customer 
     */
    public function getAssignee()
    {
        return $this->assignee;
    }

    /**
     * Set reporter
     *
     * @param \Oro\BugTrackerBundle\Entity\Customer $reporter
     * @return Issue
     */
    public function setReporter(\Oro\BugTrackerBundle\Entity\Customer $reporter = null)
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * Get reporter
     *
     * @return \Oro\BugTrackerBundle\Entity\Customer 
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * Add collaboration
     *
     * @param \Oro\BugTrackerBundle\Entity\Customer $collaboration
     * @return Issue
     */
    public function addCollaboration(\Oro\BugTrackerBundle\Entity\Customer $collaboration)
    {
        if (!$this->collaboration->contains($collaboration)) {
            $this->collaboration->add($collaboration);
        }

        return $this;
    }

    /**
     * Remove collaboration
     *
     * @param \Oro\BugTrackerBundle\Entity\Customer $collaboration
     */
    public function removeCollaboration(\Oro\BugTrackerBundle\Entity\Customer $collaboration)
    {
        $this->collaboration->removeElement($collaboration);
    }

    /**
     * Get collaboration
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCollaboration()
    {
        return $this->collaboration;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Issue
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
     * Add comments
     *
     * @param \Oro\BugTrackerBundle\Entity\Comment $comments
     * @return Issue
     */
    public function addComment(\Oro\BugTrackerBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \Oro\BugTrackerBundle\Entity\Comment $comments
     */
    public function removeComment(\Oro\BugTrackerBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $properties = array_keys(get_class_vars(Issue::class));
        $data = [];
        foreach ($properties as $property) {
            if (is_object($this->$property)) {
                if (method_exists($this->$property, '__toString')){
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
