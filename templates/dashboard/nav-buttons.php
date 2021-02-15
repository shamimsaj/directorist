<?php
/**
 * @author  wpWax
 * @since   6.6
 * @version 6.7
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="directorist-tab__nav__action">

	<?php if ( $dashboard->user_can_submit() ): ?>
		<a href="<?php echo esc_url(ATBDP_Permalink::get_add_listing_page_link()); ?>" class="directorist-btn directorist-btn-dark directorist-btn--add-listing"><?php esc_html_e( 'Submit Listing', 'directorist' ); ?></a>
	<?php endif; ?>

	<?php if ( $dashboard->user_type == 'general' ): ?>
		<a href="#" class="directorist-btn directorist-btn-primary directorist-btn--become-author" data-nonce="<?php echo wp_create_nonce( 'atbdp_become_author' ); ?>" data-userId="<?php echo get_current_user_id(); ?>"><?php esc_html_e( 'Become an author', 'directorist' ); ?></a>
		<p id="atbdp-become-author-success"></p>
	<?php endif; ?>

	<a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="directorist-btn directorist-btn-secondary directorist-btn--logout"><?php esc_html_e( 'Log Out', 'directorist' ); ?></a>

</div>