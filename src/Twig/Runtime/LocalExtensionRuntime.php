<?php

namespace Celtic34fr\ContactGestion\Twig\Runtime;

use Celtic34fr\ContactGestion\Service\ContactDbInfos;
use Twig\Extension\RuntimeExtensionInterface;

class LocalExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private ContactDbInfos $contactDbInfos)
    {
    }

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
