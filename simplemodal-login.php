<?php
/*
Plugin Name: SimpleModal Login
Plugin URI: http://www.ericmmartin.com/projects/simplemodal-login/
Description: A modal Ajax login for WordPress which utilizes jQuery and the SimpleModal jQuery plugin.
Version: 1.0
Author: Eric Martin
Author URI: http://www.ericmmartin.com
Revision: $Id$
*/

/*  Copyright 2010 Eric Martin (eric@ericmmartin.com)

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
		var $version = '1.0';

		/**
		 * @var string The options string name for this plugin
		 */
		var $optionsName = 'simplemodal_login_options';

		/**
		 * @var string $localizationDomain Domain used for localization
		 */
		var $localizationDomain = 'simplemodal_login';

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
				add_filter('login_redirect', array(&$this, 'login_redirect'), 5, 3);
				add_filter('register', array(&$this, 'register'));
				add_filter('loginout', array(&$this, 'login_loginout'));
				add_action('wp_footer', array($this, 'login_footer'));
				add_action('wp_print_styles', array(&$this, 'login_css'));
				add_action('wp_print_scripts', array(&$this, 'login_js'));
			}
		}

		function check_options() {
			$options = null;
			if (!$options = get_option($this->optionsName)) {
				// default options for a clean install
				$options = array(
					'theme' => 'default',
					'version' => $this->version
				);
				update_option($this->optionsName, $options);
			}
			else {
				// check for upgrades
				if (isset($options['version'])) {
					if ($options['version'] < $this->version) {
						// upgrade logic goes here
					}
				}
				else {
					// pre v1.0 updates
					if (isset($options['admin'])) {
						unset($options['admin']);
						$options['version'] = $this->version;
						update_option($this->optionsName, $options);
					}
				}
			}
			
			return $options;
		}

		function is_plugin_active($plugin) {
			return in_array($plugin, (array) get_option('active_plugins', array()));
		}

		function login_css() {
			$style = sprintf("%s.css", $this->options['theme']);
			wp_enqueue_style('simplemodal-login', $this->pluginurl . "css/$style", false, $this->version, 'screen');
			if (false !== @file_exists(TEMPLATEPATH . "simplemodal-login-$style")) {
				wp_enqueue_style('simplemodal-login-form', get_template_directory_uri() . $style, false, $this->version, 'screen');
			}
		}

		function login_footer() {
			$can_register = get_option('users_can_register');
			printf('<div id="simplemodal-login-form">
	<form name="loginform" id="loginform" action="%s" method="post" style="display:none;">
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
				__('Login', $this->localizationDomain),
				__('Username', $this->localizationDomain),
				__('Password', $this->localizationDomain)
			);
				
			do_action('login_form');
			
			printf('
		<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> %s</label></p>
		<p class="submit">
			<input type="submit" name="wp-submit" value="%s" tabindex="100" />
			<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
			<input type="hidden" name="testcookie" value="1" />
		</p>
		<p class="nav">', 
				__('Remember Me', $this->localizationDomain), 
				__('Log In', $this->localizationDomain), 
				__('Cancel', $this->localizationDomain)
			);

			if ($can_register) {
				printf('<a class="simplemodal-register" href="%s">%s</a> | ', site_url('wp-login.php?action=register', 'login'), __('Register', $this->localizationDomain));
			}
			
			printf('
		<a class="simplemodal-forgotpw" href="%s" title="%s">%s</a>
		</p>
		</div>
		<div class="simplemodal-login-activity" style="display:none;"></div>
	</form>', 
				site_url('wp-login.php?action=lostpassword', 'login'),
				__('Password Lost and Found', $this->localizationDomain),
				__('Lost your password?', $this->localizationDomain)
			);
	
			if ($can_register) {
				printf('
	<form name="registerform" id="registerform" action="%s" method="post" style="display:none;">
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
					__('Register', $this->localizationDomain),
					__('Username', $this->localizationDomain),
					__('E-mail', $this->localizationDomain)
				);
		
				do_action('register_form');
				
				printf('
		<p class="reg_passmail">%s</p>
		<p class="submit">
			<input type="submit" name="wp-submit" value="%s" tabindex="100" />
			<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
		</p>
		<p class="nav">
			<a class="simplemodal-login" href="%s">%s</a> | <a class="simplemodal-forgotpw" href="%s" title="%s">%s</a>
		</p>
		</div>
		<div class="simplemodal-login-activity" style="display:none;"></div>
	</form>', 
					__('A password will be e-mailed to you.', $this->localizationDomain),
					__('Register', $this->localizationDomain), 
					__('Cancel', $this->localizationDomain),
					site_url('wp-login.php', 'login'), 
					__('Log in', $this->localizationDomain),
					site_url('wp-login.php?action=lostpassword', 'login'),
					__('Password Lost and Found', $this->localizationDomain),
					__('Lost your password?', $this->localizationDomain)
				);
			}
			
			printf('
	<form name="lostpasswordform" id="lostpasswordform" action="%s" method="post" style="display:none;">
		<div class="title">%s</div>
		<div class="simplemodal-login-fields">
		<p>
			<label>%s<br />
			<input type="text" name="user_login" class="user_login input" value="" size="20" tabindex="10" /></label>
		</p>',
				site_url('wp-login.php?action=lostpassword', 'login_post'),
				__('Reset Password', $this->localizationDomain),
				__('Username or E-mail:', $this->localizationDomain)
			);		

			do_action('lostpassword_form');
			
			printf('
		<p class="submit">
			<input type="submit" name="wp-submit" value="%s" tabindex="100" />
			<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
		</p>
		<p class="nav">
			<a class="simplemodal-login" href="%s">%s</a>',
				__('Get New Password', $this->localizationDomain),
				__('Cancel', $this->localizationDomain),
				site_url('wp-login.php', 'login'),
				__('Log in', $this->localizationDomain)
			);
			
			if ($can_register) {
				printf('| <a class="simplemodal-register" href="%s">%s</a>', site_url('wp-login.php?action=register', 'login'), __('Register', $this->localizationDomain));
			}
			
			printf('
		</p>
		</div>
		<div class="simplemodal-login-activity" style="display:none;"></div>
	</form>
	<div class="simplemodal-login-credit"><a href="http://www.ericmmartin.com/projects/simplemodal-login/">%s</a></div>
</div>',
				__('Powered by', $this->localizationDomain) . " SimpleModal Login"
			);
		}

		function login_js() {
			wp_enqueue_script("jquery-simplemodal", $this->pluginurl . "js/jquery.simplemodal.js", array("jquery"), "1.3.3", true);
			
			$script = sprintf("js/%s.js", $this->options['theme']);
			wp_enqueue_script("simplemodal-login", $this->pluginurl . $script, null, $this->version, true);
			wp_localize_script('simplemodal-login', 'SimpleModalLoginL10n', array(
				'empty_username' => __('<strong>ERROR</strong>: The username field is empty.', $this->localizationDomain),
				'empty_password' => __('<strong>ERROR</strong>: The password field is empty.', $this->localizationDomain),
				'empty_email' => __('<strong>ERROR</strong>: The email field is empty.', $this->localizationDomain),
				'empty_all' => __('<strong>ERROR</strong>: All fields are required.', $this->localizationDomain)
			));
			
		}
		
		function login_loginout($link) {
			if (!is_user_logged_in()) {
				$link = str_replace('href=', 'class="simplemodal-login" href=', $link);
			}
			return $link;
		}

		function login_redirect($redirect_to, $req_redirect_to, $user) {
		    if (!isset($user->user_login)) {
				return $redirect_to;
		    }
		    if ($this->is_plugin_active('peters-login-redirect/wplogin_redirect.php') 
		    		&& function_exists('redirect_to_front_page')) {
		    	$redirect_to = redirect_to_front_page($redirect_to, $req_redirect_to, $user);
		    }
			echo "<div id='simplemodal-login-redirect'>$redirect_to</div>";
			exit();
		}

		function register($link) {
			if (!is_user_logged_in()) {
				$link = str_replace('href=', 'class="simplemodal-register" href=', $link);
			}
			return $link;
		}

		/**
		 * Retrieves the plugin options from the database.
		 * @return array
		 */
		function get_options() {
			$options = $this->check_options();
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
				check_admin_referer($this->nonce);
				
				$this->options['theme'] = $_POST['theme'];

				$this->save_admin_options();

				echo '<div class="updated"><p>' . __('Success! Your changes were successfully saved!', $this->localizationDomain) . '</p></div>';
			}
