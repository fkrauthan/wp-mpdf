<?php
/*
Plugin Name: wp-mpdf
Plugin URI: http://www.fkrauthan.de/wordpress/wp-mpdf
Description: Print a wordpress page as PDF with optional Geshi Parsing.
Version: 1.4
Author: Florian 'fkrauthan' Krauthan
Author URI: http://www.fkrauthan.de

Copyright 2009  Florian Krauthan
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
 

function mpdf_output($wp_content = '', $do_pdf = false ) {
	global $post;
	$pdf_filename = $post->post_name . '.pdf';
	
	$replace_me = array(
		  'src="/' => 'src="' . $_SERVER['DOCUMENT_ROOT'] . '/'
		, 'src="' . get_bloginfo('home') . '/' => 'src="' . $_SERVER['DOCUMENT_ROOT'] . '/'
		, '–' => '-'
		, '&ndash;' => '-'
		, '&#8211;' => '-'
		, '' => '' // add your own stuff
	);
	
	$wp_content = mpdf_filter($wp_content, $replace_me, $mpdf_delimiter1, $mpdf_delimiter2);
	if(function_exists('polyglot_filter')) $wp_content = polyglot_filter($wp_content);
	if(function_exists('filter_bbcode')) $wp_content = filter_bbcode($wp_content);
	
	/**
	 * Geshi Support
	 */
	if(get_option('mpdf_geshi')==true) {
		require_once('wp-content/plugins/wp-mpdf/geshi.inc.php');
		$wp_content = ParseGeshi($wp_content);
	}
	 
	
	if($do_pdf === false) {
		echo $wp_content;
	}
	else {
		define('_MPDF_PATH',dirname(__FILE__).'/mpdf/');
		require_once(_MPDF_PATH.'mpdf.php');
		
		$mpdf=new mPDF(); 

		$mpdf->SetUserRights();
		$mpdf->title2annots = true;
		//$mpdf->annotMargin = 12;
		$mpdf->use_embeddedfonts_1252 = true;	// false is default
		$mpdf->SetBasePath(dirname('../../../'.dirname(__FILE__)));
		$mpdf->SetAuthor('Sppro Community Magazin');
		$mpdf->SetCreator('Sppro Community Magazin');
		
		
		//The Header and Footer
		global $pdf_footer;
		global $pdf_header;
		
		$mpdf->startPageNums();	// Required for TOC use after AddPage(), and to use Headers and Footers
		$mpdf->setHeader($pdf_header);
		$mpdf->setFooter($pdf_footer);
		
		
		if(get_option('mpdf_theme')!=''&&file_exists('wp-content/plugins/wp-mpdf/themes/'.get_option('mpdf_theme').'.css')) {
			//Read the StyleCSS
			$tmpCSS = file_get_contents('wp-content/plugins/wp-mpdf/themes/'.get_option('mpdf_theme').'.css');
			$mpdf->WriteHTML($tmpCSS, 1);
		}
		
		//My Filters
		require_once('wp-content/plugins/wp-mpdf/myfilters.inc.php');
		$wp_content = mpdf_myfilters($wp_content);
		//die($wp_content);
		$mpdf->WriteHTML($wp_content);
		
		if(get_option('mpdf_caching')==true) {
			file_put_contents('wp-content/plugins/wp-mpdf/cache/'.get_option('mpdf_theme').'_'.$pdf_filename.'.cache', $post->post_modified_gmt);
			$mpdf->Output('wp-content/plugins/wp-mpdf/cache/'.get_option('mpdf_theme').'_'.$pdf_filename, 'F');
			$mpdf->Output($pdf_filename, 'I');
		}
		else {
			$mpdf->Output($pdf_filename, 'I');
		}
	}
}

