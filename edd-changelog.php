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