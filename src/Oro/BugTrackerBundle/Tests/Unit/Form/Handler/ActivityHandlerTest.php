<?php

namespace Oro\BugTrackerBundle\Tests\Unit\Form\Handler;

use Oro\BugTrackerBundle\Entity\Customer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Oro\BugTrackerBundle\Entity\Activity;
use Oro\BugTrackerBundle\Entity\Issue;
use Oro\BugTrackerBundle\Entity\Project;
use Oro\BugTrackerBundle\Form\Handler\ActivityHandler;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ActivityHandlerTest extends TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject|FormInterface */
    protected $form;

    /** @var  Request */
    protected $request;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|ObjectManager */
    protected $manager;

    /** @var  \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var  TokenStorage */
    protected $securityToken;

    /** @var  ActivityHandler */
    protected $activityHandler;

    /** @var  UsernamePasswordToken */
    protected $usernamePasswordToken;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new Request();

        $this->manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->usernamePasswordToken = $this->getMockBuilder(
            'Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->usernamePasswordToken
            ->method('getUser')
            ->willReturn(new Customer());

        $this->securityToken = $this->getMockBuilder(
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage'
        )->getMock();
        $this->securityToken->method('getToken')->willReturn($this->usernamePasswordToken);

        $this->activityHandler = new ActivityHandler(
            $this->em,
            $this->securityToken
        );
    }

    public function testHandleIssueActivity()
    {
        $project = new Project();
        $issue = new Issue();
        $issue->setProject($project);

        $type = Activity::TYPE_CREATED;

        $result = $this->activityHandler->handleIssueActivity($issue, $type, $diffData = []);
        $this->assertTrue($result);

        return true;
    }

    //////////////////////////////////////////////////////////////////////////////////////////
    /* public function handleIssueActivity(Issue $issue, $type, $diffData = [])
     {
         $activity = new Activity();
         $activity->setIssue($issue);
         $activity->setProject($issue->getProject());

         $entityName = 'Issue';
         $this->doSave($activity, $issue, $entityName, $type, $diffData);
     }


     public function handleCommentActivity(Comment $comment, $type, $diffData = [])
     {
         $activity = new Activity();
         $activity->setIssue($comment->getIssue());
         $activity->setProject($comment->getProject());

         $entityName = 'Comment';
         $this->doSave($activity, $comment, $entityName, $type, $diffData);
     }


     protected function doSave(Activity $activity, $entity, $entityName, $type, $diffData = [])
     {

         $currentEntityData = $entity->__toArray();
         $afterData = [];
         if ($type == Activity::TYPE_UPDATED) {
             foreach ($diffData as $fieldName => $fieldValue) {
                 $afterData[$fieldName] = $currentEntityData[$fieldName];
             }
         }

         $fullDiffData['diff_fields'] = array_keys($diffData);
         $fullDiffData['before_data'] = $diffData;
         $fullDiffData['after_data'] = $afterData;

         // author of activity
         $author = $this->securityToken->getToken()->getUser();
         $activity->setCustomer($author);
         $activity->setEntity($entityName);
         $activity->setType($type);

         $currentDate = new \DateTime();
         $activity->setDiffData($fullDiffData);
         $activity->setDate($currentDate);

         $this->manager->persist($activity);
         $this->manager->flush();
     }*/
}
