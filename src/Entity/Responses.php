<?php

namespace Celtic34fr\ContactGestion\Entity;

use Celtic34fr\ContactGestion\Repository\ResponsesRepository;
use Bolt\Entity\User;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

#[ORM\Entity(repositoryClass: ResponsesRepository::class)]
#[ORM\Index(columns: ["reponse"], name: "search_idx", flags: ['fulltext'])]
class Responses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $reponse = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $send_at = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $closed_at = null;

    #[ORM\OneToOne(inversedBy: 'reponse', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Demandes $contact = null;

    #[ORM\ManyToMany(targetEntity: Categories::class)]
    private Collection $categories;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $operateur;

    /**
     * constructeur de l'objet entité Responses
     */
    #[Pure] public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    /**
     * @param string $reponse
     * @return Responses
     */
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

    /**
     * @return Demandes|null
     */
    public function getContact(): ?Demandes
    {
        return $this->contact;
    }

    /**
     * @param Demandes $contact
     * @return Responses
     */
    public function setContact(Demandes $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @return Collection<int, Categories>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Categories $category
     * @return Responses
     */
    public function addCategory(Categories $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
        return $this;
    }

    /**
     * @param Categories $category
     * @return Responses
     */
    public function removeCategory(Categories $category): self
    {
        $this->categories->removeElement($category);
        return $this;
    }

    /**
     * @return User
     */
    public function getOperateur(): User
    {
        return $this->operateur;
    }

    /**
     * @param User $operateur
     * @return Responses
     */
    public function setOperateur(User $operateur): self
    {
        $this->operateur = $operateur;
        return $this;
    }
}
