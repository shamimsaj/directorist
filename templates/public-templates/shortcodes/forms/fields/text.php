<?php
/**
 * @author  AazzTech
 * @since   6.7
 * @version 6.7
 */

global $post;
$id = $post->ID ? $post->ID : '';
?>
<div class="form-group" id="directorist-text-field">
	<?php if ( ! empty( $label ) ) : ?>
		<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $label ); ?>:<?php echo ! empty( $required ) ? Directorist_Listing_Forms::instance()->add_listing_required_html() : ''; ?></label>
	<?php endif; ?>

	<input type="text" name="custom_field[<?php echo esc_attr( $id ); ?>] <?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $value ); ?>" class="form-control directory_field" placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo ! empty( $required ) ? 'required="required"' : ''; ?> >

	<p> <?php echo esc_attr( $description ); ?> </p>
</div>