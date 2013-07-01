# Post Order

**Version**: 1.0.0  
**Requires at least**: ?  
**Tested up to**: 3.5.1  
**License**: GPLv2  

**Contributors**:  
* Oz Ramos ( [CommAREus](http://commareus.com) )

##Description
Drag and drop your WordPress [custom] Posts into perfect order.

##Features
* Order Pages and Custom Post Types (CPT)
* Drag-n-drop interface similar to WordPress' built in Menu system.
* Separate "Order" Settings page for each CPT for faster, clutter free control 

##Installation
1. Place the `post-order` folder in your `/wp-content/plugins/` directory
2. Activate Post Order
3. Visit `Settings > Post Order` to blacklist post types from Post Order.

##Usage
"Post Order" works by changing the `menu_order` property of each post, which can normally be modified through the "Page Attributes" panel in the post editor. This means that once the `menu_order` has been set, you may remove the plugin without losing your post's order.

The plugin automatically adds an `Order` menu to each Post Type it finds, to include `Pages` (but excluding `Posts`). All you need to do is set `'orderby' => 'menu_order'` for [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query) (and by extension, [get_posts](http://codex.wordpress.org/Template_Tags/get_posts)).

##To Do
* Add page search (for long listings)
* Add "Force Order" for people who would rather not touch the code