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

use Symfony\Component\Intl\ResourceBundle\CurrencyBundle;
use Symfony\Component\Intl\ResourceBundle\Reader\StructuredBundleReaderInterface;

/**
 * A stub implementation of {@link \Symfony\Component\Intl\ResourceBundle\CurrencyBundleInterface}.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class IcuCurrencyBundle extends CurrencyBundle
{
    public function __construct(StructuredBundleReaderInterface $reader)
    {
        parent::__construct(realpath(IcuData::getResourceDirectory() . '/curr'), $reader);
    }
}
