<?php
/**
 * Search result element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class SearchResult extends Element {

	protected $shouldProcessParams = true;

	public function name() {
		return esc_html__( 'Search Result', 'directorist' );
	}

	public function slug() {
		return 'directorist-search-result';
	}

	public function controls() {
		/**
		 * Settings section
		 */
		$settings = $this->addControlSection( 'settings', __( 'Settings', 'directorist' ), 'assets/icon.png', $this );

		$settings->addOptionControl(
			array(
				'type'  => 'dropdown',
				'name'  => __( 'View', 'directorist' ),
				'slug'  => 'drst_view',
				'default' => 'grid',
				'value' => array(
					'grid' => __( 'Grid', 'directorist' ),
					'list' => __( 'List', 'directorist' ),
					'map'  => __( 'Map', 'directorist' ),
				)
			)
		);

		$settings->addOptionControl(
			array(
				'type'  => 'dropdown',
				'name'  => __( 'Columns', 'directorist' ),
				'slug'  => 'drst_columns',
				'default' => '3',
				'value' => array(
					'1' => __( '1 Column', 'directorist' ),
					'2' => __( '2 Columns', 'directorist' ),
					'3' => __( '3 Columns', 'directorist' ),
					'4' => __( '4 Columns', 'directorist' ),
					'6' => __( '6 Columns', 'directorist' ),
				),
				'condition' => 'drst_view=grid'
			)
		);

		$settings->addOptionControl(
			array(
				'type'    => 'textfield',
				'name'    => __( 'Number Of Listing', 'directorist' ),
				'slug'    => 'drst_listings_per_page',
				'default' => 6
			)
		);

		$settings->addOptionControl(
			array(
				'type'    => 'dropdown',
				'name'    => __( 'Order By', 'directorist' ),
				'slug'    => 'drst_orderby',
				'default' => 'date',
				'value' => array(
					'title' => __( 'Title', 'directorist' ),
					'date'  => __( 'Date', 'directorist' ),
					'price' => __( 'Price', 'directorist' ),
				)
			)
		);
		
		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Order', 'directorist' ),
				'slug'    => 'drst_order',
				'default' => 'desc',
			)
		)->setValue( array( 'asc', 'desc' ) );

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Pagination?', 'directorist' ),
				'slug'    => 'drst_show_pagination',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Header?', 'directorist' ),
				'slug'    => 'drst_header',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$settings->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Header Title', 'directorist' ),
				'slug'      => 'drst_header_title',
				'condition' => 'drst_header=yes'
			)
		);

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Featured Only?', 'directorist' ),
				'slug'    => 'drst_featured_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Popular Only?', 'directorist' ),
				'slug'    => 'drst_popular_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Logged In User Only?', 'directorist' ),
				'slug'    => 'drst_logged_in_user_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$settings->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Map Height', 'directorist' ),
				'slug'      => 'drst_map_height',
				'default'   => 500,
				'condition' => 'drst_view=map'
			)
		);

		$settings->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Map Zoom Level', 'directorist' ),
				'slug'      => 'drst_map_zoom_level',
				'condition' => 'drst_view=map'
			)
		);
	}
}

new SearchResult();
