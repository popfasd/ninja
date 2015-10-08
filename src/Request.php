<?php

namespace Popfasd\Ninja;

class Request extends \MattFerris\HttpRouting\Request
{
    /**
     * @param string $header
     * @param mixed $value
     * @return Request
     */
    public function withHeader($header, $value)
    {
        $headers = $this->headers;
        $headers[$header] = $value;
        $request = new Request($this->server, $this->_get, $this->_post, $this->_cookie, $headers);
        foreach ($this->attributes as $k => $v) {
            $request->setAttribute($k, $v);
        }
        return $request;
    }
}

