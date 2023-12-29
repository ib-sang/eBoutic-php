<?php

namespace Controllers\Twig;

use Controllers\Middleware\CsrfMiddleware;
use Controllers\AbstractTwigExtension;
use \Twig\TwigFunction;

class CsrfExtension extends AbstractTwigExtension
{

    private $csrfmiddleware;

    public function __construct(CsrfMiddleware $csrfmiddleware)
    {

        $this->csrfmiddleware=$csrfmiddleware;
    }

    public function getFunctions():array
    {
        return [
            new TwigFunction('csrf_input', [$this,'csrfInput'], ['is_safe'=>['html']])
        ];
    }

    public function csrfInput():?string
    {
        return '<input type="hidden" name="'
            .$this->csrfmiddleware->getFormKey()
            .'" value="'.$this->csrfmiddleware->generateToken().'"/>';
    }
}
