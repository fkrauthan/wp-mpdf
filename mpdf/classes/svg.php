<?php
//	svg class modified for mPDF by Ian Back: based on -
//	svg2pdf fpdf class
//	sylvain briand (syb@godisaduck.com), modified by rick trevino (rtrevino1@yahoo.com)
//	http://www.godisaduck.com/svg2pdf_with_fpdf
//	http://rhodopsin.blogspot.com
//	
//	cette class etendue est open source, toute modification devra cependant etre repertoriée~



class SVG {

	var $svg_gradient;	//	array - contient les infos sur les gradient fill du svg classé par id du svg
	var $svg_shadinglist;	//	array - contient les ids des objet shading
	var $svg_info;		//	array contenant les infos du svg voulue par l'utilisateur
	var $svg_attribs;		//	array - holds all attributes of root <svg> tag
	var $svg_style;		//	array contenant les style de groupes du svg
	var $svg_string;		//	String contenant le tracage du svg en lui même.
	var $txt_data;		//    array - holds string info to write txt to image
	var $txt_style;		// 	array - current text style
	var $mpdf_ref;

	function SVG(&$mpdf){
		$this->svg_gradient = array();
		$this->svg_shadinglist = array();
		$this->txt_data = array();
		$this->svg_string = '';
		$this->svg_info = array();
		$this->svg_attribs = array();

		$this->mpdf_ref =& $mpdf;

		$this->svg_style = array(
			array(
			'fill'		=> 'none',			//	pas de remplissage par defaut
			'fill-opacity'	=> 1,				//	remplissage opaque par defaut
			'fill-rule'		=> 'nonzero',		//	mode de remplissage par defaut
			'stroke'		=> 'none',			//	pas de trait par defaut
			'stroke-linecap'	=> 'butt',			//	style de langle par defaut
			'stroke-linejoin'	=> 'miter',			//
			'stroke-miterlimit' => 4,			//	limite de langle par defaut
			'stroke-opacity'	=> 1,				//	trait opaque par defaut
			'stroke-width'	=> 0				//	epaisseur du trait par defaut
			)
		);

		$this->txt_style = array(
			array(
			'fill'		=> 'black',		//	pas de remplissage par defaut
			'font-family' 	=> $mpdf->default_font,
			'font-size'		=> $mpdf->default_font_size,		// 	****** this is pts
			'font-weight'	=> 'normal',	//	normal | bold
			'font-style'	=> 'normal',	//	italic | normal
			'text-anchor'	=> 'start'		// alignment: start, middle, end
			)
		);



	}

	function svgGradient($gradient_info, $attribs, $element){

		$n = count($this->mpdf_ref->gradients)+1;

		// Get bounding dimensions of element
		$w = 100;
		$h = 100;
		$x_offset = 0;
		$y_offset = 0;
		if ($element=='rect') {
			$w = $attribs['width'];
			$h = $attribs['height'];
			$x_offset = $attribs['x'];
			$y_offset = $attribs['y'];
		}
		else if ($element=='ellipse') {
			$w = $attribs['rx']*2;
			$h = $attribs['ry']*2;
			$x_offset = $attribs['cx']-$attribs['rx'];
			$y_offset = $attribs['cy']-$attribs['ry'];
		}
		else if ($element=='circle') {
			$w = $attribs['r']*2;
			$h = $attribs['r']*2;
			$x_offset = $attribs['cx']-$attribs['r'];
			$y_offset = $attribs['cy']-$attribs['r'];
		}
		else if ($element=='polygon') {
			$pts = preg_split('/[ ,]+/', trim($attribs['points']));
			$maxr=$maxb=0;
			$minl=$mint=999999;
			for ($i=0;$i<count($pts); $i++) {
				if ($i % 2 == 0) {	// x values
					$minl = min($minl,$pts[$i]);
					$maxr = max($maxr,$pts[$i]);
				}
				else {	// y values
					$mint = min($mint,$pts[$i]);
					$maxb = max($maxb,$pts[$i]);
				}
			}
			$w = $maxr-$minl;
			$h = $maxb-$mint;
			$x_offset = $minl;
			$y_offset = $mint;
		}
		else if ($element=='path') {
			preg_match_all('/([a-z]|[A-Z])([ ,\-.\d]+)*/', $attribs['d'], $commands, PREG_SET_ORDER);
			$maxr=$maxb=0;
			$minl=$mint=999999;
			foreach($commands as $c){
				if(count($c)==3){
					list($tmp, $cmd, $arg) = $c;
					if ($cmd=='M' || $cmd=='L' || $cmd=='C' || $cmd=='S' || $cmd=='Q' || $cmd=='T') {
						$pts = preg_split('/[ ,]+/', trim($arg));
						for ($i=0;$i<count($pts); $i++) {
							if ($i % 2 == 0) {	// x values
								$minl = min($minl,$pts[$i]);
								$maxr = max($maxr,$pts[$i]);
							}
							else {	// y values
								$mint = min($mint,$pts[$i]);
								$maxb = max($maxb,$pts[$i]);
							}
						}
					}
					if ($cmd=='H') { // sets new x
						$minl = min($minl,$arg);
						$maxr = max($maxr,$arg);
					}
					if ($cmd=='V') { // sets new y
						$mint = min($mint,$arg);
						$maxb = max($maxb,$arg);
					}
				}
			}
			$w = $maxr-$minl;
			$h = $maxb-$mint;
			$x_offset = $minl;
			$y_offset = $mint;
		}
		if (!$w) { $w = 100; }
		if (!$h) { $h = 100; }
		if ($x_offset==999999) { $x_offset = 0; }
		if ($y_offset==999999) { $y_offset = 0; }


		$return = "";
		if ($gradient_info['type'] == 'linear'){
			$this->mpdf_ref->gradients[$n]['type'] = 2;
			if (isset($gradient_info['info']['x1'])) { $x1 = $gradient_info['info']['x1']; }
			else { $x1 = 0; }
			if (isset($gradient_info['info']['y1'])) { $y1 = $gradient_info['info']['y1']; }
			else { $y1 = 0; }
			if (isset($gradient_info['info']['x2'])) { $x2 = $gradient_info['info']['x2']; }
			else { $x2 = 1; }
			if (isset($gradient_info['info']['y2'])) { $y2 = $gradient_info['info']['y2']; }
			else { $y2 = 0; }

			if (stristr($x1, '%')!== false) { $x1 = ($x1+0)/100; }
			if (stristr($x2, '%')!== false) { $x2 = ($x2+0)/100; }
			if (stristr($y1, '%')!== false) { $y1 = ($y1+0)/100; }
			if (stristr($y2, '%')!== false) { $y2 = ($y2+0)/100; }

			$this->mpdf_ref->gradients[$n]['coords']=array($x1, $y1, $x2, $y2);

			$a = $w;	// width
			$b = 0;
			$c = 0;
			$d = -$h;	// height
			$e = $x_offset;	// x- offset
			$f = -$y_offset;	// -y-offset

			$return .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f cm ', $a, $b, $c, $d, $e, $f);

			$this->mpdf_ref->gradients[$n]['col1'] = $gradient_info['color'][0]['color'];
			$this->mpdf_ref->gradients[$n]['col2'] = $gradient_info['color'][(count($gradient_info['color'])-1)]['color'];

		}
		else if ($gradient_info['type'] == 'radial'){

			$this->mpdf_ref->gradients[$n]['type'] = 3;
			$a = $w;	// width
			$b = 0;
			$c = 0;
			$d = -$h;		// -height
			$e = $x_offset;	// x- offset
			$f = -$y_offset;	// -y-offset

			$return .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f cm ', $a, $b, $c, $d, $e, $f);

			if ($gradient_info['info']['x0'] || $gradient_info['info']['x0']===0) { $x0 = $gradient_info['info']['x0']; }
			else { $x0 = 0.5; }
			if ($gradient_info['info']['y0'] || $gradient_info['info']['y0']===0) { $y0 = $gradient_info['info']['y0']; }
			else { $y0 = 0.5; }
			if ($gradient_info['info']['r'] || $gradient_info['info']['r']===0) { $r = $gradient_info['info']['r']; }
			else { $r = 0.5; }
			if ($gradient_info['info']['x1'] || $gradient_info['info']['x1']===0) { $x1 = $gradient_info['info']['x1']; }
			else { $x1 = $x0; }
			if ($gradient_info['info']['y1'] || $gradient_info['info']['y1']===0) { $y1 = $gradient_info['info']['y1']; }
			else { $y1 = $y0; }

			if (stristr($x1, '%')!== false) { $x1 = ($x1+0)/100; }
			if (stristr($x0, '%')!== false) { $x0 = ($x0+0)/100; }
			if (stristr($y1, '%')!== false) { $y1 = ($y1+0)/100; }
			if (stristr($y0, '%')!== false) { $y0 = ($y0+0)/100; }
			if (stristr($r, '%')!== false) { $r = ($r+0)/100; }

			// x1 and y1 (fx, fy) should be inside the circle defined by x0 y0 and r else error in mPDF
			while (pow(($x1-$x0),2) + pow(($y1 - $y0),2) >= pow($r,2)) { $r += 0.05; }

			$this->mpdf_ref->gradients[$n]['coords']=array( $x1, $y1, $x0, $y0, abs($r) );


			$this->mpdf_ref->gradients[$n]['col1'] = $gradient_info['color'][0]['color'];
			$this->mpdf_ref->gradients[$n]['col2'] = $gradient_info['color'][(count($gradient_info['color'])-1)]['color'];

		}

		$this->mpdf_ref->gradients[$n]['extend']=array('true','true');

/* Only uses 2 colours for mPDF
		$this->mpdf_ref->gradients[$n]['color'] = array();
		$n_color = count($gradient_info['color']);
		for ($i = 0;$i<$n_color;$i++){
			$color = array (
				'color' => $gradient_info['color'][$i]['color'],
				'offset' => $gradient_info['color'][$i]['offset'],
				'opacity' => $gradient_info['color'][$i]['opacity']
			);
			array_push($this->mpdf_ref->gradients[$n]['color'],$color);
		}
*/

		$return .= '/Sh'.count($this->mpdf_ref->gradients).' sh Q ';
		return $return;

	}

