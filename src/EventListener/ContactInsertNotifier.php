<?php

namespace Celtic34fr\ContactGestion\EventListener;

use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\ManageTntIndexes;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ContactInsertNotifier
{
    public function __construct(private ManageTntIndexes $manageTntIndexes)
    {
        
    }

    public function postInsert(Contacts $contact, LifecycleEventArgs $event) {
        $srcArray = $contact->toTntArray();
        $this->manageTntIndexes->updateContactsIDX($srcArray, 'i');
    }
}
