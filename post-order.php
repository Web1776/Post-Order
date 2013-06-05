<?php /*
Plugin Name: 	Post Order
Description: 	Drag and drop your [custom] Posts into perfect order.
Version: 		1.0.0
Author: 		CommAREus
Author URI: 	http://commareus.com
*/

/**
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

//###########################################################################
// START TEST
//###########################################################################
add_action('init', 'oz_register_post_type');
function oz_register_post_type(){
	register_post_type('oz_cpt', array(
		'label'			=> 'Test CPT',
		'public'		=> true,
		'hierarchical' 	=> true,
		'supports'		=> array('page-attributes', 'editor', 'title')
	));
}
//###########################################################################
// END TEST
//###########################################################################




//###########################################################################
// Namespace our Plugin
//###########################################################################
$orbitScoreTable = new Post_Order_of_Oz();
class Post_Order_of_Oz{
	static protected $cpt;
	static protected $privelages;
	static protected $blacklist = array();

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	// Start this bad boy up
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	function __construct(){
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Define hooks
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		add_action('admin_menu', array(&$this, 'order_menu'));
		add_action('admin_init', array(&$this, 'post_info'));
	}

	//=============================================================================
	// Pull info about the current posts
	//=============================================================================
	function post_info(){		
		if(isset($_GET['page']) && substr($_GET['page'], -11) == '-post-order'){
			self::$cpt = get_post_type_object(str_replace( '-post-order', '', $_GET['page'] ));		
			if(!self::$cpt){
				wp_die(__('Sorry, but <b><code>'. esc_html($_GET['page']) .'</code></b> this is an invalid post type!'));
			}

			//=============================================================================
			// Enqueue Styles/Scripts
			//=============================================================================
			if(!defined('POST_ORDER_OF_OZ_URL')){
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				    define( 'POST_ORDER_OF_OZ_URL', trailingslashit( str_replace( DIRECTORY_SEPARATOR, '/', str_replace( str_replace( '/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR ), WP_CONTENT_URL, dirname(__FILE__) ) ) ) );
				} else {
				    define( 'POST_ORDER_OF_OZ_URL', apply_filters( 'cmb_meta_box_url', trailingslashit( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, dirname( __FILE__ ) ) ) ) );
				}
				add_action('admin_enqueue_scripts', array(&$this, 'scripts'));
			}
		}
	}


	//=============================================================================
	// Order menu items
	//=============================================================================
	function order_menu(){
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Get Blacklist
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		self::save_blacklist();
		self::$blacklist = get_option('post-order-of-oz');
		if(!is_array(self::$blacklist)) self::$blacklist = array();

		$cpts = get_post_types();
		$capability = 'manage_options';

		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Add the "Order" menu to each post type
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		foreach($cpts as $cpt){
			if(!in_array($cpt, self::$blacklist)) add_submenu_page('edit.php?post_type=' . $cpt, __('Order'), __('Order'), $capability, $cpt . '-post-order', array(&$this, 'order_page'));
		}

		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Add the global settings page
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		add_options_page(__('Post Order'), __('Post Order'), $capability, 'post-order', array(&$this, 'global_settings'));
	}

	//=============================================================================
	// The global settings page
	//=============================================================================
	function global_settings(){ ?>
		<?php //=======================================================================
			// Styles
			//==========================================================================
			if(!defined('POST_ORDER_OF_OZ_URL')){
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				    define( 'POST_ORDER_OF_OZ_URL', trailingslashit( str_replace( DIRECTORY_SEPARATOR, '/', str_replace( str_replace( '/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR ), WP_CONTENT_URL, dirname(__FILE__) ) ) ) );
				} else {
				    define( 'POST_ORDER_OF_OZ_URL', apply_filters( 'cmb_meta_box_url', trailingslashit( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, dirname( __FILE__ ) ) ) ) );
				}
			}
		?>
		<style>
			.wrap #icon-post-order-of-oz{
				background: url(<?php echo POST_ORDER_OF_OZ_URL ?>post-order.png);
			}
			#post-order-of-oz-settings td{
				padding: 4px 20px;
			}
		</style>


		<?php //=======================================================================
		// The page
		//========================================================================== ?>
		<div class="wrap post-order">
			<div class="icon32" id="icon-post-order-of-oz"><br></div>
			<h2>Post Order Settings</h2>
			<?php echo self::save_blacklist(); ?>
			<br>
			<h2>Usage</h2>

			<p>
				Post Order works by manually setting the "menu_order" property of each post within Pages and Custom Post Types. In order for Post Order to take effect, your theme must therefor <a href="http://codex.wordpress.org/Class_Reference/WP_Query">display posts</a> using <code>'orderby' => 'menu_order'</code>
			</p>
			<h2>Blacklist</h2>
			<p>
				Check off the Custom Post Types you <b>don't</b> want Post Order to display on. Note that users will still be able to manually change the "menu_order" through the "Page Attributes" metabox if the theme supports it.
			</p>
			<form id="post-order-of-oz-settings" method="post">
				<?php wp_nonce_field('save-blacklist', '_post-order-blacklist') ?>
				<table>
					<?php //=======================================================================
						// List of post types
						//========================================================================== 
						$cpts = get_post_types('', 'objects');
						$hardBlacklist = array('post', 'attachment', 'revision', 'nav_menu_item');

						foreach($cpts as $key=>$cpt){
							if(in_array($key, $hardBlacklist)) continue;
							echo '<tr>',
								'<td><label for="cpt-',$key,'">',$cpt->labels->name,'</label></td>',
								'<td><input id="cpt-',$key,'" type="checkbox" name="cpt[]" value="',$key,'" ',(in_array($key, self::$blacklist) ? 'checked' : ''),'></li></td>',
							'</tr>';
						}
					?>
				</table>
				<p><input type="submit" class="button button-primary" value="<?php _e('Update Blacklist'); ?> "></p>
			</form>
		</div><?php
	}

	//=============================================================================
	// Save the CPT blacklist
	//=============================================================================
	static private function save_blacklist(){
		if(isset($_POST['_post-order-blacklist'])){
			//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			// Security
			//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			if(!current_user_can('manage_options'))
				return '<div class="error"><br>'.__('Sorry, you don\'t have the required privileges to save').'<br><br></div>';

			if(!wp_verify_nonce(isset($_POST['_post-order-blacklist']), 'save-post-order'))
				return '<div class="error"><br>'.__('Incorrect credentials sent, please try again.').'<br><br></div>';

			//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			// Save
			//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			if(!isset($_POST['cpt'])){
				delete_option('post-order-of-oz');
				self::$blacklist = array();
			} else {
				update_option('post-order-of-oz', $_POST['cpt']);
				self::$blacklist = $_POST['cpt'];
			}

			return '<br><div class="updated"><br>' . __('Post orders updated!') . '<br><br></div>';
		}
	}

	//=============================================================================
	// The actual page for ordering
	//=============================================================================
	function order_page(){?>
		<div class="wrap post-order">
			<div class="icon32" id="icon-post-order-of-oz"><br></div>
			<h2><?php _e('Order: ' . self::$cpt->labels->singular_name); ?></h2>
			<br>
			<?php echo self::save(); ?>
			<p>
				Drag and drop your Custom Post Types order.
			</p>

			<form id="post-order-of-oz-listing" method="post">
				<input type="submit" class="button button-primary" value="<?php _e('Update Post Order'); ?> ">
				<?php wp_nonce_field('save-post-order', '_post-order') ?>

				<ul class="post-listing menu ui-sortable">
					<?php //=======================================================================
						// Display all CPT Posts
						//========================================================================== 
						//echo '<pre>', print_r(self::$cpt), '</pre>';
						$posts = get_posts(array(
							'post_type'	=> self::$cpt->name,
							'orderby'	=> 'menu_order',
							'order'		=> 'ASC'
						));
						foreach($posts as $post){
							echo '<li class="post-item">',
								'<div class="post-item-handle"><span class="item-title"><span class="tree-toggle opened"><span class="open">&#9660;</span><span class="close">&#9658;</span></span><b>', $post->post_title, '</b> <span class="post-count"></span></span>',
									//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									// Post information
									//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									'<input type="hidden" class="post-id" name="post-id[]" value="',$post->ID,'">',
									'<input type="hidden" class="post-parent" name="post-parent[]" value="', $post->post_parent ? $post->post_parent : '0','">',
								'</div>',
								'<ul class="post-item-transport"></ul>',
							'</li>';
						}
					?>
				</ul>
				<input type="submit" class="button button-primary" value="<?php _e('Update Post Order'); ?> ">
			</form>
		</div>
	<?php }

	//=============================================================================
	// Save the form
	//=============================================================================
	static private function save(){
		if(isset($_POST['post-id'])){
			//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			// Security
			//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			if(!current_user_can('manage_options'))
				return '<div class="error"><br>'.__('Sorry, you don\'t have the required privileges to save').'<br><br></div>';

			if(!isset($_POST['_post-order']) || !wp_verify_nonce(isset($_POST['_post-order']), 'save-post-order'))
				return '<div class="error"><br>'.__('Incorrect credentials sent, please try again.').'<br><br></div>';


			//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			// Update each post
			//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			foreach($_POST['post-id'] as $index=>$postID){
				$post = array(
					'ID'			=> $postID,
					'post_parent'	=> isset($_POST['post-parent'][$index]) ? $_POST['post-parent'][$index] : 0,
					'menu_order'	=> $index
				);
				wp_update_post($post);
			}

			return '<div class="updated"><br>' . __('Post orders updated!') . '<br><br></div>';
		}
	}

	//=============================================================================
	// Load our scripts
	//=============================================================================
	function scripts(){
		wp_enqueue_style('style-post-order-of-oz', POST_ORDER_OF_OZ_URL . 'style.css');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('script-post-order-of-oz', POST_ORDER_OF_OZ_URL . 'post-order.js');
	}
}