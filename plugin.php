<?php # -*- coding: utf-8 -*-
declare ( encoding = 'UTF-8' );
/**
 * Plugin Name: T5 Opera Speed Dial Preview
 * Description: Your latest posts and comments in Opera’s Speed Dial preview.
 * Version:     2012.03.04
 * Required:    3.3
 * Author:      Thomas Scholz <info@toscho.de>
 * Author URI:  http://toscho.de
 * License:     MIT
 * License URI: http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright (c) 2012 Thomas Scholz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Not a WordPress context? Stop.
! defined( 'ABSPATH' ) and exit;

// Wait until all needed functions are loaded.
add_action( 'plugins_loaded', array ( 'T5_Opera_Speed_Dial', 'init' ) );

register_activation_hook(
	__FILE__,
	array ( 'T5_Opera_Speed_Dial', 'set_rewrite_rule' )
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
	 * Internal handler for WordPress
	 *
	 * @type string
	 */
	protected static $query_var = 'speeddial';

	/**
	 * Creates a new instance.
	 *
	 * @wp-hook plugins_loaded
	 * @see     __construct()
	 * @return  void
	 */
	public static function init()
	{
		new self;
	}

	/**
	 * Set actions, filters and basic variables, load language.
	 */
	public function __construct()
	{
		add_filter( 'query_vars', array ( $this, 'add_query_var' ) );
		// Hook in late to allow other plugins to operate earlier.
		add_action( 'template_redirect', array ( $this, 'render' ), 100 );
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
	 * Register an URI.
	 *
	 * @wp-hook activation
	 * @return  void
	 */
	public static function set_rewrite_rule()
	{
		add_rewrite_rule(
			'^' . self::$query_var . '$',
			'index.php?' . self::$query_var . '=1',
			'top'
		);
		flush_rewrite_rules();
	}

	/**
	 * Register our query var.
	 *
	 * @wp-hook query_vars
	 * @param array $vars Existing query vars
	 * @return array
	 */
	public function add_query_var( $vars )
	{
		$vars[] = self::$query_var;
		return $vars;
	}

	/**
	 * Redirect to speed dial page or print it out if we are already there.
	 *
	 * @wp-hook template_redirect
	 * @return void
	 */
	public function render()
	{
		if ( ! get_query_var( self::$query_var ) )
		{
			$this->redirect();
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
}