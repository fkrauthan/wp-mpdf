<?php
/*
Plugin Name: wp-mpdf
Plugin URI: https://www.fkrauthan.ca/projects/php
Description: Print a wordpress page as PDF with optional Geshi Parsing.
Version: 3.9
Author: Florian 'fkrauthan' Krauthan
Author URI: http://fkrauthan.ca

Copyright 2024  Florian Krauthan
*/

/*
 * This file is part of wp-mpdf.
 * wp-mpdf is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * wp-mpdf is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with wp-mpdf. If not, see <http://www.gnu.org/licenses/>.
 */

define( 'WP_MPDF_ALLOWED_POSTS_DB', 'wp_mpdf_allowed' );
define( 'WP_MPDF_POSTS_DB', 'wp_mpdf_posts' );

function mpdf_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;
	if ( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name . '"' ) != $table_name ) {
		$sql = 'CREATE TABLE ' . $table_name . ' (
	  		id mediumint(9) NOT NULL AUTO_INCREMENT,
	  		post_type VARCHAR(4) DEFAULT "post" NOT NULL,
	  		post_id mediumint(9) NOT NULL,
	  		general smallint(1) NOT NULL,
	  		login smallint(1) NOT NULL,
	  		pdfname VARCHAR(255) DEFAULT "" NOT NULL,
	  		downloads int(11) NOT NULL,
	  		UNIQUE KEY id (id)
		);';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );


		add_option( 'mpdf_theme', 'default' );
		add_option( 'mpdf_geshi', false );
		add_option( 'mpdf_geshi_linenumbers', true );
		add_option( 'mpdf_caching', true );
		add_option( 'mpdf_allow_all', true );
		add_option( 'mpdf_need_login', false );
		add_option( 'mpdf_stats', false );
		add_option( 'mpdf_debug', false );
		add_option( 'mpdf_cron_user', '' );
		add_option( 'mpdf_code_page', 'utf-8' );
	}


	$table_name_old = $wpdb->prefix . WP_MPDF_ALLOWED_POSTS_DB;
	if ( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name_old . '"' ) == $table_name_old ) {
		//Copie old datas
		$sql  = 'SELECT id,post_type,post_id,enabled FROM ' . $table_name_old;
		$data = $wpdb->get_results( $sql, OBJECT );
		foreach ( $data as $dsatz ) {
			$sql = 'INSERT INTO ' . $table_name . ' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, %d, %d, %s, 0)';
			$wpdb->query( $wpdb->prepare( $sql, $dsatz->post_type, $dsatz->post_id, $dsatz->enabled, false, '' ) );
		}

		//Remove table
		$wpdb->query( 'DROP TABLE ' . $table_name_old );
	}

	if ( ! is_dir( WP_CONTENT_DIR . '/wp-mpdf-themes' ) ) {
		if ( ! @mkdir( WP_CONTENT_DIR . '/wp-mpdf-themes' ) ) {
			echo '<p>Can\'t create mpdf themes dir. Please create the dir "wp-content/wp-mpdf-themes" and give your webserver write permission to it.</p>';
		}
	}
}

function mpdf_deactivate () {
	wp_clear_scheduled_hook( 'mpdf_generate_pdfs_hook' );
}

function mpdf_cron_generate_pdfs_exec() {
	require_once( dirname( __FILE__ ) . '/wp-mpdf_cron.php' );
	mpdf_cron_generate_pdfs();
}

function mpdf_filename( $filename ) {
	return str_replace( '.' . mpdf_extension( $filename ), '', $filename );
}

function mpdf_extension( $filename ) {
	$filename = substr( $filename, strrpos( $filename, '.' ) );

	return strtolower( str_replace( '.', '', $filename ) );
}

