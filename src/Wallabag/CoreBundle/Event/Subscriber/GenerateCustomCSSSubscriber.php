<?php

namespace Wallabag\CoreBundle\Event\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wallabag\CoreBundle\Event\ConfigUpdatedEvent;

class GenerateCustomCSSSubscriber implements EventSubscriberInterface
{
    private $em;
    private $compiler;

    public function __construct(EntityManagerInterface $em, Compiler $compiler)
    {
        $this->em = $em;
        $this->compiler = $compiler;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConfigUpdatedEvent::NAME => 'onConfigUpdated',
        ];
    }

    /**
     * Generate custom CSS.
     */
    public function onConfigUpdated(ConfigUpdatedEvent $event)
    {
        $config = $event->getConfig();

        $css = $this->compiler->compileString(
            'h1 { font-family: "' . $config->getFont() . '";}
                    #article {
                        max-width: ' . $config->getMaxWidth() . 'em;
                        font-family: "' . $config->getFont() . '";
                    }
                    #article article {
                        font-size: ' . $config->getFontsize() . 'em;
                        line-height: ' . $config->getLineHeight() . 'em;
                    }
                    ;
        ')->getCss();

        $config->setCustomCSS($css);

        $this->em->persist($config);
        $this->em->flush();
    }
}
