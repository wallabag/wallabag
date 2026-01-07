<?php

namespace Wallabag\Event\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wallabag\Event\ConfigUpdatedEvent;

class GenerateCustomCSSSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Compiler $compiler,
    ) {
    }

    public static function getSubscribedEvents(): array
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
