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
	var $sortable 	= $('#post-order-of-oz-listing ul');
	var $form 		= $('#post-order-of-oz-listing');
	var $posts 		= $('.post-item', $form);

	//=============================================================================
	// Position Elements on load
	//=============================================================================
	$posts.each(function(){
		var $this 		= $(this);
		var targetID 	= $('> .post-item-handle > .post-parent', $this).val();

		if(targetID != 0){
			var $target = $('input.post-id[value="' + targetID + '"]');
			$this.appendTo($target.parent().parent().children('ul'));
		}
	})

	//=============================================================================
	// Update children count
	//=============================================================================
	var update_post_count = function(){
    	$posts.each(function(){
    		var $this = $(this);
    		var $counter = $('> .post-item-handle > .item-title > .post-count', $this);
    		var count = $('> ul > li', $this).length;

    		if(count){
    			$counter.text(' (' + count + ')');
    			$('> .post-item-handle > .item-title > .tree-toggle', $this).show();
    		} else {
    			$counter.text('');
    			$('> .post-item-handle > .item-title > .tree-toggle', $this).hide();
    		}
    	});
    };

	//=============================================================================
	// Initialize the sortables
	//=============================================================================
	$sortable.disableSelection();
	$sortable.sortable({
		placeholder: 	'post-order-of-oz-listing-placeholder',
		connectWith: 	postOrder.hierarchical ? '#post-order-of-oz-listing ul' : '#post-order-of-oz-listing > ul > li',
	    tolerance: 		'intersect',

	    //- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Update the post count
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	    update: update_post_count
	});
	update_post_count();

	//=============================================================================
	// Update post parents
	//=============================================================================
	$form.submit(function(e){
		$posts.each(function(){
			var $this 		= $(this);
			var parentID 	= $this.parent().parent().find('> .post-item-handle > .post-id').val();
			$('> .post-item-handle > .post-parent', $this).val(parentID);
		});
	});

	//=============================================================================
	// Toggle the tree open/close
	//=============================================================================
	$('.tree-toggle').click(function(){
		var $this = $(this);
		$this.toggleClass('opened');
		$this.closest('.post-item').children('ul').slideToggle();
	})
});