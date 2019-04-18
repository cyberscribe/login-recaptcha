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
        <tr valign="top">
                <th scope="row"><label for="id_login_nocaptcha_whitelist"><?php _e('Whitelist IP ( 1 per line )','login-recaptcha'); ?> (v2): </span>
                </label></th>
            <td><textarea type="text" id="id_login_nocaptcha_whitelist" name="login_nocaptcha_whitelist" cols="39" rows="5"><?php echo get_option('login_nocaptcha_whitelist'); ?></textarea></td>
        </tr>
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
