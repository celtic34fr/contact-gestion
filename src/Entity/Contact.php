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
/**
 * classe Contact
 * 
 * - created-at     : date de création de la demande de contact par l'internaute
 * - treated_at     : date de traitement de la demande
 * - sujet          : sujet saisi par l'internaute
 * - demande        : demande proprement dite
 * - contact_me     : booléen indiquant si l'on doit contacter par téléphone ou non l'internaute
 * - reponse        : réponse à la demande, relation OneToOne avec la table Response
 * - send_at        : date d'envoi de la réponse
 * - client         : relation qualifiée demandeur du contact, relation ManyToOne avec la table CliInfos
 * - closed_at      : date de fermeture de la demande
 * 
 * TODO
 * - ajout d'un top ou lien vers la table consignant les accord pour envoi de lettre d'informations et/ou
 *      communication commerciale (newsletter) relation unidirectionnelle table NewsLetter, UTILE ??
 */
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
    /**
     * date de création, champ obligatoire
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $created_at;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    /**
     * date de traitement, champ facultatif
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $treated_at = null;

    #[ORM\Column(type: Types::TEXT, length: 255, nullable: true)]
    #[Assert\Type('string')]
    /**
     * sujet de la demande de contact, champ facultatif
     * @var string|null
     */
    private ?string $sujet = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    /**
     * texte de la demande de contact, cahmp facultatif
     * @var string|null
     */
    private ?string $demande = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    #[Assert\Type('boolean')]
    /**
     * top pour demande de contact téléphonique (true) ou non (false), champ obligatoire initialisé à FAUX
     * @var boolean
     */
    private bool $contact_me = false;

    #[ORM\OneToOne(mappedBy: 'contact', cascade: ['persist', 'remove'])]
    #[Assert\Type(Response::class)]
    /**
     * lien vers la réponse saisie si existe, champ facultatif
     * @var Response|null
     */
    private ?Response $reponse = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    /**
     * date d'envoi de la réponse si lieu, champ facultatif
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $send_at = null;

    #[ORM\OneToOne(targetEntity: CliInfos::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type(CliInfos::class)]
    /**
     * lien vers l'interanute (informations non fixes), champ obligatoire
     * @var CliInfos
     */
    private CliInfos $client;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    /**
     * date de clôture de la demande, champ facultatif
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $closed_at = null;



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

    public function getCreatedAt(): DateTimeImmutable
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

    public function setSujet(?string $sujet): self
    {
        $this->sujet = $sujet;
        return $this;
    }

    public function getDemande(): ?string
    {
        return $this->demande;
    }

    public function setDemande(?string $demande): self
    {
        $this->demande = $demande;
        return $this;
    }

    public function isEmptyDemande(): bool
    {
        return empty($this->demande);
    }

    public function isContactMe(): bool
    {
        return $this->contact_me;
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

    public function setReponse(?Response $reponse): self
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

    public function setClient(?CliInfos $client): self
    {
        $this->client = $client;
        return $this;
    }

    /**
     * customs methods to retrieve demand vue.
     */
    public function getNom(): string
    {
        return $this->client ? $this->client->getNom() : "";
    }

    public function getPrenom(): ?string
    {
        return $this->client ? $this->client->getPrenom(): null;
    }

    public function isEmptyPrenom(): bool
    {
        return $this->client ? empty($this->client->getPrenom()) : true;
    }

    public function getFullname(): string
    {
        return $this->client 
            ? $this->client->getNom().(empty($this->client->getPrenom()) ? '' : ' '.$this->client->getPrenom())
            : false;
    }

    public function getTelephone(): ?string
    {
        return $this->client ? $this->client->getTelephone() : null;
    }

    public function isEmptyTelephone(): bool
    {
        return $this->client ? empty($this->client->getTelephone()): true;
    }

    public function getCourriel(): ?string
    {
        return $this->client ? $this->client->getClient()->getCourriel() : null;
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
