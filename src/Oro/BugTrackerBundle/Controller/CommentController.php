<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\CommentType;
use Oro\BugTrackerBundle\Entity\Comment;
use Oro\BugTrackerBundle\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Oro\BugTrackerBundle\Security\CommentVoter;


class CommentController extends Controller
{
    /**
     * Comment edit action
     * @Route("comment/edit/{id}", name="oro_bugtracker_comment_edit", requirements={"id" = "\d+"})
     */
    public function editAction(Comment $comment, Request $request)
    {
        $this->denyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $actionUrl = $this->generateUrl(
            'oro_bugtracker_comment_edit',
            array('id' => $comment->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $form = $this->createForm(CommentType::class, $comment,[
            'action' => $actionUrl,
            'method' => 'POST',
        ]);

        try {
            $formHandler = $this->getCommentHandler();
            if ($request->getMethod() == 'POST') {
                if ($formHandler->handleEditForm($form)) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Comment has been updated successfully!');

                    return $this->redirectToRoute(
                        'oro_bugtracker_issue_view',
                        array('id' => $comment->getIssue()->getId())
                    );
                }
            }
        } catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        return $this->render(
            'BugTrackerBundle:Comment:edit.html.twig',
            array(
                'form' => $form->createView(),
                'entity' => $comment,
            )
        );
    }

    /**
     * Comment delete action
     * @Route("comment/delete/{id}", name="oro_bugtracker_comment_delete", requirements={"id" = "\d+"})
     */
    public function deleteAction(Comment $comment, Request $request)
    {
        $this->denyAccessUnlessGranted(CommentVoter::DELETE, $comment);
        $actionUrl = $this->generateUrl(
            'oro_bugtracker_comment_delete',
            array('id' => $comment->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $form = $this->createFormBuilder($comment, array('validation_groups' => array('edit')))
            ->setAction($actionUrl)
            ->add('delete', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $commentId = $comment->getId();
            $issueId = $comment->getIssue()->getId();
            $formHandler = $this->getCommentHandler();

            if ($formHandler->handleDeleteForm($form)) {
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', sprintf("Comment '%s' was deleted successfully!", $commentId));

                return $this->redirectToRoute('oro_bugtracker_issue_view',['id'=> $issueId]);
            }
        }

        return $this->render(
            'BugTrackerBundle:Comment:delete.html.twig',
            array(
                'form' => $form->createView(),
                'entity' => $comment
            )
        );
    }

    /**
     * @return object|\Oro\BugTrackerBundle\Form\Handler\CommentHandler
     */
    public function getCommentHandler()
    {
        return $this->get('oro_bugtracker.handler.comment');
    }
}
