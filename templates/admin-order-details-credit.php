<div class="admin_order_credit">
    <div class="order-credits">
        <h3>Video Editing Credits</h3>
        <p><strong>Total Credits:</strong> <?php echo esc_html($credits_data->credits); ?></p>
        <p><strong>Remaining Credits:</strong> <?php echo esc_html($credits_data->remaining_credits); ?></p>
    </div>

    <?php if ($requestHistory): ?>
        <h3>Edit Requests History</h3>
        <ul>
            <?php foreach ($requestHistory as $reqHis): ?>
                <li>
                    <strong>Title:</strong> <?php echo esc_html($reqHis->project_name); ?><br>
                    <strong>Link:</strong> <?php echo esc_html($reqHis->footage_link); ?><br>
                    <strong>Status:</strong> <?php echo esc_html(ucfirst($reqHis->status)); ?><br>
                    <strong>Date:</strong> <?php echo esc_html($reqHis->created_at); ?>
                </li>
                <form method="post" action="" class="edit_cred_status_form">
                    <?php wp_nonce_field('update_request_status', 'update_request_nonce'); ?>
                    <select name="new_status">
                        <option value="Pending" <?php selected($reqHis->status, 'Pending'); ?>>Pending</option>
                        <option value="Completed" <?php selected($reqHis->status, 'Completed'); ?>>Completed</option>
                    </select>
                    <input type="hidden" name="id" value="<?php echo esc_attr($reqHis->id); ?>">
                    <button type="button" class="button update_request_status">Update Status</button>
                </form>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</div>