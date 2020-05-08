<?php
if (!defined('ABSPATH'))
    exit;

/*
 * Register Tokens
 * Params: token, platform
 */
function kibar_notification_register_token() {
    return kibar_notification_output_results(KibarToken::registerToken($_REQUEST['token'], $_REQUEST['platform']));
}
/*
 * Get All notifications
 * */
function kibar_notification_get_notifications() {
    $result['status'] = 'SUCCESS';
    $result['actions'] = [];
    $result['object'] = [];
    $notifications = KibarNotification::getNotification();
    foreach ($notifications as $key => $notification) {
        $result['object'][$key]['id'] = $notification->id;
        $result['object'][$key]['title'] = $notification->title;
        $result['object'][$key]['image'] = $notification->image;
        $result['object'][$key]['text'] = $notification->content;
    }
    kibar_notification_output_results($result);
}
/*
 * Get Notification Queue informaiton
 */
function kibar_notification_get_notificationsQueue() {
    $result['status'] = 'SUCCESS';
    $result['actions'] = [];
    $result['object'] = [];
    $notifications = KibarNotification::getNotificationQueue();
    $result['object'] = $notifications;
    kibar_notification_output_results($result);
}
/*
 * Get tokens
 */
function kibar_notification_get_token() {
    $result['status'] = 'SUCCESS';
    $result['actions'] = [];
    $result['object'] = [];
    $tokens = KibarToken::getTokens();
    $result['object'] = $tokens;
    kibar_notification_output_results($result);
}
/*
 * Output the result as JSON forma with api version
 */
function kibar_notification_output_results($result, $exit = true, $booleanOutput = false)
{
    global $version;
    $result['api_version'] = $version;
    if (!isset($result['actions']) || count($result['actions']) == 0) {
        $result['actions'] = array();
    }
    if ($exit) {
        echo json_encode($result);
        exit;
    } else {
        return json_encode($result);
    }
}