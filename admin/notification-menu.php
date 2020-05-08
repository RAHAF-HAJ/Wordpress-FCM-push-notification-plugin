<?php
/*
 * View all notifications
 * Actions: remove
 */
if (!defined('ABSPATH'))
    exit;

function kibar_notification_admin_menu()
{
    add_menu_page('All Notification', 'All Notification', KibarRole::KIBAR_MANAGE_NOTIFICATION_CAP, 'kibar_notification', 'view_all_notifications');
    add_submenu_page('kibar_notification', __('Add New Notification', 'kibar_notification'), __('Add New Notification', 'kibar_notification'), KibarRole::KIBAR_MANAGE_NOTIFICATION_CAP, 'set-notification', 'render_notification_form', '');
}
function view_all_notifications()
{
    if(isset($_GET['action']) && $_GET['action'] == 'remove') {
        if(isset($_GET['notification_id']) && !empty($_GET['notification_id'])) {
            $result = KibarNotification::deleteNotification($_GET['notification_id']);
            if($result['status'] == 'SUCCESS') {
                echo '<div class="update-message notice inline notice-success notice-alt" style="max-width: 1000px; padding: 15px;margin: 15px 0;"> Notification with ID:' . $_GET['notification_id'] .' has been removed.  </div>';
            }
        }
    }
    $notifications = KibarNotification::getNotification();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Notification</h1>
        <a href="<?php echo KibarNotification::getNewNotificationURL();?>" class="page-title-action">Add New</a>
    </div>
    <table class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
            <th scope="col" id="title" class="manage-column column-title column-primary sortable desc"><a
                    href="http://localhost/kahf/wp-admin/edit.php?post_type=reader&amp;orderby=title&amp;order=asc"><span>Title</span><span
                        class="sorting-indicator"></span></a></th>
            <th scope="col" id="date" class="manage-column column-date sortable asc"><a
                    href="http://localhost/kahf/wp-admin/edit.php?post_type=reader&amp;orderby=date&amp;order=desc"><span>Date</span><span
                        class="sorting-indicator"></span></a></th>
        </tr>
        </thead>
        <tbody id="the-list">
        <?php
        foreach ($notifications as $notification) {
            ?>
            <tr id="post-32" class="iedit author-self level-0 post-32 type-reader status-draft hentry">
                <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                    <div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
                    <strong><a class="row-title"
                               href="<?php echo KibarNotification::getNotificationUrlById($notification->id) ?>"
                               aria-label="“test” (Edit)"><?php echo stripslashes($notification->title); ?></a></strong>
                    <div class="row-actions"><span class="edit">
                            <a href="<?php echo KibarNotification::getNotificationUrlById($notification->id) ?>"
                                aria-label="Edit">Edit</a>
                        <a href="<?=admin_url()?>admin.php?page=kibar_notification&action=remove&notification_id=<?php echo $notification->id; ?>"
                            aria-label="Edit">remove</a>
                        </span>
                    </div>
                    <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span>
                    </button>
                </td>
                <td class="date column-date" data-colname="Date">Last Modified<br><abbr
                        title="2017/12/20 3:58:04 pm"><?php echo date('Y-m-d', $notification->update_date); ?></abbr>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php
}