	function svgOffset ($attribs){
		// save all <svg> tag attributes
		$this->svg_attribs = $attribs;
		if(isset($this->svg_attribs['viewBox'])) {
			$vb = preg_split('/\s+/is', trim($this->svg_attribs['viewBox']));
			if (count($vb)==4) {
				$this->svg_info['x'] = $vb[0];
				$this->svg_info['y'] = $vb[1];
				$this->svg_info['w'] = $vb[2];
				$this->svg_info['h'] = $vb[3];
				return;
			}
		}

		$svg_w = $this->mpdf_ref->ConvertSize($attribs['width']);	// mm (interprets numbers as pixels)
		$svg_h = $this->mpdf_ref->ConvertSize($attribs['height']);	// mm

		// Added to handle file without height or width specified
		if (!$svg_w && !$svg_h) { $svg_w = $svg_h = $this->mpdf_ref->blk[$this->mpdf_ref->blklvl]['inner_width'] ; }	// DEFAULT
		if (!$svg_w) { $svg_w = $svg_h; }
		if (!$svg_h) { $svg_h = $svg_w; }

		$this->svg_info['x'] = 0;
		$this->svg_info['y'] = 0;
		$this->svg_info['w'] = $svg_w/0.2645;	// mm->pixels
		$this->svg_info['h'] = $svg_h/0.2645;	// mm->pixels
	}


	//
	// check if points are within svg, if not, set to max
	function svg_overflow($x,$y)
	{
		$x2 = $x;
		$y2 = $y;
		if(isset($this->svg_attribs['overflow']))
		{
			if($this->svg_attribs['overflow'] == 'hidden')
			{
				// Not sure if this is supposed to strip off units, but since I dont use any I will omlt this step
				$svg_w = preg_replace("/([0-9\.]*)(.*)/i","$1",$this->svg_attribs['width']);
				$svg_h = preg_replace("/([0-9\.]*)(.*)/i","$1",$this->svg_attribs['height']);
				
				// $xmax = floor($this->svg_attribs['width']);
				$xmax = floor($svg_w);
				$xmin = 0;
				// $ymax = floor(($this->svg_attribs['height'] * -1));
				$ymax = floor(($svg_h * -1));
				$ymin = 0;

				if($x > $xmax) $x2 = $xmax; // right edge
				if($x < $xmin) $x2 = $xmin; // left edge
				if($y < $ymax) $y2 = $ymax; // bottom 
				if($y > $ymin) $y2 = $ymin; // top 

			}
		}


		return array( 'x' => $x2, 'y' => $y2);
	}



