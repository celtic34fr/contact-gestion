<?php

namespace Celtic34fr\ContactGestion;

use Bolt\Widget\BaseWidget;
use Bolt\Widget\CacheAwareInterface;
use Bolt\Widget\CacheTrait;
use Bolt\Widget\Injector\AdditionalTarget;
use Bolt\Widget\Injector\RequestZone;
use Bolt\Widget\StopwatchAwareInterface;
use Bolt\Widget\StopwatchTrait;
use Bolt\Widget\TwigAwareInterface;
use Celtic34fr\ContactGestion\Service\ContactDbInfos;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;

class Widget extends BaseWidget implements TwigAwareInterface, CacheAwareInterface, StopwatchAwareInterface
{
    use CacheTrait;
    use StopwatchTrait;

    private EntityManagerInterface $entityManager;
    private ContactDbInfos $contactDbInfos;

    public function __construct(ContactDbInfos $contactDbInfos)
    {
        $this->name = 'Contact Infos Widget';
        $this->target = ADDITIONALTARGET::WIDGET_BACK_DASHBOARD_ASIDE_TOP;
        $this->priority = 300;
        $this->template = '@contact-gestion/widget/infos.html.twig';
        $this->zone = RequestZone::FRONTEND;
        $this->cacheDuration = 0;
        $this->contactDbInfos = $contactDbInfos;
    }

    public function run(array $params = []): ?string
    {
        $crminfos = $this->getCrm();

        if (empty($crminfos)) {
            return null;
        }

        return parent::run(['crminfos' => $crminfos]);
    }

    public function getCrm(): array
    {
        $infos = $this->loadInformation();

        return $infos;
    }

    #[ArrayShape(['a_traiter' => "mixed", 'last2weeks' => "mixed"])]
    private function loadInformation(): array
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