<?php
/**
 * Author profile element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class AuthorProfile extends Element {

	protected $shouldProcessParams = true;

	public function name() {
		return esc_html__( 'Author Profile', 'directorist' );
	}

	public function slug() {
		return 'directorist-author-profile';
	}

	public function controls() {
		/**
		 * Settings section
		 */
		$settings = $this->addControlSection( 'settings', __( 'Settings', 'directorist' ), 'assets/icon.png', $this );

		$settings->addOptionControl(
			array(
				'type'    => 'buttons-list',
				'name'    => __( 'Logged In User Only?', 'directorist' ),
				'slug'    => 'drst_logged_in_user_only',
				'default' => 'no'
			)
		)->setValue( array( 'yes', 'no' ) );
	}
}

new AuthorProfile();
