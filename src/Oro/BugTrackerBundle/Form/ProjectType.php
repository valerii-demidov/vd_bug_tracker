<?php
/**
 * Created by PhpStorm.
 * User: ocz
 * Date: 12.04.17
 * Time: 18:59
 */

namespace Oro\BugTrackerBundle\Form;

use Oro\BugTrackerBundle\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class)
            ->add('summary', TextType::class)
            ->add('code',TextType::class)
            ->add('submit', SubmitType::class, array('label' => 'Save'));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class,
            'csrf_field_name' => '_token'
        ));
    }
}