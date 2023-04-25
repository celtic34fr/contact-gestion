<?php

namespace Celtic34fr\ContactGestion\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('searchText', TextType::class, [
            'required' => false,
            'mapped' => false,
            ])
        ->add('submit', SubmitType::class, [
            'label' => 'Soumettre la recherche',
            'mapped' => false,
            ])
        ->add('reset', SubmitType::class, [
            'label' => 'Supprimer les Critères',
            'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}