<?php
/**
 * Plugin Name: DjoleCodes - Word count
 * Description: Counts the words in the posts and displays it in the post. Simple.
 * Version: 1.0.0
 * Author: Djordje Arsenovic
 * Author URI: https://djolecodes.com
 * Text Domain: dj_wcp
 * Domain Path: /languages
 */

class DjoleCodesWordCount
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'settings']);
        add_filter('the_content', [$this, 'ifWrap']);
        add_action('init', [$this, 'languages']);
    }

    /**
     * Sets up translations for when user changes the WordPress language
     * @return void
     */
    public function languages(): void
    {
        load_plugin_textdomain('dj_wcp', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * @param $content
     * @return mixed
     */
    public function ifWrap($content)
    {
        if ((is_main_query() && is_single()) && (
                get_option('dj_wcp_display_word_count', '1') ||
                get_option('dj_wcp_char_count', '1') ||
                get_option('dj_wcp_display_reading_time', '1'))) {
            return $this->createHTML($content);
        }

        return $content;
    }

    /**
     * @param $content
     * @return string
     */
    public function createHTML($content): string
    {
        $title = get_option('dj_wcp_title', 'Post stats');
        $location = get_option('dj_wcp_location', '0');
        $displayWordCount = get_option('dj_wcp_display_word_count', '1');
        $displayReadingTime = get_option('dj_wcp_display_reading_time', '1');
        $displayCharacterCount = get_option('dj_wcp_char_count', '1');

        $html = '<h3>' . esc_html($title) . '</h3> <p>';
        if ($displayWordCount || $displayReadingTime) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if ($displayWordCount) {
            $html .= esc_html__('This post has', 'dj_wcp') . ' ' . $wordCount . ' ' . esc_html__('words.', 'dj_wcp') . '<br/>';
        }

        if ($displayCharacterCount) {
            $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters. <br/>';
        }

        if ($displayReadingTime) {
            $html .= 'This post will take ' . round($wordCount / 225) . ' minute(s) to read. <br/>';
        }

        $html .= '</p>';

        if ($location === '0') {
            return $html . $content;
        }

        return $content . $html;
    }

    /**
     * Adds the page menu with the attributes
     * -Title of the menu item
     * -Title of the page
     * -Permissions (only the ones who can manage the options - Admins)
     * -Slug of the page for the url
     * -Callback function
     * @return void
     */
    public function addSettingsPage(): void
    {
        add_options_page('Word Count Settings', esc_html__('Word Count', 'dj_wcp'), 'manage_options', 'dj-word-count-settings-page', [$this, 'settingsPage']);
    }

    /**
     * Creates the page with the form
     * @return void
     */
    public function settingsPage(): void
    { ?>
        <div class="wrap">
            <h1>Word count settings</h1>
            <!-- WP knows what to do with the action -->
            <form action="options.php" method="POST">
                <!-- Adding nonce values and permission things-->
                <?php settings_fields('dj_word_count_plugin'); ?>
                <!-- Displays the section and the fields -->
                <?php do_settings_sections('dj-word-count-settings-page'); ?>
                <!-- Displays the submit button -->
                <?php submit_button(); ?>
            </form>
        </div>
    <?php }

    /**
     * Creates the section
     * Main logic of the plugin.
     * This function registers the fields as well as the rows in the options table.
     * @return void
     */
    public function settings(): void
    {
        /**
         * Add the section to the admin page
         */
        add_settings_section('dj_wcp_first_section', null, null, 'dj-word-count-settings-page');
        /**
         * Add the fields to the section
         */
        add_settings_field('dj_wcp_location', 'Display location', [$this, 'locationFieldHtml'], 'dj-word-count-settings-page', 'dj_wcp_first_section');
        add_settings_field('dj_wcp_title', 'Stats title', [$this, 'titleFieldHtml'], 'dj-word-count-settings-page', 'dj_wcp_first_section');
        add_settings_field('dj_wcp_display_word_count', 'Display word count', [$this, 'checkBoxHtml'], 'dj-word-count-settings-page', 'dj_wcp_first_section', ['option_name' => 'dj_wcp_display_word_count']);
        add_settings_field('dj_wcp_char_count', 'Display character count', [$this, 'checkBoxHtml'], 'dj-word-count-settings-page', 'dj_wcp_first_section', ['option_name' => 'dj_wcp_char_count']);
        add_settings_field('dj_wcp_display_reading_time', 'Display reading time', [$this, 'checkBoxHtml'], 'dj-word-count-settings-page', 'dj_wcp_first_section', ['option_name' => 'dj_wcp_display_reading_time']);
        /**
         * Register the wp_options row for saving the settings value to the database
         */
        register_setting('dj_word_count_plugin', 'dj_wcp_location', ['sanitize_callback' => [$this, 'locationValidation'], 'default' => '0']);
        register_setting('dj_word_count_plugin', 'dj_wcp_title', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'Post stats']);
        register_setting('dj_word_count_plugin', 'dj_wcp_display_word_count', ['sanitize_callback' => 'sanitize_text_field', 'default' => '1']);
        register_setting('dj_word_count_plugin', 'dj_wcp_char_count', ['sanitize_callback' => 'sanitize_text_field', 'default' => '0']);
        register_setting('dj_word_count_plugin', 'dj_wcp_display_reading_time', ['sanitize_callback' => 'sanitize_text_field', 'default' => '1']);
    }

    /**
     * @param $input
     * @return string
     */
    public function locationValidation($input): string
    {
        if ($input != '0' && $input != '1') {
            // Adds wp error
            add_settings_error('dj_wcp_location', 'dj_wcp_location_error', 'Display location must be beginning or end of the post.');
            // Sets value to the default
            return get_option('dj_wcp_location');
        }

        return $input;
    }

    /**
     * Receives the argument array that has option name, so we can create
     * three checkboxes with the same function.
     * @param array $args
     * @return void
     */
    public function checkBoxHtml(array $args): void
    {
        ?>
        <input type="checkbox"
               value="1" <?php checked(get_option($args['option_name'])) ?>
               name="<?= $args['option_name'] ?>"/>
    <?php }

    /**
     * Creates the input field for the title
     * Uses esc_attr for sanitization
     * @return void
     */
    public function titleFieldHtml(): void
    { ?>
        <input type="text" value="<?= esc_attr(get_option('dj_wcp_title')); ?>" name="dj_wcp_title"/>
    <?php }

    /**
     * Creates the location select
     * Uses selected() fn that prints the selected string if it finds the match
     * between the given values
     * @return void
     */
    public function locationFieldHtml(): void
    { ?>
        <select name="dj_wcp_location" id="dj_wcp_location">
            <option value="0" <?php selected(get_option('dj_wcp_location'), '0') ?>>Beginning of the post</option>
            <option value="1" <?php selected(get_option('dj_wcp_location'), '1') ?>>End of the post</option>
        </select>
    <?php }
}

$djoleCodesWordCount = new DjoleCodesWordCount();