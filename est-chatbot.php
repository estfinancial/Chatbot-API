<?php
/**
 * Plugin Name: est Financial Chatbot
 * Plugin URI: https://est.com.au
 * Description: AI-powered chatbot for est Financial services with appointment booking and Go High Level integration
 * Version: 1.0.0
 * Author: est Financial
 * License: GPL v2 or later
 * Text Domain: est-chatbot
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EST_CHATBOT_VERSION', '1.0.0');
define('EST_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EST_CHATBOT_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Main plugin class
class EstChatbot {
    
    public function __construct() {
        add_action("init", array($this, "init"));
        add_action("wp_enqueue_scripts", array($this, "enqueue_scripts"));
        add_action("wp_footer", array($this, "render_chatbot"));
        add_action("admin_menu", array($this, "admin_menu"));
        add_action("admin_init", array($this, "admin_init"));
        
        // AJAX handlers
        add_action("wp_ajax_est_chatbot_chat", array($this, "handle_chat"));
        add_action("wp_ajax_nopriv_est_chatbot_chat", array($this, "handle_chat"));
        add_action("wp_ajax_est_chatbot_get_stats", array($this, "handle_get_stats"));
        add_action("wp_ajax_est_chatbot_get_conversations", array($this, "handle_get_conversations"));
        add_action("wp_ajax_est_chatbot_get_leads", array($this, "handle_get_leads"));
        add_action("wp_ajax_est_chatbot_get_conversation_messages", array($this, "handle_get_conversation_messages"));
        add_action("wp_ajax_est_chatbot_update_lead_status", array($this, "handle_update_lead_status"));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, "activate"));
        register_deactivation_hook(__FILE__, array($this, "deactivate"));
    }
    
    public function init() {
        // Initialize plugin
        load_plugin_textdomain('est-chatbot', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function enqueue_scripts() {
        // Only load on frontend if chatbot is enabled
        if (get_option("est_chatbot_enabled", "1") == "1") {
            wp_enqueue_script(
                "est-chatbot-js",
                EST_CHATBOT_PLUGIN_URL . "assets/chatbot.js",
                array("jquery"),
                EST_CHATBOT_VERSION,
                true
            );
            
            wp_enqueue_style(
                "est-chatbot-css",
                EST_CHATBOT_PLUGIN_URL . "assets/chatbot.css",
                array(),
                EST_CHATBOT_VERSION
            );
            
            // Localize script with AJAX URL and nonce
            wp_localize_script("est-chatbot-js", "est_chatbot_ajax", array(
                "ajax_url" => admin_url("admin-ajax.php"),
                "nonce" => wp_create_nonce("est_chatbot_nonce"),
                "api_endpoint" => get_option("est_chatbot_api_endpoint", ""),
            ));
        }
        
        // Enqueue admin scripts and styles
        if (is_admin()) {
            wp_enqueue_script(
                "est-chatbot-admin-js",
                EST_CHATBOT_PLUGIN_URL . "assets/admin.js",
                array("jquery"),
                EST_CHATBOT_VERSION,
                true
            );
            wp_enqueue_style(
                "est-chatbot-admin-css",
                EST_CHATBOT_PLUGIN_URL . "assets/admin.css",
                array(),
                EST_CHATBOT_VERSION
            );
            wp_localize_script("est-chatbot-admin-js", "est_chatbot_admin_ajax", array(
                "ajax_url" => admin_url("admin-ajax.php"),
                "nonce" => wp_create_nonce("est_chatbot_admin_nonce"),
                "api_endpoint" => get_option("est_chatbot_api_endpoint", ""),
            ));
        }
    }
    
    public function render_chatbot() {
        if (get_option('est_chatbot_enabled', '1') == '1') {
            echo '<div id="est-chatbot-container"></div>';
        }
    }
    
    public function admin_menu() {
        add_menu_page(
            "est Chatbot",
            "est Chatbot",
            "manage_options",
            "est-chatbot",
            array($this, "dashboard_page"),
            "dashicons-format-chat",
            6
        );
        
        add_submenu_page(
            "est-chatbot",
            "Chatbot Dashboard",
            "Dashboard",
            "manage_options",
            "est-chatbot",
            array($this, "dashboard_page")
        );
        
        add_submenu_page(
            "est-chatbot",
            "Chatbot Conversations",
            "Conversations",
            "manage_options",
            "est-chatbot-conversations",
            array($this, "conversations_page")
        );
        
        add_submenu_page(
            "est-chatbot",
            "Chatbot Leads",
            "Leads",
            "manage_options",
            "est-chatbot-leads",
            array($this, "leads_page")
        );
        
        add_submenu_page(
            "est-chatbot",
            "Chatbot Settings",
            "Settings",
            "manage_options",
            "est-chatbot-settings",
            array($this, "settings_page")
        );
    }
    
    public function admin_init() {
        register_setting("est_chatbot_settings", "est_chatbot_enabled");
        register_setting("est_chatbot_settings", "est_chatbot_api_endpoint");
        register_setting("est_chatbot_settings", "est_chatbot_ghl_webhook");
        register_setting("est_chatbot_settings", "est_chatbot_position");
        register_setting("est_chatbot_settings", "est_chatbot_theme_color");
        register_setting("est_chatbot_settings", "est_chatbot_welcome_message");
        register_setting("est_chatbot_settings", "est_chatbot_appointment_confirmation_message");
        register_setting("est_chatbot_settings", "est_chatbot_appointment_email_subject");
        register_setting("est_chatbot_settings", "est_chatbot_appointment_email_body");
        
        add_settings_section(
            "est_chatbot_main_section",
            "Chatbot Configuration",
            array($this, "settings_section_callback"),
            "est-chatbot-settings"
        );
        
        add_settings_field(
            "est_chatbot_enabled",
            "Enable Chatbot",
            array($this, "enabled_field_callback"),
            "est-chatbot-settings",
            "est_chatbot_main_section"
        );
        
        add_settings_field(
            "est_chatbot_api_endpoint",
            "API Endpoint URL",
            array($this, "api_endpoint_field_callback"),
            "est-chatbot-settings",
            "est_chatbot_main_section"
        );
        
        add_settings_field(
            "est_chatbot_ghl_webhook",
            "Go High Level Webhook URL",
            array($this, "ghl_webhook_field_callback"),
            "est-chatbot-settings",
            "est_chatbot_main_section"
        );
        
        add_settings_field(
            "est_chatbot_position",
            "Chatbot Position",
            array($this, "position_field_callback"),
            "est-chatbot-settings",
            "est_chatbot_main_section"
        );
        
        add_settings_field(
            "est_chatbot_theme_color",
            "Theme Color",
            array($this, "theme_color_field_callback"),
            "est-chatbot-settings",
            "est_chatbot_main_section"
        );
        
        add_settings_section(
            "est_chatbot_messages_section",
            "Custom Messages & Emails",
            array($this, "messages_section_callback"),
            "est-chatbot-settings"
        );
        
        add_settings_field(
            "est_chatbot_welcome_message",
            "Welcome Message",
            array($this, "welcome_message_field_callback"),
            "est-chatbot-settings",
            "est_chatbot_messages_section"
        );
        
        add_settings_field(
            "est_chatbot_appointment_confirmation_message",
            "Appointment Confirmation Message",
            array($this, "appointment_confirmation_message_field_callback"),
            "est-chatbot-settings",
            "est_chatbot_messages_section"
        );
        
        add_settings_field(
            "est_chatbot_appointment_email_subject",
            "Appointment Email Subject",
            array($this, "appointment_email_subject_field_callback"),
            "est-chatbot-settings",
            "est_chatbot_messages_section"
        );
        
        add_settings_field(
            "est_chatbot_appointment_email_body",
            "Appointment Email Body",
            array($this, "appointment_email_body_field_callback"),
            "est-chatbot-settings",
            "est_chatbot_messages_section"
        );
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>est Financial Chatbot Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields("est_chatbot_settings");
                do_settings_sections("est-chatbot-settings");
                submit_button();
                ?>
            </form>
            
            <div class="card" style="margin-top: 20px;">
                <h2>Setup Instructions</h2>
                <ol>
                    <li>Deploy your chatbot backend service and enter the API endpoint URL above</li>
                    <li>Configure your Go High Level webhook URL for appointment integration</li>
                    <li>Choose your preferred chatbot position and theme color</li>
                    <li>Enable the chatbot to start using it on your website</li>
                </ol>
                
                <h3>Shortcode Usage</h3>
                <p>You can also embed the chatbot in specific pages or posts using the shortcode:</p>
                <code>[est_chatbot]</code>
            </div>
        </div>
        <?php
    }
    
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1>est Financial Chatbot Dashboard</h1>
            <div id="chatbot-dashboard-app">Loading Dashboard...</div>
        </div>
        <?php
    }
    
    public function conversations_page() {
        ?>
        <div class="wrap">
            <h1>est Financial Chatbot Conversations</h1>
            <div id="chatbot-conversations-app">Loading Conversations...</div>
        </div>
        <?php
    }
    
    public function leads_page() {
        ?>
        <div class="wrap">
            <h1>est Financial Chatbot Leads</h1>
            <div id="chatbot-leads-app">Loading Leads...</div>
        </div>
        <?php
    }
    
    public function settings_section_callback() {
        echo '<p>Configure your est Financial chatbot settings below.</p>';
    }
    
    public function enabled_field_callback() {
        $enabled = get_option('est_chatbot_enabled', '1');
        echo '<input type="checkbox" name="est_chatbot_enabled" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="est_chatbot_enabled">Enable the chatbot on your website</label>';
    }
    
    public function api_endpoint_field_callback() {
        $api_endpoint = get_option('est_chatbot_api_endpoint', '');
        echo '<input type="url" name="est_chatbot_api_endpoint" value="' . esc_attr($api_endpoint) . '" class="regular-text" />';
        echo '<p class="description">Enter the full URL of your deployed chatbot API (e.g., https://your-domain.com/api)</p>';
    }
    
    public function ghl_webhook_field_callback() {
        $ghl_webhook = get_option('est_chatbot_ghl_webhook', '');
        echo '<input type="url" name="est_chatbot_ghl_webhook" value="' . esc_attr($ghl_webhook) . '" class="regular-text" />';
        echo '<p class="description">Enter your Go High Level webhook URL for appointment integration</p>';
    }
    
    public function position_field_callback() {
        $position = get_option('est_chatbot_position', 'bottom-right');
        $positions = array(
            'bottom-right' => 'Bottom Right',
            'bottom-left' => 'Bottom Left',
            'top-right' => 'Top Right',
            'top-left' => 'Top Left'
        );
        
        echo '<select name="est_chatbot_position">';
        foreach ($positions as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($position, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }
    
    public function theme_color_field_callback() {
        $theme_color = get_option("est_chatbot_theme_color", "#e53e3e");
        echo "<input type=\"color\" name=\"est_chatbot_theme_color\" value=\"" . esc_attr($theme_color) . "\" />";
        echo "<p class=\"description\">Choose the primary color for your chatbot interface</p>";
    }
    
    public function messages_section_callback() {
        echo "<p>Customize the messages and emails sent by the chatbot.</p>";
    }
    
    public function welcome_message_field_callback() {
        $message = get_option("est_chatbot_welcome_message", "Hello! I\'m here to help you with est Financial services. I can provide information about our services or help you book an appointment. What would you like to do?");
        echo "<textarea name=\"est_chatbot_welcome_message\" rows=\"5\" class=\"large-text\">" . esc_textarea($message) . "</textarea>";
        echo "<p class=\"description\">This message is displayed when the chatbot first opens.</p>";
    }
    
    public function appointment_confirmation_message_field_callback() {
        $message = get_option("est_chatbot_appointment_confirmation_message", "Perfect! I\'ve received your appointment request:\n\n**Name:** {name}\n**Email:** {email}\n**Phone:** {phone}\n**Service:** {service}\n\nYour appointment request has been sent to our team. Someone will contact you within 24 hours to confirm your appointment time. Is there anything else I can help you with?");
        echo "<textarea name=\"est_chatbot_appointment_confirmation_message\" rows=\"7\" class=\"large-text\">" . esc_textarea($message) . "</textarea>";
        echo "<p class=\"description\">This message is displayed to the user after a successful appointment booking. Use placeholders like {name}, {email}, {phone}, {service}.</p>";
    }
    
    public function appointment_email_subject_field_callback() {
        $subject = get_option("est_chatbot_appointment_email_subject", "New Appointment Request from est Financial Chatbot");
        echo "<input type=\"text\" name=\"est_chatbot_appointment_email_subject\" value=\"" . esc_attr($subject) . "\" class=\"regular-text\" />";
        echo "<p class=\"description\">Subject line for the internal notification email.</p>";
    }
    
    public function appointment_email_body_field_callback() {
        $body = get_option("est_chatbot_appointment_email_body", "A new appointment request has been received via the est Financial Chatbot.\n\nDetails:\nName: {name}\nEmail: {email}\nPhone: {phone}\nService: {service}\nTimestamp: {timestamp}\n\nPlease contact the client to confirm the appointment.");
        echo "<textarea name=\"est_chatbot_appointment_email_body\" rows=\"10\" class=\"large-text\">" . esc_textarea($body) . "</textarea>";
        echo "<p class=\"description\">Body for the internal notification email. Use placeholders like {name}, {email}, {phone}, {service}, {timestamp}.</p>";
    }
    
    public function handle_chat() {
        // Verify nonce
        if (!wp_verify_nonce($_POST["nonce"], "est_chatbot_nonce")) {
            wp_die("Security check failed");
        }
        
        $api_endpoint = get_option("est_chatbot_api_endpoint", "");
        if (empty($api_endpoint)) {
            wp_send_json_error("API endpoint not configured");
            return;
        }
        
        $message = sanitize_text_field($_POST["message"]);
        $session = isset($_POST["session"]) ? $_POST["session"] : array();
        
        // Forward request to the chatbot API
        $response = wp_remote_post($api_endpoint . "/chat", array(
            "headers" => array("Content-Type" => "application/json"),
            "body" => json_encode(array(
                "message" => $message,
                "session" => $session,
                "ghl_webhook_url" => get_option("est_chatbot_ghl_webhook", ""),
                "welcome_message" => get_option("est_chatbot_welcome_message", ""),
                "appointment_confirmation_message" => get_option("est_chatbot_appointment_confirmation_message", ""),
                "appointment_email_subject" => get_option("est_chatbot_appointment_email_subject", ""),
                "appointment_email_body" => get_option("est_chatbot_appointment_email_body", ""),
            )),
            "timeout" => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error("Failed to connect to chatbot service");
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data["success"]) && $data["success"]) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error("Chatbot service error");
        }
    }
    
    public function handle_get_stats() {
        if (!current_user_can("manage_options")) {
            wp_die("You do not have sufficient permissions to access this page.");
        }
        
        if (!wp_verify_nonce($_POST["nonce"], "est_chatbot_admin_nonce")) {
            wp_die("Security check failed");
        }
        
        $api_endpoint = get_option("est_chatbot_api_endpoint", "");
        if (empty($api_endpoint)) {
            wp_send_json_error("API endpoint not configured");
            return;
        }
        
        $response = wp_remote_get($api_endpoint . "/admin/stats", array(
            "timeout" => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error("Failed to fetch stats from chatbot service: " . $response->get_error_message());
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data["success"]) && $data["success"]) {
            wp_send_json_success($data["data"]);
        } else {
            wp_send_json_error("Chatbot service error: " . (isset($data["error"]) ? $data["error"] : "Unknown error"));
        }
    }
    
    public function handle_get_conversations() {
        if (!current_user_can("manage_options")) {
            wp_die("You do not have sufficient permissions to access this page.");
        }
        
        if (!wp_verify_nonce($_POST["nonce"], "est_chatbot_admin_nonce")) {
            wp_die("Security check failed");
        }
        
        $api_endpoint = get_option("est_chatbot_api_endpoint", "");
        if (empty($api_endpoint)) {
            wp_send_json_error("API endpoint not configured");
            return;
        }
        
        $page = isset($_POST["page"]) ? intval($_POST["page"]) : 1;
        $per_page = isset($_POST["per_page"]) ? intval($_POST["per_page"]) : 20;
        
        $response = wp_remote_get(add_query_arg(array(
            "page" => $page,
            "per_page" => $per_page
        ), $api_endpoint . "/admin/conversations"), array(
            "timeout" => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error("Failed to fetch conversations from chatbot service: " . $response->get_error_message());
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data["success"]) && $data["success"]) {
            wp_send_json_success($data["data"]);
        } else {
            wp_send_json_error("Chatbot service error: " . (isset($data["error"]) ? $data["error"] : "Unknown error"));
        }
    }
    
    public function handle_get_leads() {
        if (!current_user_can("manage_options")) {
            wp_die("You do not have sufficient permissions to access this page.");
        }
        
        if (!wp_verify_nonce($_POST["nonce"], "est_chatbot_admin_nonce")) {
            wp_die("Security check failed");
        }
        
        $api_endpoint = get_option("est_chatbot_api_endpoint", "");
        if (empty($api_endpoint)) {
            wp_send_json_error("API endpoint not configured");
            return;
        }
        
        $page = isset($_POST["page"]) ? intval($_POST["page"]) : 1;
        $per_page = isset($_POST["per_page"]) ? intval($_POST["per_page"]) : 20;
        $status = isset($_POST["status"]) ? sanitize_text_field($_POST["status"]) : null;
        
        $args = array(
            "page" => $page,
            "per_page" => $per_page
        );
        if ($status) {
            $args["status"] = $status;
        }
        
        $response = wp_remote_get(add_query_arg($args, $api_endpoint . "/admin/leads"), array(
            "timeout" => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error("Failed to fetch leads from chatbot service: " . $response->get_error_message());
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data["success"]) && $data["success"]) {
            wp_send_json_success($data["data"]);
        } else {
            wp_send_json_error("Chatbot service error: " . (isset($data["error"]) ? $data["error"] : "Unknown error"));
        }
    }
    
    public function handle_get_conversation_messages() {
        if (!current_user_can("manage_options")) {
            wp_die("You do not have sufficient permissions to access this page.");
        }
        
        if (!wp_verify_nonce($_POST["nonce"], "est_chatbot_admin_nonce")) {
            wp_die("Security check failed");
        }
        
        $api_endpoint = get_option("est_chatbot_api_endpoint", "");
        if (empty($api_endpoint)) {
            wp_send_json_error("API endpoint not configured");
            return;
        }
        
        $conversation_id = isset($_POST["conversation_id"]) ? intval($_POST["conversation_id"]) : 0;
        if ($conversation_id === 0) {
            wp_send_json_error("Invalid conversation ID");
            return;
        }
        
        $response = wp_remote_get($api_endpoint . "/admin/conversation/" . $conversation_id . "/messages", array(
            "timeout" => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error("Failed to fetch messages from chatbot service: " . $response->get_error_message());
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data["success"]) && $data["success"]) {
            wp_send_json_success($data["data"]);
        } else {
            wp_send_json_error("Chatbot service error: " . (isset($data["error"]) ? $data["error"] : "Unknown error"));
        }
    }
    
    public function handle_update_lead_status() {
        if (!current_user_can("manage_options")) {
            wp_die("You do not have sufficient permissions to access this page.");
        }
        
        if (!wp_verify_nonce($_POST["nonce"], "est_chatbot_admin_nonce")) {
            wp_die("Security check failed");
        }
        
        $api_endpoint = get_option("est_chatbot_api_endpoint", "");
        if (empty($api_endpoint)) {
            wp_send_json_error("API endpoint not configured");
            return;
        }
        
        $lead_id = isset($_POST["lead_id"]) ? intval($_POST["lead_id"]) : 0;
        $status = isset($_POST["status"]) ? sanitize_text_field($_POST["status"]) : null;
        $notes = isset($_POST["notes"]) ? sanitize_textarea_field($_POST["notes"]) : null;
        
        if ($lead_id === 0 || !$status) {
            wp_send_json_error("Invalid lead ID or status");
            return;
        }
        
        $body_data = array(
            "status" => $status
        );
        if ($notes !== null) {
            $body_data["notes"] = $notes;
        }
        
        $response = wp_remote_post($api_endpoint . "/admin/lead/" . $lead_id . "/update", array(
            "headers" => array("Content-Type" => "application/json"),
            "body" => json_encode($body_data),
            "timeout" => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error("Failed to update lead status: " . $response->get_error_message());
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data["success"]) && $data["success"]) {
            wp_send_json_success($data["data"]);
        } else {
            wp_send_json_error("Chatbot service error: " . (isset($data["error"]) ? $data["error"] : "Unknown error"));
        }
    }
    
    public function activate() {
        // Set default options
        add_option("est_chatbot_enabled", "1");
        add_option("est_chatbot_position", "bottom-right");
        add_option("est_chatbot_theme_color", "#e53e3e");
        add_option("est_chatbot_welcome_message", "Hello! I\\'m here to help you with est Financial services. I can provide information about our services or help you book an appointment. What would you like to do?");
        add_option("est_chatbot_appointment_confirmation_message", "Perfect! I\\'ve received your appointment request:\n\n**Name:** {name}\n**Email:** {email}\n**Phone:** {phone}\n**Service:** {service}\n\nYour appointment request has been sent to our team. Someone will contact you within 24 hours to confirm your appointment time. Is there anything else I can help you with?");
        add_option("est_chatbot_appointment_email_subject", "New Appointment Request from est Financial Chatbot");
        add_option("est_chatbot_appointment_email_body", "A new appointment request has been received via the est Financial Chatbot.\n\nDetails:\nName: {name}\nEmail: {email}\nPhone: {phone}\nService: {service}\nTimestamp: {timestamp}\n\nPlease contact the client to confirm the appointment.");
    }
    
    public function deactivate() {
        // Cleanup if needed
    }
}

// Initialize the plugin
new EstChatbot();

// Shortcode support
function est_chatbot_shortcode($atts) {
    $atts = shortcode_atts(array(
        'inline' => 'false'
    ), $atts);
    
    if (get_option('est_chatbot_enabled', '1') == '1') {
        if ($atts['inline'] == 'true') {
            return '<div id="est-chatbot-inline" class="est-chatbot-inline"></div>';
        } else {
            return '<div id="est-chatbot-container"></div>';
        }
    }
    
    return '';
}
add_shortcode('est_chatbot', 'est_chatbot_shortcode');

?>

