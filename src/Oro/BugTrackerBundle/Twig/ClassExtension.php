<?php
/**
 * Created by PhpStorm.
 * User: ocz
 * Date: 24.05.17
 * Time: 17:16
 */

namespace Oro\BugTrackerBundle\Twig;


class ClassExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('class', [$this, 'getClass']),
        ];
    }

    /**
     * @param $object
     * @return mixed
     */
    public function getClass($object)
    {
        return get_class($object);
    }
}