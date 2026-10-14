<?php

namespace B2BScanner\Ui;

use B2BScanner\Rest\Routes;
use B2BScanner\Support\Breakpoints;
use B2BScanner\Support\Context;

defined( 'ABSPATH' ) || exit;

/**
 * Loads the scanner's two files, and only where they are used.
 *
 * The ordering page is one page on one host for a handful of accounts. Putting
 * a camera library on every request of both shops to serve it would be a poor
 * trade, so this is conditional on the same check the button uses.
 */
class Assets {

	public const HANDLE = 'b2b-scanner';

	public static function register(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_footer', array( __CLASS__, 'panel' ), 30 );
	}

	public static function enqueue(): void {
		if ( ! Context::applies() ) {
			return;
		}

		wp_enqueue_style(
			self::HANDLE,
			B2B_SCANNER_URL . 'assets/scanner.css',
			array(),
			B2B_SCANNER_VERSION
		);

		wp_enqueue_script(
			self::HANDLE,
			B2B_SCANNER_URL . 'assets/scanner.js',
			array(),
			B2B_SCANNER_VERSION,
			true
		);

		wp_localize_script( self::HANDLE, 'B2BScannerData', self::data() );
	}

	private static function data(): array {
		return array(
			'restUrl'    => esc_url_raw( rest_url( Routes::NAMESPACE_V1 ) ),
			'nonce'      => wp_create_nonce( 'wp_rest' ),
			'loginUrl'   => esc_url_raw( wc_get_page_permalink( 'myaccount' ) ),
			'breakpoint' => Breakpoints::mobile_max_width(),
			/*
			 * The ZXing build is served from this plugin. It is only fetched
			 * when the browser has no BarcodeDetector of its own, which on
			 * current Android Chrome it does - so most phones never download it.
			 */
			'zxingUrl'   => esc_url_raw( B2B_SCANNER_URL . 'assets/lib/zxing-browser.min.js' ),
			'i18n'       => array(
				'title'         => __( 'Ler código de barras', 'b2b-scanner' ),
				'close'         => __( 'Fechar', 'b2b-scanner' ),
				'torch'         => __( 'Luz', 'b2b-scanner' ),
				'manual'        => __( 'Introduzir código', 'b2b-scanner' ),
				'manualPlace'   => __( 'EAN ou referência', 'b2b-scanner' ),
				'add'           => __( 'Adicionar', 'b2b-scanner' ),
				'undo'          => __( 'Anular', 'b2b-scanner' ),
				'report'        => __( 'Reportar', 'b2b-scanner' ),
				'notify'        => __( 'Notificar-me', 'b2b-scanner' ),
				/* translators: %s: the scanned barcode. */
				'notFound'      => __( 'Código não encontrado: %s', 'b2b-scanner' ),
				'noStock'       => __( 'Sem stock', 'b2b-scanner' ),
				'pending'       => __( 'pendente', 'b2b-scanner' ),
				'offline'       => __( 'Sem ligação. Os códigos ficam guardados.', 'b2b-scanner' ),
				'denied'        => __( 'Não conseguimos aceder à câmara. Autorize o acesso nas definições do navegador ou introduza o código manualmente.', 'b2b-scanner' ),
				'noCamera'      => __( 'Este dispositivo não tem câmara disponível. Introduza o código manualmente.', 'b2b-scanner' ),
				'webview'       => __( 'A câmara não funciona dentro desta aplicação. Abra a página no Safari ou no Chrome.', 'b2b-scanner' ),
				'openExternal'  => __( 'Abrir no navegador', 'b2b-scanner' ),
				'sessionLost'   => __( 'A sessão expirou. Inicie sessão novamente para continuar — as leituras ficam guardadas.', 'b2b-scanner' ),
				'signIn'        => __( 'Iniciar sessão', 'b2b-scanner' ),
				'restored'      => __( 'Recuperámos as leituras que tinha pendentes.', 'b2b-scanner' ),
				'available'     => __( 'Disponível: %s', 'b2b-scanner' ),
				'added'         => __( '%1$s · %2$s na encomenda', 'b2b-scanner' ),
				'starting'      => __( 'A iniciar a câmara…', 'b2b-scanner' ),
				'oneLine'       => __( '%s artigo', 'b2b-scanner' ),
				/* translators: %s: number of items on the order. */
				'nLines'        => __( '%s artigos', 'b2b-scanner' ),
				'more'          => __( 'Aumentar quantidade', 'b2b-scanner' ),
				'less'          => __( 'Diminuir quantidade', 'b2b-scanner' ),
				'qty'           => __( 'Quantidade', 'b2b-scanner' ),
				'remove'        => __( 'Remover', 'b2b-scanner' ),
			),
		);
	}

	/**
	 * The panel lives at the end of the body, not inside the shortcode.
	 *
	 * It is pinned to the top of the viewport while the list scrolls behind it,
	 * and an element that has to escape every ancestor's overflow, transform and
	 * stacking context is far easier to keep honest as a direct child of body
	 * than as whatever the builder wrapped the button in.
	 */
	public static function panel(): void {
		if ( ! Context::applies() ) {
			return;
		}

		include B2B_SCANNER_DIR . 'src/Ui/panel.php';
	}
}
