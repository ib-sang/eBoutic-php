<?php

namespace Controllers\Response;

use GuzzleHttp\Psr7\Response;

class RedirectResponse extends Response
{

    /**
     * __construct
     *
     * @param  string $url
     * @return void
     */
    public function __construct(string $url)
    {
        parent::__construct(301, ['Location'=>$url]);
    }
}
