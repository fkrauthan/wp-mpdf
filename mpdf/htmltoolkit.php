<?php
/*******************************************************************************
* Software: mPDF, Unicode-HTML Free PDF generator                              *
* Version:  3.2 based on                                                       *
*           FPDF 1.52 by Olivier PLATHEY                                       *
*           UFPDF 0.1 by Steven Wittens                                        *
*           HTML2FPDF 3.0.2beta by Renato Coelho                               *
* Date:     2009-10-25                                                         *
* Author:   Ian Back <ianb@bpm1.com>                                           *
* License:  GPL                                                                *
*                                                                              *
* Changes:	See ChangeLog.txt                                                  *
*******************************************************************************/




function GetCodepage($llcc) {
	if (strlen($llcc) == 5) {
		$lang = substr(strtolower($llcc),0,2);
		$country = substr(strtoupper($llcc),3,2);
	}
	else { $lang = strtolower($llcc); $country = ''; }
	$mpdf_pdf_unifonts = "";
	$mpdf_directionality = "ltr";
	$mpdf_jSpacing = "";

	if ($lang == "en" && $country == "GB") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "en") { $mpdf_codepage = "win-1252"; }

	else if ($lang == "ca") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "cy") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "da") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "de") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "es") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "eu") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "fr") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "ga") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "fi") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "is") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "it") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "nl") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "no") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "pt") { $mpdf_codepage = "win-1252"; }
	else if ($lang == "sv") { $mpdf_codepage = "win-1252"; }

	// ISO-8859-2
	else if ($lang == "cs") { $mpdf_codepage = "iso-8859-2"; }
	else if ($lang == "hr") { $mpdf_codepage = "iso-8859-2"; }
	else if ($lang == "hu") { $mpdf_codepage = "iso-8859-2"; }
	else if ($lang == "pl") { $mpdf_codepage = "iso-8859-2"; }
	else if ($lang == "ro") { $mpdf_codepage = "iso-8859-2"; }
	else if ($lang == "sk") { $mpdf_codepage = "iso-8859-2"; }
	else if ($lang == "sl") { $mpdf_codepage = "iso-8859-2"; }

	// ISO-8859-4
	else if ($lang == "et") { $mpdf_codepage = "iso-8859-4"; }
	else if ($lang == "kl") { $mpdf_codepage = "iso-8859-4"; }
	else if ($lang == "lt") { $mpdf_codepage = "iso-8859-4"; }
	else if ($lang == "lv") { $mpdf_codepage = "iso-8859-4"; }

	// WIN-1251
	else if ($lang == "bg") { $mpdf_codepage = "win-1251"; }
	else if ($lang == "mk") { $mpdf_codepage = "win-1251"; }
	else if ($lang == "ru") { $mpdf_codepage = "win-1251"; }
	else if ($lang == "sr") { $mpdf_codepage = "win-1251"; }
	else if ($lang == "uk") { $mpdf_codepage = "win-1251"; }

	// ISO-8859-9 (Turkish)
	else if ($lang == "tr") { $mpdf_codepage = "iso-8859-9"; }

	// ISO-8859-7 (Greek)
	else if ($lang == "el") { $mpdf_codepage = "iso-8859-7"; }

	// UTF-8
	else if ($lang == "id") { $mpdf_codepage = "UTF-8"; }
	else if ($lang == "ms") { $mpdf_codepage = "UTF-8"; }
	else if ($lang == "sh") { $mpdf_codepage = "UTF-8"; }
	else if ($lang == "sq") { $mpdf_codepage = "UTF-8"; }
	else if ($lang == "af") { $mpdf_codepage = "UTF-8"; }
	else if ($lang == "be") { $mpdf_codepage = "UTF-8"; }
	else if ($lang == "fo") { $mpdf_codepage = "UTF-8"; }
	else if ($lang == "gl") { $mpdf_codepage = "UTF-8"; }
	else if ($lang == "gv") { $mpdf_codepage = "UTF-8"; }

	// RTL Languages
	else if ($lang == "he") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "dejavusans,dejavusansB,dejavusansI,dejavusansBI,dejavusanscondensed,dejavusanscondensedB,dejavusanscondensedI,dejavusanscondensedBI,freesans,freesansB,freesansI,freesansBI,freeserif,freeserifB,freeserifI,freeserifBI,freemono,freemonoB,freemonoI,freemonoBI,scheherazade"; $mpdf_directionality = "rtl";  $mpdf_jSpacing = "W"; }
	else if ($lang == "ar") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "dejavusans,dejavusansB,dejavusansI,dejavusansBI,dejavusanscondensed,dejavusanscondensedB,dejavusanscondensedI,dejavusanscondensedBI"; $mpdf_directionality = "rtl";  $mpdf_jSpacing = "W"; }
	else if ($lang == "fa") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "dejavusans,dejavusansB,dejavusansI,dejavusansBI,dejavusanscondensed,dejavusanscondensedB,dejavusanscondensedI,dejavusanscondensedBI"; $mpdf_directionality = "rtl";  $mpdf_jSpacing = "W"; }

	else if ($lang == "ps") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "scheherazade"; $mpdf_directionality = "rtl"; $mpdf_jSpacing = "W";  }
	else if ($lang == "ur") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "scheherazade"; $mpdf_directionality = "rtl"; $mpdf_jSpacing = "W";  }
	else if ($lang == "sd" && ($country == "PK" || $country == "")) { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "scheherazade"; $mpdf_directionality = "rtl"; $mpdf_jSpacing = "W";  }

	// INDIC - only partial coverage
	// Assamese
	else if ($lang == "as") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "freesans"; $mpdf_jSpacing = "W";  }
	// Bengali
	else if ($lang == "bn") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "freesans,bengali-sans,bengali-serif"; $mpdf_jSpacing = "W";  }
	// Gujarati
	else if ($lang == "gu") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "gujarati-serif"; $mpdf_jSpacing = "W";  }
	// Hindi (Devanagari)
	else if ($lang == "hi") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "freesans,devanagari-sans,devanagari-serif"; $mpdf_jSpacing = "W";  }
	// Kannada
	else if ($lang == "kn") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "kannada-serif"; $mpdf_jSpacing = "W";  }
	// Kashmiri
	else if ($lang == "ks") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "freesans"; $mpdf_jSpacing = "W";  }
	// Malayalam
	else if ($lang == "ml") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "malayalam-sans,malayalam-serif"; $mpdf_jSpacing = "W";  }
	// Nepali (Devanagari)
	else if ($lang == "ne") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "freesans,devanagari-sans,devanagari-serif"; $mpdf_jSpacing = "W";  }
	// Oriya
	else if ($lang == "or") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "oriya-sans"; $mpdf_jSpacing = "W";  }
	// Punjabi
	else if ($lang == "pa") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "punjabi-sans,freesans"; $mpdf_jSpacing = "W";  }
	// Sindhi (Devanagari)
	else if ($lang == "sd" && $country == "IN") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "freesans,devanagari-sans,devanagari-serif"; $mpdf_jSpacing = "W";  }
	// Tamil
	else if ($lang == "ta") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "tamil-sans,freesans"; $mpdf_jSpacing = "W";  }
	// Telegu
	else if ($lang == "te") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "telugu-sans,freeserif"; $mpdf_jSpacing = "W";  }

	// VIETNAMESE and THAI
	else if ($lang == "th") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "garuda,garudaB,garudaI,garudaBI,norasi,norasiB,norasiI,norasiBI,freeserif"; }
	else if ($lang == "vi") { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "dejavusans,dejavusansB,dejavusansI,dejavusansBI,dejavuserif,dejavuserifB,dejavuserifI,dejavuserifBI,dejavusanscondensed,dejavusanscondensedB,dejavusanscondensedI,dejavusanscondensedBI,dejavuserifcondensed,dejavuserifcondensedB,dejavuserifcondensedI,dejavuserifcondensedBI"; $mpdf_jSpacing = "C";  }

	// CJK Langauges
	else if ($lang == "ja") { $mpdf_codepage = "SHIFT_JIS"; $mpdf_jSpacing = "C"; }
	else if ($lang == "ko") { $mpdf_codepage = "UHC"; $mpdf_jSpacing = "C"; }

	else if ($lang == "zh" && $country == "HK") { $mpdf_codepage = "BIG5"; $mpdf_jSpacing = "C"; }
	else if ($lang == "zh" && $country == "TW") { $mpdf_codepage = "BIG5"; $mpdf_jSpacing = "C"; }
	else if ($lang == "zh" && $country == "CN") { $mpdf_codepage = "GBK"; $mpdf_jSpacing = "C"; }
	else if ($lang == "zh") { $mpdf_codepage = "GBK"; $mpdf_jSpacing = "C"; }

	// UTF-8
	else { $mpdf_codepage = "UTF-8"; $mpdf_pdf_unifonts = "dejavusans,dejavusansB,dejavusansI,dejavusansBI,dejavuserif,dejavuserifB,dejavuserifI,dejavuserifBI,dejavusanscondensed,dejavusanscondensedB,dejavusanscondensedI,dejavusanscondensedBI,dejavuserifcondensed,dejavuserifcondensedB,dejavuserifcondensedI,dejavuserifcondensedBI"; $mpdf_jSpacing = "C";  }

	$mpdf_pdf_unifonts_arr = array();
	if ($mpdf_pdf_unifonts) {
		// mPDF 3.2 correct for any spaces left between font names
		$mpdf_pdf_unifonts_arr = preg_split('/\s*,\s*/',$mpdf_pdf_unifonts);
	}
	return array($mpdf_codepage,$mpdf_pdf_unifonts_arr,$mpdf_directionality,$mpdf_jSpacing);
}
// =======================================================================================================
// Added mPDF 1.2 for CSS handling
if(!function_exists('array_merge_recursive_unique')){ 
  function array_merge_recursive_unique($array1, $array2)
  {
    $arrays = func_get_args();
    $narrays = count($arrays);
    $ret = $arrays[0];
    for ($i = 1; $i < $narrays; $i ++) {
        foreach ($arrays[$i] as $key => $value) {
            if (((string) $key) === ((string) intval($key))) { // integer or string as integer key - append
                $ret[] = $value;
            }
            else { // string key - megre
                if (is_array($value) && isset($ret[$key])) {
                    $ret[$key] = array_merge_recursive_unique($ret[$key], $value);
                }
                else {
                    $ret[$key] = $value;
                }
            }
        }   
    }
    return $ret;
  }
}
// =======================================================================================================
// Added mPDF 1.4
// Used for tableBorders
if(!function_exists('rgb2hsl')){ 
  function rgb2hsl($var_r, $var_g, $var_b) {
    $var_min = min($var_r,$var_g,$var_b);
    $var_max = max($var_r,$var_g,$var_b);
    $del_max = $var_max - $var_min;

    $l = ($var_max + $var_min) / 2;

    if ($del_max == 0) {
            $h = 0;
            $s = 0;
    }
    else {
            if ($l < 0.5) { $s = $del_max / ($var_max + $var_min); }
            else { $s = $del_max / (2 - $var_max - $var_min); }

            $del_r = ((($var_max - $var_r) / 6) + ($del_max / 2)) / $del_max;
            $del_g = ((($var_max - $var_g) / 6) + ($del_max / 2)) / $del_max;
            $del_b = ((($var_max - $var_b) / 6) + ($del_max / 2)) / $del_max;

            if ($var_r == $var_max) { $h = $del_b - $del_g; }
            elseif ($var_g == $var_max)  { $h = (1 / 3) + $del_r - $del_b; }
            elseif ($var_b == $var_max)  { $h = (2 / 3) + $del_g - $del_r; };
 
            if ($h < 0) { $h += 1; }
            if ($h > 1) { $h -= 1; }
    }
    return array($h,$s,$l);
  }
}


if(!function_exists('hsl2rgb')){ 
  function hsl2rgb($h2,$s2,$l2) {
      // Input is HSL value of complementary colour, held in $h2, $s, $l as fractions of 1
       // Output is RGB in normal 255 255 255 format, held in $r, $g, $b
       // Hue is converted using function hue_2_rgb, shown at the end of this code

        if ($s2 == 0)
        {
                $r = $l2 * 255;
                $g = $l2 * 255;
                $b = $l2 * 255;
        }
        else
        {
                if ($l2 < 0.5)
                {
                        $var_2 = $l2 * (1 + $s2);
                }
                else
                {
                        $var_2 = ($l2 + $s2) - ($s2 * $l2);
                };

                $var_1 = 2 * $l2 - $var_2;
                $r = round(255 * hue_2_rgb($var_1,$var_2,$h2 + (1 / 3)));
                $g = round(255 * hue_2_rgb($var_1,$var_2,$h2));
                $b = round(255 * hue_2_rgb($var_1,$var_2,$h2 - (1 / 3)));
        };
    return array($r,$g,$b);
  }
}



if(!function_exists('hue_2_rgb')){ 
  function hue_2_rgb($v1,$v2,$vh) {
	// Function to convert hue to RGB, called from above
	if ($vh < 0) { $vh += 1; };
	if ($vh > 1) { $vh -= 1; };
	if ((6 * $vh) < 1) { return ($v1 + ($v2 - $v1) * 6 * $vh); };
	if ((2 * $vh) < 1) { return ($v2); };
	if ((3 * $vh) < 2) { return ($v1 + ($v2 - $v1) * ((2 / 3 - $vh) * 6)); };
	return ($v1);
  }
}


			
// =======================================================================================================
// Added mPDF 1.4
// Used for usort in fn _tableWrite
function _cmpdom($a, $b) {
    return ($a["dom"] < $b["dom"]) ? -1 : 1;
}
// =======================================================================================================
if(!function_exists('make_range_string')){ 
   function make_range_string($arr,$sep=',') {		// eg array(31,7,32,34,33,75) => string"7,31-34,75"
	$newarr = array();
	sort($arr);
	$range_start = $arr[0];
	$range_end = 0;
	for ($zi=1;$zi<count($arr);$zi++) {
	  if ($arr[$zi] == ($arr[$zi-1]+1)) {
		$range_end = $arr[$zi];
	  }
	  else {
		if ($range_end) {
			if ($range_end == $range_start+1) { $newarr[] = $range_start . $sep . $arr[$zi-1]; }
			else { $newarr[] = $range_start . "-" . $arr[$zi-1]; }
		}
		else {
			$newarr[] = $arr[$zi-1];
		}
		$range_start = $arr[$zi];
		$range_end = 0;
	  }
	}

	if ($range_end) {
		if ($range_end == $range_start+1) { $newarr[] = $range_start . $sep . $range_end; }
		else { $newarr[] = $range_start . "-" . $range_end; }
	}
	else {
		$newarr[] = $arr[count($arr)-1];
	}
	$string = implode($sep,$newarr);
	return $string;
   }
}

// =======================================================================================================

if(!function_exists('code2utf')){ 
  function code2utf($num,$lo=true){
	//Returns the utf string corresponding to the unicode value
	//added notes - http://uk.php.net/utf8_encode
	// NB this code initially had 1024 (->2048) and 38000 (-> 65536)
	if ($num<128) {
		if ($lo) return chr($num);
		else return '&#'.$num.';';	// i.e. no change
	}
	if ($num<2048) return chr(($num>>6)+192).chr(($num&63)+128);
	if ($num<65536) return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
	// mPDF 3.0
	if ($num<2097152) return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128) .chr(($num&63)+128);
	return '?';
  }
}

if(!function_exists('codeHex2utf')){ 
  function codeHex2utf($hex,$lo=true){
	$num = hexdec($hex);
	if (($num<128) && !$lo) return '&#x'.$hex.';';	// i.e. no change
	return code2utf($num,$lo);
  }
}

