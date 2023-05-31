<?php

namespace Celtic34fr\ContactGestion\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Celtic34fr\ContactGestion\FormEntity\MailingExtract;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MailingExtractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fields', ChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'label' => "Champs à inclure dans l'extraction",
                'choices' => [
                    'Nom' => 'nom',
                    'Prénom' => 'prenom',
                    'Nom complet' => 'fullname',
                    'Adresse Courielle' => 'courriel',
                    'Téléphone' => 'telephone',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MailingExtract::class,
        ]);
    }
}
