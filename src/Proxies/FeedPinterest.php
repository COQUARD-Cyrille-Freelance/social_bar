<?php
/*
Social bar is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Social bar is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Social bar. If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.html.
*/
namespace FEED_SOCIAL_SIDEBAR\Proxies;

use GuzzleHttp\Exception\GuzzleException;
use SimpleXMLElement;
use stdClass;
use FEED_SOCIAL_SIDEBAR\Proxies\Contracts\ProxyInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FeedPinterest extends Proxy implements ProxyInterface
{
    protected $baseURL = 'http://www.pinterest.com/';
    protected $method = 'GET';
    /**
     * @param array $params
     * @throws \Exception
     */
    public function getList(array $params = []) {
        if(! key_exists('user', $params))
            throw new \Exception();
        $board = key_exists('board', $params) ? $params['board'] . '.rss' : 'feed.rss';
        try {
            $response = $this->fetchList($params['user'] . '/' . $board);
        } catch (GuzzleException $e) {
            return json_encode([]);
        }
        return $this->parseXMLtoJSON($response);
    }

    /**
     * @param $xml
     * @return stdClass[]
     */
    protected function parseXMLtoJSON($xml) {
        $xmlObject = new SimpleXMLElement($xml);
        return array_map(function($item) {
            $obj = new stdClass();
            $obj->link = (string) $item->link;
            $obj->description = (string) $item->description;
            $obj->date = (string) $item->pubDate;
            return $obj;
        }, $xmlObject->xpath('//item'));
    }
}