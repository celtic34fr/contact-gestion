<?php

namespace Celtic34fr\ContactGestion\Widget;

use Bolt\Widget\BaseWidget;
use Bolt\Widget\Injector\AdditionalTarget;
use Bolt\Widget\Injector\RequestZone;
use Bolt\Widget\TwigAwareInterface;

class ContactsWidget extends BaseWidget implements TwigAwareInterface
{
    public function __construct()
    {
        $this->name = 'Contact Infos Widget';
        $this->target = ADDITIONALTARGET::WIDGET_BACK_DASHBOARD_BOTTOM;
        $this->priority = 300;
        $this->template = '@contact-gestion/widget/infos.html.twig';
        $this->zone = RequestZone::FRONTEND;
        $this->cacheDuration = 0;
    }

    public function run(array $params = []): ?string
    {
        return parent::run([]);
    }
}