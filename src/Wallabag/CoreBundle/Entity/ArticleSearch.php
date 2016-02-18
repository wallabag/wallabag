<?php

namespace Wallabag\CoreBundle\Entity;

class ArticleSearch
{
    private $searchTerm;

    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }
}
