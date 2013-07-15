<?php
	//Standard French Template
	
	global $post;
	global $pdf_output;
	global $pdf_header;
	global $pdf_footer;

	global $pdf_template_pdfpage;
	global $pdf_template_pdfpage_page;
	global $pdf_template_pdfdoc;

	//Set a pdf template. if both are set the pdfdoc is used. (You didn't need a pdf template)
	$pdf_template_pdfpage 		= ''; //The filename off the pdf file (you need this for a page template)
	$pdf_template_pdfpage_page 	= 1;  //The page off this page (you need this for a page template)

	$pdf_template_pdfdoc  		= ''; //The filename off the complete pdf document (you need only this for a document template)

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
	      				'content' => get_bloginfo('name'),
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
	      				'content' => get_bloginfo('name'),
	      				'font-size' => 8,
	      				'font-style' => 'BI',
	      				'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'line' => 1,
	  		),
	);
		

	$pdf_output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xml:lang="fr">
		
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<title>' . get_bloginfo() . '</title>
		</head>
		<body xml:lang="fr">
			<bookmark content="'.htmlspecialchars(get_bloginfo('name'), ENT_QUOTES).'" level="0" /><tocentry content="'.htmlspecialchars(get_bloginfo('name'), ENT_QUOTES).'" level="0" />
			<div id="header"><div id="headerimg">
				<h1><a href="' . get_option('home') . '/">' .  get_bloginfo('name') . '</a></h1>
				<div class="description">' .  get_bloginfo('description') . '</div>
			</div>
			</div>
			<div id="content" class="widecolumn">';
			
			if(have_posts()) :
				if(is_search()) $pdf_output .=  '<div class="post"><h2 class="pagetitle">R&eacute;sultats de la recherche</h2></div>';
			if(is_archive()) {
				global $wp_query;

				if(is_category()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archive pour la cat&eacute;gorie "' . single_cat_title('', false) . '"</h2></div>';
				} elseif(is_year()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archive pour ' . date_i18n('Y', get_the_time('U')) . '</h2></div>';
				} elseif(is_month()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archive pour ' . date_i18n('Y', get_the_time('F, Y')) . '</h2></div>';
				} elseif(is_day()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archive pour ' . date_i18n('Y', get_the_time('F jS, Y')) . '</h2></div>';
				} elseif(is_search()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">R&eacute;sultats de la Recerche</h2></div>';
				} elseif (is_author()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archive de l&apos;auteur</h2></div>';
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
					$comment_link = 'Pas de Commentaire &#187;';
				} elseif($post->comment_count == 1) {
					$comment_link = 'Un seul commentaire &#187;';
				} else {
					$comment_link = $post->comment_count . ' Commentaires &#187;';
				}

				$pdf_output .= '<bookmark content="'.the_title('','', false).'" level="1" /><tocentry content="'.the_title('','', false).'" level="1" />';
				$pdf_output .= '<div class="post">
				<h2><a href="' . get_permalink() . '" rel="bookmark" title="Lien permanent vers ' . the_title('','', false) . '">' . the_title('','', false) . '</a></h2>';


				// no authors and dates on static pages
				if(!is_page()) $pdf_output .=  '<p class="small subtitle">' . get_the_author_meta('display_name') . ' , le ' . date_i18n('l', mpdf_mysql2unix($post->post_date)) . ' ' .date_i18n('j F Y', mpdf_mysql2unix($post->post_date)) . '</p>';

				$pdf_output .= '<div class="entry">' .	wpautop($post->post_content, true) . '</div>';
				
				if(!is_page() && !is_single()) $pdf_output .= '<p class="postmetadata">Post&eacute; dans ' . $cat_links . ' | ' . '<a href="' . get_permalink() . '#comment">' . $comment_link . '</a></p>';

				// the following is the extended metadata for a single page
				if(is_single()) {
					$pdf_output .= '<p class="postmetadata alt">
						<span>Le ' . date_i18n('l', mpdf_mysql2unix($post->post_date)) . ' ' . date_i18n('j F Y', mpdf_mysql2unix($post->post_date)) . ' &agrave; ' . date_i18n('H:i', mpdf_mysql2unix($post->post_date)) . ' . Class&eacute; dans ' . $cat_links . '. Vous pouvez suivre toutes les r&eacute;ponses &agrave; ce billet via le <a href="' . get_bloginfo('comments_rss2_url') . '"> fils de commentaire (RSS)</a>.';
							
							if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
								// Both Comments and Pings are open
								$pdf_output .= ' Vous pouvez laisser une r&eacute;, ou <a href="' . get_trackback_url() . '" rel="trackback">un trackback</a> depuis votre site.';
							} elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
								// Only Pings are Open
								$pdf_output .= ' Les r&eacute;ponses sont closes en ce moment, mais vous pouvez faire un <a href="' . get_trackback_url() . '" rel="trackback">trackback</a> depuis votre propre site.';
							} elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
								// Comments are open, Pings are not
								$pdf_output .= ' Vous pouvez aller jusqu&apos; &agrave; la fin et laisser une r&eacute;ponse. Le ping n&apos;est pas permis.';
							} elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
								// Neither Comments, nor Pings are open
								$pdf_output .= ' Les commentaires et pings ne sont plus permis.';
							}
	
						$pdf_output .= '</span>
					</p>';
				}
				$pdf_output .= '</div> <!-- post -->';
			endwhile;
			
		else :
			$pdf_output .= '<h2 class="center">Not Found</h2>
				<p class="center">D&eacute;, mais vous chercher quelque chose qui n&apos;est pas ici.</p>';
		endif;

		$pdf_output .= '</div> <!--content-->';

		
	$pdf_output .= '
		</body>
		</html>';
?>
