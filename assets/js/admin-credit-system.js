jQuery(document).ready(function ($) {

    $('.update_request_status').on('click', function (e) {
        if (!confirm('Are you sure you want to update the status?')) {
            return;
        }

        const requestId = $(this).siblings('input[name="id"]').val();
        const newStatus = $(this).siblings('select[name="new_status"]').val();

        $.ajax({
            url: ajaxurl, // WordPress provides the 'ajaxurl' variable for AJAX in admin
            method: 'POST',
            data: {
                action: 'update_edit_request_status',
                id: requestId,
                new_status: newStatus,
                update_request_nonce: $(this).siblings('input[name="update_request_nonce"]').val()
            },
            success: function (response) {
                // alert('Status updated successfully');
                location.reload();
            },
            error: function () {
                alert('There was an error updating the status');
            }
        });
    });
});