function mpdf_output( $wp_content = '', $do_pdf = false, $outputToBrowser = true, $pdfName = '', $templatePath = '' ) {
	if ( is_readable( dirname(__FILE__) . '/vendor/autoload.php' ) ) {
		require dirname(__FILE__) . '/vendor/autoload.php';
	}

	global $post;
	$pdf_ofilename = $post->post_name . '.pdf';
	if ( ! empty( $pdfName ) ) {
		$pdf_filename = $pdfName . '.pdf';
	} else {
		$pdf_filename = $pdf_ofilename;
	}

	/**
	 * Allow to override the pdf file name
	 */
	$pdf_filename = sanitize_file_name(apply_filters( 'mpdf_output_pdf_filename', $pdf_filename ));

	/**
	 * Geshi Support
	 */
	if ( get_option( 'mpdf_geshi' ) == true ) {
		require_once( dirname( __FILE__ ) . '/geshi.inc.php' );
		$wp_content = ParseGeshi( $wp_content );
	}

	/**
	 * Run the content default filter
	 */
	$wp_content = apply_filters( 'the_content', $wp_content );

	/**
	 * Run the mpdf filter
	 */
	$wp_content = mpdf_filter( $wp_content, $do_pdf );


	if ( $do_pdf === false ) {
		echo $wp_content;
	} else {
		$cacheDirectory = mpdf_getcachedir();
		if ( ! is_dir( $cacheDirectory . 'tmp' ) ) {
			@mkdir( $cacheDirectory . 'tmp' );
		}

		global $pdf_margin_left;
		global $pdf_margin_right;
		global $pdf_margin_top;
		global $pdf_margin_bottom;
		global $pdf_margin_header;
		global $pdf_margin_footer;

		global $pdf_html_header;
		global $pdf_html_footer;

		global $pdf_format;
		if ( $pdf_format == '' ) {
			$pdf_format = 'A4';
		}

		if ( $pdf_margin_left !== 0 && $pdf_margin_left == '' ) {
			$pdf_margin_left = 15;
		}
		if ( $pdf_margin_right !== 0 && $pdf_margin_right == '' ) {
			$pdf_margin_right = 15;
		}
		if ( $pdf_margin_top !== 0 && $pdf_margin_top == '' ) {
			$pdf_margin_top = 16;
		}
		if ( $pdf_margin_bottom !== 0 && $pdf_margin_bottom == '' ) {
			$pdf_margin_bottom = 16;
		}
		if ( $pdf_margin_header !== 0 && $pdf_margin_header == '' ) {
			$pdf_margin_header = 9;
		}
		if ( $pdf_margin_footer !== 0 && $pdf_margin_footer == '' ) {
			$pdf_margin_footer = 9;
		}
		if ( empty( $pdf_html_header ) ) {
			$pdf_html_header = false;
		}
		if ( empty( $pdf_html_footer ) ) {
			$pdf_html_footer = false;
		}

		global $pdf_orientation;
		if ( $pdf_orientation == '' ) {
			$pdf_orientation = 'P';
		}

		$cp = 'utf-8';
		if ( get_option( 'mpdf_code_page' ) != '' ) {
			$cp = get_option( 'mpdf_code_page' );
		}

		$mpdf = new \Mpdf\Mpdf(array(
			'tempDir' => $cacheDirectory . 'tmp/',

			'format' => $pdf_format,
			'orientation' => $pdf_orientation,

			'margin_left' => $pdf_margin_left,
			'margin_right' => $pdf_margin_right,
			'margin_top' => $pdf_margin_top,
			'margin_bottom' => $pdf_margin_bottom,
			'margin_header' => $pdf_margin_header,
			'margin_footer' => $pdf_margin_footer,
		));

		$mpdf->charset_in = $cp;
		$mpdf->title2annots = false;
		//$mpdf->annotMargin = 12;
		$mpdf->onlyCoreFonts = true;    // false is default
		$mpdf->SetBasePath( $templatePath );

		//Set PDF Template if it's set
		global $pdf_template_pdfpage;
		global $pdf_template_pdfpage_page;
		global $pdf_template_pdfdoc;
		if ( isset( $pdf_template_pdfdoc ) && $pdf_template_pdfdoc != '' ) {
			$mpdf->SetDocTemplate( $templatePath . $pdf_template_pdfdoc, true );
		} else if ( isset( $pdf_template_pdfpage ) && $pdf_template_pdfpage != '' && isset( $pdf_template_pdfpage_page ) && is_numeric( $pdf_template_pdfpage_page ) ) {
			$pagecount = $mpdf->SetSourceFile( $templatePath . $pdf_template_pdfpage );
			if ( $pdf_template_pdfpage_page < 1 ) {
				$pdf_template_pdfpage_page = 1;
			} else if ( $pdf_template_pdfpage_page > $pagecount ) {
				$pdf_template_pdfpage_page = $pagecount;
			}
			$tplId = $mpdf->ImportPage( $pdf_template_pdfpage_page );
			$mpdf->UseTemplate( $tplId );
		}


		$user_info = get_userdata( $post->post_author );
		$mpdf->SetAuthor( $user_info->first_name . ' ' . $user_info->last_name . ' (' . $user_info->user_login . ')' );
		$mpdf->SetCreator( 'wp-mpdf' );


		//The Header and Footer
		global $pdf_footer;
		global $pdf_header;

		if ( $pdf_html_header ) {
			$mpdf->SetHTMLHeader( $pdf_header );
		} else {
			$mpdf->setHeader( $pdf_header );
		}
		if ( $pdf_html_footer ) {
			$mpdf->SetHTMLFooter( $pdf_footer );
		} else {
			$mpdf->setFooter( $pdf_footer );
		}


		/**
		 * Allow to override any settings before writing the output
		 */
		$mpdf = apply_filters( 'mpdf_adjust_settings', $mpdf );


		if ( get_option( 'mpdf_theme' ) != '' && file_exists( $templatePath . get_option( 'mpdf_theme' ) . '.css' ) ) {
			//Read the StyleCSS
			$tmpCSS = file_get_contents( $templatePath . get_option( 'mpdf_theme' ) . '.css' );
			$mpdf->WriteHTML( $tmpCSS, \Mpdf\HTMLParserMode::HEADER_CSS );
		}

		//My Filters
		require_once( dirname( __FILE__ ) . '/myfilters.inc.php' );
		$wp_content = mpdf_myfilters( $wp_content );

		if ( get_option( 'mpdf_debug' ) == true ) {
			if ( ! is_dir( plugin_dir_path( __FILE__ ) . 'debug/' ) ) {
				mkdir( plugin_dir_path( __FILE__ ) . 'debug/' );
			}
			file_put_contents( plugin_dir_path( __FILE__ ) . 'debug/' . get_option( 'mpdf_theme' ) . '_' . $pdf_ofilename . '.html', $wp_content );
		}

		//die($wp_content);
		$mpdf->WriteHTML( $wp_content );

		/**
		 * Allow to process the pdf by an 3th party plugin
		 */
		do_action( 'mpdf_output', $mpdf, $pdf_filename );

		if ( get_option( 'mpdf_caching' ) == true ) {
			file_put_contents( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $pdf_ofilename . '.cache', $post->post_modified_gmt );
			$mpdf->Output( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $pdf_ofilename, 'F' );
			if ( $outputToBrowser == true ) {
				$mpdf->Output( $pdf_filename, \Mpdf\Output\Destination::INLINE );
			}
		} else {
			if ( $outputToBrowser == true ) {
				$mpdf->Output( $pdf_filename, \Mpdf\Output\Destination::INLINE );
			}
		}
	}
}

