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
        /*$actions[] = [
            'label' => 'Edit',
            'router' => 'oro_bugtracker_project_edit',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id']
            ],
        ];*/

        return $this->render(
            'BugTrackerBundle:Issue:list.html.twig',
            compact(
                'collection', // grid
                'columns',  // grid
                //'actions',  // grid
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
                $em->persist($issue);
                $em->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->add('success', 'Issue has been created successfully!');

                return $this->redirectToRoute('oro_bugtracker_issue_list', array('id' => $issue->getId()));
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
}
