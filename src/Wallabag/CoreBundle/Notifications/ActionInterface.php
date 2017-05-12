<?php

namespace Wallabag\CoreBundle\Notifications;

interface ActionInterface {

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     * @return ActionInterface
     */
    public function setLabel($label);

    /**
     * @return int
     */
    public function getType();

    /**
     * @param int $type
     * @return ActionInterface
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getLink();

    /**
     * @param string $link
     * @return ActionInterface
     */
    public function setLink($link);


}
