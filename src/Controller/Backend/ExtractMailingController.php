<?php

namespace Celtic34fr\ContactGestion\Controller\Backend;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Celtic34fr\ContactGestion\Form\MailingExtractType;
use Celtic34fr\ContactGestion\FormEntity\MailingExtract;
use Celtic34fr\ContactGestion\Repository\NewsLetterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
                if (strpos($fileName, '.csv') === false) $fileName .= '.csv';
                $mailingDatas = $repoNewsletter->findMailingInfos();
                $$tmpFile = $this->generateMailingFile($list, $mailingDatas, $tmpFile);
                $response = $this->file($tmpFileName, 'mailing-extract.csv');
                $response->headers->set('Content-type', 'application/csv');
                fclose($tmpFile);
                return $response;
            }
        }

        return $this->renderForm('@contact-gestion/extract-mailing/index.html.twig', [
            'datasSource' => $datasSource,
            'form' => $form,
        ]);
    }

    private function buildTransfertTab(array $list): array
    {
        $transfertTab = [];
        foreach ($list as $item) {
            dump($item);
            // $transfertTab[$item[0]] = $item[1];
        }
        dd('fin');
        return $transfertTab;
    }

    private function generateMailingFile(array $listFields, array $mailingDatas, $file)
    {
        // génération entête fichier CSV
        $header = [];
        foreach ($listFields as $field => $csvField) {
            $header[] = $csvField;
            fputcsv($file, $header, ";");
        }
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
