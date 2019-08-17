<?php

namespace AppBundle\Entity;

use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;

class AuthCode extends BaseAuthCode
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
