<?php
/**
 * Oxygen builder integration initialization functions.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

function oxygen_init() {
    if ( ! class_exists( '\OxyEl' ) ) {
        return;
    }

    include_once __DIR__ . '/class-element.php';
    include_once __DIR__ . '/class-container.php';

    Container::instance();
}
add_action( 'init', __NAMESPACE__ . '\oxygen_init' );
