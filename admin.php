<?php
if (!is_admin()) {
    die();
}
?><div class="wrap">
<h2><?php _e('Login NoCaptcha Options','login-recaptcha'); ?></h2>
<form method="post" action="options.php">
    <?php
    echo settings_fields( 'login_nocaptcha' );
    ?>
    <p><?php echo sprintf(__('<a href="%s" target="_blank">Click here</a> to create or view keys for Google NoCaptcha.','login-recaptcha'),'https://www.google.com/recaptcha/admin#list'); ?></p>
    <table class="form-table form-v2">
        <tr valign="top">
                <th scope="row"><label for="id_login_nocaptcha_key"><?php _e('Site Key','login-recaptcha'); ?> (v2): </span>
                </label></th>
            <td><input type="text" id="id_login_nocaptcha_key" name="login_nocaptcha_key" value="<?php echo get_option('login_nocaptcha_key'); ?>" size="40" /></td>
        </tr>
        <tr valign="top">
                <th scope="row"><label for="id_login_nocaptcha_secret"><?php _e('Secret Key','login-recaptcha'); ?> (v2): </span>
                </label></th>
            <td><input type="text" id="id_login_nocaptcha_secret" name="login_nocaptcha_secret" value="<?php echo get_option('login_nocaptcha_secret'); ?>" size="40" /></td>
        </tr>
    </table>
    <table class="form-table form-v2">
        <thead>
            <tr valign="top">
                <td colspan="2">
                    <label for="login-recaptcha-toggle-advanced"><?php _e('Advanced Options','login-recaptcha'); ?></label>
                    <button type="button" class="handlediv" aria-expanded="false" id="login-recaptcha-toggle-advanced" name="login-recaptcha-toggle-advanced" style="background: none; border: 0;">
                        <span class="screen-reader-text"><?php _e('Toggle Advanced Options','login-recaptcha'); ?></span>
                        <span class="toggle-indicator" aria-hidden="true" id="login-recaptcha-toggle-indicator">◀&#xFE0E;</span>
                    </button>
                    <script>
                    document.querySelector('#login-recaptcha-toggle-advanced').addEventListener('click', function(e) {
                        var advanced_options = document.querySelector('#login-recaptcha-advanced-options');
                        var toggle_inidcator = document.querySelector('#login-recaptcha-toggle-indicator');
                        if (advanced_options.style.display === 'none') {
                            toggle_inidcator.innerHTML = '▼&#xFE0E;';
                            advanced_options.style.display = "block";
                            this.setAttribute('aria-expanded') = "true";
                        } else {
                            toggle_inidcator.innerHTML = '◀&#xFE0E;';
                            advanced_options.style.display = "none";
                            this.setAttribute('aria-expanded') = "false";
                        }
                    });
                    </script>
                </td>
            </tr> 
        </thead>
        <tbody id="login-recaptcha-advanced-options" style="display: none;">
            <tr valign="top">
                    <th scope="row"><label for="id_login_nocaptcha_whitelist"><?php _e('Whitelist IP ( 1 per line )','login-recaptcha'); ?>: </span>
                    </label></th>
                <td><textarea type="text" id="id_login_nocaptcha_whitelist" name="login_nocaptcha_whitelist" cols="39" rows="5"><?php echo get_option('login_nocaptcha_whitelist'); ?></textarea></td>
            </tr>
            <?php if (!empty(get_option('login_nocaptcha_ip_detection_method'))): ?>
                <tr valign="top">
                        <th scope="row"><label><?php _e('IP Detection Method','login-recaptcha'); ?>: </span>
                        </label></th>
                        <td>
                            <a href="https://www.php.net/manual/<?php echo htmlspecialchars(substr(get_locale(),0,2)); ?>/reserved.variables.server.php" target="_blank">
                                <pre><?php echo get_option('login_nocaptcha_ip_detection_method'); ?></pre>
                            </a>
                        </td>
                </tr>
            <?php endif; ?>
            <tr vlaign="top">
                <td colspan="2">
                    <p>
                    <?php _e('Please note: the whitelist feature is provided as a courtesy for testing purposes. In some server configurations, an attacker who knows the value of one of the whitelisted IP adresses might be able to spoof that address to bypass the captcha. By using the whitelist, you acknowledge that you have considered and undestand this risk.', 'login-recaptcha'); ?>
                    </p>
                </td>
            </tr> 
            <tr valign="top">
                <td colspan="2"><input type="checkbox" id="id_login_nocaptcha_disable_css" name="login_nocaptcha_disable_css" <?php if (!empty(get_option('login_nocaptcha_disable_css'))) echo 'checked="checked"'; ?> value="1">
                    <label for="id_login_nocaptcha_disable_css"><?php _e('Disable CSS','login-recaptcha'); ?></span></label>
                </td>
            </tr>
        </tbody>
    </table>
    <p>
    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','login-recaptcha'); ?>">
    <button name="reset" id="reset" class="button">
        <?php _e('Delete Keys and Disable','login-recaptcha'); ?>
    </button>
    </p>
</form>
<?php if(strlen(get_option('login_nocaptcha_key')) > 0 && strlen(get_option('login_nocaptcha_secret')) > 0): ?>
    <h3><?php _e('Example','login-recaptcha'); ?></h3>
    <?php wp_enqueue_script('login_nocaptcha_google_api'); ?>
    <?php LoginNocaptcha::nocaptcha_form(); ?>
    <h3><?php _e('Next Steps','login-recaptcha'); ?></h3>
    <ol>
        <li><?php _e('If you see an error message above, check your keys before proceeding.','login-recaptcha'); ?></li>
        <li><?php _e('If the reCAPTCHA displays correctly above, proceed as follows:','login-recaptcha'); ?></li>
        <ol>
            <li><?php _e('Open a completely different browser than this one','login-recaptcha'); ?></li>
            <li><?php _e('If you are logged in on that new browser, log out','login-recaptcha'); ?></li>
            <li><?php _e('Attempt to log in to your site admin from that new browser','login-recaptcha'); ?></li>
        </ol>
        <li><?php _e('Do <em>not</em> close this window or log out from this browser until you are confident that reCAPTCHA is working and you will be able to log in again. <br /><strong>You have been warned</strong>.','login-recaptcha'); ?></li>
        <li><?php echo sprintf(__('If you have any problems logging in, click "%s" above and/or deactivate the plugin.','login-recaptcha'), __('Delete Keys and Disable','login-recaptcha')); ?></li>
    </ol>
<?php endif; ?>
</div>
<script>
(function($) {
    $('#reset').on('click', function(e) {
        e.preventDefault();
        $('#id_login_nocaptcha_key').val('');
        $('#id_login_nocaptcha_secret').val('');
        $('#id_login_nocaptcha_whitelist').val('');
        $('#submit').trigger('click');
    });
})(jQuery);
</script>
<style>
    #submit + #reset {
        margin-left: 1em;
    }
</style>
