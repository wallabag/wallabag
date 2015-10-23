<?php

namespace Wallabag\ImportBundle\Import;

interface ImportInterface
{
    public function getName();
    public function getDescription();
    public function oAuthRequest($redirectUri, $callbackUri);
    public function oAuthAuthorize();
    public function import($accessToken);
}