function mpdf_filter( $wp_content = '', $do_pdf = false, $convert = false ) {
	$delimiter1 = 'screen';
	$delimiter2 = 'print';

	if ( $do_pdf === false ) {
		$d1a = '[' . $delimiter1 . ']';
		$d1b = '[/' . $delimiter1 . ']';
		$d2a = '\[' . $delimiter2 . '\]';
		$d2b = '\[\/' . $delimiter2 . '\]';
	} else {
		$d1a = '[' . $delimiter2 . ']';
		$d1b = '[/' . $delimiter2 . ']';
		$d2a = '\[' . $delimiter1 . '\]';
		$d2b = '\[\/' . $delimiter1 . '\]';
	}

	$wp_content = str_replace( $d1a, '', $wp_content );
	$wp_content = str_replace( $d1b, '', $wp_content );

	$wp_content = preg_replace( "/$d2a(.*?)$d2b/s", '', $wp_content );
	$wp_content = preg_replace( "/\[mpdfbutton[^\]]*\]/s", '', $wp_content );

	if ( $convert == true ) {
		$wp_content = mb_convert_encoding( $wp_content, "ISO-8859-1", "UTF-8" );
	}

	return $wp_content;
}

function mpdf_mysql2unix( $timestamp ) {
// stolen cold-blooded from the Polyglot plugin
	$year   = substr( $timestamp, 0, 4 );
	$month  = substr( $timestamp, 5, 2 );
	$day    = substr( $timestamp, 8, 2 );
	$hour   = substr( $timestamp, 11, 2 );
	$minute = substr( $timestamp, 14, 2 );
	$second = substr( $timestamp, 17, 2 );

	return mktime( $hour, $minute, $second, $month, $day, $year );
}

