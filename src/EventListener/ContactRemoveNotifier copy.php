<?php

namespace Celtic34fr\ContactGestion\EventListener;

use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\ManageTntIndexes;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ContactRemoveNotifier
{
    public function __construct(private ManageTntIndexes $manageTntIndexes)
    {
        
    }

    public function postRemove(Contacts $contact, LifecycleEventArgs $event) {
        $srcArray = $contact->toTntArray();
        $this->manageTntIndexes->updateContactsIDX($srcArray, 'd');
    }
}
