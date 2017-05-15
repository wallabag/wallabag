<?php

namespace Wallabag\CoreBundle\Notifications;

class OkAction extends Action {

    public function __construct($link)
    {
        $this->link = $link;
        $this->label = 'OK';
        $this->type = Action::TYPE_OK;
    }
}
