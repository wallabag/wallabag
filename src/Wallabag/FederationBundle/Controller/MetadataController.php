<?php

namespace Wallabag\FederationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\FederationBundle\Federation\CloudId;

class MetadataController extends Controller
{
    /**
     * @Route("/.well-known/host-meta", name="webfinger-host-meta")
     *
     * @return Response
     */
    public function getHostMeta()
    {
        $response = new Response();
        $response->setContent('<XRD xmlns=\"http://docs.oasis-open.org/ns/xri/xrd-1.0\">
<Link rel=\"lrdd\" type=\"application/xrd+xml\" template=\"' . $this->generateUrl('webfinger') . '?resource={uri}\"/>
</XRD>');
        $response->headers->set('Content-Type', 'application/xrd+xml');
        return $response;
    }

    /**
     * @Route("/.well-known/webfinger", name="webfinger")
     * @param Request $request
     * @return JsonResponse
     */
    public function getWebfingerData(Request $request)
    {
        $subject = $request->query->get('resource');

        if (!$subject || strlen($subject) < 12 || 0 !== strpos($subject, 'acct:') || false !== strpos($subject, '@')) {
            return new JsonResponse([]);
        }
        $subjectId = substr($subject, 5);

        $cloudId = $this->getCloudId($subjectId);


        $data = [
            'subject' => $subject,
            'aliases' => [$this->generateUrl('profile', $cloudId->getUser())],
            'links' => [
                [
                    'rel' => 'http://webfinger.net/rel/profile-page',
                    'type' => 'text/html',
                    'href' => $this->generateUrl('profile', $cloudId->getUser())
                ],
            ]
        ];
        return new JsonResponse($data);
    }

    private function getCloudId($subjectId)
    {
        return new CloudId($subjectId);
    }


    #
    # {"subject":"acct:tcit@social.tcit.fr","aliases":["https://social.tcit.fr/@tcit","https://social.tcit.fr/users/tcit"],"links":[{"rel":"http://webfinger.net/rel/profile-page","type":"text/html","href":"https://social.tcit.fr/@tcit"},{"rel":"http://schemas.google.com/g/2010#updates-from","type":"application/atom+xml","href":"https://social.tcit.fr/users/tcit.atom"},{"rel":"self","type":"application/activity+json","href":"https://social.tcit.fr/@tcit"},{"rel":"salmon","href":"https://social.tcit.fr/api/salmon/1"},{"rel":"magic-public-key","href":"data:application/magic-public-key,RSA.pXwYMUdFg3XUd-bGsh8CyiMRGpRGAWuCdM5pDWx5uM4pW2pM3xbHbcI21j9h8BmlAiPg6hbZD73KGly2N8Rt5iIS0I-l6i8kA1JCCdlAaDTRd41RKMggZDoQvjVZQtsyE1VzMeU2kbqqTFN6ew7Hvbd6O0NhixoKoZ5f3jwuBDZoT0p1TAcaMdmG8oqHD97isizkDnRn8cOBA6wtI-xb5xP2zxZMsLpTDZLiKU8XcPKZCw4OfQfmDmKkHtrFb77jCAQj_s_FxjVnvxRwmfhNnWy0D-LUV_g63nHh_b5zXIeV92QZLvDYbgbezmzUzv9UeA1s70GGbaDqCIy85gw9-w==.AQAB"},{"rel":"http://ostatus.org/schema/1.0/subscribe","template":"https://social.tcit.fr/authorize_follow?acct={uri}"}]}
    #
}
