<?php
/**
 * Add listing element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class AddListing extends Element {

	public function name() {
		return esc_html__( 'Add Listing', 'directorist' );
	}

	public function slug() {
		return 'directorist-add-listing';
	}
}

new AddListing();
