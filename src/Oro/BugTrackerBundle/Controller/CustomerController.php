<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\CustomerType;
use Oro\BugTrackerBundle\Entity\Customer;
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
        $em = $this->getDoctrine()->getEntityManager();
        $queryBuilder = $em
            ->getRepository('BugTrackerBundle:Customer')
            ->createQueryBuilder('cust');
        $queryBuilder->select(['cust.id', 'cust.username', 'cust.email', 'cust.fullName']);

        $paginator = new Paginator($queryBuilder, false);
        // init template params
        $collection = $paginator
            ->getQuery()
            ->setFirstResult(self::CUSTOMER_LIST_PAGE_SIZE * ($page - 1))// Offset
            ->setMaxResults(self::CUSTOMER_LIST_PAGE_SIZE)
            ->getResult();
        // get collection qty
        $queryBuilder = $em
            ->getRepository('BugTrackerBundle:Customer')
            ->createQueryBuilder('cust');
        $queryBuilder->select('count(cust.id)');
        $totalCount = $queryBuilder->getQuery()->getSingleScalarResult();

        $maxPages = ceil($totalCount / self::CUSTOMER_LIST_PAGE_SIZE);
        $thisPage = $page;
        $entityCreateRouter = 'oro_bugtracker_customer_create';
        $listRouteName = 'oro_bugtracker_customer_list';

        $columns = ['id' => 'Id', 'username' => 'User Name', 'email' => 'Email', 'fullName' => 'Full Name'];
        $actions[] = [
            'label' => 'Edit',
            'router' => 'oro_bugtracker_customer_edit',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id'],
            ],
        ];

        $page_title = 'Manage customers';

        return $this->render(
            'BugTrackerBundle:Customer:list.html.twig',
            compact(
                'entityCreateRouter',
                'page_title',
                'collection',
                'columns',
                'actions',
                'maxPages',
                'thisPage',
                'listRouteName'
            )
        );
    }

    /**
     * Customer customer action
     * @Route("customer/create")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

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
     * @Route("customer/edit/{id}",requirements={"id" = "\d+"})
     */
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $customerEntityData = $em->getRepository(Customer::class)->find($id);

        if (!$customerEntityData) {
            $errorMessage = 'Required customer was not found!';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $errorMessage);

            return $this->redirect('/');
        }
        $form = $this->createForm(
            CustomerType::class,
            $customerEntityData,
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
                        $password = $passwordEncoder->encodePassword($customerEntityData, $plainPassword->getData());
                        $customerEntityData->setPassword($password);
                    }

                    $em->merge($customerEntityData);

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
                'page_title' => sprintf("Edit User '%s'", $customerEntityData->getUsername()),
                'entity_id' => $customerEntityData->getId(),
            )
        );
    }

    /**
     * Customer delete action
     * @Route("customer/delete/{id}",requirements={"id" = "\d+"})
     */
    public function deleteAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository(Customer::class)->find($id);
        if (!$customer) {
            throw $this->createNotFoundException(
                'No customer found for id '.$id
            );
        }

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
            $form->submit($request);
            if ($form->isValid()) {
                $em->remove($customer);
                $em->flush();
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', sprintf("Customer '%s' was deleted successfully!", $customer->getUsername()));

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
}
