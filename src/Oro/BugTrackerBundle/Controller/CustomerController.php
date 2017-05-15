<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\CustomerType;
use Oro\BugTrackerBundle\Entity\Customer;
use Oro\BugTrackerBundle\Entity\Issue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerController extends Controller
{
    CONST CUSTOMER_LIST_PAGE_SIZE = 3;

    /**
     * Customer list action
     * @Route("customer/list/{page}", requirements={"page" = "\d+"}, defaults={"page" = 1})
     */
    public function listAction($page)
    {
        $em = $this->getDoctrine()->getManager();
        $entityRepository = $em->getRepository('BugTrackerBundle:Customer');
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
                'entity_repository' => $entityRepository,
                'columns' => $columns,
                'actions' => $actions,
                'current_page' => $page,
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
     * @Route("customer/view/{id}/{page}", name="oro_bugtracker_customer_view", requirements={"id" = "\d+"}, defaults={"page" = 1})
     */
    public function viewAction(Customer $customerEntity, $page, Request $request)
    {
        $issueGridActions = $this->getIssueGridAction();
        $issuesQb = $this->getDoctrine()->getRepository(Issue::class)->findByCondition(
            [
                'assignee' => ['=' => $customerEntity->getId()],
                'status' => ['in' => [Issue::STATUS_OPEN, Issue::STATUS_REOPEN, Issue::STATUS_IN_PROGRESS]],
            ]
        );

        $issueGridHtml = $this->getIssuesGridHtml($issuesQb, $issueGridActions, $page, $customerEntity->getId());
        return $this->render(
            'BugTrackerBundle:Customer:view.html.twig',
            array(
                'page_title' => sprintf("View User '%s'", $customerEntity->getUsername()),
                'entity' => $customerEntity,
                'issue_grid_html' => $issueGridHtml,
            )
        );
    }

    /**
     * Create edit action
     * @Route("customer/edit/{id}/{page}", name="oro_bugtracker_customer_edit", requirements={"id" = "\d+"}, defaults={"page" = 1})
     */
    public function editAction(Customer $customerEntity, Request $request)
    {
        $form = $this->createForm(
            CustomerType::class,
            $customerEntity,
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
                'page_title' => sprintf("Edit User '%s'", $customerEntity->getUsername()),
                'entity_id' => $customerEntity->getId(),
            )
        );
    }

    /**
     * Customer delete action
     * @Route("customer/delete/{id}",requirements={"id" = "\d+"})
     */
    public function deleteAction(Customer $customerEntity, Request $request)
    {
        $actionUrl = $this->generateUrl(
            'oro_bugtracker_customer_delete',
            array('id' => $customerEntity->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $form = $this->createFormBuilder($customerEntity, array('validation_groups' => array('edit')))
            ->setAction($actionUrl)
            ->add('delete', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $username = $customerEntity->getUsername();
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
    protected function getIssuesGridHtml($entityQueryBuilder, $actions, $currentPage, $staticRouteParam)
    {
        $columns = ['id' => 'Id', 'code' => 'Code', 'summary' => 'Summary', 'status' => 'Status'];
        $membersHtml = $this->render(
            'BugTrackerBundle:Customer:issue.html.twig',
            [
                'entity_query_builder' => $entityQueryBuilder,
                'columns' => $columns,
                'actions' => $actions,
                'current_page' => $currentPage,
                'static_route_params' => $staticRouteParam
            ]
        )->getContent();

        return $membersHtml;
    }

    public function getCustomerHandler()
    {
        return $this->get('oro_bugtracker.handler.customer');
    }
}
