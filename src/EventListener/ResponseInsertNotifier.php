<?php

namespace Celtic34fr\ContactGestion\EventListener;

use Celtic34fr\ContactGestion\Entity\Responses;
use Celtic34fr\ContactGestion\ManageTntIndexes;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ResponseInsertNotifier
{
    public function __construct(private ManageTntIndexes $manageTntIndexes)
    {
        
    }

    public function postInsert(Responses $response, LifecycleEventArgs $event) {
        $srcArray = $response->toTntArray();
        $this->manageTntIndexes->updateContactsIDX($srcArray, 'i');
    }
}
