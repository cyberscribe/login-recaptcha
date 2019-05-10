<?php
/*
Plugin Name: Login No Captcha reCAPTCHA (Google)
Plugin URI: https://wordpress.org/plugins/login-recaptcha/
Description: Adds a Google CAPTCHA checkbox to the login, registration, and forgot password forms, thwarting automated hacking attempts
Author: Robert Peake
Version: 1.6.4
Author URI: https://github.com/cyberscribe/login-recaptcha
Text Domain: login-recaptcha
Domain Path: /languages/
*/

if ( !function_exists( 'add_action' ) ) {
    die();
}

class LoginNocaptcha {

    public static function init() {

        add_action( 'plugins_loaded', array('LoginNocaptcha', 'load_textdomain') );
        add_action( 'admin_menu', array('LoginNocaptcha', 'register_menu_page' ));
        add_action( 'admin_init', array('LoginNocaptcha', 'register_settings' ));
        add_action( 'admin_notices', array('LoginNocaptcha', 'admin_notices' ));

        delete_option('login_nocaptcha_v3_key');
        delete_option('login_nocaptcha_v3_secret');

        if (LoginNocaptcha::valid_key_secret(get_option('login_nocaptcha_key')) &&
            LoginNocaptcha::valid_key_secret(get_option('login_nocaptcha_secret')) ) {
            add_action('login_enqueue_scripts', array('LoginNocaptcha', 'enqueue_scripts_css'));
            add_action('admin_enqueue_scripts', array('LoginNocaptcha', 'enqueue_scripts_css'));

            /* Handle form display logic downstream of this */
            add_action('login_form',array('LoginNocaptcha', 'nocaptcha_form'));
            add_action('register_form',array('LoginNocaptcha', 'nocaptcha_form'), 99);
            add_action('signup_extra_fields',array('LoginNocaptcha', 'nocaptcha_form'), 99);
            add_action('lostpassword_form',array('LoginNocaptcha', 'nocaptcha_form'));

            /* if array is in whitelist, do not hook in captcha for login, registration, or lost password */
            if ( !LoginNocaptcha::ip_in_whitelist() ) {
                add_filter('registration_errors',array('LoginNocaptcha', 'authenticate'), 10, 3);
                add_action('lostpassword_post',array('LoginNocaptcha', 'authenticate'), 10, 1);
                add_filter('authenticate', array('LoginNocaptcha', 'authenticate'), 30, 3);
                add_filter('shake_error_codes', array('LoginNocaptcha', 'add_shake_error_codes') );
                add_action('plugins_loaded', array('LoginNocaptcha', 'action_plugins_loaded'));
                delete_option('login_nocaptcha_notice');
            }
        } else {
            delete_option('login_nocaptcha_working');
            update_option('login_nocaptcha_message_type', 'notice-error');
            update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha has not been properly configured. <a href="%s">Click here</a> to configure.','login-recaptcha'), 'options-general.php?page=login-recaptcha/admin.php'));
            add_action('woocommerce_register_post',array('LoginNocaptcha', 'authenticate'));
add_action('woocommerce_register_form',array('LoginNocaptcha', 'nocaptcha_form'));
        }

    }

