<?php

namespace Celtic34fr\ContactGestion\Widget;

use Bolt\Widget\BaseWidget;
use Bolt\Widget\CacheAwareInterface;
use Bolt\Widget\CacheTrait;
use Bolt\Widget\Injector\AdditionalTarget;
use Bolt\Widget\Injector\RequestZone;
use Bolt\Widget\StopwatchAwareInterface;
use Bolt\Widget\StopwatchTrait;
use Bolt\Widget\TwigAwareInterface;
use Doctrine\ORM\EntityManagerInterface;

class ContactsWidget extends BaseWidget implements TwigAwareInterface, CacheAwareInterface, StopwatchAwareInterface
{
    use CacheTrait;
    use StopwatchTrait;

    public function __construct(EntityManagerInterface $em)
    {
        $this->name = 'Contact Infos Widget';
        $this->target = ADDITIONALTARGET::WIDGET_BACK_DASHBOARD_ASIDE_TOP;
        $this->priority = 300;
        $this->template = '@contact-gestion/widget/infos.html.twig';
        $this->zone = RequestZone::FRONTEND;
        $this->cacheDuration = 0;
        $this->em = $em;
    }

    public function run(array $params = []): ?string
    {
        return parent::run([]);
    }
}