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
				'default' => 'DESC',
			)
		)->setValue( array( 'ASC', 'DESC' ) );
	}
}

new AllListing();
