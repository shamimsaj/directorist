<?php
/**
 * Custom registration element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class CustomRegistration extends Element {

	public function name() {
		return esc_html__( 'Registration', 'directorist' );
	}

	public function slug() {
		return 'directorist-custom-registration';
	}
}

new CustomRegistration();
