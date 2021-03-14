<?php
/**
 * Payment receipt element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class PaymentReceipt extends Element {

	public function name() {
		return esc_html__( 'Payment Receipt', 'directorist' );
	}

	public function slug() {
		return 'directorist-payment-receipt';
	}
}

new PaymentReceipt();
