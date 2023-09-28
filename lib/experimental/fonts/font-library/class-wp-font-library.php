<?php
/**
 * Font Library class.
 *
 * This file contains the Font Library class definition.
 *
 * @package    WordPress
 * @subpackage Font Library
 * @since      6.4.0
 */

if ( class_exists( 'WP_Font_Library' ) ) {
	return;
}

/**
 * Font Library class.
 *
 * @since 6.4.0
 */
class WP_Font_Library {

	const PHP_7_TTF_MIME_TYPE = PHP_VERSION_ID >= 70300 ? 'application/font-sfnt' : 'application/x-font-ttf';

	const ALLOWED_FONT_MIME_TYPES = array(
		'otf'   => 'font/otf',
		'ttf'   => PHP_VERSION_ID >= 70400 ? 'font/sfnt' : self::PHP_7_TTF_MIME_TYPE,
		'woff'  => PHP_VERSION_ID >= 80100 ? 'font/woff' : 'application/font-woff',
		'woff2' => PHP_VERSION_ID >= 80100 ? 'font/woff2' : 'application/font-woff2',
	);

	/**
	 * Font collections.
	 *
	 * @since 6.4.0
	 *
	 * @var array
	 */
	private static $collections   = array();

	/**
	 * Register a new font collection.
	 *
	 * @since 6.4.0
	 *
	 * @param array $config Font collection config options.
	 *                      See {@see wp_register_font_collection()} for the supported fields.
	 * @return WP_Font_Collection|WP_Error A font collection is it was registered successfully and a WP_Error otherwise.
	 */
	public static function register_font_collection( $config ) {
		$new_collection = new WP_Font_Collection( $config );

		if ( isset( self::$collections[ $config['id'] ] ) ) {
			return new WP_Error( 'font_collection_registration_error', 'Font collection already registered.' );
		}

		self::$collections[ $config['id'] ] = $new_collection;
		return $new_collection;
	}

	/**
	 * Adds fonts to theme.json as default font families.
	 *
	 * @since 6.4.0
	 *
	 * @param WP_Theme_JSON $theme_json Theme JSON object.
	 * @return WP_Theme_JSON Modified theme JSON object.
	 */
	public static function add_default_fonts_to_theme_json( $theme_json ) {

		$default_font_collection_data = array();

		foreach ( self::get_font_collections() as $font_collection ) {
			$collection_config = $font_collection->get_config();
			if ( ! isset( $collection_config['default'] ) ) {
				continue;
			}
			$collection_item = self::get_font_collection($collection_config['id']);
			$collection_data = $collection_item->get_data()['data'];
			$default_font_collection_data = array_merge( $default_font_collection_data, $collection_data['fontFamilies'] );
		}

		if ( empty( $default_font_collection_data) ) {
			return $theme_json;
		}

		$current_data = $theme_json->get_data();

		if ( ! isset( $current_data['version'] ) || $current_data['version'] !== 2 ) {
			return $theme_json;
		}

		// get currently available font families
		$default_font_families = $current_data['settings']['typography']['fontFamilies']['default'] ?? array();
		$default_font_families = array_merge( $default_font_families,  $default_font_collection_data );

		// add font families to json structure
		$new_data = array(
			'version'  => 2,
			'settings' => array(
				'typography' => array(
					'fontFamilies' => array(
						'default' => $default_font_families,
					),
				),
			),
		);

		return $theme_json->update_with( $new_data );
	}

	/**
	 * Gets all the font collections available.
	 *
	 * @since 6.4.0
	 *
	 * @return array List of font collections.
	 */
	public static function get_font_collections() {
		return self::$collections;
	}

	/**
	 * Gets a font collection.
	 *
	 * @since 6.4.0
	 *
	 * @param string $id Font collection id.
	 * @return array List of font collections.
	 */
	public static function get_font_collection( $id ) {
		if ( array_key_exists( $id, self::$collections ) ) {
			return self::$collections[ $id ];
		}
		return new WP_Error( 'font_collection_not_found', 'Font collection not found.' );
	}

	/**
	 * Gets the upload directory for fonts.
	 *
	 * @since 6.4.0
	 *
	 * @return string Path of the upload directory for fonts.
	 */
	public static function get_fonts_dir() {
		return path_join( WP_CONTENT_DIR, 'fonts' );
	}

	/**
	 * Sets the upload directory for fonts.
	 *
	 * @since 6.4.0
	 *
	 * @param array $defaults {
	 *     Default upload directory.
	 *
	 *     @type string $path    Path to the directory.
	 *     @type string $url     URL for the directory.
	 *     @type string $subdir  Sub-directory of the directory.
	 *     @type string $basedir Base directory.
	 *     @type string $baseurl Base URL.
	 * }
	 * @return array Modified upload directory.
	 */
	public static function set_upload_dir( $defaults ) {
		$defaults['basedir'] = WP_CONTENT_DIR;
		$defaults['baseurl'] = content_url();
		$defaults['subdir']  = '/fonts';
		$defaults['path']    = self::get_fonts_dir();
		$defaults['url']     = $defaults['baseurl'] . '/fonts';

		return $defaults;
	}

	/**
	 * Sets the allowed mime types for fonts.
	 *
	 * @since 6.4.0
	 *
	 * @param array $mime_types List of allowed mime types.
	 * @return array Modified upload directory.
	 */
	public static function set_allowed_mime_types( $mime_types ) {
		return array_merge( $mime_types, self::ALLOWED_FONT_MIME_TYPES );
	}
}
