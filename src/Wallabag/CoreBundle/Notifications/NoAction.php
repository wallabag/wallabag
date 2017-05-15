<?php

namespace Wallabag\CoreBundle\Notifications;

class NoAction extends Action {

    public function __construct($link)
    {
        $this->link = $link;
        $this->label = 'No';
        $this->type = Action::TYPE_NO;
    }
}