if(!function_exists('strcode2utf')){ 
  function strcode2utf($str,$lo=true) {
	//converts all the &#nnn; and &#xhhh; in a string to Unicode
	if ($lo) { $lo = 1; } else { $lo = 0; }
	$str = preg_replace('/\&\#([0-9]+)\;/me', "code2utf('\\1',{$lo})",$str);
	$str = preg_replace('/\&\#x([0-9a-fA-F]+)\;/me', "codeHex2utf('\\1',{$lo})",$str);
	return $str;
  }
}

if(!function_exists('mb_rtrim')){ 
 function mb_rtrim($str, $enc = 'utf-8'){
	if ($str == ' ' || $str == "\n" || $str == "\t") { return ''; }
	$end = mb_strlen($str,$enc);
	for($i=$end;$i>0;$i--) {
		$last = mb_substr($str,$i-1,1,$enc);
		if (($last != ' ') && ($last != "\n") && ($last != "\r") && ($last != "\t")) { return mb_substr($str,0,$i,$enc); }
	}
	return $str;
 } 
}

if(!function_exists('mb_strrev')){ 
 function mb_strrev($str, $enc = 'utf-8'){
	$ch = array();
	for($i=0;$i<mb_strlen($str,$enc);$i++) {
		$ch[] = mb_substr($str,$i,1,$enc);
	}
	$revch = array_reverse($ch);
	return implode('',$revch);
 } 
}
// =======================================================================================================
// mPDF 2.4 For PHP4
if(!function_exists('str_ireplace')) {
  function str_ireplace($search,$replace,$subject) {
	$search = preg_quote($search, "/");
	return preg_replace("/".$search."/i", $replace, $subject); 
  }
}
// =======================================================================================================

// Callback function from function printcolumnbuffer in mpdf
function columnAdjustAdd($type,$k,$xadj,$yadj,$a,$b,$c=0,$d=0,$e=0,$f=0) {
   if ($type == 'Td') { 	// xpos,ypos
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	return 'BT '.sprintf('%.3f %.3f',$a,$b).' Td'; 
   }
   else if ($type == 're') { 	// xpos,ypos,width,height
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	return sprintf('%.3f %.3f %.3f %.3f',$a,$b,$c,$d).' re'; 
   }
   else if ($type == 'l') { 	// xpos,ypos,x2pos,y2pos
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	return sprintf('%.3f %.3f l',$a,$b); 
   }
   else if ($type == 'img') { 	// width,height,xpos,ypos
	$c += ($xadj * $k);
	$d -= ($yadj * $k);
	return sprintf('q %.3f 0 0 %.3f %.3f %.3f',$a,$b,$c,$d).' cm /I'; 
   }
   else if ($type == 'draw') { 	// xpos,ypos
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	return sprintf('%.3f %.3f m',$a,$b); 
   }
   else if ($type == 'bezier') { 	// xpos,ypos,x2pos,y2pos,x3pos,y3pos
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	$c += ($xadj * $k);
	$d -= ($yadj * $k);
	$e += ($xadj * $k);
	$f -= ($yadj * $k);
	return sprintf('%.3f %.3f %.3f %.3f %.3f %.3f',$a,$b,$c,$d,$e,$f).' c'; 
   }
}

// Added mPDF 1.1
// Callback function from function printdivbuffer in mpdf - keeping block together on one page
function blockAdjust($type,$k,$xadj,$yadj,$a,$b,$c=0,$d=0,$e=0,$f=0) {
   if ($type == 'Td') { 	// xpos,ypos
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	return 'BT '.sprintf('%.3f %.3f',$a,$b).' Td'; 
   }
   else if ($type == 're') { 	// xpos,ypos,width,height
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	return sprintf('%.3f %.3f %.3f %.3f',$a,$b,$c,$d).' re'; 
   }
   else if ($type == 'l') { 	// xpos,ypos,x2pos,y2pos
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	return sprintf('%.3f %.3f l',$a,$b); 
   }
   else if ($type == 'img') { 	// width,height,xpos,ypos
	$c += ($xadj * $k);
	$d -= ($yadj * $k);
	return sprintf('q %.3f 0 0 %.3f %.3f %.3f',$a,$b,$c,$d).' cm /I'; 
   }
   else if ($type == 'draw') { 	// xpos,ypos
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	return sprintf('%.3f %.3f m',$a,$b); 
   }
   else if ($type == 'bezier') { 	// xpos,ypos,x2pos,y2pos,x3pos,y3pos
	$a += ($xadj * $k);
	$b -= ($yadj * $k);
	$c += ($xadj * $k);
	$d -= ($yadj * $k);
	$e += ($xadj * $k);
	$f -= ($yadj * $k);
	return sprintf('%.3f %.3f %.3f %.3f %.3f %.3f',$a,$b,$c,$d,$e,$f).' c'; 
   }
}



// Callback function from function printcolumnbuffer in mpdf
function columnAdjustRatio($type,$k,$ratio,$yadj,$a,$b,$c=0,$d=0,$e=0,$f=0) {
   if ($type == 'Td') { 	// xpos,ypos
	$b -= ($yadj * $k);
	return 'BT '.sprintf('%.3f %.3f',$a,$b).' Td'; 
   }
   else if ($type == 're') { 	// xpos,ypos,width,height
	$b -= ($yadj * $k);
	$d *= ($ratio);
	return sprintf('%.3f %.3f %.3f %.3f',$a,$b,$c,$d).' re'; 
   }
   else if ($type == 'l') { 	// xpos,ypos,x2pos,y2pos
	$b -= ($yadj * $k);
	return sprintf('%.3f %.3f l',$a,$b); 
   }
   else if ($type == 'img') { 	// width,height,xpos,ypos
	$d -= ($yadj * $k);
	return sprintf('q %.3f 0 0 %.3f %.3f %.3f',$a,$b,$c,$d).' cm /I'; 
   }
   else if ($type == 'draw') { 	// xpos,ypos
	$b -= ($yadj * $k);
	return sprintf('%.3f %.3f',$a,$b).' m'; 
   }
   else if ($type == 'bezier') { 	// xpos,ypos,x2pos,y2pos,x3pos,y3pos
	$b -= ($yadj * $k);
	$d -= ($yadj * $k);
	$f -= ($yadj * $k);
	return sprintf('%.3f %.3f %.3f %.3f %.3f %.3f',$a,$b,$c,$d,$e,$f).' c'; 
   }
}



function ConvertColor($color="#000000"){
//returns an associative array (keys: R,G,B) from html code (e.g. #3FE5AA)
  //All color names array
  // mPDF 3.0 Added CSS 3 Colors based on X11 or SVG 1.0 (midnightblue corrected)
  static $common_colors = array('antiquewhite'=>'#FAEBD7','aqua'=>'#00FFFF','aquamarine'=>'#7FFFD4','beige'=>'#F5F5DC','black'=>'#000000','blue'=>'#0000FF','brown'=>'#A52A2A','cadetblue'=>'#5F9EA0','chocolate'=>'#D2691E','cornflowerblue'=>'#6495ED','crimson'=>'#DC143C','darkblue'=>'#00008B','darkgoldenrod'=>'#B8860B','darkgreen'=>'#006400','darkmagenta'=>'#8B008B','darkorange'=>'#FF8C00','darkred'=>'#8B0000','darkseagreen'=>'#8FBC8F','darkslategray'=>'#2F4F4F','darkviolet'=>'#9400D3','deepskyblue'=>'#00BFFF','dodgerblue'=>'#1E90FF','firebrick'=>'#B22222','forestgreen'=>'#228B22','fuchsia'=>'#FF00FF','gainsboro'=>'#DCDCDC','gold'=>'#FFD700','gray'=>'#808080','green'=>'#008000','greenyellow'=>'#ADFF2F','hotpink'=>'#FF69B4','indigo'=>'#4B0082','khaki'=>'#F0E68C','lavenderblush'=>'#FFF0F5','lemonchiffon'=>'#FFFACD','lightcoral'=>'#F08080','lightgoldenrodyellow'=>'#FAFAD2','lightgreen'=>'#90EE90','lightsalmon'=>'#FFA07A','lightskyblue'=>'#87CEFA','lightslategray'=>'#778899','lightyellow'=>'#FFFFE0','lime'=>'#00FF00','limegreen'=>'#32CD32','magenta'=>'#FF00FF','maroon'=>'#800000','mediumaquamarine'=>'#66CDAA','mediumorchid'=>'#BA55D3','mediumseagreen'=>'#3CB371','mediumspringgreen'=>'#00FA9A','mediumvioletred'=>'#C71585','midnightblue'=>'#191970','mintcream'=>'#F5FFFA','moccasin'=>'#FFE4B5','navy'=>'#000080','olive'=>'#808000','orange'=>'#FFA500','orchid'=>'#DA70D6','palegreen'=>'#98FB98','palevioletred'=>'#D87093','peachpuff'=>'#FFDAB9','pink'=>'#FFC0CB','powderblue'=>'#B0E0E6','purple'=>'#800080','red'=>'#FF0000','royalblue'=>'#4169E1','salmon'=>'#FA8072','seagreen'=>'#2E8B57','sienna'=>'#A0522D','silver'=>'#C0C0C0','skyblue'=>'#87CEEB','slategray'=>'#708090','springgreen'=>'#00FF7F','steelblue'=>'#236B8E','tan'=>'#D2B48C','teal'=>'#008080','thistle'=>'#D8BFD8','turquoise'=>'#40E0D0','violetred'=>'#D02090','white'=>'#FFFFFF','yellow'=>'#FFFF00', 
'aliceblue'=>'#f0f8ff', 'azure'=>'#f0ffff', 'bisque'=>'#ffe4c4', 'blanchedalmond'=>'#ffebcd', 'blueviolet'=>'#8a2be2', 'burlywood'=>'#deb887', 'chartreuse'=>'#7fff00', 'coral'=>'#ff7f50', 'cornsilk'=>'#fff8dc', 'cyan'=>'#00ffff', 'darkcyan'=>'#008b8b', 'darkgray'=>'#a9a9a9', 'darkgrey'=>'#a9a9a9', 'darkkhaki'=>'#bdb76b', 'darkolivegreen'=>'#556b2f', 'darkorchid'=>'#9932cc', 'darksalmon'=>'#e9967a', 'darkslateblue'=>'#483d8b', 'darkslategrey'=>'#2f4f4f', 'darkturquoise'=>'#00ced1', 'deeppink'=>'#ff1493', 'dimgray'=>'#696969', 'dimgrey'=>'#696969', 'floralwhite'=>'#fffaf0', 'ghostwhite'=>'#f8f8ff', 'goldenrod'=>'#daa520', 'grey'=>'#808080', 'honeydew'=>'#f0fff0', 'indianred'=>'#cd5c5c', 'ivory'=>'#fffff0', 'lavender'=>'#e6e6fa', 'lawngreen'=>'#7cfc00', 'lightblue'=>'#add8e6', 'lightcyan'=>'#e0ffff', 'lightgray'=>'#d3d3d3', 'lightgrey'=>'#d3d3d3', 'lightpink'=>'#ffb6c1', 'lightseagreen'=>'#20b2aa', 'lightslategrey'=>'#778899', 'lightsteelblue'=>'#b0c4de', 'linen'=>'#faf0e6', 'mediumblue'=>'#0000cd', 'mediumpurple'=>'#9370db', 'mediumslateblue'=>'#7b68ee', 'mediumturquoise'=>'#48d1cc', 'mistyrose'=>'#ffe4e1', 'navajowhite'=>'#ffdead', 'oldlace'=>'#fdf5e6', 'olivedrab'=>'#6b8e23', 'orangered'=>'#ff4500', 'palegoldenrod'=>'#eee8aa', 'paleturquoise'=>'#afeeee', 'papayawhip'=>'#ffefd5', 'peru'=>'#cd853f', 'plum'=>'#dda0dd', 'rosybrown'=>'#bc8f8f', 'saddlebrown'=>'#8b4513', 'sandybrown'=>'#f4a460', 'seashell'=>'#fff5ee', 'slateblue'=>'#6a5acd', 'slategrey'=>'#708090', 'snow'=>'#fffafa', 'tomato'=>'#ff6347', 'violet'=>'#ee82ee', 'wheat'=>'#f5deb3', 'whitesmoke'=>'#f5f5f5', 'yellowgreen'=>'#9acd32');
  //http://www.w3schools.com/css/css_colornames.asp
  if (strtoupper($color)=='TRANSPARENT') { return false; }
  if (strtoupper($color)=='INHERIT') { return false; }

  // mPDF 1.4
  // if ( ($color{0} != '#') and ( stristr($color,'rgb') === false ) and ( stristr($color,'cmyk') === false ) ) $color = $common_colors[strtolower($color)];
  // mPDF 3.0 
  if (isset($common_colors[strtolower($color)])) $color = $common_colors[strtolower($color)];

  if ($color{0} == '#') //case of #nnnnnn or #nnn
  {
  	$cor = strtoupper($color);
	// mPDF 1.4
	$cor = preg_replace('/\s+.*/','',$cor);	// in case of Background: #CCC url() x-repeat etc.
  	if (strlen($cor) == 4) // Turn #RGB into #RRGGBB
  	{
	 	  $cor = "#" . $cor{1} . $cor{1} . $cor{2} . $cor{2} . $cor{3} . $cor{3};
	  }  
	  $R = substr($cor, 1, 2);
	  $vermelho = hexdec($R);
	  $V = substr($cor, 3, 2);
	  $verde = hexdec($V);
	  $B = substr($cor, 5, 2);
	  $azul = hexdec($B);
	  $color = array();
	  $color['R']=$vermelho;
	  $color['G']=$verde;
	  $color['B']=$azul;
  }
  else if (stristr($color,'cmyk(')) {	//case of CMYK(c,m,y,k)
	$color = str_replace("cmyk(",'',$color); //remove ´rgb(´
	$color = str_replace("CMYK(",'',$color); //remove ´RGB(´ -- PHP < 5 does not have str_ireplace
	$color = str_replace(")",'',$color); //remove ´)´
	$cores = explode(",", $color);
	$color = array();
	$color['R']=$cores[0];
	$color['G']=$cores[1];
	$color['B']=$cores[2];
	$color['K']=$cores[3];
  }
  else if (stristr($color,'rgb(')) //case of RGB(r,g,b)
  {
	$color = str_replace("rgb(",'',$color); //remove ´rgb(´
	$color = str_replace("RGB(",'',$color); //remove ´RGB(´ -- PHP < 5 does not have str_ireplace
	$color = str_replace(")",'',$color); //remove ´)´
	$cores = explode(",", $color);
	$color = array();
	$color['R']=$cores[0];
	$color['G']=$cores[1];
	$color['B']=$cores[2];
  }
  else { return false; }
  // mPDF 1.4
  // if (empty($color)) return array('R'=>255,'G'=>255,'B'=>255);
  if (empty($color)) return false;
  else return $color; // array['R']['G']['B']
}

