jQuery(document).ready(function($) {
    $('form#wpforms-form-1479').on('submit', function() {
        var $submitButton = $(this).find('.wpforms-submit');
        $submitButton.prop('disabled', true).addClass('disabled');
        $submitButton.val('Submitting...');
    });

    $(document).on('wpformsAjaxSubmitCompleted', function(event, response, $form) {
        if ($form.attr('id') === 'wpforms-form-1479') {
            var $submitButton = $form.find('.wpforms-submit');
            $submitButton.prop('disabled', false).removeClass('disabled');
            $submitButton.val('Submit Referral');
        }
    });

    $(document).on('wpformsAjaxSubmitFailed', function(event, response, $form) {
        if ($form.attr('id') === 'wpforms-form-1479') {
            var $submitButton = $form.find('.wpforms-submit');
            $submitButton.prop('disabled', false).removeClass('disabled');
            $submitButton.val('Submit Referral');
        }
    });
});