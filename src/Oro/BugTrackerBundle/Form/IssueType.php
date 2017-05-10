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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class IssueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $issueChoiceList = [
            Issue::TYPE_STORY => 'story',
            Issue::TYPE_TASK => 'task',
            Issue::TYPE_SUBTASK => 'subtask',
            Issue::TYPE_BUG => 'bug',
        ];

        $priorityChoiceList = [
            Issue::PRIORITY_LOW => 'low',
            Issue::PRIORITY_MEDIUM => 'medium',
            Issue::PRIORITY_HIGH => 'high'
        ];

        $statusChoiceList = [
            Issue::STATUS_OPEN => 'Open',
            Issue::STATUS_IN_PROGRESS => 'In Progress',
            Issue::STATUS_RESOLVED => 'Resolved'
        ];

        $resolutionChoiceList = [
            Issue::RESOLUTION_UNRESOLVED => 'Unresolved',
            Issue::RESOLUTION_RESOLVED => 'Resolved',

        ];

        $builder
            ->add('code', TextType::class)
            ->add(
                'project',
                EntityType::class,
                array(
                    'class' => 'Oro\BugTrackerBundle\Entity\Project',
                    'choice_label' => 'label',
                )
            )
            /*->add(
                'reporter',
                EntityType::class,
                array(
                    'class' => 'Oro\BugTrackerBundle\Entity\Customer',
                    'property' => 'username'
                )
            )*/
            ->add(
                'assignee',
                EntityType::class,
                array(
                    'class' => 'Oro\BugTrackerBundle\Entity\Customer',
                    'property' => 'username',
                )
            )
            ->add('summary', TextType::class)
            ->add('description', TextareaType::class)
            ->add(
                'type',
                ChoiceType::class,
                array(
                    'choices' => $issueChoiceList,
                )
            )
            ->add(
                'priority',
                ChoiceType::class,
                array(
                    'choices' => $priorityChoiceList,
                )
            )
            ->add(
                'status',
                ChoiceType::class,
                array(
                    'choices' => $statusChoiceList,
                )
            )
            ->add(
                'resolution',
                ChoiceType::class,
                array(
                    'choices' => $resolutionChoiceList,
                )
            )
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