=== SimpleModal Login ===
Contributors: emartin24 
Donate link: http://www.ericmmartin.com/donate/
Tags: ajax, login, modal, admin, password, username, register, manage, redirect
Requires at least: 2.5.0
Tested up to: 3.0
Stable tag: 0.3

SimpleModal Login provides a modal Ajax login, registration, and password reset feature for WordPress which utilizes jQuery and the SimpleModal jQuery

== Description ==

SimpleModal Login provides a modal Ajax login, registration and password reset feature for WordPress and utilizes jQuery and the SimpleModal jQuery plugin.

SimpleModal Login allows you to create your own custom themes. See the FAQ for details.
	
== Installation ==

*Install and Activate*

1. Unzip the downloaded SimpleModal Login zip file
2. Upload the `simplemodal-login` folder and its contents into the `wp-content/plugins/` directory of your WordPress installation
3. Activate SimpleModal Login from Plugins page

*Implement*

There are 3 options for using the SimpleModal Login features:

a) Use `wp_loginout()` in your theme. SimpleModal Login will use the loginout filter to add an `class` to the WordPress Login link, which is what enables the plugin to work.

b) Enable the Keystroke Shortcut option. Once this is enabled, you will be able to invoke SimpleModal Login using the `Ctrl + Alt + L` keystroke.

c) Manually add a login link. If your theme does not use `wp_loginout()` and you still want to use this plugin, you can manually edit your theme and add a login link as follows:

	<a href="/wp-login.php" class="simplemodal-login">Login</a>

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

= How can I create my own custom theme? =

*This is for users familiar with CSS and JavaScript, namely jQuery and SimpleModal.*

To create a new theme you'll need to add two files under the `simplemodal-login` plugin directory: `css/THEME.css` and `js/THEME.js`. Replace THEME with the name you would like to use. 

I suggest copying one of the existing themes as a template to start with.

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
4. Loading screen with the default theme.
5. Login screen with the osx theme.
6. Register screen with the osx theme.
7. Reset Password screen with the osx theme.
8. Loading screen with the osx theme.
9. The SimpleModal Login admin settings page


== Changelog ==

= 1.0 =
* Added Password Reset feature
* Added Register feature
* Added support for Peter's Login Redirect plugin
* Added loading screen for better usability
* Added additional error handling
* Added Keyboard Shortcut option and feature (Ctrl + Alt + L)
* Removed the 'Redirect after login?' option
* Updated POT file (I18n/simplemodal-login.pot)
* Added plugin update logic
* Upgraded to SimpleModal 1.4
* Added additional screenshots
* Added filters for each form (login, register, reset) output HTML to allow for customization

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