<?php

namespace Oro\BugTrackerBundle\Form\Handler;

use Oro\BugTrackerBundle\Entity\Comment;
use Oro\BugTrackerBundle\Entity\Issue;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Oro\BugTrackerBundle\Form\Handler\activityHandler;
use Oro\BugTrackerBundle\Entity\Activity;

class IssueHandler
{
    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var TokenStorage */
    protected $securityToken;

    /** @var activityHandler */
    protected $activityHandler;

    /**
     * IssueHandler constructor.
     * @param RequestStack $request
     * @param EntityManagerInterface $manager
     * @param TokenStorage $securityToken
     */
    public function __construct(
        RequestStack $request,
        EntityManagerInterface $manager,
        TokenStorage $securityToken,
        activityHandler $activityHandler

    ) {
        $this->request = $request;
        $this->manager = $manager;
        $this->securityToken = $securityToken;
        $this->activityHandler = $activityHandler;
    }


    /**
     * @param $form
     * @return bool
     */
    public function handleCreateForm($form)
    {
        $request = $this->request->getCurrentRequest();
        $form->handleRequest($request);

        $issue = $form->getData();
        if ($issue instanceof Issue) {
            if ($form->isSubmitted() && $form->isValid()) {
                $reporter = $this->securityToken->getToken()->getUser();
                $issue->setReporter($reporter);
                $issue->addCollaboration($issue->getAssignee());
                $issue->addCollaboration($issue->getReporter());
                $this->manager->persist($issue);
                $this->manager->flush();

                $this->activityHandler->handleIssueActivity($issue, Activity::TYPE_CREATED, $issue->__toArray());
                return true;
            }
        }

        return false;
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

        $issue = $form->getData();
        if ($form->isValid()) {
            $entityAfter = $issue->__toArray();
            $issue->addCollaboration($issue->getAssignee());
            $issue->setUpdated(new \DateTime());

            $this->manager->merge($issue);
            $this->manager->flush();

            $diffData = array_diff_assoc($entityPreview, $entityAfter);
            $this->activityHandler->handleIssueActivity($issue, Activity::TYPE_UPDATED, $diffData);
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

        $issue = $form->getData();
        if ($form->isValid()) {
            $this->manager->remove($issue);
            $this->manager->flush();
            $this->manager->clear();
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param $form
     * @param Issue $issue
     * @return bool
     */
    public function handleCreateCommentForm($form, Issue $issue)
    {
        $request = $this->request->getCurrentRequest();
        $form->handleRequest($request);

        $comment = $form->getData();
        if ($comment instanceof Comment) {
            if ($form->isSubmitted() && $form->isValid()) {
                $customer = $this->securityToken->getToken()->getUser();
                $comment->setIssue($issue);

                $project = $issue->getProject();
                $comment->setProject($project);
                $comment->setCustomer($customer);
                $comment->setCreated(new \DateTime());

                $this->manager->persist($comment);

                $issue->addCollaboration($customer);
                $this->manager->persist($issue);
                $this->manager->flush();

                $this->activityHandler->handleCommentActivity($comment, Activity::TYPE_CREATED, $comment->__toArray());
                return true;
            }
        }

        return false;
    }
}