function mpdf_filter($wp_content = '', $replace_me = array(), $do_pdf = false, $convert = false) {
	$delimiter1 = 'screen';
	$delimiter2 = 'print';

	if($do_pdf === false ) {
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

	format_to_post('the_content');

	$wp_content = str_replace($d1a , '', $wp_content);
	$wp_content = str_replace($d1b , '', $wp_content);

	$ctpdf_wp_content = preg_replace("/$d2a(.*?)$d2b/s", '', $wp_content);


	if ($convert == true) {
		$wp_content = mb_convert_encoding($wp_content, "ISO-8859-1", "UTF-8");
	}

	return $wp_content;
}

function mpdf_mysql2unix($timestamp) {
// stolen cold-blooded from the Polyglot plugin
	$year = substr($timestamp,0,4);
	$month = substr($timestamp,5,2);
	$day = substr($timestamp,8,2);
	$hour = substr($timestamp,11,2);
	$minute = substr($timestamp,14,2);
	$second = substr($timestamp,17,2);
	return mktime($hour,$minute,$second,$month,$day,$year);
}

function mpdf_pdfbutton($buttontext = '', $print_button = true ) {
	if(empty($buttontext))
		$buttontext = '<img src="' . get_bloginfo('home') . '/wp-content/plugins/wp-mpdf/pdf.png" alt="This page as PDF" />';
	
	$x = !strpos($_SERVER['REQUEST_URI'], '?') ? '?' : '&amp;';
	$pdf_button = '<a id="pdfbutton" href="' . $_SERVER['REQUEST_URI'] . $x . 'output=pdf">' . $buttontext . '</a>';
	
	if($print_button === true) {
		echo $pdf_button;
	} else {
		return $pdf_button;
	}
}

function mpdf_readcachedfile($name) {
	$fp = fopen('wp-content/plugins/wp-mpdf/cache/'.get_option('mpdf_theme').'_'.$name, 'rb');
	if(!$fp) die('Couldn\'t Read cache file');
	fclose($fp);
	
	Header('Content-Type: application/pdf');
	Header('Content-Length: '.filesize('wp-content/plugins/wp-mpdf/cache/'.get_option('mpdf_theme').'_'.$name));
	Header('Content-disposition: inline; filename='.$name);
	
	echo file_get_contents('wp-content/plugins/wp-mpdf/cache/'.get_option('mpdf_theme').'_'.$name, FILE_BINARY | FILE_USE_INCLUDE_PATH);
}

function mpdf_exec() {
	if($_GET['output'] == 'pdf') {
		//Check for Caching option
		if(get_option('mpdf_caching')==true) {
			global $post;
			$pdf_filename = $post->post_name . '.pdf';
			if(file_exists('wp-content/plugins/wp-mpdf/cache/'.get_option('mpdf_theme').'_'.$pdf_filename.'.cache')&&file_exists('wp-content/plugins/wp-mpdf/cache/'.get_option('mpdf_theme').'_'.$pdf_filename)) {
				$createDate = file_get_contents('wp-content/plugins/wp-mpdf/cache/'.get_option('mpdf_theme').'_'.$pdf_filename.'.cache');
				if($createDate==$post->post_modified_gmt) {
					//We could Read the Cached file
					mpdf_readcachedfile($pdf_filename);
					exit;
				}
			}
		} 
		
		require_once('wp-content/plugins/wp-mpdf/themes/'.get_option('mpdf_theme').'.php');
		
		mpdf_output($pdf_output, true);
		
		exit;
	}
}

function mpdf_admin() {
	require_once('wp-mpdf_admin.php');
	mpdf_admin_display();
}

function mpdf_create_admin_menu() {
	add_submenu_page('plugins.php', 'wp-mpdf - config', 'wp-mpdf', 8, dirname(__FILE__), 'mpdf_admin');
}

//Register Filter
add_option('mpdf_theme', 'default');
add_option('mpdf_geshi', false);
add_option('mpdf_caching', true);

add_action('template_redirect', 'mpdf_exec', 98);
add_action('admin_menu', 'mpdf_create_admin_menu');
add_filter('the_content', 'mpdf_filter');
?>
