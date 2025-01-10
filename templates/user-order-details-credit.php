<?php if (!defined('ABSPATH')) exit; ?>

<div class="uCredOrdrDet">
    <h3>Credit history</h3>

    <?php if ($credit_data): ?>
        <table class="credDetTable">
            <tr>
                <td>
                    <strong>Total Credits:</strong>
                </td>
                <td>
                    <?php echo esc_html($credit_data->credits); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Remaining Credits:</strong>
                </td>
                <td>
                    <?php echo esc_html($credit_data->remaining_credits); ?>
                </td>
            </tr>
        </table>
    <?php else: ?>
        <p>No credits found for this order.</p>
    <?php endif; ?>

    <?php if ($credit_data && $credit_data->remaining_credits > 0): ?>

        <!-- Button to Open Modal -->
        <div class="d-flex justify-content-end mb-5">
            <button type="button" class="button" id="addCredProjModalBtn">
                Create New Project
            </button>
        </div>

        <!-- Modal Structure -->
        <div class="modal-overlay" id="addCredProjectModal" style="display: none;">
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
                        <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                        <button type="submit" class=" addcredprojBtn">Submit</button>
                    </form>
                </div>
            </div>
        </div>

    <?php else: ?>
        <p><?php _e('You have no remaining credits. Please purchase a package to request edits.', 'text-domain'); ?></p>
    <?php endif; ?>

    <h3>Project History</h3>
    <?php if ($editRequests): ?>
        <table class="credDetTable">
            <tr>
                <th>
                    <strong>Title</strong>
                </th>
                <th>
                    <strong>Link</strong>
                </th>
                <th>
                    <strong>Status</strong>
                </th>
                <th>
                    <strong>Date</strong>
                </th>
            </tr>
            <?php foreach ($editRequests as $editReq): ?>
                <tr>
                    <td style="text-align: center;">
                        <?php echo esc_html($editReq->project_name); ?>
                    </td>
                    <td style="text-align: center;">
                        <?php echo esc_html($editReq->footage_link); ?>
                    </td>
                    <td style="text-align: center;">
                        <?php echo esc_html(ucfirst($editReq->status)); ?>
                    </td>
                    <td style="text-align: center;">
                        <?php echo esc_html($editReq->created_at); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>


    <?php else: ?>
        <p>No project history found for this order.</p>
    <?php endif; ?>
</div>