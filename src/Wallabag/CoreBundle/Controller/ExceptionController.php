<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller allow us to customize the error template.
 * The only modified line from the parent template is for "WallabagCoreBundle".
 */
class ExceptionController extends BaseExceptionController
{
    protected function findTemplate(Request $request, $format, $code, $showException)
    {
        $name = $showException ? 'exception' : 'error';
        if ($showException && 'html' === $format) {
            $name = 'exception_full';
        }

        // For error pages, try to find a template for the specific HTTP status code and format
        if (!$showException) {
            $template = sprintf('WallabagCoreBundle:Exception:%s.%s.twig', $name, $format);
            if ($this->templateExists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = sprintf('@Twig/Exception/%s.%s.twig', $name, $format);
        if ($this->templateExists($template)) {
            return $template;
        }

        // default to a generic HTML exception
        $request->setRequestFormat('html');

        return sprintf('@Twig/Exception/%s.html.twig', $showException ? 'exception_full' : $name);
    }
}
