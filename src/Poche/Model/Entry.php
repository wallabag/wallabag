<?php

namespace Poche\Model;

class Entry
{
    public function __construct($id, $title) {
        $this->id = $id;
        $this->title = $title;
    }

    public function getId() {
        return $this->id;
    }

}
