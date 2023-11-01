<?php

namespace Bolt\Extension\Celtic34fr\ContactGestion\Twig\Runtime;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\RuntimeExtensionInterface;

class SymfonyAdvanceExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function twigFilter_field_error(FormInterface $field)
    {
        return $field->getErrors();
    }

    public function twigFunction_formErrors(FormView $form)
    {
        $formFieldsNames = [];
        $formErrors = [];

        $formChildren = $form->children;
        foreach ($formChildren as $formFieldName => $formChild) {
            if (array_key_exists('errors', $formChild->vars)) {
                $formFieldErrors = (string) $formChild->vars["errors"];
                if ($formFieldErrors) {
                    $formErrors[$formFieldName] = $formFieldErrors;
                }
            }
        }
        return $formErrors;
    }
}
