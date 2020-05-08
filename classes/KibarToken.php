<?php
if (!defined('ABSPATH'))
    exit;
/**
 * Creating token class to register new tokens, read registered tokens, and un-register token.
 */

if (!class_exists("KibarToken")):
class KibarToken
{
    const KAHF_TOKEN_TABLE = 'token';
    /*
     * Create token table if not exist
     * */
    function __construct()
    {
        self::kahf_createTokenTable();
    }
    /*** Token Table ***/
    private static function kahf_createTokenTable()
    {
        global $wpdb;
        $table = self::getTableName();
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `platform` varchar(7) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `is_registered` tinyint(1) NOT NULL,
              `created_date` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    /**
     * @param $token string FCM generated token
     * @param $platform string (android, ios)
     * @return $result
     */
    public static function registerToken($token, $platform) {
        $result['status'] = false;
        $result['actions'] = [];
        /* Check token */
        if(!isset($token) || empty($token)) {
            $message = new stdClass();
            $message->message = 'Token is required';
            $message->error_code = 'missing_token';
            $result['actions'][] = $message;
        }

        /* check platform */
        if(!isset($platform) || empty($platform)) {
            $message = new stdClass();
            $message->message = 'Platform is required';
            $message->error_code = 'missing_platform';
            $result['actions'][] = $message;
        }

        /* Check if the token exists */
        if(!is_null(self::getIdByToken($token))) {
            $message = new stdClass();
            $message->message = 'Token is registered before';
            $message->error_code = 'pre_registered_token';
            $result['actions'][] = $message;
        }
        else {
        /* Insert the new result */
        global $wpdb;
        if(count($result['error_code']) == 0) {
            $table = self::getTableName();
            $affected_rows = $wpdb->insert($table,
                array(
                    'token' => $token,
                    'platform' => $platform,
                    'is_registered' => 1,
                    'created_date' => strtotime("now")
                )
            );
            if (!$affected_rows) {
                $message = new stdClass();
                $message->message = 'Something went wrong while inserting new token';
                $message->error_code = 'database_error';
                $result['actions'][] = $message;
            } else {
                $result['status'] = true;
            }
        }}
        return $result;
    }

    /** update tokens is_register value
     * @param $token string
     * @param bool $is_registered
     * @return result
     */
    public static function updateTokenIs_register($token, $is_registered = true) {
        $result['IsSuccess'] = false;
        $result['Messages'] = [];
        $result['error_code'] = [];
        if(!isset($token) || empty($token)) {
            $result['Messages'][] = 'Token is required';
            $result['error_code'][] = 'missing_token';
        }
        $table = self::getTableName();
        global $wpdb;
        $affected_rows =  $wpdb->update($table,
            array(
                'is_registered' => $is_registered,
            ),
            array(
                'token' => $token
            )
        );
        if(!$affected_rows) {
            $result['Messages'][] = "The token hasn't changed";
            $result['error_code'][] = 'unchanged_token';
        }
        $result['IsSuccess'] = true;
        return $result;
    }

    /**get the registered tokens to send FCM notifications
     * @return result
     */
    public static function getRegisteredTokens() {
        $result['IsSuccess'] = false;
        $result['Messages'] = [];
        $result['error_code'] = [];
        global $wpdb;
        $table = self::getTableName();
        $query = 'SELECT `token` FROM '. $table. ' WHERE `is_registered` = 1';
        $tokenObs =  $wpdb->get_results($query);
        $result['object'] = [];
        if(count($tokenObs) > 0) {
            foreach ($tokenObs as $tokenOb) {
                $result['object'][] = $tokenOb->token;
            }
        }
        $result['IsSuccess'] = true;
        return $result;
    }
    /**
     * @return string Token table name
     */
    public static function getTableName() {
        global $wpdb;
        return $wpdb->prefix . self::KAHF_TOKEN_TABLE;
    }

    /** retrive token by ID
     * @param $id
     * @return null|string
     */
    public static function getTokenByID($id) {
        global $wpdb;
        $query = "SELECT `token` FROM `wp_token` WHERE `id` = " . $id;
        $token = $wpdb->get_var($query);
        return $token;
    }

    /** return if the token is registered by FCM
     * @param $id
     * @return int
     */
    public static function isRegisteredToken($id) {
        global $wpdb;
        $query = "SELECT `id` FROM `wp_token` WHERE `id` = " . $id . " AND is_registered = 1";
        $token = $wpdb->get_results($query);
        if(count($token) > 0) {
            return 1;
        }
        else {
            return 0;
        }
    }

    /** get The id by token
     * @param $token
     * @return null|string
     */
    public static function getIdByToken($token) {
        global $wpdb;
        $query = 'SELECT `id` FROM `wp_token` WHERE `token` LIKE "'. $token .'"';
        $id = $wpdb->get_var($query);
        return $id;
    }
    //Debugging functions
    public static function getTokens() {
        global $wpdb;
        $table = self::getTableName();
        $query = "SELECT * FROM $table";
        $results = $wpdb->get_results($query);
        return $results;
    }
}


new KibarToken();
endif;
