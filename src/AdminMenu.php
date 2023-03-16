<?php

namespace Celtic34fr\ContactGestion;

use Bolt\Menu\ExtensionBackendMenuInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdminMenu implements ExtensionBackendMenuInterface
{
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function addItems(MenuItem $menu): void
    {
        if (!$menu->getChild("Gestion des Contacts")) {
            $menu->addChild('Gestion des Contacts', [
                'extras' => [
                    'name' => 'Gestion des Contacts',
                    'type' => 'separator',
                    'group' => 'CRM',
                ]
            ]);
        }

        $children = $menu->getChildren();
        $childrenUpdated = [];
        $crm = false;
        $saveName = "";
        $idx = 0;

        foreach ($children as $name => $child) {
            if ((!$child->getExtra('group') || $child->getExtra('group') != 'CRM') && !$crm) {
                $childrenUpdated[$name] = $child;
                $idx += 1;
            } elseif (!$crm) {
                $crm = true;
                $childrenUpdated[$name] = $child;
                $idx += 1;
            } else {
                $saveName = $name;
                break;
            }
        }
        $menu->setChildren($childrenUpdated);

        $menu->addChild('Demandes de contact', [
            'uri' => $this->urlGenerator->generate('bolt_menupage', [
                'slug' => 'demande_contact',
            ]),
           'extras' => [
                'group' => 'CRM',
                'name' => 'Demandes de contact',
                'slug' => 'demande_contact',
            ]
        ]);

        $menu['Demandes de contact']->addChild('La liste à traiter', [
            'uri' => $this->urlGenerator->generate('request_list'),
            'extras' => [
                'icon' => 'fa-clipboard-question',
                'group' => 'CRM',
            ]
        ]);
        $menu['Demandes de contact']->addChild('Recherche dans les réponses', [
            'uri' => $this->urlGenerator->generate('search_responses'),
            'extras' => [
                'icon' => 'fa-envelope-circle-check',
                'group' => 'CRM',
            ]
        ]);

        if ($saveName) {
            $childrenUpdated = $menu->getChildren();
            $find = false;
            foreach ($children as $name => $child) {
                if ($name === $saveName || $find) {
                    $childrenUpdated[$name] = $child;
                    $find = true;
                }
            }
            $menu->setChildren($childrenUpdated);
        }
    }
}
