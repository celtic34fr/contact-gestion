<?php

namespace Celtic34fr\ContactGestion\Form;

use Celtic34fr\ContactGestion\Enum\NewsEnums;
use Celtic34fr\ContactGestion\FormEntity\MailingExtract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailingExtractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $newsEnums = NewsEnums::reflexArray();

        $builder
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => $newsEnums,
            ])
            ->add('list', HiddenType::class, [
                'required' => false,
            ])
            ->add('fileName', TextType::class, [
                'label' => 'Nom du fichier à créer',
                "required" => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MailingExtract::class,
        ]);
    }

}
