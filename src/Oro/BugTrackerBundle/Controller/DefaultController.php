<?php

namespace Oro\BugTrackerBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oro\BugTrackerBundle\Entity\Activity;
use Oro\BugTrackerBundle\Entity\Issue;

class DefaultController extends Controller
{

    public function indexAction()
    {
        $customer = $this->getCurrentUser();
        $issuesQb = $this->getDoctrine()->getRepository(Issue::class)->findByCondition(
            [
                'assignee' => ['=' => $customer->getId()],
                'status' => ['in' => [Issue::STATUS_OPEN, Issue::STATUS_REOPEN, Issue::STATUS_IN_PROGRESS]],
            ]
        );
        $actions[] = [
            'label' => 'View',
            'router' => 'oro_bugtracker_issue_view',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id']
            ],
        ];
        $issueGridHtml = $this->getIssuesGridHtml($issuesQb, $actions);
        $activitiesHtml = $this->getActivityHtml($customer);

        return $this->render('BugTrackerBundle:Default:index.html.twig', [
            'issue_grid_html' => $issueGridHtml,
            'activity_html' => $activitiesHtml,
            
            'page_title' => 'Home page'
        ]);
    }

    /**
     * @param $entityQueryBuilder
     * @param $actions
     * @param $currentPage
     * @return string
     */
    protected function getIssuesGridHtml($entityQueryBuilder, $actions)
    {
        $columns = ['id' => 'Id', 'code' => 'Code', 'summary' => 'Summary', 'status' => 'Status'];
        $membersHtml = $this->render(
            'BugTrackerBundle:Customer:issue.html.twig',
            [
                'entity_query_builder' => $entityQueryBuilder,
                'columns' => $columns,
                'actions' => $actions,
                'paginator_var' => 'issue_p'
            ]
        )->getContent();

        return $membersHtml;
    }

    public function getActivityHtml($customer)
    {
        $activityRepository = $this->getDoctrine()->getRepository(Activity::class);
        $activityCollection = $activityRepository->getActivityCustomerCollection($customer);

        $activityHtml = $this->render(
            'BugTrackerBundle:Activity:paginator_list.html.twig',
            [
                'collection' => $activityCollection,
                'paginator_var' => 'activity_p'
            ]
        )->getContent();

        return $activityHtml;
    }

    /**
     * @return mixed
     */
    public function getCurrentUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }
}
