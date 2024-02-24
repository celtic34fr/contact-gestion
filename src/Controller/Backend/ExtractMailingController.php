<?php

namespace Celtic34fr\ContactGestion\Controller\Backend;

use Celtic34fr\ContactGestion\Form\MailingExtractType;
use Celtic34fr\ContactGestion\FormEntity\MailingExtract;
use Celtic34fr\ContactGestion\Repository\NewsLetterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('extract_mailing')]
class ExtractMailingController extends AbstractController
{
    // TODO
    // -> comme des informations complémentaires ont été ajouté à Clientele & CliInfos, et CliSocialNetwork a été crée,
    //    la procédure d'extraction de liste de relation (client /prospect) doit pouvoir faire des filtres sur les
    //    données ajoutées si présente
    // ---> 2 types d'extraction :
    //    --> extraction pour lettre d'informations
    //    --> extraction pour campagne d'information / promotion commerciale
    //   |=> dans le formulaire de contact ajouter la demande si l'internaute veut recevoir toutes informations de la
    //       structure => besoin d'enregistrer cette information pour différentiation dans les extractions
    //   les critères de filtrages seront commun aux deux type d'extraction sauf que dans le premier cas 

    // => filtrage sur le type de relation : Clientele.type ==> énumération comme liste déroulante de choix
    // =>              la date de création de la fiche relation : clientele.create_at via datepicker limité par dates
    //                      présentes en table
    // =>              relation encore active : Clientele.closed_at toujours à null
    // =>              relation inactive depuis temps en jours par rapport à la date du jour (si non donnée = toutes)
    // =>              relation ayant un siteweb
    // =>              relation présente sur un réseau social, avec ou sans choix ==> liste des réseaux sociaux avec
    //                      table Parameter avec PArameter.cle à SocialNetwork::CLE

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
