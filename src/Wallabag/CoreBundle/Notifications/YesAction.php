<?php

namespace Wallabag\CoreBundle\Notifications;

class YesAction extends Action {

    public function __construct($link)
    {
        $this->link = $link;
        $this->label = 'Yes';
        $this->type = Action::TYPE_YES;
    }
}
