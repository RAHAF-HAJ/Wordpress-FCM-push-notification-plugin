<?php

if (!defined('ABSPATH'))
    exit;

$version = 1;

add_action( 'rest_api_init', 'wp_kahf_register_routes' );

function wp_kahf_register_routes() {
    register_rest_route( 'wp/v1', 'getReciters', array(
        'methods'  => 'GET',
        'callback' => 'kahf_get_all_readers',
    ) );
    register_rest_route( 'wp/v1', 'registerToken', array(
        'methods'  => 'POST',
        'callback' => 'kahf_register_token',
    ) );
    register_rest_route( 'wp/v1', 'getAboutUsURL', array(
        'methods'  => 'GET',
        'callback' => 'kahf_get_aboutus_url',
    ) );
    register_rest_route( 'wp/v1', 'getAboutUs', array(
        'methods'  => 'GET',
        'callback' => 'kahf_get_aboutus_fields',
    ) );
    register_rest_route( 'wp/v1', 'getNotifications', array(
        'methods'  => 'GET',
        'callback' => 'kahf_get_notifications',
    ) );

    register_rest_route( 'wp/v1', 'getTokens', array(
        'methods'  => 'GET',
        'callback' => 'kahf_get_token',
    ) );
    register_rest_route( 'wp/v1', 'getQueues', array(
        'methods'  => 'GET',
        'callback' => 'kahf_get_notificationsQueue',
    ) );
}
