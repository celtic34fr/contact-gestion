<?php

namespace Celtic34fr\ContactGestion\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Celtic34fr\ContactGestion\FormEntity\DemandesType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/** classe de base pour le formulaire de contact en frontal du site */
class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {::class
        $builder
            ->add('id', HiddenType::class, [
            ])
            ->add('nom', TextType::class, [
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
            ])
            ->add('adrCourriel', TextType::class, [
                'required' => true,
            ])
            ->add('telephone', PhoneNumberType::class, [
                'required' => false,
            ])
            ->add('contactMe', CheckboxType::class, [
                'required' => false,
                'value' => false,
            ])
            ->add('newsletter', CheckboxType::class, [
                'required' => false,
                'value' => false,
            ])
            ->add('sujet', TextType::class, [
                'required' => true,
            ])
            ->add('demande', TextareaType::class, [
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Soumettre la demande',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandesType::class,
        ]);
    }
}
