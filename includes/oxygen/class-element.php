<?php
/**
 * Oxygen elememnt base class for Directorist.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

defined( 'ABSPATH' ) || die();

use OxyEl;

class Element extends OxyEl {

	public function controls() {}

	public function init() {
		$this->El->useAJAXControls();
		$this->setAssetsPath( plugins_url( 'assets', __FILE__ ) ); 
	}

	public function tag() {
		return 'div';
	}

	public function icon() {
		return plugin_dir_url( __FILE__ ) . 'assets/icon.svg';
	}

	public function class_names() {
		return array( 'directorist-oxy-el' );
	}
	
	public function button_place() {
		return 'directorist::elements';
	}

	public function render( $options, $defaults, $content ) {
		$shortcode = str_replace( '-', '_', $this->slug() );
		$attributes = array();

		if ( method_exists( $this, 'remapShortcodeAttributes' ) ) {
			$attributes = $this->remapShortcodeAttributes( $options );
		}

		echo $this->doShortcodeCallback( $shortcode, $attributes, $content );
	}

	/**
	 * Call a shortcode function by tag name.
	 *
	 * @param string $tag     The shortcode whose function to call.
	 * @param array  $atts    The attributes to pass to the shortcode function. Optional.
	 * @param array  $content The shortcode's content. Default is null (none).
	 *
	 * @return string|bool False on failure, the result of the shortcode on success.
	 */
	protected function doShortcodeCallback( $tag, array $atts = array(), $content = null ) {
		global $shortcode_tags;

		if ( ! isset( $shortcode_tags[ $tag ] ) ) {
			return false;
		}

		return call_user_func( $shortcode_tags[ $tag ], $atts, $content, $tag );
	}
}
