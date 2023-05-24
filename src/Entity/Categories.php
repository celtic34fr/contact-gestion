<?php

namespace Celtic34fr\ContactGestion\Entity;

use Celtic34fr\ContactGestion\Repository\CategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
#[ORM\Table('categories')]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $category;       // libellé de la catégorie

    #[ORM\ManyToMany(targetEntity: Responses::class, mappedBy: 'categories')]
    private Collection $responses;  // ensemble des réponses qualifiées par cette catégorie

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
     * @return Collection<int, Responses>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(Responses $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
            $response->addCategory($this);
        }
        return $this;
    }

    public function removeResponse(Responses $response): self
    {
        if ($this->responses->removeElement($response)) {
            $response->removeCategory($this);
        }
        return $this;
    }
}
