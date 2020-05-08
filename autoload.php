<?php
if (!defined('ABSPATH'))
    exit;
$dirName = dirname(__FILE__);
//Classes
require_once("$dirName/classes/KibarToken.php");
require_once ("$dirName/classes/KibarNotification.php");
require_once ("$dirName/classes/KibarFCM.php");
require_once ("$dirName/classes/KibarTopic.php");
require_once ("$dirName/classes/KibarRoles.php");
//menu pages
require_once ("$dirName/admin/notification-form.php");
require_once ("$dirName/admin/notification-menu.php");
//API
//require_once ("$dirName/api/v1/rules.php");
//require_once ("$dirName/api/v1/callback/kahf-callback.php");


