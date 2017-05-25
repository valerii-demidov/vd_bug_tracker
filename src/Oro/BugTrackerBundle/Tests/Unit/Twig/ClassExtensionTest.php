<?php
/**
 * Created by PhpStorm.
 * User: ocz
 * Date: 24.05.17
 * Time: 17:16
 */


namespace Oro\BugTrackerBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Oro\BugTrackerBundle\Twig\ClassExtension;

    {


        /**
         * Class ClassExtensionTest
         * @package Oro\BugTrackerBundle\Twig
         */
        class ClassExtensionTest extends TestCase
        {
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
                $functions = $this->classExtension->getFunctions();

                $this->assertInternalType('array', $functions);
                $this->assertArrayHasKey(0, $functions);
                $this->assertInstanceOf('\Twig_SimpleFunction', $functions[0]);
                $function = $functions[0];

                $this->assertInternalType('string', $function->getName());
                $this->assertEquals($function->getName(), 'class');
                $this->assertInternalType('callable', $function->getCallable());
            }

            /**
             * @dataProvider getCallDataProvider
             *
             * @param mixed $paramValue
             * @param bool $expected
             */
            public function testCallFunction($paramValue, $expected)
            {
                $functions = $this->classExtension->getFunctions();
                $function = $functions[0];

                $actual = call_user_func(
                    $function->getCallable(),
                    $paramValue
                );

                $this->assertEquals($actual, $expected);
            }

            /**
             * @return array
             */
            public function getCallDataProvider()
            {
                return [
                    [true, false],
                    ['', false],
                    ['true', false],
                    ['false', false],
                    [
                        new \MyProject\Proxies\__CG__\Oro\BugTrackerBundle\Tests\Unit\Twig\TestObject(),
                        'Oro\BugTrackerBundle\Tests\Unit\Twig\TestObject',
                    ],
                    [
                        new TestObject(),
                        'Oro\BugTrackerBundle\Tests\Unit\Twig\TestObject',
                    ]
                ];
            }
        }

        class TestObject
        {
        }
    }

namespace MyProject\Proxies\__CG__\Oro\BugTrackerBundle\Tests\Unit\Twig;

{

    class TestObject
    {
    }
}