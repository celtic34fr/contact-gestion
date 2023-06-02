<?php

namespace Celtic34fr\ContactGestion;

use Bolt\Menu\ExtensionBackendMenuInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** classe d'ajout des menu spécifiques pour le projet */
class AdminMenu implements ExtensionBackendMenuInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function addItems(MenuItem $menu): void
    {
        dd($menu);

        if (!$menu->getChild("Gestion des Contacts")) {
            $menu->addChild('Gestion des Contacts', [
                'extras' => [
                    'name' => 'Gestion des Contacts',
                    'type' => 'separator',
                    'group' => 'Contact',
                ]
            ]);
        }

        $children = $menu->getChildren();
        $childrenUpdated = [];
        $contact = false;
        $saveName = "";
        $idx = 0;

        foreach ($children as $name => $child) {
            if ((!$child->getExtra('group') || $child->getExtra('group') != 'Contact') && !$contact) {
                $childrenUpdated[$name] = $child;
                $idx += 1;
            } elseif (!$contact) {
                $contact = true;
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
                'group' => 'Contact',
                'name' => 'Demandes de contact',
                'slug' => 'demande_contact',
            ]
        ]);

        $menu['Demandes de contact']->addChild('La liste à traiter', [
            'uri' => $this->urlGenerator->generate('request_list'),
            'extras' => [
                'icon' => 'fa-clipboard-question',
                'group' => 'Contact',
            ]
        ]);
        $menu['Demandes de contact']->addChild('Recherche dans les réponses', [
            'uri' => $this->urlGenerator->generate('search_responses'),
            'extras' => [
                'icon' => 'fa-envelope-circle-check',
                'group' => 'Contact',
            ]
        ]);

        $menu->addChild('Extraction liste Mailing Newsletter', [
            'uri' => $this->urlGenerator->generate('extract_mailing'),
            'extras' => [
                'group' => 'Contact',
                'name' => 'Extraction liste Mailing Newsletter',
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
