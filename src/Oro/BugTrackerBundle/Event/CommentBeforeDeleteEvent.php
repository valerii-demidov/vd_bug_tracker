<?php

namespace Oro\BugTrackerBundle\Event;

use Oro\BugTrackerBundle\Event\CommentAbstractEvent;

class CommentBeforeDeleteEvent extends CommentAbstractEvent
{
    const EVENT_NAME = 'oro_bugtracker.comment.before_delete';
}
