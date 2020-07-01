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

namespace FEED_SOCIAL_SIDEBAR\Traits;


use Exception;
use GuzzleHttp\Client;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait TwitterOauth
{
    protected $applicationID = '';
    protected $applicationSecret = '';
    protected $oauth_access_token = '';
    protected $oauth_access_token_secret = '';

    protected $twitterBaseUrl = 'https://api.twitter.com/';

    protected $token = '';

    protected function createSimpleAuthToken() {
        $applicationID = rawurlencode($this->applicationID);
        $applicationSecret = rawurlencode($this->applicationSecret);
        return base64_encode($applicationID . ':' . $applicationSecret);
    }

    /**
     * Private method to generate the base string used by cURL
     *
     * @param string $baseURI
     * @param string $method
     * @param array $params
     *
     * @return string Built base string
     */
    private function buildBaseString($baseURI, $method, $params)
    {
        $return = array();
        ksort($params);

        foreach($params as $key=>$value)
        {
            $return[] = "$key=" . $value;
        }

        return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $return));
    }

    protected function createHeader($url, $requestMethod, $user) {
        $time = time();
        $oauth_access_token = $this->oauth_access_token;
        $consumer_key = $this->applicationID;
        $oauth = array(
            'oauth_consumer_key' => $consumer_key,
            'oauth_nonce' => $time,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $oauth_access_token,
            'oauth_timestamp' => $time,
            'oauth_version' => '1.0',
            'screen_name' => $user,
        );

        $base_info = $this->buildBaseString($url, $requestMethod, $oauth);

        $time = rawurlencode($time);
        $oauth_access_token = rawurlencode($oauth_access_token);
        $consumer_key = rawurlencode($consumer_key);
        $user = rawurlencode($user);
        $composite_key = rawurlencode($this->applicationSecret) . '&' . rawurlencode($this->oauth_access_token_secret);
        $oauth_signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_info, $composite_key, true)));
        return [
            'Authorization' => "OAuth oauth_consumer_key=\"${consumer_key}\", oauth_nonce=\"${time}\", oauth_signature=\"${oauth_signature}\", oauth_signature_method=\"HMAC-SHA1\", oauth_timestamp=\"${time}\", oauth_token=\"${oauth_access_token}\", oauth_version=\"1.0\"",
        ];
    }
}