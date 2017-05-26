<?php

namespace Oro\BugTrackerBundle\Event;

use Oro\BugTrackerBundle\Event\IssueAbstractEvent;

class IssueBeforeUpdateEvent extends IssueAbstractEvent
{
    const EVENT_NAME = 'oro_bugtracker.issue.before_update';
}