function ConvertSize($size=5,$maxsize=0,$fontsize=false,$usefontsize=true){
// mPDF 1.4 - usefontsize - setfalse for e.g. margins - will ignore fontsize for % values
// Depends of maxsize value to make % work properly. Usually maxsize == pagewidth
// For text $maxsize = Fontsize
// Setting e.g. margin % will use maxsize (pagewidth) and em will use fontsize
  //Identify size (remember: we are using 'mm' units here)
  if ( strtolower($size) == 'thin' ) $size = 1*0.2645; //1 pixel width for table borders
  elseif ( strtolower($size) == 'medium' ) $size = 3*0.2645; //3 pixel width for table borders
  elseif ( strtolower($size) == 'thick' ) $size = 5*0.2645; //5 pixel width for table borders
  elseif ( stristr($size,'px') ) $size *= 0.2645; //pixels
  elseif ( stristr($size,'cm') ) $size *= 10; //centimeters
  elseif ( stristr($size,'mm') ) $size += 0; //millimeters
  elseif ( stristr($size,'in') ) $size *= 25.4; //inches 
  elseif ( stristr($size,'pc') ) $size *= 38.1/9; //PostScript picas 
  elseif ( stristr($size,'pt') ) $size *= 25.4/72; //72dpi
  elseif ( stristr($size,'em') ) {
  	$size += 0; //make "0.83em" become simply "0.83" 
	if ($fontsize) { $size *= $fontsize; }
	else { $size *= $maxsize; }
  }
  elseif ( stristr($size,'%') ) {
  	$size += 0; //make "90%" become simply "90" 
	if ($fontsize && $usefontsize) { $size *= $fontsize/100; }
	else { $size *= $maxsize/100; }
  }
  // mPDF 2.3
  elseif (strtoupper($size) == 'XX-SMALL') {
	if ($fontsize) { $size *= $fontsize*0.7; }
	else { $size *= $maxsize*0.7; }
  }
  elseif (strtoupper($size) == 'X-SMALL') {
	if ($fontsize) { $size *= $fontsize*0.77; }
	else { $size *= $maxsize*0.77; }
  }
  elseif (strtoupper($size) == 'SMALL') {
	if ($fontsize) { $size *= $fontsize*0.86; }
	else { $size *= $maxsize*0.86; }
  }
  elseif (strtoupper($size) == 'MEDIUM') {
	if ($fontsize) { $size *= $fontsize; }
	else { $size *= $maxsize; }
  }
  elseif (strtoupper($size) == 'LARGE') {
	if ($fontsize) { $size *= $fontsize*1.2; }
	else { $size *= $maxsize*1.2; }
  }
  elseif (strtoupper($size) == 'X-LARGE') {
	if ($fontsize) { $size *= $fontsize*1.5; }
	else { $size *= $maxsize*1.5; }
  }
  elseif (strtoupper($size) == 'XX-LARGE') {
	if ($fontsize) { $size *= $fontsize*2; }
	else { $size *= $maxsize*2; }
  }
  else $size *= 0.2645; //nothing == px
  
  return $size;
}

function value_entity_decode($html)
{
//replace each value entity by its respective char
  preg_match_all('|&#(.*?);|',$html,$temparray);
  foreach($temparray[1] as $val) $html = str_replace("&#".$val.";",chr($val),$html);
  return $html;
}

function lesser_entity_decode($html)
{
  //supports the most used entity codes
/////////////////////////////////////////////
//changed to only do ascii safe characters
/////////////////////////////////////////////
 	$html = str_replace("&nbsp;"," ",$html);
 	$html = str_replace("&lt;","<",$html);
 	$html = str_replace("&gt;",">",$html);

 	$html = str_replace("&apos;","'",$html);
 	$html = str_replace("&quot;",'"',$html);
 	$html = str_replace("&amp;","&",$html);
  return $html;
}


// mPDF 2.4
function PreparePreText($text,$ff='//FF//') {
	$text = str_ireplace('<pre',"<||@mpdf@||pre",$text);
	$text = str_ireplace('</pre',"<||@mpdf@||/pre",$text);
	if ($ff) { $text = str_replace($ff,'</pre><formfeed /><pre>',$text); }
	return ('<pre>'.$text.'</pre>');
}



// mPDF 2.3
function AdjustHTML($html,$directionality='ltr',$usepre=true, $tabSpaces=8) {
	//Try to make the html text more manageable (turning it into XHTML)
	// mPDF 2.3 Annotations
	preg_match_all("/(<annotation.*?>)/si", $html, $m);
	if (count($m[1])) { 
		for($i=0;$i<count($m[1]);$i++) {
			$sub = preg_replace("/\n/si", "\xbb\xa4\xac", $m[1][$i]);
			$html = preg_replace('/'.preg_quote($m[1][$i], '/').'/si', $sub, $html); 
		}
	}

	//Remove javascript code from HTML (should not appear in the PDF file)
	$html = preg_replace('/<script.*?<\/script>/is','',$html); // mPDF 3.0 changed from ereg_

	// mPDF 2.2
	//Remove special comments
	$html = preg_replace('/<!--mpdf/i','',$html); // mPDF 3.0 changed from ereg_
	$html = preg_replace('/mpdf-->/i','',$html); // mPDF 3.0 changed from ereg_

	//Remove comments from HTML (should not appear in the PDF file)
	$html = preg_replace('/<!--.*?-->/s','',$html); // mPDF 3.0 changed from ereg_

	$html = preg_replace('/\f/','',$html); //replace formfeed by nothing // mPDF 3.0 changed from ereg_
	$html = preg_replace('/\r/','',$html); //replace carriage return by nothing // mPDF 3.0 changed from ereg_

	// Well formed XHTML end tags
	$html = preg_replace('/<(br|hr)\/>/i',"<\\1 />",$html);	

	// Get rid of empty <thead></thead>
	$html = preg_replace('/<thead>\s*<\/thead>/i','',$html); // mPDF 3.0 changed from ereg_
	$html = preg_replace('/<tfoot>\s*<\/tfoot>/i','',$html); // mPDF 3.2
	$html = preg_replace('/<table[^>]*>\s*<\/table>/i','',$html); // mPDF 3.2

	// mPDF 1.4 Remove spaces at end of table cells
	$html = preg_replace("/[ ]+<\/t(d|h)/",'</t\\1',$html);

	// Transposes Table Cells When RTL direction
	if ($directionality == 'rtl') { 
		preg_match_all('/<table(.*?)>(.*?)<\/table>/is',$html,$matches);
		for($i=0;$i<count($matches[0]);$i++) {
		  $pre = '<table' . $matches[1][$i] . '>';
		  $post = '</table>';
		  // mPDF 3.2 Don't change if nested tables
		  if (!preg_match('/<table/is',$matches[2][$i]) && !preg_match('/<\/table/is',$matches[2][$i]) ) {
		    $table = $matches[0][$i];
		    if (preg_match('/(<thead[^>]*>)/is',$table,$m)) { $thead = $m[0]; } else { $thead = ''; }
		    preg_match_all('/<tr(.*?)>(.*?)<\/tr>/is',$table,$tmatches);
		    $newrows = array();
		    for($j=0;$j<count($tmatches[0]);$j++) {
			$rpre = '<tr' . $tmatches[1][$j] . '>';
			$rpost = '</tr>';
			$row = $tmatches[0][$j];
			preg_match_all('/<t[hd].*?>.*?<\/t[hd]>/is',$row,$rmatches);
			$cells = array();
			for($k=0;$k<count($rmatches[0]);$k++) { $cells[] = $rmatches[0][$k]; }
			$cells = array_reverse($cells);
			if (($thead) && ($j == 0)) {	// First row
				$newrows[] = $thead . $rpre . implode('',$cells) . $rpost . '</thead><tbody>';
			}
			else if (($thead) && ($j == (count($tmatches[0]) - 1))) {	// last row adds </tbody>
				$newrows[] = $rpre . implode('',$cells) . $rpost . '</tbody>';
			}
			else {
				$newrows[] = $rpre . implode('',$cells) . $rpost;
			}
		    }
		    $newtable = $pre . implode('',$newrows) . $post;
		    $html = str_replace($table,$newtable,$html);
		  }
		}
	}

	// Concatenates any Substitute characters from symbols/dingbats
	$html = str_replace('</tts><tts>','|',$html);
	$html = str_replace('</ttz><ttz>','|',$html);
	$html = str_replace('</tta><tta>','|',$html);

	$html = mb_eregi_replace('/<br \/>\s*/is',"<br />",$html); // mPDF 3.0 changed from ereg_

	//=================================================================================
	// remove redundant <br>'s before </div>
	// mPDF 3.0
//	$html = preg_replace('/(<br[ \/]?[\/]?			>)+?<\/div>/si','</div>',$html);

	//=================================================================================
	if ($usepre) //used to keep \n on content inside <pre> and inside <textarea>
 	{
		// Preserve '\n's in content between the tags <pre> and </pre>
		$thereispre = preg_match_all('#<pre(.*?)>(.*?)</pre>#si',$html,$temp);
		// Preserve '\n's in content between the tags <textarea> and </textarea>
		$thereistextarea = preg_match_all('#<textarea(.*?)>(.*?)</textarea>#si',$html,$temp2);
		$html = preg_replace('/[\n]/',' ',$html); //replace linefeed by spaces // mPDF 3.0 changed from ereg_
		$html = preg_replace('/[\t]/',' ',$html); //replace tabs by spaces // mPDF 3.0 changed from ereg_

		// mPDF 2.3 - moved
		// Converts < to &lt; when not a tag
		$html = preg_replace('/<([^!\/a-zA-Z])/i','&lt;\\1',$html);	

		// mPDF changed to prevent &nbsp; chars replaced
		$html = preg_replace("/[ ]+/",' ',$html);

		$html = preg_replace('/\/li>\s+<\/(u|o)l/i','/li></\\1l',$html); // mPDF 3.0 changed from ereg_
		$html = preg_replace('/\/(u|o)l>\s+<\/li/i','/\\1l></li',$html); // mPDF 3.0 changed from ereg_
		$html = preg_replace('/\/li>\s+<\/(u|o)l/i','/li></\\1l',$html); // mPDF 3.0 changed from ereg_
		$html = preg_replace('/\/li>\s+<li/i','/li><li',$html); // mPDF 3.0 changed from ereg_
		$html = preg_replace('/<(u|o)l([^>]*)>[ ]+/i','<\\1l\\2>',$html);
		$html = preg_replace('/[ ]+<(u|o)l/i','<\\1l',$html); // mPDF 3.0 changed from ereg_

		$iterator = 0;
		while($thereispre) //Recover <pre attributes>content</pre>
		{
			// mPDF 2.4 
			$temp[2][$iterator] = str_replace("<||@mpdf@||pre", "<pre", $temp[2][$iterator] );
			$temp[2][$iterator] = str_replace("<||@mpdf@||/pre", "</pre", $temp[2][$iterator] );
			// mPDF 2.4 (moved) / mPDF 2.3
			$temp[2][$iterator] = preg_replace("/^([^\n\t]*?)\t/me", "stripslashes('\\1') . str_repeat(' ',  ( $tabSpaces - (mb_strlen(stripslashes('\\1')) % $tabSpaces))  )",$temp[2][$iterator]);

			$temp[2][$iterator] = preg_replace('/&/',"&amp;",$temp[2][$iterator]); // mPDF 3.0 changed from ereg_
			$temp[2][$iterator] = preg_replace('/</',"&lt;",$temp[2][$iterator]); // mPDF 3.0 changed from ereg_

			$temp[2][$iterator] = preg_replace('/\t/',str_repeat(" ",$tabSpaces),$temp[2][$iterator]); // mPDF 3.0 changed from ereg_

			$temp[2][$iterator] = preg_replace('/\n/',"<br />",$temp[2][$iterator]); // mPDF 3.0 changed from ereg_
			//=================================================================================
			// mPDF 1.3 Edited to fix bug with empty pre
			// mPDF 2.4 Removed /u as not needed to be Unicode and causing bugs with annotations
			$html = preg_replace('#<pre(.*?)>(.*?)</pre>#si','<erp'.$temp[1][$iterator].'>'.$temp[2][$iterator].'</erp>',$html,1);
			$thereispre--;
			$iterator++;
		}
		$iterator = 0;
		while($thereistextarea) //Recover <textarea attributes>content</textarea>
		{
			$temp2[2][$iterator] = preg_replace('/&/',"&amp;",$temp2[2][$iterator]); // mPDF 3.0 changed from ereg_
			$temp2[2][$iterator] = preg_replace('/</',"&lt;",$temp2[2][$iterator]); // mPDF 3.0 changed from ereg_

			// mPDF 3.0 temp2 not temp
			$temp2[2][$iterator] = preg_replace('/\t/',str_repeat(" ",$tabSpaces),$temp2[2][$iterator]); // mPDF 3.0 changed from ereg_
			$temp2[2][$iterator] = preg_replace('/[ ]/',"&nbsp;",$temp2[2][$iterator]); // mPDF 3.0 changed from ereg_

			//=================================================================================
			// mPDF 1.3 Edited to fix bug with empty textareas
			$html = preg_replace('#<textarea(.*?)>(.*?)</textarea>#usi','<aeratxet'.$temp2[1][$iterator].'>'.trim($temp2[2][$iterator]).'</aeratxet>',$html,1);
			$thereistextarea--;
			$iterator++;
		}
		//Restore original tag names
		$html = str_replace("<erp","<pre",$html);
		$html = str_replace("</erp>","</pre>",$html);
		$html = str_replace("<aeratxet","<textarea",$html);
		$html = str_replace("</aeratxet>","</textarea>",$html);
		// (the code above might slowdown overall performance?)
	} //end of if($usepre)
	else
	{
		$html = preg_replace('/\n/',' ',$html); //replace linefeed by spaces // mPDF 3.0 changed from ereg_
		$html = preg_replace('/\t/',' ',$html); //replace tabs by spaces // mPDF 3.0 changed from ereg_

		// mPDF 2.3 - moved
		// Converts < to &lt; when not a tag
		$html = preg_replace('/<([^!\/a-zA-Z])/i','&lt;\\1',$html);	

		// mPDF changed to prevent &nbsp; chars replaced
		$html = preg_replace("/[ ]+/u",' ',$html);

		$html = preg_replace('/\/li>\s+<\/(u|o)l/i','/li></\\1l',$html); // mPDF 3.0 changed from ereg_
		$html = preg_replace('/\/(u|o)l>\s+<\/li/i','/\\1l></li',$html); // mPDF 3.0 changed from ereg_
		$html = preg_replace('/\/li>\s+<\/(u|o)l/i','/li></\\1l',$html); // mPDF 3.0 changed from ereg_
		$html = preg_replace('/\/li>\s+<li/i','/li><li',$html); // mPDF 3.0 changed from ereg_
		$html = preg_replace('/<(u|o)l([^>]*)>[ ]+/i','<\\1l\\2>',$html);
		$html = preg_replace('/[ ]+<(u|o)l/i','<\\1l',$html); // mPDF 3.0 changed from ereg_

	}
	//=================================================================================
	// mPDF 1.3 Added to fix bug with empty textareas
	$html = preg_replace('/<textarea([^>]*)><\/textarea>/si','<textarea\\1> </textarea>',$html);
	//=================================================================================
	//=================================================================================
	// mPDF 3.2 Keep heading together with table - allows <h1 style="..">
	$html = preg_replace('/<(h[1-6])([^>]*)(>(?:(?!h[1-6]).)*<\/\\1>\s*<table)/si','<\\1\\2 keep-with-table="1"\\3',$html);
	//=================================================================================
	// mPDF 2.3 Annotations
	$html = preg_replace("/\xbb\xa4\xac/", "\n", $html);
	return $html;
}

function dec2alpha($valor,$toupper="true"){
// returns a string from A-Z to AA-ZZ to AAA-ZZZ
// OBS: A = 65 ASCII TABLE VALUE
  if (($valor < 1)  || ($valor > 18278)) return "?"; //supports 'only' up to 18278
  $c1 = $c2 = $c3 = '';
  if ($valor > 702) // 3 letters (up to 18278)
    {
      $c1 = 65 + floor(($valor-703)/676);
      $c2 = 65 + floor((($valor-703)%676)/26);
      $c3 = 65 + floor((($valor-703)%676)%26);
    }
  elseif ($valor > 26) // 2 letters (up to 702)
  {
      $c1 = (64 + (int)(($valor-1) / 26));
      $c2 = (64 + (int)($valor % 26));
      if ($c2 == 64) $c2 += 26;
  }
  else // 1 letter (up to 26)
  {
      $c1 = (64 + $valor);
  }
  $alpha = chr($c1);
  if ($c2 != '') $alpha .= chr($c2);
  if ($c3 != '') $alpha .= chr($c3);
  if (!$toupper) $alpha = strtolower($alpha);
  return $alpha;
}


