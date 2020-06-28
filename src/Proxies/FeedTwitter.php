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
namespace SOCIAL_BAR\Proxies;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use SOCIAL_BAR\Proxies\Contracts\ProxyInterface;
use SOCIAL_BAR\Traits\TwitterOauth;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FeedTwitter extends Proxy implements ProxyInterface
{
    use TwitterOauth;

    public function __construct($applicationID, $applicationSecret, $oauth_access_token, $oauth_access_token_secret)
    {
        $this->applicationID = $applicationID;
        $this->applicationSecret = $applicationSecret;
        $this->oauth_access_token = $oauth_access_token;
        $this->oauth_access_token_secret = $oauth_access_token_secret;
    }

    public function getList(array $params = [])
    {
        if(! key_exists('user', $params))
            throw new Exception();
        try {
            $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=' . $params['user'];
            $result = $this->fetchList($url, $this->createHeader('https://api.twitter.com/1.1/statuses/user_timeline.json', $this->method, $params['user']));
        } catch (GuzzleException $e) {
            return [];
        }
        return $this->adaptResult(json_decode($result));
    }

    protected function adaptResult($result) {
        return array_map(function ($e) {
            $e->text = array_reduce($e->entities->hashtags, function ($text, $e) {
                $pattern = '#' . $e->text;
                $link = 'https://twitter.com/hashtag/' . $e->text;
                return str_replace($pattern, "<a href='${link}'>${pattern}</a>", $text);
            }, $e->text);

            $e->text = array_reduce($e->entities->urls, function ($text, $e) {
                $pattern = $e->url;
                $link = $e->expanded_url;
                $display = $e->display_url;
                return str_replace($pattern, "<a href='${link}'>${display}</a>", $text);
            }, $e->text);

            $e->text = array_reduce($e->entities->user_mentions, function ($text, $e) {
                $pattern ='@' . $e->screen_name;
                $link = 'https://twitter.com/' . $e->screen_name;
                return str_replace($pattern, "<a href='${link}'>${pattern}</a>", $text);
            }, $e->text);

            $e->created_at = date_i18n( get_option( 'date_format' ). ' ' . get_option( 'time_format' ), strtotime($e->created_at ));
            return $e;
        }, $result);
    }
}