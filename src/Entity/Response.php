<?php

namespace Celtic34fr\ContactGestion\Entity;

use Bolt\Entity\User;
use Celtic34fr\ContactGestion\Repository\ResponseRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResponseRepository::class)]
#[ORM\Table(name:'responses')]
#[ORM\Index(columns: ['reponse'], name: 'search_idx', flags: ['fulltext'])]
class Response
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    /**
     * texte de la réponse proprement dite, champ obligatoire
     * @var string
     */
    private string $reponse;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    /**
     * date d'envoi de la réponse, champ facultatif
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $send_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    /**
     * date de clôture de la demande, champ facultatif
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $closed_at = null;

    #[ORM\OneToOne(targetEntity: Contact::class, inversedBy: 'reponse', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type(Contact::class)]
    /**
     * lien vers la demande de contact, champ obligatoire
     * @var Contact
     */
    private Contact $contact;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'responses')]
    #[ORM\JoinColumn(nullable: true)]
    /**
     * ensemble des catégories qualifiant la réponse si lieu, champ facultatif
     * @var Collection|null
     */
    private ?Collection $categories = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type(User::class)]
    /**
     * opérateur ayant saisi la réponse, champ obligatoire
     *
     * @var User
     */
    private User $operateur;


    
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
