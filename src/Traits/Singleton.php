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

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Singleton
{
    private static $_instance   = null;

    public static function init()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::init();
        }
        return self::$_instance;
    }
}