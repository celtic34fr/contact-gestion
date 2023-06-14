<?php

namespace Celtic34fr\ContactGestion\Controller\Backend;

use Bolt\Entity\User;
use Celtic34fr\ContactCore\Service\ExtensionConfig;
use Celtic34fr\ContactCore\Service\SendMailer;
use Celtic34fr\ContactCore\Traits\Utilities;
use Celtic34fr\ContactGestion\Entity\Categories;
use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\Entity\Responses;
use Celtic34fr\ContactGestion\Form\ResponseType;
use Celtic34fr\ContactGestion\Form\SearchFormType;
use Celtic34fr\ContactGestion\Service\ManageTntIndexes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/** classe de gestion en BackOffice des demande de contact des internautes */
#[Route('request')]
class RequestController extends AbstractController
{
    private $schemaManager;

    public function __construct(private EntityManagerInterface $entityManager, private Environment $twigEnvironment,
    private ExtensionConfig $extConfig)
    {
        $this->entityManager = $entityManager;
        $this->twigEnvironment = $twigEnvironment;
        $this->schemaManager = $entityManager->getConnection()->getSchemaManager();
    }

    use Utilities;

    /** liste des demande de contacts à traiter ou en cours de traitement */
    #[Route('/list/{page}', name: 'request_list')]
    /**
     * interface pour afficher les requêtes adressées par les internautes.
     *
     * @throws \Exception
     */
    public function index($page = 1): Response
    {
        $currentPage = (int) $page;
        $requests = [];
        $dbPrefix = $this->getParameter('bolt.table_prefix');
        // dd($currentPage);

        if (true == $this->existsTable($dbPrefix.'contacts')) {
            $requests = $this->entityManager->getRepository(Contacts::class)->findRequestAll($currentPage);
            /*
             * avoir une case à cocher pour montrer les demandes déjà traitées
             * module de recherche dans les requêtes : date (format français), nom de l'internaute, sujet
             *    en saisie totale comme partielle.
             */
        } else {
            $this->addFlash('danger', "La table Demandes n'existe pas, veuillez en avertir l'administrateur");
        }

        return $this->render('@contact-gestion/request/index.html.twig', [
            'requests' => $requests['datas'] ?? [],
            'currentPage' => $requests['page'] ?? 0,
            'pages' => $requests['pages'] ?? 0,
        ]);
    }

