<?php
/**
 * Oxygen elememnt base class for Directorist.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

defined( 'ABSPATH' ) || die();

use OxyEl;

/**
 * === Development Notes ===
 * 1. we can use `rebuildElementOnChange()` method to update the view real time on settings change.
 * 2. `addCustomControl()` adds the html inside a wrapper which is already exists in html hence we experience extra spacing.
 */

/**
 * Base class for Directorist elements.
 */
class Element extends OxyEl {

	/**
	 * Process shortcode params from controls.
	 * 
	 * This flag helps to process controls automatically.
	 * 
	 * @see ->render()
	 *
	 * @var bool
	 */
	protected $shouldProcessParams = false;

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
		
		echo $this->doShortcodeCallback(
			$shortcode,
			$this->processShortcodeParams( $options ),
			$content
		);
	}

	protected function processShortcodeParams( $options ) {
		$params = array();

		if ( empty( $this->shouldProcessParams ) ) {
			return $params;
		}

		$validParams = array_filter( array_keys( $options ), function( $key ) {
			return strpos( $key, 'drst_' ) !== false;
		} );

		$params = array_intersect_key( $options, array_flip( $validParams ) );

		$validParams = array_map( function( $validParam ) {
			return substr( $validParam, strlen( 'drst_' ) );
		}, $validParams );

		$params = array_combine( $validParams, $params );

		if ( method_exists( $this, 'remapShortcodeParams' ) ) {
			$params = $this->remapShortcodeParams( $params );
		}

		unset( $validParams );

		return $params;
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
