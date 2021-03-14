<?php
/**
 * All listing element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class AllListing extends Element {

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
				'slug'  => 'view',
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
				'slug'  => 'columns',
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
				'slug'    => 'show_pagination',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Featured Only?', 'directorist' ),
				'slug'    => 'featured_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Header?', 'directorist' ),
				'slug'    => 'header',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Header Title', 'directorist' ),
				'slug'      => 'header_title',
				'condition' => 'header=yes'
			)
		);

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Popular Only?', 'directorist' ),
				'slug'    => 'popular_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Filter Button?', 'directorist' ),
				'slug'    => 'advanced_filter',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Show Preview Image?', 'directorist' ),
				'slug'    => 'display_preview_image',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Enable Before After Hook?', 'directorist' ),
				'slug'    => 'action_before_after_loop',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Logged In User Only?', 'directorist' ),
				'slug'    => 'logged_in_user_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );

		$layout->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Map Height', 'directorist' ),
				'slug'      => 'map_height',
				'default'   => 500,
				'condition' => 'view=map'
			)
		);

		$layout->addOptionControl(
			array(
				'type'      => 'textfield',
				'name'      => __( 'Map Zoom Level', 'directorist' ),
				'slug'      => 'map_zoom_level',
				'condition' => 'view=map'
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
				'slug'    => 'listings_per_page',
				'default' => 6
			)
		);

		$query->addOptionControl(
			array(
				'type'    => 'dropdown',
				'name'    => __( 'Order By', 'directorist' ),
				'slug'    => 'orderby',
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
				'slug'    => 'order',
				'default' => 'desc',
			)
		)->setValue( array( 'asc', 'desc' ) );

		$query->addCustomControl( Container::get_listing_items( 'ids', $this ), 'ids' );

		$query->addCustomControl( Container::get_categories_control( 'category', $this ), 'category' );

		$query->addCustomControl( Container::get_tags_control( 'tag', $this ), 'tag' );

		$query->addCustomControl( Container::get_location_control( 'location', $this ), 'location' );
	}

	protected function remapShortcodeAttributes( array $attributes = array() ) {
		$supportedAttributes = array(
			'view',
			'filterby',
			'orderby',
			'order',
			'listings_per_page',
			'show_pagination',
			'header',
			'header_title',
			'category',
			'location',
			'tag',
			'ids',
			'columns',
			'featured_only',
			'popular_only',
			'advanced_filter',
			'display_preview_image',
			'action_before_after_loop',
			'logged_in_user_only',
			'map_height',
			'map_zoom_level',
			'directory_type',
			'default_directory_type'
		);

		$attributes = array_intersect_key( $attributes, array_flip( $supportedAttributes ) );

		foreach ( ['category', 'tag', 'location', 'ids'] as $comma_separable_field ) {
			if ( isset( $attributes[ $comma_separable_field ] ) ) {
				$attributes[ $comma_separable_field ] = implode( ',', $attributes[ $comma_separable_field ] );
			}

			unset( $comma_separable_field );
		}

		return $attributes;
	}
}

new AllListing();
