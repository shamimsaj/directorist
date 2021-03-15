<?php
/**
 * All listing element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class AllListing extends Element {

	protected $shouldProcessParams = true;

	public function name() {
		return esc_html__( 'All Listing', 'directorist' );
	}

	public function slug() {
		return 'directorist-all-listing';
	}

	public function controls() {
		/**
		 * Layout section
		 */
		$layout = $this->addControlSection( 'layout', __( 'Layout' ), 'assets/icon.png', $this );

		$layout->addOptionControl(
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

		$layout->addOptionControl(
			array(
				'type'  => 'dropdown',
				'name'  => __( 'Columns', 'directorist' ),
				'slug'  => 'drst_columns',
				'default' => '3',
				'value' => array(
					'1' => __( '1 Column', 'directorist' ),
					'2' => __( '2 Columns', 'directorist' ),
					'3'  => __( '3 Columns', 'directorist' ),
					'4'  => __( '4 Columns', 'directorist' ),
					'6'  => __( '6 Columns', 'directorist' ),
				)
			)
		);

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Pagination?', 'directorist' ),
				'slug'    => 'drst_show_pagination',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Featured Only?', 'directorist' ),
				'slug'    => 'drst_featured_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Header?', 'directorist' ),
				'slug'    => 'drst_header',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Header Title', 'directorist' ),
				'slug'      => 'drst_header_title',
				'condition' => 'drst_header=yes'
			)
		);

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Popular Only?', 'directorist' ),
				'slug'    => 'drst_popular_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Filter Button?', 'directorist' ),
				'slug'    => 'drst_advanced_filter',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Preview Image?', 'directorist' ),
				'slug'    => 'drst_display_preview_image',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Enable Before After Hook?', 'directorist' ),
				'slug'    => 'drst_action_before_after_loop',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Logged In User Only?', 'directorist' ),
				'slug'    => 'drst_logged_in_user_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Map Height', 'directorist' ),
				'slug'      => 'drst_map_height',
				'default'   => 500,
				'condition' => 'drst_view=map'
			)
		);

		$layout->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Map Zoom Level', 'directorist' ),
				'slug'      => 'drst_map_zoom_level',
				'condition' => 'drst_view=map'
			)
		);

		/**
		 * Query section
		 */
		$query = $this->addControlSection( 'query', __( 'Query', 'directorist' ), 'assets/icon.png', $this );
		
		$query->addOptionControl(
			array(
				'type'    => 'textfield',
				'name'    => __( 'Number Of Listing', 'directorist' ),
				'slug'    => 'drst_listings_per_page',
				'default' => 6
			)
		);

		$query->addOptionControl(
			array(
				'type'    => 'dropdown',
				'name'    => __( 'Order By', 'directorist' ),
				'slug'    => 'drst_orderby',
				'default' => 'date',
				'value' => array(
					'title' => __( 'Title', 'directorist' ),
					'date'  => __( 'Date', 'directorist' ),
					'rand'  => __( 'Random', 'directorist' ),
					'price' => __( 'Price', 'directorist' ),
				)
			)
		);

		$query->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Order', 'directorist' ),
				'slug'    => 'drst_order',
				'default' => 'desc',
			)
		)->setValue( array( 'asc', 'desc' ) );

		$query->addCustomControl( Container::get_listing_items( 'drst_ids', $this ), 'drst_ids' );

		$query->addCustomControl( Container::get_categories_control( 'drst_category', $this ), 'drst_category' );

		$query->addCustomControl( Container::get_tags_control( 'drst_tag', $this ), 'drst_tag' );

		$query->addCustomControl( Container::get_location_control( 'drst_location', $this ), 'drst_location' );
	}

	protected function remapShortcodeParams( array $params = array() ) {
		foreach ( [ 'category', 'tag', 'location', 'ids' ] as $comma_separable_field ) {
			if ( empty( $params[ $comma_separable_field ] ) ) {
				continue;
			}

			$params[ $comma_separable_field ] = implode( ',', $params[ $comma_separable_field ] );
			unset( $comma_separable_field );
		}

		return $params;
	}
}

new AllListing();
