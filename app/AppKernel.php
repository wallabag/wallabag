<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new Liip\ThemeBundle\LiipThemeBundle(),
            new Wallabag\CoreBundle\WallabagCoreBundle(),
            new Wallabag\ApiBundle\WallabagApiBundle(),
            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
            new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new Wallabag\UserBundle\WallabagUserBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Scheb\TwoFactorBundle\SchebTwoFactorBundle(),
            new KPhoen\RulerZBundle\KPhoenRulerZBundle(),
            new Wallabag\ImportBundle\WallabagImportBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Craue\ConfigBundle\CraueConfigBundle(),
            new Lexik\Bundle\MaintenanceBundle\LexikMaintenanceBundle(),
<<<<<<< bd561aeb66e1b67a8db188042fbe6d556564341d
<<<<<<< 564b1b10a638ac2b981f05716f84104cd63bd099
<<<<<<< e9a854c48821720618d0c607260ed92a2f43fa37
            new Wallabag\AnnotationBundle\WallabagAnnotationBundle(),
=======
            new Wallabag\CommentBundle\WallabagCommentBundle(),
>>>>>>> Comment work with annotator v2
=======
            new Wallabag\AnnotationBundle\WallabagAnnotationBundle(),
>>>>>>> Rename CommentBundle with AnnotationBundle
=======
<<<<<<< HEAD
<<<<<<< HEAD
            new Wallabag\AnnotationBundle\WallabagAnnotationBundle(),
=======
            new Wallabag\CommentBundle\WallabagCommentBundle(),
>>>>>>> 0c4e4ab... Comment work with annotator v2
=======
            new Wallabag\AnnotationBundle\WallabagAnnotationBundle(),
>>>>>>> 9e1ce38... Rename CommentBundle with AnnotationBundle
>>>>>>> Add the timezone as an argument in the docker-compose. For that, need to use v2 of docker-compose (with version >= 1.6.0)
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
