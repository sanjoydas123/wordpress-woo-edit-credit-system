<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once WOO_EDIT_CREDIT_PLUGIN_PATH . 'includes/common-function.php';

class Woo_Edit_Credits
{

    public function __construct()
    {
        // WooCommerce and AJAX Hooks
        add_action('woocommerce_checkout_order_processed', array($this, 'add_credits_on_order_created'));
        add_action('woocommerce_order_details_after_order_table', array($this, 'display_credits_and_add_project_button'));
        add_action('wp_ajax_submit_edit_request', array($this, 'handle_edit_request_submission'));
        add_action('wp_ajax_nopriv_submit_edit_request', array($this, 'handle_edit_request_submission'));

        // My Orders and Admin Order Columns
        add_filter('woocommerce_account_orders_columns', array($this, 'add_credit_columns_my_orders'));
        add_action('woocommerce_my_account_my_orders_column_total_credits', array($this, 'display_total_credits_column_my_orders'));
        add_action('woocommerce_my_account_my_orders_column_credits_left', array($this, 'display_credits_left_column_my_orders'));

        // Display edit credits in admin order details
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_edit_credits_in_admin_order'));
        add_action('wp_ajax_update_edit_request_status', array($this, 'update_edit_request_status'));

        add_filter('manage_edit-shop_order_columns', array($this, 'add_credit_columns_admin_orders'), 20);
        add_action('manage_shop_order_posts_custom_column', array($this, 'display_credit_columns_admin_orders'), 10, 2);

        // Add a custom column to the Users list for "Free Trial Status"
        add_filter('manage_users_columns', array($this, 'add_credit_columns_admin_users'));
        add_action('manage_users_custom_column', array($this, 'display_credit_columns_admin_users'), 10, 3);

        // Add a new column to the My Account orders table
        add_filter('woocommerce_my_account_my_orders_actions', array($this, 'add_create_project_action_button'), 10, 2);
        add_action('wp_footer', array($this, 'inject_create_project_modal'));
    }

    public function add_credits_on_order_created($order_id)
    {
        global $wpdb;

        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);

            // Fetch the attribute value for 'edit-value'
            $edit_points = $product->get_attribute('edit-value');

