<?php

namespace Oro\BugTrackerBundle\Form\Handler;

use Oro\BugTrackerBundle\Entity\Comment;
use Oro\BugTrackerBundle\Entity\Issue;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class IssueHandler
{
    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var TokenStorage */
    protected $securityToken;

    /**
     * IssueHandler constructor.
     * @param RequestStack $request
     * @param EntityManagerInterface $manager
     * @param TokenStorage $securityToken
     */
    public function __construct(
        RequestStack $request,
        EntityManagerInterface $manager,
        TokenStorage $securityToken

    ) {
        $this->request = $request;
        $this->manager = $manager;
        $this->securityToken = $securityToken;
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
            $issue->addCollaboration($issue->getAssignee());
            $issue->setUpdated(new \DateTime());

            $this->manager->merge($issue);
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
     * @param $issue
     * @return bool
     */
    public function handleCreateCommentForm($form, $issue)
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
                $this->manager->flush();

                return true;
            }
        }

        return false;
    }
}
