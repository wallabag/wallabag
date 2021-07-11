<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;

class StaticController extends AbstractWallabagController
{
    private $version;
    private $paypalUrl;
    private $addonsUrl;

    public function __construct(string $version, string $paypalUrl, array $addonsUrl)
    {
        $this->version = $version;
        $this->paypalUrl = $paypalUrl;
        $this->addonsUrl = $addonsUrl;
    }

    /**
     * @Route("/howto", name="howto")
     */
    public function howtoAction()
    {
        return $this->render(
            '@WallabagCore/themes/common/Static/howto.html.twig',
            [
                'addonsUrl' => $this->addonsUrl,
            ]
        );
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {
        return $this->render(
            '@WallabagCore/themes/common/Static/about.html.twig',
            [
                'version' => $this->version,
                'paypal_url' => $this->paypalUrl,
            ]
        );
    }

    /**
     * @Route("/quickstart", name="quickstart")
     */
    public function quickstartAction()
    {
        return $this->render(
            '@WallabagCore/themes/common/Static/quickstart.html.twig'
        );
    }
}
