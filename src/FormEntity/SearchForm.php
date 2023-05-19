<?php

namespace Celtic34fr\ContactGestion\FormEntity;

use Doctrine\Common\Collections\ArrayCollection;

class SearchForm
{
    protected ?string $searchText = null;
    protected ?ArrayCollection $categories = null;

    /**
     * @return string
     */
    public function getSearchText(): ?string
    {
        return $this->searchText;
    }

    /**
     * @param string $searchText
     */
    public function setSearchText(string $searchText = null): void
    {
        $this->searchText = $searchText;
    }

    public function getCategories(): ?ArrayCollection
    {
        return $this->categories;
    }

    public function setCategories(?ArrayCollection $categories = null): self
    {
        $this->categories = $categories;
        return $this;
    }

    public function addCategory(int $categoty): mixed
    {
        if (!in_array($categoty, $this->categories->toArray())) {
            $this->categories[] = $categoty;
            return $this;
        }
        return false;
    }

    public function removeCategory(int $category): mixed
    {
        if (in_array($category, $this->categories->toArray())) {
            $idx = array_search($category, $this->categories->toArray());
            unset($this->categories[$idx]);
            return $this;
        }
        return false;
    }
}
