<?php

/*
 * This file is part of Simple Validator.
 *
 * (c) Frédéric Guillot <contact@fredericguillot.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SimpleValidator\Validators;

use SimpleValidator\Base;

/**
 * @author Frédéric Guillot <contact@fredericguillot.com>
 */
class Email extends Base
{
    public function execute(array $data)
    {
        if (isset($data[$this->field]) && $data[$this->field] !== '') {

            // I use the same validation method as Firefox
            // http://hg.mozilla.org/mozilla-central/file/cf5da681d577/content/html/content/src/nsHTMLInputElement.cpp#l3967

            $value = $data[$this->field];
            $length = strlen($value);

            // If the email address begins with a '@' or ends with a '.',
            // we know it's invalid.
            if ($value[0] === '@' || $value[$length - 1] === '.') {

            	return false;
            }

            // Check the username
            for ($i = 0; $i < $length && $value[$i] !== '@'; ++$i) {

                $c = $value[$i];

                if (! (ctype_alnum($c) || $c === '.' || $c === '!' || $c === '#' || $c === '$' ||
                    $c === '%' || $c === '&' || $c === '\'' || $c === '*' || $c === '+' ||
                    $c === '-' || $c === '/' || $c === '=' || $c === '?' || $c === '^' ||
                    $c === '_' || $c === '`' || $c === '{' || $c === '|' || $c === '}' ||
                    $c === '~')) {

                    return false;
                }
            }

            // There is no domain name (or it's one-character long),
            // that's not a valid email address.
            if (++$i >= $length) return false;
            if (($i + 1) === $length) return false;

            // The domain name can't begin with a dot.
            if ($value[$i] === '.') return false;

            // Parsing the domain name.
            for (; $i < $length; ++$i) {

                $c = $value[$i];

                if ($c === '.') {

                    // A dot can't follow a dot.
                    if ($value[$i - 1] === '.') return false;
                }
                elseif (! (ctype_alnum($c) || $c === '-')) {

                    // The domain characters have to be in this list to be valid.
                    return false;
                }
            }
        }

        return true;
    }
}