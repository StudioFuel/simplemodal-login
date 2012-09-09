=== SimpleModal Login ===
Contributors: emartin24 
Donate link: http://www.ericmmartin.com/donate/
Tags: ajax, login, modal, admin, password, username, register, manage, redirect, widget, plugin
Requires at least: 2.5.0
Tested up to: 3.4.2
Stable tag: 1.0.7

SimpleModal Login provides a modal Ajax login, registration, and password reset feature for WordPress which utilizes jQuery and the SimpleModal jQuery

== Description ==

**SimpleModal Login 1.0 now includes a user registration and password reset feature!**

SimpleModal Login provides a modal Ajax login, registration and password reset feature for WordPress and utilizes jQuery and the SimpleModal jQuery plugin.

SimpleModal Login allows you to create your own custom themes. See the FAQ for details.

Translations: http://plugins.svn.wordpress.org/simplemodal-login/I18n (check the version number for the correct file)



== Installation ==

*Install and Activate*

1. Unzip the downloaded SimpleModal Login zip file
2. Upload the `simplemodal-login` folder and its contents into the `wp-content/plugins/` directory of your WordPress installation
3. Activate SimpleModal Login from Plugins page

*Implement*

There are 3 options for using the SimpleModal Login features:

a) Use `wp_loginout()` or `wp_register()` in your theme. SimpleModal Login will use the loginout and register filters to add the `simplemodal-login` class or `simplemodal-register` class to the respective link.

b) Enable the Keystroke Shortcut option. Once this is enabled, you will be able to invoke SimpleModal Login using the `Ctrl+Alt+L` keystroke.

c) Manually add a Log In or Register link. If your theme does not use `wp_loginout()` and you still want to use this plugin, you can manually edit your theme and add a login link as follows:

    <a href="/wp-login.php" class="simplemodal-login">Log In</a>

    <a href="/wp-login.php?action=register" class="simplemodal-register">Register</a>

*Configure*

1) Configure the SimpleModal Login settings, if necessary, from the SimpleModal Login option in the Settings menu. You can choose from one of the available themes as well as enable/disable the keystroke shortcut.

2) The styles can be changed with the following methods:

* Add a CSS file in your theme's directory and place your custom CSS there. The name of the file should be simplemodal-login-THEME.css. For example, `simplemodal-login-default.css` or `simplemodal-login-osx.css`.
* Add your custom CSS to your theme's `style.css` stylesheet
* Modify the SimpleModal Login CSS files directly in the simplemodal-login/css directory

*Note:* The first two options will ensure that SimpleModal Login updates will not overwrite your custom styles.

== Upgrade Notice ==

There are no special upgrade requirements.

== Frequently Asked Questions ==

= How can I redirect back to the current page after login? =

The WordPress `wp_loginout()` function allows for an optional `$redirect` parameter which is the URL where the user will be sent after a logging in our logging out.

To have the user return to the page they were on, update the code to look like:

    <?php wp_loginout($_SERVER['REQUEST_URI']); ?>

If you are using the Meta Widget (Appearance > Widgets), to use this feature, you may need to delete the Meta Widget and add the code to your sidebar.php file manually.

For example, after you delete the Meta Widget, open sidebar.php (in your theme) and add[1] the following code:

    <ul>
        <?php wp_register(); ?>
        <li><?php wp_loginout(); ?></li>
    </ul>

[1] Place it wherever you'd like to display and modify the code to fit your needs.

Lastly, if you've manually added a log in link, you can change it to:

    <a href="/wp-login.php?redirect_to=<?php echo $_SERVER['REQUEST_URI']; ?>" class="simplemodal-login">Log In</a>



= How can I create my own custom theme? =

*This is for users familiar with CSS and JavaScript, namely jQuery and SimpleModal.*

To create a new theme you'll need to add two files under the `simplemodal-login` plugin directory: `css/THEME.css` and `js/THEME.js`. Replace THEME with the name you would like to use. 

I suggest copying one of the existing themes as a template to start with.


= How can I modify the form HTML? =

*This is an advanced option for users familiar with HTML, PHP and WordPress.*

Starting with SimpleModal Login 1.0, each form (login, register, password reset) has a filter available that allows you to modify the HTML.

