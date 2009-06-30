<?php

/* Edit  $newfont substituting your new font name, and the original file names (without the .ttf). Omit any variants as required. */

$newfont = 'newfontname';

$original = 'OriginalFontFile';
$originalItalic = 'OriginalFontFile-Italic';
$originalBold = 'OriginalFontFile-Bold';
$originalBoldItalic = 'OriginalFontFile-BoldItalic';

//============================================
// No need to edit any more


require('makefontuni.php');


MakeFont($original .'.ttf', $newfont.'.ufm');
if (file_exists($newfont.'b.ufm') && $originalBold ) MakeFont($originalBold .'.ttf', $newfont.'b.ufm');
if (file_exists($newfont.'bi.ufm') && $originalBoldItalic ) MakeFont($originalBoldItalic .'.ttf', $newfont.'bi.ufm');
if (file_exists($newfont.'i.ufm') && $originalItalic ) MakeFont($originalItalic .'.ttf', $newfont.'i.ufm');




?>