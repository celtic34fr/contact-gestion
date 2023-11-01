<?php

namespace Bolt\Extension\Celtic34fr\ContactGestion\Twig\Runtime;

use Bolt\Extension\Celtic34fr\ContactGestion\Service\ContactDbInfos;
use Twig\Extension\RuntimeExtensionInterface;

/** classe d'extension TWIG spécifique à l'extension Bolt CMS */
class LocalExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private ContactDbInfos $contactDbInfos)
    {
    }

    /** mpéthode de récupération des informations pour le Widget ContactWidget */
    public function TF_contact_infos()
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
}
