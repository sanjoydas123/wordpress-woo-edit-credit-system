<?php

require_once WOO_EDIT_CREDIT_PLUGIN_PATH . 'includes/common-function.php';

class Custom_Cred_User_Order
{
    public function __construct()
    {
        add_shortcode('custom-cred-order-form', array($this, 'render_custom_reg_order_form'));
        add_action('wp_ajax_nopriv_create_order_and_register_user', array($this, 'handle_custom_reg_order_submission'));
        add_action('wp_ajax_create_order_and_register_user', array($this, 'handle_custom_reg_order_submission'));
        add_action('woocommerce_account_dashboard', array($this, 'add_custom_text_to_my_account'));
    }

    function add_custom_text_to_my_account()
    {
        echo '<p style="font-weight: bold;">You are able to create a new project by entering the <a href="' . esc_url(wc_get_endpoint_url('orders')) . '">Orders</a> section.</p>';
    }


    // Render the registration form (loaded via shortcode)
    public function render_custom_reg_order_form()
    {
        ob_start();
        include WOO_EDIT_CREDIT_PLUGIN_PATH . 'templates/cust-order-cred-form.php';
        return ob_get_clean();
    }

    // Handle form submission via AJAX
    public function handle_custom_reg_order_submission()
    {
        global $wpdb;

        // Validate nonce
        if (!isset($_POST['custom_form_nonce']) || !wp_verify_nonce($_POST['custom_form_nonce'], 'custom_form_action')) {
            wp_send_json_error(['message' => __('Invalid form submission', 'text-domain')]);
        }

        // Validate required fields
        $required_fields = ['name', 'email', 'phone', 'password', 'footage_link'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(['message' => __('Please fill in all required fields', 'text-domain')]);
            }
        }

        // Extract sanitized input
        $name           = sanitize_text_field($_POST['name']);
        $email          = sanitize_email($_POST['email']);
        $phone          = sanitize_text_field($_POST['phone']);
        $password       = $_POST['password'];
        // $confirm_password = $_POST['confirm_password'];
        $footage_link = sanitize_textarea_field($_POST['footage_link']);

        // Validate email format
        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Invalid email address', 'text-domain')]);
        }

        // // Validate password confirmation
        // if ($password !== $confirm_password) {
        //     wp_send_json_error(['message' => __('Passwords do not match', 'text-domain')]);
        // }

        // Fetch allowed domains from the options table
        $allowed_domains_option = get_option('allowed_domains_list', 'wetransfer.com,drive.google.com,dropbox.com,myairbridge.com');
        $allowed_domains = array_map('trim', explode(',', $allowed_domains_option));

        // Validate 'footage_link' to allow only specific URLs
        $parsed_url = parse_url($footage_link);
        if (!isset($parsed_url['host'])) {
            wp_send_json_error(['message' => 'Invalid URL format. Please enter a valid link.']);
        }

        // Normalize and validate the host against allowed domains
        $givenHost = strtolower($parsed_url['host']);
        $is_valid = false;

        foreach ($allowed_domains as $domain) {
            $escaped_domain = preg_quote($domain, '/');
            if (preg_match("/(?:^|\.){$escaped_domain}$/", $givenHost)) {
                $is_valid = true;
                break;
            }
        }

        if (!$is_valid) {
            wp_send_json_error(['message' => 'Please enter a valid link from one of the allowed domains: ' . implode(', ', $allowed_domains) . '.']);
        }

        // Register the user
        $user_id = wp_insert_user([
            'user_login' => $email,
            'user_email' => $email,
            'first_name' => $name,
            'user_pass'  => $password,
            'role'       => 'none' // Optional, set to 'customer' if you want them to have a role
        ]);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
        }

        add_user_meta($user_id, 'is_free_trial_user', 1);

        // Retrieve product ID by category
        $products = wc_get_products([
            'category' => ['free-trial'], // Replace with your actual category slug
            'limit'    => 1,
        ]);

        if (empty($products)) {
            wp_send_json_error(['message' => __('No product found in the specified category', 'text-domain')]);
        }

        $product = $products[0];
        $product_id = $product->get_id();
        $edit_points = $product->get_attribute('edit-value');

        // Create WooCommerce order
        $order = wc_create_order(['customer_id' => $user_id]);
        $order->add_product($product, 1);
        $order->calculate_totals();

        update_post_meta($order->get_id(), 'is_free_trial_order', 1);
        // Insert the footage link and credits into the custom table
        $wpdb->insert(
            $wpdb->prefix . 'video_edit_credits',
            array(
                'user_id' => $user_id,
                'order_id' => $order->get_id(),
                'product_id' => $product_id,
                'credits' => $edit_points ? (int) $edit_points : 1,
                'remaining_credits' => $edit_points ? (int) $edit_points : 1,
            ),
            array('%d', '%d', '%d', '%d', '%d')
        );

        $user_info = get_userdata($user_id);
        $customer_name = $user_info->display_name;
        $project_name = $customer_name . ' ' . date('Y-m-d') . '(Free Trial)';

        // Insert request into custom table
        $wpdb->insert("{$wpdb->prefix}edit_requests", [
            'order_id' => $order->get_id(),
            'user_id' => $user_id,
            'project_name' => $project_name,
            'footage_link' => $footage_link,
            'status' => 'Pending'
        ]);

        // *******  mail send to trello ********//
        send_custom_email($user_id, $project_name, $footage_link, 'free_test');

        $redPage = get_page_by_title('Free Trial-Thankyou Page');
        if ($redPage) {
            $redirect_url = get_permalink($redPage->ID);
        } else {
            $redirect_url = home_url();
        }

        wp_send_json_success(['message' => __('Request sent successfully', 'text-domain'), 'redirect_url' => $redirect_url]);
    }
}
