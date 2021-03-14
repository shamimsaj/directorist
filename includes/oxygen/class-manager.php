<?php
/**
 * Oxygen manager class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

defined( 'ABSPATH' ) || die();

use CT_Toolbar;

class Manager {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->load_files();

		// Register +Add Directorist section
		add_action( 'oxygen_add_plus_sections', array( $this, 'register_add_plus_section' ) );

		// Register +Add Directorist subsections
		// oxygen_add_plus_{$id}_section_content
		add_action( 'oxygen_add_plus_directorist_category_section_content', array( $this, 'register_add_plus_subsections' ) );

		// add_action( 'init',       array( $this, 'init_callback' ) );
	}

	public function load_files() {
		// elements without controls
		include_once 'elements/add-listing.php';
		include_once 'elements/checkout.php';
		include_once 'elements/custom-registration.php';
		include_once 'elements/payment-receipt.php';
		include_once 'elements/transaction-failure.php';
		include_once 'elements/user-dashboard.php';
		include_once 'elements/user-login.php';

		// elements with controls
		include_once 'elements/all-listing.php';
	}

	/**
	 * Register directorist section tab
	 *
	 * @return void
	 */
	public function register_add_plus_section() {
		CT_Toolbar::oxygen_add_plus_accordion_section( 'directorist_category', __( 'Directorist', 'directorist' ) );
	}

	/**
	 * Register directorist elemnents section hook
	 *
	 * @return void
	 */
	public function register_add_plus_subsections() {
		/**
		 * Oxygen is creating this hook dynamically
		 * 
		 * button place value is being used to create this hook.
		 * eg. directorist::elements -> oxygen_add_plus_directorist_elements
		 * 
		 * @see \OxygenElementHelper /component-framework/api/oxygen.element-helper.class.php
		 */
		do_action( 'oxygen_add_plus_directorist_elements' );
	}

	function init_callback() {

		// we don't want wooco redirects to work when builder is loading
		if ( defined("SHOW_CT_BUILDER") || defined("OXYGEN_IFRAME") ) {
			remove_action( 'template_redirect', 'wc_template_redirect' );
		}

	}

	public static function get_locations() {

	}

	public static function get_categories() {

	}

	public static function get_tags() {

	}

	public static function get_listings() {
		
	}
}
