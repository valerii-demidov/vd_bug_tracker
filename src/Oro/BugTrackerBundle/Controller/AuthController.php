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
        $em = $this->getDoctrine()->getEntityManager();

        // 1) build the form
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        try {
            // 2) handle the submit (will only happen on POST)
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // 3) Encode the password (you could also do this via Doctrine listener)
                $password = $this->get('security.password_encoder')
                    ->encodePassword($customer, $customer->getPlainPassword());
                $customer->setPassword($password);
                $customer->setRoles([Customer::ROLE_OPERATOR]);

                // 4) save the User!
                $em->persist($customer);
                $em->flush();

                $this->addFlash('success', 'User has been created successfully! Please log in.');

                return $this->redirectToRoute('oro_bugtracker_auth_login');
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
}
