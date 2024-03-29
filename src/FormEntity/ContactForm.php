<?php

namespace Celtic34fr\ContactGestion\FormEntity;

use Symfony\Component\Validator\Constraints as Assert;

/** classe support d'exploitation des données saisie dans Form\ContactType */
class ContactForm
{
    private int $id;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type(type: 'string')]
    private string $nom;

    #[Assert\Type(type: 'string')]
    private ?string $prenom = null;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type(type: 'string')]
    #[Assert\Email(mode: 'strict')]
    private string $adr_courriel;

    #[Assert\Type(type: 'string')]
    private ?string $telephone = "";

    #[Assert\Type(type: 'bool')]
    private bool $contact_me = false;

    #[Assert\Type(type: 'bool')]
    private bool $newsletter = false;

    #[Assert\Type(type: 'bool')]
    private bool $othersInfo = false;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type(type: 'string')]
    private string $sujet;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type(type: 'string')]
    private string $demande;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function isEmptyPrenom(): string
    {
        return empty($this->prenom);
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getAdrCourriel(): string
    {
        return $this->adr_courriel;
    }

    public function setAdrCourriel(string $adr_courriel): self
    {
        $this->adr_courriel = $adr_courriel;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function isEmptyTelephone(): bool
    {
        return empty($this->telephone);
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function isContactMe(): bool
    {
        return $this->contact_me ?? false;
    }

    public function setContactMe(bool $contact_me): self
    {
        $this->contact_me = $contact_me;
        return $this;
    }

    public function isNewsLetter(): bool
    {
        return $this->newsletter ?? false;
    }

    public function setNewsLetter(bool $newsletter): self
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    public function isOthersInfo(): bool
    {
        return $this->othersInfo ?? false;
    }

    public function setOthersInfo(bool $othersInfo): self
    {
        $this->othersInfo = $othersInfo;
        return $this;
    }

    public function getSujet(): string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): self
    {
        $this->sujet = $sujet;
        return $this;
    }

    public function getDemande(): string
    {
        return $this->demande;
    }

    public function setDemande(string $demande): self
    {
        $this->demande = $demande;
        return $this;
    }

    public function isEmptyDemande(): bool
    {
        return empty($this->demande);
    }
}
