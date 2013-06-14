<?php
/*
Plugin Name: EDD Changelog
Plugin URI: http://www.connections-pro.com
Description: Adds a changelog meta box to the downloads post type.
Version: 1.0
Author: Steven A. Zahm
Author URI: http://www.connections-pro.com
License: GPL2
Text Domain: eddclog
Domain Path: /languages

Copyright 2013  Steven A. Zahm  (email : helpdesk@connections-pro.com)

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

if ( ! class_exists( 'EDD_Changelog' ) ) {

	class EDD_Changelog {

		/**
		* @var (object) Stores the instance of this class.
		*/
		private static $instance;

		/**
		 * A dummy constructor to prevent the class from being loaded more than once.
		 *
		 * @access private
		 * @since 1.0
		 * @see EDD_Changelog::instance()
		 * @see EDD_Changelog();
		 */
		private function __construct() { /* Do nothing here */ }

		/**
		 * Insures that only one instance of class exists in memory at any one time.
		 *
		 * @access public
		 * @since 1.0
		 * @return (object) EDD_Changelog
		 */
		public static function getInstance() {

			if ( ! isset( self::$instance ) ) {

				self::$instance = new self;
				self::$instance->init();
			}

			return self::$instance;
		}

		/**
		 * Initiate the plugin.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function init() {

			self::defineConstants();
			self::inludeDependencies();

			// Nothing to translate presently.
			// load_plugin_textdomain( 'eddclog' , FALSE , EDDCLOG_DIR_NAME . 'languages' );

			// If EDD Software Licensing plugin isn't installed,
			// then add the Changelog metabox.
			if ( ! class_exists( 'EDD_Software_Licensing' ) ) {

				add_action( 'add_meta_boxes', array( __CLASS__, 'edd_sl_add_license_meta_box' ), 100 );
				add_action( 'save_post', array( __CLASS__, 'edd_sl_download_meta_box_save' ) );

			}

			add_action( 'edd_meta_box_fields', array( __CLASS__, 'edd_render_disable_button' ), 40 );

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScripts' ) );
		}

		/**
		 * Define the constants.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private static function defineConstants() {

			define( 'EDDCLOG_VERSION', '1.0' );

			define( 'EDDCLOG_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'EDDCLOG_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'EDDCLOG_BASE_PATH', plugin_dir_path( __FILE__ ) );
			define( 'EDDCLOG_BASE_URL', plugin_dir_url( __FILE__ ) );

		}

		private static function inludeDependencies() {



		}

		/**
		 * Enqueue the CSS and JavaScripts.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		public static function enqueueScripts() {

		}

		/**
		 * Render Disable Button
		 *
		 * Lifted and adapted from EDD.
		 *
		 * @since 1.0
		 * @param int $post_id Download (Post) ID
		 * @return void
		 */
		public static function edd_render_disable_button( $post_id ) {

			$hide_button = get_post_meta( $post_id, '_edd_hide_changelog', TRUE ) ? TRUE : FALSE;

			echo '<p><strong>' . __( 'Change Log:', 'eddclog' ) . '</strong></p>';
			echo '<p>';
				echo '<label for="_edd_hide_changelog">';
					echo '<input type="checkbox" name="_edd_hide_changelog" id="_edd_hide_changelog" value="1"' . checked( TRUE, $hide_button ) . '/> ';
					_e( 'Disable the automatic output of the change log.', 'eddclog' );
				echo '</label>';
			echo '</p>';

		}

		/**
		 * Add License Meta Box
		 *
		 * Lifted from EDD Software Licensing for compatibility.
		 *
		 * @author Pippin Williamson
		 * @version 1.6
		 * @since 1.0
		 * @return void
		 */
		public static function edd_sl_add_license_meta_box() {

			global $post;

			if ( 'bundle' != edd_get_download_type( get_the_ID() ) )
				add_meta_box( 'edd_sl_box', __( 'Change Log', 'edd_sl' ), array( __CLASS__, 'edd_sl_render_licenses_meta_box'), 'download', 'side', 'core' );

		}


		/**
		 * Render the download information meta box
		 *
		 * Lifted from EDD Software Licensing for compatibility.
		 *
		 * @author Pippin Williamson
		 * @version 1.6
		 * @since 1.0
		 * @return void
		 */
		public static function edd_sl_render_licenses_meta_box()	{

			global $post;
			// Use nonce for verification
			echo '<input type="hidden" name="edd_sl_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

			echo '<table class="form-table">';

				$enabled   	= get_post_meta( $post->ID, '_edd_sl_enabled', true ) ? true : false;
				$limit  	= get_post_meta( $post->ID, '_edd_sl_limit', true );
				$version   	= get_post_meta( $post->ID, '_edd_sl_version', true );
				$changelog 	= get_post_meta( $post->ID, '_edd_sl_changelog', true );
				$file      	= get_post_meta( $post->ID, '_edd_sl_upgrade_file_key', true );
				$display   	= $enabled ? '' : ' style="display:none;"';

				// echo '<script type="text/javascript">jQuery( document ).ready( function($) {$( "#edd_license_enabled" ).on( "click",function() {$( ".edd_sl_toggled_row" ).toggle();} )} );</script>';

				echo '<tr' . $display . ' class="edd_sl_toggled_row">';
					echo '<td class="edd_field_type_text" colspan="2">';
						echo '<input type="checkbox" name="edd_license_enabled" id="edd_license_enabled" value="1" ' . checked( true, $enabled, false ) . '/>&nbsp;';
						echo '<label for="edd_license_enabled">' . __( 'Check to enable license creation', 'edd_sl' ) . '</label>';
					echo '<td>';
				echo '</tr>';

				echo '<tr' . $display . ' class="edd_sl_toggled_row">';
					echo '<td class="edd_field_type_text" colspan="2">';
						echo '<input type="text" class="small-text" name="edd_sl_limit" id="edd_sl_limit" value="' . esc_attr( $limit ) . '"/><br/>';
						echo __( 'Limit number of active licenses allowed', 'edd_sl' );
					echo '<td>';
				echo '</tr>';

				echo '<tr' . $display . ' class="edd_sl_toggled_row">';
					echo '<td class="edd_field_type_text" colspan="2">';
						echo '<input type="text" name="edd_sl_version" id="edd_sl_version" value="' . esc_attr( $version ) . '"/><br/>';
						echo __( 'Enter the current version number', 'edd_sl' );
					echo '<td>';
				echo '</tr>';

				echo '<tr' . $display . ' class="edd_sl_toggled_row">';
					echo '<td class="edd_field_type_select" colspan="2">';
						echo '<select name="edd_sl_upgrade_file" id="edd_sl_upgrade_file">';
							$files = get_post_meta( $post->ID, 'edd_download_files', true );
							if ( is_array( $files ) ) {
								foreach( $files as $key => $value ) {
									$name = isset( $files[$key]['name'] ) ? $files[$key]['name'] : '';
									echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $file, false ) . '>' . esc_html( $name ) . '</option>';
								}
							}
						echo '</select><br/>';
						echo '<label for="edd_sl_upgrade_file">' . __( 'Choose the source file to be used for automatic upgrades', 'edd_sl' ) . '</label>';
					echo '<td>';
				echo '</tr>';

				echo '<tr>';
					echo '<td class="edd_field_type_textarea" colspan="2">';
						echo '<label for="edd_sl_changelog">' . __( 'Change Log', 'edd_sl' ) . '</label><br/>';
						echo '<textarea name="edd_sl_changelog" id="edd_sl_changelog" rows="20" style="width: 96%;">' . esc_textarea( stripslashes( $changelog ) ) . '</textarea>';
						echo '<div class="description">' . __( 'Enter details about what changed', 'edd_sl' ) . '</div>';
					echo '</td>';
				echo '</tr>';

			echo '</table>';

		}


		/**
		 * Save data from meta box.
		 *
		 * Lifted from EDD Software Licensing for compatibility.
		 *
		 * @author Pippin Williamson
		 * @version 1.6
		 * @since 1.0
		 * @return void
		 */
		public static function edd_sl_download_meta_box_save( $post_id ) {

			global $post;

			// verify nonce
			if ( isset( $_POST['edd_sl_meta_box_nonce'] ) && ! wp_verify_nonce( $_POST['edd_sl_meta_box_nonce'], basename( __FILE__ ) ) ) {
				return $post_id;
			}

			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( isset( $_POST['post_type'] ) && 'download' != $_POST['post_type'] ) {
				return $post_id;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			if ( isset( $_POST['edd_license_enabled'] ) ) {
				update_post_meta( $post_id, '_edd_sl_enabled', true );
			} else {
				delete_post_meta( $post_id, '_edd_sl_enabled' );
			}

			if ( isset( $_POST['edd_sl_limit'] ) ) {
				update_post_meta( $post_id, '_edd_sl_limit', ( int ) $_POST['edd_sl_limit'] );
			} else {
				delete_post_meta( $post_id, '_edd_sl_limit' );
			}

			if ( isset( $_POST['edd_sl_version'] ) ) {
				update_post_meta( $post_id, '_edd_sl_version', ( string ) $_POST['edd_sl_version'] );
			} else {
				delete_post_meta( $post_id, '_edd_sl_version' );
			}

			if ( isset( $_POST['edd_sl_upgrade_file'] ) && $_POST['edd_sl_upgrade_file'] !== false ) {
				update_post_meta( $post_id, '_edd_sl_upgrade_file_key', ( int ) $_POST['edd_sl_upgrade_file'] );
			} else {
				delete_post_meta( $post_id, '_edd_sl_upgrade_file_key' );
			}

			if ( isset( $_POST['edd_sl_changelog'] ) ) {
				update_post_meta( $post_id, '_edd_sl_changelog', addslashes( $_POST['edd_sl_changelog'] ) ) ;
			} else {
				delete_post_meta( $post_id, '_edd_sl_changelog' );
			}

		}

	}

	/**
	 * The main function responsible for returning the EDD_Changelog instance
	 * to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $edd_changelog = EDD_Changelog(); ?>
	 *
	 * @access public
	 * @since 1.0
	 * @return (object)
	 */
	function EDD_Changelog() {
		return EDD_Changelog::getInstance();
	}

	/**
	 * Start the plugin.
	 */
	add_action( 'plugins_loaded', 'EDD_Changelog' );

}