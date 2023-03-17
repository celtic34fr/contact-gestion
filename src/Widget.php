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
use Celtic34fr\ContactGestion\MySQLiRepository\MySQLiDemandes;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use mysqli;

class Widget extends BaseWidget implements TwigAwareInterface, CacheAwareInterface, StopwatchAwareInterface
{
    use CacheTrait;
    use StopwatchTrait;

    private EntityManagerInterface $entityManager;
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->name = 'Contact Infos Widget';
        $this->target = ADDITIONALTARGET::WIDGET_BACK_DASHBOARD_ASIDE_TOP;
        $this->priority = 300;
        $this->template = '@contact-gestion/widget/infos.html.twig';
        $this->zone = RequestZone::FRONTEND;
        $this->cacheDuration = 0;
        $this->conn = $conn;
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
        $mySQLiDemandesRepository = new MySQLiDemandes($this->conn);
        $a_traiter = $mySQLiDemandesRepository->countRequestAll();
        $last2weeks = $mySQLiDemandesRepository->countLast2WeeksDemands();
        $goToEnd = $mySQLiDemandesRepository->countDemandGoToEnd();
        $outOfTime = $mySQLiDemandesRepository->countDemandOutOfTime();
        return [
            'a_traiter' => $a_traiter,
            'last2weeks' => $last2weeks,
            'goToEnd' => $goToEnd,
            'outOfTime' => $outOfTime,
        ];
    }
}