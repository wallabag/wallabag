<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Icu;

use Symfony\Component\Intl\ResourceBundle\Reader\StructuredBundleReaderInterface;
use Symfony\Component\Intl\ResourceBundle\RegionBundle;

/**
 * A stub implementation of {@link \Symfony\Component\Intl\ResourceBundle\RegionBundleInterface}.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class IcuRegionBundle extends RegionBundle
{
    public function __construct(StructuredBundleReaderInterface $reader)
    {
        parent::__construct(realpath(IcuData::getResourceDirectory() . '/region'), $reader);
    }
}
