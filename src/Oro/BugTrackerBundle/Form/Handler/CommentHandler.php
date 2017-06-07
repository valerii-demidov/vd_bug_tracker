<?php

namespace Oro\BugTrackerBundle\Form\Handler;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Oro\BugTrackerBundle\Event\CommentBeforeUpdateEvent;
use Oro\BugTrackerBundle\Event\CommentBeforeDeleteEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentHandler
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
     * CommentHandler constructor.
     * @param RequestStack $request
     * @param EntityManagerInterface $manager
     * @param TokenStorage $securityToken
     * @param EventDispatcherInterface $dispatcher
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
    public function handleEditForm($form)
    {
        $request = $this->request->getCurrentRequest();
        $form->handleRequest($request);

        $comment = $form->getData();
        $issue = $comment->getIssue();
        if ($form->isValid()) {
            $сurrentUser = $this->securityToken->getToken()->getUser();
            $issue->addCollaboration($сurrentUser);
            $this->dispatcher->dispatch(CommentBeforeUpdateEvent::EVENT_NAME, new CommentBeforeUpdateEvent($comment));

            $this->manager->persist($issue);
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

        $comment = $form->getData();
        $issue = $comment->getIssue();
        if ($form->isValid()) {
            $сurrentUser = $this->securityToken->getToken()->getUser();
            $issue->addCollaboration($сurrentUser);

            $this->manager->persist($issue);
            $this->manager->remove($comment);

            $this->dispatcher->dispatch(CommentBeforeDeleteEvent::EVENT_NAME, new CommentBeforeDeleteEvent($comment));
            $this->manager->flush();
            $this->manager->clear();
        } else {
            return false;
        }

        return true;
    }
}
