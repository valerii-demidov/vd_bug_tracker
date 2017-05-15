<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\IssueType;
use Oro\BugTrackerBundle\Entity\Issue;
use Oro\BugTrackerBundle\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class IssueController extends Controller
{

    const ISSUE_LIST_PAGE_SIZE = 3;  
    

    /**
     * Issue list action
     * @Route("issue/list/{page}", name="oro_bugtracker_issue_list", requirements={"page" = "\d+"},
     *     defaults={"page" = 1})
     */
    public function listAction($page)
    {
        $em = $this->getDoctrine()->getManager();
        $entityRepository = $em->getRepository('BugTrackerBundle:Issue');
        $pageTitle = 'Manage Issues';

        $columns = ['id' => 'Id', 'code' => 'Code', 'summary' => 'Summary'];
        $actions[] = [
            'label' => 'View',
            'router' => 'oro_bugtracker_issue_view',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id']
            ],
        ];
        $actions[] = [
            'label' => 'Edit',
            'router' => 'oro_bugtracker_issue_edit',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id']
            ],
        ];

        return $this->render(
            'BugTrackerBundle:Issue:list.html.twig',
            [
                'page_title' => $pageTitle,
                'entity_create_router' => 'oro_bugtracker_issue_create',
                'entity_repository' => $entityRepository,
                'columns' => $columns,
                'actions' => $actions,
                'current_page' => $page,
            ]
        );
    }

    /**
     * Project create action
     * @Route("issue/create", name="oro_bugtracker_issue_create")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // 1) build the form
        $issue = new Issue();
        $form = $this->createForm(IssueType::class, $issue);
        try {
            // 2) handle the submit (will only happen on POST)
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $reporter = $this->get('security.token_storage')->getToken()->getUser();
                $issue->setReporter($reporter);
                $issue->addCollaboration($issue->getAssignee());
                $issue->addCollaboration($issue->getReporter());
                $em->persist($issue);
                $em->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->add('success', 'Issue has been created successfully!');

                return $this->redirectToRoute('oro_bugtracker_issue_edit', array('id' => $issue->getId()));
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
    public function viewAction(Issue $issueEntity, Request $request)
    {
        return $this->render(
            'BugTrackerBundle:Issue:view.html.twig',
            array(
                'entity' => $issueEntity,
                'page_title' => sprintf("View Issue '%s'", $issueEntity->getCode()),
            )
        );
    }

    /**
     * Issue edit action
     *
     * @Route("issue/edit/{id}", name="oro_bugtracker_issue_edit", requirements={"id" = "\d+"})
     */
    public function editAction(Issue $issueEntity, Request $request)
    {
        $form = $this->createForm(
            IssueType::class,
            $issueEntity,
            array(
                'validation_groups' => array('edit'),
            )
        );

        $em = $this->getDoctrine()->getManager();
        try {
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $issueEntity->addCollaboration($issueEntity->getAssignee());
                    $em->merge($issueEntity);

                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Issue has been updated successfully!');
                    $em->flush();
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
                'entity' => $issueEntity,
                'form' => $form->createView(),
                'page_title' => sprintf("Edit Issue '%s'", $issueEntity->getCode())
            )
        );
    }

    /**
     * Issue delete action
     * @Route("issue/delete/{id}",requirements={"id" = "\d+"}, name="oro_bugtracker_issue_delete")
     */
    public function deleteAction(Issue $issueEntity, Request $request)
    {
        $actionUrl = $this->generateUrl(
            'oro_bugtracker_issue_delete',
            array('id' => $issueEntity->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $form = $this->createFormBuilder($issueEntity, array('validation_groups' => array('edit')))
            ->setAction($actionUrl)
            ->add('delete', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $issueId = $issueEntity->getId();
                $em->remove($issueEntity);
                $em->flush();
                $em->clear();
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', sprintf("Issue '%s' was deleted successfully!", $issueId));

                return $this->redirectToRoute('oro_bugtracker_issue_list');
            }
        }

        return $this->render(
            'BugTrackerBundle:Widget:form.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }
}
