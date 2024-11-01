<?php
/**
 * Plugin Name: SocialToaster for WooCommerce
 * Description: Integrate single sign-on and points with your SocialToaster campaign
 * Version: 1.0
 * Author: SocialToaster
 * Author URI: https://www.socialtoaster.com
 */

define('STWOO_WP_SSO','stwoo_wp_sso'); // sso user ID cookie
define('STWOO_REGISTERED','stwoo_registered'); // set this so that we do not show the signup block after a successful reg.

// Configure settings for plugin
add_action( 'admin_menu', 'stwoo_admin_menu' );
add_action( 'admin_init', 'stwoo_settings_init' );

// Client script to SocialToaster API
add_action( 'wp_enqueue_scripts', 'stwoo_add_scripts' );
add_action( 'wp_ajax_socialtoaster_register', 'socialtoaster_register' );
add_action( 'wp_ajax_nopriv_socialtoaster_register', 'socialtoaster_register' );

//
// SocialToaster signup calls to action
//
add_action( 'woocommerce_thankyou',   
    function() { stwoo_wrap_signup('stwoo_enable_signup_order_received_field');} );

add_action( 'woocommerce_account_content', 
    function() { stwoo_wrap_signup('stwoo_enable_signup_my_account_field');} );

add_shortcode( 'socialtoaster_signup', 
    function($atts) { stwoo_wrap_signup('stwoo_enable_signup_shortcode_field');} );

// Sign sign-on pixel
add_action( 'init', 'stwoo_sso_pixel' );

add_action( 'wp_login', 'stwoo_check_login', 10, 2 );
add_action( 'wp_logout', 'stwoo_clear_cookie' );

// REST API endpoint for handling points added through WooCommerce Points and Rewards plugin
add_action( 'rest_api_init', function () {
    register_rest_route( 'socialtoaster/v1', '/points', array(
        'methods' => 'POST',
        'callback' => 'stwoo_add_points'
    ) );
} );

add_filter( 'wc_points_rewards_event_description', 'stwoo_points_description', 10, 3 );

register_deactivation_hook( __FILE__, 'stwoo_settings_clear' );

function stwoo_settings_clear() {
    delete_option( 'stwoo_settings' );
}

function stwoo_settings_init() {
    register_setting('socialtoaster', 'stwoo_settings');

    add_settings_section(
        'socialtoaster_main_section',
        __( 'Main Config', 'st'),
        'stwoo_main_section_callback',
        'socialtoaster'
    );

    add_settings_field(
        'stwoo_campaign_key_field',
        __( 'SocialToaster Campaign Key', 'st' ),
        'stwoo_campaign_key_field_render',
        'socialtoaster',
        'socialtoaster_main_section'
    );

    add_settings_field(
        'stwoo_api_key_field',
        __( 'SocialToaster API Key', 'st' ),
        'stwoo_api_key_field_render',
        'socialtoaster',
        'socialtoaster_main_section'
    );

    add_settings_field(
        'stwoo_api_url_field',
        __( 'SocialToaster API URL', 'st' ),
        'stwoo_api_url_field_render',
        'socialtoaster',
        'socialtoaster_main_section'
    );

    add_settings_field(
        'stwoo_campaign_url_field',
        __( 'SocialToaster Campaign URL', 'st' ),
        'stwoo_campaign_url_field_render',
        'socialtoaster',
        'socialtoaster_main_section'
    );

    add_settings_section(
        'socialtoaster_options_section',
        __( 'Plugin Options', 'st'),
        'stwoo_options_section_callback',
        'socialtoaster'
    );

    add_settings_field(
        'stwoo_enable_sso_field',
        __( 'Enable Single Sign-On', 'st' ),
        'stwoo_enable_sso_field_render',
        'socialtoaster',
        'socialtoaster_options_section'
    );

    add_settings_field(
        'stwoo_allow_direct_signup_field',
        __( 'Allow Direct Signup', 'st' ),
        'stwoo_allow_direct_signup_field_render',
        'socialtoaster',
        'socialtoaster_options_section'
    );

    add_settings_field(
        'stwoo_enable_signup_my_account_field',
        __( 'Add Signup to My Account Page', 'st' ),
        'stwoo_enable_signup_my_account_field_render',
        'socialtoaster',
        'socialtoaster_options_section'
    );

    add_settings_field(
        'stwoo_enable_signup_order_received_field',
        __( 'Add Signup to Order Received Page', 'st' ),
        'stwoo_enable_signup_order_received_field_render',
        'socialtoaster',
        'socialtoaster_options_section'
    );

    add_settings_field(
        'stwoo_enable_signup_shortcode_field',
        __( 'Allow WP Shortcode for Signup Block Placement', 'st' ),
        'stwoo_enable_signup_shortcode_field_render',
        'socialtoaster',
        'socialtoaster_options_section'
    );
    
    add_settings_field(
        'stwoo_campaign_name_field',
        __( 'SocialToaster Campaign Name', 'st' ),
        'stwoo_campaign_name_field_render',
        'socialtoaster',
        'socialtoaster_main_section'
    );

    add_settings_field(
        'stwoo_signup_cta_text_field',
        __( 'Call-to-action text for Signup block', 'st' ),
        'stwoo_signup_cta_text_field_render',
        'socialtoaster',
        'socialtoaster_main_section'
    );
}


