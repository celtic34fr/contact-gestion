<?php

declare(strict_types=1);

namespace Celtic34fr\ContactGestion;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Celtic34fr\ContactGestion\Service\ContactDbInfos;

class LocalExtension extends AbstractExtension
{
    public function __construct(private ContactDbInfos $contactDbInfos)
    {
        $this->contactDbInfos = $contactDbInfos;
    }

    /**
     * Register Twig functions.
     */
    public function getFunctions(): array
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        return [
            new TwigFunction('contactInfos', [$this, 'twigFunction_contactInfos'], $safe),
        ];
    }

    /**
     * Register Twig filters.
     */
    public function getFilters(): array
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        return [
        ];
    }

    public function getTests(): array
    {
        return array(
        );
    }

    /** Twig Functions */

    /**
     * méthodes d'encapsulation de fonction ou traitements en Php
     */
    public function twigFunction_contactInfos()
    {
        $a_traiter = $this->contactDbInfos->countRequestAll();
        $last2weeks = $this->contactDbInfos->countLast2WeeksDemands();
        $goToEnd = $this->contactDbInfos->countDemandGoToEnd();
        $outOfTime = $this->contactDbInfos->countDemandOutOfTime();
        return [
            'a_traiter' => $a_traiter,
            'last2weeks' => $last2weeks,
            'goToEnd' => $goToEnd,
            'outOfTime' => $outOfTime,
        ];
    }

    /** Twig Filters */

    /** Twig Tests */

}
