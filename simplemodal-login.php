<?php
/*
Plugin Name: SimpleModal Login
Plugin URI: http://www.ericmmartin.com/projects/simplemodal-login/
Description: A modal Ajax login for WordPress which utilizes jQuery and the SimpleModal jQuery plugin.
Author: Eric Martin
Version: 0.1
Author URI: http://www.ericmmartin.com
Revision: $Id$
*/

/*  Copyright 2009 Eric Martin (eric@ericmmartin.com)

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
		var $version = '0.1';

		/**
		 * @var string The options string name for this plugin
		 */
		var $optionsName = 'simplemodal_login_options';

		/**
		 * @var string $localizationDomain Domain used for localization
		 */
		var $localizationDomain = 'simplemodal_login';

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
		 * PHP 4 Compatible Constructor
		 */
		function SimpleModalLogin() {$this->__construct();}

		/**
		 * PHP 5 Constructor
		 */
		function __construct() {
			$name = dirname(plugin_basename(__FILE__));

			//Language Setup
			load_plugin_textdomain($this->localizationDomain, false, "$name/I18n/");

			//"Constants" setup
			$this->pluginurl = WP_PLUGIN_URL . "/$name/";
			$this->pluginpath = WP_PLUGIN_DIR . "/$name/";

			//Initialize the options
			$this->get_options();

			//Actions
			add_action('admin_menu', array(&$this, 'admin_menu_link'));
			
			if (!is_admin()) {
				add_filter('loginout', array(&$this, 'simplemodal_login_loginout'));
				add_action('wp_footer', array($this, 'simplemodal_login_footer'));
				add_action('wp_print_styles', array(&$this, 'simplemodal_login_css'));
				add_action('wp_print_scripts', array(&$this, 'simplemodal_login_js'));
			}
		}

		/**
		 * Pagination based on options/args
		 */
		function paginate($args = false) {
			
		}

		function simplemodal_login_css() {
			$style = sprintf("login-%s.css", $this->options['theme']);
			wp_enqueue_style('simplemodal-login', $this->pluginurl . "css/simplemodal-" . $style, false, $this->version, 'screen');
			if (false !== @file_exists(TEMPLATEPATH . $style)) {
				wp_enqueue_style('simplemodal-login-form', get_template_directory_uri() . $style, false, $this->version, 'screen');
			}
		}

		function simplemodal_login_js() {
			wp_enqueue_script("jquery-simplemodal", $this->pluginurl . "js/jquery.simplemodal.js", "jquery", "1.3.3", true);
			
			$script = sprintf("js/simplemodal-login-%s.js", $this->options['theme']);
			wp_enqueue_script("simplemodal-login", $this->pluginurl . $script, null, $this->version, true);
			wp_localize_script('simplemodal-login', 'SimpleModalLoginL10n', array(
				'empty_username' => __('<strong>ERROR</strong>: The username field is empty.', $this->localizationDomain),
				'empty_password' => __('<strong>ERROR</strong>: The password field is empty.', $this->localizationDomain),
				'empty_both' => __('<strong>ERROR</strong>: Both fields are required.', $this->localizationDomain)
			));
			
		}
		
		function simplemodal_login_loginout($link) {
			if (!is_user_logged_in()) {
				$link = str_replace('href=', 'class="simplemodal-login" href=', $link);
			}
			return $link;
		}
		
		function simplemodal_login_footer() {
			printf('<div id="simplemodal-login-form" style="display:none;">
	%s
	<form name="loginform" id="loginform" action="%s" method="post">
		<p>
			<label>%s<br />
			<input type="text" name="log" id="user_login" class="input" value="" size="20" tabindex="10" /></label>
		</p>
		<p>
			<label>%s<br />
			<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
		</p>', $this->options['theme'] === 'osx' ? '<div class="osx-title">Login</div>' : '', site_url('wp-login.php', 'login_post'), __('Username', $this->localizationDomain), __('Password', $this->localizationDomain));
				
		do_action('login_form');
		
		printf('
		<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> %s</label></p>
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" value="%s" tabindex="100" />
			<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
			<input type="hidden" id="redirect_to" name="redirect_to" value="%s" />
			<input type="hidden" name="testcookie" value="1" />
		</p>		
	</form>
	<div class="simplemodal-login-credit"><a href="http://www.ericmmartin.com/projects/simplemodal-login/">%s</a></div>
</div>', esc_attr__('Remember Me', $this->localizationDomain), esc_attr__('Log In', $this->localizationDomain), esc_attr__('Cancel', $this->localizationDomain), $this->options['admin'] === true ? admin_url() : '', __('Powered by', $this->localizationDomain) . " SimpleModal Login");

		}

		/**
		 * Retrieves the plugin options from the database.
		 * @return array
		 */
		function get_options() {
			if (!$options = get_option($this->optionsName)) {
				$options = array(
					'admin' => true,
					'theme' => 'default'
				);
				update_option($this->optionsName, $options);
			}
			$this->options = $options;
		}
		/**
		 * Saves the admin options to the database.
		 */
		function save_admin_options(){
			return update_option($this->optionsName, $this->options);
		}

		/**
		 * @desc Adds the options subpanel
		 */
		function admin_menu_link() {
			add_options_page('SimpleModal Login', 'SimpleModal Login', 10, basename(__FILE__), array(&$this, 'admin_options_page'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
		}

		/**
		 * @desc Adds the Settings link to the plugin activate/deactivate page
		 */
		function filter_plugin_actions($links, $file) {
			$settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings', $this->localizationDomain) . '</a>';
			array_unshift($links, $settings_link); // before other links

			return $links;
		}

		/**
		 * Adds settings/options page
		 */
		function admin_options_page() {
			if (isset($_POST['simplemodal_login_save'])) {
				if (wp_verify_nonce($_POST['_wpnonce'], 'simplemodal-login-update-options')) {
					$this->options['admin'] = (isset($_POST['admin']) && $_POST['admin'] === 'on') ? true : false;
					$this->options['theme'] = $_POST['theme'];
	
					$this->save_admin_options();
	
					echo '<div class="updated"><p>' . __('Success! Your changes were successfully saved!', $this->localizationDomain) . '</p></div>';
				}
				else {
					echo '<div class="error"><p>' . __('Whoops! There was a problem with the data you posted. Please try again.', $this->localizationDomain) . '</p></div>';
				}
			}
?>

<div class="wrap">
<div class="icon32" id="icon-options-general"><br/></div>
<h2>SimpleModal Login</h2>
<form method="post" id="simplemodal_login_options">
<?php wp_nonce_field('simplemodal-login-update-options'); ?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Redirect to Admin?:', $this->localizationDomain); ?></th>
			<td><label for="admin">
				<input type="checkbox" id="admin" name="admin" <?php echo ($this->options['admin'] === true) ? "checked='checked'" : ""; ?>/> <?php _e('Select this option to redirect to the admin page after login', $this->localizationDomain); ?></label></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Theme:', $this->localizationDomain); ?></th>
			<td>
				<select name="theme" id="theme">
				<?php foreach (array('default', 'osx') as $theme) : ?>
					<option value="<?php echo $theme; ?>" <?php echo ($theme == $this->options['theme']) ? "selected='selected'" : ""; ?>><?php echo $theme; ?></option>
				<?php endforeach; ?>
				</select>
				<span class="description"><?php _e('The theme to use.', $this->localizationDomain); ?></span></td>
		</tr>
	</table>
	<p class="submit">
		<input type="submit" value="Save Changes" name="simplemodal_login_save" class="button-primary" />
	</p>
</form>
<h2><?php _e('Need Support?', $this->localizationDomain); ?></h2>
<p><?php printf(__('For questions, issues or feature requests, please post them in the %s and make sure to tag the post with simplemodal-login.', $this->localizationDomain), '<a href="http://wordpress.org/tags/simplemodal-login?forum_id=10#postform">WordPress Forum</a>'); ?></p>
<h2><?php _e('Like To Contribute?', $this->localizationDomain); ?></h2>
<p><?php _e('If you would like to contribute, the following is a list of ways you can help:', $this->localizationDomain); ?></p>
<ul>
	<li>&raquo; <?php _e('Translate SimpleModal Login into your language', $this->localizationDomain); ?></li>
	<li>&raquo; <?php _e('Blog about or link to SimpleModal Login so others can find out about it', $this->localizationDomain); ?></li>
	<li>&raquo; <?php _e('Report issues, provide feedback, request features, etc.', $this->localizationDomain); ?></li>
	<li>&raquo; <a href="http://wordpress.org/extend/plugins/simplemodal-login/"><?php _e('Rate SimpleModal Login on the WordPress Plugins Page', $this->localizationDomain); ?></a></li>
	<li>&raquo; <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=KUL9VQ6U5VYCE&lc=US&item_name=Eric%20Martin%20%28ericmmartin%2ecom%29&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted"><?php _e('Make a donation', $this->localizationDomain); ?></a></li>
</ul>
<h2><?php _e('Other Links', $this->localizationDomain); ?></h2>
<ul>
	<li>&raquo; <a href="http://twitter.com/ericmmartin">@ericmmartin</a> on Twitter</li>
	<li>&raquo; <a href="http://www.ericmmartin.com">EricMMartin.com</a></li>
	<li>&raquo; <a href="http://www.ericmmartin.com/projects/smcf/">SimpleModal Contact Form (SMCF)</a> - an Ajax powered modal contact form built on jQuery and SimpleModal</li>
	<li>&raquo; <a href="http://www.ericmmartin.com/projects/wp-paginate/">WP-Paginate</a> - a simple and flexible pagination plugin for posts and comments</li>
</ul>
</div>

<?php
		}
	}
}

//instantiate the class
if (class_exists('SimpleModalLogin')) {
	$simplemodal_login = new SimpleModalLogin();
}

/**
 * Pagination function to use for posts
 */
function simplemodal_login($args = false) {
	global $simplemodal_login;
	return $simplemodal_login->paginate($args);
}

/*
 * The format of this plugin is based on the following plugin template: 
 * http://pressography.com/plugins/wordpress-plugin-template/
 */
?>