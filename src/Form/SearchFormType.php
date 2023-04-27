<?php

namespace Celtic34fr\ContactGestion\Form;

use Celtic34fr\ContactGestion\FormEntity\SearchForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('searchText', TextType::class, [
                'required' => false,
            ])
            ->add('categories', ChoiceType::class, [
                'choice_label' => 'category',
                'choice_value' => 'category',
                'mapped' => false,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Exécuter la recherche',
            ])
            ->add('reset', SubmitType::class, [
                'label' => 'Supprimer les critères',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchForm::class,
        ]);
    }

}
