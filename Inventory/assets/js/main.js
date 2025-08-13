// Add this to your JavaScript file
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const parent = this.parentElement;
            parent.classList.toggle('active');
            
            // Close other open dropdowns
            dropdownToggles.forEach(otherToggle => {
                if (otherToggle !== toggle) {
                    otherToggle.parentElement.classList.remove('active');
                }
            });
        });
    });
});




$(document).ready(function() {
    $('#receiveForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitBtn = $('#submitReceive');
        var $modal = $('#receiveModal');
        
        // Show loading state
        $submitBtn.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                console.log("AJAX Response:", response);
                
                if (response && response.success) {
                    toastr.success(response.message);
                    
                    // Build the full redirect URL
                    var currentPath = window.location.pathname;
                    var basePath = currentPath.substring(0, currentPath.lastIndexOf('/'));
                    var redirectUrl = basePath + '/' + response.redirect;
                    
                    console.log("Redirecting to:", redirectUrl);
                    
                    setTimeout(function() {
                        window.location.href = redirectUrl;
                    }, 1500);
                } else {
                    var errorMsg = response && response.message ? response.message : 'An unknown error occurred';
                    toastr.error(errorMsg);
                    $submitBtn.prop('disabled', false)
                        .html('<i class="fas fa-check-circle"></i> Confirm Receipt');
                }
            },
            error: function(xhr) {
                var errorMessage = 'An error occurred. Please try again.';
                try {
                    var jsonResponse = JSON.parse(xhr.responseText);
                    if (jsonResponse && jsonResponse.message) {
                        errorMessage = jsonResponse.message;
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
                toastr.error(errorMessage);
                $submitBtn.prop('disabled', false)
                    .html('<i class="fas fa-check-circle"></i> Confirm Receipt');
            },
            complete: function() {
                $modal.modal('hide');
            }
        });
    });
});