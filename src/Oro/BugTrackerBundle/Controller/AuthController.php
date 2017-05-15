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
use Oro\BugTrackerBundle\Entity\Auth;
use Oro\BugTrackerBundle\Form\LoginForm;
use Oro\BugTrackerBundle\Entity\Customer;
use Oro\BugTrackerBundle\Form\CustomerType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;

class AuthController extends Controller
{


    /**
     * @Route("/auth/login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $auth = new Auth();

        $actionUrl = $this->generateUrl('oro_bugtracker_auth_loginpost', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $form = $this->createForm(LoginForm::class, $auth, array(
            'action' => $actionUrl
        ));

        return $this->render(
            'BugTrackerBundle:Auth:login.html.twig',
            array(
                'form' => $form->createView(),
                'error' => $error,
                'last_username' => $lastUsername
            )
        );
    }

    /**
     * @Route("/auth/forgotpassword")
     */
    public function forgotpasswordAction()
    {
        //todo
    }

    /**
     * @Route("/auth/register")
     */
    public function registerAction(Request $request)
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        try {
            $formHandler = $this->getCustomerHandler();
            if ($request->getMethod() == 'POST') {
                if ($formHandler->handleCreateForm($form)) {
                    $this->addFlash('success', 'User has been created successfully! Please log in.');

                    return $this->redirectToRoute('oro_bugtracker_customer_edit', array('id' => $customer->getId()));
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('success', "User wasn't created successfully!");

                    return $this->redirectToRoute('oro_bugtracker_auth_login');
                }
            }
        } catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        return $this->render(
            'BugTrackerBundle:Auth:register.html.twig',[
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/auth/logout")
     */
    public function logoutAction()
    {
        return new Response('asd');
    }

    /**
     * @Route("/auth/loginpost")
     */
    public function loginpostAction()
    {
        return new Response();
    }

    public function getCustomerHandler()
    {
        return $this->get('oro_bugtracker.handler.customer');
    }
}
