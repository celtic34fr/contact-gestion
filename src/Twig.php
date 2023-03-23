<?php

declare(strict_types=1);

namespace Celtic34fr\ContactGestion;

use Celtic34fr\ContactCore\Service\ExtensionConfig;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Celtic34fr\ContactGestion\Service\ContactDbInfos;

class Twig extends AbstractExtension
{

    private ContactDbInfos $contactDbInfos;

    public function __construct(ContactDbInfos $contactDbInfos)
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
            new TwigFunction('crminfos', [$this, 'twigFunction_crminfos'], $safe),
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
    public function twigFunction_crminfos()
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
