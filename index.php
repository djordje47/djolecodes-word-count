<?php
/**
 * Plugin Name: Word count
 * Description: Counts the words in the posts and displays it in the post. Simple.
 * Version: 1.0.0
 * Author: Djordje Arsenovic
 * Author URI: https://djolecodes.com
 */

class DjoleCodesWordCount
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
    }

    public function addSettingsPage()
    {
        add_options_page('Word Count Settings', 'Word Count', 'manage_options', 'dj-word-count-settings-page', [$this, 'settingsPage']);
    }

    public function settingsPage()
    { ?>
        <div class="wrap">
            <h1>Word count settings</h1>

        </div>
    <?php }
}

$djoleCodesWordCount = new DjoleCodesWordCount();

