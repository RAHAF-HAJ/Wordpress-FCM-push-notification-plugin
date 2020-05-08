<?php
if (!defined('ABSPATH'))
    exit;
/**
 * Creating Roles and capabilities for this plugin
 */

if (!class_exists("KibarRole")):
    class KibarRole
    {
        const KIBAR_MANAGE_NOTIFICATION_CAP = 'manage_notifications';
        function __construct()
        {
            //Add new capability to manage notification
            $role = get_role('administrator');
            if(!$role->has_cap(self::KIBAR_MANAGE_NOTIFICATION_CAP)) {
                $role->add_cap(self::KIBAR_MANAGE_NOTIFICATION_CAP);
            }
        }
    }
    new KibarRole();
endif;