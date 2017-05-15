<?php

namespace Wallabag\CoreBundle\Notifications;

class InfoAction extends Action {

    public function __construct($link)
    {
        $this->link = $link;
        $this->label = 'Info';
        $this->type = Action::TYPE_INFO;
    }
}
