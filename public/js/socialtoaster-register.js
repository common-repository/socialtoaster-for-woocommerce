jQuery(document).ready(function($) {
    $('.st-direct-signup').click(function() {
        var data = {
            'action': 'socialtoaster_register',
        };

        $.post(ajax_object.ajax_url, data, function(response) {
            if (response.success) {
                $('.st-direct-signup').html('Success! Log into your account now.');
                $('.st-direct-signup').attr('href', response.campaign_url).attr('target', '_blank');
                $('.st-signup-message').html('');
                $('.st-signup-cta-text').html('');
                $('body').append(response.sso_pixel);
            } else {
                $('.st-signup-message').html(response.message);
                $('.st-direct-signup').html('Visit ' + response.campaign_name);
                $('.st-direct-signup').attr('href', response.campaign_url).attr('target', '_blank');
            }
        });
    });
});
