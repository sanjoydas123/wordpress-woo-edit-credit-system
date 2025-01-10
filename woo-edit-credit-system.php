<?php
/*
Plugin Name: Woo Edit Credit System
Description: A custom plugin to manages video editing credits and requests for video editing.
Version: 1.0
Author: Sanjoy Das
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Define Constants
define('WOO_EDIT_CREDIT_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Enqueue Scripts and Styles
function woo_edit_credit_enqueue_scripts()
{
    wp_enqueue_style('woo-edit-credit-style', plugins_url('assets/css/edit-credit-system.css', __FILE__));
    wp_enqueue_script('woo-edit-credit-script', plugins_url('assets/js/edit-credit-system.js', __FILE__), array('jquery'), null, true);

    // Get allowed domains from the settings
    $allowed_domains_option = get_option('allowed_domains_list', "wetransfer.com\ndrive.google.com\ndropbox.com\nmyairbridge.com");
    $allowed_domains = array_map('trim', explode(",", $allowed_domains_option));

    wp_localize_script('woo-edit-credit-script', 'wooEditCreditVars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'allowedDomains' => $allowed_domains
    ));
}
add_action('wp_enqueue_scripts', 'woo_edit_credit_enqueue_scripts');

function woo_admin_credit_enqueue_scripts()
{
    wp_enqueue_style('woo-admin-credit-style', plugins_url('assets/css/admin-credit-system.css', __FILE__));
    wp_enqueue_script('woo-admin-credit-script', plugins_url('assets/js/admin-credit-system.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'woo_admin_credit_enqueue_scripts');

// =====================
// Hook to add the settings menu
add_action('admin_menu', 'register_allowed_domains_menu');

function register_allowed_domains_menu()
{
    add_options_page(
        'Allowed Domains for Uploads',
        'Allowed Domains',
        'manage_options',
        'allowed-domains-settings',
        'allowed_domains_settings_page'
    );
}

// Display the settings page
function allowed_domains_settings_page()
{
?>
    <div class="wrap">
        <h1><?php _e('Allowed Domains for Footage Links', 'text-domain'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('allowed_domains_settings_group');
            do_settings_sections('allowed-domains-settings');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

add_action('admin_init', 'register_allowed_domains_setting');

function register_allowed_domains_setting()
{
    register_setting('allowed_domains_settings_group', 'allowed_domains_list');

    add_settings_section(
        'allowed_domains_section',
        __('Allowed Domains for Footage Links', 'text-domain'),
        null,
        'allowed-domains-settings'
    );

    add_settings_field(
        'allowed_domains_list',
        __('Enter allowed domains (comma-separated)', 'text-domain'),
        'allowed_domains_list_field',
        'allowed-domains-settings',
        'allowed_domains_section'
    );
}

function allowed_domains_list_field()
{
    $allowed_domains = get_option('allowed_domains_list', 'wetransfer.com,drive.google.com,dropbox.com,myairbridge.com');
    echo '<textarea id="allowed_domains_list" name="allowed_domains_list" rows="4" class="large-text">' . esc_textarea($allowed_domains) . '</textarea>';
    echo '<p class="description">' . __('Enter allowed domains, one per line.', 'text-domain') . '</p>';
}

// Add custom message if no orders are found
add_action('woocommerce_account_orders_endpoint', 'my_custom_no_orders_message');

function my_custom_no_orders_message()
{
    // Get the customer orders
    $customer_orders = wc_get_orders(array(
        'customer_id' => get_current_user_id(),
        'limit'       => -1,
    ));

    // Check if the orders list is empty
    if (empty($customer_orders)) {
        echo '<p class="my-order-custom-message">';
        echo 'It looks like you haven\'t placed any orders yet! Start your journey with us now by exploring our services. ';
        echo '<a href="' . site_url('#buy') . '" class="cta-link">Discover More</a>';
        echo '</p>';
    }
}


// =====================

// Include Required Classes
require_once WOO_EDIT_CREDIT_PLUGIN_PATH . 'includes/class-db-tables.php';
require_once WOO_EDIT_CREDIT_PLUGIN_PATH . 'includes/class-woo-edit-credits.php';
require_once WOO_EDIT_CREDIT_PLUGIN_PATH . 'includes/class-custom-cred-user-order.php';

// Instantiate Classes
if (class_exists('Woo_Edit_Credits') && class_exists('DB_Tables') && class_exists('Custom_Cred_User_Order')) {
    $db_tables = new DB_Tables();
    $Woo_Edit_Credits = new Woo_Edit_Credits();
    $custom_cred_user_order = new Custom_Cred_User_Order();
}

// Activation and Deactivation Hooks
register_activation_hook(__FILE__, array($db_tables, 'create_tables'));
register_deactivation_hook(__FILE__, array($db_tables, 'deactivate'));
