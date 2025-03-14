<?php
/**
 * Personas Storage Class
 *
 * Handles storage and retrieval of persona preferences.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Storage Class
 *
 * This class is responsible for storing and retrieving persona preferences
 * using cookies or other storage mechanisms.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */
class Personas_Storage {

	/**
	 * Instance of the class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Storage    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Cookie name for storing persona preference.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      string    $cookie_name    The name of the cookie for storing persona preference.
	 */
	private $cookie_name = 'cme_persona';

	/**
	 * Cookie expiration time in days.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      int    $cookie_expiration    Cookie expiration time in days.
	 */
	private $cookie_expiration = 30;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.6.0
	 * @return    Personas_Storage    The singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since    1.6.0
	 */
	private function __construct() {
		// Initialize settings.
		$this->cookie_name       = apply_filters( 'cme_persona_cookie_name', $this->cookie_name );
		$this->cookie_expiration = apply_filters( 'cme_persona_cookie_expiration', $this->cookie_expiration );
	}

	/**
	 * Get the persona from cookie.
	 *
	 * @since     1.6.0
	 * @return    string|null    The persona from cookie, or null if not found.
	 */
	public function get_persona_from_cookie() {
		if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return sanitize_text_field( wp_unslash( $_COOKIE[ $this->cookie_name ] ) );
		}

		return null;
	}

	/**
	 * Set the persona cookie.
	 *
	 * @since     1.6.0
	 * @param     string $persona_id    The persona identifier to store in the cookie.
	 * @return    void
	 */
	public function set_persona_cookie( $persona_id ) {
		$expiration = time() + ( $this->cookie_expiration * DAY_IN_SECONDS );
		setcookie( $this->cookie_name, $persona_id, $expiration, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
	}

	/**
	 * Clear the persona cookie.
	 *
	 * @since     1.6.0
	 * @return    void
	 */
	public function clear_persona_cookie() {
		if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
			setcookie( $this->cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
		}
	}

	/**
	 * Get the cookie name.
	 *
	 * @since     1.6.0
	 * @return    string    The cookie name.
	 */
	public function get_cookie_name() {
		return $this->cookie_name;
	}

	/**
	 * Get the cookie expiration.
	 *
	 * @since     1.6.0
	 * @return    int    The cookie expiration in days.
	 */
	public function get_cookie_expiration() {
		return $this->cookie_expiration;
	}
}
