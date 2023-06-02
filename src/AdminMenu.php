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

        //dd('before', $menuBefore, 'contacts', $menuContacts, 'after', $menuAfter);

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

        dd('before', $menuBefore, 'contacts', $menuContacts, 'after', $menuAfter);

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

        dd($menu);
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
                    'extra' => $child->getExtras()
                ]);
                $idx += 1;
            } elseif (!$contact || $child->getExtra('group') == 'Contact') {
                $contact = true;
                $menuContacts->addChild($name, [
                    'uri' => $child->getUri(),
                    'extra' => $child->getExtras()
                ]);
                $idx += 1;
            } else {
                $menuAfter->addChild($name, [
                    'uri' => $child->getUri(),
                    'extra' => $child->getExtras()
                ]);
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
                    } else if (!empty($menuParent) && (!array_key_exists($name, $menu->getChildren()))) {
                        if (!array_key_exists($menuParent, $menusToAdd)) {
                            throw new Exception("SousMenu $name dont le menu parent $menuParent est introuvable");
                        }
                        $menu->addChild($menuParent, $menusToAdd[$menuParent]['item']);
                    }
                    $menu[$menuParent]->addChild($name, $datas['item']);
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