if(!function_exists('dec2roman')){ 
 function dec2roman($valor,$toupper=true){
 //returns a string as a roman numeral
  $r1=$r2=$r3=$r4='';
  if (($valor >= 5000) || ($valor < 1)) return "?"; //supports 'only' up to 4999
  $aux = (int)($valor/1000);
  if ($aux!==0)
  {
    $valor %= 1000;
    while($aux!==0)
    {
    	$r1 .= "M";
    	$aux--;
    }
  }
  $aux = (int)($valor/100);
  if ($aux!==0)
  {
    $valor %= 100;
    switch($aux){
    	case 3: $r2="C";
    	case 2: $r2.="C";
    	case 1: $r2.="C"; break;
  	  case 9: $r2="CM"; break;
  	  case 8: $r2="C";
  	  case 7: $r2.="C";
    	case 6: $r2.="C";
      case 5: $r2="D".$r2; break;
      case 4: $r2="CD"; break;
      default: break;
	  }
  }
  $aux = (int)($valor/10);
  if ($aux!==0)
  {
    $valor %= 10;
    switch($aux){
    	case 3: $r3="X";
    	case 2: $r3.="X";
    	case 1: $r3.="X"; break;
    	case 9: $r3="XC"; break;
    	case 8: $r3="X";
    	case 7: $r3.="X";
  	  case 6: $r3.="X";
      case 5: $r3="L".$r3; break;
      case 4: $r3="XL"; break;
      default: break;
    }
  }
  switch($valor){
  	case 3: $r4="I";
  	case 2: $r4.="I";
  	case 1: $r4.="I"; break;
  	case 9: $r4="IX"; break;
  	case 8: $r4="I";
    case 7: $r4.="I";
    case 6: $r4.="I";
    case 5: $r4="V".$r4; break;
    case 4: $r4="IV"; break;
    default: break;
  }
  $roman = $r1.$r2.$r3.$r4;
  if (!$toupper) $roman = strtolower($roman);
  return $roman;
 }	
}


//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Sets entities => unicode decimal value for all those > 127
// Example to set
//	$mpdf->setHiEntitySubstitutions(GetHiEntitySubstitutions());
function GetHiEntitySubstitutions() {
  return array (
  'nbsp' => '160',
  'iexcl' => '161',
  'cent' => '162',
  'pound' => '163',
  'curren' => '164',
  'yen' => '165',
  'brvbar' => '166',
  'sect' => '167',
  'uml' => '168',
  'copy' => '169',
  'ordf' => '170',
  'laquo' => '171',
  'not' => '172',
  'shy' => '173',
  'reg' => '174',
  'macr' => '175',
  'deg' => '176',
  'plusmn' => '177',
  'sup2' => '178',
  'sup3' => '179',
  'acute' => '180',
  'micro' => '181',
  'para' => '182',
  'middot' => '183',
  'cedil' => '184',
  'sup1' => '185',
  'ordm' => '186',
  'raquo' => '187',
  'frac14' => '188',
  'frac12' => '189',
  'frac34' => '190',
  'iquest' => '191',
  'Agrave' => '192',
  'Aacute' => '193',
  'Acirc' => '194',
  'Atilde' => '195',
  'Auml' => '196',
  'Aring' => '197',
  'AElig' => '198',
  'Ccedil' => '199',
  'Egrave' => '200',
  'Eacute' => '201',
  'Ecirc' => '202',
  'Euml' => '203',
  'Igrave' => '204',
  'Iacute' => '205',
  'Icirc' => '206',
  'Iuml' => '207',
  'ETH' => '208',
  'Ntilde' => '209',
  'Ograve' => '210',
  'Oacute' => '211',
  'Ocirc' => '212',
  'Otilde' => '213',
  'Ouml' => '214',
  'times' => '215',
  'Oslash' => '216',
  'Ugrave' => '217',
  'Uacute' => '218',
  'Ucirc' => '219',
  'Uuml' => '220',
  'Yacute' => '221',
  'THORN' => '222',
  'szlig' => '223',
  'agrave' => '224',
  'aacute' => '225',
  'acirc' => '226',
  'atilde' => '227',
  'auml' => '228',
  'aring' => '229',
  'aelig' => '230',
  'ccedil' => '231',
  'egrave' => '232',
  'eacute' => '233',
  'ecirc' => '234',
  'euml' => '235',
  'igrave' => '236',
  'iacute' => '237',
  'icirc' => '238',
  'iuml' => '239',
  'eth' => '240',
  'ntilde' => '241',
  'ograve' => '242',
  'oacute' => '243',
  'ocirc' => '244',
  'otilde' => '245',
  'ouml' => '246',
  'divide' => '247',
  'oslash' => '248',
  'ugrave' => '249',
  'uacute' => '250',
  'ucirc' => '251',
  'uuml' => '252',
  'yacute' => '253',
  'thorn' => '254',
  'yuml' => '255',
  'OElig' => '338',
  'oelig' => '339',
  'Scaron' => '352',
  'scaron' => '353',
  'Yuml' => '376',
  'fnof' => '402',
  'circ' => '710',
  'tilde' => '732',
  'Alpha' => '913',
  'Beta' => '914',
  'Gamma' => '915',
  'Delta' => '916',
  'Epsilon' => '917',
  'Zeta' => '918',
  'Eta' => '919',
  'Theta' => '920',
  'Iota' => '921',
  'Kappa' => '922',
  'Lambda' => '923',
  'Mu' => '924',
  'Nu' => '925',
  'Xi' => '926',
  'Omicron' => '927',
  'Pi' => '928',
  'Rho' => '929',
  'Sigma' => '931',
  'Tau' => '932',
  'Upsilon' => '933',
  'Phi' => '934',
  'Chi' => '935',
  'Psi' => '936',
  'Omega' => '937',
  'alpha' => '945',
  'beta' => '946',
  'gamma' => '947',
  'delta' => '948',
  'epsilon' => '949',
  'zeta' => '950',
  'eta' => '951',
  'theta' => '952',
  'iota' => '953',
  'kappa' => '954',
  'lambda' => '955',
  'mu' => '956',
  'nu' => '957',
  'xi' => '958',
  'omicron' => '959',
  'pi' => '960',
  'rho' => '961',
  'sigmaf' => '962',
  'sigma' => '963',
  'tau' => '964',
  'upsilon' => '965',
  'phi' => '966',
  'chi' => '967',
  'psi' => '968',
  'omega' => '969',
  'thetasym' => '977',
  'upsih' => '978',
  'piv' => '982',
  'ensp' => '8194',
  'emsp' => '8195',
  'thinsp' => '8201',
  'zwnj' => '8204',
  'zwj' => '8205',
  'lrm' => '8206',
  'rlm' => '8207',
  'ndash' => '8211',
  'mdash' => '8212',
  'lsquo' => '8216',
  'rsquo' => '8217',
  'sbquo' => '8218',
  'ldquo' => '8220',
  'rdquo' => '8221',
  'bdquo' => '8222',
  'dagger' => '8224',
  'Dagger' => '8225',
  'bull' => '8226',
  'hellip' => '8230',
  'permil' => '8240',
  'prime' => '8242',
  'Prime' => '8243',
  'lsaquo' => '8249',
  'rsaquo' => '8250',
  'oline' => '8254',
  'frasl' => '8260',
  'euro' => '8364',
  'image' => '8465',
  'weierp' => '8472',
  'real' => '8476',
  'trade' => '8482',
  'alefsym' => '8501',
  'larr' => '8592',
  'uarr' => '8593',
  'rarr' => '8594',
  'darr' => '8595',
  'harr' => '8596',
  'crarr' => '8629',
  'lArr' => '8656',
  'uArr' => '8657',
  'rArr' => '8658',
  'dArr' => '8659',
  'hArr' => '8660',
  'forall' => '8704',
  'part' => '8706',
  'exist' => '8707',
  'empty' => '8709',
  'nabla' => '8711',
  'isin' => '8712',
  'notin' => '8713',
  'ni' => '8715',
  'prod' => '8719',
  'sum' => '8721',
  'minus' => '8722',
  'lowast' => '8727',
  'radic' => '8730',
  'prop' => '8733',
  'infin' => '8734',
  'ang' => '8736',
  'and' => '8743',
  'or' => '8744',
  'cap' => '8745',
  'cup' => '8746',
  'int' => '8747',
  'there4' => '8756',
  'sim' => '8764',
  'cong' => '8773',
  'asymp' => '8776',
  'ne' => '8800',
  'equiv' => '8801',
  'le' => '8804',
  'ge' => '8805',
  'sub' => '8834',
  'sup' => '8835',
  'nsub' => '8836',
  'sube' => '8838',
  'supe' => '8839',
  'oplus' => '8853',
  'otimes' => '8855',
  'perp' => '8869',
  'sdot' => '8901',
  'lceil' => '8968',
  'rceil' => '8969',
  'lfloor' => '8970',
  'rfloor' => '8971',
  'lang' => '9001',
  'rang' => '9002',
  'loz' => '9674',
  'spades' => '9824',
  'clubs' => '9827',
  'hearts' => '9829',
  'diams' => '9830',
 );
}



//==============================================
// Array of substitutions - all chars in symbols, dingbats or win1252 not included in the specified codepage
// Example:
// $pdf->setSubstitutions(GetSubstitutions($pdf->codepage,$font));

