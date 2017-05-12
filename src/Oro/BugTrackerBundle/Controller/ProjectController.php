<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\ProjectType;
use Oro\BugTrackerBundle\Entity\Project;
use Oro\BugTrackerBundle\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @Route("project/list/{page}", name="oro_bugtracker_project_list", requirements={"page" = "\d+"}, defaults={"page" = 1})
     */
    public function listAction($page)
    {
        $em = $this->getDoctrine()->getManager();
        $entityRepository = $em->getRepository('BugTrackerBundle:Project');

        $pageTitle = 'Manage projects';
        $columns = ['id' => 'Id', 'label' => 'Label', 'summary' => 'Summary', 'code' => 'Code'];
        $actions[] = [
            'label' => 'View',
            'router' => 'oro_bugtracker_project_view',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id']
            ],
        ];
        $actions[] = [
            'label' => 'Edit',
            'router' => 'oro_bugtracker_project_edit',
            'router_parameters' => [
                ['collection_key' => 'id', 'router_key' => 'id']
            ],
        ];

        return $this->render(
            'BugTrackerBundle:Project:list.html.twig',
            [
                'page_title' => $pageTitle,
                'entity_create_router' => 'oro_bugtracker_project_create',
                'entity_repository' => $entityRepository,
                'columns' => $columns,
                'actions' => $actions,
                'current_page' => $page,
            ]
        );
    }

    /**
     * Project create action
     * @Route("project/create", name="oro_bugtracker_project_create")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

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
     * Project view action
     *
     * @Route("project/view/{id}", name="oro_bugtracker_project_view", requirements={"id" = "\d+"})
     */
    public function viewAction(Project $projectEntity, Request $request)
    {
        $membersCollection = $projectEntity->getCustomers();
        $actions = $this->getMemberGridAction($projectEntity->getId(), true, false);

        return $this->render(
            'BugTrackerBundle:Project:view.html.twig',
            array(
                'page_title' => sprintf(
                    "View Project '%s'",
                    $projectEntity->getCode()
                ),
                'entity' => $projectEntity,
                'members_grid_html' => $this->getMembersGridHtml($membersCollection, $actions),
            )
        );
    }

    /**
     * Project edit action
     *
     * @Route("project/edit/{id}", name="oro_bugtracker_project_edit", requirements={"id" = "\d+"})
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Project $projectEntity, Request $request)
    {
        $form = $this->createForm(
            ProjectType::class,
            $projectEntity,
            array(
                'validation_groups' => array('edit'),
            )
        );
        $em = $this->getDoctrine()->getManager();
        try {
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em->merge($projectEntity);

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


        $membersCollection = $projectEntity->getCustomers();
        $actions = $this->getMemberGridAction($projectEntity->getId(), true, true);

        return $this->render(
            'BugTrackerBundle:Project:edit.html.twig',
            array(
                'form' => $form->createView(),
                'page_title' => sprintf(
                    "Edit Project '%s'",
                    $projectEntity->getCode()
                ),
                'entity_id' => $projectEntity->getId(),
                'members_grid_html' => $this->getMembersGridHtml($membersCollection, $actions),
            )
        );
    }

    /**
     * Project delete action
     * @Route("project/delete/{id}", name="oro_bugtracker_project_delete", requirements={"id" = "\d+"})
     */
    public function deleteAction(Project $projectEntity, Request $request)
    {
        $actionUrl = $this->generateUrl(
            'oro_bugtracker_project_delete',
            array('id' => $projectEntity->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $form = $this->createFormBuilder($projectEntity, array('validation_groups' => array('edit')))
            ->setAction($actionUrl)
            ->add('delete', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $projectEntityId = $projectEntity->getId();
                $em->remove($projectEntity);
                $em->flush();
                $em->clear();
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', sprintf("Project '%s' was deleted successfully!", $projectEntityId));

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
     *
     * @Route("project/{id}/addmember", name="oro_bugtracker_project_addmember", requirements={"id" = "\d+"})
     */
    public function addmemberAction(Project $projectEntity, Request $request)
    {
        $response = new JsonResponse();
        $result = [];
        $result['success'] = true;
        $em = $this->getDoctrine()->getManager();

        $membersCollection = [];
        if ($request->getMethod() == 'POST') {
            $customerRepository = $em->getRepository(Customer::class);
            $requiredUsername = $request->get('username');
            $customerEntity = $customerRepository->findOneBy(['username' => $requiredUsername]);
            if ($customerEntity) {
                $projectEntity->addCustomer($customerEntity);

                $em->persist($customerEntity);
                $em->persist($projectEntity);
                $em->flush();

                $membersCollection = $projectEntity->getCustomers();
            }
        }

        $actions = $this->getMemberGridAction($projectEntity->getId(), true, true);
        $result['members_grid_html'] = $this->getMembersGridHtml($membersCollection, $actions);
        $response->setData($result);

        return $response;
    }



    /**
     * @Route("/blog/{id}/comments/{comment_id}")
     * @ParamConverter("comment", class="SensioBlogBundle:Comment", options={"id" = "comment_id"})
     */
    public function showAction(Post $post, Comment $comment)
    {
    }



    /**
     * Add new Project Member
     *
     * @Route("project/{id}/removemember/{member_id}", name="oro_bugtracker_project_removemember")
     */
    public function removememberAction(Project $projectEntity, $member_id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $customerEntity = $em->getRepository(Customer::class)->find($member_id);
        if ($customerEntity) {
            $projectEntity->removeCustomer($customerEntity);

            $em->persist($customerEntity);
            $em->persist($projectEntity);
            $em->flush();
        }

        return $this->redirectToRoute('oro_bugtracker_project_edit', array('id' => $projectEntity->getId()));
    }

    /**
     * Return members list
     *
     * @Route("project/{projectid}/members", name="oro_bugtracker_project_members", requirements={"projectid" = "\d+"})
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

    /**
     * @param $projectid
     * @param bool $useView
     * @param bool $useDelete
     * @return array
     */
    public function getMemberGridAction($projectid, $useView = true, $useDelete = true)
    {
        $actions = [];
        if ($useView) {
            $actions[] = [
                'label' => 'View',
                'router' => 'oro_bugtracker_customer_view',
                'router_parameters' => [['collection_key' => 'id', 'router_key' => 'id']],
            ];
        }
        if ($useDelete) {
            $actions[] = [
                'label' => 'Delete Member',
                'router' => 'oro_bugtracker_project_removemember',
                'router_parameters' => [
                    ['router_key' => 'id', 'router_value' => $projectid],
                    ['router_key' => 'member_id', 'collection_key' => 'id'],
                ],
            ];
        }

        return $actions;
    }

    /**
     * @param array $collection
     * @param $actions
     * @return string
     */
    protected function getMembersGridHtml($collection = [], $actions)
    {
        $columns = ['id' => 'Id', 'username' => 'User Name', 'email' => 'Email', 'fullName' => 'Full Name'];

        $membersHtml = $this->render(
            'BugTrackerBundle:Project:members.html.twig',
            compact(
                'collection',
                'columns',
                'actions'
            )
        )->getContent();

        return $membersHtml;
    }
}
