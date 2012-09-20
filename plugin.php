<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: T5 Opera Speed Dial Preview
 * Plugin URI:  https://github.com/toscho/T5-Opera-Speed-Dial-Preview
 * Description: Your latest posts and comments in Opera’s Speed Dial preview.
 * Version:     2012.09.20
 * Required:    3.3
 * Author:      Thomas Scholz <info@toscho.de>
 * Author URI:  http://toscho.de
 * License:     MIT
 * License URI: http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright (c) 2012 Thomas Scholz
 */

// Wait until all needed functions are loaded.
add_action( 'init', array ( 'T5_Opera_Speed_Dial', 'get_instance' ) );

add_filter(
	'plugin_row_meta',
	array( 'T5_Opera_Speed_Dial', 'add_feedback_link' ),
	10,
	2
);

/**
 * Creates a dedicated speed dial page.
 *
 * @author Thomas Scholz, <info@toscho.de>
 * @link http://dev.opera.com/articles/view/opera-speed-dial-enhancements/
 *
 */
class T5_Opera_Speed_Dial
{
	/**
	 * Current plugin instance.
	 *
	 * @since 2012.09.20
	 * @see get_instance()
	 * @type NULL|object
	 */
	protected static $instance = NULL;

	/**
	 * Internal handler for WordPress
	 *
	 * @type string
	 */
	protected static $query_var = 'speeddial';

	/**
	 * Creates a new instance.
	 *
	 * @wp-hook init
	 * @see     __construct()
	 * @return  void
	 */
	public static function get_instance()
	{
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Set actions, filters and basic variables, load language.
	 *
	 * @wp-hook init
	 */
	public function __construct()
	{
		add_rewrite_endpoint( self::$query_var, EP_ROOT );
		add_filter( 'request', array ( $this, 'set_query_var' ) );
		// Hook in late to allow other plugins to operate earlier.
		add_action( 'template_redirect', array ( $this, 'render' ), 100 );
	}

	/**
	 * Set the endpoint variable to TRUE.
	 *
	 * If the endpoint was called without further parameters it does not
	 * evaluate to TRUE otherwise.
	 *
	 * @wp-hook request
	 * @since   2012.09.20
	 * @param   array $vars
	 * @return  array
	 */
	public function set_query_var( $vars )
	{
		isset ( $vars[ self::$query_var ] ) and $vars[ self::$query_var ] = TRUE;
		return $vars;
	}

	/**
	 * Redirect to speed dial page if it is a preview request.
	 *
	 * @wp-hook template_redirect
	 * @return void
	 */
	public function redirect()
	{
		isset ( $_SERVER['HTTP_X_PURPOSE'] )
		and 'preview' == $_SERVER['HTTP_X_PURPOSE']
		and ! get_query_var( self::$query_var )
		and wp_redirect( home_url( self::$query_var ) )
		and exit;
	}

	/**
	 * Redirect to speed dial page or print it out if we are already there.
	 *
	 * @wp-hook template_redirect
	 * @return void
	 */
	public function render()
	{
		if ( ! is_front_page() or ! get_query_var( self::$query_var ) )
		{
			return;
		}

		$template = 'speed-dial.php';
		$path     = locate_template( array ( $template ) );

		if ( '' != $path and file_exists( $path ) )
		{
			require $path;
		}
		else
		{
			require dirname( __FILE__ ) . "/$template";
		}
		exit;
	}

	/**
	 * Shortens an UTF-8 encoded string without breaking words.
	 * Template helper.
	 *
	 * @link   http://wordpress.stackexchange.com/q/11085/11089#11089
	 * @param  string $string     String to shorten.
	 * @param  int    $max_chars  Maximal length in characters.
	 * @param  string $append     Replacement for truncated words.
	 * @return string
	 */
	public static function utf8_truncate(
		$string,
		$max_chars = 25,
		$append    = "\xC2\xA0…"
	)
	{
	    $string = strip_tags( $string );
	    $string = html_entity_decode( $string, ENT_QUOTES, 'utf-8' );
	    // \xC2\xA0 is the no-break space
	    $string = trim( $string, "\n\r\t .-;–,—\xC2\xA0" );
	    $length = strlen( utf8_decode( $string ) );

	    // Nothing to do.
	    if ( $length < $max_chars )
	    {
	        return $string;
	    }

	    // mb_substr() is in /wp-includes/compat.php as a fallback if
	    // your the current PHP installation is missing it.
	    $string = mb_substr( $string, 0, $max_chars, 'utf-8' );

	    // No white space. One long word or chinese/korean/japanese text.
	    if ( FALSE === strpos( $string, ' ' ) )
	    {
	        return $string . $append;
	    }

	    // Avoid breaks within words. Find the last white space.
	    if ( extension_loaded( 'mbstring' ) )
	    {
	        $pos   = mb_strrpos( $string, ' ', 'utf-8' );
	        $short = mb_substr( $string, 0, $pos, 'utf-8' );
	    }
	    else
	    {
	        // Workaround. May be slow on long strings.
	        $words = explode( ' ', $string );
	        // Drop the last word.
	        array_pop( $words );
	        $short = implode( ' ', $words );
	    }

	    return $short . $append;
	}

	/**
	 * Adds a link to the GitHub bug tracker.
	 *
	 * @param  array  $links Already existing links.
	 * @return string
	 */
	public static function add_feedback_link( $links, $file )
	{
		static $base_name = '';
		'' === $base_name and $base_name = plugin_basename( __FILE__ );

		if ( $base_name === $file )
		{
			$links[] = "<a href='https://github.com/toscho/T5-Opera-Speed-Dial-Preview/issues'>Send feedback</a>";
		}

		return $links;
	}
}