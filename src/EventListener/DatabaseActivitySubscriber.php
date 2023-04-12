<?php

namespace Celtic34fr\ContactGestion\EventListener;

use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\Entity\Responses;
use Doctrine\ORM\Events;
use Celtic34fr\ContactGestion\ManageTntIndexes;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class DatabaseActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(private ManageTntIndexes $manageTntIndexes)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Contacts || $entity instanceof Responses) {
            $srcArray = $entity->toTntArray();
            $this->manageTntIndexes->updateContactsIDX($srcArray, 'i');
        }
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Contacts || $entity instanceof Responses) {
            $srcArray = $entity->toTntArray();
            $this->manageTntIndexes->updateContactsIDX($srcArray, 'd');
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Contacts || $entity instanceof Responses) {
            $srcArray = $entity->toTntArray();
            $this->manageTntIndexes->updateContactsIDX($srcArray, 'u');
        }
    }
}