function stwoo_main_section_callback() {
    echo __( 'Connect the plugin to your SocialToaster campaign. For help, contact <a href="mailto:support@socialtoaster.com">support@socialtoaster.com</a>.' );
}

function stwoo_options_section_callback() {
    echo __( 'Configure functionality of the plugin.' );
}

function stwoo_campaign_key_field_render() {
    stwoo_render_text_option( 'stwoo_campaign_key_field' );
}

function stwoo_api_key_field_render() {
    stwoo_render_text_option( 'stwoo_api_key_field' );
}

function stwoo_api_url_field_render() {
    stwoo_render_text_option( 'stwoo_api_url_field' );
}

function stwoo_campaign_url_field_render() {
    stwoo_render_text_option( 'stwoo_campaign_url_field' );
}

function stwoo_enable_sso_field_render() {
    stwoo_render_checkbox_option( 'stwoo_enable_sso_field' );
}

function stwoo_allow_direct_signup_field_render() {
    stwoo_render_checkbox_option( 'stwoo_allow_direct_signup_field' );
}

function stwoo_enable_signup_my_account_field_render() {
    stwoo_render_checkbox_option( 'stwoo_enable_signup_my_account_field' );
}

function stwoo_enable_signup_order_received_field_render() {
    stwoo_render_checkbox_option( 'stwoo_enable_signup_order_received_field' );
}

function stwoo_enable_signup_shortcode_field_render() {
    stwoo_render_checkbox_option( 'stwoo_enable_signup_shortcode_field' );
}

function stwoo_campaign_name_field_render() {
    stwoo_render_text_option( 'stwoo_campaign_name_field' );
}

function stwoo_signup_cta_text_field_render() {
    stwoo_render_text_option( 'stwoo_signup_cta_text_field' );
}

function stwoo_render_text_option( $field_name ) {
    $options = get_option( 'stwoo_settings' );
    $option_val = $options[$field_name];
    ?>
    <input type='text' name='stwoo_settings[<?php echo $field_name; ?>]' value='<?php echo $option_val; ?>'>
    <?php
}

function stwoo_render_checkbox_option( $field_name ) {
    $options = get_option( 'stwoo_settings' );
    $option_val = $options[$field_name];
    ?>
    <input type='checkbox' name='stwoo_settings[<?php echo $field_name; ?>]' <?php checked( $option_val, 1 ); ?> value='1'>
    <?php
}

function stwoo_render_textbox_option( $field_name ) {
    $options = get_option( 'stwoo_settings' );
    $option_val = $options[$field_name];
    ?>
        <textarea name='stwoo_settings[<?php echo $field_name; ?>]' value='<?php echo $option_val; ?>' rows="5" cols="50">
    <?php
}

function stwoo_admin_menu() {
    add_options_page( 'SocialToaster Configuration', 'SocialToaster', 'manage_options', 'socialtoaster', 'stwoo_config_options' );
}

function stwoo_config_options() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    ?>
    <div class="wrap">
    <h1>SocialToaster</h1>
    <form method="post" action="options.php">
        <?php
            settings_fields( 'socialtoaster' );
            do_settings_sections( 'socialtoaster' );
            submit_button();
        ?>
    </form>
    </div>
    <?php
}

/** MSV - decouple the display logic of the signin form from the rendering of the signin form */
function stwoo_wrap_signup( $option_key ) {
    // no display if the option is disabled in the plugin admin page
    $options = get_option( 'stwoo_settings' );
    
    if ( ! stwoo_minimum_required_settings( $options ) ) {
        return;
    }
    
    if ( $options[$option_key] ) { 
        stwoo_display_signup();  
     }
}

