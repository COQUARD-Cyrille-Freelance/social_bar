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
namespace FEED_SOCIAL_SIDEBAR;

use FEED_SOCIAL_SIDEBAR\Proxies\FeedPinterest;
use FEED_SOCIAL_SIDEBAR\Proxies\FeedTwitter;
use FEED_SOCIAL_SIDEBAR\Traits\Singleton;
use WP_Error;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class API {
    use Singleton;

    private function __construct()
    {
        add_action('rest_api_init', array($this, 'initRoutes'));
    }

    /**
     * @param int $user_id
     * @return string
     */
    public static function createNonce($user_id=0)
    {
        $nonce_tick = ceil(time() / 86400);

        return substr( wp_hash( $nonce_tick . '|feed_social_sidebar_api|' . $user_id , 'nonce' ), -12, 10 );
    }

    /**
     * @param $nonce
     * @param int $user_id
     * @return bool
     */
    public static function verifyNonce($nonce, $user_id=0)
    {
        return true; //($nonce == self::createNonce($user_id));
    }

    /**
     * Initialize Routes
     */
    public function initRoutes()
    {
        /**
         * Liste de tous les chantiers
         */
        register_rest_route( 'social_bar', '/pinterest/feed', array(
            'methods' => 'GET',
            'callback' => function(WP_REST_Request $request){
                return (new FeedPinterest())->getList($request->get_params());
            },
            'args' => array(),
            'permission_callback' => array($this, 'permissionPublic'),
        ) );

        /**
         * Liste de tous les chantiers
         */
        register_rest_route( 'social_bar', '/twitter/feed', array(
            'methods' => 'GET',
            'callback' => function(WP_REST_Request $request){
                return (new FeedTwitter(get_option("feed_social_sidebar_twitter_application_id"), get_option("feed_social_sidebar_twitter_application_secret"), get_option('feed_social_sidebar_twitter_access_token_id'), get_option('feed_social_sidebar_twitter_access_token_secret')))->getList($request->get_params());
            },
            'args' => array(),
            'permission_callback' => array($this, 'permissionPublic'),
        ) );
    }

    /**
     * Check public permission
     *
     * @return bool|WP_Error
     */
    public function permissionPublic($request)
    {
        global $wp_rest_server;

        $user_id    = 0;
        $user_nonce = null;

        if ( isset( $_SERVER['HTTP_X_USER_ID'] ) ) {
            $user_id = $_SERVER['HTTP_X_USER_ID'];
        }

        if ( isset( $_SERVER['HTTP_X_USER_NONCE'] ) ) {
            $user_nonce = $_SERVER['HTTP_X_USER_NONCE'];
        }

        // DEBUG SET USER
        /*if (empty($user_id) && IS_DEV) {
            $user_id = 2;
        }*/

        //if (!$result && !IS_DEV) {
        if (!self::verifyNonce($user_nonce, $user_id)) {
            return new WP_Error( 'feed_social_sidebar_rest_invalid_nonce',  'Feed Social sidebar nonce is invalid ('.__LINE__.')', array( 'status' => 403 ) );
        }

        $wp_rest_server->send_header( 'X-User-Nonce', self::createNonce($user_id) );

        $this->user_id = $user_id;

        return true;
    }
}