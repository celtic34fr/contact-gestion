<?php

namespace Celtic34fr\ContactGestion\Controller\Backend;

class SearchController
{
    private $autocompleteContactFinder;

    public function __construct($autocompleteContactFinder)
    {
        $this->autocompleteContactFinder = $autocompleteContactFinder;
    }

    public function autocomplete($term = '')
    {
        $results = $this->autocompleteContactFinder->search($term)->getResults();
        // or if you want raw results
        $rawResults = $this->autocompleteContactFinder->search($term)->getRawResults();
    }
}