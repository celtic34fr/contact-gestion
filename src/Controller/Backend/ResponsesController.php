<?php

namespace Celtic34fr\ContactGestion\Controller\Backend;

use ArrayObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Celtic34fr\ContactGestion\Entity\Responses;
use Celtic34fr\ContactGestion\ManageTntIndexes;
use Symfony\Component\Routing\Annotation\Route;
use Celtic34fr\ContactGestion\Entity\Categories;
use Bolt\Controller\Backend\BackendZoneInterface;
use Celtic34fr\ContactCore\Trait\DbPaginateTrait;
use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\Form\SearchFormType;
use Celtic34fr\ContactGestion\FormEntity\SearchForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('response')]
class ResponsesController extends AbstractController implements BackendZoneInterface
{
    use DbPaginateTrait;

    public function __construct(private EntityManagerInterface $entityManager, private ManageTntIndexes $manageIdx)
    {
    }

    #[Route('/search', name: 'search_responses')]
    public function seachInResponses(Request $request): Response
    {
        $dbCategories = [];
        $results = [];
        $cPage = 1;
        $limit = 10;
        $categories = $this->entityManager->getRepository(Categories::class)->findAll();
        if ($categories) {
            foreach ($categories as $category) {
                $dbCategories[] = ['value' => $category->getCategory(), 'label' => $category->getCategory()];
            }
        }

        $searchForm = new SearchForm();
        $reset = true;

        $form = $this->createForm(SearchFormType::class, $searchForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->getMethod() === "POST") {
                if (!$form->get('reset')->isClicked()) {
                    // recherche via le module TntSearch dans ManageTntIndexes
                    $results = $this->manageIdx->search($form->get('searchText')->getData());
                    $results = $this->filterByCategories($results, $form->get('categories')->getData());
                } else {
                    $searchForm->setSearchText("");
                    $results = [];
                }
            }
        }

        return $this->render('@contact-gestion/responses/search.html.twig', [
            'dbCategories' => $dbCategories,
            'form' => $form->createView(),
            'reset' => $reset,
            'results' => $results,
        ]);
    }

    #[Route('/show_qr/[id}', name: 'showQR')]
    public function showQR(string $id, Request $request): Response
    {
        return $this->render('@contact-gestion/responses/show.html.twig', [
        ]);
    }


    private function filterByCategories(array $results, $categories): array
    {
        $categoriesIds = [];
        $filteredResults = [];
    
        /** filtrage des résultats par catégories */
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
            $categoriesIdsObj = new ArrayObject($categoriesIds); // sauvegarde en obj tableau de catégories
            foreach ($results as $result) { // recherche des catégories liées aux réponses trouvées
                /** @var Contacts $contact */
                $contact = $this->entityManager->getRepository(Contacts::class)->find($result['id']);
                /** @var Responses $response */
                $response = $contact->getReponse();
                if ($response) {
                    $responseCats = $response->getCategories();
                    $responseCatsIds = [];
                    if ($responseCats) {
                        /** @var Categories $responseCat */
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
        } else { // pas de catégories choisies => retour des valeurs trouvées telles que
            $filteredResults = $results;
        }

        return $filteredResults;
    }
}
