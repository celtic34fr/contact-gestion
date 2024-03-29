<?php

namespace Celtic34fr\ContactGestion\Entity;

use Celtic34fr\ContactGestion\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table('categories')]
/**
 * classe Category
 * 
 * - category  : libellé de la catégorie proprement dite
 * - responses : relation bidirectionnelle ManyToOne avec la table Response
 */
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type('string')]
    /**
     * libellé de la catégorie, champ obligatoire
     * @var string
     */
    private string $category;

    #[ORM\ManyToMany(targetEntity: Response::class, mappedBy: 'categories')]
    #[ORM\JoinColumn(nullable: true)]
    /**
     * ensemble des réponses qualifiées par cette catégorie, champ facultatif
     * @var Collection|null
     */
    #[Assert\Type(Response::class)]
    private ?Collection $responses = null;



    public function __construct()
    {
        $this->responses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Collection<int, Response>
     */
    public function getResponses(): ?Collection
    {
        return $this->responses;
    }

    public function addResponse(Response $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
            $response->addCategory($this);
        }
        return $this;
    }

    public function removeResponse(Response $response): self
    {
        if ($this->responses->removeElement($response)) {
            $response->removeCategory($this);
        }
        return $this;
    }
}
