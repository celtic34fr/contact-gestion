<?php

namespace Celtic34fr\ContactGestion\Entity;

use Celtic34fr\ContactCore\Entity\CliInfos;
use Celtic34fr\ContactGestion\Repository\ContactRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Table(name:'contacts')]
#[ORM\HasLifecycleCallbacks]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\DateTime]
    private DateTimeImmutable $created_at;         //date de création

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    private ?DateTimeImmutable $treated_at = null; // date de traitement

    #[ORM\Column(type: Types::TEXT, length: 255, nullable: true)]
    #[Assert\Type('string')]
    private ?string $sujet = null;                  // sujet de la demande de contact

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    private ?string $demande = null;                // texte de la demande de contact

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    #[Assert\Type('boolean')]
    private ?bool $contact_me = null;               // top pour demande de contact téléphonique (true) ou non (false)

    #[ORM\OneToOne(mappedBy: 'contact', cascade: ['persist', 'remove'])]
    #[Assert\Type(Response::class)]
    private ?Response $reponse = null;             // lien vers la réponse saisie si existe

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    private ?DateTimeImmutable $send_at = null;    // date d'envoi de la réponse si lieu

    #[ORM\OneToOne(targetEntity: CliInfos::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\Type(CliInfos::class)]
    private ?CliInfos $client = null;               // lien vers l'interanute (informations non fixes)

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    private ?DateTimeImmutable $closed_at = null;  // date de clôture de la demande


    #[ORM\PrePersist]
    public function beforPersist(PrePersistEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $this->created_at = new DateTimeImmutable('now');
    }

    #[ORM\PreUpdate]
    public function beforeUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        if (empty($entity->getTreatedAt())) $this->treated_at = new DateTimeImmutable('now');
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getTreatedAt(): ?DateTimeImmutable
    {
        return $this->treated_at;
    }

    public function setTreatedAt(?DateTimeImmutable $treated_at): self
    {
        $this->treated_at = $treated_at;
        return $this;
    }

//    public function getAdrCourriel(): ?string
//    {
//        return $this->adr_courriel;
//    }
//
//    public function setAdrCourriel(string $adr_courriel): self
//    {
//        $this->adr_courriel = $adr_courriel;
//        return $this;
//    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): self
    {
        $this->sujet = $sujet;
        return $this;
    }

    public function getDemande(): ?string
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

    public function isContactMe(): ?bool
    {
        return $this->contact_me ?? false;
    }

    public function setContactMe(bool $contact_me): self
    {
        $this->contact_me = $contact_me;
        return $this;
    }

    public function getReponse(): ?Response
    {
        return $this->reponse;
    }

    public function setReponse(Response $reponse): self
    {
        // set the owning side of the relation if necessary
        if ($reponse->getContact() !== $this) {
            $reponse->setContact($this);
        }
        $this->reponse = $reponse;
        return $this;
    }

    public function getSendAt(): ?DateTimeImmutable
    {
        return $this->send_at;
    }

    public function setSendAt(?DateTimeImmutable $send_at): self
    {
        $this->send_at = $send_at;
        return $this;
    }

    public function getClient(): ?CliInfos
    {
        return $this->client;
    }

    public function setClient(CliInfos $client): self
    {
        $this->client = $client;
        return $this;
    }

    /**
     * customs methods to retrieve demand vue.
     */
    public function getNom(): string
    {
        return $this->client->getNom();
    }

    public function getPrenom(): ?string
    {
        return $this->client->getPrenom();
    }

    public function isEmptyPrenom(): bool
    {
        return empty($this->client->getPrenom());
    }

    public function getFullname(): string
    {
        return $this->client->getNom().(empty($this->client->getPrenom()) ? '' : ' '.$this->client->getPrenom());
    }

    public function getTelephone(): ?string
    {
        return $this->client->getTelephone();
    }

    public function isEmptyTelephone(): bool
    {
        return empty($this->client->getTelephone());
    }

    public function getCourriel(): ?string
    {
        return $this->client->getClient()->getCourriel();
    }

    public function getClosedAt(): ?DateTimeImmutable
    {
        return $this->closed_at;
    }

    public function setClosedAt(?DateTimeImmutable $closed_at): self
    {
        $this->closed_at = $closed_at;
        return $this;
    }

    public function toTntArray()
    {
        return [
            'id' => $this->id,
            'sujet' => $this->sujet,
            'demande' => $this->demande,
        ];
    }
}
