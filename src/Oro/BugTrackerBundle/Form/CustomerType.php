<?php
/**
 * Created by PhpStorm.
 * User: ocz
 * Date: 12.04.17
 * Time: 18:59
 */

namespace Oro\BugTrackerBundle\Form;

use Oro\BugTrackerBundle\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CustomerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $customerChoiceList = [
            Customer::ROLE_ADMIN => Customer::ROLE_ADMIN,
            Customer::ROLE_MANAGER => Customer::ROLE_MANAGER,
            Customer::ROLE_OPERATOR => Customer::ROLE_OPERATOR,
        ];

        $builder
            ->add('email', EmailType::class)
            ->add('username', TextType::class)
            ->add('fullName', TextType::class)
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'multiple' => true,
                    'choices' => $customerChoiceList,
                ]
            )
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                ]
            )
            ->add('submit', SubmitType::class, ['label' => 'Save']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Customer::class,
                'csrf_field_name' => '_token',
            ]
        );
    }
}
