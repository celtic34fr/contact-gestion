<?php

namespace Celtic34fr\ContactGestion\EventListener;

use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\ManageTntIndexes;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ContactUpdateNotifier
{
    public function __construct(private ManageTntIndexes $manageTntIndexes)
    {
        
    }

    public function postUpdate(Contacts $contact, LifecycleEventArgs $event) {
        $srcArray = $contact->toTntArray();
        $this->manageTntIndexes->updateContactsIDX($srcArray, 'u');
    }
}
