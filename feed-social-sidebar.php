<?php
/*
  Plugin Name: Feed Social sidebar
  Description: Social sidebar widget
  Author: COQUARD Cyrille
  Version: 1.0.0
  License:     GPL2

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

use Timber\Timber;
use SOCIAL_BAR\API;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once( __DIR__ . '/vendor/autoload.php' );
$timber = new Timber();

define('SOCIAL_BAR', true);

add_action('wp_enqueue_scripts', 'enqueue_style');

function enqueue_style(){
    wp_enqueue_script('social_bar_main_js', plugins_url( 'assets/js/main.js', __FILE__ ), array('jquery'), '1.0.0', true);
    wp_enqueue_script('social_bar_instagram_main_js', plugins_url( 'assets/js/jquery.instagramFeed.min.js', __FILE__ ), array('jquery'), '1.0.0', true);
    wp_enqueue_script('isotope', plugins_url( 'assets/js/isotope.js', __FILE__ ), array('jquery'), '1.0.0', true);
    wp_enqueue_script('social_bar_pinterest_main_js', plugins_url( 'assets/js/pinterest.js', __FILE__ ), array('jquery', 'isotope'), '1.0.0', true);
    wp_enqueue_style( 'social_bar_awesome', plugins_url('assets/css/all.min.css',__FILE__ ), false, '1.0.0', 'all');
    wp_enqueue_style( 'social_bar_main_css', plugins_url('assets/css/main.css',__FILE__ ), false, '1.0.0', 'all');
}

class FeedSocialSidebarWidget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'social_bar_widget',
            esc_html__( 'Social Networks sidebar', 'social_bar_widget' ),
            array( 'description' => esc_html__( 'Display feed of different social networks on a sidebar', 'social_bar_widget' ), )
        );
    }

    private $widget_fields = array(
        array(
            'label' => 'Name of the Twitter account to display',
            'id' => 'name_twitter',
            'type' => 'text',
        ),
        array(
            'label' => 'Name of the Instagram account to display',
            'id' => 'name_instagram',
            'type' => 'text',
        ),
        array(
            'label' => 'Name of the Pinterest account to display',
            'id' => 'name_pinterest',
            'type' => 'text',
        ),
        array(
            'label' => 'Twitter application ID',
            'id' => 'twitter_application_id',
            'type' => 'text',
        ),
        array(
            'label' => 'Twitter application secret',
            'id' => 'twitter_application_secret',
            'type' => 'text',
        ),
        array(
            'label' => 'Twitter access token ID',
            'id' => 'twitter_access_token_id',
            'type' => 'text',
        ),
        array(
            'label' => 'Twitter access token secret',
            'id' => 'twitter_access_token_secret',
            'type' => 'text',
        ),
        array(
            'label' => 'Activate Twitter',
            'id' => 'activate_twitter',
            'type' => 'checkbox',
        ),
        array(
            'label' => 'Activate Pinterest',
            'id' => 'activate_pinterest',
            'type' => 'checkbox',
        ),
        array(
            'label' => 'Activate Instagram',
            'id' => 'activate_instagram',
            'type' => 'checkbox',
        ),
    );

    public function widget( $args, $instance ) {
        $context = Timber::context();
        $context['name_twitter'] = $instance['name_twitter'];
        $context['name_instagram'] = $instance['name_instagram'];
        $context['name_pinterest'] = $instance['name_pinterest'];
        $context['activate_twitter'] = $instance['activate_twitter'];
        $context['activate_instagram'] = $instance['activate_instagram'];
        $context['activate_pinterest'] = $instance['activate_pinterest'];

        echo $args['before_widget'];
        Timber::render(__DIR__ . '/templates/view.html.twig', $context);
        echo $args['after_widget'];
    }

    public function field_generator( $instance ) {
        $output = '';
        foreach ( $this->widget_fields as $widget_field ) {
            $default = '';
            if ( isset($widget_field['default']) ) {
                $default = $widget_field['default'];
            }
            $widget_value = ! empty( $instance[$widget_field['id']] ) ? $instance[$widget_field['id']] : esc_html__( $default, 'textdomain' );
            switch ( $widget_field['type'] ) {
                case 'checkbox':
                    $output .= '<p>';
                    $output .= '<label for="'.esc_attr( $this->get_field_id( $widget_field['id'] ) ).'">'.esc_attr( $widget_field['label'], 'textdomain' ).':</label> ';
                    $output .= '<input class="widefat" id="'.esc_attr( $this->get_field_id( $widget_field['id'] ) ).'" name="'.esc_attr( $this->get_field_name( $widget_field['id'] ) ).'" type="'.$widget_field['type'].'"'. ($widget_value ? ' checked="checked"' : '') .'>';
                    $output .= '</p>';
                    break;
                default:
                    $output .= '<p>';
                    $output .= '<label for="'.esc_attr( $this->get_field_id( $widget_field['id'] ) ).'">'.esc_attr( $widget_field['label'], 'textdomain' ).':</label> ';
                    $output .= '<input class="widefat" id="'.esc_attr( $this->get_field_id( $widget_field['id'] ) ).'" name="'.esc_attr( $this->get_field_name( $widget_field['id'] ) ).'" type="'.$widget_field['type'].'" value="'.esc_attr( $widget_value ).'">';
                    $output .= '</p>';
            }
        }
        echo $output;
    }

    public function form( $instance ) {
        $this->field_generator( $instance );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        foreach ( $this->widget_fields as $widget_field ) {
            switch ( $widget_field['type'] ) {
                case 'checkbox':
                    $instance[$widget_field['id']] = array_key_exists($widget_field['id'], $new_instance);
                    add_option('social_bar_' . $widget_field['id'], '');
                    update_option('social_bar_' . $widget_field['id'], array_key_exists($widget_field['id'], $new_instance));
                    break;
                default:
                    $instance[$widget_field['id']] = ( ! empty( $new_instance[$widget_field['id']] ) ) ? strip_tags( $new_instance[$widget_field['id']] ) : '';
                    add_option('social_bar_' . $widget_field['id'], '');
                    update_option('social_bar_' . $widget_field['id'], ( ! empty( $new_instance[$widget_field['id']] ) ) ? strip_tags( $new_instance[$widget_field['id']] ) : '');
            }
        }
        return $instance;
    }
}

add_action( 'widgets_init', 'register_feed_social_sidebar_widget' );

function register_feed_social_sidebar_widget() {
    register_widget( 'FeedSocialSidebarWidget' );
}

API::init();