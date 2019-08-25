<?php

namespace AppBundle\Entity;

use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;

class AccessToken extends BaseAccessToken
{
    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @var User $user
     */
    protected $user;
}
