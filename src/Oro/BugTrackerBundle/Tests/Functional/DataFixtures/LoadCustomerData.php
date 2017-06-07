<?php
/**
 * Created by PhpStorm.
 * User: ocz
 * Date: 29.05.17
 * Time: 19:21
 */

namespace Oro\BugTrackerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\BugTrackerBundle\Entity\Customer;
use Oro\BugTrackerBundle\Entity\Project;
use Oro\BugTrackerBundle\Entity\Issue;
use Oro\BugTrackerBundle\Entity\Activity;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Oro\BugTrackerBundle\Event\IssueBeforeCreateEvent;

class LoadCustomerData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface
{
    const TEST_USER_NAME = 'tester';
    const TEST_USER_EMAIL = 'tester@ra.ru';
    const TEST_USER_PASSWORD = '123';
    const TEST_USER_FULLANME = 'tester_fullname';

    const TEST_USER_PROJECT_CODE = 'tester_user_project';
    const TEST_USER_PROJECT_LABLEL = 'tester_user_project_label';
    const TEST_USER_PROJECT_SUMMARY = 'test_summary';

    const TEST_USER_ISSUE_SUMMARY = 'test_issue_summary';
    const TEST_USER_ISSUE_CODE = 'test_issue_code';
    const TEST_USER_ISSUE_PRIORITY = 'low';
    const TEST_USER_ISSUE_STATUS = 'open';
    const TEST_USER_ISSUE_RESOLUTION = 'unresolved';
    const TEST_USER_ISSUE_TYPE = 'task';
    const TEST_USER_ISSUE_DESCRIPTION = 'test_issue_description';

    /** @var  ContainerInterface */
    private $container;

    /** @var  IssueBeforeCreateEvent */
    private $dispatcher;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        //$this->dispatcher = $this->container->get('event_dispatcher');
    }


    public function load(ObjectManager $manager)
    {
        $passwordEncoder = $this->container->get('security.password_encoder');

        //create customer
        $user = new Customer();
        $user->setUsername(self::TEST_USER_NAME);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setEmail(self::TEST_USER_EMAIL);
        $user->setFullName(self::TEST_USER_FULLANME);
        $encodePassword = $passwordEncoder->encodePassword($user, self::TEST_USER_PASSWORD);
        $user->setPassword($encodePassword);

        $manager->persist($user);
        $manager->flush();

        //create project
        $project = new Project();
        $project->setCode(self::TEST_USER_PROJECT_CODE);
        $project->setLabel(self::TEST_USER_PROJECT_LABLEL);
        $project->setSummary(self::TEST_USER_PROJECT_SUMMARY);

        $manager->persist($project);
        $manager->flush();

        //create issue
        $issue = new Issue();
        $issue->setSummary(self::TEST_USER_ISSUE_SUMMARY);
        $issue->setCode(self::TEST_USER_ISSUE_CODE);
        $issue->setAssignee($user);
        $issue->setPriority(self::TEST_USER_ISSUE_PRIORITY);
        $issue->addCollaboration($user);
        $issue->setProject($project);
        $issue->setType(self::TEST_USER_ISSUE_TYPE);
        $issue->setStatus(SELF::TEST_USER_ISSUE_STATUS);
        $issue->setDescription(self::TEST_USER_ISSUE_DESCRIPTION);
        $issue->setReporter($user);
        $issue->setResolution(self::TEST_USER_ISSUE_RESOLUTION);
        //$this->dispatcher->dispatch(IssueBeforeCreateEvent::EVENT_NAME, new IssueBeforeCreateEvent($issue));
        $manager->persist($issue);

        $activity = new Activity();
        $activity->setEntity('Comment');
        $activity->setType(Activity::TYPE_CREATED);
        $activity->setCustomer($user);
        $activity->setProject($project);
        $activity->setIssue($issue);
        $manager->persist($activity);
        $activity->setDiffData([]);
        $activity->setDate(new \DateTime());

        $manager->flush();
    }
}
