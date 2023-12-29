<?php

namespace Controllers\Auth;

use Controllers\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Controllers\Auth\ForbiddenException;

class LoggedInMiddleware implements MiddlewareInterface
{

    
    /**
     * auth
     *
     * @var Auth
     */
    private $auth;
    
    /**
     * __construct
     *
     * @param  Auth $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth=$auth;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->getUser();
        if (is_null($user)) {
            throw new ForbiddenException();
        }
        return $handler->handle($request->withAttribute('auth', $user));
    }
}
