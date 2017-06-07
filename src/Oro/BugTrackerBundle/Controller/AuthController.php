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

class AuthController extends Controller
{


    /**
     * @Route("/auth/login")
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $auth = new Auth();

        $actionUrl = $this->generateUrl('oro_bugtracker_auth_loginpost', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $form = $this->createForm(
            LoginForm::class,
            $auth,
            [
                'action' => $actionUrl,
            ]
        );

        return $this->render(
            'BugTrackerBundle:Auth:login.html.twig',
            [
                'form' => $form->createView(),
                'error' => $error,
                'last_username' => $lastUsername,
            ]
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
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function registerAction(Request $request)
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        try {
            $formHandler = $this->getCustomerHandler();
            if ($request->getMethod() === 'POST') {
                if ($formHandler->handleCreateForm($form)) {
                    $this->addFlash('success', 'User has been created successfully! Please log in.');

                    return $this->redirectToRoute('oro_bugtracker_customer_edit', ['id' => $customer->getId()]);
                }

                $request->getSession()
                    ->getFlashBag()
                    ->add('success', "User wasn't created successfully!");

                return $this->redirectToRoute('oro_bugtracker_auth_login');
            }
        } catch (\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        return $this->render(
            'BugTrackerBundle:Auth:register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/auth/logout")
     */
    public function logoutAction()
    {
        return $this->redirectToRoute('oro_bugtracker_auth_login');
    }

    /**
     * @Route("/auth/loginpost")
     */
    public function loginpostAction()
    {
        return $this->redirectToRoute('bug_tracker_homepage');
    }

    public function getCustomerHandler()
    {
        return $this->get('oro_bugtracker.handler.customer');
    }
}
