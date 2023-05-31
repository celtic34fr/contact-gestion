<?php

namespace Celtic34fr\ContactGestion\Controller\Backend;

use Celtic34fr\ContactGestion\Form\MailingExtractType;
use Celtic34fr\ContactGestion\FormEntity\MailingExtract;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('extract_mailing')]
class ExtractMailingController extends AbstractController
{
    public function indexAction(): Response
    {
        $mailingExtract = new MailingExtract();
        $form = $this->createForm(MailingExtractType::class, $mailingExtract);

        return $this->render('@contact-gestion/extract-mailing/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
