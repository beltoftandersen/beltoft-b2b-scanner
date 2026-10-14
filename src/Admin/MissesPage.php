<?php

namespace B2BScanner\Admin;

use B2BScanner\Log\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * The list of barcodes that did not resolve, so they can be fixed in Odoo.
 *
 * Grouped by code and ordered with reported codes first, then by how often each
 * one has come back: a code scanned forty times is one gap that forty orders
 * tripped over, and it should be the first thing anyone looks at.
 */
class MissesPage {

	private const SLUG = 'b2b-scanner-misses';

	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
	}

	public static function menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Códigos por resolver', 'b2b-scanner' ),
			__( 'Códigos por resolver', 'b2b-scanner' ),
			'manage_woocommerce',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$rows = Repository::summary();

		echo '<div class="wrap"><h1>' . esc_html__( 'Códigos por resolver', 'b2b-scanner' ) . '</h1>';
		echo '<p>' . esc_html__( 'Códigos lidos na página de encomenda que não deram um produto. Corrija-os em Odoo.', 'b2b-scanner' ) . '</p>';

		if ( ! $rows ) {
			echo '<p><em>' . esc_html__( 'Nada por resolver.', 'b2b-scanner' ) . '</em></p></div>';

			return;
		}

		echo '<table class="widefat striped"><thead><tr>'
			. '<th>' . esc_html__( 'Código', 'b2b-scanner' ) . '</th>'
			. '<th>' . esc_html__( 'Motivo', 'b2b-scanner' ) . '</th>'
			. '<th>' . esc_html__( 'Leituras', 'b2b-scanner' ) . '</th>'
			. '<th>' . esc_html__( 'Última', 'b2b-scanner' ) . '</th>'
			. '<th>' . esc_html__( 'Produto', 'b2b-scanner' ) . '</th>'
			. '<th>' . esc_html__( 'Reportado', 'b2b-scanner' ) . '</th>'
			. '</tr></thead><tbody>';

		foreach ( $rows as $row ) {
			$product = $row->product_id ? wc_get_product( (int) $row->product_id ) : null;

			printf(
				'<tr><td><code>%s</code></td><td>%s</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td></tr>',
				esc_html( $row->code ),
				esc_html( self::reason_label( $row->reason ) ),
				(int) $row->hits,
				esc_html( $row->last_seen ),
				$product ? esc_html( $product->get_name() ) : '&mdash;',
				$row->reported ? esc_html__( 'Sim', 'b2b-scanner' ) : '&mdash;'
			);
		}

		echo '</tbody></table></div>';
	}

	private static function reason_label( string $reason ): string {
		$labels = array(
			Repository::NOT_FOUND       => __( 'Sem produto com este código', 'b2b-scanner' ),
			Repository::NOT_PURCHASABLE => __( 'Existe, mas não disponível para esta conta', 'b2b-scanner' ),
			Repository::NOT_VISIBLE     => __( 'Fora do catálogo de parceiros', 'b2b-scanner' ),
			Repository::OUT_OF_STOCK    => __( 'Sem stock', 'b2b-scanner' ),
			Repository::DISCONTINUED    => __( 'Descontinuado', 'b2b-scanner' ),
		);

		return $labels[ $reason ] ?? $reason;
	}
}
