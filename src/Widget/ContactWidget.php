<?php

namespace Celtic34fr\ContactGestion\Widget;

use Bolt\Widget\BaseWidget;
use Bolt\Widget\CacheTrait;
use Bolt\Widget\StopwatchTrait;
use Bolt\Widget\TwigAwareInterface;
use Bolt\Widget\CacheAwareInterface;
use Bolt\Widget\Injector\AdditionalTarget;
use Bolt\Widget\Injector\RequestZone;
use Bolt\Widget\StopwatchAwareInterface;

class ContactWidget extends BaseWidget implements TwigAwareInterface, CacheAwareInterface, StopwatchAwareInterface
{
    use CacheTrait;
    use StopwatchTrait;

    public function __construct()
    {
        $this->name = 'Contacts Widget';
        $this->target = ADDITIONALTARGET::WIDGET_BACK_DASHBOARD_ASIDE_TOP;
        $this->priority = 300;
        $this->template = '@contact-gestion/widget/contacts.html.twig';
        $this->zone = RequestZone::FRONTEND;
        $this->cacheDuration = 0;
    }

    public function run(array $params = []): ?string
    {
        return parent::run([]);
    }
}
