<?php

use B2BScanner\Catalog\ProductLookup;

/**
 * The core judges only what WooCommerce itself knows - purchasable, in stock.
 * Catalog rules (who may see or buy what) are plugged in through
 * beltoft_scanner_assess by whoever owns them.
 */
class AssessTest extends WP_UnitTestCase {

	private WC_Product $product;
	private int $user;

	public function setUp(): void {
		parent::setUp();
		$product = new WC_Product_Simple();
		$product->set_regular_price( '10' );
		$product->set_status( 'publish' );
		$product->save();

		$this->product = wc_get_product( $product->get_id() );
		$this->user    = self::factory()->user->create();
	}

	public function tearDown(): void {
		remove_all_filters( 'beltoft_scanner_assess' );
		parent::tearDown();
	}

	private function status(): string {
		return ProductLookup::assess( $this->product, $this->user )['status'];
	}

	public function test_without_a_rule_a_purchasable_product_in_stock_is_ok() {
		$this->assertSame( ProductLookup::OK, $this->status() );
	}

	public function test_a_rule_can_hide_a_product() {
		add_filter( 'beltoft_scanner_assess', fn() => 'not_visible' );

		$this->assertSame( 'not_visible', $this->status() );
	}

	public function test_a_rule_can_refuse_to_sell_a_product() {
		add_filter( 'beltoft_scanner_assess', fn() => 'not_purchasable' );

		$this->assertSame( 'not_purchasable', $this->status() );
	}

	public function test_the_rule_is_given_the_product_and_the_user() {
		$args = null;

		add_filter( 'beltoft_scanner_assess', function ( $status, $product, $user_id ) use ( &$args ) {
			$args = array( $product->get_id(), $user_id );

			return $status;
		}, 10, 3 );

		$this->status();

		$this->assertSame( array( $this->product->get_id(), $this->user ), $args );
	}

	/** Only the two refusal statuses mean anything; anything else is ignored. */
	public function test_an_unknown_answer_is_ignored() {
		add_filter( 'beltoft_scanner_assess', fn() => 'maybe' );

		$this->assertSame( ProductLookup::OK, $this->status() );
	}

	public function test_the_core_still_refuses_an_out_of_stock_product() {
		$this->product->set_stock_status( 'outofstock' );
		$this->product->save();

		$this->assertSame( 'out_of_stock', $this->status() );
	}
}
