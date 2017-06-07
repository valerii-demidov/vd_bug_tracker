<?php

namespace Oro\BugTrackerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Oro\BugTrackerBundle\Entity\Issue;

class IssueAbstractEvent extends Event
{
    /** @var Issue  */
    protected $entity;

    /**
     * IssueAbstractEvent constructor.
     * @param Issue $entity
     */
    public function __construct(Issue $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return Issue
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
