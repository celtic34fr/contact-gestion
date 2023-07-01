<?php

namespace Celtic34fr\ContactGestion\Entity;

use Bolt\Entity\User;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Celtic34fr\ContactGestion\Repository\ResponsesRepository;

#[ORM\Entity(repositoryClass: ResponsesRepository::class)]
#[ORM\Table(name:'responses')]
#[ORM\Index(columns: ['reponse'], name: 'search_idx', flags: ['fulltext'])]
class Response
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $reponse = null;                // texte de la réponse proprement dite

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $send_at = null;    // date d'envoi de la réponse

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $closed_at = null;  // date de clôture de la demande

    #[ORM\OneToOne(targetEntity: Contact::class, inversedBy: 'reponse', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contact $contact = null;              // lien vers la demande de contact

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'responses')]
    private Collection $categories;                 // ensemble des catégories qualifiant la réponse si lieu

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $operateur;                        // opérateur ayant saisi la réponse

    /**
     * constructeur de l'objet entité Responses.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(string $reponse): self
    {
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

    public function getClosedAt(): ?DateTimeImmutable
    {
        return $this->closed_at;
    }

    public function setClosedAt(?DateTimeImmutable $closed_at): self
    {
        $this->closed_at = $closed_at;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(Contact $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    /**
    * @return Collection<int, Category>
    */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);
        return $this;
    }

    public function getOperateur(): User
    {
        return $this->operateur;
    }

    public function setOperateur(User $operateur): self
    {
        $this->operateur = $operateur;
        return $this;
    }

    public function toTntArray()
    {
        return [
            'id' => $this->id,
            'reponse' => $this->reponse,
        ];
    }
}
