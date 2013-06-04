/*!================================================================================
Plugin Name: 	Post Order
Description: 	Drag and drop your [custom] Posts into perfect order.
Version: 		0.0.1
Author: 		CommAREus
Author URI: 	http://commareus.com
================================================================================*/

/*!
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
jQuery(function($){
	$('#post-order-of-oz-listing ul').sortable({
		placeholder: 	'sortable-placeholder',
		connectWith: 	'#post-order-of-oz-listing ul',
	    tolerance: 		'intersect',
	});
	$('#post-order-of-oz-listing ul').disableSelection();
});