<?php
/**
 * Created by PhpStorm.
 * User: ocz
 * Date: 24.05.17
 * Time: 17:16
 */

namespace Oro\BugTrackerBundle\Twig;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Oro\BugTrackerBundle\Twig\ActivityExtension;
use Oro\BugTrackerBundle\Entity\Activity;

class ActivityExtensionTest extends TestCase
{
    /** @var  EntityManagerInterface */
    protected $em;

    /** @var  ActivityExtension */
    protected $activityExtension;

    public function setUp()
    {
        $this->em = $this
            ->getMockBuilder(ActivityExtensionTest::class)
            ->getMock();
        $this->activityExtension = new ActivityExtension();
    }

    public function testGetFunctions()
    {
        $this->assertEquals(
            [
                new \Twig_SimpleFunction(
                    'activity_template_detect',
                    [$this->activityExtension, 'getActivityTemplate']
                ),
            ],
            $this->activityExtension->getFunctions()
        );
    }

    public function testGetActivityTemplate()
    {
        $entity = 'Comment';
        $type = Activity::TYPE_CREATED;
        $expectedActivityTemplatePath = "BugTrackerBundle:Activity/$entity:$type.html.twig";


        $activity = new Activity();
        $activity->setEntity($entity);
        $activity->setType($type);
        $actualActivityTemplatePath = $this->activityExtension->getActivityTemplate($activity);
        $this->assertEquals($expectedActivityTemplatePath, $actualActivityTemplatePath);
    }
}