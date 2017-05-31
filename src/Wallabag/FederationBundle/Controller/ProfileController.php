<?php

namespace Wallabag\FederationBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\FederationBundle\Entity\Account;
use Wallabag\FederationBundle\Federation\CloudId;

class ProfileController extends Controller
{
    /**
     * @Route("/profile/@{user}", name="user-profile")
     * @ParamConverter("user", class="WallabagFederationBundle:Account", options={
     *     "repository_method" = "findOneByUsername"})
     *
     * @param Request $request
     * @param Account $user
     * @return JsonResponse|Response
     */
    public function getUserProfile(Request $request, Account $user)
    {
        if (in_array('application/ld+json; profile="https://www.w3.org/ns/activitystreams', $request->getAcceptableContentTypes(), true)) {
            $data = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Person',
                'id' => CloudId::getCloudIdFromAccount($user, $this->generateUrl('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL))->getDisplayId(),
                'following' => $this->generateUrl('following', ['user' => $user->getUsername()], UrlGeneratorInterface::ABSOLUTE_URL),
                'followers' => $this->generateUrl('followers', ['user' => $user->getUsername()], UrlGeneratorInterface::ABSOLUTE_URL),
                //'liked' => $this->generateUrl('recommended', ['user' => $user], UrlGeneratorInterface::ABSOLUTE_URL),
                'inbox' => $this->generateUrl('user-inbox', ['user' => $user], UrlGeneratorInterface::ABSOLUTE_URL),
                'outbox' => $this->generateUrl('user-outbox', ['user' => $user->getUsername()], UrlGeneratorInterface::ABSOLUTE_URL),
                'preferredUsername' => $user->getUser()->getName(),
                'name' => $user->getUsername(),
                //'oauthAuthorizationEndpoint' => $this->generateUrl('fos_oauth_server_authorize', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'oauthTokenEndpoint' => $this->generateUrl('fos_oauth_server_token', [], UrlGeneratorInterface::ABSOLUTE_URL),
                //'publicInbox' => $this->generateUrl('public_inbox', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ];
            return new JsonResponse($data);
        }
        return $this->render(
            'WallabagFederationBundle:User:profile.html.twig', [
                'user' => $user,
                'registration_enabled' => $this->getParameter('wallabag_user.registration_enabled'),
            ]
        );
    }

    /**
     * @Route("/profile/@{user}/followings/{page}", name="following", defaults={"page" : 0})
     * @ParamConverter("user", class="WallabagFederationBundle:Account", options={
     *     "repository_method" = "findOneByUsername"})
     *
     * @param Request $request
     * @param Account $user
     * @param int $page
     * @return JsonResponse|Response
     */
    public function getUsersFollowing(Request $request, Account $user, $page = 0)
    {
        $qb = $this->getDoctrine()->getRepository('WallabagFederationBundle:Account')->getBuilderForFollowingsByAccount($user->getId());

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);

        $following = new Pagerfanta($pagerAdapter);
        $totalFollowing = $following->getNbResults();

        $activityStream = in_array('application/ld+json; profile="https://www.w3.org/ns/activitystreams', $request->getAcceptableContentTypes(), true);

        if ($page === 0 && $activityStream) {
            /** Home page */
            $dataPrez = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'summary' => $user->getUsername() . " followings'",
                'type' => 'Collection',
                'id' => $this->generateUrl('following', ['user' => $user->getUsername()], UrlGeneratorInterface::ABSOLUTE_URL),
                'totalItems' => $totalFollowing,
                'first' => [
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'type' => 'Link',
                    'href' => $this->generateUrl('following', ['user' => $user->getUsername(), 'page' => 1], UrlGeneratorInterface::ABSOLUTE_URL),
                    'name' => 'First page of ' . $user->getUsername() . ' followings'
                ],
                'last' => [
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'type' => 'Link',
                    'href' => $this->generateUrl('following', ['user' => $user->getUsername(), 'page' => $following->getNbPages()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'name' => 'Last page of ' . $user->getUsername() . ' followings'
                ]
            ];
            return new JsonResponse($dataPrez);
            //}
        }

        $following->setMaxPerPage(30);
        $following->setCurrentPage($page);

        if (!$activityStream) {
            return $this->render('WallabagFederationBundle:User:followers.html.twig', [
                'users' => $following,
                'user' => $user,
                'registration_enabled' => $this->getParameter('wallabag_user.registration_enabled'),
            ]);
        }

        $items = [];

        foreach ($following->getCurrentPageResults() as $follow) {
            /** @var Account $follow */
            /** Items in the page */
            $items[] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Person',
                'name' => $follow->getUsername(),
                'id' => CloudId::getCloudIdFromAccount($follow),
            ];
        }

        $data = [
            'summary' => 'Page ' . $page . ' of ' . $user->getUsername() . ' followers',
            'partOf' => $this->generateUrl('following', ['user' => $user->getUsername()], UrlGeneratorInterface::ABSOLUTE_URL),
            'type' => 'OrderedCollectionPage',
            'startIndex' => ($page - 1) * 30,
            'orderedItems' => $items,
            'first' => [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('following', ['user' => $user->getUsername(), 'page' => 1], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'First page of ' . $user->getUsername() . ' followings'
            ],
            'last' => [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('following', ['user' => $user->getUsername(), 'page' => $following->getNbPages()], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'Last page of ' . $user->getUsername() . ' followings'
            ],
        ];

        /** Previous page */
        if ($page > 1) {
            $data['prev'] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('following', ['user' => $user->getUsername(), 'page' => $page - 1], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'Previous page of ' . $user->getUsername() . ' followings'
            ];
        }

        /** Next page */
        if ($page < $following->getNbPages()) {
            $data['next'] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('following', ['user' => $user->getUsername(), 'page' => $page + 1], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'Next page of ' . $user->getUsername() . ' followings'
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/profile/@{user}/followers/{page}", name="followers", defaults={"page" : 0})
     * @ParamConverter("user", class="WallabagFederationBundle:Account", options={
     *     "repository_method" = "findOneByUsername"})
     *
     * @param Request $request
     * @param Account $user
     * @return JsonResponse
     */
    public function getUsersFollowers(Request $request, Account $user, $page)
    {
        $qb = $this->getDoctrine()->getRepository('WallabagFederationBundle:Account')->getBuilderForFollowersByAccount($user->getId());

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);

        $followers = new Pagerfanta($pagerAdapter);
        $totalFollowers = $followers->getNbResults();

        $activityStream = in_array('application/ld+json; profile="https://www.w3.org/ns/activitystreams', $request->getAcceptableContentTypes(), true);

        if ($page === 0  && $activityStream) {
            /** Home page */
            $dataPrez = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'summary' => $user->getUsername() . " followers'",
                'type' => 'Collection',
                'id' => $this->generateUrl('followers', ['user' => $user->getUsername()], UrlGeneratorInterface::ABSOLUTE_URL),
                'totalItems' => $totalFollowers,
                'first' => [
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'type' => 'Link',
                    'href' => $this->generateUrl('followers', ['user' => $user->getUsername(), 'page' => 1], UrlGeneratorInterface::ABSOLUTE_URL),
                    'name' => 'First page of ' . $user->getUsername() . ' followers'
                ],
                'last' => [
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'type' => 'Link',
                    'href' => $this->generateUrl('followers', ['user' => $user->getUsername(), 'page' => $followers->getNbPages()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'name' => 'Last page of ' . $user->getUsername() . ' followers'
                ]
            ];
            return new JsonResponse($dataPrez);
        }

        $followers->setMaxPerPage(30);
        if (!$activityStream && $page === 0) {
            $followers->setCurrentPage(1);
        } else {
            $followers->setCurrentPage($page);
        }

        if (!$activityStream) {
            return $this->render('WallabagFederationBundle:User:followers.html.twig', [
                'users' => $followers,
                'user' => $user,
                'registration_enabled' => $this->getParameter('wallabag_user.registration_enabled'),
            ]);
        }

        $items = [];

        foreach ($followers->getCurrentPageResults() as $follow) {
            /** @var Account $follow */
            /** Items in the page */
            $items[] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Person',
                'name' => $follow->getUsername(),
                'id' => CloudId::getCloudIdFromAccount($follow)->getDisplayId(),
            ];
        }
        $data = [
            'summary' => 'Page ' . $page . ' of ' . $user->getUsername() . ' followers',
            'partOf' => $this->generateUrl('followers', ['user' => $user->getUsername()], UrlGeneratorInterface::ABSOLUTE_URL),
            'type' => 'OrderedCollectionPage',
            'startIndex' => ($page - 1) * 30,
            'orderedItems' => $items,
            'first' => [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('followers', ['user' => $user->getUsername(), 'page' => 1], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'First page of ' . $user->getUsername() . ' followers'
            ],
            'last' => [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('followers', ['user' => $user->getUsername(), 'page' => $followers->getNbPages()], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'Last page of ' . $user->getUsername() . ' followers'
            ],
        ];

        /** Previous page */
        if ($page > 1) {
            $data['prev'] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('followers', ['user' => $user->getUsername(), 'page' => $page - 1], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'Previous page of ' . $user->getUsername() . ' followers'
            ];
        }

        /** Next page */
        if ($page < $followers->getNbPages()) {
            $data['next'] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('followers', ['user' => $user->getUsername(), 'page' => $page + 1], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'Next page of ' . $user->getUsername() . ' followers'
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/profile/@{userToFollow}/follow", name="follow-user")
     * @ParamConverter("userToFollow", class="WallabagFederationBundle:Account", options={
     *     "repository_method" = "findOneByUsername"})
     * @param Account $userToFollow
     */
    public function followAccountAction(Account $userToFollow)
    {
        // if we're on our own instance
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {

            /** @var Account $userAccount */
            $userAccount = $this->getUser()->getAccount();

            if ($userToFollow === $userAccount) {
                $this->createAccessDeniedException("You can't follow yourself");
            }

            $em = $this->getDoctrine()->getManager();

            $userAccount->addFollowing($userToFollow);
            $userToFollow->addFollower($userAccount);

            $em->persist($userAccount);
            $em->persist($userToFollow);

            $em->flush();
        } else {
            // ask cloud id and redirect to instance
        }
    }

    /**
     * @Route("/profile/@{user}/recommendations", name="user-recommendations", defaults={"page" : 0})
     * @ParamConverter("user", class="WallabagFederationBundle:Account", options={
     *     "repository_method" = "findOneByUsername"})
     *
     * @param Request $request
     * @param Account $user
     * @param int $page
     * @return JsonResponse|Response
     */
    public function getUsersRecommendationsAction(Request $request, Account $user, $page = 0)
    {
        $qb = $this->getDoctrine()->getRepository('WallabagCoreBundle:Entry')->getBuilderForRecommendationsByUser($user->getUser()->getId());

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);

        $recommendations = new Pagerfanta($pagerAdapter);
        $totalRecommendations = $recommendations->getNbResults();

        $activityStream = in_array('application/ld+json; profile="https://www.w3.org/ns/activitystreams', $request->getAcceptableContentTypes(), true);

        if ($page === 0  && $activityStream) {
            $dataPrez = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'summary' => $user->getUsername() . " recommendations'",
                'type' => 'Collection',
                'id' => $this->generateUrl('user-recommendations', ['user' => $user->getUsername()], UrlGeneratorInterface::ABSOLUTE_URL),
                'totalItems' => $totalRecommendations,
                'first' => [
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'type' => 'Link',
                    'href' => $this->generateUrl('user-recommendations', ['user' => $user->getUsername(), 'page' => 1], UrlGeneratorInterface::ABSOLUTE_URL),
                    'name' => 'First page of ' . $user->getUsername() . ' followers'
                ],
                'last' => [
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'type' => 'Link',
                    'href' => $this->generateUrl('user-recommendations', ['user' => $user->getUsername(), 'page' => $recommendations->getNbPages()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'name' => 'Last page of ' . $user->getUsername() . ' followers'
                ]
            ];
            return new JsonResponse($dataPrez);
        }

        $recommendations->setMaxPerPage(30);
        if (!$activityStream && $page === 0) {
            $recommendations->setCurrentPage(1);
        } else {
            $recommendations->setCurrentPage($page);
        }

        if (!$activityStream) {
            return $this->render('WallabagFederationBundle:User:recommendations.html.twig', [
                'recommendations' => $recommendations,
                'registration_enabled' => $this->getParameter('wallabag_user.registration_enabled'),
            ]);
        }

        $items = [];

        foreach ($recommendations->getCurrentPageResults() as $recommendation) {
            $items[] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Person',
                'name' => $recommendation->getTitle(),
                'id' => $recommendation->getUrl(),
            ];
        }
        $data = [
            'summary' => 'Page ' . $page . ' of ' . $user->getUsername() . ' recommendations',
            'partOf' => $this->generateUrl('user-recommendations', ['user' => $user->getUsername()], UrlGeneratorInterface::ABSOLUTE_URL),
            'type' => 'OrderedCollectionPage',
            'startIndex' => ($page - 1) * 30,
            'orderedItems' => $items,
            'first' => [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('user-recommendations', ['user' => $user->getUsername(), 'page' => 1], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'First page of ' . $user->getUsername() . ' recommendations'
            ],
            'last' => [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('user-recommendations', ['user' => $user->getUsername(), 'page' => $recommendations->getNbPages()], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'Last page of ' . $user->getUsername() . ' recommendations'
            ],
        ];

        if ($page > 1) {
            $data['prev'] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('user-recommendations', ['user' => $user->getUsername(), 'page' => $page - 1], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'Previous page of ' . $user->getUsername() . ' recommendations'
            ];
        }

        if ($page < $recommendations->getNbPages()) {
            $data['next'] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Link',
                'href' => $this->generateUrl('user-recommendations', ['user' => $user->getUsername(), 'page' => $page + 1], UrlGeneratorInterface::ABSOLUTE_URL),
                'name' => 'Next page of ' . $user->getUsername() . ' recommendations'
            ];
        }

        return new JsonResponse($data);
    }

}
