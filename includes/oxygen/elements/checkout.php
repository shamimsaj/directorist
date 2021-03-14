<?php
/**
 * Checkout element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class Checkout extends Element {

	public function name() {
		return esc_html__( 'Cart / Checkout', 'directorist' );
	}

	public function slug() {
		return 'directorist-checkout';
	}
}

new Checkout();
