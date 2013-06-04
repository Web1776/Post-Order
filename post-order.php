<?php /*
Plugin Name: 	Post Order
Description: 	Drag and drop your [custom] Posts into perfect order.
Version: 		0.0.1
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
		$cpts = get_post_types();
		$capability = 'manage_options';

		foreach($cpts as $cpt){
			add_submenu_page('edit.php?post_type=' . $cpt, __('Order'), __('Order'), $capability, $cpt . '-post-order', array(&$this, 'order_page'));
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
							echo get_post_meta($post->ID, 'menu_order', true);
							echo '<li class="menu-item menu-item-bar">',
								'<div class="menu-item-handle"><span class="item-title">', $post->post_title, '</span>',
									//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									// Post information
									//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									'<input type="hidden" name="post-id[]" value="',$post->ID,'">',
									'<input type="hidden" name="post-parent[]" value="',$post->post_parent,'">',
								'</div>',
								'<ul class="menu-item-transport"></ul>',
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
	static function save(){
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
			foreach($_POST['post-id'] as $order=>$postID){
				$post = array(
					'ID'			=> $postID,
					'menu_order'	=> $order
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