    /**
     * formulaire de saisie de réponse à une demande de contact
     * @throws \Exception
     */
    #[Route('/answer/{id}', name: 'request_answer')]
    public function answer(string $id, Request $request, ExtensionConfig $extConfig, ManageTntIndexes $manageIdx): HttpResponse
    {
        $id = (int) $id;
        $requete = $this->entityManager->getRepository(Contacts::class)->find($id);
        $response = $requete?->getReponse();
        $dbCategories = [];
        $err_msg = [];
        $dbPrefix = $this->getParameter('bolt.table_prefix');
        $operation = 'u';

        if (true == $this->existsTable($dbPrefix.'contacts')) {
            /** @var User $operateur */
            $operateur = $this->getUser();
            if (!$response) {
                $response = new Responses();
                $response->setOperateur($operateur);
                /* lien avec la requête de l'internaure */
                $response->setContact($requete);
                $operation = 'i';
            }
            $categories = $this->entityManager->getRepository(Categories::class)->findAll();
            if ($categories) {
                foreach ($categories as $category) {
                    $dbCategories[] = ['value' => $category->getCategory(), 'label' => $category->getCategory()];
                }
            }

            /** @var Responses $response */
            $form = $this->createForm(ResponseType::class, $response);
            $form->handleRequest($request);

            $formS = $this->createForm(SearchFormType::class);

            if ('POST' == $request->getMethod()) {
                if ($form->isSubmitted()) {
                    /* cancel : retour à la liste des demandes ou requêtes d'internautes */
                    if (array_key_exists('response', $_POST) && array_key_exists('cancel', $_POST['response'])) {
                        return $this->redirectToRoute('request_list');
                    }

                    /* traitement pour création lien avec catégories dans réponse voir création de catégorie avant */
                    if (array_key_exists('reponse', $_POST) && array_key_exists('categories', $_POST['reponse'])) {
                        foreach ($_POST['reponse']['categories'] as $category) {
                            $record = $this->entityManager->getRepository(Categories::class)->findOneBy(['category' => $category]);
                            if (!$record) {
                                $record = new Categories();
                                $record->setCategory($category);
                                $this->entityManager->persist($record);
                            }
                            $response->addCategory($record);
                        }
                    }
                    /* sauvegarde de la réponse */
                    $reponse = $response->getReponse() ?? '';

                    /* contrôle avant enregistrement */
                    /* - il faut que la réponse soit saisie, même partielle - */
                    if (empty($reponse)) {
                        $err_msg[] = [
                            'title' => 'Une erreur est survenue',
                            'type' => 'error',
                            'body' => 'Veuillez saisir un début de réponse avant toute action.',
                        ];
                    }

                    if (empty($err_msg)) {
                        /* ajout de la date de début de traitement si absente */
                        if (empty($requete->getTreatedAt())) {
                            $requete->setTreatedAt(new \DateTimeImmutable('now'));
                        }
                        /* enregistrement de la réponse quelque soit le bouton activé */
                        $response->setReponse($reponse);
                        if (!$response->getId()) {
                            $this->entityManager->persist($response);
                        }
                        $this->entityManager->flush();
                        /* aiguillage suivant le bouton activé */
                        switch (true) {
                            case $form->get('record')->isClicked():// traitement réponse sans cloture
                                $this->entityManager->flush();

                                return $this->redirectToRoute('request_list');
                                break;
                            case $form->get('send')->isClicked(): // traitement réponse + envoi & cloture
                                $connexion = [];
                                $bodyContext = [
                                    'client' => $requete->getClient(),
                                    'demande' => $requete,
                                ];
                                $mailer = new SendMailer();
                                $mailer->initialize($this->entityManager, $this->twigEnvironment, $extConfig);
                                $mailer->sendTemplate(
                                    $requete->getClient(), '@contact-gestion/courriels/send_response.html.twig',
                                    $requete->getSujet(), $bodyContext
                                );
                                $requete->setSendAt(new \DateTimeImmutable('now'));
                                $requete->setClosedAt(new \DateTimeImmutable('now'));
                                $response->setSendAt(new \DateTimeImmutable('now'));
                                $response->setClosedAt(new \DateTimeImmutable('now'));
                                $this->entityManager->flush();
                                $manageIdx->updateResponsesIDX($response->toTntArray(), $operation);

                                return $this->redirectToRoute('request_list');
                                break;
                            case $form->get('close')->isClicked():// traitement réponse + cloture
                                $requete->setClosedAt(new \DateTimeImmutable('now'));
                                $response->setClosedAt(new \DateTimeImmutable('now'));
                                $this->entityManager->flush();

                                return $this->redirectToRoute('request_list');
                                break;
                        }
                    }
                }
            }
        } else {
            $err_msg[] = [
                'title' => 'Une erreur est survenue',
                'type' => 'error',
                'body' => "Aucune demande à traiter, table inexistante, veuillez contacter l'adminitrateur du site",
            ];
        }

        return $this->render('@contact-gestion/request/form.html.twig', [
            'requete' => $requete,
            'dbCategories' => $dbCategories,
            'form' => $form->createView(),
            'errors' => $err_msg,
            'formS' => $formS->createView(),
            'okRdv' => $this->extConfig->isExtnsionInstall("contactrdv"),
        ]);
    }

    /**
     * m"thode d'envoi direct de réponse (par courriel) à une demande de contact (si réponse saisie)
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \Exception
     */
    #[Route('/send/{id}', name: 'send_answer')]
    public function send(string $id): HttpResponse
    {
        $sendMail = new SendMailer();
        $dbPrefix = $this->getParameter('bolt.table_prefix');

        if ($this->existsTable($dbPrefix.'demandes')) {
            $flashMessage = $this->isEmptyReponse($id);
            if ($flashMessage) {
                $this->addFlash($flashMessage['type'], $flashMessage['corps']);
            } else {
                $demande = $this->entityManager->getRepository(Contacts::class)->find($id);
                $bodyContext = [
                    'client' => $demande->getClient(),
                    'demande' => $demande,
                ];
                $isOk = $sendMail->sendTemplate(
                    $demande->getClient(), '@contact-gestion/courriels/send_response.html.twig',
                    $demande->getSujet(), $bodyContext
                );
                $demande->setSendAt(new \DateTimeImmutable('now'));
                $demande->setClosedAt(new \DateTimeImmutable('now'));
                $this->entityManager->flush();
                if ($isOk) {
                    $this->addFlash('success', 'La réponse à la demandes a été envoyée et la demande cloturée');

                    return $this->redirectToRoute('request_list');
                }
            }
        } else {
            $this->addFlash('danger', "La table Demandes n'existe pas, veuillez en avertir l'administrateur");

            return $this->redirectToRoute('bolt_dashboard');
        }

        return $this->render('@contact-gestion/request/send.html.twig');
    }