	function svgDefineStyle($critere_style){

		$tmp = count($this->svg_style)-1;
		$current_style = $this->svg_style[$tmp];

		unset($current_style['transformations']);

		// TRANSFORM SCALE
		$transformations = '';
		if (isset($critere_style['transform'])){
			preg_match_all('/(matrix|translate|scale|rotate|skewX|skewY)\((.*?)\)/is',$critere_style['transform'],$m);
			if (count($m[0])) {
				for($i=0; $i<count($m[0]); $i++) {
					$c = strtolower($m[1][$i]);
					$v = trim($m[2][$i]);
					$vv = preg_split('/[ ,]+/',$v);
					if ($c=='matrix' && count($vv)==6) {
						$transformations .= sprintf(' %.3f %.3f %.3f %.3f %.3f %.3f cm ', $vv[0], $vv[1], $vv[2], $vv[3], $vv[4], $vv[5]);
					}
					else if ($c=='translate' && count($vv)) {
						$tm[4] = $vv[0];
						if (count($vv)==2) { $t_y = -$vv[1]; }
						else { $t_y = 0; }
						$tm[5] = $t_y;
						$transformations .= sprintf(' 1 0 0 1 %.3f %.3f cm ', $tm[4], $tm[5]);
					}
					else if ($c=='scale' && count($vv)) {
						if (count($vv)==2) { $s_y = $vv[1]; }
						else { $s_y = $vv[0]; }
						$tm[0] = $vv[0];
						$tm[3] = $s_y;
						$transformations .= sprintf(' %.3f 0 0 %.3f 0 0 cm ', $tm[0], $tm[3]);
					}
					else if ($c=='rotate' && count($vv)) {
						$tm[0] = cos(deg2rad(-$vv[0]));
						$tm[1] = sin(deg2rad(-$vv[0]));
						$tm[2] = -$tm[1];
						$tm[3] = $tm[0];
						if (count($vv)==3) {
							$transformations .= sprintf(' 1 0 0 1 %.3f %.3f cm ', $vv[1], -$vv[2]);
						}
						$transformations .= sprintf(' %.3f %.3f %.3f %.3f 0 0 cm ', $tm[0], $tm[1], $tm[2], $tm[3]);
						if (count($vv)==3) {
							$transformations .= sprintf(' 1 0 0 1 %.3f %.3f cm ', -$vv[1], $vv[2]);
						}
					}
					else if ($c=='skewx' && count($vv)) {
						$tm[2] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 0 %.3f 1 0 0 cm ', $tm[2]);
					}
					else if ($c=='skewy' && count($vv)) {
						$tm[1] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 %.3f 0 1 0 0 cm ', $tm[1]);
					}

				}
			}
			$current_style['transformations'] = $transformations;
		}

		if (isset($critere_style['style'])){
			if (preg_match('/fill:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/',$critere_style['style'], $m)) {
				$current_style['fill'] = '#'.str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			}
			else { $tmp = preg_replace("/(.*)fill:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
				if ($tmp != $critere_style['style']){ $current_style['fill'] = $tmp; }
			}

			$tmp = preg_replace("/(.*)fill-opacity:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['fill-opacity'] = $tmp;}

			$tmp = preg_replace("/(.*)fill-rule:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['fill-rule'] = $tmp;}

			if (preg_match('/stroke:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/',$critere_style['style'], $m)) {
				$current_style['stroke'] = '#'.str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT).str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			}
			else { $tmp = preg_replace("/(.*)stroke:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
				if ($tmp != $critere_style['style']){ $current_style['stroke'] = $tmp; }
			}
			
			$tmp = preg_replace("/(.*)stroke-linecap:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-linecap'] = $tmp;}

			$tmp = preg_replace("/(.*)stroke-linejoin:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-linejoin'] = $tmp;}
			
			$tmp = preg_replace("/(.*)stroke-miterlimit:\s*([a-z0-9#]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-miterlimit'] = $tmp;}
			
			$tmp = preg_replace("/(.*)stroke-opacity:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-opacity'] = $tmp; }
			
			$tmp = preg_replace("/(.*)stroke-width:\s*([a-z0-9.]*|none)(.*)/i","$2",$critere_style['style']);
			if ($tmp != $critere_style['style']){ $current_style['stroke-width'] = $tmp;}

		}

		if(isset($critere_style['fill'])){
			$current_style['fill'] = $critere_style['fill'];
		}

		if(isset($critere_style['fill-opacity'])){
			$current_style['fill-opacity'] = $critere_style['fill-opacity'];
		}

		if(isset($critere_style['fill-rule'])){
			$current_style['fill-rule'] = $critere_style['fill-rule'];
		}

		if(isset($critere_style['stroke'])){
			$current_style['stroke'] = $critere_style['stroke'];
		}

		if(isset($critere_style['stroke-linecap'])){
			$current_style['stroke-linecap'] = $critere_style['stroke-linecap'];
		}

		if(isset($critere_style['stroke-linejoin'])){
			$current_style['stroke-linejoin'] = $critere_style['stroke-linejoin'];
		}

		if(isset($critere_style['stroke-miterlimit'])){
			$current_style['stroke-miterlimit'] = $critere_style['stroke-miterlimit'];
		}

		if(isset($critere_style['stroke-opacity'])){
			$current_style['stroke-opacity'] = $critere_style['stroke-opacity'];
		}

		if(isset($critere_style['stroke-width'])){
			$current_style['stroke-width'] = $critere_style['stroke-width'];
		}

		return $current_style;

	}

	//
	//	Cette fonction ecrit le style dans le stream svg.
	function svgStyle($critere_style, $attribs, $element){
		$path_style = '';

		if (substr_count($critere_style['fill'],'url')>0){
			//
			// couleur degradé
			$id_gradient = preg_replace("/url\(#([\w_]*)\)/i","$1",$critere_style['fill']);
			if ($id_gradient != $critere_style['fill']) {
				$fill_gradient = $this->svgGradient($this->svg_gradient[$id_gradient], $attribs, $element);

				$path_style = "q ";
				$w = "W";
				$style .= 'N';
			}

		}
		else if ($critere_style['fill'] != 'none'){
			//	fill couleur pleine
			$col = $this->mpdf_ref->ConvertColor($critere_style['fill']);
			if ($col) {
				$path_style .= sprintf('%.3f %.3f %.3f rg ',$col['R']/255,$col['G']/255,$col['B']/255);
				$style .= 'F';
			}
		}

		if ($critere_style['stroke'] != 'none'){
			$col = $this->mpdf_ref->ConvertColor($critere_style['stroke']);
			if ($col) {
				$path_style .= sprintf('%.3f %.3f %.3f RG ',$col['R']/255,$col['G']/255,$col['B']/255);
				$style .= 'D';

				$path_style .= sprintf('%.3f w ',$critere_style['stroke-width']);
			}
		}


	if ($critere_style['stroke'] != 'none'){
		if ($critere_style['stroke-linejoin'] == 'miter'){
			$path_style .= ' 0 j ';
		}
		else if ($critere_style['stroke-linejoin'] == 'round'){
			$path_style .= ' 1 j ';
		}
		else if ($critere_style['stroke-linejoin'] == 'bevel'){
			$path_style .= ' 2 j ';
		}

		if ($critere_style['stroke-linecap'] == 'butt'){
			$path_style .= ' 0 J ';
		}
		else if ($critere_style['stroke-linecap'] == 'round'){
			$path_style .= ' 1 J ';
		}
		else if ($critere_style['stroke-linecap'] == 'square'){
			$path_style .= ' 2 J ';
		}

		if (isset($critere_style['stroke-miterlimit'])){
			$path_style .= sprintf('%.2f M ',$critere_style['stroke-miterlimit']);
		}
	}


		if ($critere_style['fill-opacity'] > 0 && $critere_style['fill-opacity'] <= 1){
			$gs = $this->mpdf_ref->AddExtGState(array('ca'=>$critere_style['fill-opacity'], 'BM'=>'/Normal'));
			$path_style .= sprintf(' /GS%d gs ', $gs);
		}

		if ($critere_style['stroke-opacity'] > 0 && $critere_style['stroke-opacity'] <= 1){ 
			$gs = $this->mpdf_ref->AddExtGState(array('CA'=>$critere_style['stroke-opacity'], 'BM'=>'/Normal'));
			$path_style .= sprintf(' /GS%d gs ', $gs);
		}

		switch ($style){
			case 'F':
				$op = 'f';
			break;
			case 'FD':
				$op = 'B';
			break;
			case 'ND':
				$op = 'S';
			break;
			case 'D':
				$op = 'S';
			break;
			default:
				$op = 'n';
		}

		$final_style = "$path_style $w $op $fill_gradient \n";
		// echo 'svgStyle: '. $final_style .'<br><br>';

		return $final_style;

	}

	//
	//	fonction retracant les <path />
	function svgPath($command, $arguments){

		global $xbase, $ybase;

		$path_cmd = '';


		preg_match_all('/[\-^]?[\d.]+/', $arguments, $a, PREG_SET_ORDER);


		//	if the command is a capital letter, the coords go absolute, otherwise relative
		if(strtolower($command) == $command) $relative = true;
		else $relative = false;


		$ile_argumentow = count($a);

		//	each command may have different needs for arguments [1 to 8]

		switch(strtolower($command)){
			case 'm': // move
				for($i = 0; $i<$ile_argumentow; $i+=2){
					$x = $a[$i][0]; 
					$y = $a[$i+1][0]; 
					if($relative){
						$pdfx = ($xbase + $x);
						$pdfy = ($ybase - $y);
						$xbase += $x;
						$ybase += -$y;
					}
					else{
						$pdfx = $x;
						$pdfy =  -$y ;
						$xbase = $x;
						$ybase = -$y;
					}
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					if($i == 0) $path_cmd .= sprintf('%.3f %.3f m ', $pdf_pt['x'], $pdf_pt['y']);
					else $path_cmd .= sprintf('%.3f %.3f l ',  $pdf_pt['x'], $pdf_pt['y']);
				}
			break;
			case 'l': // a simple line
				for($i = 0; $i<$ile_argumentow; $i+=2){
					$x = ($a[$i][0]); 
					$y = ($a[$i+1][0]); 
					if($relative){
						$pdfx = ($xbase + $x);
						$pdfy = ($ybase - $y);
						$xbase += $x;
						$ybase += -$y;
					}
					else{
						$pdfx = $x ;
						$pdfy =  -$y ;
						$xbase = $x;
						$ybase = -$y;
					}
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					$path_cmd .= sprintf('%.3f %.3f l ',  $pdf_pt['x'], $pdf_pt['y']);
				}
			break;
			case 'h': // a very simple horizontal line
				for($i = 0; $i<$ile_argumentow; $i++){
					$x = ($a[$i][0]); 
					if($relative){
						$y = 0;
						$pdfx = ($xbase + $x) ;
						$pdfy = ($ybase - $y) ;
						$xbase += $x;
						$ybase += -$y;
					}
					else{
						$y = -$ybase;
						$pdfx = $x;
						$pdfy =  -$y;
						$xbase = $x;
						$ybase = -$y;
					}
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					$path_cmd .= sprintf('%.3f %.3f l ', $pdf_pt['x'], $pdf_pt['y']);
				}
			break;
			case 'v': // the simplest line, vertical
				for($i = 0; $i<$ile_argumentow; $i++){
					$y = ($a[$i][0]); 
					if($relative){
						$x = 0;
						$pdfx = ($xbase + $x);
						$pdfy = ($ybase - $y);
						$xbase += $x;
						$ybase += -$y;
					}
					else{
						$x = $xbase;
						$pdfx = $x;
						$pdfy =  -$y;
						$xbase = $x;
						$ybase = -$y;
					}
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					$path_cmd .= sprintf('%.3f %.3f l ', $pdf_pt['x'], $pdf_pt['y']);
				}
			break;
			case 's': // bezier with first vertex equal first control
			   if ($this->lastcommand == 'C' || $this->lastcommand == 'c') {

				for($i = 0; $i<$ile_argumentow; $i += 4){
					$x1 = $this->lastcontrolpoints[0];
					$y1 = $this->lastcontrolpoints[1];
					$x2 = ($a[$i][0]); 
					$y2 = ($a[$i+1][0]); 
					$x = ($a[$i+2][0]); 
					$y = ($a[$i+3][0]); 
					if($relative){
						$pdfx1 = ($xbase + $x1);
						$pdfy1 = ($ybase - $y1);
						$pdfx2 = ($xbase + $x2);
						$pdfy2 = ($ybase - $y2);
						$pdfx = ($xbase + $x);
						$pdfy = ($ybase - $y);
						$xbase += $x;
						$ybase += -$y;
					}
					else{
						$pdfx1 = $xbase + $x1;
						$pdfy1 = $ybase -$y1;
						$pdfx2 = $x2;
						$pdfy2 = -$y2;
						$pdfx = $x;
						$pdfy =  -$y;
						$xbase = $x;
						$ybase = -$y;
					}
					$this->lastcontrolpoints = array(($x-$x1),($y-$y1));
					// $pdf_pt2 = $this->svg_overflow($pdfx2,$pdfy2);
					// $pdf_pt1 = $this->svg_overflow($pdfx1,$pdfy1);
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					if( ($pdf_pt['x'] != $pdfx) || ($pdf_pt['y'] != $pdfy) )
					{
						$path_cmd .= sprintf('%.3f %.3f l ',  $pdf_pt['x'], $pdf_pt['y']);
					}
					else
					{
						$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $pdfx1, $pdfy1, $pdfx2, $pdfy2, $pdfx, $pdfy);
					}

				}
			   }
			break;
			case 'c': // bezier with second vertex equal second control
			for($i = 0; $i<$ile_argumentow; $i += 6){
					$x1 = ($a[$i][0]); 
					$y1 = ($a[$i+1][0]); 
					$x2 = ($a[$i+2][0]); 
					$y2 = ($a[$i+3][0]); 
					$x = ($a[$i+4][0]); 
					$y = ($a[$i+5][0]); 
					if($relative){
						$pdfx1 = ($xbase + $x1);
						$pdfy1 = ($ybase - $y1);
						$pdfx2 = ($xbase + $x2);
						$pdfy2 = ($ybase - $y2);
						$pdfx = ($xbase + $x);
						$pdfy = ($ybase - $y);
						$xbase += $x;
						$ybase += -$y;
					}
					else{
						$pdfx1 = $x1;
						$pdfy1 = -$y1;
						$pdfx2 = $x2;
						$pdfy2 = -$y2;
						$pdfx = $x;
						$pdfy =  -$y;
						$xbase = $x;
						$ybase = -$y;
					}
					$this->lastcontrolpoints = array(($x-$x2),($y-$y2));
					// $pdf_pt2 = $this->svg_overflow($pdfx2,$pdfy2);
					// $pdf_pt1 = $this->svg_overflow($pdfx1,$pdfy1);
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					if( ($pdf_pt['x'] != $pdfx) || ($pdf_pt['y'] != $pdfy) )
					{
						$path_cmd .= sprintf('%.3f %.3f l ',  $pdf_pt['x'], $pdf_pt['y']);
					}
					else
					{
						$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $pdfx1, $pdfy1, $pdfx2, $pdfy2, $pdfx, $pdfy);
					}

				}
			break;

			case 'q': // bezier quadratic avec point de control
				for($i = 0; $i<$ile_argumentow; $i += 4){
					$x1 = ($a[$i][0]); 
					$y1 = ($a[$i+1][0]); 
					$x = ($a[$i+2][0]); 
					$y = ($a[$i+3][0]); 
					if($relative){

						$pdfx1 = ($xbase + ($x1*2/3));
						$pdfy1 = ($ybase - ($y1*2/3));

						$pdfx2 = ($xbase + $x - ($x1*2/3));
						$pdfy2 = ($ybase - $y - ($y1*2/3));

						$pdfx = ($xbase + $x);
						$pdfy = ($ybase - $y);
						$xbase += $x;
						$ybase += -$y;
					}
					else{
						$pdfx1 = ($xbase+(($x1-$xbase)*2/3));
						$pdfy1 = ($ybase-(($y1+$ybase)*2/3));

						$pdfx2 = ($x+(($x1-$x)*2/3));
						$pdfy2 = (-$y-(($y1-$y)*2/3));

						$pdfx = $x;
						$pdfy =  -$y;
						$xbase = $x;
						$ybase = -$y;
					}
					$this->lastcontrolpoints = array(($x-$x1),($y-$y1));

					// $pdf_pt2 = $this->svg_overflow($pdfx2,$pdfy2);
					// $pdf_pt1 = $this->svg_overflow($pdfx1,$pdfy1);
					$pdf_pt = $this->svg_overflow($pdfx,$pdfy);
					if( ($pdf_pt['x'] != $pdfx) || ($pdf_pt['y'] != $pdfy) )
					{
						$path_cmd .= sprintf('%.3f %.3f l ',  $pdf_pt['x'], $pdf_pt['y']);
					}
					else
					{
						$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $pdfx1, $pdfy1, $pdfx2, $pdfy2, $pdfx, $pdfy);
					}
				}
			break;
			case 't': // bezier quadratic avec point de control simetrique a lancien point de control
				if ($this->lastcommand == 'Q' || $this->lastcommand == 'q' || $this->lastcommand == 'T' || $this->lastcommand == 't') {
					$x = ($a[0][0]); 
					$y = ($a[1][0]); 

					$x1 = $this->lastcontrolpoints[0];
					$y1 = $this->lastcontrolpoints[1];

					if($relative){
						$pdfx = ($xbase + $x);
						$pdfy = ($ybase - $y);

						$pdfx1 = ($xbase + ($x1*2/3));
						$pdfy1 = ($ybase - ($y1*2/3));
						$pdfx2 = ($xbase + $x - ($x1*2/3));
						$pdfy2 = ($ybase -$y - ($y1*2/3));

						$xbase += $x;
						$ybase += -$y;
					}
					else{
						$pdfx = $x;
						$pdfy =  -$y;

						$pdfx1 = ($xbase + ($x1*2/3));
						$pdfy1 = ($ybase - ($y1*2/3));
						$pdfx2 = ($x - ($x1*2/3));
						$pdfy2 = (-$y - ($y1*2/3));

						$xbase = $x;
						$ybase = -$y;
					}




					$this->lastcontrolpoints = array(($x-$pdfx2),($y-$pdfy2));
					$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $pdfx1, $pdfy1, $pdfx2, $pdfy2, $pdfx, $pdfy);


				}
				$this->lastcontrolpoints = array();
			break;
			case 'a':	// Elliptical arc
				for($i = 0; $i<$ile_argumentow; $i += 7){
					$rx = ($a[$i][0]); 
					$ry = ($a[$i+1][0]); 
					$angle = ($a[$i+2][0]); //x-axis-rotation 
					$largeArcFlag = ($a[$i+3][0]); 
					$sweepFlag = ($a[$i+4][0]); 
					$x2 = ($a[$i+5][0]); 
					$y2 = ($a[$i+6][0]); 
					$x1 = $xbase;
					$y1 = -$ybase;
					if($relative){
						$x2 = $xbase + $x2;
						$y2 = -$ybase + $y2;
						$xbase += ($a[$i+5][0]); 
						$ybase += -($a[$i+6][0]); 
					}
					else{
						$xbase = $x2;
						$ybase = -$y2;
					}
					$path_cmd .= $this->Arcto($x1, $y1, $x2, $y2, $rx, $ry, $angle, $largeArcFlag, $sweepFlag);

				}
			break;
			case'z':
				$path_cmd .= 'h ';
			break;
			default:
			break;
			}

		$this->lastcommand = $command;

		return $path_cmd;

	}

function Arcto($x1, $y1, $x2, $y2, $rx, $ry, $angle, $largeArcFlag, $sweepFlag) {

	// 1. Treat out-of-range parameters as described in
	// http://www.w3.org/TR/SVG/implnote.html#ArcImplementationNotes
	// If the endpoints (x1, y1) and (x2, y2) are identical, then this
	// is equivalent to omitting the elliptical arc segment entirely
	if ($x1 == $x2 && $y1 == $y2) return '';

	// If rX = 0 or rY = 0 then this arc is treated as a straight line
	// segment (a "lineto") joining the endpoints.
	if ($rx == 0.0 || $ry == 0.0) {
	//   return Lineto(x2, y2);	// ****
	}

	// If rX or rY have negative signs, these are dropped; the absolute
	// value is used instead.
	if ($rx<0.0) $rx = -$rx;
	if ($ry<0.0) $ry = -$ry;

	// 2. convert to center parameterization as shown in
	// http://www.w3.org/TR/SVG/implnote.html
	$sinPhi = sin(deg2rad($angle));
	$cosPhi = cos(deg2rad($angle));

	$x1dash =  $cosPhi * ($x1-$x2)/2.0 + $sinPhi * ($y1-$y2)/2.0;
	$y1dash = -$sinPhi * ($x1-$x2)/2.0 + $cosPhi * ($y1-$y2)/2.0;


	$numerator = $rx*$rx*$ry*$ry - $rx*$rx*$y1dash*$y1dash - $ry*$ry*$x1dash*$x1dash;

	if ($numerator < 0.0) { 
		//  If rX , rY and are such that there is no solution (basically,
		//  the ellipse is not big enough to reach from (x1, y1) to (x2,
		//  y2)) then the ellipse is scaled up uniformly until there is
		//  exactly one solution (until the ellipse is just big enough).

		// -> find factor s, such that numerator' with rx'=s*rx and
		//    ry'=s*ry becomes 0 :
		$s = sqrt(1.0 - $numerator/($rx*$rx*$ry*$ry));

		$rx *= $s;
		$ry *= $s;
		$root = 0.0;

	}
	else {
		$root = ($largeArcFlag == $sweepFlag ? -1.0 : 1.0) * sqrt( $numerator/($rx*$rx*$y1dash*$y1dash+$ry*$ry*$x1dash*$x1dash) );
	}

	$cxdash = $root*$rx*$y1dash/$ry;
	$cydash = -$root*$ry*$x1dash/$rx;

	$cx = $cosPhi * $cxdash - $sinPhi * $cydash + ($x1+$x2)/2.0;
	$cy = $sinPhi * $cxdash + $cosPhi * $cydash + ($y1+$y2)/2.0;


	$theta1 = $this->CalcVectorAngle(1.0, 0.0, ($x1dash-$cxdash)/$rx, ($y1dash-$cydash)/$ry);
	$dtheta = $this->CalcVectorAngle(($x1dash-$cxdash)/$rx, ($y1dash-$cydash)/$ry, (-$x1dash-$cxdash)/$rx, (-$y1dash-$cydash)/$ry);
	if (!$sweepFlag && $dtheta>0)
		$dtheta -= 2.0*M_PI;
	else if ($sweepFlag && $dtheta<0)
		$dtheta += 2.0*M_PI;

	// 3. convert into cubic bezier segments <= 90deg
	$segments = ceil(abs($dtheta/(M_PI/2.0)));
	$delta = $dtheta/$segments;
	$t = 8.0/3.0 * sin($delta/4.0) * sin($delta/4.0) / sin($delta/2.0);
	$coords = array();
	for ($i = 0; $i < $segments; $i++) {
		$cosTheta1 = cos($theta1);
		$sinTheta1 = sin($theta1);
		$theta2 = $theta1 + $delta;
		$cosTheta2 = cos($theta2);
		$sinTheta2 = sin($theta2);

		// a) calculate endpoint of the segment:
		$xe = $cosPhi * $rx*$cosTheta2 - $sinPhi * $ry*$sinTheta2 + $cx;
		$ye = $sinPhi * $rx*$cosTheta2 + $cosPhi * $ry*$sinTheta2 + $cy;

		// b) calculate gradients at start/end points of segment:
		$dx1 = $t * ( - $cosPhi * $rx*$sinTheta1 - $sinPhi * $ry*$cosTheta1);
		$dy1 = $t * ( - $sinPhi * $rx*$sinTheta1 + $cosPhi * $ry*$cosTheta1);

		$dxe = $t * ( $cosPhi * $rx*$sinTheta2 + $sinPhi * $ry*$cosTheta2);
		$dye = $t * ( $sinPhi * $rx*$sinTheta2 - $cosPhi * $ry*$cosTheta2);

		// c) draw the cubic bezier:
		$coords[$i] = array(($x1+$dx1), ($y1+$dy1), ($xe+$dxe), ($ye+$dye), $xe, $ye);

		// do next segment
		$theta1 = $theta2;
		$x1 = $xe;
		$y1 = $ye;
	}
	$path = ' ';
	foreach($coords AS $c) {
		$cpx1 = $c[0];
		$cpy1 = $c[1];
		$cpx2 = $c[2];
		$cpy2 = $c[3];
		$x2 = $c[4];
		$y2 = $c[5];
		$path .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $cpx1, -$cpy1, $cpx2, -$cpy2, $x2, -$y2)  ."\n";
	}
	return $path ;
}


	function CalcVectorAngle($ux, $uy, $vx, $vy) {
		$ta = atan2($uy, $ux);
		$tb = atan2($vy, $vx);
		if ($tb >= $ta)
			return ($tb-$ta);
		return (6.28318530718 - ($ta-$tb));
	}

	//
	//	fonction retracant les <rect />
	function svgRect($arguments){

		$x = $arguments['x']; 
		$y = $arguments['y']; 
		$h = $arguments['h']; 
		$w = $arguments['w']; 
		$rx = ($arguments['rx']/2); 
		$ry = ($arguments['ry']/2); 

		if ($rx>0 and $ry == 0){$ry = $rx;}
		if ($ry>0 and $rx == 0){$rx = $ry;}

		if ($rx == 0 and $ry == 0){
			//	trace un rectangle sans angle arrondit
			$path_cmd = sprintf('%.3f %.3f m ', ($x), -($y));
			$path_cmd .= sprintf('%.3f %.3f l ', (($x+$w)), -($y));
			$path_cmd .= sprintf('%.3f %.3f l ', (($x+$w)), -(($y+$h)));
			$path_cmd .= sprintf('%.3f %.3f l ', ($x), -(($y+$h)));
			$path_cmd .= sprintf('%.3f %.3f l h ', ($x), -($y));

			
		}
		else {
			//	trace un rectangle avec les arrondit
			//	les points de controle du bezier sont deduis grace a la constante kappa
			$kappa = 4*(sqrt(2)-1)/3;

			$kx = $kappa*$rx;
			$ky = $kappa*$ry;

			$path_cmd = sprintf('%.3f %.3f m ', $x+($rx), -$y);
			$path_cmd .= sprintf('%.3f %.3f l ', $x+(($w-$rx)), -$y);
			$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $x+(($w-$rx+$kx)), -$y, $x+($w), -$y+((-$ry+$ky)), $x+($w), -$y+(-$ry) );
			$path_cmd .= sprintf('%.3f %.3f l ', $x+($w), -$y+((-$h+$ry)));
		 	$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $x+($w), -$y+((-$h-$ky+$ry)), $x+(($w-$rx+$kx)), -$y+(-$h), $x+(($w-$rx)), -$y+(-$h) );

			$path_cmd .= sprintf('%.3f %.3f l ', $x+($rx), -$y+(-$h));
			$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $x+(($rx-$kx)), -$y+(-$h), $x, -$y+((-$h-$ky+$ry)), $x, -$y+((-$h+$ry)) );
			$path_cmd .= sprintf('%.3f %.3f l ', $x, -$y+(-$ry));
			$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c h ', $x, -$y+((-$ry+$ky)), $x+(($rx-$kx)), -$y, $x+($rx), -$y );


		}
		return $path_cmd;
	}

	//
	//	fonction retracant les <ellipse /> et <circle />
	//	 le cercle est tracé grave a 4 bezier cubic, les poitn de controles
	//	sont deduis grace a la constante kappa * rayon
	function svgEllipse($arguments){
		$kappa = 4*(sqrt(2)-1)/3;

		$cx = $arguments['cx'] ;
		$cy = $arguments['cy'] ;
		$rx = $arguments['rx'] ;
		$ry = $arguments['ry'] ;

		$x1 = $cx;
		$y1 = -$cy+$ry;

		$x2 = $cx+$rx;
		$y2 = -$cy;

		$x3 = $cx;
		$y3 = -$cy-$ry;

		$x4 = $cx-$rx;
		$y4 = -$cy;

		$path_cmd = sprintf('%.3f %.3f m ', $x1, $y1);
		$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $x1+($rx*$kappa), $y1, $x2, $y2+($ry*$kappa), $x2, $y2);
		$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $x2, $y2-($ry*$kappa), $x3+($rx*$kappa), $y3, $x3, $y3);
		$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $x3-($rx*$kappa), $y3, $x4, $y4-($ry*$kappa), $x4, $y4);
		$path_cmd .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $x4, $y4+($ry*$kappa), $x1-($rx*$kappa), $y1, $x1, $y1);
		$path_cmd .= 'h ';

		return $path_cmd;

	}

	//
	//	fonction retracant les <polyline /> et les <line />
	function svgPolyline($arguments){
		$xbase = $arguments[0] ;
		$ybase = - $arguments[1] ;
		$path_cmd = sprintf('%.3f %.3f m ', $xbase, $ybase);
		for ($i = 2; $i<count($arguments);$i += 2) {

			$tmp_x = $arguments[$i] ;
			$tmp_y = - $arguments[($i+1)] ;
			$path_cmd .= sprintf('%.3f %.3f l ', $tmp_x, $tmp_y);
		}

	//	$path_cmd .= 'h '; // ?? In error - don't close subpath here
		return $path_cmd;

	}

	//
	//	fonction retracant les <polygone />
	function svgPolygon($arguments){
		$xbase = $arguments[0] ;
		$ybase = - $arguments[1] ;
		$path_cmd = sprintf('%.3f %.3f m ', $xbase, $ybase);
		for ($i = 2; $i<count($arguments);$i += 2) {
			$tmp_x = $arguments[$i] ;
			$tmp_y = - $arguments[($i+1)] ;

			$path_cmd .= sprintf('%.3f %.3f l ', $tmp_x, $tmp_y);

		}
		$path_cmd .= sprintf('%.3f %.3f l ', $xbase, $ybase);
		$path_cmd .= 'h ';
		return $path_cmd;

	}

	//
	//	write string to image
	function svgText(){
		// $tmp = count($this->txt_style)-1;
		$current_style = array_pop($this->txt_style);
		$style = '';
		if(isset($this->txt_data[2]))
		{
			// select font
			$style .= ($current_style['font-weight'] == 'bold')?'B':'';
			$style .= ($current_style['font-style'] == 'italic')?'I':'';
			$size = $current_style['font-size'];
			$current_style['font-family'] = $this->mpdf_ref->SetFont($current_style['font-family'],$style,$current_style['font-size'],false);

			$col = $this->mpdf_ref->ConvertColor($current_style['fill']);

			$x = $this->txt_data[0];
			$y = $this->txt_data[1];
			$txt = $this->txt_data[2];
			$txt = trim($txt);

			$txt = $this->mpdf_ref->purify_utf8_text($txt);
			if ($this->mpdf_ref->text_input_as_HTML) {
				$txt = $this->mpdf_ref->all_entities_to_utf8($txt);
			}
			if (!$this->mpdf_ref->is_MB) { $txt = mb_convert_encoding($txt,$this->mpdf_ref->mb_enc,'UTF-8'); }
			$this->mpdf_ref->magic_reverse_dir($txt);	
			$this->mpdf_ref->ConvertIndic($txt);

			if ($current_style['text-anchor']=='middle') {
				$tw = $this->mpdf_ref->GetStringWidth($txt)*2.835/2;
			}
			else if ($current_style['text-anchor']=='end') {
				$tw = $this->mpdf_ref->GetStringWidth($txt)*2.835;
			}
			else $tw = 0;

			if ($this->mpdf_ref->useSubsets && $this->mpdf_ref->CurrentFont['type']=='Type1subset' && !$this->mpdf_ref->isCJK && !$this->mpdf_ref->usingCoreFont) {
				$txt = $this->mpdf_ref->UTF8toSubset($txt);
			}
			else {
				if ($this->mpdf_ref->is_MB && !$this->mpdf_ref->usingCoreFont) {
					$txt= $this->mpdf_ref->UTF8ToUTF16BE($txt, false);
				}
				$txt='('.$this->mpdf_ref->_escape($txt).')'; 
			}
			$this->mpdf_ref->CurrentFont['used']= true;

			$pdfx = ($x - $tw);
			$pdfy =  -$y  ;
			$xbase = $x;
			$ybase = -$y;

			$path_cmd =  sprintf('q BT /F%d %.3f Tf %.3f %.3f Td 0 Tr %.3f %.3f %.3f rg %s Tj ET Q ',$this->mpdf_ref->CurrentFont['i'],$this->mpdf_ref->FontSizePt,$pdfx,$pdfy,$col['R']/255,$col['G']/255,$col['B']/255,$txt);

			unset($this->txt_data[0], $this->txt_data[1],$this->txt_data[2]);
		}
		else
		{
			die("No string to write!");
		}
		$path_cmd .= 'h ';
		return $path_cmd;
	}


