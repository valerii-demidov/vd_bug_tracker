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
        return $this->render(
            'BugTrackerBundle:Customer:list.html.twig',
            [
                'entity_class' => Customer::class,
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

                    return $this->redirectToRoute('oro_bugtracker_customer_view', array('id' => $customer->getId()));
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
        return $this->render(
            'BugTrackerBundle:Customer:view.html.twig',
            array(
                'page_title' => sprintf("View User '%s'", $customer->getUsername()),
                'entity' => $customer,
                'activity_class' => Activity::class,
                'issue_class' => Issue::class
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

    public function getCustomerHandler()
    {
        return $this->get('oro_bugtracker.handler.customer');
    }
}
