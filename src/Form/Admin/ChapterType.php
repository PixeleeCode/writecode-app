<?php

namespace App\Form\Admin;

use App\Entity\Chapter;
use App\Entity\Course;
use App\EventSubscriber\ChapterTypeSubscriber;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $training = $options['training'];

        $builder
            ->add('course', EntityType::class, [
                'required' => true,
                'label' => false,
                'class' => Course::class,
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er) use ($training) {
                    // Requête SQL pour le formulaire d'édition
                    // SELECT * FROM courses
                    // LEFT JOIN chapters ON chapters.course_id = courses.id
                    // WHERE courses.draft = false AND chapters.training_id IS NULL OR chapters.training_id = ?
                    // ORDER BY courses.title ASC
                    return $er->createQueryBuilder('c')
                        ->leftJoin('c.chapters', 'ch')
                        ->andWhere('c.draft = :draft')
                        ->andWhere('ch.training IS NULL')
                        ->orWhere('ch.training = :training')
                        ->setParameters([
                            'draft' => false,
                            'training' => $training,
                        ])
                        ->orderBy('c.title', 'ASC');
                },
                'attr' => [
                    'class' => 'appearance-none outline-none w-full bg-white text-gray-900 ring-1 ring-gray-200 text-lg rounded py-2 px-4',
                ],
            ])
            ->add('position', HiddenType::class)
            ->addEventSubscriber(new ChapterTypeSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Chapter::class,
            'training' => null,
        ]);
    }
}
