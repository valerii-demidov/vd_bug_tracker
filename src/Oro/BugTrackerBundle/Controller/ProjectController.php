<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\ProjectType;
use Oro\BugTrackerBundle\Entity\Project;
use Oro\BugTrackerBundle\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class ProjectController extends Controller
{
    CONST PROJECT_LIST_PAGE_SIZE = 3;

    /**
     * Project list action
     * @Route("project/list/{page}", requirements={"page" = "\d+"}, defaults={"page" = 1})
     */
    public function listAction($page)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $queryBuilder = $em
            ->getRepository('BugTrackerBundle:Project')
            ->createQueryBuilder('pr');
        $queryBuilder->select(['pr.id', 'pr.label', 'pr.summary', 'pr.code']);

        $paginator = new Paginator($queryBuilder, false);

        $collection = $paginator
            ->getQuery()
            ->setFirstResult(self::PROJECT_LIST_PAGE_SIZE * ($page - 1))
            ->setMaxResults(self::PROJECT_LIST_PAGE_SIZE)
            ->getArrayResult();

        $queryBuilder = $em
            ->getRepository('BugTrackerBundle:Project')
            ->createQueryBuilder('pr');
        $queryBuilder->select('count(pr.id)');
        $totalCount = $queryBuilder->getQuery()->getSingleScalarResult();

        $maxPages = ceil($totalCount / self::PROJECT_LIST_PAGE_SIZE);
        $thisPage = $page;
        $entityCreateRouter = 'oro_bugtracker_project_create';
        $entityRouter = 'oro_bugtracker_project_edit';
        $listRouteName = 'oro_bugtracker_project_list';
        $header = ['id', 'Label', 'Summary', 'Code'];
        $page_title = 'Manage projects';

        return $this->render(
            'BugTrackerBundle:Project:list.html.twig',
            compact(
                'entityCreateRouter',
                'page_title',
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
     * Project create action
     * @Route("project/create")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        // 1) build the form
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        try {
            // 2) handle the submit (will only happen on POST)
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($project);
                $em->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->add('success', 'Project has been created successfully!');

                return $this->redirectToRoute('oro_bugtracker_project_edit', array('id' => $project->getId()));
            }

        } catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        return $this->render(
            'BugTrackerBundle:Project:create.html.twig',
            array(
                'form' => $form->createView(),
                'page_title' => 'New Project',
            )
        );
    }

    /**
     * Project edit action
     *
     * @Route("project/edit/{id}",requirements={"id" = "\d+"})
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getEntityManager();
        $projectEntityData = $em->getRepository(Project::class)->find($id);

        if (!$projectEntityData) {
            $errorMessage = 'Required project was not found!';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $errorMessage);

            return $this->redirect('/');
        }
        $form = $this->createForm(
            ProjectType::class,
            $projectEntityData,
            array(
                'validation_groups' => array('edit'),
            )
        );

        try {
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em->merge($projectEntityData);

                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Project has been updated successfully!');
                    $em->flush();
                }
            }
        } catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        $projectRepository = $em->getRepository(Project::class);
        $membersCollection = $projectRepository->find($id)->getCustomers()->toArray();
        $membersCollectionAssoc = $projectRepository->convertCollectionToAssoc(
            $membersCollection,
            ['id', 'username', 'email', 'fullName']
        );

        return $this->render(
            'BugTrackerBundle:Project:edit.html.twig',
            array(
                'form' => $form->createView(),
                'page_title' => sprintf("Edit Project '%s'", $projectEntityData->getId()),
                'entity_id' => $projectEntityData->getId(),
                'members_grid_html' => $this->getMembersGridHtml($membersCollectionAssoc),
            )
        );
    }

    /**
     * Project delete action
     * @Route("project/delete/{id}",requirements={"id" = "\d+"})
     */
    public function deleteAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository(Project::class)->find($id);
        if (!$project) {
            throw $this->createNotFoundException(
                'No project found for id '.$id
            );
        }

        $actionUrl = $this->generateUrl(
            'oro_bugtracker_project_delete',
            array('id' => $project->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $form = $this->createFormBuilder($project, array('validation_groups' => array('edit')))
            ->setAction($actionUrl)
            ->add('delete', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $projectId = $project->getId();
                $em->remove($project);
                $em->flush();
                $em->clear();
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', sprintf("Project '%s' was deleted successfully!", $projectId));

                return $this->redirectToRoute('oro_bugtracker_project_list');
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
     * Add new Project Member
     * @Route("project/{projectid}/addmember",requirements={"projectid" = "\d+"})
     */
    public function addmemberAction($projectid, Request $request)
    {
        $response = new JsonResponse();
        $result = [];
        $result['success'] = true;
        $em = $this->getDoctrine()->getManager();
        $membersCollection = [['id' => 1, 123, 123, 123]];

        if ($request->getMethod() == 'POST') {
            $customerRepository = $em->getRepository(Customer::class);
            $requiredUsername = $request->get('username');
            $customerEntity = $customerRepository->findOneBy(['username' => $requiredUsername]);
            if ($customerEntity) {
                $projectRepository = $em->getRepository(Project::class);
                $projectEntity = $projectRepository->find($projectid);
                $projectEntity->addCustomer($customerEntity);

                $em->persist($customerEntity);
                $em->persist($projectEntity);
                $em->flush();

                $projectRepository = $em->getRepository(Project::class);
                $membersCollection = $projectRepository->find($projectid)->getCustomers()->toArray();
                $membersCollectionAssoc = $projectRepository->convertCollectionToAssoc(
                    $membersCollection,
                    ['id', 'username', 'email', 'fullName']
                );
            }
        }

        $result['members_grid_html'] = $this->getMembersGridHtml($membersCollectionAssoc);
        $response->setData($result);

        return $response;
    }

    /**
     * Return members list
     *
     * @Route("project/{projectid}/members", requirements={"projectid" = "\d+"})
     */
    public function membersAction($projectid, Request $request)
    {
        $response = new JsonResponse();
        $result = [];
        $result['success'] = true;
        $result['members_list'] = '';

        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();
            $customerRepository = $em->getRepository(Customer::class);
            $usernameTemplate = $request->get('username');
            if (!empty($usernameTemplate)) {
                $conditionCollection = ['username' => ['like' => $usernameTemplate.'%']];
                $findResult = $customerRepository->findByCondition($conditionCollection);
                $findResult = (is_array($findResult)) ? $findResult : [$findResult];

                $memberListAssoc = $customerRepository->convertCollectionToAssoc($findResult, ['username']);
                $result['members_list'] = (empty($memberListAssoc)) ? [] : array_column($memberListAssoc, 'username');
            }
        }

        $response->setData($result);

        return $response;
    }

    protected function getMembersGridHtml($collection = [])
    {
        $entityRouter = 'oro_bugtracker_customer_edit';
        $header = $memberHeaderLabels = ['id', 'username', 'email', 'Full Name'];;

        $membersHtml = $this->render(
            'BugTrackerBundle:Project:members.html.twig',
            compact(
                'collection',
                'header',
                'entityRouter'
            )
        )->getContent();

        return $membersHtml;
    }
}
