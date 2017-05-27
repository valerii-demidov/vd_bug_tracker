<?php

namespace Oro\BugTrackerBundle\Form\Handler;

use Oro\BugTrackerBundle\Entity\Comment;
use Oro\BugTrackerBundle\Entity\Issue;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\BugTrackerBundle\Entity\Activity;
use Oro\BugTrackerBundle\Event\CommentBeforeCreateEvent;
use Oro\BugTrackerBundle\Event\IssueBeforeCreateEvent;
use Oro\BugTrackerBundle\Event\IssueBeforeUpdateEvent;

class IssueHandler
{
    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var TokenStorage */
    protected $securityToken;

    /** @var EventDispatcherInterface  */
    protected $dispatcher;

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
        EventDispatcherInterface $dispatcher

    ) {
        $this->request = $request;
        $this->manager = $manager;
        $this->securityToken = $securityToken;
        $this->dispatcher = $dispatcher;
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
                $сurrentUser = $this->securityToken->getToken()->getUser();
                $issue->setReporter($сurrentUser);
                $issue->addCollaboration($issue->getAssignee());
                $issue->addCollaboration($issue->getReporter());
                $issue->addCollaboration($сurrentUser);
                $this->manager->persist($issue);
                $this->dispatcher->dispatch(IssueBeforeCreateEvent::EVENT_NAME, new IssueBeforeCreateEvent($issue));

                $this->manager->flush();
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
        $form->handleRequest($request);

        $issue = $form->getData();
        if ($form->isValid()) {
            $currentUser = $this->securityToken->getToken()->getUser();
            $issue->addCollaboration($currentUser);
            $issue->addCollaboration($issue->getAssignee());
            $issue->setUpdated(new \DateTime());

            $this->manager->persist($issue);
            $this->dispatcher->dispatch(IssueBeforeUpdateEvent::EVENT_NAME, new IssueBeforeUpdateEvent($issue));

            $this->manager->flush();
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
                $currentUser = $this->securityToken->getToken()->getUser();
                $comment->setIssue($issue);

                $project = $issue->getProject();
                $comment->setProject($project);
                $comment->setCustomer($currentUser);
                $comment->setCreated(new \DateTime());

                $this->manager->persist($comment);

                $issue->addCollaboration($currentUser);
                $this->manager->persist($issue);

                $this->dispatcher->dispatch(CommentBeforeCreateEvent::EVENT_NAME, new CommentBeforeCreateEvent($comment));
                $this->manager->flush();

                return true;
            }
        }

        return false;
    }
}
