<?php

namespace Bolt\Extension\Celtic34fr\ContactGestion;

use Bolt\Extension\Celtic34fr\ContactCore\Traits\AdminMenuTrait;
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

    use AdminMenuTrait;

    public function addItems(MenuItem $menu): void
    {
        /* 1/ décomposition de $menu en $menuBefor, $menuContacts et $menu after */
        list($menuBefore, $menuContacts, $menuAfter) = $this->extractsMenus($menu);
        if (!$menuContacts->hasChild("Gestion des Contacts")) {
            $menuContacts->addChild('Gestion des Contacts', [
                'extras' => [
                    'name' => 'Gestion des Contacts',
                    'type' => 'separator',
                    'group' => 'Contact',
                ]
            ]);
        }

        /* 2/ ajout des menu de gestion des demandes */
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
                        'icon' => "fa-mail-bulk",
                    ]
                ]
            ],
            'La liste à traiter' => [
                'type' => 'smenu',
                'parent' => 'Demandes de contact',
                'item' => [
                    'uri' => $this->urlGenerator->generate('request_list'),
                    'extras' => [
                        'icon' => 'fa-clipboard-list',
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
                        'icon' => 'fa-question-circle',
                        'group' => 'Contact',
                    ]
                ]
            ]
        ];
        $menuContacts = $this->addMenu($demandeDeContact, $menuContacts);

        /** extraction menu 'Utilitaires' et mise en fin du bloc menu */
        if (!$menuContacts->hasChild("Utilitaires")) {
            $utilitairesItems = [
                "Utilitaires" => [
                    'type' => 'menu',
                    'item' => [
                        'uri' => $this->urlGenerator->generate('bolt_menupage', [
                            'slug' => 'utilitaires',
                        ]),
                        'extras' => [
                            'group' => 'Contact',
                            'name' => 'Utilitaires',
                            'slug' => 'utilitaires',
                            'icon' => 'fa-tools'
                        ]
                    ]
                ]
            ];
        } else {
            $utilitaires = $menuContacts['Utilitaires'];
            unset($menuContacts['Utilitaires']);
            $menuContacts->addChild($utilitaires);
        }

        /* 3/ ajout au menuContacts.utilistaire de l'accès au module d'extraction pour mailing newsletter */
        $utilitaires = [
            'Extraction liste Mailing Newsletter' => [
                'type' => 'smenu',
                'parent' => 'Utilitaires',
                'item' => [
                    'uri' => $this->urlGenerator->generate('extract_mailing'),
                    'extras' => [
                        'group' => 'Contact',
                        'name' => 'Extraction liste Mailing Newsletter',
                        'icon' => 'fa-file-csv',
                    ]
                ]
            ]
        ];
        $menuContacts = $this->addMenu($utilitaires, $menuContacts);

        /* 4/ recontruction de $menu avec $menuBefore, $menuContacts et $menuAfter */
        $menu = $this->rebuildMenu($menu, $menuBefore, $menuContacts, $menuAfter);
    }
}
