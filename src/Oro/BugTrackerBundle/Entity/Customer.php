<?php

namespace Oro\BugTrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="bugtracker_customer")
 * @ORM\Entity(repositoryClass="Oro\BugTrackerBundle\Repository\CustomerRepository")
 */
class Customer implements UserInterface, \Serializable
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_MANAGER = 'ROLE_MANAGER';
    const ROLE_OPERATOR = 'ROLE_OPERATOR';

    /**
     * Many Customers have Many Projects.
     * @ORM\ManyToMany(targetEntity="Project", mappedBy="customers")
     */
    private $projects;

    /**
     * One Customer have Many Issues.
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="assignee")
     */
    private $issues;

    /**
     * One Customer have Many Activities.
     * @ORM\OneToMany(targetEntity="Activity", mappedBy="customer")
     */
    private $activities;

    /**
     * @ORM\Column(type="integer", options={"comment":"Id"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true, options={"comment":"User Name"})
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, unique=true, options={"comment":"Email"})
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100, options={"comment":"Full Name"})
     */
    private $fullName;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array", options={"comment":"Roles"})
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=100, options={"comment":"Password"})
     */
    private $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

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
     * @return Customer
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
     * Set email
     *
     * @param string $email
     * @return Customer
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
     * Set Full Name
     *
     * @param string $fullName
     * @return Customer
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get Full Name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * Set the roles granted to the user.
     *
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Set Password
     *
     * @param string $password
     *
     * @return Customer
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get Password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *  Set Plain Password
     *
     * @param string $plainPassword
     * @return $this
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Get Plain Password
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->id,
                $this->username,
                $this->password,
            )
        );
    }

    /**
     * @param string $serialized
     * @return mixed|void
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            ) = unserialize($serialized);
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
     * Constructor
     */
    public function __construct()
    {
        $this->projects = new \Doctrine\Common\Collections\ArrayCollection();
        $this->issues = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add projects
     *
     * @param \Oro\BugTrackerBundle\Entity\Project $projects
     * @return Customer
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
     * Add issues
     *
     * @param \Oro\BugTrackerBundle\Entity\Issue $issues
     * @return Customer
     */
    public function addIssue(\Oro\BugTrackerBundle\Entity\Issue $issues)
    {
        $this->issues[] = $issues;

        return $this;
    }

    /**
     * Remove issues
     *
     * @param \Oro\BugTrackerBundle\Entity\Issue $issues
     */
    public function removeIssue(\Oro\BugTrackerBundle\Entity\Issue $issues)
    {
        $this->issues->removeElement($issues);
    }

    /**
     * Get issues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $properties = array_keys(get_class_vars(Customer::class));
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

    /**
     * Add activities
     *
     * @param \Oro\BugTrackerBundle\Entity\Activity $activities
     * @return Customer
     */
    public function addActivity(\Oro\BugTrackerBundle\Entity\Activity $activities)
    {
        $this->activities[] = $activities;

        return $this;
    }

    /**
     * Remove activities
     *
     * @param \Oro\BugTrackerBundle\Entity\Activity $activities
     */
    public function removeActivity(\Oro\BugTrackerBundle\Entity\Activity $activities)
    {
        $this->activities->removeElement($activities);
    }

    /**
     * Get activities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivities()
    {
        return $this->activities;
    }
}
