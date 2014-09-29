<?php
/**
 * @package wp-excerpt-settings
 * @version 1.1.1
 */
/*
Plugin Name: WP Excerpt Settings
Plugin URI: http://wordpress.org/plugins/wp-excerpt-settings/
Description: Configure WordPress Excerpt through UI (User Interface).
Author: Yslo
Version: 1.1.1
Author URI: http://profiles.wordpress.org/yslo/
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WP_Excerpt_Settings
{
	// Define version
	const VERSION = '1.1.1';

	var $wp_excerpt_options;
	var $wp_excerpt_admin_page;

	function __construct()
	{
		$this->wp_excerpt_options = get_option('wp_excerpt_options');
		
		// Default install settings
		register_activation_hook(__FILE__, array(&$this, 'wp_excerpt_install'));
		
		// Languages
		load_plugin_textdomain('wp-excerpt-settings', false, 'wp-excerpt-settings/languages');

		add_filter('excerpt_more', array($this, 'wp_excerpt_more'));
		add_filter('excerpt_length', array(&$this, 'wp_excerpt_length'), 999);
		
		add_action('admin_init', array(&$this, 'wp_excerpt_settings_admin_init'));
	}

	
	function wp_excerpt_install(){	
		if($this->wp_excerpt_options === false)
		{
			$wp_excerpt_options = array(
				'excerpt_more' => '[...]',
				'excerpt_length' => 55,
				'version' => self::VERSION
			);
			
			update_option('wp_excerpt_options', $wp_excerpt_options);
		}
	}

	/**
	* the_excerpt function
	* http://codex.wordpress.org/Function_Reference/the_excerpt
	*/
	function wp_excerpt_more($more){
		return $this->wp_excerpt_options['excerpt_more'];
	}
	
	// http://codex.wordpress.org/Function_Reference/the_excerpt
	function wp_excerpt_length($length){
		return $this->wp_excerpt_options['excerpt_length'];
	}
	
	function add_action_link($links, $file)
	{
		static $this_plugin;
		
		if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
		
		if ($file == $this_plugin){
			$settings_link = '<a href="options-reading.php">' . __('Settings', 'wp-excerpt-settings') . '</a>';
			array_unshift($links, $settings_link);
		}
		
		return $links;
	}


	function wp_excerpt_settings_admin_init()
	{
		// Give the plugin a settings link in the plugin overview
		add_filter('plugin_action_links', array(&$this, 'add_action_link'), 10, 2);

		register_setting('reading', 'wp_excerpt_options', array(&$this, 'wp_excerpt_validate_options'));
		
		add_settings_section('wp_excerpt_options', __('Excerpt', 'wp-excerpt-settings'), array(&$this, 'wp_excerpt_options_section_text'), 'reading');
		add_settings_field('wp_excerpt_more', __('Excerpt text "More"', 'wp-excerpt-settings'),	array(&$this, 'wp_excerpt_more_input'), 'reading', 'wp_excerpt_options');
		add_settings_field('wp_excerpt_length', __('Excerpt words length', 'wp-excerpt-settings'),	array(&$this, 'wp_excerpt_length_input'), 'reading', 'wp_excerpt_options');

		add_action('load-options-reading.php', array(&$this, 'wp_excerpt_settings_add_help_tab'), 20);
	}


	function wp_excerpt_options_section_text(){
		_e('By default, core excerpt display "[...]" text at the end and refers to the first 55 words of the post\'s content.', 'wp-excerpt-settings');
	}

	
	function wp_excerpt_more_input()
	{
		$options = $this->wp_excerpt_options;
		$option_value = isset($options['excerpt_more']) ? $options['excerpt_more'] : '[...]';
		echo '<input name="wp_excerpt_options[excerpt_more]" type="text" value="'. $option_value .'">';
		echo '<p class="description">' . __('Default: [...]', 'wp-excerpt-settings') . '</p>';
	}


	function wp_excerpt_length_input()
	{
		$options = $this->wp_excerpt_options;
		$option_value = isset($options['excerpt_length']) ? $options['excerpt_length'] : 55;
		echo '<input name="wp_excerpt_options[excerpt_length]" type="text" value="'. $option_value .'">';
		echo '<p class="description">' . __('Default: 55, min: 5, max: 255', 'wp-excerpt-settings') . '</p>';
	}


	function wp_excerpt_settings_add_help_tab()
	{
		$screen = get_current_screen();

		if ($screen->id != 'options-reading')
			return;

		$screen->add_help_tab( array(
			'id'	=> 'wpus_help_excerpt_tab',
			'title'	=> __('Excerpt', 'wp-excerpt-settings'),
			'content'	=> '<p><ul><li>' . __('Displays the excerpt of the current post with the "[...]" text at the end. If you do not provide an explicit excerpt to a post (in the post editor\'s optional excerpt field), it will display an automatic excerpt which refers to the first 55 words of the post\'s content. (see <a href="http://codex.wordpress.org/Function_Reference/the_excerpt" target="_blank">Function Reference/the excerpt in Wordpress Codex</a>)', 'wp-excerpt-settings') . '</li>'
				. '</ul>'
				. '</p>',
		));
	}


	function wp_excerpt_validate_options($input)
	{
		$valid = array();
		
		$valid['excerpt_more'] = empty($input['excerpt_more']) ? '[...]' : sanitize_text_field($input['excerpt_more']);
		$valid['excerpt_length'] = (empty($input['excerpt_length']) || intval($input['excerpt_length']) == false || intval($input['excerpt_length']) < 5 || intval($input['excerpt_length']) > 255) ? 55 : intval($input['excerpt_length']);
		$valid['version'] = self::VERSION;
		
		return $valid;
	}
}

$wp_excerpt_settings = new WP_Excerpt_Settings();
