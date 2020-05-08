<?php
/**
 * Create Notification class
 */
if (!defined('ABSPATH'))
    exit;

if (!class_exists("KibarNotification")):
    class KibarNotification
    {
        public static $hasImage = false;
        public static $hasTopic = true;

        public static $NOTIFICATION_TABLE_NAME = 'notification';
        public static $NOTIFICATION_QUEUE_TABLE_NAME = 'notification_queue';
        /**
         * KibarNotification constructor.
         */
        function __construct()
        {
            add_action('check_notification', [$this, 'readNotificationQueue']);
            self::createNotificationTable();
        }

        /**
         * create notification table in database
         */
        private static function createNotificationTable() {

            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            // creates notification in database if not exists
            $table = $wpdb->prefix . "notification";
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS $table (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `title` text NOT NULL,
                      `content` text NOT NULL,
                      `image` text NOT NULL,
                      `is_deleted` BOOLEAN NOT NULL DEFAULT FALSE,
                      `created_date` int(11) NOT NULL,
                      `update_date` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) $charset_collate;";
            dbDelta($sql);

            $table = $wpdb->prefix . "notification_queue";
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS $table (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `notification_id` int(11) NOT NULL,
                      `title` text NOT NULL,
                      `content` text NOT NULL,
                      `image` text NOT NULL,
                      `token_id` int(11) NOT NULL,
                      `is_sent` BOOLEAN NOT NULL DEFAULT FALSE,
                      `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
                      `created_date` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) $charset_collate;";
            dbDelta($sql);


        }
        //get Main notification table name
        private static function getTableName() {
            global $wpdb;
            return $wpdb->prefix . self::$NOTIFICATION_TABLE_NAME;
        }

        //get Main notification queue name
        private static function getQueueTableName() {
            global $wpdb;
            return $wpdb->prefix . self::$NOTIFICATION_QUEUE_TABLE_NAME;
        }

        /**
         * @param $args['title'] notification title
         * @param $args['content'] notification content (Message)
         *
         * @return mixed
         */
        public static function setNotification($args)
        {
            //TODO sent args['user_id'] if this was a user notification
            //TODO after creating the user notification, pass the user_id as a second parameter to the setNotificationQueue
            global $wpdb;
            $result['status'] = 'failed';
            $result['actions'] = [];
            $message = new stdClass();
            $notification_id = null;
            if(!isset($args['title']) || empty($args['title'])) {
                $message->message = 'Title is required';
                $message->code = 'missing_title';
                $result['actions'][] = $message;
            }
            if(!isset($args['content']) || empty($args['content'])) {
                $message->message = 'Content is required';
                $message->code = 'missing_content';
                $result['actions'][] = $message;
            }
            if(count($result['actions']) > 0) {
                return $result;
            }
            $url = self::insertNotificationImageAsAttachment();
            $fieldsToUpdate = array(
                'title' => $args['title'],
                'content' => $args['content'],
                'update_date' => strtotime('now')
            );
            if(!empty($url)) {
                $fieldsToUpdate['image'] = $url;
            }
            if(self::$hasTopic && !empty($args['topic'])) {
                $fieldsToUpdate['topic'] = $args['topic'];
            }
            //if this is an old notification update the old one.
            if(isset($args['id']) && !empty($args['id']) && is_numeric($args['id'])) {
                if(self::isPrevNotification($args['id'])) {
                    $affected_rows = $wpdb->update(self::getTableName(),
                        $fieldsToUpdate,
                        array(
                            'id' => $args['id']
                        )
                    );
                    if($affected_rows) {
                        $result['status'] = 'SUCCESS';
                        $notification_id = $args['id'];
                    }
                    else {
                        $message->message = 'Data hasn\'t been updated!';
                        $message->code = 'updating_error';
                        $result['actions'][] = $message;
                    }
                }
                else {
                    $message->message = 'The notification id doesn\'t  exist or it has been deleted';
                    $message->code = 'wrong_id_or_deleted_id';
                    $result['actions'][] = $message;
                }
            }
            //if there was no id, insert a new row
            else {
                $fieldsToInsert = array(
                    'title' => $args['title'],
                    'content' => $args['content'],
                    'created_date' => strtotime('now'),
                    'update_date' => strtotime('now'),
                );
                if(!empty($url)) {
                    $fieldsToInsert['image'] = $url;
                }
                if(self::$hasTopic && !empty($args['topic'])) {
                    $fieldsToInsert['topic'] = $args['topic'];
                }
                $affected_rows = $wpdb->insert(self::getTableName(),
                    $fieldsToInsert
                );
                if($affected_rows) {
                    $result['status'] = 'SUCCESS';
                    $notification_id = $wpdb->insert_id;
                }
                else {
                    $message->message = 'There was a database error while insertion';
                    $message->code = 'insertion_error';
                    $result['actions'][] = $message;
                }
            }
            //Trigger setNotificationRow after insertion if the notification doesn't support topics
            if($result['status'] == 'SUCCESS') {
                if(!self::$hasTopic) {
                    self::setNotificationQueue($notification_id);
                }
                else {
                    $topic = get_term_by('id', $args['topic'], 'topic');
                    $topic = $topic->name;
                    $to = '/topics/'. $topic;
                    $notification = new stdClass();
                    $notification->id = $notification_id;
                    $notification->title = $args['title'];
                    $notification->content = $args['content'];
                    $notification->image = $url;
                    $args = array(
                        'to' => $to,
                        'notification' => $notification
                    );
                    KibarFCM::sendFCMMsg($args);

                }
            }
            return $result;
        }

        /**
         * Delete existed notification
         * @param $id notification_id
         * @return mixed
         */
        public static function deleteNotification($id) {
            global $wpdb;
            $result['status'] = 'failed';
            $result['actions'] = [];
            $message = new stdClass();
            if(isset($id) && !empty($id) && is_numeric($id)) {
                if(self::isPrevNotification($id)) {
                    $affected_rows = $wpdb->update(self::getTableName(),
                        array(
                            'is_deleted' => 1,
                            'update_date' => strtotime('now')
                        ),
                        array(
                            'id' => $id
                        )
                    );
                    if($affected_rows) {
                        $result['status'] = 'SUCCESS';
                    }
                    else {
                        $message->message = 'Data hasn\'t been updated!';
                        $message->code = 'updating_error';
                        $result['actions'][] = $message;
                    }
                }
            }
            else {
                $message->message = 'The notification id doesn\'t  exist';
                $message->code = 'wrong_id';
                $result['actions'][] = $message;
            }
            return $result;
        }
        private static function isPrevNotification($id) {
            global $wpdb;
            $query = 'SELECT COUNT(id) FROM `wp_notification` WHERE is_deleted = 0 AND id =' . $id ;
            $result = $wpdb->get_var($query);
            if($result > 0 ) {
                return true;
            }
            return false;
        }

        /**Insert notification image from $_FILES
         * @return string
         */
        public static function insertNotificationImageAsAttachment() {
            $url = '';
            if(isset($_FILES['image'])) {
                $file = wp_upload_bits($_FILES['image']['name'], null, file_get_contents($_FILES['image']['tmp_name']));
                if($file['error'] === false) {
                    $url = $file['url'];
                }
            }
            return $url;
        }

        /** Get notification by ID
         * @param $id
         * @return array|null|object|void
         */
        public static function getNotificationById($id) {
            global $wpdb;
            $tabel = self::getTableName();
            $query = "SELECT * FROM $tabel WHERE `id` = "  . $id;
            $results = $wpdb->get_row($query);
            return $results;
        }
        //Not used yet
        public static function getNotificationImageURL($notification_id) {
            global $wpdb;
            $tabel = self::getTableName();
            $query = "SELECT image FROM $tabel WHERE `id` = "  . $notification_id;
            $results = $wpdb->get_var($query);
            return $results;
        }
        /**Get all notifications if there was no specific ID
         * Or get the notification with that id
         * @param bool $id
         * @return array|null|object
         */
        public static function getNotification($id = false, $orderBy=false) {
            global $wpdb;
            $query = 'SELECT * FROM `wp_notification` WHERE is_deleted = 0 ';
            if($id !== false) {
                $query .= ' AND id = '. $id;
            }
            if($orderBy !== false) {
                $query .= ' ORDER BY' . $orderBy;
            }
            $results = $wpdb->get_results($query);
            return $results;
        }
        //Function for debugging
        public static function getNotificationQueue($id = false, $orderBy=false) {
            global $wpdb;
            $table = self::getQueueTableName();
            $query = "SELECT * FROM $table";
            if($id !== false) {
                $query .= ' AND id = '. $id;
            }
            if($orderBy !== false) {
                $query .= ' ORDER BY' . $orderBy;
            }
            $results = $wpdb->get_results($query);
            return $results;
        }

        /**Get notification page id
         * @param $id
         * @return string
         */
        public static function getNotificationUrlById($id) {
            return admin_url().'admin.php?page=set-notification&id=' . $id;
        }

        /**Get new notification page url
         * @return string
         */
        public static function getNewNotificationURL() {
            return admin_url().'admin.php?page=set-notification';
        }

        /**Notification Queue functions, take all the tokens and fill them in the queue to send FCM messages
         * @param $notification_id
         * @param null $user_id
         */
        private static function setNotificationQueue($notification_id, $user_id = null) {
            global $wpdb;
            $query = 'SELECT * FROM ' . KibarToken::getTableName() . ' Where is_registered=1';
            //TODO Add a user_id field to the token
            if(!is_null($user_id)) {
                $query .= ' user_id = ' . $user_id;
            }
            $results = $wpdb->get_results($query);
            if(count($results) > 0) {
                foreach ($results as $result) {
                    $token_id = $result->id;
                    self::setQueueRow($notification_id, $token_id);
                }
                self::triggerSendNotification();
            }
        }

        static function triggerSendNotification() {
            wp_schedule_single_event(time() + 120, 'check_notification' );
            self::readNotificationQueue();
        }
        /** Insert one row in notification queue
         * @param $notification_id
         * @param $token_id
         * @return bool|false|int
         */
        private static function setQueueRow($notification_id, $token_id) {
            global $wpdb;
            $table = self::getQueueTableName();
            $notification = self::getNotificationById($notification_id);
            $affected_rows = $wpdb->insert($table,
                array(
                        'notification_id' => $notification_id,
                        'token_id' => $token_id,
                        'title' => $notification->title,
                        'image' => $notification->image,
                        'content' => $notification->content,
                        'created_date' => strtotime('now')
                    )
                );
            if(!$affected_rows) {
                return false;
            }
            return $affected_rows;
        }

        /** update is_sent for notifications in the queue
         * @param $id
         * @param int $is_sent
         * @return false|int
         */
        public static function updateSentProperty($id, $is_sent = 0)
        {
            global $wpdb;
            $table = self::getQueueTableName();
            $affected_rows = $wpdb->update($table,
                array(
                    'is_sent' => $is_sent
                ),
                array(
                    'id' => $id
                )
            );
            return $affected_rows;
        }

        /**Start sending fcm messages for all notifications those with is_sent = false
         * @return int
         */
        public static function readNotificationQueue() {
            global $wpdb;
            //TODO
            // 1- Add user_id field to token table
            // 2- if the notification isn't an admin
            //TODO Use This when There is a user Id with multiple tokens
            $tokens = [];
            $table = self::getQueueTableName();
            $query = "SELECT * FROM $table WHERE `is_sent` = 0 LIMIT 100";
            //Admin Notifications
            $results = $wpdb->get_results($query);
            if(count($results) < 1) {
                return 0;
            }
            else {
                foreach ($results as $result) {
                    $token_id = $result->token_id;
                    $token = KibarToken::getTokenByID($token_id);
                    $notification = self::getNotificationById($result->notification_id);
                    //Call FCM For each token row
                    $args = array(
                        'entry_id' => $result->id,
                        'to' => $token,
                        'notification' => $notification
                    );

                    KibarFCM::sendFCMMsg($args);
                    //set each token is_sent to false
                }
            }
        }
    }
    new KibarNotification();
endif;