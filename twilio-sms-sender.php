<?php
/**
 * Plugin Name: Twilio SMS Sender
 * Description: A simple plugin to send SMS messages using Twilio.
 * Version: 1.0.1
 * Author: Boon Band
 * Author URI: https://boon.band/
 */

// Import the required classes
use Twilio\Rest\Client;

// Add the Twilio PHP library to your project
require_once __DIR__ . '/vendor/autoload.php';

// Add the settings page to the WordPress admin menu
add_action('admin_menu', 'twilio_sms_sender_settings_page');

function twilio_sms_sender_settings_page()
{
    add_submenu_page(
        'options-general.php',
        'Twilio SMS Sender',
        'Twilio SMS Sender',
        'manage_options',
        'twilio-sms-sender',
        'twilio_sms_sender_settings_page_html'
    );
}

// Register and store the plugin settings
add_action('admin_init', 'twilio_sms_sender_settings_init');

function twilio_sms_sender_settings_init()
{
    register_setting('twilio_sms_sender', 'twilio_sms_sender_settings');

    add_settings_section(
        'twilio_sms_sender_section',
        __('Twilio Settings', 'twilio-sms-sender'),
        '',
        'twilio-sms-sender'
    );

    add_settings_field(
        'account_sid',
        __('Account SID', 'twilio-sms-sender'),
        'twilio_sms_sender_account_sid_render',
        'twilio-sms-sender',
        'twilio_sms_sender_section'
    );

    add_settings_field(
        'auth_token',
        __('Auth Token', 'twilio-sms-sender'),
        'twilio_sms_sender_auth_token_render',
        'twilio-sms-sender',
        'twilio_sms_sender_section'
    );

    add_settings_field(
        'twilio_number',
        __('Twilio Phone Number', 'twilio-sms-sender'),
        'twilio_sms_sender_twilio_number_render',
        'twilio-sms-sender',
        'twilio_sms_sender_section'
    );

    // Add this line to whitelist the 'twilio_sms_sender_settings' option
    add_filter('whitelist_options', 'twilio_sms_sender_whitelist_options');
}

// Add this new function to handle the whitelisting
function twilio_sms_sender_whitelist_options($whitelist_options)
{
    $whitelist_options['twilio_sms_sender'][] = 'twilio_sms_sender_settings';
    return $whitelist_options;
}

// Render the settings fields
function twilio_sms_sender_account_sid_render()
{
    $options = get_option('twilio_sms_sender_settings');
    ?>
    <input type='text' name='twilio_sms_sender_settings[account_sid]' value='<?php echo $options['account_sid']; ?>'>
    <?php
}

function twilio_sms_sender_auth_token_render()
{
    $options = get_option('twilio_sms_sender_settings');
    ?>
    <input type='text' name='twilio_sms_sender_settings[auth_token]' value='<?php echo $options['auth_token']; ?>'>
    <?php
}

function twilio_sms_sender_twilio_number_render()
{
    $options = get_option('twilio_sms_sender_settings');
    ?>
    <input type='text' name='twilio_sms_sender_settings[twilio_number]'
           value='<?php echo $options['twilio_number']; ?>'>
    <?php
}

// Display the plugin settings page
function twilio_sms_sender_settings_page_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
    <?php settings_errors('twilio_sms_sender'); ?>
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('twilio_sms_sender');
            do_settings_sections('twilio-sms-sender');
            submit_button(__('Save Settings', 'twilio-sms-sender'));
            ?>
        </form>

        <h2><?php _e('Test SMS Sending', 'twilio-sms-sender'); ?></h2>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="send_sms">
            <?php wp_nonce_field('send_sms_nonce', 'send_sms_nonce_field'); ?>
            <label for="recipient_number"><?php _e('Recipient Number', 'twilio-sms-sender'); ?>:</label>
            <input type="text" name="recipient_number" id="recipient_number">
            <br>
            <label for="sms_text"><?php _e('SMS Text', 'twilio-sms-sender'); ?>:</label>
            <input type="text" name="sms_text" id="sms_text">
            <br>
            <input type="submit" name="send_sms" value="<?php _e('Send SMS', 'twilio-sms-sender'); ?>">
        </form>
    </div>
    <?php
}

// Handle the test SMS sending form submission
add_action('admin_post_send_sms', 'handle_send_sms');

function handle_send_sms()
{
    if (!isset($_POST['send_sms_nonce_field']) || !wp_verify_nonce($_POST['send_sms_nonce_field'], 'send_sms_nonce')) {
        wp_die('Nonce verification failed.');
    }
    $recipient_number = sanitize_text_field($_POST['recipient_number']);
    $sms_text = sanitize_text_field($_POST['sms_text']);

    $result = send_sms($recipient_number, $sms_text);

    if (strpos($result, 'Error') === false) {
        $message = "SMS sent successfully. Message SID: " . $result;
        $type = 'success';
    } else {
        $message = "Failed to send SMS: " . $result;
        $type = 'error';
    }

    add_settings_error('twilio_sms_sender', 'twilio_sms_sender_message', $message, $type);
    set_transient('settings_errors', get_settings_errors(), 30);

    wp_redirect(admin_url('options-general.php?page=twilio-sms-sender'));
    exit;
}

function send_sms($recipient_number, $sms_text)
{
    $settings = get_option('twilio_sms_sender_settings');
    // Retrieve the Twilio settings from the options table
    $account_sid = $settings['account_sid'];
    $auth_token = $settings['auth_token'];
    $twilio_number = $settings['twilio_number'];

// Initialize the Twilio client
    $client = new Client($account_sid, $auth_token);

    try {
        // Send the SMS
        $message = $client->messages->create(
            $recipient_number,
            [
                'from' => $twilio_number,
                'body' => $sms_text
            ]
        );

        // Return the message SID as a confirmation
        return $message->sid;
    } catch (Exception $e) {
        // Return the error message if something goes wrong
        return 'Error: ' . $e->getMessage();
    }
}

function twilio_sms_sender_enqueue_styles($hook)
{
    if ($hook != 'settings_page_twilio-sms-sender') {
        return;
    }
    wp_enqueue_style('twilio_sms_sender_admin_styles', plugin_dir_url(__FILE__) . 'admin-style.css');
}

add_action('admin_enqueue_scripts', 'twilio_sms_sender_enqueue_styles');
