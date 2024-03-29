<?php

namespace Celtic34fr\ContactGestion\Form;

use Celtic34fr\ContactGestion\Entity\Category;
use Celtic34fr\ContactGestion\FormEntity\SearchForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** classe support du formulaire de recherche dans des demandes de contact et/ou des réponses */
class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('searchText', TextType::class, [
                'required' => false,
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
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
