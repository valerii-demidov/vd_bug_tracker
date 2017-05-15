<?php

namespace Oro\BugTrackerBundle\Form\Handler;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Oro\BugTrackerBundle\Entity\Project;
use Oro\BugTrackerBundle\Entity\Customer;

class ProjectHandler
{


    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /**
     * ProjectHandler constructor.
     * @param RequestStack $request
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        RequestStack $request,
        EntityManagerInterface $manager
    ) {
        $this->request = $request;
        $this->manager = $manager;
    }


    /**
     * @param $form
     * @return bool
     */
    public function handleCreateForm($form)
    {
        $request = $this->request->getCurrentRequest();
        $form->handleRequest($request);

        $project = $form->getData();
        if ($project instanceof Project) {

            if ($form->isSubmitted() && $form->isValid()) {
                $this->manager->persist($project);
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

        $project = $form->getData();
        if ($form->isValid()) {
            $this->manager->merge($project);
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

        $project = $form->getData();
        if ($form->isValid()) {
            $this->manager->remove($project);
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
