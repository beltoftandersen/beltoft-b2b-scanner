<?php
/**
 * The scan button as a Bricks element.
 *
 * Not namespaced on purpose: Bricks includes this file and then instantiates
 * the class by the global name it discovers in it.
 *
 * @package B2BScanner
 */

defined( 'ABSPATH' ) || exit;

class Element_B2B_Scan_Button extends \Bricks\Element {

	public $category = 'woocommerce';
	public $name     = 'b2b-scan-button';
	public $icon     = 'ti-camera';

	public function get_label() {
		return esc_html__( 'Ler código de barras (B2B)', 'b2b-scanner' );
	}

	public function set_controls() {
		$this->controls['label'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Texto', 'b2b-scanner' ),
			'type'        => 'text',
			'default'     => esc_html__( 'Ler código', 'b2b-scanner' ),
			'placeholder' => esc_html__( 'Ler código', 'b2b-scanner' ),
		);

		/*
		 * Bricks already has a conditions system, and this site uses it for
		 * logged-in and role checks elsewhere. This control exists so the button
		 * can be restricted without leaving the builder, but it is not the
		 * security boundary: B2BScanner\Support\Context decides on the server
		 * whether the button renders and whether the endpoints answer, and it
		 * does so whatever is set here.
		 */
		$this->controls['rolesInfo'] = array(
			'tab'     => 'content',
			'type'    => 'info',
			'content' => esc_html__( 'Visível apenas para parceiros aprovados, na página de encomenda e em ecrãs pequenos. Use as condições do Bricks para restringir mais.', 'b2b-scanner' ),
		);

		$this->controls['alwaysVisible'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Mostrar também em ecrãs grandes', 'b2b-scanner' ),
			'type'        => 'checkbox',
			'description' => esc_html__( 'Por omissão o botão só aparece abaixo do breakpoint mobile landscape do Bricks.', 'b2b-scanner' ),
		);
	}

	public function render() {
		$settings = $this->settings;
		$label    = isset( $settings['label'] ) && '' !== $settings['label']
			? $settings['label']
			: esc_html__( 'Ler código', 'b2b-scanner' );

		if ( ! \B2BScanner\Support\Context::applies() ) {
			// In the builder, show why nothing renders instead of an empty box.
			if ( bricks_is_builder_call() ) {
				echo '<div ' . $this->render_attributes( '_root' ) . '>'
					. esc_html__( 'O botão aparece na página de encomenda, para parceiros aprovados.', 'b2b-scanner' )
					. '</div>';
			}

			return;
		}

		$classes = empty( $settings['alwaysVisible'] ) ? '' : ' b2bs-trigger--always';

		echo '<div ' . $this->render_attributes( '_root' ) . '>'
			. str_replace( 'class="b2bs-trigger"', 'class="b2bs-trigger' . esc_attr( $classes ) . '"', \B2BScanner\Ui\Shortcode::markup( $label ) )
			. '</div>';
	}
}
