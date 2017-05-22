<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Entity\Activity;
use Oro\BugTrackerBundle\Entity\Comment;
use Oro\BugTrackerBundle\Form\CommentType;
use Oro\BugTrackerBundle\Form\IssueType;
use Oro\BugTrackerBundle\Entity\Issue;
use Oro\BugTrackerBundle\Security\IssueVoter;
use Oro\BugTrackerBundle\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class IssueController extends Controller
{

    const ISSUE_LIST_PAGE_SIZE = 3;


    /**
     * Issue list action
     *
     * @Route("issue/list/", name="oro_bugtracker_issue_list")
     */
    public function listAction()
    {

        $em = $this->getDoctrine()->getManager();
        $entityRepository = $em->getRepository('BugTrackerBundle:Issue');
        $user = $this->getUser();
        $isManagerGranted = $this->isGranted(Customer::ROLE_MANAGER);
        $issueCollection = $entityRepository->getGrantedIssues($user, $isManagerGranted);

        $pageTitle = 'Manage Issues';

        $columns = ['id' => 'Id', 'code' => 'Code', 'summary' => 'Summary'];
        $actions[] = [
            'label' => 'View',
            'router' => 'oro_bugtracker_issue_view',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id'],
            ],
        ];
        if ($isManagerGranted) {
            $actions[] = [
                'label' => 'Edit',
                'router' => 'oro_bugtracker_issue_edit',
                'router_parameters' => [
                    ['collection_key' => 'id', 'router_key' => 'id'],
                ],
            ];
        }

        return $this->render(
            'BugTrackerBundle:Issue:list.html.twig',
            [
                'page_title' => $pageTitle,
                'entity_create_router' => 'oro_bugtracker_issue_create',
                'entity_collection' => $issueCollection,
                'columns' => $columns,
                'actions' => $actions,
                'paginator_var' => 'issue_p'
            ]
        );
    }

    /**
     * Project create action
     * @Route("issue/create", name="oro_bugtracker_issue_create")
     */
    public function createAction(Request $request)
    {
        $issue = new Issue();
        $form = $this->createForm(IssueType::class, $issue);
        try {
            $formHandler = $this->getIssueHandler();
            if ($request->getMethod() == 'POST') {
                if ($formHandler->handleCreateForm($form)) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Issue has been created successfully!');

                    return $this->redirectToRoute('oro_bugtracker_issue_edit', array('id' => $issue->getId()));
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('error', "Issue wasn't created successfully!");
                }
            }
        } catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        return $this->render(
            'BugTrackerBundle:Issue:create.html.twig',
            array(
                'form' => $form->createView(),
                'page_title' => 'New Issue',
            )
        );
    }

    /**
     * Issue view action
     *
     * @Route("issue/view/{id}", name="oro_bugtracker_issue_view", requirements={"id" = "\d+"})
     */
    public function viewAction(Issue $issue, Request $request)
    {
        $this->denyAccessUnlessGranted(IssueVoter::VIEW, $issue);
        $actionUrl = $this->generateUrl(
            'oro_bugtracker_issue_addcomment',
            array('id' => $issue->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment, ['action' => $actionUrl]);
        $activityCollection = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->getActivityIssueCollection($issue)
            ->getQuery()
            ->getResult();

        return $this->render(
            'BugTrackerBundle:Issue:view.html.twig',
            array(
                'entity' => $issue,
                'page_title' => sprintf("View Issue '%s'", $issue->getCode()),
                'comment_form' => $commentForm->createView(),
                'activity_collection' => $activityCollection
            )
        );
    }

    /**
     * Issue edit action
     *
     * @Route("issue/edit/{id}", name="oro_bugtracker_issue_edit", requirements={"id" = "\d+"})
     */
    public function editAction(Issue $issue, Request $request)
    {
        $this->denyAccessUnlessGranted(IssueVoter::EDIT, $issue);
        $form = $this->createForm(
            IssueType::class,
            $issue,
            array(
                'validation_groups' => array('edit'),
            )
        );

        try {
            if ($request->getMethod() == 'POST') {
                $formHandler = $this->getIssueHandler();
                if ($formHandler->handleEditForm($form)) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Issue has been updated successfully!');
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('error', "Issue wasn't updated successfully!");
                }
            }
        } catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        return $this->render(
            'BugTrackerBundle:Issue:edit.html.twig',
            array(
                'entity' => $issue,
                'form' => $form->createView(),
                'page_title' => sprintf("Edit Issue '%s'", $issue->getCode()),
            )
        );
    }

    /**
     * Issue delete action
     * @Route("issue/delete/{id}",requirements={"id" = "\d+"}, name="oro_bugtracker_issue_delete")
     */
    public function deleteAction(Issue $issue, Request $request)
    {
        $this->denyAccessUnlessGranted(IssueVoter::DELETE, $issue);
        $actionUrl = $this->generateUrl(
            'oro_bugtracker_issue_delete',
            array('id' => $issue->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $form = $this->createFormBuilder($issue, array('validation_groups' => array('edit')))
            ->setAction($actionUrl)
            ->add('delete', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            if ($request->getMethod() == 'POST') {
                $issueId = $issue->getId();
                $formHandler = $this->getIssueHandler();
                if ($formHandler->handleDeleteForm($form)) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', sprintf("Issue '%s' was deleted successfully!", $issueId));

                    return $this->redirectToRoute('oro_bugtracker_issue_list');
                }
            }
        }

        return $this->render(
            'BugTrackerBundle:Widget:form.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Add comment action
     * @Route("issue/{id}/addcomment/",requirements={"id" = "\d+"}, name="oro_bugtracker_issue_addcomment")
     */
    public function addcommentAction(Issue $issue, Request $request)
    {
        $this->denyAccessUnlessGranted(IssueVoter::VIEW, $issue);
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        try {
            $formHandler = $this->getIssueHandler();
            if ($request->getMethod() == 'POST') {
                if ($formHandler->handleCreateCommentForm($form, $issue)) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Comment has been created successfully!');
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('error', "Comment wasn't created successfully!");
                }
            }
        } catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        return $this->redirectToRoute('oro_bugtracker_issue_view', array('id' => $issue->getId()));
    }

    /**
     * @return object|\Oro\BugTrackerBundle\Form\Handler\IssueHandler
     */
    public function getIssueHandler()
    {
        return $this->get('oro_bugtracker.handler.issue');
    }
}
