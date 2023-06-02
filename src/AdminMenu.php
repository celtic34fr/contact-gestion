<?php

namespace Celtic34fr\ContactGestion;

use Bolt\Menu\ExtensionBackendMenuInterface;
use Exception;
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
        /*
            1/ décomposition de $menu en $menuBefor, $menuContacts et $menu after
            2/ ajout des menu de gestion des demandes
            3/ ajout au menuContacts.utilistaire de l'accès au module d'extraction pour mailing newsletter
            4/ recontruction de $menu avec $menuBefore, $menuContacts et $menuAfter
        */

        list($menuBefore, $menuContacts, $menuAfter) = $this->extractsMenus($menu);

        dump($menu, 'before', $menuBefore, 'contacts', $menuContacts, 'after', $menuAfter);

        $demandeDeContact = [
            'Demandes de contact' => [
                'type' => 'menu',
                'item' => [
                    'uri' => $this->urlGenerator->generate('bolt_menupage', [
                        'slug' => 'demande_contact',
                    ]),
                    'extras' => [
                        'group' => 'Contact',
                        'name' => 'Demandes de contact',
                        'slug' => 'demande_contact',
                    ]
                ]
            ],
            'La liste à traiter' => [
                'type' => 'smenu',
                'parent' => 'Demandes de contact',
                'item' => [
                    'uri' => $this->urlGenerator->generate('request_list'),
                    'extras' => [
                        'icon' => 'fa-clipboard-question',
                        'group' => 'Contact',
                    ]
                ]
            ],
            'Recherche dans les réponses' => [
                'type' => 'smenu',
                'parent' => 'Demandes de contact',
                'item' => [
                    'uri' => $this->urlGenerator->generate('search_responses'),
                    'extras' => [
                        'icon' => 'fa-envelope-circle-check',
                        'group' => 'Contact',
                    ]
                ]
            ]
        ];
        $menuContacts = $this->addMenu($demandeDeContact, $menuContacts);

        $utilitaires = [
            'Extraction liste Mailing Newsletter' => [
                'type' => 'smenu',
                'parent' => 'Utilitaires',
                'item' => [
                    'uri' => $this->urlGenerator->generate('extract_mailing'),
                    'extras' => [
                        'group' => 'Contact',
                        'name' => 'Extraction liste Mailing Newsletter',
                    ]
                ]
            ]
        ];
        $menuContacts = $this->addMenu($utilitaires, $menuContacts);

        dd('before', $menuBefore, 'contacts', $menuContacts, 'after', $menuAfter);
    }

    private function extractsMenus(MenuItem $menu): array
    {
        $menuBefore = $this->emptyMenuItem(clone $menu);
        $menuContacts = $this->emptyMenuItem(clone $menu);
        $menuAfter = $this->emptyMenuItem(clone $menu);
        $children = $menu->getChildren();
        $contact = false;
        $idx = 0;

        foreach ($children as $name => $child) {
            if ((!$child->getExtra('group') || $child->getExtra('group') != 'Contact') && !$contact) {
                $menuBefore->addChild($name, [
                    'uri' => $child->getUri(),
                    'extra' => $child->getExtras(),
                ]);
                if ($child->getChildren()) {
                    foreach ($child->getChildren() as $childName => $childChild) {
                        $menuBefore[$name]->addChild($childName, [
                            'uri' => $childChild->getUri(),
                            'extra' => $childChild->getExtras(),
                        ]);
                    }
                }
                $idx += 1;
            } elseif (!$contact || $child->getExtra('group') == 'Contact') {
                $contact = true;
                $menuContacts->addChild($name, [
                    'uri' => $child->getUri(),
                    'extra' => $child->getExtras(),
                ]);
                if ($child->getChildren()) {
                    foreach ($child->getChildren() as $childName => $childChild) {
                        $menuContacts[$name]->addChild($childName, [
                            'uri' => $childChild->getUri(),
                            'extra' => $childChild->getExtras(),
                        ]);
                    }
                }
                $idx += 1;
            } else {
                $menuAfter->addChild($name, [
                    'uri' => $child->getUri(),
                    'extra' => $child->getExtras(),
                ]);
                if ($child->getChildren()) {
                    foreach ($child->getChildren() as $childName => $childChild) {
                        $menuAfter[$name]->addChild($childName, [
                            'uri' => $childChild->getUri(),
                            'extra' => $childChild->getExtras(),
                        ]);
                    }
                }
                break;
            }
        }

        return [$menuBefore, $menuContacts, $menuAfter];
    }

    private function addMenu(array $menusToAdd, MenuItem $menu): MenuItem
    {
        foreach ($menusToAdd as $name => $datas) {
            switch (true) {
                case (!array_key_exists($name, $menu->getChildren()) && $datas['type'] === "menu"):
                    $menu->addChild($name, $datas['item']);
                    break;
                case ($datas['type'] === "smenu"):
                    $menuParent = $datas['parent'];
                    if (empty($menuParent)) {
                        throw new Exception("SouMenu $name sans menu parent");
                    } else if (!empty($menuParent) && (!array_key_exists($menuParent, $menu->getChildren()))) {
                        if (!array_key_exists($menuParent, $menusToAdd)) {
                            throw new Exception("SousMenu $name dont le menu parent $menuParent est introuvable");
                        } else {
                            $menu->addChild($menuParent, $menusToAdd[$menuParent]['item']);
                        }
                    }
                    if (array_key_exists($menuParent, $menu->getChildren())) {
                        $menu[$menuParent]->addChild($name, $datas['item']);
                    }
                    break;
            }
        }
        return $menu;
    }

    private function emptyMenuItem(MenuItem $menu): MenuItem
    {
        $children = $menu->getChildren();
        foreach ($children as $name => $child) {
            $menu->removeChild($name);
        }
        return $menu;
    }
}
