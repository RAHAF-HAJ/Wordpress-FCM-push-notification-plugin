<?php

/*
 * Create a form to create, update notifications
 */
if (!defined('ABSPATH'))
    exit;

function render_notification_form()
{
    $result = KibarNotification::setNotification($_POST);
    if($result['status'] == 'SUCCESS') {
        echo '<div class="update-message notice inline notice-success notice-alt" style="max-width: 1000px; padding: 15px;margin: 15px 0;"> Notification has been set. </div>';
    }
    if(isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];
        $info = KibarNotification::getNotification($id);
        $info = $info[0];
    }
    if(!isset($info)) {
        //update form
        ?>
        <form method="post" enctype='multipart/form-data' action="">
            <div class="wrap" style="max-width: 1000px">
                <h1 class="wp-heading-inline">Add New Notification</h1>
                <hr class="wp-header-end">
                <div id="poststuff">
                    <div id="lost-connection-notice" class="error hidden">
                        <p><span class="spinner"></span> <strong>Connection lost.</strong> Saving has been disabled
                            until
                            you’re
                            reconnected. <span class="hide-if-no-sessionstorage">We’re backing up this post in your browser, just in case.</span>
                        </p>
                    </div>
                    <div id="post-body-content" style="position: relative;">
                        <div id="titlediv">
                            <div id="titlewrap">
                                <!--                    <label class="" id="title-prompt-text" for="title">Enter title here</label>-->
                                <input type="text" name="title" size="30" value="" id="title" spellcheck="true"
                                       autocomplete="off" placeholder="Enter title here">
                            </div>
                        </div>
                        <div class="inside">
                            <div id="edit-slug-box" class="hide-if-no-js">
                            </div>
                        </div>
                        <div id="postdivrich" class="postarea wp-editor-expand">
                            <div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap html-active has-dfw"
                                 style="padding-top: 10px;">
                        <textarea style="width: 100%" placeholder="Enter content here" type="textarea" name="content"
                                  rows="12" cols="200" id="content"></textarea>
                            </div>
                        </div>
                        <?php if(KibarNotification::$hasImage) { ?>
                        <div id="postFile" class="postarea wp-editor-expand" style="padding-top: 40px">
                            <div id="wp-content-file-wrap" class="wp-core-ui wp-editor-wrap html-active has-dfw"
                                 style="padding-top: 10px;">
                                <h2 style="-moz-padding-start: 0; -webkit-padding-start: 0" class="wp-heading-inline">
                                    Upload image </h2>
                                <!--                            <input type="file" name="image" accept="image/*" placeholder="Upload image">-->
                                <input type="file" id="example-jpg-file" accept="image/*" name="image" value=""/>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if(KibarNotification::$hasTopic) {
                            ?>
                            <div id="topic" class="postarea wp-editor-expand">
                                <div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap html-active has-dfw"
                                     style="padding-top: 10px;">
                                    <h2 style="-moz-padding-start: 0; -webkit-padding-start: 0" class="wp-heading-inline">
                                        Topic </h2>
                                    <?php
                                    echo KibarTopic::getTopicTerms();
                                    ?>
                                </div>
                            </div>
                            <?php
                        } ?>

                        <div id="postFile" class="postarea wp-editor-expand" style="padding-top: 20px">
                            <div class="wp-core-ui wp-editor-wrap html-active has-dfw"
                                 style="padding-top: 10px;">
                                <button type="submit" name="publish" id="publish"
                                        class="button button-primary button-large"
                                        value="Publish">
                                    publish
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
        <?php
    }
    else {
        //Add form
        ?>
        <form method="post"  enctype='multipart/form-data' action="">
            <div class="wrap" style="max-width: 1000px">
                <h1 class="wp-heading-inline">Edit Notification</h1>
                <input type="hidden" value="<?= $info->id;?>" name="id">
                <hr class="wp-header-end">
                <div id="poststuff">
                    <div id="lost-connection-notice" class="error hidden">
                        <p><span class="spinner"></span> <strong>Connection lost.</strong> Saving has been disabled until
                            you’re
                            reconnected. <span class="hide-if-no-sessionstorage">We’re backing up this post in your browser, just in case.</span>
                        </p>
                    </div>
                    <div id="post-body-content" style="position: relative;">
                        <div id="titlediv">
                            <div id="titlewrap">
                                <!--                    <label class="" id="title-prompt-text" for="title">Enter title here</label>-->
                                <input type="text" name="title" size="30" value="<?= stripslashes($info->title);?>" id="title" spellcheck="true"
                                       autocomplete="off" placeholder="Enter title here">
                            </div>
                        </div>
                        <div class="inside">
                            <div id="edit-slug-box" class="hide-if-no-js">
                            </div>
                        </div>
                        <div id="postdivrich" class="postarea wp-editor-expand">
                            <div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap html-active has-dfw"
                                 style="padding-top: 10px;">
                        <textarea style="width: 100%" placeholder="Enter content here" type="textarea" name="content"
                                  rows="12" cols="200" id="content"><?= stripslashes($info->content);?></textarea>
                            </div>
                        </div>
                        <?php
                        /* If notification support images, display upload input */
                        if(KibarNotification::$hasImage) { ?>
                        <div id="postFile" class="postarea wp-editor-expand" style="padding-top: 40px">
                            <div id="wp-content-file-wrap" class="wp-core-ui wp-editor-wrap html-active has-dfw"
                                 style="padding-top: 10px;">
                                <?php
                                    if(!empty($info->image)) {
                                        ?>
                                        <img width="250px" src="<?= $info->image;?>" title="<?= $info->title;?>">
                                        <?php
                                    }
                                ?>
                                
                                <h2 style="-moz-padding-start: 0; -webkit-padding-start: 0" class="wp-heading-inline">
                                    Upload image </h2>
                                    
                                <input type="file" id="example-jpg-file"  accept="image/*" name="image" value="" />
                            </div>
                        </div>
                        <?php } ?>

                        <?php if(KibarNotification::$hasTopic) {
                        ?>
                            <div id="topic" class="postarea wp-editor-expand">
                                <div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap html-active has-dfw"
                                     style="padding-top: 10px;">
                                    <h2 style="-moz-padding-start: 0; -webkit-padding-start: 0" class="wp-heading-inline">
                                        Topic </h2>
                                    <?php

                                        echo KibarTopic::getTopicTerms($info->topic);
                                    ?>
                                </div>
                            </div>
                        <?php
                        } ?>
                        <div id="postFile" class="postarea wp-editor-expand" style="padding-top: 20px">
                            <div  class="wp-core-ui wp-editor-wrap html-active has-dfw"
                                  style="padding-top: 10px;">
                                <button type="submit" name="publish" id="publish" class="button button-primary button-large"
                                        value="Publish">
                                    publish
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
        <?php
    }
}