?>

<div class="wrap">
<div class="icon32" id="icon-options-general"><br/></div>
<h2>SimpleModal Login <span style='font-size:60%;'>v<?php echo $this->version; ?></span></h2>
<form method="post" id="simplemodal_login_options">
<?php wp_nonce_field($this->nonce); ?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Theme:', $this->localizationDomain); ?></th>
			<td>
				<select name="theme" id="theme">
				<?php foreach (glob($this->pluginpath . "css/*.css") as $cssfile) : 
					$cssfile = basename($cssfile);
					$theme = str_replace('.css', '', $cssfile);
					
					if (false === @file_exists($this->pluginpath . "js/{$theme}.js")) {
						continue;
					}
				?>
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
<h2><?php _e('Themes', $this->localizationDomain); ?></h2>
<p><?php _e('SimpleModal Login allows you to create your own themes.', $this->localizationDomain); ?></p>
<p><?php _e('To create a new theme you\'ll need to add two files under the <code>simplemodal-login</code> plugin directory: <code>css/THEME.css</code> and <code>js/THEME.js</code>. Replace THEME with the name you would like to use. I suggest using one of the existing themes as a template.', $this->localizationDomain); ?></p>
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

/*
 * The format of this plugin is based on the following plugin template: 
 * http://pressography.com/plugins/wordpress-plugin-template/
 */
?>