<?php
#
#  Copyright (c) 2007-2009 Ryan McGeary
#
#  This file is part of WP-Syntax.
#
#  WP-Syntax is free software; you can redistribute it and/or modify it under
#  the terms of the GNU General Public License as published by the Free
#  Software Foundation; either version 2 of the License, or (at your option)
#  any later version.
#
#  WP-Syntax is distributed in the hope that it will be useful, but WITHOUT ANY
#  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
#  FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
#  details.
#
#  You should have received a copy of the GNU General Public License along
#  with WP-Syntax; if not, write to the Free Software Foundation, Inc., 59
#  Temple Place, Suite 330, Boston, MA 02111-1307 USA
#

if ( ! defined( "WP_CONTENT_URL" ) ) {
	define( "WP_CONTENT_URL", get_option( "siteurl" ) . "/wp-content" );
}
if ( ! defined( "WP_PLUGIN_URL" ) ) {
	define( "WP_PLUGIN_URL", WP_CONTENT_URL . "/plugins" );
}


if ( ! function_exists( 'wp_syntax_code_trim' ) ) {
	function wp_syntax_code_trim( $code ) {
		// special ltrim b/c leading whitespace matters on 1st line of content
		$code = preg_replace( "/^\s*\n/siU", "", $code );
		$code = rtrim( $code );

		return $code;
	}
}
if ( ! function_exists( 'wp_syntax_substitute' ) ) {
	function wp_syntax_substitute( &$match ) {
		global $wp_syntax_token, $wp_syntax_matches;

		$i                       = count( $wp_syntax_matches );
		$wp_syntax_matches[ $i ] = $match;

		return "\n\n<p>" . $wp_syntax_token . sprintf( "%03d", $i ) . "</p>\n\n";
	}
}

function wp_syntax_highlight_mpdf( $match ) {
	global $wp_syntax_matches;

	$i     = intval( $match[1] );
	$match = $wp_syntax_matches[ $i ];

	$language = strtolower( trim( $match[1] ) );
	$line     = trim( $match[2] );
	$escaped  = trim( $match[3] );
	$code     = wp_syntax_code_trim( $match[4] );
	$code     = htmlspecialchars_decode( $code );

	$geshi = new GeSHi( $code, $language );
	$geshi->enable_keyword_links( false );
	$geshi->set_header_type( GESHI_HEADER_DIV );
	$geshi->set_tab_width( 4 );
	do_action_ref_array( 'wp_syntax_init_geshi', array( &$geshi ) );

	$output = "\n<div class=\"wp_syntax\">";

	//Beim Printen immer Line numbern anmachen
	$line = get_option( 'mpdf_geshi_linenumbers' );
	if ( $line ) {
		$lineMode = explode( "\n", $code );

		$output .= '<table>';
		for ( $i = 0; $i < count( $lineMode ); $i ++ ) {
			$geshi->set_source( $lineMode[ $i ] );

			if ( $i % 2 ) {
				$output .= '<tr style="background-color: #f5f5f5;">';
			} else {
				$output .= '<tr>';
			}
			$output .= '<td class="line_numbers" style="vertical-align: top;">';
			if ( ( $i + 1 ) % 5 ) {
				$output .= ( $i + 1 );
			} else {
				$output .= '<b>' . ( $i + 1 ) . '</b>';
			}
			$output .= '</td><td class="code">';
			$output .= $geshi->parse_code();
			$output .= '</td></tr>';
		}
		$output .= '</table>';
	} else {
		$output .= "<div class=\"code\">";
		$output .= $geshi->parse_code();
		$output .= "</div>";
	}

	return

		$output .= "</div>\n";

	return $output;
}

if ( ! function_exists( 'wp_syntax_before_filter' ) ) {
	function wp_syntax_before_filter( $content ) {
		return preg_replace_callback(
			"/\s*<pre(?:lang=[\"']([\w-]+)[\"']|line=[\"'](\d*)[\"']|escaped=[\"'](true|false)?[\"']|\s)+>(.*)<\/pre>\s*/siU",
			"wp_syntax_substitute",
			$content
		);
	}
}

function wp_syntax_after_filter_mpdf( $content ) {
	global $wp_syntax_token;

	$content = preg_replace_callback(
		"/<p>\s*" . $wp_syntax_token . "(\d{3})\s*<\/p>/si",
		"wp_syntax_highlight_mpdf",
		$content
	);

	return $content;
}

$wp_syntax_token = md5( uniqid( rand() ) );