function mpdf_pdfbutton_shortcode($atts = [], $content = null, $tag = '') {
	$atts = array_change_key_case((array) $atts, CASE_LOWER);
	$mpdf_atts = shortcode_atts(
		array(
			'opennewtab' => false,
			'buttontext' => '',
			'logintext' => '',
			'nofollow' => false,
		), $atts, $tag
	);

	return mpdf_pdfbutton($mpdf_atts['opennewtab'], $mpdf_atts['buttontext'], $mpdf_atts['logintext'], false,  $mpdf_atts['nofollow']);
}

function mpdf_shortcodes_init() {
	add_shortcode( 'mpdfbutton', 'mpdf_pdfbutton_shortcode' );
}

function mpdf_pdfbutton( $opennewtab = false, $buttontext = '', $logintext = 'Login!', $print_button = true, $nofollow = false, $options = array() ) {
	$nofollowHtml = '';
	if ( $nofollow ) {
		$nofollowHtml = 'rel="nofollow" ';
	}

	//Check if button should displayed
	if ( get_option( 'mpdf_allow_all' ) != 1 || get_option( 'mpdf_need_login' ) != 0 ) {
		global $wpdb;
		global $post;
		$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;
		$sql        = 'SELECT general,login FROM ' . $table_name . ' WHERE post_id=' . $post->ID . ' AND post_type="' . $post->post_type . '" LIMIT 1';
		$dsatz      = $wpdb->get_row( $sql ) ?: _mpdf_default_post();

		if ( get_option( 'mpdf_allow_all' ) == 2 && isset( $dsatz->general ) && $dsatz->general == false || get_option( 'mpdf_allow_all' ) == 3 && $dsatz->general == true ) {
			return;
		} else if ( ( get_option( 'mpdf_need_login' ) == 2 && $dsatz->login == false || get_option( 'mpdf_need_login' ) == 3 && $dsatz->login == true ) && is_user_logged_in() != true ) {
			if ( empty( $buttontext ) ) {
				$image = plugin_dir_url( __FILE__ ) . 'pdf_lock.png';
				if ( isset( $options['pdf_lock_image'] ) ) {
					$image = get_bloginfo( 'wpurl' ) . $options['pdf_lock_image'];
				}

				$buttontext = '<img src="' . $image . '" alt="' . __( $logintext, 'wp-mpdf' ) . '" title="' . __( 'You must login first', 'wp-mpdf' ) . '" border="0" />';
			} else {
				$buttontext = __( $logintext, 'wp-mpdf' );
			}

			$pdf_button = '<a ' . $nofollowHtml . 'class="pdfbutton loginfirst" href="' . wp_login_url( get_permalink() ) . '" title="' . __( 'You must login first', 'wp-mpdf' ) . '">' . $buttontext . '</a>';

			if ( $print_button === true ) {
				echo $pdf_button;

				return;
			} else {
				return $pdf_button;
			}
		}
	}


	//Print the button
	if ( empty( $buttontext ) ) {
		$image = plugin_dir_url( __FILE__ ) . 'pdf.png';
		if ( isset( $options['pdf_image'] ) ) {
			$image = get_bloginfo( 'wpurl' ) . $options['pdf_image'];
		}

		$buttontext = '<img src="' . $image . '" alt="' . __( 'This page as PDF', 'wp-mpdf' ) . '" border="0" />';
	}

	$x          = ! strpos( apply_filters( 'the_permalink', get_permalink() ), '?' ) ? '?' : '&amp;';
	$pdf_button = '<a ' . $nofollowHtml;
	if ( $opennewtab == true ) {
		$pdf_button .= 'target="_blank" ';
	}
	$pdf_button .= 'class="pdfbutton" href="' . apply_filters( 'the_permalink', get_permalink() ) . $x . 'output=pdf">' . __( $buttontext, 'wp-mpdf' ) . '</a>';

	if ( $print_button === true ) {
		echo $pdf_button;
	} else {
		return $pdf_button;
	}
}

