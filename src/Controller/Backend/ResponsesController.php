<?php

namespace Celtic34fr\ContactGestion\Controller\Backend;

use Bolt\Controller\Backend\BackendZoneInterface;
use Celtic34fr\ContactCore\Traits\DbPaginateTrait;
use Celtic34fr\ContactGestion\Entity\Category;
use Celtic34fr\ContactGestion\Entity\Contact;
use Celtic34fr\ContactGestion\Entity\Response;
use Celtic34fr\ContactGestion\Form\SearchFormType;
use Celtic34fr\ContactGestion\FormEntity\SearchForm;
use Celtic34fr\ContactGestion\Service\ManageTntIndexes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('response')]
class ResponsesController extends AbstractController implements BackendZoneInterface
{
    public function __construct(private EntityManagerInterface $entityManager, private ManageTntIndexes $manageIdx)
    {
    }

    use DbPaginateTrait;

    /** formulaire de recherche d'informations dans le desmandes (questions) et/ou réponses enregistées en base */
    #[Route('/searchIn', name: 'search_responses')]
    public function seachInResponses(Request $request): HttpResponse
    {
        $results = [];
        $cPage = 1;
        $limit = 10;

        $searchForm = new SearchForm();
        $reset = true;

        $form = $this->createForm(SearchFormType::class, $searchForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ('POST' === $request->getMethod()) {
                if (!$form->get('reset')->isClicked()) {
                    // recherche via le module TntSearch dans ManageTntIndexes
                    $results = $this->manageIdx->search($form->get('searchText')->getData());
                    $results = $this->filterByCategories($results, $form->get('categories')->getData());
                    $searchForm->setSearchText($form->get('searchText')->getData());
                    $searchForm->setCategories($form->get('categories')->getData());
                } else {
                    $searchForm->setSearchText();
                    $searchForm->setCategories();
                    $results = [];
                }
            }
        }

        return $this->render('@contact-gestion/responses/search.html.twig', [
            'form' => $form->createView(),
            'searchForm' => $searchForm,
            'reset' => $reset,
            'results' => $results,
        ]);
    }

    /** visualisation d'un ensemble demande (question) et/ou réponse  */
    #[Route('/show_qr/{id}', name: 'showQR')]
    public function showQR(Contact $contact, Request $request): HttpResponse
    {
        $qr = $this->formatQR($contact, 'contacts', 0);
        return $this->render('@contact-gestion/responses/show.html.twig', [
            'qr' => $qr,
            'demandeur' => $contact->getClient(),
        ]);
    }

    /** méthodes privées */

    private function filterByCategories(array $results, $categories): array
    {
        $categoriesIds = [];
        $filteredResults = $results;

        /* filtrage des résultats par catégories */
        if ($categories instanceof Categories) {
            // choix d'une seule catégorie
            $categoriesIds[] = $categories->getId();
        } elseif (is_array($categories)) {
            // chois de plusieurs catégories => relation AND entre
            foreach ($categories as $category) {
                $categoriesIds[] = $category->getId();
            }
        }

        if ($categoriesIds) { // une ou des catégories ont été choisies
            $filteredResults = [];
            $categoriesIdsObj = new \ArrayObject($categoriesIds); // sauvegarde en obj tableau de catégories
            foreach ($results as $result) { // recherche des catégories liées aux réponses trouvées
                /** @var Contact $contact */
                $contact = $this->entityManager->getRepository(Contact::class)->find($result['id']);
                /** @var Response $response */
                $response = $contact->getReponse();
                if ($response) {
                    $responseCats = $response->getCategories();
                    $responseCatsIds = [];
                    if ($responseCats) {
                        /** @var Category $responseCat */
                        foreach ($responseCats as $responseCat) {
                            $responseCatsIds[] = $responseCat->getId();
                        }
                    }
                    if ($responseCatsIds) { // la réponse traité est rattaché à une ou plusieurs catégories
                        $tmpCategories = $categoriesIdsObj->getArrayCopy();
                        foreach ($responseCatsIds as $responseCatsId) {
                            if (in_array($responseCatsId, $tmpCategories)) {
                                $idx = array_search($responseCatsId, $tmpCategories);
                                unset($tmpCategories[$idx]);
                            }
                        }
                        if (empty($tmpCategories)) {
                            $filteredResults[] = $result;
                        }
                    }
                }
            }
        }

        return $filteredResults;
    }

    private function formatQR($object, string $entity, $score)
    {
        $contact = null;
        $response = null;
        /* formatage des résultats de recherche pour obtebir une structure unique quelque soit l'objet à traiter */
        switch ($entity) {
            case 'contacts':
                $contact = $object;
                $response = $object ? $object->getReponse() : null;
                break;
            case 'responses':
                $response = $object;
                $contact = $object ? $object->getContact() : null;
                break;
        }
        $record = [];
        if (!empty($contact)) {
            return [
                'id' => $contact->getId(),
                'sujet' => $contact->getSujet(),
                'demande' => $contact->getDemande(),
                'createdAt' => $contact->getCreatedAt() ? $contact->getCreatedAt()->format('d/m/Y') : '',
                'treatedAt' => $contact->getTreatedAt() ? $contact->getTreatedAt()->format('d/m/Y') : '',
                'idResponses' => $response ? $response->getId() : '',
                'reponse' => $response ? $response->getReponse() : '',
                'sendAt' => $response && $response->getSendAt() ? $response->getSendAt()->format('d/m/Y') : '',
                'closedAt' => $response && $response->getClosedAt() ? $response->getClosedAt()->format('d/m/Y') : '',
                'score' => $score,
            ];
        }

        return [];
    }
}
