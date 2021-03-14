<?php
/**
 * Transaction failure element class.
 * 
 * @author wpWax
 */
namespace wpWax\Directorist\Oxygen;

class TransactionFailure extends Element {

	public function name() {
		return esc_html__( 'Transaction Failure', 'directorist' );
	}

	public function slug() {
		return 'directorist-transaction-failure';
	}
}

new TransactionFailure();