function mpdf_getcachedir() {
	$directory = plugin_dir_path( __FILE__ ) . '/cache/';
	if ( ! is_dir( $directory ) || ! is_writable( $directory ) ) {
		$directory = WP_CONTENT_DIR . '/wp-mpdf-themes/cache/';
		if ( ! is_dir( $directory ) || ! is_writable( $directory ) ) {
			die( 'wp-mpdf can\'t access cache directory. Please verify your setup!' );
		}
	}

	return $directory;
}

function mpdf_readcachedfile( $name, $pdfname ) {
	$fp = fopen( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $name, 'rb' );
	if ( ! $fp ) {
		die( 'Couldn\'t Read cache file' );
	}
	fclose( $fp );

	Header( 'Content-Type: application/pdf' );
	Header( 'Content-Length: ' . filesize( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $name ) );
	Header( 'Content-disposition: inline; filename=' . $pdfname );

	echo file_get_contents( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $name, FILE_BINARY | FILE_USE_INCLUDE_PATH );
}

function mpdf_exec( $outputToBrowser = '' ) {
	if ( $outputToBrowser == '' ) {
		$outputToBrowser = true;
	} else {
		$outputToBrowser = false;
	}

	/**
	 * Allow to override the outputToBrowser variable
	 */
	$outputToBrowser = apply_filters( 'mpdf_exec_outputToBrowser', $outputToBrowser );

	if ( isset( $_GET['output'] ) && $_GET['output'] == 'pdf' ) {
		//Check if this Page is allowed to print as PDF
		global $wpdb;
		global $post;
		$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;
		$sql        = 'SELECT id,general,login,pdfname FROM ' . $table_name . ' WHERE post_id=%d AND post_type=%s LIMIT 1';
		$dsatz      = $wpdb->get_row( $wpdb->prepare( $sql, $post->ID, $post->post_type ) ) ?: _mpdf_default_post();

		if ( post_password_required( $post ) ) {
			return;
		} else if ( get_option( 'mpdf_allow_all' ) == 2 && $dsatz->general == false || get_option( 'mpdf_allow_all' ) == 3 && $dsatz->general == true ) {
			return;
		} else if ( ( get_option( 'mpdf_need_login' ) == 2 && $dsatz->login == false || get_option( 'mpdf_need_login' ) == 3 && $dsatz->login == true ) && is_user_logged_in() != true && $outputToBrowser == true ) {
			wp_redirect( wp_login_url( get_permalink() ) );

			return;
		}

		//Update download stats if enabled
		if ( get_option( 'mpdf_stats' ) == true ) {
			if ( $dsatz->id == null ) {
				$sql = 'INSERT INTO ' . $table_name . ' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, %d, %d, %s, 1)';
				$wpdb->query( $wpdb->prepare( $sql, $post->post_type, $post->ID, false, false, '' ) );
			} else {
				$sql = 'UPDATE ' . $table_name . ' SET downloads=downloads+1 WHERE id=%d LIMIT 1';
				$wpdb->query( $wpdb->prepare( $sql, $dsatz->id ) );
			}
		}

		//Check for Caching option
		if ( get_option( 'mpdf_caching' ) == true ) {
			$pdf_filename = $post->post_name . '.pdf';
			if ( file_exists( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $pdf_filename . '.cache' ) && file_exists( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $pdf_filename ) ) {
				$createDate = file_get_contents( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $pdf_filename . '.cache' );
				if ( $createDate == $post->post_modified_gmt ) {
					//We could Read the Cached file
					if ( $outputToBrowser == true ) {
						if ( ! empty( $dsatz->pdfname ) ) {
							mpdf_readcachedfile( $pdf_filename, $dsatz->pdfname . '.pdf' );
						} else {
							mpdf_readcachedfile( $pdf_filename, $pdf_filename );
						}
						exit;
					} else {
						return;
					}
				}
			}
		}

		$templatePath = WP_CONTENT_DIR . '/wp-mpdf-themes/';
		$templateFile = $templatePath . get_option( 'mpdf_theme' ) . '.php';
		if ( ! file_exists( $templateFile ) ) {
			$templatePath = plugin_dir_path( __FILE__ ) . 'themes/';
			$templateFile = $templatePath . get_option( 'mpdf_theme' ) . '.php';
		}

		$pdfName = isset( $dsatz->pdfname ) ? $dsatz->pdfname : '';

		$pdf_output = '';
		require( $templateFile );
		mpdf_output( $pdf_output, true, $outputToBrowser, $pdfName, $templatePath );

		if ( $outputToBrowser == true ) {
			exit;
		}
	}
}

