<?php

namespace App\Services\Auth;

use Controllers\AbstractTwigExtension;
use App\Services\Auth\DatabaseAuth;
use Twig\TwigFunction;

class AuthTwigExtension extends AbstractTwigExtension
{
    private $auth;

    public function __construct(DatabaseAuth $auth)
    {
        $this->auth=$auth;
    }

    public function getFunctions():array
    {
        return [
            new TwigFunction('current_user', [$this->auth,'getUser'])
        ];
    }
}
