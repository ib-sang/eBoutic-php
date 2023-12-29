<?php

namespace App\Services\Auth;

use Controllers\Auth\ForbiddenException;
use Controllers\Auth\User;
use Controllers\Response\RedirectResponse;
use Controllers\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForbiddenMiddleware implements MiddlewareInterface
{
    private $loginPath;
    private $session;

    public function __construct(string $loginPath, SessionInterface $session)
    {
        $this->loginPath = $loginPath;
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ForbiddenException $e) {
            return $this->redirectLogin($request);
        } catch (\TypeError $e) {
            if (strpos($e->getMessage(), User::class)==false) {
                return $this->redirectLogin($request);
            }
        }
        return $handler->handle($request);
    }

    public function redirectLogin(ServerRequestInterface $request):ResponseInterface
    {
        $uri=$request->getUri()->getPath();
        $this->session->set('auth.redirect', $uri);
        return new RedirectResponse($this->loginPath);
    }
}
