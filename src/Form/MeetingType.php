<?php

namespace App\Form;

use App\Entity\Meeting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок',
            ])
            ->add('location', TextType::class, [
                'label' => 'Локация',
            ])
            ->add('beginsAt', DateTimeType::class, [
                'label' => 'Дата встречи',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
            ])
            ->add('mainPhotoFile', FileType::class, [
                'label' => 'Основное фото',
                'required' => false,
            ])
            ->add('galleryPhotoFiles', FileType::class, [
                'label' => 'Дополнительные изображения',
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Meeting::class,
        ]);
    }
}