The 3 available filters are:

* simplemodal_login_form
* simplemodal_registration_form
* simplemodal_reset_form

To use the filter, you'll need to add code to your theme's functions.php file. For example:

    add_filter('simplemodal_login_form', 'mytheme_login_form');
    function mytheme_login_form($form) {
        // $form contains the SimpleModal Login login HTML

        // do stuff here

        // you have to return the code that you want displayed
        return $form;
    }

You'd probably want to start by copying the form HTML from the appropriate function in the main plugin file and then modifying to fit your requirements.

Things you'll need to change:

1. Change $this->users_can_register (for login and reset forms only)

* Create a `$users_can_register` variable in your function:

    $users_can_register = get_option('users_can_register') ? true : false;

* Replace `$this->users_can_register` with `$users_can_register`

2. Change $this->options['registration'] and $this->options['reset']

* Create an `$options` variables in your function:

    $options = get_option('simplemodal_login_options');

* Replace `$this->options['registration']` with `$options['registration']`

* Replace `$this->options['reset']` with `$options['reset']`


Here are complete working examples for each of the three filters:

* simplemodal_login_form: http://pastebin.com/rm3WWWRS

* simplemodal_registration_form: http://pastebin.com/bVzZBKZf

* simplemodal_reset_form: http://pastebin.com/jpd1RiP9


= Can I remove the "Powered by SimpleModal Login" link?  =

Yup, see below. A donation is appreciated, but not required ;)


= How can I remove the "Powered by SimpleModal Login" link?  =

Just add the following to your `style.css` file:

    .simplemodal-login-credit {display:none;}


*Have a question, comments or feature requests? Be sure to let me know.*

== Screenshots ==

1. Login screen with the default theme.
2. Register screen with the default theme.
3. Reset Password screen with the default theme.
4. Activity indicator with the default theme.
5. Login screen with the osx theme.
6. Register screen with the osx theme.
7. Reset Password screen with the osx theme.
8. Activity indicator with the osx theme.
9. The SimpleModal Login admin settings page


== Changelog ==

= 1.0.7 =
* Upgraded to SimpleModal 1.4.3 (requires jQuery 1.3+)

= 1.0.6 =
* Fix HTML markup bug.

= 1.0.5 =
* Removed "Powered by SimpleModal Login" link and CSS.
* Fixed "empty_both" bug.
* Added modal z-index to prevent stacking issues.
* Upgraded to SimpleModal 1.4.2

= 1.0.4 =
* Added output buffering to the login_form, register_form, and lostpassword_form actions. Thanks to @thenbrent for the fix.
* Fixed 'Undefined variable' warning in WordPress DEBUG mode. Thanks to @thenbrent for the fix.

= 1.0.3 =
* Upgraded to SimpleModal 1.4.1
* Updated add_options_page() arguments to prevent deprecation warning. Thanks to DanHarrison for reporting and providing a fix.
* Removed s.modal.update(); from both osx.js and default.js. It was causing issues with the dialog height.

= 1.0.2 =
* Changed language domain name from simplemodal_login to simplemodal-login (this will affect translation file names)
* Updated pastebin.com link with language domain name updates
* Translations can now be found at http://plugins.svn.wordpress.org/simplemodal-login/I18n/

= 1.0.1 =
* Added support for the wp_loginout() redirect parameter (See FAQ for usage)

= 1.0 =
* Added Password Reset feature
* Added Register feature
* Added support for Peter's Login Redirect plugin
* Added activity indicator for better usability
* Added additional error handling
* Added Keyboard Shortcut option and feature (Ctrl+Alt+L)
* Removed the 'Redirect after login?' option
* Updated POT file (I18n/simplemodal-login.pot)
* Added plugin update logic
* Upgraded to SimpleModal 1.4
* Added additional screenshots
* Added filters for each form (login, register, password reset) output HTML to allow for customization

= 0.3 =
* Added uninstall cleanup code
* Updated POT file
* Added the ability to add new themes dynamically
* Renamed CSS and JS theme files (removed simplemodal-login- prefix)
* Removed unused code
* Changed redirect option meaning on the Settings page

= 0.2 =
* Updated POT file

= 0.1 =
* Initial beta release