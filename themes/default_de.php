<?php
	//Standard Template Deutsch
	
	global $post;
	global $pdf_output;
	global $pdf_header;
	global $pdf_footer;

	global $pdf_template_pdfpage;
	global $pdf_template_pdfpage_page;
	global $pdf_template_pdfdoc;
	
	global $pdf_html_header;
	global $pdf_html_footer;

	//Hier kann man eine PDF als template setzen. Wenn man beide arten setzt wird pdfdoc verwendet (Man braucht kein PDF Template.)
	$pdf_template_pdfpage 		= ''; //Der dateiname zu einem pdf file (Für ein seiten template wird dies gebraucht)
	$pdf_template_pdfpage_page 	= 1;  //Die Seite aus diesem PDF (Für ein seiten template wird dies gebraucht)

	$pdf_template_pdfdoc  		= ''; //Der dateiname zu einem pdf file welches komplet immer wieder weiderholt werden soll (Man braucht dies nur für ein Dokumenten Template)
	
	$pdf_html_header 			= false; //Wenn hier true steht kann man stat dem array in $pdf_header einen html string eintragen
	$pdf_html_footer 			= false; //Wenn hier true steht kann man stat dem array in $pdf_footer einen html string eintragen

	//Set the Footer and the Header
	$pdf_header = array (
  		'odd' => 
  			array (
    			'R' => 
   					array (
						'content' => '{PAGENO}',
						'font-size' => 8,
						'font-style' => 'B',
						'font-family' => 'DejaVuSansCondensed',
    				),
    				'line' => 1,
  				),
  		'even' => 
  			array (
    			'R' => 
    				array (
						'content' => '{PAGENO}',
						'font-size' => 8,
						'font-style' => 'B',
						'font-family' => 'DejaVuSansCondensed',
    				),
    				'line' => 1,
  			),
	);
	$pdf_footer = array (
	  	'odd' => 
	 	 	array (
	    		'R' => 
	    			array (
						'content' => '{DATE d.m.Y}',
					    'font-size' => 8,
					    'font-style' => 'BI',
					    'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'C' => 
	    			array (
	      				'content' => '- {PAGENO} / {nb} -',
	      				'font-size' => 8,
	      				'font-style' => '',
	      				'font-family' => '',
	    			),
	    		'L' => 
	    			array (
	      				'content' => 'Copyright © '.'{DATE Y} '.get_bloginfo('name'),
	      				'font-size' => 8,
	      				'font-style' => 'BI',
	      				'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'line' => 1,
	  		),
	  	'even' => 
			array (
	    		'R' => 
	    			array (
						'content' => '{DATE d.m.Y}',
					    'font-size' => 8,
					    'font-style' => 'BI',
					    'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'C' => 
	    			array (
	      				'content' => '- {PAGENO} / {nb} -',
	      				'font-size' => 8,
	      				'font-style' => '',
	      				'font-family' => '',
	    			),
	    		'L' => 
	    			array (
	      				'content' => 'Copyright © '.'{DATE Y} '.get_bloginfo('name'),
	      				'font-size' => 8,
	      				'font-style' => 'BI',
	      				'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'line' => 1,
	  		),
	);
		

	$pdf_output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xml:lang="en">
		
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<title>' . get_bloginfo() . '</title>
		</head>
		<body xml:lang="en">
			<bookmark content="'.htmlspecialchars(get_bloginfo('name'), ENT_QUOTES).'" level="0" /><tocentry content="'.htmlspecialchars(get_bloginfo('name'), ENT_QUOTES).'" level="0" />
			<div id="header"><div id="headerimg">
				<h1><a href="' . get_option('home') . '/">' .  get_bloginfo('name') . '</a></h1>
				<div class="description">' .  get_bloginfo('description') . '</div>
			</div>
			</div>
			<div id="content" class="widecolumn">';
			if(have_posts()) :
				if(is_search()) $pdf_output .=  '<div class="post"><h2 class="pagetitle">Suchergebnisse</h2></div>';
			if(is_archive()) {
				global $wp_query;

				if(is_category()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archiv für die Kategorie "' . single_cat_title('', false) . '"</h2></div>';
				} elseif(is_year()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archiv für ' . date_i18n('Y', get_the_time('U')) . '</h2></div>';
				} elseif(is_month()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archiv für ' . date_i18n('Y', get_the_time('F, Y')) . '</h2></div>';
				} elseif(is_day()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archiv für ' . date_i18n('Y', get_the_time('F jS, Y')) . '</h2></div>';
				} elseif(is_search()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Suchergebnisse</h2></div>';
				} elseif (is_author()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Autor Archiv</h2></div>';
				}
			}
			
			while (have_posts()) : the_post();
			
				$cat_links = "";
				foreach((get_the_category()) as $cat) {
					$cat_links .= '<a href="' . get_category_link($cat->term_id) . '" title="' . $cat->category_description . '">' . $cat->cat_name . '</a>, ';
				}
				$cat_links = substr($cat_links, 0, -2);

				// Create comments link
				if($post->comment_count == 0) {
					$comment_link = 'Keine Kommentare &#187;';
				} elseif($post->comment_count == 1) {
					$comment_link = 'Ein Kommentar &#187;';
				} else {
					$comment_link = $post->comment_count . ' Kommentare &#187;';
				}

				$pdf_output .= '<bookmark content="'.the_title('','', false).'" level="1" /><tocentry content="'.the_title('','', false).'" level="1" />';
				$pdf_output .= '<div class="post">
				<h2><a href="' . get_permalink() . '" rel="bookmark" title="Permanenter Link zu ' . the_title('','', false) . '">' . the_title('','', false) . '</a></h2>';


				// no authors and dates on static pages
				if(!is_page()) $pdf_output .=  '<p class="small subtitle">' . get_the_author_meta('display_name') . ' &middot; ' . date_i18n('l', mpdf_mysql2unix($post->post_date)) . ' den ' .  date_i18n('j. F Y', mpdf_mysql2unix($post->post_date)) . '</p>';

				$pdf_output .= '<div class="entry">' .	wpautop($post->post_content, true) . '</div>';
				
				if(!is_page() && !is_single()) $pdf_output .= '<p class="postmetadata">Gepostet in ' . $cat_links . ' | ' . '<a href="' . get_permalink() . '#comment">' . $comment_link . '</a></p>';

				// the following is the extended metadata for a single page
				if(is_single()) {
					$pdf_output .= '<p class="postmetadata alt">
						<span>
							Dieser Beitrag wurde publiziert am ' . date_i18n('l', mpdf_mysql2unix($post->post_date)) . ' den ' .  date_i18n('j. F Y', mpdf_mysql2unix($post->post_date)) . ' um ' . date_i18n('H:i', mpdf_mysql2unix($post->post_date)) . '
							in der Kategorie: ' . $cat_links . '.
							Kommentare können über den <a href="' . get_bloginfo('comments_rss2_url') . '">Kommentar (RSS)</a> Feed verfolgt werden.';
	
							if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
								// Both Comments and Pings are open
								$pdf_output .= '
								Du kannst ein Kommentar abgeben oder erstelle einen <a href="' . get_trackback_url() . '" rel="trackback">Trackback</a> dieses Beitrages auf deine Webseite.';
							} elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
								// Only Pings are Open
								$pdf_output .= '
								Kommentare sind geschlossen aber Du kannst einen <a href="' . get_trackback_url() . '" rel="trackback">Trackback</a> zu diesem Beitrag auf deiner Webseite erstellen.';
							} elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
								// Comments are open, Pings are not
								$pdf_output .= '
								Du kannst zum Ende springen und ein Kommentar abgeben. Pingen ist momentan nicht erlaubt.';
							} elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
								// Neither Comments, nor Pings are open
								$pdf_output .= '
								Kommentare und Pings sind momentan geschlossen.';
							}
	
						$pdf_output .= '</span>
					</p>';
				}
				$pdf_output .= '</div> <!-- post -->';
			endwhile;
			
		else :
			$pdf_output .= '<h2 class="center">Nicht gefunden</h2>
				<p class="center">Sorry, du scheinst nach etwas zu suchen das hier nicht ist.</p>';
		endif;

		$pdf_output .= '</div> <!--content-->';

		
	$pdf_output .= '
		</body>
		</html>';
?>
