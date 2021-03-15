<?php
/**
 * All categories element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class AllCategories extends Element {

	protected $shouldProcessParams = true;

	public function name() {
		return esc_html__( 'All Categories', 'directorist' );
	}

	public function slug() {
		return 'directorist-all-categories';
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
				'slug'    => 'drst_cat_per_page',
				'default' => 6
			)
		);

		$settings->addOptionControl(
			array(
				'type'    => 'dropdown',
				'name'    => __( 'Order By', 'directorist' ),
				'slug'    => 'drst_orderby',
				'default' => 'name',
				'value' => array(
					'id'    => __( 'ID', 'directorist' ),
					'count' => __( 'Count', 'directorist' ),
					'name'  => __( 'Name', 'directorist' ),
					'slug'  => __( 'Categories', 'directorist' ),
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

		$settings->addCustomControl( Container::get_categories_control( 'drst_slug', $this ), 'drst_slug' );
	}

	protected function remapShortcodeParams( array $params = array() ) {
		foreach ( [ 'slug' ] as $comma_separable_field ) {
			if ( empty( $params[ $comma_separable_field ] ) ) {
				continue;
			}

			$params[ $comma_separable_field ] = implode( ',', $params[ $comma_separable_field ] );
			unset( $comma_separable_field );
		}

		return $params;
	}
}

new AllCategories();
