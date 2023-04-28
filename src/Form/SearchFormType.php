<?php

namespace Celtic34fr\ContactGestion\Form;

use Symfony\Component\Form\AbstractType;
use Celtic34fr\ContactGestion\Entity\Categories;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Celtic34fr\ContactGestion\FormEntity\SearchForm;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('searchText', TextType::class, [
                'required' => false,
            ])
            ->add('categories', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'category',
                'choice_value' => 'category',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
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
