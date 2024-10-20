<?php
/**
 * Plugin Name: Spam Comment Marker
 * Plugin URI: shahidbasheer.me
 * Description: Automatically marks spam comments based on predefined criteria.
 * Version: 1.0
 * Author: Shahid Bashir
 * Author URI: shahidbasheer.me
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants
define('SCM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SCM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files (if any in future)
// require_once SCM_PLUGIN_DIR . 'includes/some-file.php';

// Initialize the plugin
function scm_init() {
    // Hook into comment posting
    add_action('preprocess_comment', 'scm_detect_spam');
    
    // Add admin menu
    add_action('admin_menu', 'scm_add_admin_menu');
}

add_action('plugins_loaded', 'scm_init');

/**
 * Detects spam comments based on predefined keywords.
 *
 * @param array $commentdata The comment data.
 * @return array Modified comment data.
 */
function scm_detect_spam($commentdata) {
    // Retrieve spam keywords from settings
    $keywords_option = get_option('scm_spam_keywords', 'viagra, casino, free money, click here');
    $spam_keywords = array_map('trim', explode(',', strtolower($keywords_option)));

    // Check comment content
    $content = strtolower($commentdata['comment_content']);

    foreach ($spam_keywords as $keyword) {
        if (strpos($content, $keyword) !== false) {
            // Mark as spam
            add_filter('pre_comment_approved', function($approved) {
                return 'spam';
            });

            // Optionally, notify admin or take other actions

            break;
        }
    }

    return $commentdata;
}


/**
 * Adds an admin menu for the plugin.
 */
function scm_add_admin_menu() {
    add_options_page(
        'Spam Comment Marker',
        'Spam Comment Marker',
        'manage_options',
        'spam-comment-marker',
        'scm_settings_page'
    );
}

/**
 * Renders the settings page.
 */
function scm_settings_page() {
    // Check if user has submitted the settings
    if (isset($_POST['scm_submit'])) {
        // Verify nonce for security
        check_admin_referer('scm_settings_verify');

        // Update spam keywords
        $keywords = sanitize_textarea_field($_POST['scm_spam_keywords']);
        update_option('scm_spam_keywords', $keywords);

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Get current spam keywords
    $current_keywords = get_option('scm_spam_keywords', 'viagra, casino, free money, click here');

    ?>
    <div class="wrap">
        <h1>Spam Comment Marker Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('scm_settings_verify'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Spam Keywords</th>
                    <td>
                        <textarea name="scm_spam_keywords" rows="5" cols="50"><?php echo esc_textarea($current_keywords); ?></textarea>
                        <p class="description">Enter comma-separated keywords that are considered spam.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Settings', 'primary', 'scm_submit'); ?>
        </form>
    </div>
    <?php
}

