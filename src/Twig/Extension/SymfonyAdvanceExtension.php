<?php

namespace Celtic34fr\ContactGestion\Twig\Extension;

use Celtic34fr\ContactGestion\Twig\Runtime\SymfonyAdvanceExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SymfonyAdvanceExtension extends AbstractExtension
{
    public const SAFE = ['is_safe' => ['html']];

    public function getFilters(): array
    {
        return [
            new TwigFilter('field_error', [SymfonyAdvanceExtensionRuntime::class, 'twigFilter_field_error'], self::SAFE),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('formErrors', [SymfonyAdvanceExtensionRuntime::class, 'twigFunction_formErrors'], self::SAFE),
        ];
    }
}
