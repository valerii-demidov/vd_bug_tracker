<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\CustomerType;
use Oro\BugTrackerBundle\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
            ->getArrayResult();
        // get collection qty
        $queryBuilder = $em
            ->getRepository('BugTrackerBundle:Customer')
            ->createQueryBuilder('cust');
        $queryBuilder->select('count(cust.id)');
        $totalCount = $queryBuilder->getQuery()->getSingleScalarResult();

        $maxPages = ceil($totalCount / self::CUSTOMER_LIST_PAGE_SIZE);
        $thisPage = $page;
        $entityRouter = 'oro_bugtracker_customer_edit';
        $listRouteName = 'oro_bugtracker_customer_list';
        $header = ['id', 'username', 'email', 'full name'];


        return $this->render('BugTrackerBundle:Customer:list.html.twig',
            compact(
                'collection',
                'header',
                'entityRouter',
                'maxPages',
                'thisPage',
                'listRouteName'
            )
        );
    }

    /**
     * Create customer action
     * @Route("customer/create")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        try {
            // 1) build the form
            $customer = new Customer();
            $form = $this->createForm(CustomerType::class, $customer);
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
            array('form' => $form->createView())
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

        if ($customerEntityData == null) {
            $errorMessage = 'Required customer was not found!';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $errorMessage);
            return $this->redirect('/');
        }
        $form = $this->createForm(CustomerType::class, $customerEntityData, array('validation_groups' => array('edit')));

        try {
            if ($request->getMethod() == 'POST') {
                $form->submit($request);
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
            array('form' => $form->createView())
        );
    }
}
