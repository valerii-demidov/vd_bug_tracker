<?php

namespace Oro\BugTrackerBundle\Form\Handler;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Oro\BugTrackerBundle\Entity\Customer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class CustomerHandler
{
    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var UserPasswordEncoder */
    protected $passwordEncoder;

    /**
     * ProjectHandler constructor.
     * @param RequestStack $request
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        RequestStack $request,
        EntityManagerInterface $manager,
        UserPasswordEncoder $passwordEncoder
    ) {
        $this->request = $request;
        $this->manager = $manager;
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * @param $form
     * @return bool
     */
    public function handleCreateForm($form)
    {
        $request = $this->request->getCurrentRequest();
        $form->handleRequest($request);

        $customer = $form->getData();
        if ($customer instanceof Customer) {
            if ($form->isSubmitted() && $form->isValid()) {
                $password = $this->passwordEncoder->encodePassword($customer, $customer->getPlainPassword());
                $customer->setPassword($password);

                $this->manager->persist($customer);
                $this->manager->flush();
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param $form
     * @return bool
     */
    public function handleEditForm($form)
    {
        $request = $this->request->getCurrentRequest();
        $form->handleRequest($request);

        $customer = $form->getData();
        if ($form->isValid()) {
            $plainPassword = $form->get('plainPassword');
            if (!$plainPassword->isEmpty()) {
                $password = $this->passwordEncoder->encodePassword($customer, $customer->getData());
                $customer->setPassword($password);
            }

            $this->manager->merge($customer);
            $this->manager->flush();
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param $form
     * @return bool
     */
    public function handleDeleteForm($form)
    {
        $request = $this->request->getCurrentRequest();
        $form->handleRequest($request);

        $customer = $form->getData();
        if ($form->isValid()) {
            $this->manager->remove($customer);
            $this->manager->flush();
            $this->manager->clear();
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param $projectEntity
     * @return bool
     */
    public function handleAddMemberForm($projectEntity)
    {
        $request = $this->request->getCurrentRequest();

        $customerRepository = $this->manager->getRepository(Customer::class);
        $requiredUsername = $request->get('username');
        $customerEntity = $customerRepository->findOneBy(['username' => $requiredUsername]);

        if ($customerEntity) {
            $projectEntity->addCustomer($customerEntity);
            $this->manager->persist($customerEntity);
            $this->manager->persist($projectEntity);
            $this->manager->flush();

            return true;
        }

        return false;
    }
}
