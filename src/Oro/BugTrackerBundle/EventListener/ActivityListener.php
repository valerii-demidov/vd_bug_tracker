<?php

namespace Oro\BugTrackerBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Oro\BugTrackerBundle\Entity\Comment;
use Oro\BugTrackerBundle\Entity\Issue;
use Oro\BugTrackerBundle\Event\CommentBeforeCreateEvent;
use Oro\BugTrackerBundle\Event\CommentBeforeUpdateEvent;
use Oro\BugTrackerBundle\Event\CommentBeforeDeleteEvent;
use Oro\BugTrackerBundle\Event\IssueBeforeCreateEvent;
use Oro\BugTrackerBundle\Event\IssueBeforeUpdateEvent;
use Oro\BugTrackerBundle\Entity\Activity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ActivityListener
{
    /** @var  EntityManagerInterface */
    protected $em;

    /** @var TokenStorage  */
    protected $securityToken;

    /**
     * ActivityListener constructor.
     * @param EntityManagerInterface $em
     * @param TokenStorage $securityToken
     */
    public function __construct(EntityManagerInterface $em, TokenStorage $securityToken)
    {
        $this->em = $em;
        $this->securityToken = $securityToken;
    }

    /**
     * @param CommentBeforeCreateEvent $event
     */
    public function addCommentCreateActivity(CommentBeforeCreateEvent $event)
    {
        $comment = $event->getEntity();
        $entityName = 'Comment';

        $activity = new Activity();
        $activity->setIssue($comment->getIssue());
        $activity->setProject($comment->getProject());
        $changes = $comment->__toArray();

        $this->doSave($activity, $entityName, Activity::TYPE_CREATED, $changes);
    }

    /**
     * @param CommentBeforeUpdateEvent $event
     */
    public function addCommentUpdateActivity(CommentBeforeUpdateEvent $event)
    {
        $comment = $event->getEntity();
        $unit = $this->em->getUnitOfWork();
        $unit->computeChangeSet($this->em->getClassMetadata(Comment::class), $comment);
        $changes = $unit->getEntityChangeSet($comment);

        $entityName = 'Comment';

        $activity = new Activity();
        $activity->setIssue($comment->getIssue());
        $activity->setProject($comment->getProject());

        $this->doSave($activity, $entityName, Activity::TYPE_UPDATED, $changes);
    }

    /**
     * @param CommentBeforeDeleteEvent $event
     */
    public function addCommentDeleteActivity(CommentBeforeDeleteEvent $event)
    {
        $comment = $event->getEntity();
        $entityName = 'Comment';

        $activity = new Activity();
        $activity->setIssue($comment->getIssue());
        $activity->setProject($comment->getProject());
        $changes = $comment->__toArray();

        $this->doSave($activity, $entityName, Activity::TYPE_DELETED, $changes);
    }

    /**
     * @param IssueBeforeCreateEvent $event
     */
    public function addIssueCreateActivity(IssueBeforeCreateEvent $event)
    {
        $issue = $event->getEntity();
        $activity = new Activity();
        $activity->setIssue($issue);
        $activity->setProject($issue->getProject());

        $entityName = 'Issue';
        $this->doSave($activity, $entityName, Activity::TYPE_CREATED, $issue->__toArray());
    }

    /**
     * @param IssueBeforeUpdateEvent $event
     */
    public function addIssueUpdateActivity(IssueBeforeUpdateEvent $event)
    {
        $issue = $event->getEntity();
        $unit = $this->em->getUnitOfWork();
        $unit->computeChangeSet($this->em->getClassMetadata(Issue::class), $issue);
        $changes = $unit->getEntityChangeSet($issue);
        foreach ($changes as $field => $change) {
            foreach ($change as $key => $element) {
                if (is_object($element)) {
                    if (method_exists($element, '__toString')) {
                        $changes[$field][$key] = $element->__toString();
                    } else {
                        unset($changes[$field]);
                    }
                }
            }
        }

        $entityName = 'Issue';

        $activity = new Activity();
        $activity->setIssue($issue);
        $activity->setProject($issue->getProject());

        $this->doSave($activity, $entityName, Activity::TYPE_UPDATED, $changes);
    }

    /**
     * @param Activity $activity
     * @param $entityName
     * @param $type
     * @param array $changes
     */
    protected function doSave(Activity $activity, $entityName, $type, $changes = [])
    {
        // author of activity
        $author = $this->securityToken->getToken()->getUser();
        $activity->setCustomer($author);
        $activity->setEntity($entityName);
        $activity->setType($type);

        $currentDate = new \DateTime();
        $fullDiffData['diff_fields'] = array_keys($changes);
        $fullDiffData['changes'] = $changes;
        $activity->setDiffData($fullDiffData);
        $activity->setDate($currentDate);

        $this->em->persist($activity);
        $this->em->flush();
    }
}