            // Ensure we have a numeric value
            if (is_numeric($edit_points) && $edit_points > 0) {
                $wpdb->insert(
                    $wpdb->prefix . 'video_edit_credits',
                    array(
                        'user_id' => $user_id,
                        'order_id' => $order_id,
                        'product_id' => $product_id,
                        'credits' => (int) $edit_points,
                        'remaining_credits' => (int) $edit_points,
                    ),
                    array('%d', '%d', '%d', '%d', '%d')
                );
            }
        }
    }

    // Add a new column to the My Account orders table
    function add_create_project_action_button($actions, $order)
    {
        $order_id = $order->get_id();
        $user_id = get_current_user_id();

        // Check if the user has remaining credits for this order
        global $wpdb;
        $credit_data = $wpdb->get_row($wpdb->prepare(
            "SELECT remaining_credits FROM {$wpdb->prefix}video_edit_credits WHERE order_id = %d AND user_id = %d",
            $order_id,
            $user_id
        ));

        if ($order->get_status() === 'processing') {
            if ($credit_data && $credit_data->remaining_credits > 0) {
                // Add a new action for "Create New Project"
                $actions['create_project'] = [
                    'url'    => 'javascript:void(0)',  // Use javascript void to prevent page redirection
                    'name'   => __('Create New Project', 'text-domain'),
                    'action' => 'create-project', // This can be used for custom styling or JavaScript handling
                    'order-id' => $order_id  // Optionally pass the order ID as a custom data attribute
                ];
            }
        }

        return $actions;
    }

    function inject_create_project_modal()
    {
        if (is_account_page() && is_wc_endpoint_url('orders')) {
            if (file_exists(WOO_EDIT_CREDIT_PLUGIN_PATH . 'templates/user-order-list-credit-modal.php')) {
                include WOO_EDIT_CREDIT_PLUGIN_PATH . 'templates/user-order-list-credit-modal.php';
            } else {
                echo '<p>Modal template not found.</p>'; // Debug message
            }
        }
    }

    public function display_credits_and_add_project_button($order)
    {
        global $wpdb;
        $order_id = $order->get_id();
        $user_id = get_current_user_id();

        // Retrieve user's edit credits for this order
        $credit_data = $wpdb->get_row($wpdb->prepare(
            "SELECT credits, remaining_credits FROM {$wpdb->prefix}video_edit_credits WHERE order_id = %d AND user_id = %d",
            $order_id,
            $user_id
        ));

        // Pass data to template
        $editRequests = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}edit_requests WHERE order_id = %d AND user_id = %d ORDER BY created_at DESC",
            $order_id,
            $user_id
        ));

        if ($order->get_status() === 'processing') {
            // Include template to render HTML
            include WOO_EDIT_CREDIT_PLUGIN_PATH . 'templates/user-order-details-credit.php';
        }
    }

    function handle_edit_request_submission()
    {
        global $wpdb;

        // Retrieve and sanitize input data
        $order_id = intval($_POST['order_id']);
        $user_id = get_current_user_id();
        $project_name = sanitize_text_field($_POST['project_name']);
        $footage_link = sanitize_textarea_field($_POST['footage_link']);

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

        // Set default project name if it is blank
        if (empty($project_name)) {
            $user_info = get_userdata($user_id);
            $customer_name = $user_info->display_name;
            $project_name = $customer_name . ' ' . date('Y-m-d');
        }

        // Deduct one point from remaining credits
        $credit_data = $wpdb->get_row($wpdb->prepare("SELECT remaining_credits FROM {$wpdb->prefix}video_edit_credits WHERE order_id = %d", $order_id));

        if ($credit_data->remaining_credits > 0) {
            $wpdb->update("{$wpdb->prefix}video_edit_credits", ['remaining_credits' => $credit_data->remaining_credits - 1], ['order_id' => $order_id]);

            // Insert request into custom table
            $wpdb->insert("{$wpdb->prefix}edit_requests", [
                'order_id' => $order_id,
                'user_id' => $user_id,
                'project_name' => $project_name,
                'footage_link' => $footage_link,
                'status' => 'Pending'
            ]);

            // *******  mail send to trello ********//
            send_custom_email($user_id, $project_name, $footage_link, 'Paid Project');

            wp_send_json_success(['message' => 'Edit request submitted successfully.']);
        } else {
            wp_send_json_error(['message' => 'No remaining credits for this order.']);
        }
        wp_die();
    }


    // Custom Columns in My Orders
    // public function add_credit_columns_my_orders($columns)
    // {
    //     $columns['total_credits'] = __('Total Credits', 'woo-edit-credit');
    //     $columns['credits_left'] = __('Credits Left', 'woo-edit-credit');
    //     return $columns;
    // }
    public function add_credit_columns_my_orders($columns)
    {
        // Insert the new columns before the Actions column
        $new_columns = array();
        foreach ($columns as $key => $column) {
            if ($key === 'order-actions') {  // Add before 'Actions' column
                $new_columns['total_credits'] = __('Total Credits', 'text-domain');
                $new_columns['credits_left'] = __('Credits Left', 'text-domain');
            }
            $new_columns[$key] = $column;
        }
        return $new_columns;
    }

    public function display_total_credits_column_my_orders($order)
    {
        global $wpdb;
        $order_id = $order->get_id();
        $credits_data = $wpdb->get_row($wpdb->prepare("SELECT credits FROM {$wpdb->prefix}video_edit_credits WHERE order_id = %d", $order_id));

        echo $credits_data ? esc_html($credits_data->credits) : __('N/A', 'text-domain');
    }

    public function display_credits_left_column_my_orders($order)
    {
        global $wpdb;
        $order_id = $order->get_id();
        $credits_data = $wpdb->get_row($wpdb->prepare("SELECT remaining_credits FROM {$wpdb->prefix}video_edit_credits WHERE order_id = %d", $order_id));

        echo $credits_data ? esc_html($credits_data->remaining_credits) : __('N/A', 'text-domain');
    }

    // Display edit credits in admin order details
    public function display_edit_credits_in_admin_order($order)
    {
        global $wpdb;
        $order_id = $order->get_id();

        $credits_data = $wpdb->get_row($wpdb->prepare(
            "SELECT credits, remaining_credits FROM {$wpdb->prefix}video_edit_credits WHERE order_id = %d",
            $order_id
        ));

        $requestHistory = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}edit_requests WHERE order_id = %d",
            $order_id
        ));

        // Load the template, passing $credits_data and $requestHistory
        include WOO_EDIT_CREDIT_PLUGIN_PATH . 'templates/admin-order-details-credit.php';
    }

    // AJAX function to update the edit request status
    function update_edit_request_status()
    {
        global $wpdb;

        // Check for required fields, permissions, and nonce
        if (
            !isset($_POST['id'], $_POST['new_status'], $_POST['update_request_nonce']) ||
            !wp_verify_nonce($_POST['update_request_nonce'], 'update_request_status')
        ) {
            wp_send_json_error(['message' => __('Invalid request', 'text-domain')]);
            return;
        }

        $request_id = intval($_POST['id']);
        $new_status = sanitize_text_field($_POST['new_status']);

        // Update the request status in the database
        $updated = $wpdb->update(
            "{$wpdb->prefix}edit_requests",
            ['status' => $new_status],
            ['id' => $request_id],
            ['%s'],
            ['%d']
        );

        if ($updated !== false) {
            wp_send_json_success(['message' => __('Status updated successfully', 'text-domain')]);
        } else {
            // Log the error message for debugging purposes
            error_log('Database error: ' . $wpdb->last_error);
            wp_send_json_error(['message' => __('Failed to update status', 'text-domain')]);
        }
    }


    // Add custom columns in the Admin Order List table
    public function add_credit_columns_admin_orders($columns)
    {
        $columns['free_trial_status'] = __('Free Trial Status', 'text-domain');
        return $columns;
    }

    // Step 2: Display data in the Total Credits and Credits Left columns
    public function display_credit_columns_admin_orders($column, $post_id)
    {
        // global $wpdb;

        // // Check if the current column is one of our custom columns
        // if (
        //     $column === 'total_credits' || $column === 'credits_left'
        // ) {
        //     // Get credit data from the custom table based on order ID
        //     $credits_data = $wpdb->get_row($wpdb->prepare("SELECT credits, remaining_credits FROM {$wpdb->prefix}video_edit_credits WHERE order_id = %d", $post_id));

        //     // Output the data in the correct column
        //     if ($credits_data) {
        //         if ($column === 'total_credits') {
        //             echo esc_html($credits_data->credits);
        //         } elseif ($column === 'credits_left') {
        //             echo esc_html($credits_data->remaining_credits);
        //         }
        //     } else {
        //         echo __('N/A', 'text-domain');
        //     }
        // }

        if ('free_trial_status' === $column) {
            $is_free_trial_order = get_post_meta(
                $post_id,
                'is_free_trial_order',
                true
            );
            echo $is_free_trial_order ? __('Free Trial Order', 'text-domain') : __('Regular Order', 'text-domain');
        }
    }

    // Add a custom column to the Users list for "Free Trial Status"
    public function add_credit_columns_admin_users($columns)
    {
        $columns['free_trial_status'] = __('Free Trial Status', 'text-domain');
        return $columns;
    }

    public function display_credit_columns_admin_users($value, $column_name, $user_id)
    {
        if ('free_trial_status' === $column_name) {
            $is_free_trial_user = get_user_meta($user_id, 'is_free_trial_user', true);
            return $is_free_trial_user ? __('Free Trial User', 'text-domain') : __('Regular User', 'text-domain');
        }
        return $value;
    }

    // Add custom column to WooCommerce Orders list


}
