<?php

namespace Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator;

interface FormBased
{
    public function getUsernameFieldName();

    public function getPasswordFieldName();

    public function getExtraFormFields();
}
