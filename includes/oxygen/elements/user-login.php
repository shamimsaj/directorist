<?php
/**
 * User login element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class UserLogin extends Element {

	public function name() {
		return esc_html__( 'Login', 'directorist' );
	}

	public function slug() {
		return 'directorist-user-login';
	}
}

new UserLogin();
