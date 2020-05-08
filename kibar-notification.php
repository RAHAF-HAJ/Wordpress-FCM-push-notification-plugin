<?php
/**
 * @package Kibar Notification
 */
/*
Plugin Name: Kibar Notification
Description: Used to send push notification with FCM Messaging system.
Version: 1.0.0
Author: KibarSoft
Author URI: https://kibarsoft.com
License: GPLv2 or later
Text Domain: kibar_notification
*/
if (!defined('ABSPATH'))
    exit;
//include autoload
$dirName = dirname(__FILE__);
$plugins_url = plugins_url() . '/kahf';
require_once ("$dirName/autoload.php");
//Actions
add_action( 'admin_menu', 'kibar_notification_admin_menu');