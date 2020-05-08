<?php
if (!defined('ABSPATH'))
    exit;
/*
 * Create Class to deal with FCM server(push notification)
 * */

if(!class_exists('KibarFCM')):
    class KibarFCM {
        //Change this key to be your Server API key
        const API_ACCESS_KEY = 'AAAAeVRXd1w:APA91bEDqkC4K4qpA4AuSsaDg_cD_4zXhEqe1-bsmO-cCqgG8JiF6yHf3HQFFooSn8tn1dDz4HizqX3Gz3iGIdZFnZhscLt3sdgJVjl9Jia0tY4_81xFEvtXEWoewKO6Ybpc2J_q7N_A';

        /**
         * Create FCM notification fields
         * @param $registrationIDs array of tokens
         * @param $to one token
         * @param $title notification title
         * @param $message notification message
         * @param $id notification id
         * @param $imgURL notification larg image url
         * @param $picURL additional image for later
         * @return array of notification fields
         */
        static function createFCMFields($registrationIDs = [], $to = '', $title, $message, $id, $imgURL, $picURL) {
            $data = array(
                "id" => (int)($id),
                "title" => $title,
                "body" => $message,
                'color'=> "#AF1E6B",
                "icon" => "ic_notification",
                'sound' => 'default'

            );
            if(KibarNotification::$hasImage) {
                $data['image'] = $imgURL;
            }

            $fcmFields = array(
                'priority' => 'normal',
                'notification' => $data
            );
            if(!empty($registrationIDs)) {
                $fcmFields['registration_ids'] = $registrationIDs;
            }
            else if(!empty($to)) {
                $fcmFields['to'] = $to;
            }
            return $fcmFields;
        }
        /*
         * Retrun FCM header
         */
        static function getFCMHeaders() {
            $headers = array(
                'Authorization: key=' . self::API_ACCESS_KEY,
                'Content-Type: application/json'
            );
            return $headers;
        }
        /*
         * Send Fcm Message
         */
        public static function sendFCMMsg($args) {
            /* for multiple tokens for all un-sent notificaiotn in notification queue*/
            $registrationIDs = [];
            /* For one token */
            $to = '';
            if(isset($args['registrationIDs'])) {
                $registrationIDs = $args['registrationIDs'];
            }
            if($args['to']) {
                $to = $args['to'];
            }
            $notification = $args['notification'];
            $title = stripslashes($notification->title);
            $message = stripslashes($notification->content);
            $id = $notification->id;
            $imgURL = $notification->image;
            $picURL = '';
            $headers = self::getFCMHeaders();
            $fcmFields = self::createFCMFields($registrationIDs, $to, $title, $message, $id, $imgURL, $picURL);
            print_r($fcmFields);
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
            //HTTP debugging
            $fp = fopen(dirname(__FILE__).'/errorlog.txt', 'w');
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_STDERR, $fp);
            fclose($fp);
            $result = curl_exec($ch );
            curl_close( $ch );
            $result = json_decode($result);
            error_log($result);
            if(isset($result->results)) {
                $fcmResponse = $result->results;
                if(count($fcmResponse) == 1) {
                    $fcmResponse_element = $fcmResponse[0];
                    if(isset($fcmResponse_element->error) && $fcmResponse_element->error == 'InvalidRegistration') {
                        $de_reg = KibarToken::updateTokenIs_register($to, 0);
                    }
                    if(isset($fcmResponse_element->message_id)) {
                        $updateSentProp = KibarNotification::updateSentProperty($args['entry_id'], 1);
                    }
                }
            }
        }
    }
    new KibarFCM();
endif;
