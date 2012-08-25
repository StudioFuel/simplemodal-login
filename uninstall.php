<?php
if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
	exit();

if (!class_exists('SimpleModalLogin'))
	require_once('simplemodal-login.php');

$simplemodal_login = new SimpleModalLogin();
delete_option($simplemodal_login->optionsName);

?>