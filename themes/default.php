<?php
	//Standard Plan Template
	
	global $post;
	global $pdf_output;
	global $pdf_header;
	global $pdf_footer;

	global $pdf_template_pdfpage;
	global $pdf_template_pdfpage_page;
	global $pdf_template_pdfdoc;

	global $pdf_html_header;
	global $pdf_html_footer;

	//Set a pdf template. if both are set the pdfdoc is used. (You didn't need a pdf template)
	$pdf_template_pdfpage 		= ''; //The filename off the pdf file (you need this for a page template)
	$pdf_template_pdfpage_page 	= 1;  //The page off this page (you need this for a page template)

	$pdf_template_pdfdoc  		= ''; //The filename off the complete pdf document (you need only this for a document template)
	
	$pdf_html_header 			= false; //If this is ture you can write instead of the array a html string on the var $pdf_header
	$pdf_html_footer 			= false; //If this is ture you can write instead of the array a html string on the var $pdf_footer

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
				if(is_search()) $pdf_output .=  '<div class="post"><h2 class="pagetitle">Search Results</h2></div>';
			if(is_archive()) {
				global $wp_query;

				if(is_category()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archive for the "' . single_cat_title('', false) . '" category</h2></div>';
				} elseif(is_year()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archive for ' . get_the_time('Y') . '</h2></div>';
				} elseif(is_month()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archive for ' . get_the_time('F, Y') . '</h2></div>';
				} elseif(is_day()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Archive for ' . get_the_time('F jS, Y') . '</h2></div>';
				} elseif(is_search()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Search Results</h2></div>';
				} elseif (is_author()) {
					$pdf_output .= '<div class="post"><h2 class="pagetitle">Author Archive</h2></div>';
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
					$comment_link = 'No Comments &#187;';
				} elseif($post->comment_count == 1) {
					$comment_link = 'One Comment &#187;';
				} else {
					$comment_link = $post->comment_count . ' Comments &#187;';
				}

				$pdf_output .= '<bookmark content="'.the_title('','', false).'" level="1" /><tocentry content="'.the_title('','', false).'" level="1" />';
				$pdf_output .= '<div class="post">
				<h2><a href="' . get_permalink() . '" rel="bookmark" title="Permanent Link to ' . the_title('','', false) . '">' . the_title('','', false) . '</a></h2>';


				// no authors and dates on static pages
				if(!is_page()) $pdf_output .=  '<p class="small subtitle">' . get_the_author_meta('display_name') . ' &middot; ' . date('l, F jS, Y', mpdf_mysql2unix($post->post_date)) . '</p>';

				$pdf_output .= '<div class="entry">' .	wpautop($post->post_content, true) . '</div>';
				
				if(!is_page() && !is_single()) $pdf_output .= '<p class="postmetadata">Posted in ' . $cat_links . ' | ' . '<a href="' . get_permalink() . '#comment">' . $comment_link . '</a></p>';

				// the following is the extended metadata for a single page
				if(is_single()) {
					$pdf_output .= '<p class="postmetadata alt">
						<span>
							This entry was posted on ' . date('l, F jS, Y', mpdf_mysql2unix($post->post_date)) . ' at ' . date('g:i a', mpdf_mysql2unix($post->post_date)) . ' and is filed under ' . $cat_links . '
							You can follow any responses to this entry through the <a href="' . get_bloginfo('comments_rss2_url') . '">Comments (RSS)</a> feed.';
	
							if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
								// Both Comments and Pings are open
								$pdf_output .= ' You can leave a response, or <a href="' . get_trackback_url() . '" rel="trackback">trackback</a> from your own site.';
							} elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
								// Only Pings are Open
								$pdf_output .= ' Responses are currently closed, but you can <a href="' . get_trackback_url() . '" rel="trackback">trackback</a> from your own site.';
							} elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
								// Comments are open, Pings are not
								$pdf_output .= ' You can skip to the end and leave a response. Pinging is currently not allowed.';
							} elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
								// Neither Comments, nor Pings are open
								$pdf_output .= ' Both comments and pings are currently closed.';
							}
	
						$pdf_output .= '</span>
					</p>';
				}
				$pdf_output .= '</div> <!-- post -->';
			endwhile;
			
		else :
			$pdf_output .= '<h2 class="center">Not Found</h2>
				<p class="center">Sorry, but you are looking for something that isn\'t here.</p>';
		endif;

		$pdf_output .= '</div> <!--content-->';

		
	$pdf_output .= '
		</body>
		</html>';
?>
