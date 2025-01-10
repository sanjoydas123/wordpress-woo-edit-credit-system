<div class="modal-overlay" id="addCredOrderListProjectModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h5 class="modal-title">New Project</h5>
            <button type="button" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit_cred_request_form">
                <div class="form-group">
                    <label for="project_name">Project Name:</label>
                    <input type="text" class="form-control" id="project_name" name="project_name">
                </div>
                <div class="form-group mt-2">
                    <label for="footage_link">Footage Link:</label>
                    <textarea class="form-control" id="footage_link" name="footage_link" required></textarea>
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
                </div>
                <input type="hidden" name="order_id" value="">
                <button type="submit" class=" addcredprojBtn">Submit</button>
            </form>
        </div>
    </div>
</div>