function stwoo_display_signup() {
    $options = get_option( 'stwoo_settings' );
    $direct_signup_enabled = $options['stwoo_allow_direct_signup_field'];
    $current_user = wp_get_current_user();
    $first_name = trim( $current_user->user_firstname );
    $last_name = trim( $current_user->user_lastname );
    $email = trim( $current_user->user_email );
    $show_direct_signup = $direct_signup_enabled && is_user_logged_in();
    
    if ( empty($first_name) || empty($last_name) || empty($email) ) {
        $show_direct_signup = false;
    }

    // Display visit option if we determine that the user is already logged in via SSO
    if ( isset($_COOKIE[STWOO_WP_SSO]) && (isset($current_user) && ($current_user->ID==$_COOKIE[STWOO_WP_SSO])) ) {
        $show_direct_signup = false;
    }
    
    // Display visit option if registered successfully
    if ( isset($_COOKIE[STWOO_REGISTERED]) && (isset($current_user) && ($current_user->ID==$_COOKIE[STWOO_REGISTERED])) ) {
        $show_direct_signup = false;
    }
    
    if ( $show_direct_signup ) {
        $signup_url = 'javascript:void(0)';
        $signup_class = 'st-direct-signup';
        $signup_target= "";
        $signup_action = 'Signup for';
    } else {
        $signup_url = trim( $options['stwoo_campaign_url_field'] );
        $signup_class = '';
        $signup_target= "target='_blank'";
        $signup_action = 'Visit';
    }
    $campaign_name = trim( $options['stwoo_campaign_name_field'] );
    $signup_cta_text = $options['stwoo_signup_cta_text_field'];
    
    if ( isset( $campaign_name) && empty( $campaign_name ) ) {
        $campaign_name = 'SocialToaster';
    }
    
    echo "<div class='st-signup-wrapper'>" . 
         "<div class='st-signup-cta-text'>{$signup_cta_text}</div>" . 
         "<div class='st-signup-message'></div>" . 
         "<a href='{$signup_url}' class='button {$signup_class}' {$signup_target}>${signup_action} {$campaign_name}</a>" . 
         "</div>";
}

function socialtoaster_register() {
    $options = get_option( 'stwoo_settings' );       
    
    if ( ! stwoo_minimum_required_settings( $options ) ) {
        $endpoint_response = array( 'success' => false, 'message' => 'Missing plugin required configuration' );
        wp_send_json( $endpoint_response );
    }
    
    $client_key = trim( $options['stwoo_campaign_key_field'] );
    $api_key = trim( $options['stwoo_api_key_field'] );
    $api_url = trim( $options['stwoo_api_url_field'] );
    $campaign_name = trim( $options['stwoo_campaign_name_field'] );
    $campaign_url = trim( $options['stwoo_campaign_url_field'] );
    $api_endpoint = $api_url . $client_key . '/superfan/';
    $is_logged_in = is_user_logged_in();
    
    if ( isset( $campaign_name ) && empty( $campaign_name ) ) {
        $campaign_name = 'SocialToaster';
    }

    $endpoint_response = array( 'success' => false, 'campaign_name' => $campaign_name, 'campaign_url' => $campaign_url );

    if ( ! $is_logged_in ) {
        wp_send_json( $endpoint_response );
    }

    if ( ! isset($client_key) || ! isset($api_key) || ! isset($api_url) ) {
        $endpoint_response['message'] = 'Missing param(s)';
        wp_send_json( $endpoint_response );
    }          

    $current_user = wp_get_current_user();
    $first_name = $current_user->user_firstname;
    $last_name = $current_user->user_lastname;
    $email = $current_user->user_email;
    $user_id = $current_user->ID;

    if ( ! isset($first_name) || ! isset($last_name) || ! isset($email) ) {
        $endpoint_response['message'] = 'Missing required user info';
        wp_send_json( $endpoint_response );
    }

    $data = array(
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'external_id' => $user_id,
        'api_key'=> $api_key,
    );
    $args = array(
        'body' => $data,
        'timeout' => '20',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'cookies' => array()
    );

    $response = wp_remote_post( $api_endpoint, $args );
    $http_code = wp_remote_retrieve_response_code( $response );

    if ( 200 == $http_code ) {
        $api_data = json_decode( wp_remote_retrieve_body( $response ) );

        if ( $api_data->success ) {
            $endpoint_response['success'] = true;
            try {                    
                if ( ! setcookie(STWOO_REGISTERED, $user_id, 0) ) {
                   throw new Exception("Cannot set Registration SocialToaster Cookie - Please contact site administrator");
                }
                $_COOKIE[STWOO_REGISTERED] = $user_id;

                $sso_url = 
                    parse_url($options['stwoo_api_url_field'],PHP_URL_SCHEME) . '://' .
                    parse_url($options['stwoo_api_url_field'],PHP_URL_HOST) . 
                    '/qlog/' . $api_data->nonce . '/';
                $sso_pixel_markup = "<img src='$sso_url' id='socialtoaster-connector-px' style='display: none'>";
                $endpoint_response['sso_pixel'] = $sso_pixel_markup;
            }
            catch( Exception $e ) {
                $endpoint_response['message'] = $e->getMessage(); 
            }
        } else {
            switch( $api_data->reason_code) {
                case 'missing_fields':
                    $msg = "Sorry, we can't complete your registration because your account is missing some information";
                    break;
                case 'exists':
                    $msg = "It looks like you already have an account";
                    break;
                case 'too_many':
                    $msg = "Sorry, sign up is not available for you at this time";
                    break;
                case 'blacklisted':
                    $msg = "Sorry, sign up is not available for you at this time";
                    break;
                default:
                    $msg = "{$api_data->reason_code} / {$api_data->reason}"; 
            }
            $endpoint_response['message'] = $msg;
        }
    } else {
        $endpoint_response['message'] = "Bad API response code: $http_code";
    }

    wp_send_json( $endpoint_response );
}

