<?php


// List of ALL available fonts (incl. styles) in non-Unicode directory
// Always put main font (without styles) before font+style; put preferable defaults first in order
// Do NOT include Arial Helvetica Times Courier Symbol or ZapfDingbats
$this->available_fonts = array(
		'dejavusanscondensed','dejavusanscondensedB','dejavusanscondensedI','dejavusanscondensedBI',
		'dejavuserifcondensed','dejavuserifcondensedB','dejavuserifcondensedI','dejavuserifcondensedBI',
		'dejavusans','dejavusansB','dejavusansI','dejavusansBI',
		'dejavuserif','dejavuserifB','dejavuserifI','dejavuserifBI',
		'dejavusansmono','dejavusansmonoB','dejavusansmonoI','dejavusansmonoBI',

		'freesans','freesansB','freesansI','freesansBI',
		'freeserif','freeserifB','freeserifI','freeserifBI',
		'freemono','freemonoB','freemonoI','freemonoBI',
		'ocrb',

		);

// List of ALL available fonts (incl. styles) in Unicode directory
// Always put main font (without styles) before font+style; put preferable defaults first in order
// Do NOT include Arial Helvetica Times Courier Symbol or ZapfDingbats
$this->available_unifonts = array(
		'dejavusanscondensed','dejavusanscondensedB','dejavusanscondensedI','dejavusanscondensedBI',
		'dejavuserifcondensed','dejavuserifcondensedB','dejavuserifcondensedI','dejavuserifcondensedBI',
		'dejavusans','dejavusansB','dejavusansI','dejavusansBI',
		'dejavuserif','dejavuserifB','dejavuserifI','dejavuserifBI',
		'dejavusansmono','dejavusansmonoB','dejavusansmonoI','dejavusansmonoBI',

		'freesans','freesansB','freesansI','freesansBI',
		'freeserif','freeserifB','freeserifI','freeserifBI',
		'freemono','freemonoB','freemonoI','freemonoBI',
		'garuda','garudaB','garudaI','garudaBI',
		'norasi','norasiB','norasiI','norasiBI',
		'scheherazade',
		'ocrb',

		/* added Indic scripts mPDF 2.3 */
		'bengali-sans','bengali-serif','devanagari-sans','devanagari-serif','gujarati-serif','kannada-serif',
		'malayalam-sans','malayalam-serif','oriya-sans','punjabi-sans','tamil-sans','telugu-sans',


		);


// List of all font families in directories (either) 
// + any others that may be read from a stylesheet - to determine 'type'
// should include sans-serif, serif and monospace, arial, helvetica, times and courier
// The order will determine preference when substituting fonts in certain circumstances
$this->sans_fonts = array('dejavusanscondensed','dejavusans','freesans','franklin','tahoma','garuda','calibri','trebuchet',
				'verdana','geneva','lucida','arial','helvetica','arialnarrow','arialblack','sans','sans-serif','cursive','fantasy',
				'bengali-sans','devanagari-sans','malayalam-sans','oriya-sans',
				'punjabi-sans','tamil-sans','telugu-sans'
);

$this->serif_fonts = array('dejavuserifcondensed','dejavuserif','freeserif','constantia','georgia','albertus','times',
				'norasi','scheherazade','serif', 
				'bengali-serif','devanagari-serif','gujarati-serif','kannada-serif','malayalam-serif'
);

$this->mono_fonts = array('dejavusansmono','freemono','courier', 'mono','monospace','ocrb','ocr-b');

?>