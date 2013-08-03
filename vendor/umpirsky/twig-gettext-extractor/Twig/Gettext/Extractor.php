<?php

/**
 * This file is part of the Twig Gettext utility.
 *
 *  (c) Саша Стаменковић <umpirsky@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\Gettext;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Extracts translations from twig templates.
 *
 * @author Саша Стаменковић <umpirsky@gmail.com>
 */
class Extractor
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * Template cached file names.
     *
     * @var string[]
     */
    protected $templates;

    /**
     * Gettext parameters.
     *
     * @var string[]
     */
    protected $parameters;

    public function __construct(\Twig_Environment $environment)
    {
        $this->environment = $environment;
        $this->reset();
    }

    protected function reset()
    {
        $this->templates = array();
        $this->parameters = array();
    }

    public function addTemplate($path)
    {
        $this->environment->loadTemplate($path);
        $this->templates[] = $this->environment->getCacheFilename($path);
    }

    public function addGettextParameter($parameter)
    {
        $this->parameters[] = $parameter;
    }

    public function setGettextParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function extract()
    {
        $command = 'xgettext';
        $command .= ' '.join(' ', $this->parameters);
        $command .= ' '.join(' ', $this->templates);

        $error = 0;
        $output = system($command, $error);
        if (0 !== $error) {
            throw new \RuntimeException(sprintf(
                'Gettext command "%s" failed with error code %s and output: %s',
                $command,
                $error,
                $output
            ));
        }

        $this->reset();
    }

    public function __destruct()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->environment->getCache());
    }
}