function stwoo_add_scripts() {
   wp_enqueue_script( 'socialtoaster-register', plugins_url( 'public/js/socialtoaster-register.js', __FILE__ ), array( 'jquery' ) );
   wp_localize_script( 'socialtoaster-register', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
   
   wp_enqueue_style( 'style1', plugins_url( 'public/css/style.css', __FILE__ ) );
}

function stwoo_sso_pixel() {

    $options = get_option( 'stwoo_settings' );
    
    if ( ! stwoo_minimum_required_settings( $options ) ) {
        return;
    }
    
    // If the 'Enable SSO' admin page checkbox is unchecked, then don't render the pixel
    if ( ! $options['stwoo_enable_sso_field'] ) {
        return;
    }
    
    $client_key = trim( $options['stwoo_campaign_key_field'] );
    $api_key = trim( $options['stwoo_api_key_field'] );
    $api_url = trim( $options['stwoo_api_url_field'] );
    $api_endpoint = $api_url . '/' . $client_key . '/loginnonce/';

    $sso_cookie = isset( $_COOKIE[STWOO_WP_SSO] ) ? $_COOKIE[STWOO_WP_SSO] : null;
    
    $is_logged_in = is_user_logged_in();
    $skip_paths = Array( '/wp-json', '/wp-admin', '/wp-cron' );
    $request_uri = $_SERVER['REQUEST_URI'];
    
    // Don't set pixel on URLs off the shop
    foreach ( $skip_paths as $path ) {
        if ( strpos( $request_uri, $path ) === 0 ) {
            return;
        }
    }
    
    if ($is_logged_in) {
        $current_user = wp_get_current_user();
        $email = $current_user->user_email;
        $user_id = $current_user->ID;

        // If the user already logged in recently, don't check again
        if ( isset( $sso_cookie ) && $sso_cookie == $user_id ) {
          return;
        }
    
        if ( ! isset($email) ) {
            return;
        }

        $data = array(
            'email' => $email,
            'api_key' => $api_key,
            'external_id' => $user_id
        );
        $args = array(
            'body' => $data,
            'timeout' => '20',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'cookies' => array()
        );

        $response = wp_remote_post( $api_endpoint, $args );
        $http_code = wp_remote_retrieve_response_code( $response );

        if ( 200 == $http_code ) {
            $api_data = json_decode( wp_remote_retrieve_body( $response ) );

            if ( $api_data->success ) {
                $nonce = $api_data->nonce;
                $sso_url = 
                    parse_url($options['stwoo_api_url_field'],PHP_URL_SCHEME) . '://' .
                    parse_url($options['stwoo_api_url_field'],PHP_URL_HOST) . 
                    '/qlog/' . $nonce . '/';

                // Set a session cookie to marked them logged in
                try {
                   if ( ! setcookie( STWOO_WP_SSO, $user_id, 0, '/' ) ) {
                       throw new Exception("Cannot set SocialToaster Cookie - Please contact site administrator");
                   }
                   $_COOKIE[STWOO_WP_SSO] = $user_id;
                   echo "<img src='$sso_url' id='socialtoaster-connector-px' style='display: none'>";
                }
                catch( Exception $e ) {
                    echo "<span id='socialtoaster-connector-px'>{$e->getMessage()}</span>";
                }
            }
        }
    }
}

function stwoo_add_points( WP_REST_Request $request ) {
    $options = get_option( 'stwoo_settings' );
    
    if ( ! stwoo_minimum_required_settings( $options ) ) {
        return new WP_Error( 'cant-add', __( 'Plugin configuration incomplete', 'st' ) );
    }
    
    $saved_api_key = trim( $options['stwoo_api_key_field'] );

    $post_params = $request->get_params();

    // Check for required API params
    if ( !isset( $post_params['id'] ) || !isset( $post_params['points'] ) || !isset( $post_params['description'] ) || !isset( $post_params['api_key'] ) ) {
        return new WP_Error( 'cant-add', __( 'Missing required param', 'st' ) );
    }
    $user_id = $post_params['id'];
    $points = $post_params['points'];
    $description = $post_params['description'];
    $api_key = trim( $post_params['api_key'] );
    $event_type = 'socialtoaster-activity';

    // Verify API key
    if ( $api_key != $saved_api_key ) {
        return new WP_Error( 'cant-add', __( 'API key does not match', 'st' ) );
    }

    $user = get_user_by( 'id', $user_id );

    // Check that user exists
    if ( !$user ) {
        return new WP_Error( 'cant-add', __( 'User not found', 'st' ) );
    }

    try {
        WC_Points_Rewards_Manager::increase_points( $user_id, $points, $event_type );
    } catch (Exception $e) {
        return new WP_Error( 'missing-plugin', __( 'Unable to add - missing Points and Rewards plugin?', 'st' ) );
    }

    return new WP_REST_Response( null, 200 );
}

function stwoo_check_login( $user_login, $user ) {
    $roles = $user->roles;
    $is_customer = in_array( 'customer', $roles, true );
    
    // Skip non-customer logins
    if ( ! $is_customer ) {
        return;
    }
    
    $options = get_option( 'stwoo_settings' );
    
    if ( ! stwoo_minimum_required_settings( $options ) ) {
        return;
    }
    
    // If the 'Allow Direct Signup' admin page checkbox is unchecked, then no need to check
    if ( ! $options['stwoo_allow_direct_signup_field'] ) {
        return;
    }
    
    // Skip users with the cookie already set
    if ( isset($_COOKIE[STWOO_REGISTERED]) && ( $user->ID == $_COOKIE[STWOO_REGISTERED] ) ) {
        return;
    }
    
    $client_key = trim( $options['stwoo_campaign_key_field'] );
    $api_key = trim( $options['stwoo_api_key_field'] );
    $api_url = trim( $options['stwoo_api_url_field'] );
    $api_endpoint = $api_url . '/' . $client_key . '/superfan/allowed/';

    $data = array(
        'email' => $user->user_email,
        'api_key' => $api_key
    );
    
    $args = array(
        'body' => $data,
        'timeout' => '20',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'cookies' => array()
    );

    $response = wp_remote_post( $api_endpoint, $args );
    $http_code = wp_remote_retrieve_response_code( $response );

    if ( 200 == $http_code ) {
        $api_data = json_decode( wp_remote_retrieve_body( $response ) );

        if ( !$api_data->allowed && $api_data->reason_code == 'exists' ) {
            setcookie(STWOO_REGISTERED, $user->ID, 0);
        }
    }
}

function stwoo_clear_cookie() {
    setcookie( STWOO_WP_SSO, 'null', time() - 3600, '/' );
}

function stwoo_points_description( $event_description, $event_type, $event ) {
    $options = get_option( 'stwoo_settings' );
    
    if ( ! stwoo_minimum_required_settings( $options ) ) {
        return $event_description;
    }
    $campaign_name = trim( $options['stwoo_campaign_name_field'] );
    
    switch ( $event_type ) {
        case 'socialtoaster-activity':
            $event_description = __( "Activity from your ${campaign_name} account", 'st' );
            break;
    }

    return $event_description;
}

function stwoo_minimum_required_settings($options) {
    $required_settings = Array(
        'stwoo_campaign_key_field',
        'stwoo_api_key_field',
        'stwoo_api_url_field',
        'stwoo_campaign_url_field',
        'stwoo_campaign_name_field',
        'stwoo_signup_cta_text_field'
    );
    
    if (! is_array( $options ) ) {
        return false;
    }
    
    foreach ( $required_settings as $setting_key ) {
        if (! array_key_exists( $setting_key, $options ) || empty( $options[$setting_key] ) ) {
            return false;
        }
    }
    return true;
}
