<?php
/**
 * Created by PhpStorm.
 * User: ocz
 * Date: 24.05.17
 * Time: 17:16
 */

namespace Oro\BugTrackerBundle\Twig;

use Doctrine\Common\Util\ClassUtils;

class ClassExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'class',
                function ($object) {
                    if (is_object($object)) {
                        return ClassUtils::getClass($object);
                    }

                    return false;
                }
            ),
        ];
    }
}
