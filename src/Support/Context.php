<?php

namespace B2BScanner\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Who the scanner is for, and where it appears - decided in one place.
 *
 * Both the REST endpoints and the button need the same answer to "is this an
 * approved partner on the trade site?", and if the two ever disagree the result
 * is either a button that cannot look anything up or an endpoint answering
 * requests the button would never make. So they ask this.
 *
 * Approval matters: this install has b2b_pending as well as b2b_customer, and a
 * pending applicant can sign in before anyone has approved them. They must not
 * be able to price or order anything, here or anywhere else.
 */
class Context {

	/**
	 * Roles allowed to scan.
	 *
	 * b2b_pending is deliberately not one of them: a pending applicant can sign
	 * in before anyone has approved them and must not be able to price or order
	 * anything.
	 *
	 * pos_operator is - the till is the case this was built for even before it
	 * existed. Somebody standing at a counter with a barcode in their hand is
	 * the most literal use of a scanner there is, and without this the button
	 * simply never appeared for them.
	 *
	 * A LIST, and therefore something to keep in step by hand. This plugin
	 * stands alone and only talks to beltoft-commerce through class_exists
	 * guards, so it cannot ask the pricing registry which groups may buy. If a
	 * third selling group is ever added, it has to be added here too or the
	 * scanner will quietly not appear for it.
	 */
	private const ROLES = array( 'b2b_customer', 'pos_operator' );

	public static function is_b2b_host(): bool {
		if ( ! class_exists( '\BeltoftCommerce\Support\Hosts' ) ) {
			return false;
		}

		return \BeltoftCommerce\Support\Hosts::is_b2b_host();
	}

	/**
	 * An approved partner, on any host.
	 *
	 * Host and role are separate questions: the REST endpoint is reached by an
	 * XHR whose Host header is the trade site, but an administrator testing from
	 * the retail host should still get a sensible answer rather than silence.
	 */
	public static function user_may_scan( ?int $user_id = null ): bool {
		$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();

		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		if ( array_intersect( self::ROLES, (array) $user->roles ) ) {
			return true;
		}

		// Shop managers and administrators can use it to check the catalog.
		return user_can( $user, 'manage_woocommerce' );
	}

	/**
	 * An ordering page, which is the only kind of page the scanner belongs on.
	 *
	 * The answer comes from beltoft-commerce rather than being worked out here;
	 * that plugin already keeps the paths in one place because Domain\HostRoutes
	 * redirects on them and the two must not drift. There is more than one now -
	 * /parceiros/loja/ is a working copy of the ordering page - and asking the
	 * shim rather than comparing against a single path is what keeps the button
	 * on both without this plugin having to know why there are two.
	 */
	public static function is_ordering_page(): bool {
		if ( ! class_exists( '\BeltoftCommerce\Frontend\QuickOrderPagePathShim' ) ) {
			return false;
		}

		return \BeltoftCommerce\Frontend\QuickOrderPagePathShim::is_ordering_page();
	}

	/** Everything has to be true for the button to render. */
	public static function applies(): bool {
		return self::is_b2b_host() && self::is_ordering_page() && self::user_may_scan();
	}
}
