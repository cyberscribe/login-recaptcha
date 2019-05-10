=== Login No Captcha reCAPTCHA ===
Contributors: robertpeake, robert.peake
Tags: google,nocaptcha,recaptcha,security,login,bots
Requires at least: 4.6
Tested up to: 5.2
Stable tag: 1.6.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a Google No Captcha ReCaptcha checkbox to your Wordpress and Woocommerce login, forgot password, and user registration pages.

== Description ==

Adds a Google No Captcha ReCaptcha checkbox to your Wordpress and Woocommerce login, forgot password, and user registration pages. Denies access to automated scripts while making it easy on humans to log in by checking a box. As Google says, it is "Tough on bots, easy on humans."

== Installation ==

Install as normal for WordPress plugins.

== Frequently Asked Questions ==

= Why should I install this plugin? =

Many Worpdress sites are bombarded by automated scripts trying to log in to the admin over and over. 

The No Captcha is a very simple, Google-supported test to quickly deny access to automated scripts. It is great by itself to instantly make your Wordpress site more secure, or can be used with other plugins (like Google Authenticator, Limit Login Attempts, etc.) as part of a defense-in-depth strategy.

= There are a lot of other plugins for this, why should I install <em>this</em> one? =

I've gone to great lengths to try to make sure this plugin is easy to use and install, that it is compatible with different Wordpress configurations, supports multiple languages, and that you won't accidentally lock yourself out of the admin by using it. I use it myself on my own sites as well. So far, it just works.

= Does this plugin support [insert name] custom login page plugin? =

Probably not. Many custom login form plugins do not call the standard <a href="https://codex.wordpress.org/Plugin_API/Action_Reference/login_form">login_form</a> action hook from their login forms, making it impossible to correctly render the captcha after the password prompt. For this reason, this plugin only supports the default wp-login.php and WooCommerce forms. Many such plugins do offer captcha fields (sometimes as a paid upgrade). This plugin tries to do just a few things well.

= Does this plugin add a CAPTCHA to comment forms? =

No. This plugin is designed to thwart automated hacking attempts, not prevent comment spam. Most good comment plugins have their own spam prevention methods. This plugin tries to do just a few things well.

= Does this plugin add a CAPTCHA to custom forms? =

No. This plugin is designed to thwart automated hacking attempts, not prevent spam from custom forms. Most good custom form plugins have their own spam prevention methods. Many of them support a CAPTCHA field already. This plugin tries to do just a few things well.

= Can I help? =

Yes, please. Submit pull requests on <a href="https://github.com/cyberscribe/login-recaptcha">github</a>.

= I am having trouble with the reCAPTCHA in Internet Explorer =

Please see <a href="https://support.google.com/recaptcha/answer/6223838?hl=en">this page</a> for help from Google.

= I still see lots of brute force attacks against /wp-login.php in my log files =

The reCAPTCHA plugin will not prevent the attempt of brute force attacks, rather it will simply ensure that they do not succeed. That is, scripts may still attempt direct POST attacks against /wp-login.php, but without the correct reCAPTCHA data, they will not go through (even if they have guessed the login and password correctly). To prevent repeat attempts against /wp-login.php, consider using a plugin that <a href="https://en-gb.wordpress.org/plugins/search.php?q=Limit+Login+Attempts">limits login attempts</a> in conjunction with this one. Other approaches, such as a <a href="https://en-gb.wordpress.org/plugins/tags/web-application-firewall">web application firewall</a> should also form a part of your complete defense-in-depth strategy.

= Where can I learn more about Google reCAPTCHA? =

<a href="https://www.google.com/recaptcha/intro/index.html">https://www.google.com/recaptcha/intro/index.html</a>

= What are your boring legal disclaimers? =

This plugin is not affiliated with or endorsed by Google in any way. Google is a registered trademark of Google, Inc. By using reCAPTCHA you agree  the <a href="https://www.google.com/intl/en/policies/terms/">terms of service</a> set out by Google. The author provides no warranty as to the suitability to any purpose of this software. You agree to use it entirely at your own risk.

== Screenshots ==

1. Configuration options screen
2. Login screen once configured

== Changelog ==

= 1.6.4 =

 * Tested with 5.2
 * Using callback to disable submit buttons

= 1.6.3 =

 * Resolve issue with captcha not displaying on embedded login form on WooCommerce checkout page
 
= 1.6.2 =

 * Resolve fatal error with old versions of PHP (reported on 5.4.45)

= 1.6.1 = 

 * Disable submit buttons via javascript on Woo forms
 * Improve error messaging

= 1.6 =

 * Added IP whitelist functionality (thanks @farley1122)
 * More comprehensive protection of signup endpoints, including wp-signup.php and my-account page

= 1.5 =

 * Resolve issue introduced in 1.4x whereby captcha was being be bypassed
 * ALL USERS STRONGLY ENCOURAGED TO UPDATE FOR SECURITY REASONS AND NOT USE 1.4x

= 1.4.1 =

 * Align language text domain with plugin tag to allow translation contributions

= 1.4 =

 * Added support for registration forms (thanks to d2roth)
 * Align filter/hook calls with codex
 * Increase priority (earlier execution) of login checking (prevents unnecessary alerts from e.g. WordFence)

= 1.3.3 =

 * Fixed bug with fallback to cURL in cases where TLS is misconfigured

= 1.3.2 =

 * Compatibility fix for use of empty() language construct in php 5.x
 
= 1.3.1 =

 * Added experimental support for v3 (hidden for now)

= 1.3 =

 * Added reCaptcha to lost password form
 * Added Russian translation
 * Tested with 5.0

= 1.2.5 =

 * Revert lost password form change as it was frontend-only

= 1.2.4 =

 * Add reCaptcha to lost password form

= 1.2.3 =

 * Improved Section 508 compliance
 * Do not check for noCaptcha values when using a non-WordPress authentication method other than WooCommerce
 * Add standard WordPress shake effect to invalid login response

= 1.2.2 =

 * Do not check for noCaptcha values when using a non-WordPress authentication method
 * Fix bug with submit button greyed out on settings page

= 1.2.1 =

 * Implement noCaptcha for WooCommerce customer login form

= 1.2 =

 * Fixed an important security issue (thanks to jezevec10 for reporting) to harden the reCaptcha-enabled login page against clever bots

= 1.1.11 =

 * Added French translation (thanks to fdinh)

= 1.1.10 =

 * Minor bugfix for error reporting

= 1.1.7 =

 * Bug fix for login form display in admin, testing on 4.5

= 1.1.6 =

 * Disable login with js until NoCaptcha returns

= 1.1.5 =
 * Tested compatible with 4.4x

= 1.1.4 =

 * Better display of captcha when javascript disabled (thanks to webmasteral)

= 1.1.3 =

 * Improved handling of certain Google responses

= 1.1.2 =

 * Improved just-in-time script registration (only for admin/login)

= 1.1.1 =

 * Remove warning about enqueueing css/js too early

= 1.1 =

 * Major security improvement: now supporting reCaptcha checking with javascript disabled (thanks to mfjtf)

= 1.0.3 =

 * Resolve issue with Wordpress hosted on an inaccessible domain (e.g. localhost)

= 1.0.2 =

 * Resolve bug with wp_remote_post() payload

= 1.0.1 =

 * Resolve linking issue due to repository maintainers renaming the plugin

= 1.0.0 =

* Initial release
