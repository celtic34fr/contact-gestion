<?php

namespace Celtic34fr\ContactGestion\Controller;

use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Celtic34fr\ContactCore\Entity\CliInfos;
use Celtic34fr\ContactCore\Entity\Clientele;
use Celtic34fr\ContactCore\Traits\Utilities;
use Celtic34fr\ContactGestion\Entity\Contact;
use Symfony\Component\HttpFoundation\Request;
use Celtic34fr\ContactCore\Enum\CustomerEnums;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Celtic34fr\ContactGestion\Entity\NewsLetter;
use Celtic34fr\ContactCore\Service\ExtensionConfig;
use Celtic34fr\ContactGestion\Form\ContactFormType;
use Celtic34fr\ContactGestion\FormEntity\ContactForm;
use Celtic34fr\ContactGestion\Service\ManageTntIndexes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** classe de mise en oeuvre et gestion du frontal de demande de contact */
#[Route('/contact')]
class ContactController extends AbstractController
{
    /**
     * @param ExtensionConfig $extConfigy
     *
     * @return Response
     */
    public function __construct(private EntityManagerInterface $entityManager,
        private ExtensionConfig $extConfig, private ManageTntIndexes $manageIdx)
    {
    }

    use Utilities;

    #[Route('/', name: 'contact')]
    public function __invoke(Request $request): Response
    {
        $contact = new ContactForm();
        $no_error = true;

        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (array_key_exists('contact_demande', $_POST)) {
                $contact->setDemande($_POST['contact_demande']);
            }
            if ($contact->isEmptyDemande()) {
                $msg = 'Veuillez saisir votre demande avant de la soumettre';
                $form->get('demande')->addError(new FormError($msg));
                $no_error = false;
            }

            if (false !== $contact->isContactMe() && $contact->isEmptyTelephone()) {
                $msg = 'Vous demandez à être contacté, veuillez saisir votre numéro de téléphone';
                $form->get('telephone')->addError(new FormError($msg));
                $no_error = false;
            }

            if ($no_error) {
                $rc = $this->create_request($contact);
                if ($rc) {
                    $titre = 'Enreistrement de votre demande';
                    $corps = "Votre demande vient d'être enregistrée dans nos bases";
                    $corps .= "<br>L'équipe va la prendre en compte dans les plus bref délais.";
                    $corps .= '<br>Nous reviendrons vers vous dans un délai maximum de 2 semaines.';
                    $type = 'success';
                } else {
                    $type = 'danger';
                    $titre = 'Une Erreur est survenue';
                    $corps = "Il a été impossible d'enregistrer votre demande.";
                    $corps .= "<br>L'équipe va examiner les causes de cette anomalie pour la corriger au plutôt.";
                    $corps .= '<br>Veuillez nous excuser de la gène occasionée.';
                }
                /* forward pour afficher la page mail-success.twig avec redirection vars la page d'accueil */
                $this->addFlash($type, $corps);
                $redirectRoute = $this->extConfig->get('celtic34fr-contactgestion/redirect_after_record');

                return $this->redirectToRoute($redirectRoute);
            }
        }

        $coordonnees = $this->extConfig->get('celtic34fr-contactgestion/coordonnees');
        $adresse = $this->extConfig->get('celtic34fr-contactgestion/adresse');
        $OSM_params = $this->extConfig->get('celtic34fr-contactgestion/OSM');
        $template = $this->extConfig->get('celtic34fr-contactgestion/contact_form_template');
        $newsletter = $this->extConfig->get('celtic34fr-contactgestion/newsletter');

        return $this->render('@'.$template, [
            'controller_name' => 'ContactController',
            'form' => $form->createView(),
            'titre' => 'Contactez nous',
            'breadcrumbs' => [],
            'coordonnees' => $coordonnees,
            'adresse' => $adresse,
            'OSM_params' => $OSM_params,
            'newsletter' => $newsletter,
        ]);
    }

    /** méthode privée */

    private function create_request(ContactForm $contact): bool
    {
        $rc = true;
        try {
            /** vérifi existance adresse courrielle */
            $clientele = $this->entityManager->getRepository(Clientele::class)->findOneBy(['courriel' => strtolower($contact->getAdrCourriel())]);
            if (!$clientele) {
                /** création d'un nouveau prospect comme absent de la base */
                $prospect = CustomerEnums::Prospect->_toString();
                $clientele = new Clientele();
                $clientele->setCourriel($contact->getAdrCourriel());
                $clientele->setType($prospect);
            }

            /** recherche si détail prospect déjà présent */
            $cliInfos = $this->entityManager->getRepository(CliInfos::class)->findOneBy([
                'nom' => strtoupper($contact->getNom()),
                'prenom' => $contact->isEmptyPrenom() ? null : $this->ucfirstPrenom($contact->getPrenom()),
                'telephone' => $contact->isEmptyTelephone() ? null : $contact->getTelephone(),
            ]);
            if (!$cliInfos) {
                /** création d'un nouveau detail prospect comme absent de la base */
                $cliInfos = new CliInfos();
                $cliInfos->setNom(strtoupper($contact->getNom()));
                if (!$contact->isEmptyPrenom()) {
                    $cliInfos->setPrenom($this->ucfirstPrenom($contact->getPrenom()));
                }
                if (!$contact->isEmptyTelephone()) {
                    $cliInfos->setTelephone($contact->getTelephone());
                }
                $cliInfos->setClient($clientele);
            }

            $demande = new Contact();
            $demande->setSujet($contact->getSujet());
            $demande->setDemande($contact->getDemande());
            $demande->setContactMe($contact->isContactMe());
            $demande->setClient($cliInfos);

            if ($contact->isNewsLetter()) {
                $newsletter = $this->entityManager->getRepository(NewsLetter::class)->findOneBy(['client' => $clientele]);
                if (null === $newsletter) {
                    $newsletter = new NewsLetter();
                    $newsletter->setClient($clientele);
                    $this->entityManager->persist($newsletter);
                }
            }

            if (!$clientele->getId()) {
                $this->entityManager->persist($clientele);
            }
            $this->entityManager->persist($cliInfos);
            $this->entityManager->persist($demande);

            $this->entityManager->flush();

            $this->manageIdx->updateContactsIDX($demande->toTntArray(), 'i');
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            $rc = false;
        }

        return $rc;
    }
}
