<?php

namespace SOCIAL_BAR\Proxies;


use Exception;
use GuzzleHttp\Client;
use SOCIAL_BAR\Proxies\Contracts\ProxyInterface;

abstract class Proxy implements ProxyInterface
{
    protected $baseURL = '';
    protected $method = 'GET';

    /**
     * @param $uri
     * @param array $headers
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function fetchList($uri, $headers = []) {
        $client = new Client([
            'headers' => $headers,
            'base_uri' => $this->baseURL,
            'timeout'  => 2.0,
        ]);
        $response = $client->request($this->method, $uri);
        if($response->getStatusCode() == 200 && $response->getReasonPhrase() == "OK")
            return (string) $response->getBody();
        throw new Exception();
    }
}