    /**
     * méthode de clôture d'une demande de contact sans envoi de réponse
     * @throws \Exception
     */
    #[Route('/close/{id}', name: 'request_close')]
    public function close(string $id): HttpResponse
    {
        $dbPrefix = $this->getParameter('bolt.table_prefix');

        if ($this->existsTable($dbPrefix.'demandes')) {
            $flashMessage = $this->isEmptyReponse($id);
            if ($flashMessage) {
                $this->addFlash($flashMessage['type'], $flashMessage['corps']);
            } else {
                $demande = $this->entityManager->getRepository(Contacts::class)->find($id);
                $demande->setClosedAt(new \DateTimeImmutable('now'));
                $this->entityManager->flush();
                $corps = 'la demande du '.$demande->getCreatedAt()->format('d/m/Y').' de '.$demande->getFullname();
                $corps .= ' a bien été cloturée en base';
                $flashMessage = [
                    'type' => 'success',
                    'titre' => "Cloture d'une demande",
                    'corps' => $corps,
                    'route' => 'request_list',
                    'btn_label' => 'Retour à la liste des demandes',
                ];
                $this->addFlash($flashMessage['type'], $flashMessage['corps']);
            }
        } else {
            $this->addFlash('danger', "La table Demandes n'existe pas, veuillez en avertir l'administrateur");

            return $this->redirectToRoute('bolt_dashboard');
        }

        return $this->redirectToRoute('request_list');
    }

    /** gestion formulaire de recherche dans les demandes et/ou les réponses enregistrées d'informations - AJAX */
    #[Route('/searchInDB', name: 'search_in_db', methods: ['POST'])]
    public function searchInDB(Request $request, ManageTntIndexes $manageIdx)
    {
        $results = [];
        $formS = $this->createForm(SearchFormType::class);
        $formS->handleRequest($request);
        if ($formS->isSubmitted() && $formS->isValid()) {
            $searchText = $formS->get('searchText')->getData();
            $results = $manageIdx->search($searchText);
        }

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        if (!empty($results)) {
            $results = $this->formatList($results);
            $response->setData($results);
        } else {
            $reponse = new JsonResponse('no results found', Response::HTTP_NOT_FOUND);
        }
        return $response;
    }

    /** méthode de récupération d'un ensemble demande (question) et réponse enregistrées en base - AJAX */
    #[Route('/getQR/{id}', name: 'get-qr', methods: ['POST'])]
    public function getQR(Contacts $contact)
    {
        $response = $contact->getReponse();

        $result = [
            'fullname' => $contact->getClient()->getFullName(),
            'sujet' => $contact->getSujet(),
            'demande' => $contact->getDemande(),
            'createdAt' => $contact->getCreatedAt()->format('m/d/Y'),
            'treatedAt' => $contact->getTreatedAt() ? $contact->getTreatedAt()->format("d/m/Y") : '',
            'sendAt' => $response && $response->getSendAt() ? $response->getSendAt()->format("d/m/Y") : '',
            'closedAt' => $contact->getClosedAt() ? $contact->getClosedAt()->format('d/m/Y') : '',
            'respondBy' => $response ? $response->getOperateur()->getDisplayName() : '',
            'response' => $response ? $response->getReponse() : '',
        ];
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        $response->setData($result);
        return $response;
    }

    /** méthodes privées */

    private function isEmptyReponse(string $id): array
    {
        $demande = $this->entityManager->getRepository(Contacts::class)->find($id);
        if (empty($demande->getReponse())) {
            $titre = 'Une erreur est survenue';
            $type = 'danger';
            $corps = 'Vous demandez à envoyer la réponse au demandeur ou clôturer la demande sans avoir saisie de';
            $corps .= ' réponse.<br/>Veuillez saisir une réponse avant de refaire votre demande.';

            return [
                'type' => $type,
                'titre' => $titre,
                'corps' => $corps,
                'route' => 'request_list',
                'btn_label' => 'Retour à la liste des demandes',
            ];
        }

        return [];
    }

    private function formatList(array $results) {
        $list = [];
        foreach ($results as $idx => $result) {
            $list[$idx] = $result['sujet'];
        }
        return $list;
    }

    public function isExtnsionInstall(string $extName): bool
    {
        foreach ($this->extConfig->getInstalled() as $extInstalled) {
            $name = substr($extInstalled, strpos($extInstalled, '-') + 1);
            $name = substr($name, 0, (strpos($name, '.') ? strpos($name, '.') : strlen($name)));
            if ($name === $extName) {
                return true;
            }
        }
        return false;
    }
}
