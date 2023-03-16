<?php

namespace Celtic34fr\ContactGestion\Controller;

use Celtic34fr\ContactGestion\Service\ConfigService;
use Celtic34fr\ContactCore\Entity\Clientele;
use Celtic34fr\ContactCore\Entity\CliInfos;
use Celtic34fr\ContactGestion\Entity\Demandes;
use Celtic34fr\ContactCore\Enum\CustomerEnums;
use Celtic34fr\ContactGestion\Form\ContactType;
use Celtic34fr\ContactGestion\FormEntity\DemandesType;
use Celtic34fr\ContactCore\Service\ExtensionConfig;
use Celtic34fr\ContactCore\Service\Utilities;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contact')]
class ContactController extends AbstractController
{
    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ConfigService $config
     * @param Utilities $utilit
     * @param ExtensionConfig $extConfigy
     * @return Response
     */
    #[Route('/', name: 'crm_contact')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager, ConfigService $config,
                          Utilities $utilit, ExtensionConfig $extConfig): Response
    {
        $contact = new DemandesType();
        $no_error = true;
//        $extConfig->initialize($this->getParameter('kernel.project_dir'));

        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (array_key_exists('contact_demande', $_POST)) $contact->setDemande($_POST['contact_demande']);
            if ($contact->isEmptyDemande()) {
                $msg = "Veuillez saisir votre demande avant de la soumettre";
                $form->get('demande')->addError(new FormError($msg));
                $no_error = false;
            }

            if ((array_key_exists('contactMe', $_POST['contact']) && $contact->isEmptyTelephone())
                || ($contact->isContactMe() !== false && $contact->isEmptyTelephone()))
            {
                $msg = "Vous demandez à être contacté, veuillez saisir votre numéro de téléphone";
                $form->get('telephone')->addError(new FormError($msg));
                $no_error = false;
            }

            if ($no_error) {
                $rc = $this->create_request($contact, $entityManager);

                if ($rc) {
                    $titre = "Enreistrement de votre demande";
                    $corps = "Votre demande vient d'être enregistrée dans nos bases";
                    $corps .="<br>L'équipe va la prendre en compte dans les plus bref délais.";
                    $corps .="<br>Nous reviendrons vers vous dans un délai maximum de 2 semaines.";
                    $type = 'success';
                } else {
                    $type = 'danger';
                    $titre = "Une Erreur est survenue";
                    $corps = "Il a été impossible d'enregistrer votre demande.";
                    $corps .="<br>L'équipe va examiner les causes de cette anomalie pour la corriger au plutôt.";
                    $corps .="<br>Veuillez nous excuser de la gène occasionée.";
                }
                /** forward pour afficher la page mail-success.twig avec redirection vars la page d'accueil */
                $this->addFlash($type, $corps);
                $redirectRoute = $extConfig->get('celtic34fr-contactgestion/redirect_after_record');
                return $this->redirectToRoute($redirectRoute);
            }
        }

        $coordonnees = $extConfig->get('celtic34fr-contactgestion/coordonnees');
        $adresse = $extConfig->get('celtic34fr-contactgestion/adresse');
        $OSM_params = $extConfig->get('celtic34fr-contactgestion/OSM');
        $template = $extConfig->get('celtic34fr-contactgestion/contact_form_template');


        return $this->render('@'.$template, [
            'controller_name' => 'ContactController',
            'form' => $form->createView(),
            'titre' => 'Contactez nous',
            'breadcrumbs' => [],
            'coordonnees' => $coordonnees,
            'adresse' => $adresse,
            'OSM_params' => $OSM_params,
        ]);
    }

    /**
     * @param DemandesType $contact
     * @param EntityManagerInterface $entityManager
     * @return bool
     */
    private function create_request(DemandesType $contact, EntityManagerInterface $entityManager): bool
    {
        $rc = null;
        try {
            $clientele = $entityManager->getRepository(Clientele::class)->findOneBy(['courriel' => $contact->getAdrCourriel()]);
            if (!$clientele) {
                $prospect = CustomerEnums::Prospect->_toString();
                $clientele = new Clientele();
                $clientele->setCourriel($contact->getAdrCourriel());
                $clientele->setType($prospect);
            }

            $cliInfos = new CliInfos();
            $cliInfos->setNom($contact->getNom());
            if (!$contact->isEmptyPrenom()) {
                $cliInfos->setPrenom($contact->getPrenom());
            }
            if (!$contact->isEmptyTelephone()) {
                $cliInfos->setTelephone($contact->getTelephone());
            }
            $cliInfos->setClient($clientele);

            $demande = new Demandes();
            $demande->setSujet($contact->getSujet());
            $demande->setDemande($contact->getDemande());
            $demande->setContactMe($contact->isContactMe());
            $demande->setClient($cliInfos);

            if (!$clientele->getId()) {
                $entityManager->persist($cliInfos);
            }
            $entityManager->persist($clientele);
            $entityManager->persist($demande);

            $entityManager->flush();

            $rc = true;
        } catch (Exception $exception) {
            echo $exception->getMessage();
            $rc = false;
        }
        return $rc;
    }
}
