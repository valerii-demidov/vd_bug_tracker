<?php

namespace Oro\BugTrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oro\BugTrackerBundle\Entity\Activity;
use Oro\BugTrackerBundle\Entity\Issue;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('BugTrackerBundle:Default:index.html.twig', [
            'issue_class' => Issue::class,
            'activity_class' => Activity::class,
        ]);
    }
}
