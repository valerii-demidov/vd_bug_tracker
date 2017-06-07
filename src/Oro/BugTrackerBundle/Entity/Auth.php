<?php

namespace Oro\BugTrackerBundle\Entity;

class Auth
{
    /**
     * @var
     */
    protected $username;
    /**
     * @var
     */
    protected $password;

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}
