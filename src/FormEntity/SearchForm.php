<?php

namespace Celtic34fr\ContactGestion\FormEntity;

class SearchForm
{
    protected string $searchText;
    protected array $categories;

    /**
     * @return string
     */
    public function getSearchText(): string
    {
        return $this->searchText;
    }

    /**
     * @param string $searchText
     */
    public function setSearchText(string $searchText): void
    {
        $this->searchText = $searchText;
    }


    public function getCategories(): array
    {
        return $this->categories;
    }

    public function addCategory(int $categoty): mixed
    {
        if (!in_array($categoty, $this->categories)) {
            $this->categories[] = $categoty;
            return $this;
        }
        return false;
    }

    public function removeCategory(int $category):mixed
    {
        if (in_array($category, $this->categories)) {
            $idx = array_search($category, $this->categories);
            unset($this->categories[$idx]);
            return $this;
        }
        return false;
    }
}