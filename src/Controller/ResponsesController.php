<?php

namespace Celtic34fr\ContactGestion\Controller;

use Bolt\Controller\Backend\BackendZoneInterface;
use Celtic34fr\ContactGestion\Entity\CrmCategories;
use Celtic34fr\ContactGestion\Entity\CrmResponses;
use Celtic34fr\ContactGestion\Form\SearchFormType;
use Celtic34fr\ContactGestion\FormEntity\SearchForm;
use Celtic34fr\CrmCore\Trait\DbPaginateTrait;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('response')]
class ResponsesController extends AbstractController implements BackendZoneInterface
{
    use DbPaginateTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/search', name: 'search_responses')]
    public function seachInResponses(Request $request): Response
    {
        $dbCategories = [];
        $cPage = 1;
        $limit = 10;
        $categories = $this->entityManager->getRepository(CrmCategories::class)->findAll();
        if ($categories) {
            foreach ($categories as $category) {
                $dbCategories[] = ['value' => $category->getCategory(), 'label' => $category->getCategory()];
            }
        }

        $searchForm = new SearchForm();
        $reponses = $this->entityManager->getRepository(CrmResponses::class)->findQrPaginate();
        $reset = true;

        $form = $this->createForm(SearchFormType::class, $searchForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->getMethod() === "POST") {
                if ($form->get('submit')->isClicked()) {
                    $reponses = $this->entityManager->getRepository(CrmResponses::class)
                        ->findMatchText($form->get('searchText')->getData(), $form->get('categories')->getData());
                    $reponses = $this->paginateManual($reponses, $cPage, $limit);
                    $reponses = $this->formatSearchReturn($reponses);
                    $reset = false;
                } else {
                    $searchForm->setSearchText("");
                }
            }
        }

        return $this->render('@crm-contact/responses/search.html.twig', [
            'dbCategories' => $dbCategories,
            'form' => $form->createView(),
            'reponses' => $reponses['datas'],
            'currentPage' => $reponses['page'],
            'pages' => $reponses['pages'],
            'reset' => $reset,
        ]);
    }

    /**
     * @param array $reponses
     * @return array
     */
    #[Pure] private function formatSearchReturn(array $reponses): array
    {
        $datas_output = [];
        /** @var CrmResponses $data */
        foreach ($reponses['datas'] as $data) {
            $datas_output[] = [
                'sujet' => $data->getContact()->getSujet(),
                'reponse' => $data->getReponse(),
                'created_at' => $data->getContact()->getCreatedAt(),
                'closed_at' => $data->getClosedAt(),
            ];
        }
        $reponses['datas'] = $datas_output;
        return $reponses;
    }
}
