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
        $queryBuilder = $em
            ->getRepository('BugTrackerBundle:Issue')
            ->createQueryBuilder('issue');
        $queryBuilder->select(['issue.id', 'issue.code', 'issue.summary']);

        $paginator = new Paginator($queryBuilder, false);

        $collection = $paginator
            ->getQuery()
            ->setFirstResult(self::ISSUE_LIST_PAGE_SIZE * ($page - 1))
            ->setMaxResults(self::ISSUE_LIST_PAGE_SIZE)
            ->getResult();

        $queryBuilder = $em
            ->getRepository('BugTrackerBundle:Issue')
            ->createQueryBuilder('issue');
        $queryBuilder->select('count(issue.id)');
        $totalCount = $queryBuilder->getQuery()->getSingleScalarResult();

        $maxPages = ceil($totalCount / self::ISSUE_LIST_PAGE_SIZE);
        $thisPage = $page;
        $entityCreateRouter = 'oro_bugtracker_issue_create';
        $listRouteName = 'oro_bugtracker_issue_list';
        $page_title = 'Manage Issues';

        $columns = ['id' => 'Id', 'code' => 'Code', 'summary' => 'Summary'];
        $actions[] = [
            'label' => 'Edit',
            'router' => 'oro_bugtracker_issue_edit',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id']
            ],
        ];

        return $this->render(
            'BugTrackerBundle:Issue:list.html.twig',
            compact(
                'collection', // grid
                'columns',  // grid
                'actions',  // grid
                'page_title',
                'entityCreateRouter', //buttons
                'listRouteName', //paginator
                'maxPages', //paginator
                'thisPage' //paginator
            )
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
                $em->persist($issue);
                $issue->addCollaboration($issue->getAssignee());
                $issue->addCollaboration($issue->getReporter());
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
     * Issue edit action
     *
     * @Route("issue/edit/{id}", name="oro_bugtracker_issue_edit", requirements={"id" = "\d+"})
     * @param $id
     * @param Request $request
     */
    public function editAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $issueEntityData = $em->getRepository(Issue::class)->find($id);

        if (!$issueEntityData) {
            $errorMessage = 'Required issue was not found!';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $errorMessage);

            return $this->redirect('/');
        }
        $form = $this->createForm(
            IssueType::class,
            $issueEntityData,
            array(
                'validation_groups' => array('edit'),
            )
        );

        try {
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $issueEntityData->addCollaboration($issueEntityData->getAssignee());
                    $em->merge($issueEntityData);

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
                'entity' => $issueEntityData,
                'form' => $form->createView(),
                'page_title' => sprintf("Edit Project '%s'", $issueEntityData->getId())
            )
        );
    }

    /**
     * Issue delete action
     * @Route("issue/delete/{id}",requirements={"id" = "\d+"}, name="oro_bugtracker_issue_delete")
     */
    public function deleteAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $issue = $em->getRepository(Issue::class)->find($id);
        if (!$issue) {
            throw $this->createNotFoundException(
                'No issues found for id '.$id
            );
        }

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
            $form->submit($request);
            if ($form->isValid()) {
                $issueId = $issue->getId();
                $em->remove($issue);
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
