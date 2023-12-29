<?php

namespace Controllers\Middleware;

use ArrayAccess;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Controller\Exception\CsrfInvalidException;

class CsrfMiddleware implements MiddlewareInterface
{

    
    /**
     * formKey
     *
     * @var string
     */
    private $formKey;
        
    /**
     * sessionKey
     *
     * @var string
     */
    private $sessionKey;
    
    /**
     * session
     *
     * @var array
     */
    private $session=[];
        
    /**
     * limit
     *
     * @var int
     */
    private $limit;
    
    /**
     * __construct
     *
     * @param  mixed $session
     * @param  int $limit
     * @param  string $formKey
     * @param  string $sessionKey
     * @return void
     */
    public function __construct(&$session, int $limit = 50, string $formKey = '-csrf', string $sessionKey = 'csrf')
    {
        $this->validSession($session);
        $this->session = &$session;
        $this->formKey = $formKey;
        $this->sessionKey = $sessionKey;
        $this->limit = $limit;
    }
    
    /**
     * process
     *
     * @param  ServerRequestInterface $request
     * @param  RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        if (in_array($request->getMethod(), ['POST','PUT','DELETE'])) {
            $params = $request->getParsedBody()?:[];
            if (!array_key_exists($this->formKey, $params)) {
                $this->reject();
            } else {
                $csrfList=$this->session[$this->sessionKey] ?? [];
                if (in_array($params[$this->formKey], $csrfList)) {
                    $this->useToken($params[$this->formKey]);
                    return $handler->handle($request);
                } else {
                    $this->reject();
                }
            }
        } else {
            return $handler->handle($request);
        }
    }
    
    /**
     * generateToken
     *
     * @return string
     */
    public function generateToken():string
    {
        $token=bin2hex(random_bytes(16));
        $csrfList=$this->session[$this->sessionKey] ?? [];
        $csrfList[]=$token;
        $this->session[$this->sessionKey]=$csrfList;
        $this->limitTokens();
        return $token;
    }
    
    /**
     * getFormKey
     *
     * @return void
     */
    public function getFormKey()
    {
        return $this->formKey;
    }
    
    /**
     * limitTokens
     *
     * @return void
     */
    private function limitTokens()
    {
        $tokens=$this->session[$this->sessionKey] ?? [];
        if (count($tokens)>$this->limit) {
            array_shift($tokens);
        }
        $this->session[$this->sessionKey] =$tokens;
    }
    
    /**
     * reject
     *
     * @return void
     */
    private function reject()
    {
        throw new CsrfInvalidException();
    }
    
    /**
     * useToken
     *
     * @param  mixed $token
     * @return void
     */
    private function useToken($token)
    {
        $tokens=array_filter($this->session[$this->sessionKey], function ($t) use ($token) {
            return $token!=$t;
        }) ;
        $this->session[$this->sessionKey]=$tokens;
    }
    
    /**
     * validSession
     *
     * @param  mixed $session
     * @return void
     */
    private function validSession($session)
    {
        if (!is_array($session)  && !$session instanceof ArrayAccess) {
            throw new CsrfInvalidException();
        }
    }
}
