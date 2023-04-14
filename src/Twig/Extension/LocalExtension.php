<?php

namespace Celtic34fr\ContactGestion\Twig\Extension;

use Celtic34fr\ContactGestion\Twig\Runtime\LocalExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LocalExtension extends AbstractExtension
{

    const SAFE = [ 'is_safe' => ['html'], ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction('contact_infos', [LocalExtensionRuntime::class, 'TF_contact_infos'], self::SAFE),
        ];
    }
}