function GetSubstitutions($cp,$ufont='') {	// cp = codepage; ufont - specifiy font if unicode else returns empty array
  $subsarray = array();
  if ((strtolower($cp) == 'utf-8') && ($ufont == '')) { return $subsarray; }
  $mainarray = array (
  160 => 
  array (
	'subs' => '<tta>160</tta>',
	'win-1252' => 'A0',
	'iso-8859-2' => 'A0',
	'iso-8859-4' => 'A0',
	'iso-8859-5' => 'A0',
	'iso-8859-7' => 'A0',
	'iso-8859-9' => 'A0',
  ),
  161 => 
  array (
	'subs' => '<tta>161</tta>',
	'win-1252' => 'A1',
	'iso-8859-9' => 'A1',
	'UHC' => 'A2AE',
  ),
  162 => 
  array (
	'subs' => '<tta>162</tta>',
	'win-1252' => 'A2',
	'iso-8859-9' => 'A2',
	/* 'BIG5' => 'A246',	not in Adobe Fonts CENT	*/
	'SHIFT_JIS' => '8191',
  ),
  163 => 
  array (
	'subs' => '<tta>163</tta>',
	'win-1252' => 'A3',
	'iso-8859-7' => 'A3',
	'iso-8859-9' => 'A3',
	'BIG5' => 'A247',
	'SHIFT_JIS' => '8192',
  ),
  164 => 
  array (
	'subs' => '<tta>164</tta>',
	'win-1252' => 'A4',
	'iso-8859-2' => 'A4',
	'iso-8859-4' => 'A4',
	'iso-8859-9' => 'A4',
	'UHC' => 'A2B4',
	'GBK' => 'A1E8',
  ),
  165 => 
  array (
	'subs' => '<tta>165</tta>',
	'win-1252' => 'A5',
	'iso-8859-9' => 'A5',
	'BIG5' => 'A244',
	'SHIFT_JIS' => '5C',
  ),
  166 => 
  array (
	'subs' => '<tta>166</tta>',
	'win-1252' => 'A6',
	'iso-8859-7' => 'A6',
	'iso-8859-9' => 'A6',
  ),
  167 => 
  array (
	'subs' => '<tta>167</tta>',
	'win-1252' => 'A7',
	'iso-8859-2' => 'A7',
	'iso-8859-4' => 'A7',
	'iso-8859-5' => 'FD',
	'iso-8859-7' => 'A7',
	'iso-8859-9' => 'A7',
	'BIG5' => 'A1B1',
	'UHC' => 'A1D7',
	'GBK' => 'A1EC',
	'SHIFT_JIS' => '8198',
  ),
  168 => 
  array (
	'subs' => '<tta>168</tta>',
	'win-1252' => 'A8',
	'iso-8859-2' => 'A8',
	'iso-8859-4' => 'A8',
	'iso-8859-7' => 'A8',
	'iso-8859-9' => 'A8',
	'UHC' => 'A1A7',
	'GBK' => 'A1A7',
	'SHIFT_JIS' => '814E',
  ),
  169 => 
  array (
	'subs' => '<tts>227</tts>',
	'win-1252' => 'A9',
	'iso-8859-7' => 'A9',
	'iso-8859-9' => 'A9',
  ),
  170 => 
  array (
	'subs' => '<tta>170</tta>',
	'win-1252' => 'AA',
	'iso-8859-9' => 'AA',
	'UHC' => 'A8A3',
  ),
  171 => 
  array (
	'subs' => '<tta>171</tta>',
	'win-1252' => 'AB',
	'iso-8859-7' => 'AB',
	'iso-8859-9' => 'AB',
  ),
  172 => 
  array (
	'subs' => '<tts>216</tts>',
	'win-1252' => 'AC',
	'iso-8859-7' => 'AC',
	'iso-8859-9' => 'AC',
	'SHIFT_JIS' => '81CA',
  ),
  173 => 
  array (
	'subs' => '<tta>173</tta>',
	'win-1252' => 'AD',
	'iso-8859-2' => 'AD',
	'iso-8859-4' => 'AD',
	'iso-8859-5' => 'AD',
	'iso-8859-7' => 'AD',
	'iso-8859-9' => 'AD',
	'UHC' => 'A1A9',
  ),
  174 => 
  array (
	'subs' => '<tts>226</tts>',
	'win-1252' => 'AE',
	'iso-8859-9' => 'AE',
	/* 'UHC' => 'A2E7',	not in Adobe Fonts (R)	*/
  ),
  175 => 
  array (
	'subs' => '<tta>175</tta>',
	'win-1252' => 'AF',
	'iso-8859-4' => 'AF',
	'iso-8859-9' => 'AF',
  ),
  176 => 
  array (
	'subs' => '<tts>176</tts>',
	'win-1252' => 'B0',
	'iso-8859-2' => 'B0',
	'iso-8859-4' => 'B0',
	'iso-8859-7' => 'B0',
	'iso-8859-9' => 'B0',
	'BIG5' => 'A258',
	'UHC' => 'A1C6',
	'GBK' => 'A1E3',
	'SHIFT_JIS' => '818B',
  ),
  177 => 
  array (
	'subs' => '<tts>177</tts>',
	'win-1252' => 'B1',
	'iso-8859-7' => 'B1',
	'iso-8859-9' => 'B1',
	'BIG5' => 'A1D3',
	'UHC' => 'A1BE',
	'GBK' => 'A1C0',
	'SHIFT_JIS' => '817D',
  ),
  178 => 
  array (
	'subs' => '<tta>178</tta>',
	'win-1252' => 'B2',
	'iso-8859-7' => 'B2',
	'iso-8859-9' => 'B2',
	'UHC' => 'A9F7',
  ),
  179 => 
  array (
	'subs' => '<tta>179</tta>',
	'win-1252' => 'B3',
	'iso-8859-7' => 'B3',
	'iso-8859-9' => 'B3',
	'UHC' => 'A9F8',
  ),
  180 => 
  array (
	'subs' => '<tta>180</tta>',
	'win-1252' => 'B4',
	'iso-8859-2' => 'B4',
	'iso-8859-4' => 'B4',
	'iso-8859-9' => 'B4',
	'UHC' => 'A2A5',
	'SHIFT_JIS' => '814C',
  ),
  181 => 
  array (
	'subs' => '<tts>109</tts>',
	'win-1252' => 'B5',
	'iso-8859-9' => 'B5',
  ),
  182 => 
  array (
	'subs' => '<tta>182</tta>',
	'win-1252' => 'B6',
	'iso-8859-9' => 'B6',
	'UHC' => 'A2D2',
	'SHIFT_JIS' => '81F7',
  ),
  183 => 
  array (
	'subs' => '<tta>183</tta>',
	'win-1252' => 'B7',
	'iso-8859-7' => 'B7',
	'iso-8859-9' => 'B7',
	'BIG5' => 'A150',
	'UHC' => 'A1A4',
	'GBK' => 'A1A4',
  ),
  184 => 
  array (
	'subs' => '<tta>184</tta>',
	'win-1252' => 'B8',
	'iso-8859-2' => 'B8',
	'iso-8859-4' => 'B8',
	'iso-8859-9' => 'B8',
	'UHC' => 'A2AC',
  ),
  185 => 
  array (
	'subs' => '<tta>185</tta>',
	'win-1252' => 'B9',
	'iso-8859-9' => 'B9',
	'UHC' => 'A9F6',
  ),
  186 => 
  array (
	'subs' => '<tta>186</tta>',
	'win-1252' => 'BA',
	'iso-8859-9' => 'BA',
	'UHC' => 'A8AC',
  ),
  187 => 
  array (
	'subs' => '<tta>187</tta>',
	'win-1252' => 'BB',
	'iso-8859-7' => 'BB',
	'iso-8859-9' => 'BB',
  ),
  188 => 
  array (
	'subs' => '<tta>188</tta>',
	'win-1252' => 'BC',
	'iso-8859-9' => 'BC',
	'UHC' => 'A8F9',
  ),
  189 => 
  array (
	'subs' => '<tta>189</tta>',
	'win-1252' => 'BD',
	'iso-8859-7' => 'BD',
	'iso-8859-9' => 'BD',
	'UHC' => 'A8F6',
  ),
  190 => 
  array (
	'subs' => '<tta>190</tta>',
	'win-1252' => 'BE',
	'iso-8859-9' => 'BE',
	'UHC' => 'A8FA',
  ),
  191 => 
  array (
	'subs' => '<tta>191</tta>',
	'win-1252' => 'BF',
	'iso-8859-9' => 'BF',
	'UHC' => 'A2AF',
  ),
  192 => 
  array (
	'subs' => '<tta>192</tta>',
	'win-1252' => 'C0',
	'iso-8859-9' => 'C0',
  ),
  193 => 
  array (
	'subs' => '<tta>193</tta>',
	'win-1252' => 'C1',
	'iso-8859-2' => 'C1',
	'iso-8859-4' => 'C1',
	'iso-8859-9' => 'C1',
  ),
  194 => 
  array (
	'subs' => '<tta>194</tta>',
	'win-1252' => 'C2',
	'iso-8859-2' => 'C2',
	'iso-8859-4' => 'C2',
	'iso-8859-9' => 'C2',
  ),
  195 => 
  array (
	'subs' => '<tta>195</tta>',
	'win-1252' => 'C3',
	'iso-8859-4' => 'C3',
	'iso-8859-9' => 'C3',
  ),
  196 => 
  array (
	'subs' => '<tta>196</tta>',
	'win-1252' => 'C4',
	'iso-8859-2' => 'C4',
	'iso-8859-4' => 'C4',
	'iso-8859-9' => 'C4',
  ),
  197 => 
  array (
	'subs' => '<tta>197</tta>',
	'win-1252' => 'C5',
	'iso-8859-4' => 'C5',
	'iso-8859-9' => 'C5',
  ),
  198 => 
  array (
	'subs' => '<tta>198</tta>',
	'win-1252' => 'C6',
	'iso-8859-4' => 'C6',
	'iso-8859-9' => 'C6',
	'UHC' => 'A8A1',
  ),
  199 => 
  array (
	'subs' => '<tta>199</tta>',
	'win-1252' => 'C7',
	'iso-8859-2' => 'C7',
	'iso-8859-9' => 'C7',
  ),
  200 => 
  array (
	'subs' => '<tta>200</tta>',
	'win-1252' => 'C8',
	'iso-8859-9' => 'C8',
  ),
  201 => 
  array (
	'subs' => '<tta>201</tta>',
	'win-1252' => 'C9',
	'iso-8859-2' => 'C9',
	'iso-8859-4' => 'C9',
	'iso-8859-9' => 'C9',
  ),
  202 => 
  array (
	'subs' => '<tta>202</tta>',
	'win-1252' => 'CA',
	'iso-8859-9' => 'CA',
  ),
  203 => 
  array (
	'subs' => '<tta>203</tta>',
	'win-1252' => 'CB',
	'iso-8859-2' => 'CB',
	'iso-8859-4' => 'CB',
	'iso-8859-9' => 'CB',
  ),
  204 => 
  array (
	'subs' => '<tta>204</tta>',
	'win-1252' => 'CC',
	'iso-8859-9' => 'CC',
  ),
  205 => 
  array (
	'subs' => '<tta>205</tta>',
	'win-1252' => 'CD',
	'iso-8859-2' => 'CD',
	'iso-8859-4' => 'CD',
	'iso-8859-9' => 'CD',
  ),
  206 => 
  array (
	'subs' => '<tta>206</tta>',
	'win-1252' => 'CE',
	'iso-8859-2' => 'CE',
	'iso-8859-4' => 'CE',
	'iso-8859-9' => 'CE',
  ),
  207 => 
  array (
	'subs' => '<tta>207</tta>',
	'win-1252' => 'CF',
	'iso-8859-9' => 'CF',
  ),
  208 => 
  array (
	'subs' => '<tta>208</tta>',
	'win-1252' => 'D0',
	'UHC' => 'A8A2',
  ),
  209 => 
  array (
	'subs' => '<tta>209</tta>',
	'win-1252' => 'D1',
	'iso-8859-9' => 'D1',
  ),
  210 => 
  array (
	'subs' => '<tta>210</tta>',
	'win-1252' => 'D2',
	'iso-8859-9' => 'D2',
  ),
  211 => 
  array (
	'subs' => '<tta>211</tta>',
	'win-1252' => 'D3',
	'iso-8859-2' => 'D3',
	'iso-8859-9' => 'D3',
  ),
  212 => 
  array (
	'subs' => '<tta>212</tta>',
	'win-1252' => 'D4',
	'iso-8859-2' => 'D4',
	'iso-8859-4' => 'D4',
	'iso-8859-9' => 'D4',
  ),
  213 => 
  array (
	'subs' => '<tta>213</tta>',
	'win-1252' => 'D5',
	'iso-8859-4' => 'D5',
	'iso-8859-9' => 'D5',
  ),
  214 => 
  array (
	'subs' => '<tta>214</tta>',
	'win-1252' => 'D6',
	'iso-8859-2' => 'D6',
	'iso-8859-4' => 'D6',
	'iso-8859-9' => 'D6',
  ),
  215 => 
  array (
	'subs' => '<tts>180</tts>',
	'win-1252' => 'D7',
	'iso-8859-2' => 'D7',
	'iso-8859-4' => 'D7',
	'iso-8859-9' => 'D7',
	'BIG5' => 'A1D1',
	'UHC' => 'A1BF',
	'GBK' => 'A1C1',
	'SHIFT_JIS' => '817E',
  ),
  216 => 
  array (
	'subs' => '<tta>216</tta>',
	'win-1252' => 'D8',
	'iso-8859-4' => 'D8',
	'iso-8859-9' => 'D8',
	'UHC' => 'A8AA',
  ),
  217 => 
  array (
	'subs' => '<tta>217</tta>',
	'win-1252' => 'D9',
	'iso-8859-9' => 'D9',
  ),
  218 => 
  array (
	'subs' => '<tta>218</tta>',
	'win-1252' => 'DA',
	'iso-8859-2' => 'DA',
	'iso-8859-4' => 'DA',
	'iso-8859-9' => 'DA',
  ),
  219 => 
  array (
	'subs' => '<tta>219</tta>',
	'win-1252' => 'DB',
	'iso-8859-4' => 'DB',
	'iso-8859-9' => 'DB',
  ),
  220 => 
  array (
	'subs' => '<tta>220</tta>',
	'win-1252' => 'DC',
	'iso-8859-2' => 'DC',
	'iso-8859-4' => 'DC',
	'iso-8859-9' => 'DC',
  ),
  221 => 
  array (
	'subs' => '<tta>221</tta>',
	'win-1252' => 'DD',
	'iso-8859-2' => 'DD',
  ),
  222 => 
  array (
	'subs' => '<tta>222</tta>',
	'win-1252' => 'DE',
	'UHC' => 'A8AD',
  ),
  223 => 
  array (
	'subs' => '<tta>223</tta>',
	'win-1252' => 'DF',
	'iso-8859-2' => 'DF',
	'iso-8859-4' => 'DF',
	'iso-8859-9' => 'DF',
	'UHC' => 'A9AC',
  ),
  224 => 
  array (
	'subs' => '<tta>224</tta>',
	'win-1252' => 'E0',
	'iso-8859-9' => 'E0',
	'GBK' => 'A8A4',
  ),
  225 => 
  array (
	'subs' => '<tta>225</tta>',
	'win-1252' => 'E1',
	'iso-8859-2' => 'E1',
	'iso-8859-4' => 'E1',
	'iso-8859-9' => 'E1',
	'GBK' => 'A8A2',
  ),
  226 => 
  array (
	'subs' => '<tta>226</tta>',
	'win-1252' => 'E2',
	'iso-8859-2' => 'E2',
	'iso-8859-4' => 'E2',
	'iso-8859-9' => 'E2',
  ),
  227 => 
  array (
	'subs' => '<tta>227</tta>',
	'win-1252' => 'E3',
	'iso-8859-4' => 'E3',
	'iso-8859-9' => 'E3',
  ),
  228 => 
  array (
	'subs' => '<tta>228</tta>',
	'win-1252' => 'E4',
	'iso-8859-2' => 'E4',
	'iso-8859-4' => 'E4',
	'iso-8859-9' => 'E4',
  ),
  229 => 
  array (
	'subs' => '<tta>229</tta>',
	'win-1252' => 'E5',
	'iso-8859-4' => 'E5',
	'iso-8859-9' => 'E5',
  ),
  230 => 
  array (
	'subs' => '<tta>230</tta>',
	'win-1252' => 'E6',
	'iso-8859-4' => 'E6',
	'iso-8859-9' => 'E6',
	'UHC' => 'A9A1',
  ),
  231 => 
  array (
	'subs' => '<tta>231</tta>',
	'win-1252' => 'E7',
	'iso-8859-2' => 'E7',
	'iso-8859-9' => 'E7',
  ),
  232 => 
  array (
	'subs' => '<tta>232</tta>',
	'win-1252' => 'E8',
	'iso-8859-9' => 'E8',
	'GBK' => 'A8A8',
  ),
  233 => 
  array (
	'subs' => '<tta>233</tta>',
	'win-1252' => 'E9',
	'iso-8859-2' => 'E9',
	'iso-8859-4' => 'E9',
	'iso-8859-9' => 'E9',
	'GBK' => 'A8A6',
  ),
  234 => 
  array (
	'subs' => '<tta>234</tta>',
	'win-1252' => 'EA',
	'iso-8859-9' => 'EA',
	'GBK' => 'A8BA',
  ),
  235 => 
  array (
	'subs' => '<tta>235</tta>',
	'win-1252' => 'EB',
	'iso-8859-2' => 'EB',
	'iso-8859-4' => 'EB',
	'iso-8859-9' => 'EB',
  ),
  236 => 
  array (
	'subs' => '<tta>236</tta>',
	'win-1252' => 'EC',
	'iso-8859-9' => 'EC',
	'GBK' => 'A8AC',
  ),
  237 => 
  array (
	'subs' => '<tta>237</tta>',
	'win-1252' => 'ED',
	'iso-8859-2' => 'ED',
	'iso-8859-4' => 'ED',
	'iso-8859-9' => 'ED',
	'GBK' => 'A8AA',
  ),
  238 => 
  array (
	'subs' => '<tta>238</tta>',
	'win-1252' => 'EE',
	'iso-8859-2' => 'EE',
	'iso-8859-4' => 'EE',
	'iso-8859-9' => 'EE',
  ),
  239 => 
  array (
	'subs' => '<tta>239</tta>',
	'win-1252' => 'EF',
	'iso-8859-9' => 'EF',
  ),
  240 => 
  array (
	'subs' => '<tta>240</tta>',
	'win-1252' => 'F0',
	'UHC' => 'A9A3',
  ),
  241 => 
  array (
	'subs' => '<tta>241</tta>',
	'win-1252' => 'F1',
	'iso-8859-9' => 'F1',
  ),
  242 => 
  array (
	'subs' => '<tta>242</tta>',
	'win-1252' => 'F2',
	'iso-8859-9' => 'F2',
	'GBK' => 'A8B0',
  ),
  243 => 
  array (
	'subs' => '<tta>243</tta>',
	'win-1252' => 'F3',
	'iso-8859-2' => 'F3',
	'iso-8859-9' => 'F3',
	'GBK' => 'A8AE',
  ),
  244 => 
  array (
	'subs' => '<tta>244</tta>',
	'win-1252' => 'F4',
	'iso-8859-2' => 'F4',
	'iso-8859-4' => 'F4',
	'iso-8859-9' => 'F4',
  ),
  245 => 
  array (
	'subs' => '<tta>245</tta>',
	'win-1252' => 'F5',
	'iso-8859-4' => 'F5',
	'iso-8859-9' => 'F5',
  ),
  246 => 
  array (
	'subs' => '<tta>246</tta>',
	'win-1252' => 'F6',
	'iso-8859-2' => 'F6',
	'iso-8859-4' => 'F6',
	'iso-8859-9' => 'F6',
  ),
  247 => 
  array (
	'subs' => '<tts>184</tts>',
	'win-1252' => 'F7',
	'iso-8859-2' => 'F7',
	'iso-8859-4' => 'F7',
	'iso-8859-9' => 'F7',
	'BIG5' => 'A1D2',
	'UHC' => 'A1C0',
	'GBK' => 'A1C2',
	'SHIFT_JIS' => '8180',
  ),
  248 => 
  array (
	'subs' => '<tta>248</tta>',
	'win-1252' => 'F8',
	'iso-8859-4' => 'F8',
	'iso-8859-9' => 'F8',
	'UHC' => 'A9AA',
  ),
  249 => 
  array (
	'subs' => '<tta>249</tta>',
	'win-1252' => 'F9',
	'iso-8859-9' => 'F9',
	'GBK' => 'A8B4',
  ),
  250 => 
  array (
	'subs' => '<tta>250</tta>',
	'win-1252' => 'FA',
	'iso-8859-2' => 'FA',
	'iso-8859-4' => 'FA',
	'iso-8859-9' => 'FA',
	'GBK' => 'A8B2',
  ),
  251 => 
  array (
	'subs' => '<tta>251</tta>',
	'win-1252' => 'FB',
	'iso-8859-4' => 'FB',
	'iso-8859-9' => 'FB',
  ),
  252 => 
  array (
	'subs' => '<tta>252</tta>',
	'win-1252' => 'FC',
	'iso-8859-2' => 'FC',
	'iso-8859-4' => 'FC',
	'iso-8859-9' => 'FC',
	'GBK' => 'A8B9',
  ),
  253 => 
  array (
	'subs' => '<tta>253</tta>',
	'win-1252' => 'FD',
	'iso-8859-2' => 'FD',
  ),
  254 => 
  array (
	'subs' => '<tta>254</tta>',
	'win-1252' => 'FE',
	'UHC' => 'A9AD',
  ),
  255 => 
  array (
	'subs' => '<tta>255</tta>',
	'win-1252' => 'FF',
	'iso-8859-9' => 'FF',
  ),
  338 => 
  array (
	'subs' => '<tta>140</tta>',
	'win-1252' => '8C',
	'UHC' => 'A8AB',
  ),
  339 => 
  array (
	'subs' => '<tta>156</tta>',
	'win-1252' => '9C',
	'UHC' => 'A9AB',
  ),
  352 => 
  array (
	'subs' => '<tta>138</tta>',
	'win-1252' => '8A',
	'iso-8859-2' => 'A9',
	'iso-8859-4' => 'A9',
  ),
  353 => 
  array (
	'subs' => '<tta>154</tta>',
	'win-1252' => '9A',
	'iso-8859-2' => 'B9',
	'iso-8859-4' => 'B9',
  ),
  376 => 
  array (
	'subs' => '<tta>159</tta>',
	'win-1252' => '9F',
  ),
  381 => 
  array (
	'subs' => '<tta>142</tta>',
	'win-1252' => '8E',
	'iso-8859-2' => 'AE',
	'iso-8859-4' => 'AE',
  ),
  382 => 
  array (
	'subs' => '<tta>158</tta>',
	'win-1252' => '9E',
	'iso-8859-2' => 'BE',
	'iso-8859-4' => 'BE',
  ),
  402 => 
  array (
	'subs' => '<tts>166</tts>',
	'win-1252' => '83',
  ),
  710 => 
  array (
	'subs' => '<tta>136</tta>',
	'win-1252' => '88',
  ),
  732 => 
  array (
	'subs' => '<tta>152</tta>',
	'win-1252' => '98',
  ),
  913 => 
  array (
	'subs' => '<tts>65</tts>',
	'iso-8859-7' => 'C1',
	'BIG5' => 'A344',
	'UHC' => 'A5C1',
	'GBK' => 'A6A1',
	'SHIFT_JIS' => '839F',
  ),
  914 => 
  array (
	'subs' => '<tts>66</tts>',
	'iso-8859-7' => 'C2',
	'BIG5' => 'A345',
	'UHC' => 'A5C2',
	'GBK' => 'A6A2',
	/* 'SHIFT_JIS' => '83A0',	not in Adobe Fonts Greek capital B	*/
  ),
  915 => 
  array (
	'subs' => '<tts>71</tts>',
	'iso-8859-7' => 'C3',
	'BIG5' => 'A346',
	'UHC' => 'A5C3',
	'GBK' => 'A6A3',
	'SHIFT_JIS' => '83A1',
  ),
  916 => 
  array (
	'subs' => '<tts>68</tts>',
	'iso-8859-7' => 'C4',
	'BIG5' => 'A347',
	'UHC' => 'A5C4',
	'GBK' => 'A6A4',
	'SHIFT_JIS' => '83A2',
  ),
  917 => 
  array (
	'subs' => '<tts>69</tts>',
	'iso-8859-7' => 'C5',
	'BIG5' => 'A348',
	'UHC' => 'A5C5',
	'GBK' => 'A6A5',
	'SHIFT_JIS' => '83A3',
  ),
  918 => 
  array (
	'subs' => '<tts>90</tts>',
	'iso-8859-7' => 'C6',
	'BIG5' => 'A349',
	'UHC' => 'A5C6',
	'GBK' => 'A6A6',
	'SHIFT_JIS' => '83A4',
  ),
  919 => 
  array (
	'subs' => '<tts>72</tts>',
	'iso-8859-7' => 'C7',
	'BIG5' => 'A34A',
	'UHC' => 'A5C7',
	'GBK' => 'A6A7',
	'SHIFT_JIS' => '83A5',
  ),
  920 => 
  array (
	'subs' => '<tts>81</tts>',
	'iso-8859-7' => 'C8',
	'BIG5' => 'A34B',
	'UHC' => 'A5C8',
	'GBK' => 'A6A8',
	'SHIFT_JIS' => '83A6',
  ),
  921 => 
  array (
	'subs' => '<tts>73</tts>',
	'iso-8859-7' => 'C9',
	'BIG5' => 'A34C',
	'UHC' => 'A5C9',
	'GBK' => 'A6A9',
	'SHIFT_JIS' => '83A7',
  ),
  922 => 
  array (
	'subs' => '<tts>75</tts>',
	'iso-8859-7' => 'CA',
	'BIG5' => 'A34D',
	'UHC' => 'A5CA',
	'GBK' => 'A6AA',
	'SHIFT_JIS' => '83A8',
  ),
  923 => 
  array (
	'subs' => '<tts>76</tts>',
	'iso-8859-7' => 'CB',
	'BIG5' => 'A34E',
	'UHC' => 'A5CB',
	'GBK' => 'A6AB',
	'SHIFT_JIS' => '83A9',
  ),
  924 => 
  array (
	'subs' => '<tts>77</tts>',
	'iso-8859-7' => 'CC',
	'BIG5' => 'A34F',
	'UHC' => 'A5CC',
	'GBK' => 'A6AC',
	'SHIFT_JIS' => '83AA',
  ),
  925 => 
  array (
	'subs' => '<tts>78</tts>',
	'win-1252' => '',
	'iso-8859-7' => 'CD',
	'BIG5' => 'A350',
	'UHC' => 'A5CD',
	'GBK' => 'A6AD',
	'SHIFT_JIS' => '83AB',
  ),
  926 => 
  array (
	'subs' => '<tts>88</tts>',
	'iso-8859-7' => 'CE',
	'BIG5' => 'A351',
	'UHC' => 'A5CE',
	'GBK' => 'A6AE',
	'SHIFT_JIS' => '83AC',
  ),
  927 => 
  array (
	'subs' => '<tts>79</tts>',
	'iso-8859-7' => 'CF',
	'BIG5' => 'A352',
	'UHC' => 'A5CF',
	'GBK' => 'A6AF',
	'SHIFT_JIS' => '83AD',
  ),
  928 => 
  array (
	'subs' => '<tts>80</tts>',
	'iso-8859-7' => 'D0',
	'BIG5' => 'A353',
	'UHC' => 'A5D0',
	'GBK' => 'A6B0',
	'SHIFT_JIS' => '83AE',
  ),
  929 => 
  array (
	'subs' => '<tts>82</tts>',
	'iso-8859-7' => 'D1',
	'BIG5' => 'A354',
	'UHC' => 'A5D1',
	'GBK' => 'A6B1',
	'SHIFT_JIS' => '83AF',
  ),
  931 => 
  array (
	'subs' => '<tts>83</tts>',
	'iso-8859-7' => 'D3',
	'BIG5' => 'A355',
	'UHC' => 'A5D2',
	'GBK' => 'A6B2',
	'SHIFT_JIS' => '83B0',
  ),
  932 => 
  array (
	'subs' => '<tts>84</tts>',
	'iso-8859-7' => 'D4',
	'BIG5' => 'A356',
	'UHC' => 'A5D3',
	'GBK' => 'A6B3',
	'SHIFT_JIS' => '83B1',
  ),
  933 => 
  array (
	'subs' => '<tts>85</tts>',
	'iso-8859-7' => 'D5',
	'BIG5' => 'A357',
	'UHC' => 'A5D4',
	'GBK' => 'A6B4',
	'SHIFT_JIS' => '83B2',
  ),
  934 => 
  array (
	'subs' => '<tts>70</tts>',
	'win-1252' => '',
	'iso-8859-7' => 'D6',
	'BIG5' => 'A358',
	'UHC' => 'A5D5',
	'GBK' => 'A6B5',
	'SHIFT_JIS' => '83B3',
  ),
  935 => 
  array (
	'subs' => '<tts>67</tts>',
	'iso-8859-7' => 'D7',
	'BIG5' => 'A359',
	'UHC' => 'A5D6',
	'GBK' => 'A6B6',
	'SHIFT_JIS' => '83B4',
  ),
  936 => 
  array (
	'subs' => '<tts>89</tts>',
	'iso-8859-7' => 'D8',
	'BIG5' => 'A35A',
	'UHC' => 'A5D7',
	'GBK' => 'A6B7',
	'SHIFT_JIS' => '83B5',
  ),
  937 => 
  array (
	'subs' => '<tts>87</tts>',
	'iso-8859-7' => 'D9',
	'BIG5' => 'A35B',
	'UHC' => 'A5D8',
	'GBK' => 'A6B8',
	'SHIFT_JIS' => '83B6',
  ),
  945 => 
  array (
	'subs' => '<tts>97</tts>',
	'iso-8859-7' => 'E1',
	'BIG5' => 'A35C',
	'UHC' => 'A5E1',
	'GBK' => 'A6C1',
	'SHIFT_JIS' => '83BF',
  ),
  946 => 
  array (
	'subs' => '<tts>98</tts>',
	'iso-8859-7' => 'E2',
	'BIG5' => 'A35D',
	'UHC' => 'A5E2',
	'GBK' => 'A6C2',
	'SHIFT_JIS' => '83C0',
  ),
  947 => 
  array (
	'subs' => '<tts>103</tts>',
	'iso-8859-7' => 'E3',
	'BIG5' => 'A35E',
	'UHC' => 'A5E3',
	'GBK' => 'A6C3',
	'SHIFT_JIS' => '83C1',
  ),
  948 => 
  array (
	'subs' => '<tts>100</tts>',
	'iso-8859-7' => 'E4',
	'BIG5' => 'A35F',
	'UHC' => 'A5E4',
	'GBK' => 'A6C4',
	'SHIFT_JIS' => '83C2',
  ),
  949 => 
  array (
	'subs' => '<tts>101</tts>',
	'iso-8859-7' => 'E5',
	'BIG5' => 'A360',
	'UHC' => 'A5E5',
	'GBK' => 'A6C5',
	'SHIFT_JIS' => '83C3',
  ),
  950 => 
  array (
	'subs' => '<tts>122</tts>',
	'iso-8859-7' => 'E6',
	'BIG5' => 'A361',
	'UHC' => 'A5E6',
	'GBK' => 'A6C6',
	'SHIFT_JIS' => '83C4',
  ),
  951 => 
  array (
	'subs' => '<tts>104</tts>',
	'iso-8859-7' => 'E7',
	'BIG5' => 'A362',
	'UHC' => 'A5E7',
	'GBK' => 'A6C7',
	'SHIFT_JIS' => '83C5',
  ),
  952 => 
  array (
	'subs' => '<tts>113</tts>',
	'iso-8859-7' => 'E8',
	'BIG5' => 'A363',
	'UHC' => 'A5E8',
	'GBK' => 'A6C8',
	'SHIFT_JIS' => '83C6',
  ),
  953 => 
  array (
	'subs' => '<tts>105</tts>',
	'iso-8859-7' => 'E9',
	'BIG5' => 'A364',
	'UHC' => 'A5E9',
	'GBK' => 'A6C9',
	'SHIFT_JIS' => '83C7',
  ),
  954 => 
  array (
	'subs' => '<tts>107</tts>',
	'iso-8859-7' => 'EA',
	'BIG5' => 'A365',
	'UHC' => 'A5EA',
	'GBK' => 'A6CA',
	'SHIFT_JIS' => '83C8',
  ),
  955 => 
  array (
	'subs' => '<tts>108</tts>',
	'iso-8859-7' => 'EB',
	'BIG5' => 'A366',
	'UHC' => 'A5EB',
	'GBK' => 'A6CB',
	'SHIFT_JIS' => '83C9',
  ),
  956 => 
  array (
	'subs' => '<tts>109</tts>',
	'iso-8859-7' => 'EC',
	'BIG5' => 'A367',
	'UHC' => 'A5EC',
	'GBK' => 'A6CC',
	'SHIFT_JIS' => '83CA',
  ),
  957 => 
  array (
	'subs' => '<tts>110</tts>',
	'iso-8859-7' => 'ED',
	'BIG5' => 'A368',
	'UHC' => 'A5ED',
	'GBK' => 'A6CD',
	'SHIFT_JIS' => '83CB',
  ),
  958 => 
  array (
	'subs' => '<tts>120</tts>',
	'iso-8859-7' => 'EE',
	'BIG5' => 'A369',
	'UHC' => 'A5EE',
	'GBK' => 'A6CE',
	'SHIFT_JIS' => '83CC',
  ),
  959 => 
  array (
	'subs' => '<tts>111</tts>',
	'iso-8859-7' => 'EF',
	'BIG5' => 'A36A',
	'UHC' => 'A5EF',
	'GBK' => 'A6CF',
	'SHIFT_JIS' => '83CD',
  ),
  960 => 
  array (
	'subs' => '<tts>112</tts>',
	'iso-8859-7' => 'F0',
	'BIG5' => 'A36B',
	'UHC' => 'A5F0',
	'GBK' => 'A6D0',
	'SHIFT_JIS' => '83CE',
  ),
  961 => 
  array (
	'subs' => '<tts>114</tts>',
	'iso-8859-7' => 'F1',
	'BIG5' => 'A36C',
	'UHC' => 'A5F1',
	'GBK' => 'A6D1',
	'SHIFT_JIS' => '83CF',
  ),
  962 => 
  array (
	'subs' => '<tts>86</tts>',
	'iso-8859-7' => 'F2',
  ),
  963 => 
  array (
	'subs' => '<tts>115</tts>',
	'iso-8859-7' => 'F3',
	'BIG5' => 'A36D',
	'UHC' => 'A5F2',
	'GBK' => 'A6D2',
	'SHIFT_JIS' => '83D0',
  ),
  964 => 
  array (
	'subs' => '<tts>116</tts>',
	'iso-8859-7' => 'F4',
	'BIG5' => 'A36E',
	'UHC' => 'A5F3',
	'GBK' => 'A6D3',
	'SHIFT_JIS' => '83D1',
  ),
  965 => 
  array (
	'subs' => '<tts>117</tts>',
	'iso-8859-7' => 'F5',
	'BIG5' => 'A36F',
	'UHC' => 'A5F4',
	'GBK' => 'A6D4',
	'SHIFT_JIS' => '83D2',
  ),
  966 => 
  array (
	'subs' => '<tts>102</tts>',
	'iso-8859-7' => 'F6',
	'BIG5' => 'A370',
	'UHC' => 'A5F5',
	'GBK' => 'A6D5',
	'SHIFT_JIS' => '83D3',
  ),
  967 => 
  array (
	'subs' => '<tts>99</tts>',
	'iso-8859-7' => 'F7',
	'BIG5' => 'A371',
	'UHC' => 'A5F6',
	'GBK' => 'A6D6',
	'SHIFT_JIS' => '83D4',
  ),
  968 => 
  array (
	'subs' => '<tts>121</tts>',
	'iso-8859-7' => 'F8',
	'BIG5' => 'A372',
	'UHC' => 'A5F7',
	'GBK' => 'A6D7',
	'SHIFT_JIS' => '83D5',
  ),
  969 => 
  array (
	'subs' => '<tts>119</tts>',
	'iso-8859-7' => 'F9',
	'BIG5' => 'A373',
	'UHC' => 'A5F8',
	'GBK' => 'A6D8',
	'SHIFT_JIS' => '83D6',
  ),
  977 => 
  array (
	'subs' => '<tts>74</tts>',
  ),
  978 => 
  array (
	'subs' => '<tts>161</tts>',
  ),
  981 => 
  array (
	'subs' => '<tts>106</tts>',
  ),
  982 => 
  array (
	'subs' => '<tts>118</tts>',
  ),
  8211 => 
  array (
	'subs' => '<tta>150</tta>',
	'win-1252' => '96',
	'BIG5' => 'A156',
	'GBK' => 'A843',
  ),
  8212 => 
  array (
	'subs' => '<tta>151</tta>',
	'win-1252' => '97',
	'BIG5' => 'A158',
	'GBK' => 'A1AA',
  ),
  8216 => 
  array (
	'subs' => '<tta>145</tta>',
	'win-1252' => '91',
	'BIG5' => 'A1A5',
	'UHC' => 'A1AE',
	'GBK' => 'A1AE',
	'SHIFT_JIS' => '8165',
  ),
  8217 => 
  array (
	'subs' => '<tta>146</tta>',
	'win-1252' => '92',
	'BIG5' => 'A1A6',
	'UHC' => 'A1AF',
	'GBK' => 'A1AF',
	'SHIFT_JIS' => '8166',
  ),
  8218 => 
  array (
	'subs' => '<tta>130</tta>',
	'win-1252' => '82',
  ),
  8220 => 
  array (
	'subs' => '<tta>147</tta>',
	'win-1252' => '93',
	'BIG5' => 'A1A7',
	'UHC' => 'A1B0',
	'GBK' => 'A1B0',
	'SHIFT_JIS' => '8167',
  ),
  8221 => 
  array (
	'subs' => '<tta>148</tta>',
	'win-1252' => '94',
	'BIG5' => 'A1A8',
	'UHC' => 'A1B1',
	'GBK' => 'A1B1',
	'SHIFT_JIS' => '8168',
  ),
  8222 => 
  array (
	'subs' => '<tta>132</tta>',
	'win-1252' => '84',
  ),
  8224 => 
  array (
	'subs' => '<tta>134</tta>',
	'win-1252' => '86',
	'UHC' => 'A2D3',
	'SHIFT_JIS' => '81F5',
  ),
  8225 => 
  array (
	'subs' => '<tta>135</tta>',
	'win-1252' => '87',
	'UHC' => 'A2D4',
	'SHIFT_JIS' => '81F6',
  ),
  8226 => 
  array (
	'subs' => '<tts>183</tts>',
	'win-1252' => '95',
	'BIG5' => 'A145',
  ),
  8230 => 
  array (
	'subs' => '<tts>188</tts>',
	'win-1252' => '85',
	'BIG5' => 'A14B',
	'UHC' => 'A1A6',
	'GBK' => 'A1AD',
	'SHIFT_JIS' => '8163',
  ),
  8240 => 
  array (
	'subs' => '<tta>137</tta>',
	'win-1252' => '89',
	'UHC' => 'A2B6',
	'GBK' => 'A1EB',
	'SHIFT_JIS' => '81F1',
  ),
  8242 => 
  array (
	'subs' => '<tts>162</tts>',
	'BIG5' => 'A1AC',
	'UHC' => 'A1C7',
	'GBK' => 'A1E4',
	'SHIFT_JIS' => '818C',
  ),
  8243 => 
  array (
	'subs' => '<tts>178</tts>',
	'UHC' => 'A1C8',
	'GBK' => 'A1E5',
	'SHIFT_JIS' => '818D',
  ),
  8249 => 
  array (
	'subs' => '<tta>139</tta>',
	'win-1252' => '8B',
  ),
  8250 => 
  array (
	'subs' => '<tta>155</tta>',
	'win-1252' => '9B',
  ),
  8260 => 
  array (
	'subs' => '<tts>164</tts>',
  ),
  8364 => 
  array (
	'subs' => '<tta>128</tta>',
	'win-1252' => '80',
	/* 'UHC' => 'A2E6',	not in Adobe Fonts EURO	*/
	/* 'GBK' => '80',		not in Adobe Fonts	*/
  ),
  8465 => 
  array (
	'subs' => '<tts>193</tts>',
  ),
  8472 => 
  array (
	'subs' => '<tts>195</tts>',
  ),
  8476 => 
  array (
	'subs' => '<tts>194</tts>',
  ),
  8482 => 
  array (
	'subs' => '<tts>228</tts>',
	'win-1252' => '99',
	'UHC' => 'A2E2',
  ),
  8486 => 
  array (
	'subs' => '<tts>87</tts>',
	'UHC' => 'A7D9',
  ),
  8501 => 
  array (
	'subs' => '<tts>192</tts>',
  ),
  8592 => 
  array (
	'subs' => '<tts>172</tts>',
	'BIG5' => 'A1F6',
	'UHC' => 'A1E7',
	'GBK' => 'A1FB',
	'SHIFT_JIS' => '81A9',
  ),
  8593 => 
  array (
	'subs' => '<tts>173</tts>',
	'BIG5' => 'A1F4',
	'UHC' => 'A1E8',
	'GBK' => 'A1FC',
	'SHIFT_JIS' => '81AA',
  ),
  8594 => 
  array (
	'subs' => '<tts>174</tts>',
	'BIG5' => 'A1F7',
	'UHC' => 'A1E6',
	'GBK' => 'A1FA',
	'SHIFT_JIS' => '81A8',
  ),
  8595 => 
  array (
	'subs' => '<tts>175</tts>',
	'BIG5' => 'A1F5',
	'UHC' => 'A1E9',
	'GBK' => 'A1FD',
	'SHIFT_JIS' => '81AB',
  ),
  8596 => 
  array (
	'subs' => '<tts>171</tts>',
	'UHC' => 'A1EA',
  ),
  8597 => 
  array (
	'subs' => '<ttz>215</ttz>',
	'UHC' => 'A2D5',
  ),
  8629 => 
  array (
	'subs' => '<tts>191</tts>',
  ),
  8656 => 
  array (
	'subs' => '<tts>220</tts>',
  ),
  8657 => 
  array (
	'subs' => '<tts>221</tts>',
  ),
  8658 => 
  array (
	'subs' => '<tts>222</tts>',
	'UHC' => 'A2A1',
	'SHIFT_JIS' => '81CB',
  ),
  8659 => 
  array (
	'subs' => '<tts>223</tts>',
  ),
  8660 => 
  array (
	'subs' => '<tts>219</tts>',
	'UHC' => 'A2A2',
	'SHIFT_JIS' => '81CC',
  ),
  8704 => 
  array (
	'subs' => '<tts>34</tts>',
	'UHC' => 'A2A3',
	'SHIFT_JIS' => '81CD',
  ),
  8706 => 
  array (
	'subs' => '<tts>182</tts>',
	'UHC' => 'A1D3',
	'SHIFT_JIS' => '81DD',
  ),
  8707 => 
  array (
	'subs' => '<tts>36</tts>',
	'UHC' => 'A2A4',
	'SHIFT_JIS' => '81CE',
  ),
  8709 => 
  array (
	'subs' => '<tts>198</tts>',
  ),
  8710 => 
  array (
	'subs' => '<tts>68</tts>',
  ),
  8711 => 
  array (
	'subs' => '<tts>209</tts>',
	'UHC' => 'A1D4',
	'SHIFT_JIS' => '81DE',
  ),
  8712 => 
  array (
	'subs' => '<tts>206</tts>',
	'UHC' => 'A1F4',
	'GBK' => 'A1CA',
	'SHIFT_JIS' => '81B8',
  ),
  8713 => 
  array (
	'subs' => '<tts>207</tts>',
  ),
  8715 => 
  array (
	'subs' => '<tts>39</tts>',
	'UHC' => 'A1F5',
	'SHIFT_JIS' => '81B9',
  ),
  8719 => 
  array (
	'subs' => '<tts>213</tts>',
	'UHC' => 'A2B3',
	'GBK' => 'A1C7',
  ),
  8721 => 
  array (
	'subs' => '<tts>229</tts>',
	'UHC' => 'A2B2',
	'GBK' => 'A1C6',
  ),
  8722 => 
  array (
	'subs' => '<tts>45</tts>',
	'SHIFT_JIS' => '817C',
  ),
  8725 => 
  array (
	'subs' => '<tts>164</tts>',
	'GBK' => 'A84D',
  ),
  8727 => 
  array (
	'subs' => '<tts>42</tts>',
  ),
  8730 => 
  array (
	'subs' => '<tts>214</tts>',
	'BIG5' => 'A1D4',
	'UHC' => 'A1EE',
	'GBK' => 'A1CC',
	'SHIFT_JIS' => '81E3',
  ),
  8733 => 
  array (
	'subs' => '<tts>181</tts>',
	'UHC' => 'A1F0',
	'GBK' => 'A1D8',
	'SHIFT_JIS' => '81E5',
  ),
  8734 => 
  array (
	'subs' => '<tts>165</tts>',
	'BIG5' => 'A1DB',
	'UHC' => 'A1C4',
	'GBK' => 'A1DE',
	'SHIFT_JIS' => '8187',
  ),
  8736 => 
  array (
	'subs' => '<tts>208</tts>',
	'BIG5' => 'A1E7',
	'UHC' => 'A1D0',
	'GBK' => 'A1CF',
	'SHIFT_JIS' => '81DA',
  ),
  8743 => 
  array (
	'subs' => '<tts>217</tts>',
	'UHC' => 'A1FC',
	'GBK' => 'A1C4',
	'SHIFT_JIS' => '81C8',
  ),
  8744 => 
  array (
	'subs' => '<tts>218</tts>',
	'UHC' => 'A1FD',
	'GBK' => 'A1C5',
	'SHIFT_JIS' => '81C9',
  ),
  8745 => 
  array (
	'subs' => '<tts>199</tts>',
	'BIG5' => 'A1E4',
	'UHC' => 'A1FB',
	'GBK' => 'A1C9',
	'SHIFT_JIS' => '81BF',
  ),
  8746 => 
  array (
	'subs' => '<tts>200</tts>',
	'BIG5' => 'A1E5',
	'UHC' => 'A1FA',
	'GBK' => 'A1C8',
	'SHIFT_JIS' => '81BE',
  ),
  8747 => 
  array (
	'subs' => '<tts>242</tts>',
	'BIG5' => 'A1EC',
	'UHC' => 'A1F2',
	'GBK' => 'A1D2',
	'SHIFT_JIS' => '81E7',
  ),
  8756 => 
  array (
	'subs' => '<tts>92</tts>',
	'BIG5' => 'A1EF',
	'UHC' => 'A1C5',
	'GBK' => 'A1E0',
	'SHIFT_JIS' => '8188',
  ),
  8764 => 
  array (
	'subs' => '<tts>126</tts>',
	'BIG5' => 'A1E3',
	'UHC' => 'A1AD',
  ),
  8773 => 
  array (
	'subs' => '<tts>64</tts>',
	'win-1252' => '',
  ),
  8776 => 
  array (
	'subs' => '<tts>187</tts>',
	'GBK' => 'A1D6',
  ),
  8800 => 
  array (
	'subs' => '<tts>185</tts>',
	'BIG5' => 'A1DA',
	'UHC' => 'A1C1',
	'GBK' => 'A1D9',
	'SHIFT_JIS' => '8182',
  ),
  8801 => 
  array (
	'subs' => '<tts>186</tts>',
	'BIG5' => 'A1DD',
	'UHC' => 'A1D5',
	'GBK' => 'A1D4',
	'SHIFT_JIS' => '81DF',
  ),
  8804 => 
  array (
	'subs' => '<tts>163</tts>',
	'UHC' => 'A1C2',
	'GBK' => 'A1DC',
  ),
  8805 => 
  array (
	'subs' => '<tts>179</tts>',
	'UHC' => 'A1C3',
	'GBK' => 'A1DD',
  ),
  8834 => 
  array (
	'subs' => '<tts>204</tts>',
	'UHC' => 'A1F8',
	'SHIFT_JIS' => '81BC',
  ),
  8835 => 
  array (
	'subs' => '<tts>201</tts>',
	'UHC' => 'A1F9',
	'SHIFT_JIS' => '81BD',
  ),
  8836 => 
  array (
	'subs' => '<tts>203</tts>',
  ),
  8838 => 
  array (
	'subs' => '<tts>205</tts>',
	'UHC' => 'A1F6',
	'SHIFT_JIS' => '81BA',
  ),
  8839 => 
  array (
	'subs' => '<tts>202</tts>',
	'UHC' => 'A1F7',
	'SHIFT_JIS' => '81BB',
  ),
  8853 => 
  array (
	'subs' => '<tts>197</tts>',
	'GBK' => 'A892',
  ),
  8855 => 
  array (
	'subs' => '<tts>196</tts>',
  ),
  8869 => 
  array (
	'subs' => '<tts>94</tts>',
	'BIG5' => 'A1E6',
	'UHC' => 'A1D1',
	'GBK' => 'A1CD',
	'SHIFT_JIS' => '81DB',
  ),
  8901 => 
  array (
	'subs' => '<tts>215</tts>',
  ),
  8992 => 
  array (
	'subs' => '<tts>243</tts>',
  ),
  8993 => 
  array (
	'subs' => '<tts>245</tts>',
  ),
  9001 => 
  array (
	'subs' => '<tts>225</tts>',
  ),
  9002 => 
  array (
	'subs' => '<tts>241</tts>',
  ),
  9312 => 
  array (
	'subs' => '<ttz>172</ttz>',
	'BIG5' => 'C7E9',
	'UHC' => 'A8E7',
	'GBK' => 'A2D9',
  ),
  9313 => 
  array (
	'subs' => '<ttz>173</ttz>',
	'BIG5' => 'C7EA',
	'UHC' => 'A8E8',
	'GBK' => 'A2DA',
  ),
  9314 => 
  array (
	'subs' => '<ttz>174</ttz>',
	'BIG5' => 'C7EB',
	'UHC' => 'A8E9',
	'GBK' => 'A2DB',
  ),
  9315 => 
  array (
	'subs' => '<ttz>175</ttz>',
	'BIG5' => 'C7EC',
	'UHC' => 'A8EA',
	'GBK' => 'A2DC',
  ),
  9316 => 
  array (
	'subs' => '<ttz>176</ttz>',
	'BIG5' => 'C7ED',
	'UHC' => 'A8EB',
	'GBK' => 'A2DD',
  ),
  9317 => 
  array (
	'subs' => '<ttz>177</ttz>',
	'BIG5' => 'C7EE',
	'UHC' => 'A8EC',
	'GBK' => 'A2DE',
  ),
  9318 => 
  array (
	'subs' => '<ttz>178</ttz>',
	'BIG5' => 'C7EF',
	'UHC' => 'A8ED',
	'GBK' => 'A2DF',
  ),
  9319 => 
  array (
	'subs' => '<ttz>179</ttz>',
	'BIG5' => 'C7F0',
	'UHC' => 'A8EE',
	'GBK' => 'A2E0',
  ),
  9320 => 
  array (
	'subs' => '<ttz>180</ttz>',
	'BIG5' => 'C7F1',
	'UHC' => 'A8EF',
	'GBK' => 'A2E1',
  ),
  9321 => 
  array (
	'subs' => '<ttz>181</ttz>',
	'BIG5' => 'C7F2',
	'UHC' => 'A8F0',
	'GBK' => 'A2E2',
  ),
  9632 => 
  array (
	'subs' => '<ttz>110</ttz>',
	'BIG5' => 'A1BD',
	'UHC' => 'A1E1',
	'GBK' => 'A1F6',
	'SHIFT_JIS' => '81A1',
  ),
  9650 => 
  array (
	'subs' => '<ttz>115</ttz>',
	'BIG5' => 'A1B6',
	'UHC' => 'A1E3',
	'GBK' => 'A1F8',
	'SHIFT_JIS' => '81A3',
  ),
  9660 => 
  array (
	'subs' => '<ttz>116</ttz>',
	'BIG5' => 'A1BF',
	'UHC' => 'A1E5',
	'GBK' => 'A88B',
	'SHIFT_JIS' => '81A5',
  ),
  9670 => 
  array (
	'subs' => '<ttz>117</ttz>',
	'BIG5' => 'A1BB',
	'UHC' => 'A1DF',
	'GBK' => 'A1F4',
	'SHIFT_JIS' => '819F',
  ),
  9674 => 
  array (
	'subs' => '<tts>224</tts>',
  ),
  9679 => 
  array (
	'subs' => '<ttz>108</ttz>',
	'BIG5' => 'A1B4',
	'UHC' => 'A1DC',
	'GBK' => 'A1F1',
	'SHIFT_JIS' => '819C',
  ),
  9687 => 
  array (
	'subs' => '<ttz>119</ttz>',
  ),
  9733 => 
  array (
	'subs' => '<ttz>72</ttz>',
	'BIG5' => 'A1B9',
	'UHC' => 'A1DA',
	'GBK' => 'A1EF',
	'SHIFT_JIS' => '819A',
  ),
  9742 => 
  array (
	'subs' => '<ttz>37</ttz>',
	'UHC' => 'A2CF',
  ),
  9755 => 
  array (
	'subs' => '<ttz>42</ttz>',
  ),
  9758 => 
  array (
	'subs' => '<ttz>43</ttz>',
	'UHC' => 'A2D1',
  ),
  9824 => 
  array (
	'subs' => '<tts>170</tts>',
	'UHC' => 'A2BC',
  ),
  9827 => 
  array (
	'subs' => '<tts>167</tts>',
	'UHC' => 'A2C0',
  ),
  9829 => 
  array (
	'subs' => '<tts>169</tts>',
	'UHC' => 'A2BE',
  ),
  9830 => 
  array (
	'subs' => '<tts>168</tts>',
  ),
  9985 => 
  array (
	'subs' => '<ttz>33</ttz>',
  ),
  9986 => 
  array (
	'subs' => '<ttz>34</ttz>',
  ),
  9987 => 
  array (
	'subs' => '<ttz>35</ttz>',
  ),
  9988 => 
  array (
	'subs' => '<ttz>36</ttz>',
  ),
  9990 => 
  array (
	'subs' => '<ttz>38</ttz>',
  ),
  9991 => 
  array (
	'subs' => '<ttz>39</ttz>',
  ),
  9992 => 
  array (
	'subs' => '<ttz>40</ttz>',
  ),
  9993 => 
  array (
	'subs' => '<ttz>41</ttz>',
  ),
  9996 => 
  array (
	'subs' => '<ttz>44</ttz>',
  ),
  9997 => 
  array (
	'subs' => '<ttz>45</ttz>',
  ),
  9998 => 
  array (
	'subs' => '<ttz>46</ttz>',
  ),
  9999 => 
  array (
	'subs' => '<ttz>47</ttz>',
  ),
  10000 => 
  array (
	'subs' => '<ttz>48</ttz>',
  ),
  10001 => 
  array (
	'subs' => '<ttz>49</ttz>',
  ),
  10002 => 
  array (
	'subs' => '<ttz>50</ttz>',
  ),
  10003 => 
  array (
	'subs' => '<ttz>51</ttz>',
  ),
  10004 => 
  array (
	'subs' => '<ttz>52</ttz>',
  ),
  10005 => 
  array (
	'subs' => '<ttz>53</ttz>',
  ),
  10006 => 
  array (
	'subs' => '<ttz>54</ttz>',
  ),
  10007 => 
  array (
	'subs' => '<ttz>55</ttz>',
  ),
  10008 => 
  array (
	'subs' => '<ttz>56</ttz>',
  ),
  10009 => 
  array (
	'subs' => '<ttz>57</ttz>',
  ),
  10010 => 
  array (
	'subs' => '<ttz>58</ttz>',
  ),
  10011 => 
  array (
	'subs' => '<ttz>59</ttz>',
  ),
  10012 => 
  array (
	'subs' => '<ttz>60</ttz>',
  ),
  10013 => 
  array (
	'subs' => '<ttz>61</ttz>',
  ),
  10014 => 
  array (
	'subs' => '<ttz>62</ttz>',
  ),
  10015 => 
  array (
	'subs' => '<ttz>63</ttz>',
  ),
  10016 => 
  array (
	'subs' => '<ttz>64</ttz>',
  ),
  10017 => 
  array (
	'subs' => '<ttz>65</ttz>',
  ),
  10018 => 
  array (
	'subs' => '<ttz>66</ttz>',
  ),
  10019 => 
  array (
	'subs' => '<ttz>67</ttz>',
  ),
  10020 => 
  array (
	'subs' => '<ttz>68</ttz>',
  ),
  10021 => 
  array (
	'subs' => '<ttz>69</ttz>',
  ),
  10022 => 
  array (
	'subs' => '<ttz>70</ttz>',
  ),
  10023 => 
  array (
	'subs' => '<ttz>71</ttz>',
  ),
  10025 => 
  array (
	'subs' => '<ttz>73</ttz>',
  ),
  10026 => 
  array (
	'subs' => '<ttz>74</ttz>',
  ),
  10027 => 
  array (
	'subs' => '<ttz>75</ttz>',
  ),
  10028 => 
  array (
	'subs' => '<ttz>76</ttz>',
  ),
  10029 => 
  array (
	'subs' => '<ttz>77</ttz>',
  ),
  10030 => 
  array (
	'subs' => '<ttz>78</ttz>',
  ),
  10031 => 
  array (
	'subs' => '<ttz>79</ttz>',
  ),
  10032 => 
  array (
	'subs' => '<ttz>80</ttz>',
  ),
  10033 => 
  array (
	'subs' => '<ttz>81</ttz>',
  ),
  10034 => 
  array (
	'subs' => '<ttz>82</ttz>',
  ),
  10035 => 
  array (
	'subs' => '<ttz>83</ttz>',
  ),
  10036 => 
  array (
	'subs' => '<ttz>84</ttz>',
  ),
  10037 => 
  array (
	'subs' => '<ttz>85</ttz>',
  ),
  10038 => 
  array (
	'subs' => '<ttz>86</ttz>',
  ),
  10039 => 
  array (
	'subs' => '<ttz>87</ttz>',
  ),
  10040 => 
  array (
	'subs' => '<ttz>88</ttz>',
  ),
  10041 => 
  array (
	'subs' => '<ttz>89</ttz>',
  ),
  10042 => 
  array (
	'subs' => '<ttz>90</ttz>',
  ),
  10043 => 
  array (
	'subs' => '<ttz>91</ttz>',
  ),
  10044 => 
  array (
	'subs' => '<ttz>92</ttz>',
  ),
  10045 => 
  array (
	'subs' => '<ttz>93</ttz>',
  ),
  10046 => 
  array (
	'subs' => '<ttz>94</ttz>',
  ),
  10047 => 
  array (
	'subs' => '<ttz>95</ttz>',
  ),
  10048 => 
  array (
	'subs' => '<ttz>96</ttz>',
  ),
  10049 => 
  array (
	'subs' => '<ttz>97</ttz>',
  ),
  10050 => 
  array (
	'subs' => '<ttz>98</ttz>',
  ),
  10051 => 
  array (
	'subs' => '<ttz>99</ttz>',
  ),
  10052 => 
  array (
	'subs' => '<ttz>100</ttz>',
  ),
  10053 => 
  array (
	'subs' => '<ttz>101</ttz>',
  ),
  10054 => 
  array (
	'subs' => '<ttz>102</ttz>',
  ),
  10055 => 
  array (
	'subs' => '<ttz>103</ttz>',
  ),
  10056 => 
  array (
	'subs' => '<ttz>104</ttz>',
  ),
  10057 => 
  array (
	'subs' => '<ttz>105</ttz>',
  ),
  10058 => 
  array (
	'subs' => '<ttz>106</ttz>',
  ),
  10059 => 
  array (
	'subs' => '<ttz>107</ttz>',
  ),
  10061 => 
  array (
	'subs' => '<ttz>109</ttz>',
  ),
  10063 => 
  array (
	'subs' => '<ttz>111</ttz>',
  ),
  10064 => 
  array (
	'subs' => '<ttz>112</ttz>',
  ),
  10065 => 
  array (
	'subs' => '<ttz>113</ttz>',
  ),
  10066 => 
  array (
	'subs' => '<ttz>114</ttz>',
  ),
  10070 => 
  array (
	'subs' => '<ttz>118</ttz>',
  ),
  10072 => 
  array (
	'subs' => '<ttz>120</ttz>',
  ),
  10073 => 
  array (
	'subs' => '<ttz>121</ttz>',
  ),
  10074 => 
  array (
	'subs' => '<ttz>122</ttz>',
  ),
  10075 => 
  array (
	'subs' => '<ttz>123</ttz>',
  ),
  10076 => 
  array (
	'subs' => '<ttz>124</ttz>',
  ),
  10077 => 
  array (
	'subs' => '<ttz>125</ttz>',
  ),
  10078 => 
  array (
	'subs' => '<ttz>126</ttz>',
  ),
  10081 => 
  array (
	'subs' => '<ttz>161</ttz>',
  ),
  10082 => 
  array (
	'subs' => '<ttz>162</ttz>',
  ),
  10083 => 
  array (
	'subs' => '<ttz>163</ttz>',
  ),
  10084 => 
  array (
	'subs' => '<ttz>164</ttz>',
  ),
  10085 => 
  array (
	'subs' => '<ttz>165</ttz>',
  ),
  10086 => 
  array (
	'subs' => '<ttz>166</ttz>',
  ),
  10087 => 
  array (
	'subs' => '<ttz>167</ttz>',
  ),
  10102 => 
  array (
	'subs' => '<ttz>182</ttz>',
  ),
  10103 => 
  array (
	'subs' => '<ttz>183</ttz>',
  ),
  10104 => 
  array (
	'subs' => '<ttz>184</ttz>',
  ),
  10105 => 
  array (
	'subs' => '<ttz>185</ttz>',
  ),
  10106 => 
  array (
	'subs' => '<ttz>186</ttz>',
  ),
  10107 => 
  array (
	'subs' => '<ttz>187</ttz>',
  ),
  10108 => 
  array (
	'subs' => '<ttz>188</ttz>',
  ),
  10109 => 
  array (
	'subs' => '<ttz>189</ttz>',
  ),
  10110 => 
  array (
	'subs' => '<ttz>190</ttz>',
  ),
  10111 => 
  array (
	'subs' => '<ttz>191</ttz>',
  ),
  10112 => 
  array (
	'subs' => '<ttz>192</ttz>',
  ),
  10113 => 
  array (
	'subs' => '<ttz>193</ttz>',
  ),
  10114 => 
  array (
	'subs' => '<ttz>194</ttz>',
  ),
  10115 => 
  array (
	'subs' => '<ttz>195</ttz>',
  ),
  10116 => 
  array (
	'subs' => '<ttz>196</ttz>',
  ),
  10117 => 
  array (
	'subs' => '<ttz>197</ttz>',
  ),
  10118 => 
  array (
	'subs' => '<ttz>198</ttz>',
  ),
  10119 => 
  array (
	'subs' => '<ttz>199</ttz>',
  ),
  10120 => 
  array (
	'subs' => '<ttz>200</ttz>',
  ),
  10121 => 
  array (
	'subs' => '<ttz>201</ttz>',
  ),
  10122 => 
  array (
	'subs' => '<ttz>202</ttz>',
  ),
  10123 => 
  array (
	'subs' => '<ttz>203</ttz>',
  ),
  10124 => 
  array (
	'subs' => '<ttz>204</ttz>',
  ),
  10125 => 
  array (
	'subs' => '<ttz>205</ttz>',
  ),
  10126 => 
  array (
	'subs' => '<ttz>206</ttz>',
  ),
  10127 => 
  array (
	'subs' => '<ttz>207</ttz>',
  ),
  10128 => 
  array (
	'subs' => '<ttz>208</ttz>',
  ),
  10129 => 
  array (
	'subs' => '<ttz>209</ttz>',
  ),
  10130 => 
  array (
	'subs' => '<ttz>210</ttz>',
  ),
  10131 => 
  array (
	'subs' => '<ttz>211</ttz>',
  ),
  10132 => 
  array (
	'subs' => '<ttz>212</ttz>',
  ),
  10136 => 
  array (
	'subs' => '<ttz>216</ttz>',
  ),
  10137 => 
  array (
	'subs' => '<ttz>217</ttz>',
  ),
  10138 => 
  array (
	'subs' => '<ttz>218</ttz>',
  ),
  10139 => 
  array (
	'subs' => '<ttz>219</ttz>',
  ),
  10140 => 
  array (
	'subs' => '<ttz>220</ttz>',
  ),
  10141 => 
  array (
	'subs' => '<ttz>221</ttz>',
  ),
  10142 => 
  array (
	'subs' => '<ttz>222</ttz>',
  ),
  10143 => 
  array (
	'subs' => '<ttz>223</ttz>',
  ),
  10144 => 
  array (
	'subs' => '<ttz>224</ttz>',
  ),
  10145 => 
  array (
	'subs' => '<ttz>225</ttz>',
  ),
  10146 => 
  array (
	'subs' => '<ttz>226</ttz>',
  ),
  10147 => 
  array (
	'subs' => '<ttz>227</ttz>',
  ),
  10148 => 
  array (
	'subs' => '<ttz>228</ttz>',
  ),
  10149 => 
  array (
	'subs' => '<ttz>229</ttz>',
  ),
  10150 => 
  array (
	'subs' => '<ttz>230</ttz>',
  ),
  10151 => 
  array (
	'subs' => '<ttz>231</ttz>',
  ),
  10152 => 
  array (
	'subs' => '<ttz>232</ttz>',
  ),
  10153 => 
  array (
	'subs' => '<ttz>233</ttz>',
  ),
  10154 => 
  array (
	'subs' => '<ttz>234</ttz>',
  ),
  10155 => 
  array (
	'subs' => '<ttz>235</ttz>',
  ),
  10156 => 
  array (
	'subs' => '<ttz>236</ttz>',
  ),
  10157 => 
  array (
	'subs' => '<ttz>237</ttz>',
  ),
  10158 => 
  array (
	'subs' => '<ttz>238</ttz>',
  ),
  10159 => 
  array (
	'subs' => '<ttz>239</ttz>',
  ),
  10161 => 
  array (
	'subs' => '<ttz>241</ttz>',
  ),
  10162 => 
  array (
	'subs' => '<ttz>242</ttz>',
  ),
  10163 => 
  array (
	'subs' => '<ttz>243</ttz>',
  ),
  10164 => 
  array (
	'subs' => '<ttz>244</ttz>',
  ),
  10165 => 
  array (
	'subs' => '<ttz>245</ttz>',
  ),
  10166 => 
  array (
	'subs' => '<ttz>246</ttz>',
  ),
  10167 => 
  array (
	'subs' => '<ttz>247</ttz>',
  ),
  10168 => 
  array (
	'subs' => '<ttz>248</ttz>',
  ),
  10169 => 
  array (
	'subs' => '<ttz>249</ttz>',
  ),
  10170 => 
  array (
	'subs' => '<ttz>250</ttz>',
  ),
  10171 => 
  array (
	'subs' => '<ttz>251</ttz>',
  ),
  10172 => 
  array (
	'subs' => '<ttz>252</ttz>',
  ),
  10173 => 
  array (
	'subs' => '<ttz>253</ttz>',
  ),
  10174 => 
  array (
	'subs' => '<ttz>254</ttz>',
  ),
  );

 if (strtolower($cp) == 'utf-8') {
	// FIND Characters Not in Font selected
	include(FPDF_FONTPATH . strtolower($ufont).'.php');	// Reads font information from e.g. freesans.php; sets array $cw
	foreach($mainarray AS $key=>$val) {
		if (!array_key_exists($key,$cw)) { 
			$subsarray[$key] = $val['subs'];
		}
	}

	if (!$cw[32]) {	// Unicode font file does not include ASCII characters (e.g. Indic scripts)
		for($i=32;$i<127;$i++) {
			$subsarray[$i] = '<tta>'.$i.'</tta>';
		}
	}

 }
 else {
  foreach($mainarray AS $key=>$val) {
	if (!isset($val[$cp])) {
		$subsarray[$key] = $val['subs'];
	}
  }
 }

  return $subsarray;

}



?>