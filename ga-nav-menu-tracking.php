<?php
/*
Plugin Name: Google Analytics Nav Menu Tracking
Plugin URI: http://secretstache.com
Description: Hide custom menu items based on user roles
Version: 0.1
Author: Paul de Wouters
Author URI: http://secretstache.com
License: GPL2

    Copyright 2013  Secret Stache Media (email: paul@secretstache.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


// don't load directly
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


if ( ! class_exists( "GA_Nav_Tracking" ) ) :

	class GA_Nav_Tracking {

		function __construct() {

			define( 'SSM_GA_VERSION', '0.1' );
			// Include required files
			if ( is_admin() ) {
				$this->admin_includes();
			}

			// load the textdomain
			add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

			// switch the admin walker
			add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_nav_menu_walker' ), 10, 2 );

			// save the menu item meta
			add_action( 'wp_update_nav_menu_item', array( $this, 'nav_update' ), 10, 3 );

			// add meta to menu item
			add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_nav_item' ) );

			// exclude items via filter instead of via custom Walker
			if ( ! is_admin() ) {
				add_filter( 'walker_nav_menu_start_el', array( $this, 'add_tracking' ), 10, 4 );
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		}

		/**
		 * Include required admin files.
		 *
		 * @access public
		 * @return void
		 */
		function admin_includes() {
			/* include the custom admin walker */
			include_once( plugin_dir_path( __FILE__ ) . 'inc/class.Walker_Nav_Menu_Edit_Roles.php' );
		}

		/**
		 * Make Plugin Translation-ready
		 * CALLBACK FUNCTION FOR:  add_action( 'plugins_loaded', array( $this,'load_text_domain'));
		 * @since 1.0
		 */

		function load_text_domain() {
			load_plugin_textdomain( 'ga-nav-menu-tracking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Override the Admin Menu Walker
		 * @since 1.0
		 */
		function edit_nav_menu_walker( $walker, $menu_id ) {
			return 'Walker_Nav_Menu_Edit_Roles';
		}

		/**
		 * Save the roles as menu item meta
		 * @return string
		 * @since 1.0
		 */
		function nav_update( $menu_id, $menu_item_db_id, $args ) {
			// global $wp_roles;

			//$allowed_roles = apply_filters( 'nav_menu_roles', $wp_roles->role_names );

			// verify this came from our screen and with proper authorization.
			if ( ! isset( $_POST['ga-nav-menu-tracking-nonce'] ) || ! wp_verify_nonce( $_POST['ga-nav-menu-tracking-nonce'], 'nav-menu-nonce-name' ) )
				return;

			$saved_data = array();

			if ( isset( $_POST['menu-item-tracking-1'][$menu_item_db_id] ) ) {
				$saved_data[] = $_POST['menu-item-tracking-1'][$menu_item_db_id];
			}
			if ( isset( $_POST['menu-item-tracking-2'][$menu_item_db_id] ) ) {
				$saved_data[] = $_POST['menu-item-tracking-2'][$menu_item_db_id];
			}
			if ( isset( $_POST['menu-item-tracking-3'][$menu_item_db_id] ) ) {
				$saved_data[] = $_POST['menu-item-tracking-3'][$menu_item_db_id];
			}
			if ( isset( $_POST['menu-item-tracking-4'][$menu_item_db_id] ) ) {
				$saved_data[] = $_POST['menu-item-tracking-4'][$menu_item_db_id];
			}

			if ( ! empty( $saved_data ) ) {
				foreach ( $saved_data as $key => $item ) {
					update_post_meta( $menu_item_db_id, '_nav_menu_tracking_' . $key, $item );
				}
			}
			else {
				foreach ( $saved_data as $key => $item ) {
					if ( empty( $item ) )
						delete_post_meta( $menu_item_db_id, '_nav_menu_tracking_' . $key );
				}


			}
		}

		/**
		 * Adds value of new field to $item object
		 * is be passed to Walker_Nav_Menu_Edit_Custom
		 * @since 1.0
		 */
		function setup_nav_item( $menu_item ) {

			$roles = get_post_meta( $menu_item->ID, '_nav_menu_role', true );

			if ( ! empty( $roles ) ) {
				$menu_item->roles = $roles;
			}
			return $menu_item;
		}

		/**
		 * Exclude menu items via wp_get_nav_menu_items filter
		 * this fixes plugin's incompatibility with theme's that use their own custom Walker
		 * Thanks to Evan Stein @vanpop http://vanpop.com/
		 * @since 1.2
		 */
		function add_tracking( $item_output, $item, $depth, $args ) {

			$tracking1 = get_post_meta( $item->ID, '_nav_menu_tracking_0', true );
			$tracking2 = get_post_meta( $item->ID, '_nav_menu_tracking_1', true );
			$tracking3 = get_post_meta( $item->ID, '_nav_menu_tracking_2', true );
			$tracking4 = get_post_meta( $item->ID, '_nav_menu_tracking_3', true );

			return $item_output
					. '<span style="display:none;" class="ga-tracking" '
					. 'data-tracking-1="' . $tracking1 . '"'
					. 'data-tracking-2="' . $tracking2 . '"'
					. 'data-tracking-3="' . $tracking3 . '"'
					. 'data-tracking-4="' . $tracking4 . '"'
					. '>'
					. '</span>';
		}

		function register_scripts() {
			wp_register_script( 'ga-tracking', plugins_url( '/js/plugin.js', __FILE__ ), array( 'jquery' ), SSM_GA_VERSION, true );
			wp_enqueue_script( 'ga-tracking' );
		}


	} // end class

endif; // class_exists check


/**
 * Launch the whole plugin
 */
global $GA_Nav_Tracking;
$GA_Nav_Tracking = new GA_Nav_Tracking();
