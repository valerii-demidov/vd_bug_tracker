<?php

namespace Oro\BugTrackerBundle\Controller;

use Oro\BugTrackerBundle\Form\ProjectType;
use Oro\BugTrackerBundle\Entity\Project;
use Oro\BugTrackerBundle\Entity\Customer;
use Oro\BugTrackerBundle\Entity\Activity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class ProjectController extends Controller
{
    CONST PROJECT_LIST_PAGE_SIZE = 3;

    /**
     * Project list action
     * @Route("project/list/", name="oro_bugtracker_project_list")
     */
    public function listAction()
    {

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
                'entity_class' => Project::class,
                'columns' => $columns,
                'actions' => $actions,
                'paginator_var' => 'project_p'
            ]
        );
    }

    /**
     * Project create action
     * @Route("project/create", name="oro_bugtracker_project_create")
     */
    public function createAction(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        try {
            $formHandler = $this->getProjectHandler();
            if ($request->getMethod() == 'POST') {
                if ($formHandler->handleCreateForm($form)) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Project has been created successfully!');

                    return $this->redirectToRoute(
                        'oro_bugtracker_project_edit',
                        array('id' => $project->getId())
                    );
                }
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
    public function viewAction(Project $project, Request $request)
    {
        $membersCollection = $project->getCustomers();
        $actions = $this->getMemberGridAction($project->getId(), true, false);

        $activityRepository = $this->getDoctrine()->getRepository(Activity::class);
        $activityCollection = $activityRepository->getActivityProjectCollection(
            $project
        );

        return $this->render(
            'BugTrackerBundle:Project:view.html.twig',
            array(
                'page_title' => sprintf(
                    "View Project '%s'",
                    $project->getCode()
                ),
                'entity' => $project,
                'members_grid_html' => $this->getMembersGridHtml($membersCollection, $actions),
                'activity_collection' => $activityCollection,
                'activity_paginator_var' => 'activity_p'
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
        try {
            if ($request->getMethod() == 'POST') {
                $formHandler = $this->getProjectHandler();

                if ($formHandler->handleEditForm($form)) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', 'Project has been updated successfully!');
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('error', "Project wasn't update successfully!");
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

        if ($request->getMethod() == 'POST') {
            $projectEntityId = $projectEntity->getId();
            $formHandler = $this->getProjectHandler();

            if ($formHandler->handleDeleteForm($form)) {
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

        if ($request->getMethod() == 'POST') {
            $formHandler = $this->getProjectHandler();
            $formHandler->handleAddMemberForm($projectEntity);
        }

        $actions = $this->getMemberGridAction($projectEntity->getId(), true, true);
        $result['members_grid_html'] = $this->getMembersGridHtml($projectEntity->getCustomers(), $actions);
        $response->setData($result);

        return $response;
    }



    /**
     * @Route("/blog/{id}/comments/{comment_id}")
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
            $projectRepository = $this->getDoctrine()->getRepository(Project::class);
            $requiredUsername = $request->get('username');
            $membersList = $projectRepository->getProjectMembersListBySlug($requiredUsername);
            if ($membersList) {
                $result['members_list'] = $membersList;
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

    /**
     * @return object|\Oro\BugTrackerBundle\Form\Handler\ProjectHandler
     */
    public function getProjectHandler()
    {
        return $this->get('oro_bugtracker.handler.project');
    }
}
