<?php

namespace Oro\BugTrackerBundle\Form\Handler;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Oro\BugTrackerBundle\Entity\Comment;
use Oro\BugTrackerBundle\Form\Handler\activityHandler;
use Oro\BugTrackerBundle\Entity\Activity;

class CommentHandler
{
    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var activityHandler */
    protected $activityHandler;

    /**
     * CommentHandler constructor.
     * @param RequestStack $request
     * @param EntityManagerInterface $manager
     * @param \Oro\BugTrackerBundle\Form\Handler\activityHandler $activityHandler
     */
    public function __construct(
        RequestStack $request,
        EntityManagerInterface $manager,
        activityHandler $activityHandler
    ) {
        $this->request = $request;
        $this->manager = $manager;
        $this->activityHandler = $activityHandler;
    }

    /**
     * @param $form
     * @return bool
     */
    public function handleEditForm($form)
    {
        $request = $this->request->getCurrentRequest();
        $entityPreview = $form->getData()->__toArray();
        $form->handleRequest($request);

        $comment = $form->getData();
        if ($form->isValid()) {
            $this->manager->merge($comment);
            $entityAfter = $comment->__toArray();
            $this->manager->flush();

            $diffData = array_diff_assoc($entityPreview, $entityAfter);
            $this->activityHandler->handleCommentActivity(
                $comment,
                Activity::TYPE_UPDATED,
                $diffData);
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param $form
     * @return bool
     */
    public function handleDeleteForm($form)
    {
        $request = $this->request->getCurrentRequest();
        $form->handleRequest($request);

        $comment = $form->getData();
        if ($form->isValid()) {
            $this->activityHandler->handleCommentActivity(
                $comment,
                Activity::TYPE_DELETED,
                $comment->__toArray()
            );
            $this->manager->remove($comment);
            $this->manager->flush();
            $this->manager->clear();
        } else {
            return false;
        }

        return true;
    }
}
