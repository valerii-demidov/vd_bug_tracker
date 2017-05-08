<?php
/**
 * Created by PhpStorm.
 * User: ocz
 * Date: 12.04.17
 * Time: 18:59
 */

namespace Oro\BugTrackerBundle\Form;

use Oro\BugTrackerBundle\Entity\Issue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IssueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class)
            ->add('project',TextType::class)
            ->add('summary', TextType::class)
            ->add('description',TextareaType::class)
            ->add('type',TextType::class)  // todo - dropdown
            ->add('priority',TextType::class) // todo - dropdown
            ->add('status',TextType::class) // todo - dropdown
            ->add('resolution',TextType::class) // todo - dropdown
            ->add('reporter',TextType::class) // todo - author id  - sting - neverchange
            ->add('assignee',TextType::class) // todo - assignee id - dropdown
            ->add('parent',TextType::class) // todo -id - dropdown
            ->add('submit', SubmitType::class, array('label' => 'Create'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Issue::class,
            'csrf_field_name' => '_token'
        ));
    }
}