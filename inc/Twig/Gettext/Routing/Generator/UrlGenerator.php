<?php

/**
 * This file is part of the Twig Gettext utility.
 *
 *  (c) Саша Стаменковић <umpirsky@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\Gettext\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Dummy url generator.
 *
 * @author Саша Стаменковић <umpirsky@gmail.com>
 */
class UrlGenerator implements UrlGeneratorInterface
{
    protected $context;

    public function generate($name, $parameters = array(), $absolute = false)
    {
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }
}
