=== SimpleModal Login ===
Contributors: emartin24 
Donate link: http://www.ericmmartin.com/donate/
Tags: ajax, login, modal, admin, password, username
Requires at least: 2.2.0
Tested up to: 2.9
Stable tag: 0.2

SimpleModal Login provides a modal Ajax modal login for WordPress

== Description ==

SimpleModal Login provides a modal Ajax login for WordPress and utilizes jQuery and the SimpleModal jQuery plugin.

SimpleModal Login allows you to create your own custom themes. See the FAQ for details.
	
== Installation ==

*Install and Activate*

1. Unzip the downloaded SimpleModal Login zip file
2. Upload the `simplemodal-login` folder and its contents into the `wp-content/plugins/` directory of your WordPress installation
3. Activate SimpleModal Login from Plugins page

*Implement*

SimpleModal Login relies on the use of `wp_loginout()` in your theme. SimpleModal Login will use the loginout filter to add an `class` to the WordPress Login link, which is what enables the plugin to work.

Other than requiring that `wp_loginout()` is used in your theme, there are no other changes that are required to use SimpleModal Login.

If your theme does not use `wp_loginout()` and you still want to use this plugin, you can manually edit your theme and add a login link as follows:

	<a href="/wp-login.php" class="simplemodal-login">Login</a>

*Configure*

1) Configure the SimpleModal Login settings, if necessary, from the SimpleModal Login option in the Settings menu. You can choose from one of the available themes as well as select where the user will be taken upon successful logon.

2) The styles can be changed with the following methods:

* Add a CSS file in your theme's directory and place your custom CSS there. The name of the file should be simplemodal-login-THEME.css. For example, `simplemodal-login-default.css` or `simplemodal-login-osx.css`.
* Add your custom CSS to your theme's `style.css`
* Modify the SimpleModal Login CSS files directly in the simplemodal-login/css directory

*Note:* The first two options will ensure that SimpleModal Login updates will not overwrite your custom styles.

== Upgrade Notice ==

There are no special upgrade requirements.

== Frequently Asked Questions ==

= How can I create my own custom theme? =

*This is for users familiar with CSS and JavaScript, namely jQuery and SimpleModal.*

To create a new theme you'll need to add two files under the `simplemodal-login` plugin directory: `css/THEME.css` and `js/THEME.js`. Replace THEME with the name you would like to use. 

I suggest using one of the existing themes as a template to start with.

= Can I remove the "Powered by SimpleModal Login" link?  =

Yup. See below.

= How can I remove the "Powered by SimpleModal Login" link?  =

You can edit the `simplemodal-login.php` file, or more simply, just add the following to your `style.css` file:

	.simplemodal-login-credit {display:none;}


*Have a question, be sure to let me know.*

== Screenshots ==

1. Login screen with the default theme.
2. Login screen with the osx theme.
3. The SimpleModal Login admin settings page


== Changelog ==

= 0.3 =
* Added uninstall cleanup code
* Updated POT file
* Added dynamic theme support
* Renamed CSS and JS theme files (removed simplemodal-login- prefix)
* Removed unused code

= 0.2 =
* Updated POT file

= 0.1 =
* Initial beta release