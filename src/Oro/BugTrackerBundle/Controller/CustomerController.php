<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\CustomerType;
use Oro\BugTrackerBundle\Entity\Customer;
use Oro\BugTrackerBundle\Entity\Issue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Oro\BugTrackerBundle\Entity\Activity;

class CustomerController extends Controller
{
    CONST CUSTOMER_LIST_PAGE_SIZE = 3;
    CONST ACTIVITY_CUSTOMER_PAGE_LIMIT = 5;

    /**
     * Customer list action
     * @Route("customer/list/", name="oro_bugtracker_customer_list")
     */
    public function listAction()
    {
        $pageTitle = 'Manage customers';
        $columns = ['id' => 'Id', 'username' => 'User Name', 'email' => 'Email', 'fullName' => 'Full Name'];
        $actions[] = [
            'label' => 'View',
            'router' => 'oro_bugtracker_customer_view',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id'],
            ],
        ];
        $actions[] = [
            'label' => 'Edit',
            'router' => 'oro_bugtracker_customer_edit',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id'],
            ],
        ];

        return $this->render(
            'BugTrackerBundle:Customer:list.html.twig',
            [
                'page_title' => $pageTitle,
                'entity_create_router' => 'oro_bugtracker_customer_create',
                'entity_class' => Customer::class,
                'columns' => $columns,
                'actions' => $actions,
                'paginator_var' => 'customer_p',
            ]
        );
    }

    /**
     * Customer customer action
     * @Route("customer/create")
     */
    public function createAction(Request $request)
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        try {
            $formHandler = $this->getCustomerHandler();
            if ($request->getMethod() == 'POST') {
                if ($formHandler->handleCreateForm($form)) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'User has been created successfully!');

                    return $this->redirectToRoute('oro_bugtracker_customer_edit', array('id' => $customer->getId()));
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', "User wasn't created successfully!");

                    return $this->redirectToRoute('oro_bugtracker_customer_create');
                }
            }
        } catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        return $this->render(
            'BugTrackerBundle:Customer:create.html.twig',
            array(
                'form' => $form->createView(),
                'page_title' => 'New Customer',
            )
        );
    }

    /**
     * Create edit action
     * @Route("customer/view/{id}/", name="oro_bugtracker_customer_view", requirements={"id" = "\d+"})
     */
    public function viewAction(Customer $customer, Request $request)
    {
        $issueGridActions = $this->getIssueGridAction();
        //todo  вызов медода без аргументов
        $issuesQb = $this->getDoctrine()->getRepository(Issue::class)->findByCondition(
            [
                'assignee' => ['=' => $customer->getId()],
                'status' => ['in' => [Issue::STATUS_OPEN, Issue::STATUS_REOPEN, Issue::STATUS_IN_PROGRESS]],
            ]
        );

        $issueGridHtml = $this->getIssuesGridHtml($issuesQb, $issueGridActions);
        $activitiesHtml = $this->getActivityHtml($customer, true);

        return $this->render(
            'BugTrackerBundle:Customer:view.html.twig',
            array(
                'page_title' => sprintf("View User '%s'", $customer->getUsername()),
                'entity' => $customer,
                'issue_grid_html' => $issueGridHtml,
                'activity_html' => $activitiesHtml
            )
        );
    }

    /**
     * Create edit action
     * @Route("customer/edit/{id}/{page}", name="oro_bugtracker_customer_edit", requirements={"id" = "\d+"}, defaults={"page" = 1})
     */
    public function editAction(Customer $customer, Request $request)
    {
        $form = $this->createForm(
            CustomerType::class,
            $customer,
            array(
                'validation_groups' => array('edit'),
            )
        );

        try {
            $formHandler = $this->getCustomerHandler();
            if ($request->getMethod() == 'POST') {
                if ($formHandler->handleEditForm($form)) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Customer has been updated successfully!');
                }
            }
        }catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        return $this->render(
            'BugTrackerBundle:Customer:edit.html.twig',
            array(
                'form' => $form->createView(),
                'page_title' => sprintf("Edit User '%s'", $customer->getUsername()),
                'entity_id' => $customer->getId(),
            )
        );
    }

    /**
     * Customer delete action
     * @Route("customer/delete/{id}",requirements={"id" = "\d+"})
     */
    public function deleteAction(Customer $customer, Request $request)
    {
        $actionUrl = $this->generateUrl(
            'oro_bugtracker_customer_delete',
            array('id' => $customer->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $form = $this->createFormBuilder($customer, array('validation_groups' => array('edit')))
            ->setAction($actionUrl)
            ->add('delete', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $username = $customer->getUsername();
            $formHandler = $this->getCustomerHandler();

            if ($formHandler->handleDeleteForm($form)) {
                $request->getSession()
                    ->getFlashBag()
                    ->add(
                        'success',
                        sprintf("Customer '%s' was deleted successfully!", $username)
                    );

                return $this->redirectToRoute('oro_bugtracker_customer_list');
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
     * @param bool $useView
     * @return array
     */
    public function getIssueGridAction($useView = true)
    {
        $actions = [];
        if ($useView) {
            $actions[] = [
                'label' => 'View',
                'router' => 'oro_bugtracker_issue_view',
                'router_parameters' => [
                    ['collection_key' => 'id', 'router_key' => 'id']
                ],
            ];
        }

        return $actions;
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
                'entity_class' => Issue::class,
                'entity_query_builder' => $entityQueryBuilder,
                'columns' => $columns,
                'actions' => $actions,
                'paginator_var' => 'issue_p'
            ]
        )->getContent();

        return $membersHtml;
    }

    public function getActivityHtml(Customer $customer, $limited = false)
    {
        $activityRepository = $this->getDoctrine()->getRepository(Activity::class);
        $activityCollection = $activityRepository->getActivityCustomerCollection(
            $customer,
            self::ACTIVITY_CUSTOMER_PAGE_LIMIT
        );

        $activityHtml = $this->render(
            'BugTrackerBundle:Activity:paginator_list.html.twig',
            [
                'entity_class' => Customer::class,
                'limit' => self::ACTIVITY_CUSTOMER_PAGE_LIMIT,
                'collection' => $activityCollection,
                'paginator_var' => 'activity_p'
            ]
        )->getContent();

        return $activityHtml;
    }

    public function getCustomerHandler()
    {
        return $this->get('oro_bugtracker.handler.customer');
    }
}
