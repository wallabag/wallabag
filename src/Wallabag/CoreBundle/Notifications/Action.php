<?php

namespace Wallabag\CoreBundle\Notifications;

class Action implements ActionInterface {

    /**
     * @var string
     */
    protected $label;

    /**
     * @var int
     */
    protected $type;

    const TYPE_OK = 1;
    const TYPE_YES = 2;
    const TYPE_NO = 3;
    const TYPE_INFO = 4;

    /**
     * @var string
     */
    protected $link;

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return ActionInterface
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return ActionInterface
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        if ($type <= 0 || $type > 4) {
            throw new \InvalidArgumentException('The given type option is invalid');
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return ActionInterface
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }
}
