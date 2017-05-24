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
use Oro\BugTrackerBundle\Twig\ClassExtension;

class ClassExtensionTest extends TestCase
{
    /** @var  EntityManagerInterface */
    protected $em;

    /** @var  ClassExtension */
    protected $classExtension;

    public function setUp()
    {
        $this->em = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $this->classExtension = new ClassExtension();
    }

    public function testGetFunctions()
    {
        $this->assertEquals(
            [
                new \Twig_SimpleFunction(
                    'class',
                    function ($object) {
                    }
                ),
            ],
            $this->classExtension->getFunctions()
        );
    }
}