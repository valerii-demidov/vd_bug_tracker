<?php
/**
 * Created by PhpStorm.
 * User: ocz
 * Date: 11.04.17
 * Time: 15:44
 */

namespace Oro\BugTrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Oro\BugTrackerBundle\Entity\AdminLogin;
use Oro\BugTrackerBundle\Form\LoginForm;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class AuthController extends Controller
{
    /**
     * @Route("/auth/index")
     */
    public function indexAction()
    {
        $adminLogin = new AdminLogin();
        $form = $this->createForm(LoginForm::class, $adminLogin);

        return $this->render(
            'BugTrackerBundle:Auth:login.html.twig',
            array('form' => $form->createView())
        );
    }

    public function forgotpasswordAction()
    {
        //todo
    }
}
