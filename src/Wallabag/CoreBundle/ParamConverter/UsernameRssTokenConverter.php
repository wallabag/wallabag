<?php

namespace Wallabag\CoreBundle\ParamConverter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wallabag\UserBundle\Entity\User;

/**
 * ParamConverter used in the RSS controller to retrieve the right user according to
 * username & token given in the url.
 *
 * @see http://stfalcon.com/en/blog/post/symfony2-custom-paramconverter
 */
class UsernameRssTokenConverter implements ParamConverterInterface
{
    private $registry;

    /**
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     *
     * Check, if object supported by our converter
     */
    public function supports(ParamConverter $configuration)
    {
        // If there is no manager, this means that only Doctrine DBAL is configured
        // In this case we can do nothing and just return
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }

        // Check, if option class was set in configuration
        if (null === $configuration->getClass()) {
            return false;
        }

        // Get actual entity manager for class
        $em = $this->registry->getManagerForClass($configuration->getClass());

        // Check, if class name is what we need
        if ('Wallabag\UserBundle\Entity\User' !== $em->getClassMetadata($configuration->getClass())->getName()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Applies converting
     *
     * @throws \InvalidArgumentException When route attributes are missing
     * @throws NotFoundHttpException     When object not found
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $username = $request->attributes->get('username');
        $rssToken = $request->attributes->get('token');

        // Check, if route attributes exists
        if (null === $username || null === $rssToken) {
            throw new \InvalidArgumentException('Route attribute is missing');
        }

        // Get actual entity manager for class
        $em = $this->registry->getManagerForClass($configuration->getClass());

        $userRepository = $em->getRepository($configuration->getClass());

        // Try to find user by its username and config rss_token
        $user = $userRepository->findOneByUsernameAndRsstoken($username, $rssToken);

        if (null === $user || !($user instanceof User)) {
            throw new NotFoundHttpException(sprintf('%s not found.', $configuration->getClass()));
        }

        // Map found user to the route's parameter
        $request->attributes->set($configuration->getName(), $user);
    }
}
