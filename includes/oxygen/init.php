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
    include_once __DIR__ . '/class-manager.php';

    Manager::instance();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\oxygen_init' );
