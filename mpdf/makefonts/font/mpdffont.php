<?php

/* Edit  $newfont substituting your new font name. Omit any variants as required. */

$newfont = 'newfontname';


//============================================
// No need to edit any more

require('makefont.php');
$cpages = array('win-1251','iso-8859-2','iso-8859-4','iso-8859-7','iso-8859-9');

foreach($cpages AS $cpage) {
	MakeFont($newfont.'-'.$cpage.'.pfb', $newfont.'-'.$cpage.'.afm', $cpage);
	if (file_exists($newfont.'b-'.$cpage.'.afm')) MakeFont($newfont.'b-'.$cpage.'.pfb', $newfont.'b-'.$cpage.'.afm', $cpage);
	if (file_exists($newfont.'bi-'.$cpage.'.afm')) MakeFont($newfont.'bi-'.$cpage.'.pfb', $newfont.'bi-'.$cpage.'.afm',$cpage);
	if (file_exists($newfont.'i-'.$cpage.'.afm')) MakeFont($newfont.'i-'.$cpage.'.pfb', $newfont.'i-'.$cpage.'.afm', $cpage);
}
// win-1252
MakeFont($newfont.'.pfb', $newfont.'.afm', 'win-1252');
if (file_exists($newfont.'b.afm')) MakeFont($newfont.'b.pfb', $newfont.'b.afm','win-1252');
if (file_exists($newfont.'bi.afm')) MakeFont($newfont.'bi.pfb', $newfont.'bi.afm','win-1252');
if (file_exists($newfont.'i.afm')) MakeFont($newfont.'i.pfb', $newfont.'i.afm', 'win-1252');



?>