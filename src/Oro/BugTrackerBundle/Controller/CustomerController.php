<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\CustomerType;
use Oro\BugTrackerBundle\Entity\Customer;
use Oro\BugTrackerBundle\Entity\Issue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
        $em = $this->getDoctrine()->getManager();

        // 1) build the form
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        try {
            // 2) handle the submit (will only happen on POST)
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // 3) Encode the password (you could also do this via Doctrine listener)
                $password = $this->get('security.password_encoder')
                    ->encodePassword($customer, $customer->getPlainPassword());
                $customer->setPassword($password);

                // 4) save the User!
                $em->persist($customer);
                $em->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->add('success', 'User has been created successfully!');

                return $this->redirectToRoute('oro_bugtracker_customer_edit', array('id' => $customer->getId()));
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
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(
            CustomerType::class,
            $customerEntity,
            array(
                'validation_groups' => array('edit'),
            )
        );

        try {
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $plainPassword = $form->get('plainPassword');
                    if (!$plainPassword->isEmpty()) {
                        $passwordEncoder = $this->get('security.password_encoder');
                        $password = $passwordEncoder->encodePassword($customerEntity, $plainPassword->getData());
                        $customerEntity->setPassword($password);
                    }

                    $em->merge($customerEntity);

                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Customer has been updated successfully!');
                    $em->flush();
                }
            }
        } catch (\Exception $exception) {
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
        $em = $this->getDoctrine()->getManager();
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
            $form->submit($request);
            if ($form->isValid()) {
                $em->remove($customerEntity);
                $em->flush();
                $request->getSession()
                    ->getFlashBag()
                    ->add(
                        'success',
                        sprintf("Customer '%s' was deleted successfully!", $customerEntity->getUsername())
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
}
