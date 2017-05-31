<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Annotation;

use Symfony\Component\EventDispatcher\Event;
use Wallabag\AnnotationBundle\Entity\Annotation;

/**
 * This event is fired when annotation-relative stuff is made.
 */
abstract class AnnotationEvent extends Event
{
    protected $annotation;

    /**
     * AnnotationEvent constructor.
     * @param Annotation $annotation
     */
    public function __construct(Annotation $annotation)
    {
        $this->annotation = $annotation;
    }

    /**
     * @return Annotation
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }

    /**
     * @param Annotation $annotation
     */
    public function setAnnotation(Annotation $annotation)
    {
        $this->annotation = $annotation;
    }
}
