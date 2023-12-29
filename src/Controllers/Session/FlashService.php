<?php

namespace Controllers\Session;

class FlashService
{

    
    /**
     * session
     *
     * @var SessionInterface
     */
    private $session;
        
    /**
     * messages
     *
     * @var mixed
     */
    private $messages;
    
    /**
     * sessionKey
     *
     * @var string
     */
    private $sessionKey='flash';
    
    /**
     * __construct
     *
     * @param  SessionInterface $session
     * @return void
     */
    public function __construct(SessionInterface $session)
    {

        $this->session=$session;
    }
    
    /**
     * success
     *
     * @param  string $message
     * @return void
     */
    public function success(string $message):void
    {
        $flash=$this->session->get($this->sessionKey, []);
        $flash['success']=$message;
        $this->session->set($this->sessionKey, $flash);
    }
    
    /**
     * error
     *
     * @param  string $message
     * @return void
     */
    public function error(string $message):void
    {
        $flash=$this->session->get($this->sessionKey, []);
        $flash['error']=$message;
        $this->session->set($this->sessionKey, $flash);
    }
    
    /**
     * get
     *
     * @param  string $type
     * @return string/null
     */
    public function get(string $type):?string
    {
        if (is_null($this->messages)) {
            $this->messages=$this->session->get($this->sessionKey, []);
            $this->session->delete($this->sessionKey);
        }
        if (array_key_exists($type, $this->messages)) {
            return $this->messages[$type];
        }
        return null;
    }
}
