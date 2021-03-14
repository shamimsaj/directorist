<?php
/**
 * User dashboard element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class UserDashboard extends Element {

	public function name() {
		return esc_html__( 'User Dashboard', 'directorist' );
	}

	public function slug() {
		return 'directorist-user-dashboard';
	}
}

new UserDashboard();
