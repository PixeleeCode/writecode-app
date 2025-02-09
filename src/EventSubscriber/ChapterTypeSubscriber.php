<?php

namespace App\EventSubscriber;

use App\Entity\Course;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ChapterTypeSubscriber implements EventSubscriberInterface
{
    public function preSetData(FormEvent $event): void
    {
        $course = $event->getData();
        $form = $event->getForm();

        // Checks if the Training object is "new"
        // This should be considered a new "Training"
        if (!$course || null === $course->getId()) {
            $form->add('course', EntityType::class, [
                'required' => true,
                'label' => false,
                'class' => Course::class,
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er) {
                    // Requête SQL pour le formulaire d'ajout
                    // SELECT * FROM courses
                    // LEFT JOIN chapters ON chapters.course_id = courses.id
                    // WHERE courses.draft = false AND chapters.training_id IS NULL
                    // ORDER BY courses.title ASC
                    return $er->createQueryBuilder('c')
                        ->leftJoin('c.chapters', 'ch')
                        ->andWhere('c.draft = :draft')
                        ->andWhere('ch.training IS NULL')
                        ->setParameter('draft', false)
                        ->orderBy('c.title', 'ASC');
                },
                'attr' => [
                    'class' => 'appearance-none outline-none w-full bg-white text-gray-900 ring-1 ring-gray-200 text-lg rounded py-2 px-4',
                ],
            ]);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }
}
