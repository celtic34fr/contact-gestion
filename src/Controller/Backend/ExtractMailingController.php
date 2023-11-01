<?php

namespace Bolt\Extension\Celtic34fr\ContactGestion\Controller\Backend;

use Bolt\Extension\Celtic34fr\ContactGestion\Form\MailingExtractType;
use Bolt\Extension\Celtic34fr\ContactGestion\FormEntity\MailingExtract;
use Bolt\Extension\Celtic34fr\ContactGestion\Repository\NewsLetterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('extract_mailing')]
class ExtractMailingController extends AbstractController
{
    #[Route('/', name: 'extract_mailing')]
    public function indexAction(Request $request, NewsLetterRepository $repoNewsletter): Response
    {
        $tmpFileName = (new Filesystem())->tempnam(sys_get_temp_dir(), 'sb_');
        $tmpFile = fopen($tmpFileName, 'wb+');
        if (!\is_resource($tmpFile)) {
            throw new \RuntimeException('Unable to create a temporary file.');
        }
        $msgError = [];

        $datasSource = [
            ['nom', 'Nom'],
            ['prenom', 'Prénom'],
            ['fullname', 'Nom Complet'],
            ['courriel', 'Adresse Courriel'],
            ['telephone', 'Téléphone'],
        ];

        $mailingExtract = new MailingExtract();
        $form = $this->createForm(MailingExtractType::class, $mailingExtract);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $list = $form->get("list")->getData();
                $list = $this->buildTransfertTab(json_decode($list));
                $fileName = $form->get("fileName")->getData();
                if (!str_contains($fileName, '.csv')) $fileName .= '.csv';
                $mailingDatas = $repoNewsletter->findMailingInfos();
                if ($mailingDatas) {
                    $$tmpFile = $this->generateMailingFile($list, $mailingDatas, $tmpFile);
                    $response = $this->file($tmpFileName, $fileName);
                    $response->headers->set('Content-type', 'application/csv');
                    fclose($tmpFile);
                    return $response;
                } else {
                    $msgError = ['Pas de données de liste de diffusion'];
                }
            }
        }

        return $this->renderForm('@contact-gestion/extract-mailing/index.html.twig', [
            'datasSource' => $datasSource,
            'form' => $form,
            'msgError' => $msgError,
        ]);
    }

    private function buildTransfertTab(array $list): array
    {
        $transfertTab = [];
        foreach ($list as $item) {
            $transfertTab[$item->index] = $item->value;
        }
        return $transfertTab;
    }

    private function generateMailingFile(array $listFields, array $mailingDatas, $file)
    {
        // génération entête fichier CSV
        $header = [];
        foreach ($listFields as $field => $csvField) {
            $header[] = $csvField;
        }
        fputcsv($file, $header, ";");

        // génération du contenu du fichier CSV
        foreach ($mailingDatas as $maingData) {
            $item = [];
            foreach ($listFields as $field => $csvField) {
                $item[] = $maingData[$field];
            }
            fputcsv($file, $item, ";");
        }
        return $file;
    }
}
