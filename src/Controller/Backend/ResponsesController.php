<?php

namespace Celtic34fr\ContactGestion\Controller\Backend;

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
        $reponses = $this->entityManager->getRepository(Responses::class)->findQrPaginate();
        $reset = true;

        $form = $this->createForm(SearchFormType::class, $searchForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->getMethod() === "POST") {
                if ($form->get('submit')->isClicked()) {
                    // recherche via le module TntSearch dans ManageTntIndexes
                    $maxResults = 20;
                    $results = $this->manageIdx->search($form->get('searchText')->getData(), $maxResults);
                } else {
                    $searchForm->setSearchText("");
                }
            }
        }

        return $this->render('@contact-gestion/responses/search.html.twig', [
            'dbCategories' => $dbCategories,
            'form' => $form->createView(),
            'reponses' => $reponses['datas'],
            'currentPage' => $reponses['page'],
            'pages' => $reponses['pages'],
            'reset' => $reset,
            'results' => $results,
        ]);
    }
}
