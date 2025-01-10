jQuery(document).ready(function ($) {
    $(document).on('click', '.create_project', function (e) {
        e.preventDefault();

        // Retrieve order ID from the data attribute
        const orderId = $(this).attr('aria-label').match(/\d+/)[0];

        if (!orderId) {
            alert('Order ID not found.');
            return;
        }

        // Populate hidden input with the retrieved order ID
        $('#addCredOrderListProjectModal').find('input[name="order_id"]').val(orderId);

        // Show the modal
        $('#addCredOrderListProjectModal').fadeIn();
        $('#addCredOrderListProjectModal').css('display', 'flex');
    });

    // Close modal on close button click
    $(document).on('click', '#addCredOrderListProjectModal .close-btn', function () {
        $('#addCredOrderListProjectModal').fadeOut();
        $('#addCredOrderListProjectModal').css('display', 'none');
    });

    $('#addCredOrderListProjectModal').on('click', function (e) {
        if ($(e.target).is($('#addCredOrderListProjectModal'))) {
            $('#addCredOrderListProjectModal').css('display', 'none');
        }
    });


    // Hide popup and submit form via AJAX
    $('#edit_cred_request_form').on('submit', function (event) {
        event.preventDefault();

        // Get the value of the footage link field
        const footageLink = $('#footage_link').val().trim();

        if (footageLink === '') {
            alert('Please enter a link to the footage.');
            return;
        }

        // Retrieve dynamic allowed domains and build validation pattern
        const allowedDomains = wooEditCreditVars.allowedDomains;

        // Check if the allowed domains are loaded properly
        if (!allowedDomains || !Array.isArray(allowedDomains)) {
            alert('Allowed domains list is not properly configured.');
            return;
        }

        // Build a regex pattern from allowed domains list
        const domainPattern = allowedDomains
            .map(domain => domain.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&')) // Escape special characters in domains
            .join('|');  // Join domains with the pipe `|` to create a regex alternation

        // Updated regex pattern to validate links with optional protocol, subdomains, paths, and query parameters
        const validLinkPattern = new RegExp(`^(https?:\\/\\/)?([a-z0-9.-]+\\.)?(${domainPattern})(\\/.*)?(\\?.*)?$`, 'i');

        // Join allowed domains into a readable string for feedback
        const allowedDomainsString = allowedDomains.join(', ');

        // Validate 'footage_link' against the dynamic pattern
        if (!validLinkPattern.test(footageLink)) {
            alert(`Please enter a valid link from one of the following: ${allowedDomainsString}.`);
            return;
        }

        // Disable submit button and show loader
        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true);
        const spinnerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-left: 8px;"></span>';
        submitButton.append(spinnerHTML);

        // If everything is valid, submit the form via AJAX
        let formData = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: wooEditCreditVars.ajaxurl,  // This should be set by wp_localize_script in your main file
            data: formData + '&action=submit_edit_request',
            success: function (response) {
                if (response.success) {
                    $('#addCredProjectModal').hide();
                    location.reload(); // Reload to update request history
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    const modalOverlay = $('#addCredProjectModal');
    const addCredProjModalBtn = $('#addCredProjModalBtn');
    const closeModalBtn = $('#addCredProjectModal .close-btn');

    // Open modal on button click
    addCredProjModalBtn.on('click', function () {
        modalOverlay.css('display', 'flex');
    });

    // Close modal on close button click
    closeModalBtn.on('click', function () {
        modalOverlay.css('display', 'none');
    });

    // Close modal when clicking outside modal content
    modalOverlay.on('click', function (e) {
        if ($(e.target).is(modalOverlay)) {
            modalOverlay.css('display', 'none');
        }
    });

    //custom order form
    $('#custom-cred-reg-order-form').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();
        $('#cred-reg-order-form-message').html('Processing...');
        //disable submit button
        $('#custom-cred-reg-order-form button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: wooEditCreditVars.ajaxurl,
            type: 'POST',
            data: formData + '&action=create_order_and_register_user',
            success: function (response) {
                console.log(response);

                //enable submit button
                $('#custom-cred-reg-order-form button[type="submit"]').prop('disabled', false);
                if (response.success) {
                    // $('#cred-reg-order-form-message').html('<p style="color:green;">' + response.data.message + '</p>');
                    $('#custom-cred-reg-order-form')[0].reset();
                    window.location.href = response.data.redirect_url;
                } else {
                    $('#cred-reg-order-form-message').html('<p style="color:red;">' + response.data.message + '</p>');
                }
            },
            error: function () {
                $('#cred-reg-order-form-message').html('<p style="color:red;">An error occurred. Please try again.</p>');
            }
        });
    });

});