function svgDefineTxtStyle($critere_style)
{
		// get copy of current/default txt style, and modify it with supplied attributes
		$tmp = count($this->txt_style)-1;
		$current_style = $this->txt_style[$tmp];

		if (isset($critere_style['font'])){

			// [ [ <'font-style'> || <'font-variant'> || <'font-weight'> ]?<'font-size'> [ / <'line-height'> ]? <'font-family'> ]

			$tmp = preg_replace("/(.*)(italic|oblique)(.*)/i","$2",$critere_style['font']);
			if ($tmp != $critere_style['font']){ 
				if($tmp == 'oblique'){
					$tmp = 'italic';
				}
				$current_style['font-style'] = $tmp;
			}
			$tmp = preg_replace("/(.*)(bold|bolder)(.*)/i","$2",$critere_style['font']);
			if ($tmp != $critere_style['font']){ 
				if($tmp == 'bolder'){
					$tmp = 'bold';
				}
				$current_style['font-weight'] = $tmp;
			}
			
			// select digits not followed by percent sign nor preceeded by forward slash
			$tmp = preg_replace("/(.*)\b(\d+)[\b|\/](.*)/i","$2",$critere_style['font']);
			if ($tmp != $critere_style['font']){ $current_style['font-size'] = $tmp; }
			
		}

		if(isset($critere_style['fill'])){
			$current_style['fill'] = $critere_style['fill'];
		}
		
		if(isset($critere_style['font-style'])){
			if(strtolower($critere_style['font-style']) == 'oblique') 
			{
				$critere_style['font-style'] = 'italic';
			}
			$current_style['font-style'] = $critere_style['font-style'];
		}
		
		if(isset($critere_style['font-weight'])){
			if(strtolower($critere_style['font-weight']) == 'bolder')
			{
				$critere_style['font-weight'] = 'bold';
			}
			$current_style['font-weight'] = $critere_style['font-weight'];
		}
		
		if(isset($critere_style['font-size'])){
			$current_style['font-size'] = $this->mpdf_ref->ConvertSize($critere_style['font-size'])* 2.835;
		}
		
		if(isset($critere_style['font-family'])){
			$current_style['font-family'] = $critere_style['font-family'];
		}
	
		if(isset($critere_style['text-anchor'])){
			$current_style['text-anchor'] = $critere_style['text-anchor'];
		}
	
	// add current style to text style array (will remove it later after writing text to svg_string)
	array_push($this->txt_style,$current_style);
}



	//
	//	fonction ajoutant un gradient
	function svgAddGradient($id,$array_gradient){

		$this->svg_gradient[$id] = $array_gradient;

	}
	//
	//	Ajoute une couleur dans le gradient correspondant

	//
	//	function ecrivant dans le svgstring
	function svgWriteString($content){

		$this->svg_string .= $content;

	}



	//	analise le svg et renvoie aux fonctions precedente our le traitement
	function ImageSVG($data){
		$this->svg_info = array();
		$this->svg_info['data'] = $data;

		$this->svg_string = '';
		
		//
		//	chargement unique des fonctions
		if(!function_exists(xml_svg2pdf_start)){

			function xml_svg2pdf_start($parser, $name, $attribs){
				//
				//	definition
				global $svg_class, $last_gradid;
				switch (strtolower($name)){

				case 'svg':
					$svg_class->svgOffset($attribs);
					break;

				case 'path':
					$path = $attribs['d'];
					preg_match_all('/([a-z]|[A-Z])([ ,\-.\d]+)*/', $path, $commands, PREG_SET_ORDER);
					$path_cmd = '';
					foreach($commands as $c){
						if(count($c)==3){
							list($tmp, $command, $arguments) = $c;
						}
						else{
							list($tmp, $command) = $c;
							$arguments = '';
						}

						$path_cmd .= $svg_class->svgPath($command, $arguments);
					}
					$critere_style = $attribs;
					unset($critere_style['d']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'rect':
					if (!isset($attribs['x'])) {$attribs['x'] = 0;}
					if (!isset($attribs['y'])) {$attribs['y'] = 0;}
					if (!isset($attribs['rx'])) {$attribs['rx'] = 0;}
					if (!isset($attribs['ry'])) {$attribs['ry'] = 0;}
					$arguments = array(
						'x' => $attribs['x'],
						'y' => $attribs['y'],
						'w' => $attribs['width'],
						'h' => $attribs['height'],
						'rx' => $attribs['rx'],
						'ry' => $attribs['ry']
					);
					$path_cmd =  $svg_class->svgRect($arguments);
					$critere_style = $attribs;
					unset($critere_style['x'],$critere_style['y'],$critere_style['rx'],$critere_style['ry'],$critere_style['height'],$critere_style['width']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'circle':
					if (!isset($attribs['cx'])) {$attribs['cx'] = 0;}
					if (!isset($attribs['cy'])) {$attribs['cy'] = 0;}
					$arguments = array(
						'cx' => $attribs['cx'],
						'cy' => $attribs['cy'],
						'rx' => $attribs['r'],
						'ry' => $attribs['r']
					);
					$path_cmd =  $svg_class->svgEllipse($arguments);
					$critere_style = $attribs;
					unset($critere_style['cx'],$critere_style['cy'],$critere_style['r']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'ellipse':
					if (!isset($attribs['cx'])) {$attribs['cx'] = 0;}
					if (!isset($attribs['cy'])) {$attribs['cy'] = 0;}
					$arguments = array(
						'cx' => $attribs['cx'],
						'cy' => $attribs['cy'],
						'rx' => $attribs['rx'],
						'ry' => $attribs['ry']
					);
					$path_cmd =  $svg_class->svgEllipse($arguments);
					$critere_style = $attribs;
					unset($critere_style['cx'],$critere_style['cy'],$critere_style['rx'],$critere_style['ry']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'line':
					$arguments = array($attribs['x1'],$attribs['y1'],$attribs['x2'],$attribs['y2']);
					$path_cmd =  $svg_class->svgPolyline($arguments);
					$critere_style = $attribs;
					unset($critere_style['x1'],$critere_style['y1'],$critere_style['x2'],$critere_style['y2']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'polyline':
					$path = $attribs['points'];
					preg_match_all('/[0-9\-\.]*/',$path, $tmp, PREG_SET_ORDER);
					$arguments = array();
					for ($i=0;$i<count($tmp);$i++){
						if ($tmp[$i][0] !=''){
							array_push($arguments, $tmp[$i][0]);
						}
					}
					$path_cmd =  $svg_class->svgPolyline($arguments);
					$critere_style = $attribs;
					unset($critere_style['points']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'polygon':
					$path = $attribs['points'];
					preg_match_all('/([\-]*[0-9\.]+)/',$path, $tmp);
					$arguments = array();
					for ($i=0;$i<count($tmp[0]);$i++){
						if ($tmp[0][$i] !=''){
							array_push($arguments, $tmp[0][$i]);
						}
					}
					$path_cmd =  $svg_class->svgPolygon($arguments);
					//	definition du style de la forme:
					$critere_style = $attribs;
					unset($critere_style['points']);
					$path_style = $svg_class->svgDefineStyle($critere_style);
					break;

				case 'lineargradient':
						$tmp_gradient = array(
							'type' => 'linear',
							'info' => array(
								'x1' => $attribs['x1'],
								'y1' => $attribs['y1'],
								'x2' => $attribs['x2'],
								'y2' => $attribs['y2']
							),
							'transform' => $attribs['gradientTransform'],
							'color' => array()
						);

						$last_gradid = $attribs['id'];

						$svg_class->svgAddGradient($attribs['id'],$tmp_gradient);

					break;

				case 'radialgradient':

						$tmp_gradient = array(
							'type' => 'radial',
							'info' => array(
								'x0' => $attribs['cx'],
								'y0' => $attribs['cy'],
								'x1' => $attribs['fx'],
								'y1' => $attribs['fy'],
								'r' => $attribs['r']
							),
							'transform' => $attribs['gradientTransform'],
							'color' => array()
						);

						$last_gradid = $attribs['id'];

						$svg_class->svgAddGradient($attribs['id'],$tmp_gradient);

					break;

				case 'stop':

if (!$last_gradid) break;
						if (isset($attribs['style']) AND !isset($attribs['stop-color'])){
							$color = preg_replace('/stop-color:([0-9#]*)/i','$1',$attribs['style']);
						} else {
							$color = $attribs['stop-color'];
						}
						$col = $svg_class->mpdf_ref->ConvertColor($color);
						$color_r = $col['R'];
						$color_g = $col['G'];
						$color_b = $col['B'];
						$color_final = $path_style .= sprintf('%.3f %.3f %.3f',$color_r/255,$color_g/255,$color_b/255);
						$tmp_color = array(
							'color' => $color_final,
							'offset' => $attribs['offset'],
							'opacity' => $attribs['stop-opacity']
						);
						array_push($svg_class->svg_gradient[$last_gradid]['color'],$tmp_color);
					break;


				case 'g':
						$array_style = $svg_class->svgDefineStyle($attribs);
						if ($array_style['transformations']) {
							$svg_class->svgWriteString(' q '.$array_style['transformations']);
						}
						array_push($svg_class->svg_style,$array_style);
					break;

				case 'text':
						$svg_class->txt_data = array();
						$svg_class->txt_data[0] = $attribs['x'];
						$svg_class->txt_data[1] = $attribs['y'];
						$critere_style = $attribs;
						unset($critere_style['x'], $critere_style['y']);
						$svg_class->svgDefineTxtStyle($critere_style);

					break;
				}

				//
				//insertion des path et du style dans le flux de donné general.
				if (isset($path_cmd)){
					$get_style = $svg_class->svgStyle($path_style, $attribs, strtolower($name));
					if ($path_style['transformations']) {	// transformation on an element
						$svg_class->svgWriteString(" q ".$path_style['transformations']. "$path_cmd $get_style" . " Q\n");
					}
					else {
						$svg_class->svgWriteString("$path_cmd $get_style\n");
					}
				}
			}

			function characterData($parser, $data)
			{
				global $svg_class;
				if(isset($svg_class->txt_data[2]))
				{
					$svg_class->txt_data[2] .= $data;
				}
				else
				{
					$svg_class->txt_data[2] = $data;
				}
			}


			function xml_svg2pdf_end($parser, $name){
				global $svg_class;
				switch($name){
					case "g":
						$tmp = count($svg_class->svg_style)-1;
						$current_style = $svg_class->svg_style[$tmp];
						if ($current_style['transformations']) {
							$svg_class->svgWriteString(" Q ");
						}
						array_pop($svg_class->svg_style);
					break;
					case 'radialgradient':
					case 'lineargradient':
						$last_gradid = '';
					break;
					case "text":
						$path_cmd = $svg_class->svgText();
						// echo 'path >> '.$path_cmd."<br><br>";
						// echo "style >> ".$get_style[1]."<br><br>";
						$svg_class->svgWriteString($path_cmd);
					break;
				}

			}

		}

		$svg2pdf_xml='';
		global $svg_class;

		$svg_class = $this;
 		$svg2pdf_xml_parser = xml_parser_create("utf-8");
		xml_parser_set_option($svg2pdf_xml_parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($svg2pdf_xml_parser, "xml_svg2pdf_start", "xml_svg2pdf_end");
		xml_set_character_data_handler($svg2pdf_xml_parser, "characterData");
		xml_parse($svg2pdf_xml_parser, $data);

		return array('x'=>$this->svg_info['x'],'y'=>-$this->svg_info['y'],'w'=>$this->svg_info['w'],'h'=>-$this->svg_info['h'],'data'=>$svg_class->svg_string);

	}

}

?>