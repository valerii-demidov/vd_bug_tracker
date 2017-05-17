<?php

namespace Oro\BugTrackerBundle\Form\Handler;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Oro\BugTrackerBundle\Entity\Comment;

class CommentHandler
{
    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /**
     * CommentHandler constructor.
     * @param RequestStack $request
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        RequestStack $request,
        EntityManagerInterface $manager
    ) {
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * @param $form
     * @return bool
     */
    public function handleEditForm($form)
    {
        $request = $this->request->getCurrentRequest();
        $form->handleRequest($request);

        $project = $form->getData();
        if ($form->isValid()) {
            $this->manager->merge($project);
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
        if ($form->isValid()) {
            $this->manager->remove($comment);
            $this->manager->flush();
            $this->manager->clear();
        } else {
            return false;
        }

        return true;
    }
}
