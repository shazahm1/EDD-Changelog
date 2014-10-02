<?php
/*
Plugin Name: EDD Changelog
Plugin URI: http://www.connections-pro.com
Description: Adds a changelog meta box to the downloads post type.
Version: 1.1
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
			// self::inludeDependencies();

			load_plugin_textdomain( 'eddclog' , FALSE , EDDCLOG_DIR_NAME . 'languages' );

			// If EDD Software Licensing plugin isn't installed, then add the Changelog metabox.
			if ( ! class_exists( 'EDD_Software_Licensing' ) ) {

				add_action( 'add_meta_boxes', array( __CLASS__, 'edd_sl_add_license_meta_box' ), 100 );
				add_action( 'save_post', array( __CLASS__, 'edd_sl_download_meta_box_save' ) );

			}

			add_action( 'edd_meta_box_fields', array( __CLASS__, 'render_disable_checkbox' ), 40 );

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

			add_action( 'edd_after_download_content', array( __CLASS__, 'append_changelog' ), 100 );

			// add_action( 'admin_print_footer_scripts', array( __CLASS__ , 'quicktag_js' ) );

			add_action( 'edd_receipt_files', array( __CLASS__ , 'receipt' ), 10, 5 );

			add_action( 'edd_download_history_header_end', array( __CLASS__ , 'download_history_head' ) );

			add_action( 'edd_download_history_row_end', array( __CLASS__ , 'download_history' ), 10, 2 );

			add_shortcode( 'edd_changelog', array( __CLASS__, 'shortcode' ) );

			add_action( 'wp_footer', array( __CLASS__, 'inline_js' ) );
		}

		/**
		 * Define the constants.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private static function defineConstants() {

			define( 'EDDCLOG_VERSION', '1.1' );

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
		public static function enqueue_scripts() {

			// If SCRIPT_DEBUG is set and TRUE load the non-minified CSS files, otherwise, load the minified files.
			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'jquery' );
			wp_enqueue_style( 'edd-changelog', EDDCLOG_BASE_URL . "edd-changelog$min.css", array(), EDDCLOG_VERSION );
			wp_enqueue_style( 'genericons', EDDCLOG_BASE_URL . "lib/genericons$min.css", array(), '2.06' );
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
		public static function render_disable_checkbox( $post_id ) {

			$hide_button = get_post_meta( $post_id, '_edd_hide_changelog', TRUE ) ? TRUE : FALSE;

			echo '<p><strong>' . __( 'Change Log:', 'eddclog' ) . '</strong></p>';
			echo '<p>';
				echo '<label for="_edd_hide_changelog">';
					echo '<input type="checkbox" name="_edd_hide_changelog" id="_edd_hide_changelog" value="1"' , checked( TRUE, $hide_button ) , '/> ';
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
				$display   	= FALSE ? '' : ' style="display:none;"'; // Always display none. --> Adapted by saz

				// No need for this --> Adapted by saz
				// echo '<script type="text/javascript">jQuery( document ).ready( function($) {$( "#edd_license_enabled" ).on( "click",function() {$( ".edd_sl_toggled_row" ).toggle();} )} );</script>';

				// Hide this. --> Adapted by saz
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

				//  Show this --> Adapted by saz
				echo '<tr>';
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

				//  Show this --> Adapted by saz
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

			// Save the hide changelog option. --> Adapted by saz
			if ( isset( $_POST['_edd_hide_changelog'] ) ) {
				update_post_meta( $post_id, '_edd_hide_changelog', true );
			} else {
				delete_post_meta( $post_id, '_edd_hide_changelog' );
			}

		}

		/**
		 * Outputs the JS necessary to support the quicktag for the changelog text area field..
		 *
		 * @author Steven A. Zahm
		 * @access private
		 * @since 0.7.3.0
		 * @return void
		 */
		public static function quicktag_js() {

			$screen = get_current_screen();

			if ( 'download' == $screen->id ) {

				echo '<script type="text/javascript">/* <![CDATA[ */';
					echo 'quicktags("edd_sl_changelog");';
				echo '/* ]]> */</script>';

			}

		}

		/**
		 * Append the changelog to the end of the download page.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		public static function append_changelog( $download_id ) {

			$show_changelog = get_post_meta( $download_id, '_edd_hide_changelog', TRUE ) ? FALSE : TRUE;

			if ( $show_changelog ) {

				echo self::shortcode( array( 'id' => $download_id ) );

			}

		}

		/**
		 * The [edd_changelog] shortcode.
		 *
		 * @access public
		 * @since 1.0
		 * @return string
		 */
		public static function shortcode( $atts, $content = NULL, $tag = 'edd_changelog' ) {
			$html = '';
			$style = '';
			static $instance = 1;

			$defaults = array(
					'id' => get_the_id(),
					'toggle' => 'yes',
					'show' => 'Show Change Log',
					'hide' => 'Hide Change Log'
				);

			$atts = shortcode_atts( $defaults, $atts, $tag );

			$atts['toggle'] = strtolower( $atts['toggle'] );

			$changelog = get_post_meta( $atts['id'], '_edd_sl_changelog', TRUE );

			//  Sanitize the HTML from the change log field.
			$changelog = balanceTags( wp_kses_post( $changelog ), TRUE );

			if ( ! empty( $changelog ) ) {

				if ( 'yes' === strtolower( $atts['toggle'] ) ) {

					$html .= '<div class="edd_changelog-container">';

					$html .= sprintf( '<span class="edd_changelog-toggle-container" onclick="eddclog_toggle(\'%1$d\', \'%2$s\', \'%3$s\');"><span id="edd_changelog_toggle-%1$d" class="edd_changelog-toggle">%2$s</span><span id="edd_changelog-icon-%1$s" class="edd_changelog-icon"></span></span>',
							$instance,
							esc_js( $atts['show'] ),
							esc_js( $atts['hide'] )
						);

					$style = ' style="display: none;"';

				}

				$html .= sprintf( '<div id="edd_changelog_content-%1$d" class="edd_changelog-content"%2$s>%3$s</div>',
						$instance,
						$style,
						$changelog
					);

				if ( 'yes' === strtolower( $atts['toggle'] ) ) $html .= '</div>';

				$instance++;
			}

			return $html;
		}

		/**
		 * Add the current version and link to the changelog popup to the purchase history receipt.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		public static function receipt( $filekey, $file, $item_ID, $payment_ID, $meta ) {
			static $instance = 1;
			$version = get_post_meta( $item_ID, '_edd_sl_version', TRUE );
			$changelog = get_post_meta( $item_ID, '_edd_sl_changelog', TRUE );

			if ( ! empty( $version ) )
				printf( '<li class="edd_download_version"><strong>%1$s</strong> %2$s</li>',
					__( 'Current Version:', 'eddclog' ),
					esc_html( $version )
				);

			if ( ! empty( $changelog ) ) {

				wp_enqueue_style('thickbox');
				wp_enqueue_script('thickbox');

				printf( '<li class="edd_download_changlog"><a class="thickbox" href="#TB_inline?height=500&amp;width=400&amp;inlineId=edd_download_changlog-%3$d" title="%2$s">%1$s</a></li>',
					__( 'View Change Log', 'eddclog' ),
					__( 'Change Log', 'edd_sl' ),
					absint( $instance )
				);

				printf( '<li id="edd_download_changlog-%1$d" style="display:none">%2$s</li>',
					absint( $instance ),
					balanceTags( wp_kses_post( $changelog ), TRUE )
				);

				$instance++;

			}
		}

		/**
		 * Append the Version table header to the download history shortcode output.
		 *
		 * @access private
		 * @since  1.1
		 * @static
		 * @return string
		 */
		public static function download_history_head() {

			?>

			<th class="edd_download_download_version">

			<?php

				_e( 'Current Version', 'eddclog' );

			?>

			</th>

			<?php
		}

		/**
		 * the current version to the Version column in the  download history shortcode output.
		 *
		 * @access private
		 * @since  1.1
		 * @static
		 * @return string
		 */
		public static function download_history( $payment_id, $download_id ) {

			?>

			<td class="edd_download_changlog">

			<?php

				$version = get_post_meta( $download_id, '_edd_sl_version', TRUE );
				echo esc_attr( $version );

			?>

			</td>

			<?php
		}

		/**
		 * The inline toggle JS.
		 *
		 * @access private
		 * @since 1.0
		 * @return string
		 */
		public static function inline_js() {
			echo '<script type="text/javascript">'."\n";
			echo '/* <![CDATA[ */'."\n";
			echo 'if ( undefined !== window.jQuery ) { function eddclog_toggle(a,b,c) { jQuery( "#edd_changelog_content-" + a ).slideToggle("slow"); jQuery("#edd_changelog_toggle-" + a ).text( jQuery("#edd_changelog_toggle-" + a ).text() == b ? c : b ); jQuery("#edd_changelog-icon-" + a ).toggleClass( "edd_changelog-icon-down" ); } }'."\n";
			echo '/* ]]> */'."\n";
			echo '</script>'."\n";
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
