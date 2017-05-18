<?php

namespace Oro\BugTrackerBundle\Form\Handler;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Oro\BugTrackerBundle\Entity\Issue;
use Oro\BugTrackerBundle\Entity\Comment;
use Oro\BugTrackerBundle\Entity\Activity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ActivityHandler
{
    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var TokenStorage */
    protected $securityToken;

    /**
     * CommentHandler constructor.
     * @param RequestStack $request
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        RequestStack $request,
        EntityManagerInterface $manager,
        TokenStorage $tokenStorage
    ) {
        $this->request = $request;
        $this->manager = $manager;
        $this->securityToken = $tokenStorage;
    }

    /**
     * @param Issue $issue
     * @param $type
     * @param array $diffData
     */
    public function handleIssueActivity(Issue $issue, $type, $diffData = [])
    {
        $activity = new Activity();
        $activity->setIssue($issue);
        $activity->setProject($issue->getProject());

        $entityName = 'Issue';
        $this->doSave($activity, $issue, $entityName, $type, $diffData);
    }

    /**
     * @param Comment $comment
     * @param $type
     * @param array $diffData
     */
    public function handleCommentActivity(Comment $comment, $type, $diffData = [])
    {
        $activity = new Activity();
        $activity->setIssue($comment->getIssue());
        $activity->setProject($comment->getProject());

        $entityName = 'Comment';
        $this->doSave($activity, $comment, $entityName, $type, $diffData);
    }

    /**
     * @param Activity $activity
     * @param $entity
     * @param $entityName
     * @param $type
     * @param array $diffData
     */
    protected function doSave(Activity $activity, $entity, $entityName, $type, $diffData = [])
    {

        $currentEntityData = $entity->__toArray();
        $afterData = [];
        if ($type == Activity::TYPE_UPDATED) {
            foreach ($diffData as $fieldName => $fieldValue) {
                $afterData[$fieldName] = $currentEntityData[$fieldName];
            }
        }

        $fullDiffData['diff_fields'] = array_keys($diffData);
        $fullDiffData['before_data'] = $diffData;
        $fullDiffData['after_data'] = $afterData;

        // author of activity
        $author = $this->securityToken->getToken()->getUser();
        $activity->setCustomer($author);
        $activity->setEntity($entityName);
        $activity->setType($type);

        $currentDate = new \DateTime();
        $activity->setDiffData($fullDiffData);
        $activity->setDate($currentDate);

        $this->manager->persist($activity);
        $this->manager->flush();
    }
}
