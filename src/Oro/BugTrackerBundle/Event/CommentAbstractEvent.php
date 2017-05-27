<?php

namespace Oro\BugTrackerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Oro\BugTrackerBundle\Entity\Comment;

class CommentAbstractEvent extends Event
{
    /** @var Customer  */
    protected $entity;

    /**
     * CommentAbstractEvent constructor.
     * @param Comment $entity
     */
    public function __construct(Comment $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return Customer
     */
    public function getEntity()
    {
        return $this->entity;
    }
}