function mpdf_admin() {
	require_once( dirname( __FILE__ ) . '/wp-mpdf_admin.php' );
	mpdf_admin_display();
}

function mpdf_create_admin_menu() {
	add_options_page( 'WP-MPDF Options', 'WP-MPDF', 'manage_options', 'wp-mpdf', 'mpdf_admin' );

	if ( function_exists( 'add_meta_box' ) ) {
		add_meta_box( 'mpdf_admin', 'wp-mpdf', 'mpdf_admin_printeditbox', 'post', 'normal', 'high' );
		add_meta_box( 'mpdf_admin', 'wp-mpdf', 'mpdf_admin_printeditbox', 'page', 'normal', 'high' );
	} else {
		add_action( 'dbx_post_advanced', 'mpdf_admin_printeditbox_old' );
		add_action( 'dbx_page_advanced', 'mpdf_admin_printeditbox_old' );
	}
}

function _mpdf_default_post() {
	return (object) array(
		'id' => null,
		'login' => false,
		'general' => false,
		'pdfname' => '',
	);
}

function mpdf_admin_printeditbox() {
	global $wpdb;
	global $post;

	$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;
	$sql        = 'SELECT * FROM ' . $table_name . ' WHERE post_id=' . $post->ID . ' AND post_type="' . $post->post_type . '" LIMIT 1';
	$datas      = $wpdb->get_row( $sql ) ?: _mpdf_default_post();

	echo '<input type="hidden" name="wp_mpdf_noncename" id="wp_mpdf_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';


	echo '<table border="0">';
	echo '<tr><td>Put on whitelist/blacklist:</td><td><input ';
	if ( $datas->general == true ) {
		echo 'checked="checked" ';
	}
	echo 'type="checkbox" name="wp_mpdf_candownload" /></td></tr>';

	echo '<tr><td>' . __( 'Put on whitelist/blacklist for need Login', 'wp-mpdf' ) . '</td><td><input ';
	if ( $datas->login == true ) {
		echo 'checked="checked" ';
	}
	echo 'type="checkbox" name="wp_mpdf_needlogin" /></td></tr>';

	echo '<tr><td>' . __( 'Set a special PDF output name', 'wp-mpdf' ) . ':</td><td><input type="text" name="wp_mpdf_pdfname" value="';
	echo esc_attr($datas->pdfname);
	echo '" /> (' . __( 'without .pdf at the end', 'wp-mpdf' ) . ')</td></tr>';
	echo '</table>';
}

