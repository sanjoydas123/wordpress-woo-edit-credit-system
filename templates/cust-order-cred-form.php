<?php if (!defined('ABSPATH')) exit; ?>

<div id="cred-reg-order-form">
    <form id="custom-cred-reg-order-form">
        <?php wp_nonce_field('custom_form_action', 'custom_form_nonce'); ?>
        <label>Username <span style="color:red;">*</span> :</label>
        <input type="text" name="name" required>

        <label>Email <span style="color:red;">*</span> :</label>
        <input type="email" name="email" required>

        <label>Phone <span style="color:red;">*</span> :</label>
        <input type="text" name="phone" required>

        <label>Password <span style="color:red;">*</span> :</label>
        <input type="password" name="password" required>

        <!-- <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required> -->

        <label>Footage Link <span style="color:red;">*</span> :</label>
        <textarea name="footage_link" required></textarea>
        <!-- Display allowed domains dynamically below the field -->
        <div id="allowed-domains" class="mt-1 mb-2" style="font-size: 0.9em; color: gray;">
            <?php
            // Fetch allowed domains and ensure it's an array
            $allowed_domains = get_option('allowed_domains_list');

            // If it’s a string, split it by commas and trim whitespace
            if (is_string($allowed_domains)) {
                $allowed_domains = array_map('trim', explode(',', $allowed_domains));
            }

            // Fallback to default domains if the array is empty or invalid
            if (!is_array($allowed_domains) || empty($allowed_domains)) {
                $allowed_domains = ['wetransfer.com', 'drive.google.com', 'dropbox.com', 'myairbridge.com'];
            }

            // Display the allowed domains
            echo '<b>*Allowed domains: ' . implode(', ', array_map('esc_html', $allowed_domains)) . '</b>';
            ?>
        </div>

        <button type="submit">Submit</button>
    </form>
    <div id="cred-reg-order-form-message"></div>
</div>