    public static function action_plugins_loaded() {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            add_action('wp_head', array('LoginNocaptcha', 'enqueue_scripts_css'));
            add_action('woocommerce_login_form',array('LoginNocaptcha', 'nocaptcha_form'));
            add_action('woocommerce_lostpassword_form',array('LoginNocaptcha', 'nocaptcha_form'));
            add_action('woocommerce_register_post',array('LoginNocaptcha', 'woo_authenticate'), 10, 3);
            add_action('woocommerce_register_form',array('LoginNocaptcha', 'nocaptcha_form'));
        }
    }

    public static function load_textdomain() {
        load_plugin_textdomain( 'login-recaptcha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public static function register_menu_page(){
        add_options_page( __('Login NoCatpcha Options','login-recaptcha'), __('Login NoCaptcha','login-recaptcha'), 'manage_options', plugin_dir_path(  __FILE__ ).'admin.php');
    }

    public static function register_settings() {

        /* user-configurable values */
        add_option('login_nocaptcha_key', '');
        add_option('login_nocaptcha_secret', '');
        add_option('login_nocaptcha_whitelist', '');

        /* user-configurable value checking public static functions */
        register_setting( 'login_nocaptcha', 'login_nocaptcha_key', 'LoginNocaptcha::filter_string' );
        register_setting( 'login_nocaptcha', 'login_nocaptcha_secret', 'LoginNocaptcha::filter_string' );
        register_setting( 'login_nocaptcha', 'login_nocaptcha_whitelist', 'LoginNocaptcha::filter_whitelist' );

        /* system values to determine if captcha is working and display useful error messages */
        delete_option('login_nocaptcha_working');
        add_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha has not been properly configured. <a href="%s">Click here</a> to configure.','login-recaptcha'), 'options-general.php?page=login-recaptcha/admin.php'));
        add_option('login_nocaptcha_message_type', 'notice-error');
        if (LoginNocaptcha::valid_key_secret(get_option('login_nocaptcha_key')) &&
           LoginNocaptcha::valid_key_secret(get_option('login_nocaptcha_secret')) ) {
            update_option('login_nocaptcha_working', true);
        } else {
            delete_option('login_nocaptcha_working');
            update_option('login_nocaptcha_message_type', 'notice-error');
            update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha has not been properly configured. <a href="%s">Click here</a> to configure.','login-recaptcha'), 'options-general.php?page=login-recaptcha/admin.php'));
        }
    }

    public static function filter_string( $string ) {
        return trim(filter_var($string, FILTER_SANITIZE_STRING)); //must consist of valid string characters
    }

    public static function valid_key_secret( $string ) {
        if (strlen($string) === 40) {
            return true;
        } else {
            return false;
        }
    }

    public static function filter_whitelist( $string ) {
        return preg_replace( '/[ \t]/', '', trim(filter_var($string, FILTER_SANITIZE_STRING)) ); //must consist of valid string characters, remove spaces
    }

    public static function get_ip_address() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    $ip = filter_var($ip, FILTER_VALIDATE_IP);
                    if (!empty($ip)) {
                        return $ip;
                    }
                }
            }
        }
        return false;
    }

    public static function ip_in_whitelist() {

        /* get whitelist and convert to array */
        $whitelist_str = get_option('login_nocaptcha_whitelist');
        if (!empty($whitelist_str)) {
            $whitelist = explode("\r\n", trim($whitelist_str));
        } else {
            $whitelist = array();
        }

        /* get ip address */
        $ip = LoginNoCaptcha::get_ip_address();

        if ( !empty($ip) && !empty($whitelist) && in_array($ip, $whitelist) ) {
            return true;
        } else {
            return false;
        }

    }

    public static function register_scripts_css() {
        $api_url = 'https://www.google.com/recaptcha/api.js?onload=submitDisable';
        wp_register_script('login_nocaptcha_google_api', $api_url, array(), null );
        wp_register_style('login_nocaptcha_css', plugin_dir_url( __FILE__ ) . 'css/style.css');
    }

    public static function enqueue_scripts_css() {
        if(!wp_script_is('login_nocaptcha_google_api','registered')) {
            LoginNocaptcha::register_scripts_css();
        }
        if ( (!empty($GLOBALS['pagenow']) && ($GLOBALS['pagenow'] == 'options-general.php' ||
                $GLOBALS['pagenow'] == 'wp-login.php')) || 
                (function_exists('is_account_page') && is_account_page()) ||
                (function_exists('is_checkout') && is_checkout())
            ) {
            wp_enqueue_script('login_nocaptcha_google_api');
            wp_enqueue_style('login_nocaptcha_css');
        }
    }

    public static function get_google_errors_as_string( $g_response ) {
        $string = '';
        $codes = array( 'missing-input-secret' => __('The secret parameter is missing.','login-recaptcha'),
                        'invalid-input-secret' => __('The secret parameter is invalid or malformed.','login-recaptcha'),
                        'missing-input-response' => __('The response parameter is missing.','login-recaptcha'),
                        'invalid-input-response' => __('The response parameter is invalid or malformed.','login-recaptcha')
                        );
        foreach ($g_response->{'error-codes'} as $code) {
            $string .= $codes[$code].' ';
        }
        return trim($string);
    }

    public static function nocaptcha_form() {

        if (!LoginNocaptcha::ip_in_whitelist()) {
            echo sprintf('<div class="g-recaptcha" id="g-recaptcha" data-sitekey="%s" data-callback="submitEnable" data-expired-callback="submitDisable"></div>', get_option('login_nocaptcha_key'))."\n";
            echo '<script>'."\n";
            echo "    function submitEnable() {\n";
            echo "                 var button = document.getElementById('wp-submit');\n";
            echo "                 if (button === null) {\n";
            echo "                     button = document.getElementById('submit');\n";
            echo "                 }\n";
            echo "                 if (button !== null) {\n";
            echo "                     button.removeAttribute('disabled');\n";
            echo "                 }\n";
            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                echo "                 var woo_buttons = ".json_encode(array('.woocommerce-form-login button','.woocommerce-form-register button','.woocommerce-ResetPassword button')).";\n"; 
                echo "                 if (typeof jQuery != 'undefined') {\n";
                echo "                     jQuery.each(woo_buttons,function(i,btn) {\n";
                echo "                         jQuery(btn).removeAttr('disabled');\n";
                echo "                     });\n";
                echo "                 }\n";
            }
            echo "             }\n";
            echo "    function submitDisable() {\n";
            echo "                 var button = document.getElementById('wp-submit');\n";
            // do not disable button with id "submit" in admin context, as this is the settings submit button
            if (!is_admin()) {
                echo "                 if (button === null) {\n";
                echo "                     button = document.getElementById('submit');\n";
                echo "                 }\n";
            }
            echo "                 if (button !== null) {\n";
            echo "                     button.setAttribute('disabled','disabled');\n";
            echo "                 }\n";
            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                echo "                 var woo_buttons = ".json_encode(array('.woocommerce-form-login button','.woocommerce-form-register button','.woocommerce-ResetPassword button')).";\n"; 
                echo "                 if (typeof jQuery != 'undefined') {\n";
                echo "                     jQuery.each(woo_buttons,function(i,btn) {\n";
                echo "                        jQuery(btn).attr('disabled','disabled');\n";
                echo "                     });\n";
                echo "                 }\n";
            }
            echo "             }\n";
            echo '</script>'."\n";
            echo '<noscript>'."\n";
            echo '  <div style="width: 100%; height: 473px;">'."\n";
            echo '      <div style="width: 100%; height: 422px; position: relative;">'."\n";
            echo '          <div style="width: 302px; height: 422px; position: relative;">'."\n";
            echo sprintf('              <iframe src="https://www.google.com/recaptcha/api/fallback?k=%s"', get_option('login_nocaptcha_key'))."\n";
            echo '                  frameborder="0" title="captcha" scrolling="no"'."\n";
            echo '                  style="width: 302px; height:422px; border-style: none;">'."\n";
            echo '              </iframe>'."\n";
            echo '          </div>'."\n";
            echo '          <div style="width: 100%; height: 60px; border-style: none;'."\n";
            echo '              bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px; background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">'."\n";
            echo '              <textarea id="g-recaptcha-response" name="g-recaptcha-response"'."\n";
            echo '                  title="response" class="g-recaptcha-response"'."\n";
            echo '                  style="width: 250px; height: 40px; border: 1px solid #c1c1c1;'."\n";
            echo '                  margin: 10px 25px; padding: 0px; resize: none;" value="">'."\n";
            echo '              </textarea>'."\n";
            echo '          </div>'."\n";
            echo '      </div>'."\n";
            echo '</div><br>'."\n";
            echo '</noscript>'."\n";
        } else {
            update_option('login_nocaptcha_notice', time());
            update_option('login_nocaptcha_message_type', 'notice-info');
            update_option('login_nocaptcha_error', sprintf(__('Captcha bypassed by whitelist for ip address %s',    'login-recaptcha'), LoginNoCaptcha::get_ip_address()) );
            echo '<p style="color: red; font-weight: bold;">'.sprintf(__('Captcha bypassed by whitelist for ip address %s','login-recaptcha'), LoginNoCaptcha::get_ip_address()).'</p>';
        }
    }

    public static function woo_authenticate($username, $email, $errors) {
        return LoginNocaptcha::authenticate( $errors, $username );
    }

    public static function authenticate($user_or_email, $username = null, $password = null) {
        if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) !== 'wp-login.php' && //calling context must be wp-login.php
            !isset($_POST['woocommerce-login-nonce']) && !isset($_POST['woocommerce-lost-password-nonce']) && !isset($_POST['woocommerce-register-nonce']) ) { //or a WooCommerce form
            //bypass reCaptcha checking
            update_option('login_nocaptcha_notice', time());
            update_option('login_nocaptcha_message_type', 'notice-error');
            update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha was bypassed on login page: %s.','login-recaptcha'),basename($_SERVER['PHP_SELF'])));
            return $user_or_email;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          return $user_or_email;
        }
        if (isset($_POST['g-recaptcha-response'])) {
            $response = LoginNocaptcha::filter_string($_POST['g-recaptcha-response']);
            $remoteip = LoginNoCaptcha::get_ip_address();
            $secret = get_option('login_nocaptcha_secret');
            $payload = array('secret' => $secret, 'response' => $response, 'remoteip' => $remoteip);
            $result = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array('body' => $payload) );
            if (is_wp_error($result)) { // disable SSL verification for older clients and misconfigured TLS trust certificates
                $error_msg = $result->get_error_message();
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $result = curl_exec($ch);
                $g_response = json_decode( $result );
                update_option('login_nocaptcha_notice', time());
                update_option('login_nocaptcha_message_type', 'notice-warning');
                update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha fell back to using cURL instead of wp_remote_post(). The error message was: %s.','login-recaptcha'), $error_msg) );
            } else {
                $g_response = json_decode($result['body']);
            }
            if (is_object($g_response)) {
                if ( $g_response->success ) {
                    update_option('login_nocaptcha_working', true);
                    return $user_or_email; // success, let them in
                } else {
                    if ( isset($g_response->{'error-codes'}) && $g_response->{'error-codes'} && in_array('missing-input-response', $g_response->{'error-codes'})) {
                        update_option('login_nocaptcha_working', true);
                        if (is_wp_error($user_or_email)) {
                            $user_or_email->add('no_captcha', __('<strong>ERROR</strong>&nbsp;: Please check the ReCaptcha box.','login-recaptcha'));
                            return $user_or_email;
                        } else {
                            return new WP_Error('authentication_failed', __('<strong>ERROR</strong>&nbsp;: Please check the ReCaptcha box.','login-recaptcha'));
                        }
                    } else if ( isset($g_response->{'error-codes'}) && $g_response->{'error-codes'} &&
                                (in_array('missing-input-secret', $g_response->{'error-codes'}) || in_array('invalid-input-secret', $g_response->{'error-codes'})) ) {
                        delete_option('login_nocaptcha_working');
                        update_option('login_nocaptcha_notice', time());
                        update_option('login_nocaptcha_google_error', 'error');
                        update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha is not working. <a href="%s">Please check your settings</a>. The message from Google was: %s', 'login-recaptcha'),
                                                               'options-general.php?page=login-recaptcha/admin.php',
                                                                self::get_google_errors_as_string($g_response)));
                        return $user_or_email; //invalid secret entered; prevent lockouts
                    } else if( isset($g_response->{'error-codes'})) {
                        update_option('login_nocaptcha_working', true);
                        if (is_wp_error($user_or_email)) {
                            $user_or_email->add('invalid_captcha', __('<strong>ERROR</strong>&nbsp;: Incorrect ReCaptcha, please try again.','login-recaptcha'));
                            return $user_or_email;
                        } else {
                            return new WP_Error('authentication_failed', __('<strong>ERROR</strong>&nbsp;: Incorrect ReCaptcha, please try again.','login-recaptcha'));
                        }
                    } else {
                        delete_option('login_nocaptcha_working');
                        update_option('login_nocaptcha_notice', time());
                        update_option('login_nocaptcha_google_error', 'error');
                        update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha is not working. <a href="%s">Please check your settings</a>.', 'login-recaptcha'), 'options-general.php?page=login-recaptcha/admin.php').' '.__('The response from Google was not valid.','login-recaptcha'));
                        return $user_or_email; //not a sane response, prevent lockouts
                    }
                }
            } else {
                delete_option('login_nocaptcha_working');
                update_option('login_nocaptcha_notice',time());
                update_option('login_nocaptcha_google_error', 'error');
                update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha is not working. <a href="%s">Please check your settings</a>.', 'login-recaptcha'), 'options-general.php?page=login-recaptcha/admin.php').' '.__('The response from Google was not valid.','login-recaptcha'));
                return $user_or_email; //not a sane response, prevent lockouts
            }
        } else {
            update_option('login_nocaptcha_working', true);
            if (isset($_POST['action']) && $_POST['action'] === 'lostpassword') {
                return new WP_Error('authentication_failed', __('<strong>ERROR</strong>&nbsp;: Please check the ReCaptcha box.','login-recaptcha'));
            }
            if (is_wp_error($user_or_email)) {
                $user_or_email->add('no_captcha', __('<strong>ERROR</strong>&nbsp;: Please check the ReCaptcha box.','login-recaptcha'));
                return $user_or_email;
            } else {
                return new WP_Error('authentication_failed', __('<strong>ERROR</strong>&nbsp;: Please check the ReCaptcha box.','login-recaptcha'));
            }
        }
    }

    public static function admin_notices() {
        // not working, or notice fired in last 30 seconds
        $login_nocaptcha_error = get_option('login_nocaptcha_error');
        $login_nocaptcha_working = get_option('login_nocaptcha_working'); 
        $login_nocaptcha_notice = get_option('login_nocaptcha_notice');
        $time = time();
        if(!empty($login_nocaptcha_error) && (empty($login_nocaptcha_working) || ($time - $login_nocaptcha_notice < 30))) {
            $message_type = get_option('login_nocaptcha_message_type');
            if (empty($message_type)) {
                $message_type = 'notice-info';
            }
            echo '<div class="notice '.$message_type.' is-dismissible">'."\n";
            echo '    <p>'."\n";
            echo get_option('login_nocaptcha_error');
            echo '    </p>'."\n";
            echo '</div>'."\n";
        }
    }

    public static function add_shake_error_codes( $shake_error_codes ) {
        $shake_error_codes[] = 'no_captcha';
        $shake_error_codes[] = 'invalid_captcha';
        return $shake_error_codes;
    }
}
LoginNocaptcha::init();