/* Prints the edit form for pre-WordPress 2.5 post/page */
function mpdf_admin_printeditbox_old() {

	echo '<div class="dbx-b-ox-wrapper">' . "\n";
	echo '<fieldset id="mpdf_admin" class="dbx-box">' . "\n";
	echo '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">wp-mpdf</h3></div>';

	echo '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';

	// output editing form

	mpdf_admin_printeditbox();

	// end wrapper

	echo '</div></div></fieldset></div>' . "\n";
}


function mpdf_admin_savepost( $post_id ) {
	if ( ! isset( $_POST['wp_mpdf_noncename'] ) || ! wp_verify_nonce( $_POST['wp_mpdf_noncename'], plugin_basename( __FILE__ ) ) ) {
		return $post_id;
	}

	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} else if ( isset( $_POST['post_type'] ) && $_POST['post_type'] == 'post' ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	} else {
		return $post_id;
	}


	$post_id = wp_is_post_revision( $post_id );
	if ( $post_id == 0 ) {
		return $post_id;
	}


	global $wpdb;
	$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;

	$canPrintAsPDF = isset( $_POST['wp_mpdf_candownload'] );
	$needLogin     = isset( $_POST['wp_mpdf_needlogin'] );
	$pdfOutputName = sanitize_file_name( $_POST['wp_mpdf_pdfname'] );

	$sql   = 'SELECT id FROM ' . $table_name . ' WHERE post_id=' . $post_id . ' AND post_type="' . $_POST['post_type'] . '" LIMIT 1';
	$db_id = $wpdb->get_var( $sql );

	if ( $db_id == null ) {
		$sql = 'INSERT INTO ' . $table_name . ' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, %d, %d, %s, 0)';
		$wpdb->query( $wpdb->prepare( $sql, $_POST['post_type'], $post_id, $canPrintAsPDF, $needLogin, $pdfOutputName ) );
	} else {
		$sql = 'UPDATE ' . $table_name . ' SET general=%d , login=%d , pdfname=%s WHERE id=%d LIMIT 1';
		$wpdb->query( $wpdb->prepare( $sql, $canPrintAsPDF, $needLogin, $pdfOutputName, $db_id ) );
	}
}


function mpdf_admin_deletepost( $post_id ) {
	$post = get_post( $post_id );

	if ( 'page' == $post->post_type ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} else if ( $post->post_type == 'post' ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	} else {
		return $post_id;
	}


	//Clear the db entry if it exist
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;

	$sql = 'DELETE FROM ' . $table_name . ' WHERE post_id=%d AND post_type=%s LIMIT 1';
	$wpdb->query( $wpdb->prepare( $sql, $post_id, $post->post_type ) );


	//Clear the cache from a post
	$pdf_filename = $post->post_name . '.pdf';
	if ( file_exists( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $pdf_filename . '.cache' ) ) {
		unlink( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $pdf_filename . '.cache' );
	}
	if ( file_exists( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $pdf_filename ) ) {
		unlink( mpdf_getcachedir() . get_option( 'mpdf_theme' ) . '_' . $pdf_filename );
	}
}


//Register Filter
add_action( 'mpdf_generate_pdfs_hook', 'mpdf_cron_generate_pdfs_exec' );
add_action( 'delete_post', 'mpdf_admin_deletepost' );

add_action( 'template_redirect', 'mpdf_exec', 98 );
add_action( 'admin_menu', 'mpdf_create_admin_menu' );
add_filter( 'the_content', 'mpdf_filter' );

add_action( 'save_post', 'mpdf_admin_savepost' );

add_action( 'init', 'mpdf_shortcodes_init' );

register_activation_hook( __FILE__, 'mpdf_install' );
register_deactivation_hook( __FILE__, 'mpdf_deactivate' );
