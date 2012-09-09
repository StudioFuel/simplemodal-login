<?php
/*
Plugin Name: SimpleModal Login
Plugin URI: http://www.ericmmartin.com/projects/simplemodal-login/
Description: A modal Ajax login, registration, and password reset feature for WordPress which utilizes jQuery and the SimpleModal jQuery plugin.
Version: 1.0.7
Author: Eric Martin
Author URI: http://www.ericmmartin.com
*/

/*  Copyright 2012 Eric Martin (eric@ericmmartin.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

/**
 * Set the wp-content and plugin urls/paths
 */
if (!defined('WP_CONTENT_URL'))
	define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
	define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_PLUGIN_URL') )
	define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
if (!defined('WP_PLUGIN_DIR') )
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

if (!class_exists('SimpleModalLogin')) {
	class SimpleModalLogin {
		/**
		 * @var string The plugin version
		 */
		var $version = '1.0.7';

		/**
		 * @var string The plugin version
		 */
		var $simplemodalVersion = '1.4.3';

		/**
		 * @var string The options string name for this plugin
		 */
		var $optionsName = 'simplemodal_login_options';

		/**
		 * @var string $nonce String used for nonce security
		 */
		var $nonce = 'simplemodal-login-update-options';

		/**
		 * @var string $pluginurl The url to this plugin
		 */
		var $pluginurl = '';

		/**
		 * @var string $pluginpath The path to this plugin
		 */
		var $pluginpath = '';

		/**
		 * @var array $options Stores the options for this plugin
		 */
		var $options = array();

		/**
		 * @var boolean $users_can_register Stores the option for this plugin
		 */
		var $users_can_register = null;

		/**
		 * PHP 4 Compatible Constructor
		 */
		function SimpleModalLogin() {$this->__construct();}

		/**
		 * PHP 5 Constructor
		 */
		function __construct() {
			$name = dirname(plugin_basename(__FILE__));

			//Language Setup
			load_plugin_textdomain('simplemodal-login', false, "$name/I18n/");

			//"Constants" setup
			$this->pluginurl = WP_PLUGIN_URL . "/$name/";
			$this->pluginpath = WP_PLUGIN_DIR . "/$name/";

			//Initialize the options
			$this->get_options();

			//Actions
			add_action('admin_menu', array(&$this, 'admin_menu_link'));

			if (!is_admin()) {
				add_filter('login_redirect', array(&$this, 'login_redirect'), 5, 3);
				add_filter('register', array(&$this, 'register'));
				add_filter('loginout', array(&$this, 'login_loginout'));
				add_action('wp_footer', array($this, 'login_footer'));
				add_action('wp_print_styles', array(&$this, 'login_css'));
				add_action('wp_print_scripts', array(&$this, 'login_js'));
			}
		}

		/**
		 * @desc Adds the options subpanel
		 */
		function admin_menu_link() {
			add_options_page('SimpleModal Login', 'SimpleModal Login', 'manage_options', basename(__FILE__), array(&$this, 'admin_options_page'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
		}

		/**
		 * @desc Adds settings/options page
		 */
		function admin_options_page() {
			if (isset($_POST['simplemodal_login_save'])) {
				check_admin_referer($this->nonce);

				$this->options['theme'] = $_POST['theme'];
				$this->options['shortcut'] = (isset($_POST['shortcut']) && $_POST['shortcut'] === 'on') ? true : false;
				$this->options['registration'] = (isset($_POST['registration']) && $_POST['registration'] === 'on') ? true : false;
				$this->options['reset'] = (isset($_POST['reset']) && $_POST['reset'] === 'on') ? true : false;

				$this->save_admin_options();

				echo '<div class="updated"><p>' . __('Success! Your changes were successfully saved!', 'simplemodal-login') . '</p></div>';
			}
?>

<div class='wrap'>
<div class='icon32' id='icon-options-general'><br/></div>
<h2>SimpleModal Login <span style='font-size:60%;'>v<?php echo $this->version; ?></span></h2>
<form method='post' id='simplemodal_login_options'>
<?php wp_nonce_field($this->nonce); ?>
	<table class='form-table'>
		<tr valign='top'>
			<th scope='row'><?php _e('Theme:', 'simplemodal-login'); ?></th>
			<td>
				<select name='theme' id='theme'>
				<?php foreach (glob($this->pluginpath . 'css/*.css') as $cssfile) :
					$cssfile = basename($cssfile);
					$theme = str_replace('.css', '', $cssfile);

					if (false === @file_exists($this->pluginpath . "js/{$theme}.js")) {
						continue;
					}
				?>
					<option value='<?php echo $theme; ?>' <?php echo ($theme == $this->options['theme']) ? "selected='selected'" : ''; ?>><?php echo $theme; ?></option>
				<?php endforeach; ?>
				</select>
				<span class='description'><?php _e('The theme to use.', 'simplemodal-login'); ?></span></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('User Registration:', 'simplemodal-login'); ?></th>
			<td><label for="registration">
				<input type="checkbox" id="registration" name="registration" <?php echo ($this->options['registration'] === true) ? "checked='checked'" : ""; ?>/> <?php _e('Enable', 'simplemodal-login'); ?></label><br/>
				<span class='description'><?php _e('Select this option to enable the user registration feature in SimpleModal Login. This requires the "Anyone can register" Membership option to be selected on your WordPress General Settings page.', 'simplemodal-login'); ?></span>
				</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Password Reset:', 'simplemodal-login'); ?></th>
			<td><label for="reset">
				<input type="checkbox" id="reset" name="reset" <?php echo ($this->options['reset'] === true) ? "checked='checked'" : ""; ?>/> <?php _e('Enable', 'simplemodal-login'); ?></label><br/>
				<span class='description'><?php _e('Select this option to enable the password reset feature in SimpleModal Login.', 'simplemodal-login'); ?></span>
				</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Keystroke Shortcut:', 'simplemodal-login'); ?></th>
			<td><label for="shortcut">
				<input type="checkbox" id="shortcut" name="shortcut" <?php echo ($this->options['shortcut'] === true) ? "checked='checked'" : ""; ?>/> <?php _e('Enable', 'simplemodal-login'); ?></label><br/>
				<span class='description'><?php _e('Select this option to enable the keystroke shortcut of Ctrl+Alt+L to display the log in form. Allows you to invoke the log in form without displaying a "Log In" link.', 'simplemodal-login'); ?></span>
				</td>
		</tr>
	</table>
	<p class='submit'>
		<input type='submit' value='Save Changes' name='simplemodal_login_save' class='button-primary' />
	</p>
</form>
<h2><?php _e('Themes', 'simplemodal-login'); ?></h2>
<p><?php _e('SimpleModal Login allows you to create your own themes.', 'simplemodal-login'); ?></p>
<p><?php _e('To create a new theme you\'ll need to add two files under the <code>simplemodal-login</code> plugin directory: <code>css/THEME.css</code> and <code>js/THEME.js</code>. Replace THEME with the name you would like to use. I suggest using one of the existing themes as a template.', 'simplemodal-login'); ?></p>
<h2><?php _e('Need Support?', 'simplemodal-login'); ?></h2>
<p><?php printf(__('For questions, issues or feature requests, please post them in the %s and make sure to tag the post with simplemodal-login.', 'simplemodal-login'), '<a href="http://wordpress.org/tags/simplemodal-login?forum_id=10#postform">WordPress Forum</a>'); ?></p>
<h2><?php _e('Like To Contribute?', 'simplemodal-login'); ?></h2>
<p><?php _e('If you would like to contribute, the following is a list of ways you can help:', 'simplemodal-login'); ?></p>
<ul>
	<li>&raquo; <?php _e('Translate SimpleModal Login into your language', 'simplemodal-login'); ?></li>
	<li>&raquo; <?php _e('Blog about or link to SimpleModal Login so others can find out about it', 'simplemodal-login'); ?></li>
	<li>&raquo; <?php _e('Report issues, provide feedback, request features, etc.', 'simplemodal-login'); ?></li>
	<li>&raquo; <a href='http://wordpress.org/extend/plugins/simplemodal-login/'><?php _e('Rate SimpleModal Login on the WordPress Plugins Page', 'simplemodal-login'); ?></a></li>
	<li>&raquo; <a href='https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=KUL9VQ6U5VYCE&lc=US&item_name=Eric%20Martin%20%28ericmmartin%2ecom%29&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted'><?php _e('Make a donation', 'simplemodal-login'); ?></a></li>
</ul>
<h2><?php _e('Other Links', 'simplemodal-login'); ?></h2>
<ul>
	<li>&raquo; <a href='https://github.com/ericmmartin/simplemodal-login'>SimpleModal Login</a> on GitHub</li>
	<li>&raquo; <a href='http://twitter.com/ericmmartin'>@ericmmartin</a> on Twitter</li>
	<li>&raquo; <a href='http://www.ericmmartin.com'>ericmmartin.com</a></li>
	<li>&raquo; <a href='http://www.ericmmartin.com/projects/smcf/'>SimpleModal Contact Form (SMCF)</a> - an Ajax powered modal contact form built on jQuery and SimpleModal</li>
	<li>&raquo; <a href='http://www.ericmmartin.com/projects/wp-paginate/'>WP-Paginate</a> - a simple and flexible pagination plugin for posts and comments</li>
</ul>
</div>

<?php
		}

		/**
		 * @desc Loads the SimpleModal Login options. Responsible for
		 * handling upgrades and default option values.
		 * @return array
		 */
		function check_options() {
			$options = null;
			if (!$options = get_option($this->optionsName)) {
				// default options for a clean install
				$options = array(
					'shortcut' => true,
					'theme' => 'default',
					'version' => $this->version,
					'registration' => $this->users_can_register,
					'reset' => true
				);
				update_option($this->optionsName, $options);
			}
			else {
				// check for upgrades
				if (isset($options['version'])) {
					if ($options['version'] < $this->version) {
						// post v1.0 upgrade logic goes here
					}
				}
				else {
					// pre v1.0 updates
					if (isset($options['admin'])) {
						unset($options['admin']);
						$options['shortcut'] = true;
						$options['version'] = $this->version;
						$options['registration'] = $this->users_can_register;
						$options['reset'] = true;
						update_option($this->optionsName, $options);
					}
				}
			}

			return $options;
		}

		/**
		 * @desc Adds the Settings link to the plugin activate/deactivate page
		 * @return string
		 */
		function filter_plugin_actions($links, $file) {
			$settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings', 'simplemodal-login') . '</a>';
			array_unshift($links, $settings_link); // before other links

			return $links;
		}

		/**
		 * @desc Retrieves the plugin options from the database.
		 */
		function get_options() {
			$options = $this->check_options();
			$this->options = $options;
		}

		/**
		 * @desc Determines if request is an AJAX call
		 * @return boolean
		 */
		function is_ajax() {
			return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
					&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
		}

		/**
		 * @desc Checks to see if the given plugin is active.
		 * @return boolean
		 */
		function is_plugin_active($plugin) {
			return in_array($plugin, (array) get_option('active_plugins', array()));
		}

		/**
		 * @desc Enqueue's the CSS for the specified theme.
		 */
		function login_css() {
			$style = sprintf('%s.css', $this->options['theme']);
			wp_enqueue_style('simplemodal-login', $this->pluginurl . "css/$style", false, $this->version, 'screen');
			if (false !== @file_exists(TEMPLATEPATH . "simplemodal-login-$style")) {
				wp_enqueue_style('simplemodal-login-form', get_template_directory_uri() . $style, false, $this->version, 'screen');
			}
		}

		/**
		 * @desc Builds the login, registration, and password reset form HTML.
		 * Calls filters for each form, then echo's the output.
		 */
		function login_footer() {
			$output = '<div id="simplemodal-login-form" style="display:none">';

			$login_form = $this->login_form();
			$output .= apply_filters('simplemodal_login_form', $login_form);

			if ($this->users_can_register && $this->options['registration']) {
				$registration_form = $this->registration_form();
				$output .= apply_filters('simplemodal_registration_form', $registration_form);
			}

			if ($this->options['reset']) {
				$reset_form = $this->reset_form();
				$output .= apply_filters('simplemodal_reset_form', $reset_form);
			}
			$output .= '</div>';

			echo $output;
		}

		/**
		 * @desc Builds the login form HTML.
		 * If using the simplemodal_login_form filter, copy and modify this code
		 * into your function.
		 * @return string
		 */
		function login_form() {
			$output = sprintf('
	<form name="loginform" id="loginform" action="%s" method="post">
		<div class="title">%s</div>
		<div class="simplemodal-login-fields">
		<p>
			<label>%s<br />
			<input type="text" name="log" class="user_login input" value="" size="20" tabindex="10" /></label>
		</p>
		<p>
			<label>%s<br />
			<input type="password" name="pwd" class="user_pass input" value="" size="20" tabindex="20" /></label>
		</p>',
				site_url('wp-login.php', 'login_post'),
				__('Login', 'simplemodal-login'),
				__('Username', 'simplemodal-login'),
				__('Password', 'simplemodal-login')
			);

			ob_start();
			do_action('login_form');
			$output .= ob_get_clean();

			$output .= sprintf('
		<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" class="rememberme" value="forever" tabindex="90" /> %s</label></p>
		<p class="submit">
			<input type="submit" name="wp-submit" value="%s" tabindex="100" />
			<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
			<input type="hidden" name="testcookie" value="1" />
		</p>
		<p class="nav">',
				__('Remember Me', 'simplemodal-login'),
				__('Log In', 'simplemodal-login'),
				__('Cancel', 'simplemodal-login')
			);

			if ($this->users_can_register && $this->options['registration']) {
				$output .= sprintf('<a class="simplemodal-register" href="%s">%s</a>',
					site_url('wp-login.php?action=register', 'login'),
					__('Register', 'simplemodal-login')
				);
			}

			if (($this->users_can_register && $this->options['registration']) && $this->options['reset']) {
				$output .= ' | ';
			}

			if ($this->options['reset']) {
				$output .= sprintf('<a class="simplemodal-forgotpw" href="%s" title="%s">%s</a>',
					site_url('wp-login.php?action=lostpassword', 'login'),
					__('Password Lost and Found', 'simplemodal-login'),
					__('Lost your password?', 'simplemodal-login')
				);
			}

			$output .= '
			</p>
			</div>
			<div class="simplemodal-login-activity" style="display:none;"></div>
		</form>';

			return $output;
		}

		/**
		 * @desc Responsible for loading the necessary scripts and localizing JavaScript messages
		 */
		function login_js() {
			global $wp_scripts;

			if (isset($wp_scripts->registered['jquery-simplemodal'])
					&& version_compare($wp_scripts->registered['jquery-simplemodal']->ver, $this->simplemodalVersion) === -1) {
				wp_deregister_script('jquery-simplemodal'); // remove older versions
			}
			wp_enqueue_script('jquery-simplemodal', $this->pluginurl . 'js/jquery.simplemodal.js', array('jquery'), $this->simplemodalVersion, true);

			$script = sprintf('js/%s.js', $this->options['theme']);
			wp_enqueue_script('simplemodal-login', $this->pluginurl . $script, null, $this->version, true);
			wp_localize_script('simplemodal-login', 'SimpleModalLoginL10n', array(
				'shortcut' => $this->options['shortcut'] ? 'true' : 'false',
				'logged_in' => is_user_logged_in() ? 'true' : 'false',
				'admin_url' => get_admin_url(),
				'empty_username' => __('<strong>ERROR</strong>: The username field is empty.', 'simplemodal-login'),
				'empty_password' => __('<strong>ERROR</strong>: The password field is empty.', 'simplemodal-login'),
				'empty_email' => __('<strong>ERROR</strong>: The email field is empty.', 'simplemodal-login'),
				'empty_all' => __('<strong>ERROR</strong>: All fields are required.', 'simplemodal-login')
			));

		}

		/**
		 * @desc loginout filter that adds the simplemodal-login class to the "Log In" link
		 * @return string
		 */
		function login_loginout($link) {
			if (!is_user_logged_in()) {
				$link = str_replace('href=', 'class="simplemodal-login" href=', $link);
			}
			return $link;
		}

		/**
		 * @desc login_redirect filter that determines where to redirect the user.
		 * Supports Peter's Login Redirect plugin, if enabled.
		 * @return string
		 */
		function login_redirect($redirect_to, $req_redirect_to, $user) {
			if (!isset($user->user_login) || !$this->is_ajax()) {
				return $redirect_to;
			}
			if ($this->is_plugin_active('peters-login-redirect/wplogin_redirect.php')
					&& function_exists('redirect_to_front_page')) {
				$redirect_to = redirect_to_front_page($redirect_to, $req_redirect_to, $user);
			}
			echo "<div id='simplemodal-login-redirect'>$redirect_to</div>";
			exit();
		}

		/**
		 * @desc register filter that adds the simplemodal-register class to the "Register" link
		 * @return string
		 */
		function register($link) {
			if ($this->users_can_register && $this->options['registration']) {
				if (!is_user_logged_in()) {
					$link = str_replace('href=', 'class="simplemodal-register" href=', $link);
				}
			}
			return $link;
		}

		/**
		 * @desc Builds the registration form HTML.
		 * If using the simplemodal_registration_form filter, copy and modify this code
		 * into your function.
		 * @return string
		 */
		function registration_form() {
			$output = sprintf('
<form name="registerform" id="registerform" action="%s" method="post">
	<div class="title">%s</div>
	<div class="simplemodal-login-fields">
	<p>
		<label>%s<br />
		<input type="text" name="user_login" class="user_login input" value="" size="20" tabindex="10" /></label>
	</p>
	<p>
		<label>%s<br />
		<input type="text" name="user_email" class="user_email input" value="" size="25" tabindex="20" /></label>
	</p>',
				site_url('wp-login.php?action=register', 'login_post'),
				__('Register', 'simplemodal-login'),
				__('Username', 'simplemodal-login'),
				__('E-mail', 'simplemodal-login')
			);

			ob_start();
			do_action('register_form');
			$output .= ob_get_clean();

			$output .= sprintf('
	<p class="reg_passmail">%s</p>
	<p class="submit">
		<input type="submit" name="wp-submit" value="%s" tabindex="100" />
		<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
	</p>
	<p class="nav">
		<a class="simplemodal-login" href="%s">%s</a>',
				__('A password will be e-mailed to you.', 'simplemodal-login'),
				__('Register', 'simplemodal-login'),
				__('Cancel', 'simplemodal-login'),
				site_url('wp-login.php', 'login'),
				__('Log in', 'simplemodal-login')
			);

			if ($this->options['reset']) {
				$output .= sprintf(' | <a class="simplemodal-forgotpw" href="%s" title="%s">%s</a>',
					site_url('wp-login.php?action=lostpassword', 'login'),
					__('Password Lost and Found', 'simplemodal-login'),
					__('Lost your password?', 'simplemodal-login')
				);
			}

			$output .= '
	</p>
	</div>
	<div class="simplemodal-login-activity" style="display:none;"></div>
</form>';

			return $output;
		}

		/**
		 * @desc Builds the reset password form HTML.
		 * If using the simplemodal_reset_form filter, copy and modify this code
		 * into your function.
		 * @return string
		 */
		function reset_form() {
			$output = sprintf('
	<form name="lostpasswordform" id="lostpasswordform" action="%s" method="post">
		<div class="title">%s</div>
		<div class="simplemodal-login-fields">
		<p>
			<label>%s<br />
			<input type="text" name="user_login" class="user_login input" value="" size="20" tabindex="10" /></label>
		</p>',
				site_url('wp-login.php?action=lostpassword', 'login_post'),
				__('Reset Password', 'simplemodal-login'),
				__('Username or E-mail:', 'simplemodal-login')
			);

			ob_start();
			do_action('lostpassword_form');
			$output .= ob_get_clean();

			$output .= sprintf('
		<p class="submit">
			<input type="submit" name="wp-submit" value="%s" tabindex="100" />
			<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
		</p>
		<p class="nav">
			<a class="simplemodal-login" href="%s">%s</a>',
				__('Get New Password', 'simplemodal-login'),
				__('Cancel', 'simplemodal-login'),
				site_url('wp-login.php', 'login'),
				__('Log in', 'simplemodal-login')
			);

			if ($this->users_can_register && $this->options['registration']) {
				$output .= sprintf('| <a class="simplemodal-register" href="%s">%s</a>', site_url('wp-login.php?action=register', 'login'), __('Register', 'simplemodal-login'));
			}

			$output .= '
		</p>
		</div>
		<div class="simplemodal-login-activity" style="display:none;"></div>
	</form>';

			return $output;
		}

		/**
		 * Saves the admin options to the database.
		 */
		function save_admin_options(){
			return update_option($this->optionsName, $this->options);
		}
	}
}

// instantiate the class
if (class_exists('SimpleModalLogin')) {
	$simplemodal_login = new SimpleModalLogin();
	$simplemodal_login->users_can_register = get_option('users_can_register') ? true : false;
}

/*
 * The format of this plugin is based on the following plugin template:
 * http://pressography.com/plugins/wordpress-plugin-template/
 */
?>