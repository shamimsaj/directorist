<?php
/**
 * Search listing element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class SearchListing extends Element {

	protected $shouldProcessParams = true;

	public function name() {
		return esc_html__( 'Search Listing', 'directorist' );
	}

	public function slug() {
		return 'directorist-search-listing';
	}

	public function controls() {
		/**
		 * Settings section
		 */
		$settings = $this->addControlSection( 'settings', __( 'Settings', 'directorist' ), 'assets/icon.png', $this );

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Title & Subtitle?', 'directorist' ),
				'slug'    => 'drst_show_title_subtitle',
				'default' => 'yes'
			)
		)->setValue( array( 'yes', 'no' ) );

		$settings->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Search Form Title', 'directorist' ),
				'slug'      => 'drst_search_bar_title',
				'default'   => __( 'Search here', 'directorist' ),
				'condition' => 'drst_show_title_subtitle=yes',
			)
		);

		$settings->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Search Form Subtitle', 'directorist' ),
				'slug'      => 'drst_search_bar_sub_title',
				'default'   => __( 'Find the best match of your interest', 'directorist' ),
				'condition' => 'drst_show_title_subtitle=yes',
			)
		);

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show More Filters Button?', 'directorist' ),
				'slug'    => 'drst_more_filters_button',
				'default' => 'yes'
			)
		)->setValue( array( 'yes', 'no' ) );

		$settings->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'More Filters Button Label', 'directorist' ),
				'slug'      => 'drst_more_filters_text',
				'default'   => __( 'More Filters', 'directorist' ),
				'condition' => 'drst_more_filters_button=yes'
			)
		);

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Apply Filters Button?', 'directorist' ),
				'slug'    => 'drst_apply_filters_button',
				'default' => 'no',
				'condition' => 'drst_more_filters_button=yes',
			)
		)->setValue( array( 'yes', 'no' ) );

		$settings->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Apply Filters Text', 'directorist' ),
				'slug'      => 'drst_apply_filters_text',
				'default'   => __( 'Apply Filters', 'directorist' ),
				'condition' => 'drst_more_filters_button=yes&&drst_apply_filters_button=yes'
			)
		);

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Reset Filters Button?', 'directorist' ),
				'slug'    => 'drst_reset_filters_button',
				'default' => 'yes',
				'condition' => 'drst_more_filters_button=yes',
			)
		)->setValue( array( 'yes', 'no' ) );

		$settings->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Reset Filters Text', 'directorist' ),
				'slug'      => 'drst_reset_filters_text',
				'default'   => __( 'Reset Filters', 'directorist' ),
				'condition' => 'drst_more_filters_button=yes&&drst_reset_filters_button=yes'
			)
		);

		$settings->addOptionControl(
			array(
				'type'    => 'dropdown',
				'name'    => __( 'More Filter By', 'directorist' ),
				'slug'    => 'drst_more_filters_display',
				'default' => 'overlapping',
				'value' => array(
					'overlapping' => __( 'Overlapping', 'directorist' ),
					'sliding'     => __( 'Sliding', 'directorist' ),
					'always_open' => __( 'Always Open', 'directorist' ),
				)
			)
		);

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Logged In User Only?', 'directorist' ),
				'slug'    => 'drst_logged_in_user_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );
	}
}

new SearchListing();
