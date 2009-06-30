<?php


/*******************************************************************************
* Software: mPDF, Unicode-HTML Free PDF generator                              *
* Version:  3.0beta  based on                                                  *
*           FPDF 1.52 by Olivier PLATHEY                                       *
*           UFPDF 0.1 by Steven Wittens                                        *
*           HTML2FPDF 3.0.2beta by Renato Coelho                               *
* Date:     2009-06-14                                                         *
* Author:   Ian Back <ianb@bpm1.com>                                           *
* License:  GPL                                                                *
*                                                                              *
* Changes:  See changelog.txt                                                  *
*******************************************************************************/


define('mPDF_VERSION','3.0');

// mPDF 2.3
define('AUTOFONT_CJK',1);
define('AUTOFONT_THAIVIET',2);
define('AUTOFONT_RTL',4);
define('AUTOFONT_INDIC',8);
define('AUTOFONT_ALL',15);

// mPDF 2.0
define('_BORDER_ALL',15);
define('_BORDER_TOP',8);
define('_BORDER_RIGHT',4);
define('_BORDER_BOTTOM',2);
define('_BORDER_LEFT',1);

if (!defined('_MPDF_PATH')) define('_MPDF_PATH','');
require_once(_MPDF_PATH.'htmltoolkit.php');
// mPDF 2.4
if (!defined('_JPGRAPH_PATH')) define("_JPGRAPH_PATH", _MPDF_PATH.'jpgraph/'); 

// mPDF 2.4
$errorlevel=error_reporting();
$errorlevel=error_reporting($errorlevel & ~E_NOTICE);

if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
@date_default_timezone_set(@date_default_timezone_get());

class mPDF
{
/* Updated mPDF 2.0 to allow permissions for annotations (comments) and forms */
var $pdf_version = '1.5';

// mPDF 2.4. Reusing DocTemplate
var $docTemplateStart;		// Internal flag for page (page no. -1) that docTemplate starts on

///////////////////
///////////////////
// HYPHENATION
// mPDF 2.5 Soft Hyphens
///////////////////
///////////////////
var $hyphenate = false;
var $hyphenateTables = false;
var $SHYlang = "en";
var $SHYleftmin = 2;
var $SHYrightmin = 2;
var $SHYcharmin = 2;
var $SHYcharmax = 10;
var $SHYpatterns = array();
var $SHYlanguages = array('en','de','es','fi','fr','it','nl','pl','ru','sv');	// existing patterns
var $loadedSHYpatterns;
var $loadedSHYdictionary = false;
var $SHYdictionary = array();
var $SHYdictionaryWords = array();


// mPDF 3.0 Float DIV
var $blockContext = 1;
var $floatDivs = array();

// mPDF 3.0 - Tiling Patterns
var $patterns = array();	// Tiling patterns used for backgrounds
var $pageBackgrounds = array();

var $bodyBackgroundGradient;
var $bodyBackgroundImage;
var $bodyBackgroundColor;

// mPDF 3.0 PageNumber Conditional Text
var $pagenumPrefix;
var $pagenumSuffix;
var $nbpgPrefix;
var $nbpgSuffix;

var $writingHTMLheader = false;

var $showImageErrors = false;	// false/true; can set = 2 to show errors on primary parsing of PNG files

var $allow_output_buffering = false;

var $autoPadding = false; // Automatically increases padding in block elements with border-radius set - if required

// mPDF 3.0
var $gradients = array();

// mPDF 3.0
var $kwt_Reference = array();
var $kwt_BMoutlines = array();
var $kwt_toc = array();

var $tbrot_Reference = array();
var $tbrot_BMoutlines = array();
var $tbrot_toc = array();

var $col_Reference = array();
var $col_BMoutlines = array();
var $col_toc = array();

// mPDF 2.4 JPGRAPH
var $useGraphs = false;
var $currentGraphId;
var $graphs = array();

// mPDF 2.4 Float Images
var $floatbuffer = array();
var $floatmargins = array();

var $bullet;
var $bulletarray;

// mPDF 2.3 autoFontGroups
var $autoFontGroups = 0;

var $autoFontGroupSize = 2;	// 1: individual words are spanned; 2: words+; 3: as big chunks as possible.
var $rtlAsArabicFarsi = false;	// Treats all Arabic (which may include Pashto, Urdu etc) as Arabic or Farsi/Persian
						// Uses one font, and joins to presentation forms (Hebrew is OK)

var $disableMultilingualJustify = true;	// Disables If more than one language on a line using different text-justification
							// e.g. Chinese (character) and RTL (word)

var $tabSpaces = 8;	// Number of spaces to replace for a TAB in <pre> sections
				// Notepad uses 6, HTML specification recommends 8

// mPDF 2.3 
var $useLang;
var $currentLang;
var $default_lang;
var $default_jSpacing;
var $default_available_fonts;
var $default_dir;

// mPDF 2.3 Templates
var $pageTemplate;
var $docTemplate;
var $docTemplateContinue;

// mPDF 2.3 
var $arabGlyphs = null;
var $arabHex = null;
var $persianGlyphs = null;
var $persianHex = null;
var $arabVowels;
var $arabPrevLink;
var $arabNextLink;

var $restoreBlockPagebreaks = false;

// mPDF 2.2
var $formobjects=array(); // array of Form Objects for WMF
var $gdiObjectArray;      // array of GDI objects for WMF
var $watermarkTextAlpha = 0.2;
var $watermarkImageAlpha = 0.2;
var $watermark_size;
var $watermark_pos;
var $annotSize = 0.5;	// default mm for Adobe annotations - nominal
var $annotMargin;		// default position for Annotations
var $annotOpacity = 0.5;	// default opacity for Annotations
var $title2annots = false;
var $InlineProperties=array();	// Should have done this a long time ago
var $InlineAnnots=array();
var $ktAnnots=array();
var $tbrot_Annots=array();
var $kwt_Annots=array();
var $columnAnnots=array();

var $PageAnnots; // mPDF 2.4 changed from $pageAnnots

var $pageDim=array();	// Keep track of page wxh for orientation changes - set in _beginpage, used in _putannots

// mPDF 2.2 - variable name changed to lowercase first letter
var $keepColumns = false;	// Set to go to the second column only when the first is full of text etc.

// mPDF 2.2
var $keep_table_proportions = false;	// If table width set > page width, force resizing but keep relative sizes
							// Also forces respect of cell widths set by %
var $ignore_table_widths = false;
var $ignore_table_percents = false;

// mPDF 2.1
var $list_align_style = 'R';	// Determines alignment of numbers in numbered lists
var $list_number_suffix = '.';	// Content to follow a numbered list marker e.g. '.' gives 1. or IV.; ')' gives 1) or a)

var $breakpoints = array();	// used in columnbuffer

var $useSubstitutions = true;

/* mPDF 2.0 - Nested Tables */
var $tableLevel=0;
var $tbctr=array();	// counter for nested tables at each level
var $innermostTableLevel;
var $saveTableCounter;
var $cellBorderBuffer;

var $saveHTMLFooter_height;
var $saveHTMLFooterE_height;

var $firstPageBoxHeader;
var $firstPageBoxHeaderEven;
var $firstPageBoxFooter;
var $firstPageBoxFooterEven;

// mPDF 2.0 Paged media
var $page_box = array();
var $show_marks = '';	// crop or cross marks

var $disablePrintCSS;	// prevents CSS stylesheets marked as media="print" to be ignored

// mPDF 2.0
var $basepathIsLocal;

// mPDF 2.0 Keep heading with following table
var $use_kwt = false;
var $kwt = false;
var $kwt_height = 0;
var $kwt_y0 = 0;
var $kwt_x0 = 0;
var $kwt_buffer = array();
var $kwt_Links = array();
var $kwt_moved = false;
var $kwt_saved = false;


// mPDF 2.3
var $formBgColor = 'white';
var $formBgColorSmall = '#DDDDFF';	// Color used for background of form fields if reduced in size (so border disappears)

// Added mPDF 1.3
var $PageNumSubstitutions = array();

var $forcePortraitHeaders = false;
// mPDF 2.3
var $forcePortraitMargins = false;
var $displayDefaultOrientation = false;
 
// mPDF 2.0
var $table_borders_separate; 
var $base_table_properties=array();
var $tblborderstyles = array('inset','groove','outset','ridge','dotted','dashed','solid','double');
// doesn't include none or hidden

var $listjustfinished;
var $blockjustfinished;

// v1.4 Save orginal settings in case of changed orientation
var $orig_bMargin;
var $orig_tMargin;
var $orig_lMargin;
var $orig_rMargin;
var $orig_hMargin;
var $orig_fMargin;

var $pageheaders=array();
var $pagefooters=array();

var $pageHTMLheaders=array();
var $pageHTMLfooters=array();

var $headerPageNoMarker = "!|";	// use as pagenumber placeholder in HTML Headers & Footers 
						// Must use ASCII characters i.e. code <128; avoid (normal) quotes
var $saveHTMLFooter_N;
var $saveHTMLFooterE_N;
var $saveHTMLFooter_NN;
var $saveHTMLFooterE_NN;
var $saveHTMLFooter_NNN;
var $saveHTMLFooterE_NNN;
var $saveHTMLFooter_NNNN;
var $saveHTMLFooterE_NNNN;
var $saveHTMLFooter_NNNNN;
var $saveHTMLFooterE_NNNNN;

// mPDF 3.0
var $HTMLheaderPageLinks = array();
var $saveHeaderLinksE_N = array();
var $saveHeaderLinksE_NN = array();
var $saveHeaderLinksE_NNN = array();
var $saveHeaderLinksE_NNNN = array();
var $saveHeaderLinksE_NNNNN = array();
var $saveHeaderLinks_N = array();
var $saveHeaderLinks_NN = array();
var $saveHeaderLinks_NNN = array();
var $saveHeaderLinks_NNNN = array();
var $saveHeaderLinks_NNNNN = array();
var $saveFooterLinksE_N = array();
var $saveFooterLinksE_NN = array();
var $saveFooterLinksE_NNN = array();
var $saveFooterLinksE_NNNN = array();
var $saveFooterLinksE_NNNNN = array();
var $saveFooterLinks_N = array();
var $saveFooterLinks_NN = array();
var $saveFooterLinks_NNN = array();
var $saveFooterLinks_NNNN = array();
var $saveFooterLinks_NNNNN = array();

// See mpdf_config.php for these next 5 values
var $available_fonts;
var $available_unifonts;
var $sans_fonts;
var $serif_fonts;
var $mono_fonts;

// List of ALL available CJK fonts (incl. styles) (Adobe add-ons)  hw removed
var $available_CJK_fonts = array(
		'gb','big5','sjis','uhc',
		'gbB','big5B','sjisB','uhcB',
		'gbI','big5I','sjisI','uhcI',
		'gbBI','big5BI','sjisBI','uhcBI',
		);

// Added v1.2 option to continue if invalid UTF-8 chars - used in function is_utf8()
var $ignore_invalid_utf8 = false;

// Added in mPDF v1.2
var $allowedCSStags = 'DIV|P|H1|H2|H3|H4|H5|H6|FORM|IMG|A|BODY|TABLE|HR|THEAD|TFOOT|TBODY|TH|TR|TD|UL|OL|LI|PRE|BLOCKQUOTE|ADDRESS|DL|DT|DD';
var $cascadeCSS = array();

// Added mPDF 1.2 HTML headers and Footers
var $HTMLHeader;
var $HTMLFooter;
var $HTMLHeaderE;	// for Even pages
var $HTMLFooterE;	// for Even pages
var $bufferoutput = false; 

// This will force all fonts to be substituted with Arial(Helvetica) Times or Courier when using codepage win-1252 - makes smaller files
var $useOnlyCoreFonts = false;
var $use_embeddedfonts_1252 = false;

// If using a CJK codepage with only CJK/ASCII or embedded characters, this will prevent laoding of Unicode fonts - makes smaller files
var $use_CJK_only = false;

// Allows automatic character set conversion if "charset=xxx" detected in html header (WriteHTML() )
var $allow_charset_conversion = true;

var $jSpacing;	// Spacing method when Justifying [ C, W or blank (for mixed 40/60) ]
var $jSWord = 0.4;	// Percentage(/100) of space (when justifying margins) to allocate to Word vs. Character
var $jSmaxChar = 2;	// Maximum spacing to allocate to character spacing. (0 = no maximum)

var $orphansAllowed = 5;	// No of SUP or SUB characters to include on line to avoid leaving e.g. end of line//<sup>32</sup>
var $max_colH_correction = 1.15;	// Maximum ratio to adjust column height when justifying - too large a value can give ugly results


var $table_error_report = false;		// Die and report error if table is too wide to contain whole words
var $table_error_report_param = '';		// Parameter which can be passed to show in error report i.e. chapter number being processed//
// mPDF 2.2 - variable name changed to lowercase first letter
var $biDirectional=false;	// automatically determine BIDI text in LTR page
var $text_input_as_HTML = false; // Converts all entities in Text inputs to UTF-8 before encoding
// mPDF 2.2 - variable name changed to lowercase first letter
var $anchor2Bookmark = 0;	// makes <a name=""> into a bookmark as well as internal link target; 1 = just name; 2 = name (p.34)
var $list_indent_first_level = 0;	// 1/0 yex/no to indent first level of list
var $shrink_tables_to_fit = 1.4;	// automatically reduce fontsize in table if words would have to split ( not in CJK)
						// 0 or false to disable; value (if set) gives maximum factor to reduce fontsize

var $rtlCSS = 2; 	// RTL: 0 overrides defaultCSS; 1 overrides stylesheets; 2 overrides inline styles - TEXT-ALIGN left => right etc.
			// when directionality is set to rtl

// Automatically correct for tags where HTML specifies optional end tags e.g. P,LI,DD,TD
// If you are confident input html is valid XHTML, turning this off may make it more reliable(?)
var $allow_html_optional_endtags = true;

var $img_dpi = 96;	// Default dpi to output images if size not defined

// Values used if simple FOOTER/HEADER given i.e. not array
var $defaultheaderfontsize = 8;	// pt
var $defaultheaderfontstyle = 'BI';	// '', or 'B' or 'I' or 'BI'
var $defaultheaderline = 1;		// 1 or 0 - line under the header
var $defaultfooterfontsize = 8;	// pt
var $defaultfooterfontstyle = 'BI';	// '', or 'B' or 'I' or 'BI'
var $defaultfooterline = 1;		// 1 or 0 - line over the footer
var $header_line_spacing = 0.25;	// spacing between bottom of header and line (if present) - function of fontsize
var $footer_line_spacing = 0.25;	// spacing between bottom of header and line (if present) - function of fontsize

var $showdefaultpagenos = true;	// DEPRACATED -left for backward compatability


var $chrs;	// Added mPDF 1.1 used to store chr() and ord() - quicker than using functions
var $ords;

//////////////////////////////////////////////

// Default values if no style sheet offered	(cf. http://www.w3.org/TR/CSS21/sample.html)
var $defaultCSS = array(
	'BODY' => array(
		'FONT-FAMILY' => 'serif',
		'FONT-SIZE' => '11pt',
		'TEXT-ALIGN' => 'left',
		'LINE-HEIGHT' => 1.33,
		'MARGIN-COLLAPSE' => 'collapse', /* Custom property to collapse top/bottom margins at top/bottom of page - ignored in tables/lists */
	),
	'P' => array(
		'TEXT-INDENT' => '0pt',	/* ?HTML SPEC is INDENT? */
		'TEXT-ALIGN' => 'left',
		'MARGIN' => '1.12em 0',
	),
	'H1' => array(
		'FONT-SIZE' => '2em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '0.67em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H2' => array(
		'FONT-SIZE' => '1.5em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '0.75em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H3' => array(
		'FONT-SIZE' => '1.17em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '0.83em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H4' => array(
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '1.12em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H5' => array(
		'FONT-SIZE' => '0.83em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '1.5em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H6' => array(
		'FONT-SIZE' => '0.75em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '1.67em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'HR' => array(
		'COLOR' => '#888888',
		'TEXT-ALIGN' => 'center',
		'WIDTH' => '100%',
		'HEIGHT' => '0.2mm',
		'MARGIN-TOP' => '0.83em',
		'MARGIN-BOTTOM' => '0.83em',
	),
	'PRE' => array(
		'MARGIN' => '0.83em 0',
		'FONT-FAMILY' => 'monospace',
	),
	'S' => array(
		'TEXT-DECORATION' => 'line-through',
	),
	'STRIKE' => array(
		'TEXT-DECORATION' => 'line-through',
	),
	'DEL' => array(
		'TEXT-DECORATION' => 'line-through',
	),
	'SUB' => array(
		'VERTICAL-ALIGN' => 'sub',
		'FONT-SIZE' => '55%',	/* Recommended 0.83em */
	),
	'SUP' => array(
		'VERTICAL-ALIGN' => 'super',
		'FONT-SIZE' => '55%',	/* Recommended 0.83em */
	),
	'U' => array(
		'TEXT-DECORATION' => 'underline',
	),
	'INS' => array(
		'TEXT-DECORATION' => 'underline',
	),
	'B' => array(
		'FONT-WEIGHT' => 'bold',
	),
	'STRONG' => array(
		'FONT-WEIGHT' => 'bold',
	),
	'I' => array(
		'FONT-STYLE' => 'italic',
	),
	'CITE' => array(
		'FONT-STYLE' => 'italic',
	),
	'Q' => array(
		'FONT-STYLE' => 'italic',
	),
	'EM' => array(
		'FONT-STYLE' => 'italic',
	),
	'VAR' => array(
		'FONT-STYLE' => 'italic',
	),
	'SAMP' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'CODE' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'KBD' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'TT' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'SMALL' => array(
		'FONT-SIZE' => '83%',
	),
	'BIG' => array(
		'FONT-SIZE' => '117%',
	),
	'ACRONYM' => array(
		'FONT-SIZE' => '77%',
		'FONT-WEIGHT' => 'bold',
	),
	'ADDRESS' => array(
		'FONT-STYLE' => 'italic',
	),
	'BLOCKQUOTE' => array(
		'MARGIN-LEFT' => '40px',
		'MARGIN-RIGHT' => '40px',
		'MARGIN-TOP' => '1.12em',
		'MARGIN-BOTTOM' => '1.12em',
	),
	'A' => array(
		'COLOR' => '#0000FF',
		'TEXT-DECORATION' => 'underline',
	),
	'UL' => array(
		'MARGIN' => '0.83em 0',		/* only applied to top-level of nested lists */
		'TEXT-INDENT' => '1.3em',	/* Custom effect - list indent */
		'LINE-HEIGHT' => 1.3,
	),
	'OL' => array(
		'MARGIN' => '0.83em 0',		/* only applied to top-level of nested lists */
		'TEXT-INDENT' => '1.3em',	/* Custom effect - list indent */
		'LINE-HEIGHT' => 1.3,
	),
	'DL' => array(
		'MARGIN' => '1.67em 0',
	),
	'DT' => array(
	),
	'DD' => array(
		'PADDING-LEFT' => '40px',
	),
	'TABLE' => array(
		'MARGIN' => '0.83em 0',
		'BORDER-COLLAPSE' => 'separate',
		'BORDER-SPACING' => '2px',	/* Added mPDF 2.0 */
		'EMPTY-CELLS' => 'show',	/* Added mPDF 2.0  2.2 Changed default */
		'TEXT-ALIGN' => 'left',
		'LINE-HEIGHT' => '1.2',
		'VERTICAL-ALIGN' => 'middle',
	),
	'THEAD' => array(
	),
	'TFOOT' => array(
	),
	'TH' => array(
		'FONT-WEIGHT' => 'bold',
		'TEXT-ALIGN' => 'center',
		'PADDING-LEFT' => '0.1em',	/* added mPDF 2.0 */
		'PADDING-RIGHT' => '0.1em',
		'PADDING-TOP' => '0.1em',
		'PADDING-BOTTOM' => '0.1em',
	),
	'TD' => array(
		'PADDING-LEFT' => '0.1em',	/* added mPDF 2.0 */
		'PADDING-RIGHT' => '0.1em',
		'PADDING-TOP' => '0.1em',
		'PADDING-BOTTOM' => '0.1em',
	),
	'IMG' => array(
		'MARGIN' => '0.5em',
		'VERTICAL-ALIGN' => 'bottom',
	),
	'INPUT' => array(
		'FONT-FAMILY' => 'sans-serif',
		'VERTICAL-ALIGN' => 'middle',
		'FONT-SIZE' => '0.9em',
	),
	'SELECT' => array(
		'FONT-FAMILY' => 'sans-serif',
		'FONT-SIZE' => '0.9em',
		'VERTICAL-ALIGN' => 'middle',
	),
	'TEXTAREA' => array(
		'FONT-FAMILY' => 'monospace',
		'FONT-SIZE' => '0.9em',
		'VERTICAL-ALIGN' => 'top',
	),
);

//////////////////////////////////////////////////////////
//// mPDF compatible /////////////////////////////////////
//////////////////////////////////////////////////////////
// mPDF 2.2 Now changed to a default stylesheet mpdf.css
var $useDefaultCSS2 = false;
var $defaultCSS2 = array();
//////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////

///////////END OF USER-DEFINED VARIABLES//////////////////

//internal attributes
var $form_element_spacing;
var $textarea_lineheight = 1.25;
var $linemaxfontsize;
var $lineheight_correction;
var $lastoptionaltag = '';	// Save current block item which HTML specifies optionsl endtag
var $pageoutput;
var $charset_in = '';
var $blk = array();
var $blklvl = 0;
var $ColumnAdjust;
var $ws;	// Word spacing
var $HREF; //! string
var $pgwidth; //! float
var $fontlist; //! array 
var $issetfont; //! bool
var $issetcolor; //! bool
var $titulo; //! string
var $oldx; //! float
var $oldy; //! float
var $B; //! int
var $U; //! int
var $I; //! int

var $tdbegin; //! bool
var $table; //! array
var $cell; //! array 
var $col; //! int
var $row; //! int

var $divbegin; //! bool
var $divalign; //! char
var $divwidth; //! float
var $divheight; //! float
var $divrevert; //! bool
var $spanbgcolor; //! bool

var $spanlvl;
var $listlvl; //! int
var $listnum; //! int
var $listtype; //! string
//array(lvl,# of occurrences)
var $listoccur; //! array
//array(lvl,occurrence,type,maxnum)
var $listlist; //! array
//array(lvl,num,content,type)
var $listitem; //! array

var $pjustfinished; //! bool
var $ignorefollowingspaces; //! bool
var $SUP; //! bool
var $SUB; //! bool
var $SMALL; //! bool
var $BIG; //! bool
var $toupper; //! bool
var $tolower; //! bool
var $dash_on; //! bool
var $dotted_on; //! bool
var $strike; //! bool

var $CSS; //! array
var $textbuffer; //! array
var $currentfontstyle; //! string
var $currentfontfamily; //! string
var $currentfontsize; //! string
var $colorarray; //! array
var $bgcolorarray; //! array
var $internallink; //! array
var $enabledtags; //! string

var $lineheight; //! int
var $basepath; //! string
// array('COLOR','WIDTH','OLDWIDTH')
var $outlineparam; //! array
var $outline_on; //! bool

var $specialcontent; //! string
var $selectoption; //! array

//options attributes
var $usecss; //! bool
var $usepre; //! bool
var $usetableheader; //! bool

// mPDF 3.0
var $tableheadernrows;

var $shownoimg; //! bool

var $objectbuffer;

// Table Rotation
var $table_rotate;	// flag used for table rotation
var $tbrot_maxw;		// Max width for rotated table
var $tbrot_maxh;		// Max height
var $tablebuffer;		// Buffer used when rotating table

// mPDF 2.0
var $tbrot_align = 'C';
var $tbrot_Links;	

// Edited mPDF 1.1 keeping block together on one page
var $divbuffer;		// Buffer used when keeping DIV on one page
var $keep_block_together;	// Keep a Block from page-break-inside: avoid
var $ktLinks;		// Keep-together Block links array
var $ktBlock;		// Keep-together Block array
var $ktReference;
var $ktBMoutlines;
var $_kttoc;


var $tbrot_y0;		// y position starting table rotate
var $tbrot_x0;		// x position starting table rotate
var $tbrot_w;		// Actual printed width
var $tbrot_h;		// Actual printed height
var $TOCmark = 0;		// Page to insert Table of Contents

var $is_MB=false;		// mPDF 2.5 renamed from isunicode
var $codepage='win-1252';
var $isCJK = false;
var $mb_encoding='windows-1252';
var $directionality='ltr';


// mPDF 2.3 Updated
var $pregRTLchars = "\x{0590}-\x{06FF}\x{0750}-\x{077F}\x{FB00}-\x{FDFD}\x{FE70}-\x{FEFF}";	
// pattern used to detect RTL characters -> force RTL

// Used in AutoFont()
// CJK Chars which require changing and are distinctive of specific charset
var $pregUHCchars = "\x{3130}-\x{318F}\x{AC00}-\x{D7AF}";	// mPDF 3.0  removed Old Hangul 1100-11FF
var $pregSJISchars = "\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{3190}-\x{319F}\x{31F0}-\x{31FF}";	// mPDF 3.0 

// Chars which distinguish CJK but not between different
var $pregCJKchars = "\x{2E80}-\x{A4CF}\x{A800}-\x{D7AF}\x{F900}-\x{FAFF}\x{FF00}-\x{FFEF}\x{20000}-\x{2FA1F}";	// mPDF 3.0 widen Plane 3

// ASCII Chars which shouldn't break string
// Use for very specific words
var $pregASCIIchars1 = "\x{0021}-\x{002E}\x{0030}-\x{003B}?";	// no [SPACE]
// Use for words+
var $pregASCIIchars2 = "\x{0020}-\x{002E}\x{0030}-\x{003B}?";	// [SPACE] punctuation and 0-9
// Use for chunks > words
var $pregASCIIchars3 = "\x{0000}-\x{002E}\x{0030}-\x{003B}\x{003F}-\x{007E}";	// all except <>

// Vietnamese - specific
var $pregVIETchars = "\x{01A0}\x{01A1}\x{01AF}\x{01B0}\x{1EA0}-\x{1EF1}";	
// Vietnamese -  Chars which shouldn't break string 
var $pregVIETPluschars = "\x{0000}-\x{003B}\x{003F}-\x{00FF}\x{0300}-\x{036F}\x{0102}\x{0103}\x{0110}\x{0111}\x{0128}\x{0129}\x{0168}\x{0169}\x{1EF1}-\x{1EF9}";	// omits < >

var $pregHEBchars = "\x{0590}-\x{05FF}\x{FB00}-\x{FB49}";	// Hebrew

// Arabic
var $pregARABICchars = "\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFD}\x{FE70}-\x{FEFF}";	
// Characters of Urdu, Pashto, Sindhi (but NOT arabic or persian/farsi) [not covered by DejavuSans font]
var $pregNonARABICchars = "\x{0671}-\x{067D}\x{067F}-\x{0685}\x{0687}-\x{0697}\x{0699}-\x{06A8}\x{06AA}-\x{06AE}\x{06B0}-\x{06CB}\x{06CD}-\x{06D3}";	

// INDIC
var $pregASCIILchars = "0-9 ";	// ASCII Chars which shouldn't break string if limited coverage in fonts (Indic scripts)


// Removed in mPDF v1.2
//var $memory_opt = false;	// Memory Optimization - added mPDF1.1
var $extgstates; // Used for alpha channel - Transparency (Watermark)
var $tt_savefont;
var $mgl;
var $mgt;
var $mgr;
var $mgb;

var $tts = false;
var $ttz = false;
var $tta = false;

var $headerDetails=array();
var $footerDetails=array();
var $useOddEven = 0;

var $splitdivborderwidth = 0.2;	// Linewidth used when drawing border split across pages

// Best to alter the below variables using default stylesheet above
var $div_margin_bottom;	
var $div_bottom_border = '';
var $p_margin_bottom;
var $p_bottom_border = '';
var $page_break_after_avoid = false;
var $margin_bottom_collapse = false;
var $img_margin_top;	// default is set at top of fn.openTag 'IMG'
var $img_margin_bottom;
var $text_indent = 0;	// Indent hanging margin for <p>
var $list_indent;	// array
var $list_align;	// array
var $list_margin_bottom; 
var $default_font_size;	// in pts
var $default_lineheight_correction=1.2;	// Value 1 sets lineheight=fontsize height; 
var $original_default_font_size;	// used to save default sizes when using table default
var $original_default_font;
var $watermark_font = '';
var $defaultAlign = 'L';

// TABLE
var $defaultTableAlign = 'L';
var $tablethead = 0;
var $thead_font_weight;	
var $thead_font_style;	
var $thead_valign_default;	
var $thead_textalign_default;	

var $tabletfoot = 0;
var $tfoot_font_weight;	
var $tfoot_font_style;	
var $tfoot_valign_default;	
var $tfoot_textalign_default;	

// Added mPDF 1.3 for rotated text in cell
var $trow_text_rotate;	// 90,-90

var $cellPaddingL;
var $cellPaddingR;
var $cellPaddingT;
var $cellPaddingB;
var $table_lineheight;

// Added mPDF 1.1 for correct table border inheritance
var $table_border_attr_set = 0;
var $table_border_css_set = 0;

var $shrin_k = 1.0;			// factor with which to shrink tables - used internally - do not change
var $shrink_this_table_to_fit = 0;	// flag used when table autosize turned on and off by tags
						// 0 or false to disable; value (if set) gives maximum factor to reduce fontsize
// mPDF 2.2
var $watermarkText = '';
var $watermarkImage = '';
var $showWatermarkText = 0;
var $showWatermarkImage = 0;
var $UnvalidatedText = '';
var $TopicIsUnvalidated = 0;

var $MarginCorrection = 0;	// corrects for OddEven Margins
var $margin_footer=15;	// in mm
var $margin_header=15;	// in mm

var $tabletheadjustfinished = false;
var $usingCoreFont = false;	// mPDF 2.5 renamed from $usingembeddedfonts
var $charspacing=0;

//Private properties FROM FPDF
var $DisplayPreferences=''; //EDITEI - added
var $outlines=array(); //EDITEI - added
var $flowingBlockAttr; //EDITEI - added
var $page;               //current page number
var $n;                  //current object number
var $offsets;            //array of object offsets
var $buffer;             //buffer holding in-memory PDF
var $pages;              //array containing pages
var $state;              //current document state
var $compress;           //compression flag
var $DefOrientation;     //default orientation
var $CurOrientation;     //current orientation
var $OrientationChanges; //array indicating orientation changes
var $k;                  //scale factor (number of points in user unit)
var $fwPt,$fhPt;         //dimensions of page format in points
var $fw,$fh;             //dimensions of page format in user unit
var $wPt,$hPt;           //current dimensions of page in points
var $w,$h;               //current dimensions of page in user unit
var $lMargin;            //left margin
var $tMargin;            //top margin
var $rMargin;            //right margin
var $bMargin;            //page break margin
var $cMarginL;            //cell margin Left
var $cMarginR;            //cell margin Right
var $cMarginT;            //cell margin Left
var $cMarginB;            //cell margin Right
var $DeflMargin;            //Default left margin
var $DefrMargin;            //Default right margin
var $x,$y;               //current position in user unit for cell positioning
var $lasth;              //height of last cell printed
var $LineWidth;          //line width in user unit
var $CoreFonts;          //array of standard font names
var $fonts;              //array of used fonts
var $FontFiles;          //array of font files
var $diffs;              //array of encoding differences
var $images;             //array of used images
var $PageLinks;          //array of links in pages
var $links;              //array of internal links
var $FontFamily;         //current font family
var $FontStyle;          //current font style
var $underline;          //underlining flag
var $CurrentFont;        //current font info
var $FontSizePt;         //current font size in points
var $FontSize;           //current font size in user unit
var $DrawColor;          //commands for drawing color
var $FillColor;          //commands for filling color
var $TextColor;          //commands for text color
var $ColorFlag;          //indicates whether fill and text colors are different
var $AutoPageBreak;      //automatic page breaking
var $PageBreakTrigger;   //threshold used to trigger page breaks
var $InFooter;           //flag set when processing footer

// Added mPDF 1.3 as flag to prevent page triggering in footers containing table
var $InHTMLFooter;

var $processingFooter;   //flag set when processing footer - added for columns
var $processingHeader;   //flag set when processing header - added for columns
var $ZoomMode;           //zoom display mode
var $LayoutMode;         //layout display mode
var $title;              //title
var $subject;            //subject
var $author;             //author
var $keywords;           //keywords
var $creator;            //creator
// mPDF 2.2 - variable name changed to lowercase first letter
var $aliasNbPg;       //alias for total number of pages

// mPDF 2.0
// mPDF 2.2 - variable name changed to lowercase first letter
var $aliasNbPgGp;       //alias for total number of pages in page group

var $ispre=false;

// mPDF 2.1 used
var $outerblocktags = array('DIV','FORM','CENTER','DL');
var $innerblocktags = array('P','BLOCKQUOTE','ADDRESS','PRE','H1','H2','H3','H4','H5','H6','DT','DD');
// NOT Currently used
var $inlinetags = array('SPAN','TT','I','B','BIG','SMALL','EM','STRONG','DFN','CODE','SAMP','KBD','VAR','CITE','ABBR','ACRONYM','STRIKE','S','U','DEL','INS','Q','FONT','TTS','TTZ','TTA');
var $listtags = array('UL','OL','LI');
var $tabletags = array('TABLE','THEAD','TFOOT','TBODY','TFOOT','TR','TH','TD');
var $formtags = array('TEXTAREA','INPUT','SELECT');


//**********************************
//**********************************
//**********************************
//**********************************
//**********************************
//**********************************
//**********************************
//**********************************
//**********************************

function mPDF($codepage='win-1252',$format='A4',$default_font_size=0,$default_font='',$mgl=15,$mgr=15,$mgt=16,$mgb=16,$mgh=9,$mgf=9, $orientation='P') {

	$unit='mm';
	if (strlen($codepage)==0) { $codepage='win-1252'; }
	//Some checks
	$this->_dochecks();

	// mPDF 2.2 Set up Aliases
	$this->UnvalidatedText =& $this->watermarkText;
	$this->TopicIsUnvalidated =& $this->showWatermarkText;
	$this->AliasNbPg =& $this->aliasNbPg;
	$this->AliasNbPgGp =& $this->aliasNbPgGp;
	$this->BiDirectional =& $this->biDirectional;
	$this->Anchor2Bookmark =& $this->anchor2Bookmark;
	$this->KeepColumns =& $this->keepColumns;
	// mPDF 3.0
	$this->use_embeddedfonts_1252 =& $this->useOnlyCoreFonts;


	//Initialization of properties
	$this->page=0;
	$this->n=2;
	$this->buffer='';
	$this->objectbuffer = array();
	$this->pages=array();
	$this->OrientationChanges=array();
	$this->state=0;
	$this->fonts=array();
	$this->FontFiles=array();
	$this->diffs=array();
	$this->images=array();
	$this->links=array();
	$this->InFooter=false;
	$this->processingFooter=false;
	$this->processingHeader=false;
	$this->lasth=0;
	$this->FontFamily='';
	$this->FontStyle='';
	$this->FontSizePt=9;
	$this->underline=false;
	$this->DrawColor='0 G';
	$this->FillColor='0 g';
	$this->TextColor='0 g';
	$this->ColorFlag=false;
	$this->extgstates = array();

	// FORM ELEMENT SPACING
	$this->form_element_spacing['select']['outer']['h'] = 0.5;	// Horizontal spacing around SELECT
	$this->form_element_spacing['select']['outer']['v'] = 0.5;	// Vertical spacing around SELECT
	$this->form_element_spacing['select']['inner']['h'] = 0.7;	// Horizontal padding around SELECT
	$this->form_element_spacing['select']['inner']['v'] = 0.7;	// Vertical padding around SELECT
	$this->form_element_spacing['input']['outer']['h'] = 0.5;
	$this->form_element_spacing['input']['outer']['v'] = 0.5;
	$this->form_element_spacing['input']['inner']['h'] = 0.7;
	$this->form_element_spacing['input']['inner']['v'] = 0.7;
	$this->form_element_spacing['textarea']['outer']['h'] = 0.5;
	$this->form_element_spacing['textarea']['outer']['v'] = 0.5;
	$this->form_element_spacing['textarea']['inner']['h'] = 1;
	$this->form_element_spacing['textarea']['inner']['v'] = 0.5;
	$this->form_element_spacing['button']['outer']['h'] = 0.5;
	$this->form_element_spacing['button']['outer']['v'] = 0.5;
	$this->form_element_spacing['button']['inner']['h'] = 2;
	$this->form_element_spacing['button']['inner']['v'] = 1;


	//Scale factor
	$this->k=72/25.4;	// Will only use mm

	//Page format
	if(is_string($format))
	{
		// mPDF 2.2
		if ($format=='') { $format = 'A4'; }
		if(preg_match('/([0-9a-zA-Z]*)-L/i',$format,$m)) {	// e.g. A4-L = A$ landscape
			$format=$m[1]; 
			$orientation='L'; 	// Overrides orientation
		}
		switch (strtoupper($format)) {
			case '4A0': {$format = array(4767.87,6740.79); break;}
			case '2A0': {$format = array(3370.39,4767.87); break;}
			case 'A0': {$format = array(2383.94,3370.39); break;}
			case 'A1': {$format = array(1683.78,2383.94); break;}
			case 'A2': {$format = array(1190.55,1683.78); break;}
			case 'A3': {$format = array(841.89,1190.55); break;}
			case 'A4': default: {$format = array(595.28,841.89); break;}
			case 'A5': {$format = array(419.53,595.28); break;}
			case 'A6': {$format = array(297.64,419.53); break;}
			case 'A7': {$format = array(209.76,297.64); break;}
			case 'A8': {$format = array(147.40,209.76); break;}
			case 'A9': {$format = array(104.88,147.40); break;}
			case 'A10': {$format = array(73.70,104.88); break;}
			case 'B0': {$format = array(2834.65,4008.19); break;}
			case 'B1': {$format = array(2004.09,2834.65); break;}
			case 'B2': {$format = array(1417.32,2004.09); break;}
			case 'B3': {$format = array(1000.63,1417.32); break;}
			case 'B4': {$format = array(708.66,1000.63); break;}
			case 'B5': {$format = array(498.90,708.66); break;}
			case 'B6': {$format = array(354.33,498.90); break;}
			case 'B7': {$format = array(249.45,354.33); break;}
			case 'B8': {$format = array(175.75,249.45); break;}
			case 'B9': {$format = array(124.72,175.75); break;}
			case 'B10': {$format = array(87.87,124.72); break;}
			case 'C0': {$format = array(2599.37,3676.54); break;}
			case 'C1': {$format = array(1836.85,2599.37); break;}
			case 'C2': {$format = array(1298.27,1836.85); break;}
			case 'C3': {$format = array(918.43,1298.27); break;}
			case 'C4': {$format = array(649.13,918.43); break;}
			case 'C5': {$format = array(459.21,649.13); break;}
			case 'C6': {$format = array(323.15,459.21); break;}
			case 'C7': {$format = array(229.61,323.15); break;}
			case 'C8': {$format = array(161.57,229.61); break;}
			case 'C9': {$format = array(113.39,161.57); break;}
			case 'C10': {$format = array(79.37,113.39); break;}
			case 'RA0': {$format = array(2437.80,3458.27); break;}
			case 'RA1': {$format = array(1729.13,2437.80); break;}
			case 'RA2': {$format = array(1218.90,1729.13); break;}
			case 'RA3': {$format = array(864.57,1218.90); break;}
			case 'RA4': {$format = array(609.45,864.57); break;}
			case 'SRA0': {$format = array(2551.18,3628.35); break;}
			case 'SRA1': {$format = array(1814.17,2551.18); break;}
			case 'SRA2': {$format = array(1275.59,1814.17); break;}
			case 'SRA3': {$format = array(907.09,1275.59); break;}
			case 'SRA4': {$format = array(637.80,907.09); break;}
			case 'LETTER': {$format = array(612.00,792.00); break;}
			case 'LEGAL': {$format = array(612.00,1008.00); break;}
			case 'EXECUTIVE': {$format = array(521.86,756.00); break;}
			case 'FOLIO': {$format = array(612.00,936.00); break;}
			case 'B': {$format=array(362.83,561.26 );	 break;}		//	'B' format paperback size 128x198mm
			case 'A': {$format=array(314.65,504.57 );	 break;}		//	'A' format paperback size 111x178mm
			case 'DEMY': {$format=array(382.68,612.28 );  break;}		//	'Demy' format paperback size 135x216mm
			case 'ROYAL': {$format=array(433.70,663.30 );  break;}	//	'Royal' format paperback size 153x234mm
			default: $this->Error('Unknown page format: '.$format);
		}
		$this->fwPt=$format[0];
		$this->fhPt=$format[1];
	}
	else
	{
		$this->fwPt=$format[0]*$this->k;
		$this->fhPt=$format[1]*$this->k;
	}
	$this->fw=$this->fwPt/$this->k;
	$this->fh=$this->fhPt/$this->k;
	//Page orientation
	$orientation=strtolower($orientation);
	if($orientation=='p' or $orientation=='portrait')
	{
		$this->DefOrientation='P';
		$this->wPt=$this->fwPt;
		$this->hPt=$this->fhPt;
	}
	elseif($orientation=='l' or $orientation=='landscape')
	{
		$this->DefOrientation='L';
		$this->wPt=$this->fhPt;
		$this->hPt=$this->fwPt;
	}
	else $this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation=$this->DefOrientation;

	$this->w=$this->wPt/$this->k;
	$this->h=$this->hPt/$this->k;

	//PAGE MARGINS
	//mm=2.835/$this->k;

	$this->margin_header=$mgh;
	$this->margin_footer=$mgf;

	$bmargin=$mgb;

	$this->DeflMargin = $mgl;
	$this->DefrMargin = $mgr;

	// v1.4 Save orginal settings in case of changed orientation
	$this->orig_tMargin = $mgt;
	$this->orig_bMargin = $bmargin;
	$this->orig_lMargin = $this->DeflMargin;
	$this->orig_rMargin = $this->DefrMargin;
	$this->orig_hMargin = $this->margin_header;
	$this->orig_fMargin = $this->margin_footer;

	$this->SetMargins($this->DeflMargin,$this->DefrMargin,$mgt);	// sets l r t margin
	//Automatic page break
	$this->SetAutoPageBreak(true,$bmargin);	// sets $this->bMargin & PageBreakTrigger

	$this->pgwidth = $this->w - $this->lMargin - $this->rMargin ;

	//Interior cell margin (1 mm) ? not used
	$this->cMarginL = 1;
	$this->cMarginR = 1;
	//Line width (0.2 mm)
	$this->LineWidth=.567/$this->k;

	//To make the function Footer() work - replaces {nb} with page number
	$this->AliasNbPages();
	// mPDF 2.0
	$this->AliasNbPageGroups();


	//Enable all tags as default
	$this->DisableTags();
	//Full width display mode
	$this->SetDisplayMode(100);	// fullwidth?		'fullpage'
	//Compression
	$this->SetCompression(true);
	//Set default display preferences
	$this->DisplayPreferences('');

	// mPDF 2.0	changed from require_once so allows multiple PDF files to be generated
	require(_MPDF_PATH.'mpdf_config.php');	// font data

	//Standard fonts
	$this->CoreFonts=array('courier'=>'Courier','courierB'=>'Courier-Bold','courierI'=>'Courier-Oblique','courierBI'=>'Courier-BoldOblique',
		'helvetica'=>'Helvetica','helveticaB'=>'Helvetica-Bold','helveticaI'=>'Helvetica-Oblique','helveticaBI'=>'Helvetica-BoldOblique',
		'times'=>'Times-Roman','timesB'=>'Times-Bold','timesI'=>'Times-Italic','timesBI'=>'Times-BoldItalic',
		'symbol'=>'Symbol','zapfdingbats'=>'ZapfDingbats');
	$this->fontlist=array("times","courier","helvetica","symbol","zapfdingbats");

	switch(strtolower($codepage)){
	case 'utf-8': $codepage='UTF-8';break;
	case 'big5': case 'big-5': $codepage='BIG5';break;
	case 'gbk': case 'cp936': $codepage='GBK';break;
	case 'uhc': case 'cp949': $codepage='UHC';break;
	case 'shift_jis': case 'shift-jis': case 'sjis': $codepage='SHIFT_JIS';break;
	case 'win-1251': case 'windows-1251': case 'cp1251': $codepage='win-1251';break;
	case 'win-1252': case 'windows-1252': case 'cp1252': $codepage='win-1252';break;
	case 'iso-8859-2': $codepage='iso-8859-2';break;
	case 'iso-8859-4': $codepage='iso-8859-4';break;
	case 'iso-8859-7': $codepage='iso-8859-7';break;
	case 'iso-8859-9': $codepage='iso-8859-9';break; 
	}

	// mPDF 2.3
	$this->default_available_fonts = $this->available_unifonts;

	// Autodetect IF codepage is a language_country string (en-GB or en_GB or en)
	if ((strlen($codepage) == 5 && $codepage != 'UTF-8') || strlen($codepage) == 2) {
		// in HTMLToolkit
		list ($codepage,$mpdf_pdf_unifonts,$mpdf_directionality,$mpdf_jSpacing) = GetCodepage($codepage);
		$this->jSpacing = $mpdf_jSpacing;
		if (($codepage != 'BIG5') && ($codepage != 'GBK') && ($codepage != 'UHC') && ($codepage != 'SHIFT_JIS')) { 
			if ($mpdf_pdf_unifonts) { 
				$this->RestrictUnicodeFonts($mpdf_pdf_unifonts); 
				$this->default_available_fonts = $mpdf_pdf_unifonts;
			}
		}
		$this->SetDirectionality($mpdf_directionality);
		$this->currentLang = $codepage;
		$this->default_lang = $codepage;
		$this->default_jSpacing = $mpdf_jSpacing;
		$this->default_dir = $mpdf_directionality;
	}


	$this->codepage =  $codepage;
	if ($codepage == 'UTF-8') { $this->is_MB = true; }
	if (($codepage == 'BIG5') || ($codepage == 'GBK') || ($codepage == 'UHC') || ($codepage == 'SHIFT_JIS')) { 
		$this->isCJK = true;
		require(_MPDF_PATH . 'CJKdata.php');
		// FONTS
		if ($codepage == 'BIG5') { $this->AddCJKFont('big5'); $default_font = 'big5';}
		else if ($codepage == 'GBK') { $this->AddCJKFont('gb'); $default_font = 'gb'; }
		else if ($codepage == 'SHIFT_JIS') { $this->AddCJKFont('sjis'); $default_font = 'sjis'; }
		else if ($codepage == 'UHC') { $this->AddCJKFont('uhc'); $default_font = 'uhc';}

		$this->is_MB = true; 
		$this->use_CJK_only = true;

	}

	if ($this->is_MB) { define('FPDF_FONTPATH',_MPDF_PATH.'unifont/'); }
	else { define('FPDF_FONTPATH',_MPDF_PATH.'font/'); }

	// mPDF 2.0
	//if ($this->useDefaultCSS2) { $this->defaultCSS = array_merge_recursive_unique($this->defaultCSS,$this->defaultCSS2); }
	// mPDF 2.2
	if (file_exists(_MPDF_PATH.'mpdf.css')) {
		$css = file_get_contents(_MPDF_PATH.'mpdf.css');
		$css2 = $this->ReadDefaultCSS($css);
		$this->defaultCSS = array_merge_recursive_unique($this->defaultCSS,$css2); 
	}

	if ($default_font=='') { 
	  if ($codepage == 'win-1252') { 
		if (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']),$this->mono_fonts)) { $default_font = 'courier' ; }
		else if (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']),$this->sans_fonts)) { $default_font = 'helvetica' ; }
		else { $default_font = 'times' ; }
	  }
	  else { $default_font = $this->defaultCSS['BODY']['FONT-FAMILY'] ; }
	}
	if (!$default_font_size) { 
		$mmsize = ConvertSize($this->defaultCSS['BODY']['FONT-SIZE']);
		$default_font_size = $mmsize*(72/25.4);
	}

	if ($default_font) { $this->SetDefaultFont($default_font); }
	if ($default_font_size) { $this->SetDefaultFontSize($default_font_size); }

	$this->setMBencoding($this->codepage);	// sets $this->mb_encoding
	@mb_regex_encoding('UTF-8'); 	// Edit mPDF 1.1 Required for mb_split

	$this->setHiEntitySubstitutions(GetHiEntitySubstitutions());

	$this->SetLineHeight();	// lineheight is in mm

	$this->SetFillColor(255);
	$this->HREF='';
	$this->titulo='';
	$this->oldy=-1;
	$this->B=0;
	$this->U=0;
	$this->I=0;

	$this->listlvl=0;
	$this->listnum=0; 
	$this->listtype='';
	$this->listoccur=array();
	$this->listlist=array();
	$this->listitem=array();

	$this->tdbegin=false; 
	$this->table=array(); 
	$this->cell=array();  
	$this->col=-1; 
	$this->row=-1; 
	$this->cellBorderBuffer = array();

	$this->divbegin=false;
	$this->divalign=$this->defaultAlign;
	$this->divwidth=0; 
	$this->divheight=0; 
	$this->spanbgcolor=false;
	$this->divrevert=false;

	$this->issetfont=false;
	$this->issetcolor=false;

	$this->blockjustfinished=false;
	$this->listjustfinished=false;
	$this->ignorefollowingspaces = true; //in order to eliminate exceeding left-side spaces
	$this->toupper=false;
	$this->tolower=false;
	$this->dash_on=false;
	$this->dotted_on=false;
	$this->SUP=false;
	$this->SUB=false;
	$this->strike=false;

	$this->currentfontfamily='';
	$this->currentfontsize='';
	$this->currentfontstyle='';
	$this->colorarray=array();
	$this->spanbgcolorarray=array();
	$this->textbuffer=array();
	$this->CSS=array();
	$this->internallink=array();
	$this->basepath = "";

	// Edited mPDF 2.0
	$this->setBasePath('');

	$this->outlineparam = array();
	$this->outline_on = false;

	$this->specialcontent = '';
	$this->selectoption = array();

	$this->shownoimg=true;
	$this->usetableheader=false;
	$this->usecss=true;
	$this->usepre=true;

	for($i=0;$i<256;$i++) {
		$this->chrs[$i] = chr($i);
		$this->ords[chr($i)] = $i;

	}
}


function RestrictUnicodeFonts($res) {
	// mPDF 2.3
	$this->available_unifonts = $this->default_available_fonts;
	// $res = array of (Unicode) fonts to restrict to: e.g. norasi|norasiB - language specific
	if (count($res)) {	// Leave full list of available fonts if passed blank array
	   foreach($this->available_unifonts AS $k => $f) {
		if (!in_array($f,$res)) { 
			unset($this->available_unifonts[$k]);
		}
	   }
	}
	// mPDF 2.3 No longer dies if no fonts - uses first of default.
	if (count($this->available_unifonts) == 0) { $this->available_unifonts[] = $this->default_available_fonts[0]; }
	$this->available_unifonts = array_values($this->available_unifonts);
}


function setMBencoding($enc) {
	// Edited mPDF1.1 - only call mb_internal_encoding if need to change
	$curr = $this->mb_encoding;
	// Sets encoding string for use in mb_string functions
	if ($enc == 'win-1252') { $this->mb_encoding = 'windows-1252'; }
	else if ($enc == 'win-1251') { $this->mb_encoding = 'windows-1251'; }
	else if ($enc == 'UTF-8') { $this->mb_encoding = 'UTF-8'; }
	else if ($enc == 'BIG5') { $this->mb_encoding = 'UTF-8'; }
	else if ($enc == 'GBK') { $this->mb_encoding = 'UTF-8'; }	// cp936
	else if ($enc == 'SHIFT_JIS') { $this->mb_encoding = 'UTF-8'; }
	else if ($enc == 'UHC') { $this->mb_encoding = 'UTF-8'; }	// cp949
	else { $this->mb_encoding = $enc; }	// works for iso-8859-n
	if ($this->mb_encoding && $curr != $this->mb_encoding) { 
		mb_internal_encoding($this->mb_encoding); 
	}
}

function getMBencoding() {
	return $this->mb_encoding;
}



function SetMargins($left,$right,$top)
{
	//Set left, top and right margins
	$this->lMargin=$left;
	$this->rMargin=$right;
	$this->tMargin=$top;
}

function ResetMargins()
{
	//ReSet left, top margins
	// Added mPDF v1.4
	// mPDF 2.3
	if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation=='P' && $this->CurOrientation=='L') {
	    if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
		$this->tMargin=$this->orig_rMargin;
		$this->bMargin=$this->orig_lMargin;
	    }
	    else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS
		$this->tMargin=$this->orig_lMargin;
		$this->bMargin=$this->orig_rMargin;
	    }
	   $this->lMargin=$this->DeflMargin;
	   $this->rMargin=$this->DefrMargin;
	   $this->MarginCorrection = 0;
	   $this->PageBreakTrigger=$this->h-$this->bMargin;
	}
	else  if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
		$this->lMargin=$this->DefrMargin;
		$this->rMargin=$this->DeflMargin;
		$this->MarginCorrection = $this->DefrMargin-$this->DeflMargin;

	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS
		$this->lMargin=$this->DeflMargin;
		$this->rMargin=$this->DefrMargin;
		if ($this->useOddEven) { $this->MarginCorrection = $this->DeflMargin-$this->DefrMargin; }
	}
	$this->x=$this->lMargin;

}

function SetLeftMargin($margin)
{
	//Set left margin
	$this->lMargin=$margin;
	if($this->page>0 and $this->x<$margin) $this->x=$margin;
}

function SetTopMargin($margin)
{
	//Set top margin
	$this->tMargin=$margin;
}

function SetRightMargin($margin)
{
	//Set right margin
	$this->rMargin=$margin;
}

function SetAutoPageBreak($auto,$margin=0)
{
	//Set auto page break mode and triggering margin
	$this->AutoPageBreak=$auto;
	$this->bMargin=$margin;
	$this->PageBreakTrigger=$this->h-$margin;
}

function SetDisplayMode($zoom,$layout='continuous')
{
	//Set display mode in viewer
	if($zoom=='fullpage' or $zoom=='fullwidth' or $zoom=='real' or $zoom=='default' or !is_string($zoom))
		$this->ZoomMode=$zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' or $layout=='continuous' or $layout=='two' or $layout=='default')
		$this->LayoutMode=$layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress)
{
	//Set page compression
	if(function_exists('gzcompress'))	$this->compress=$compress;
	else $this->compress=false;
}

function SetTitle($title)
{
	//Title of document // Arrives as UTF-8
	$this->title = $title;
	// mPDF 2.3
	// mPDF 3.0
//	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->title, false); }
}

function SetSubject($subject)
{
	//Subject of document
	$this->subject= $subject;
	// mPDF 2.3
	// mPDF 3.0
//	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->subject, false); }
}

function SetAuthor($author)
{
	//Author of document
	$this->author= $author;
	// mPDF 2.3
	// mPDF 3.0
//	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->author, false); }
}

function SetKeywords($keywords)
{
	//Keywords of document
	$this->keywords= $keywords;
	// mPDF 2.3
	// mPDF 3.0
//	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->keywords, false); }
}

function SetCreator($creator)
{
	//Creator of document
	$this->creator= $creator;
	// mPDF 2.3
	// mPDF 3.0
//	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->creator, false); }
}


// mPDF 2.2 - function name changed to capitalise first letter
function SetAnchor2Bookmark($x) {
	// mPDF 2.2 - variable name changed to lowercase first letter
	$this->anchor2Bookmark = $x;
}

function AliasNbPages($alias='{nb}')
{
	//Define an alias for total number of pages
	// mPDF 2.2 - variable name changed to lowercase first letter
	$this->aliasNbPg=$alias;
}

// mPDF 2.0
function AliasNbPageGroups($alias='{nbpg}')
{
	//Define an alias for total number of pages in a group
	// mPDF 2.2 - variable name changed to lowercase first letter
	$this->aliasNbPgGp=$alias;
}

function SetAlpha($alpha, $bm='Normal') {
// alpha: real value from 0 (transparent) to 1 (opaque)
// bm:    blend mode, one of the following:
//          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
//          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
// set alpha for stroking (CA) and non-stroking (ca) operations
	$gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
	$this->SetExtGState($gs);
}

// Edited mPDF 2.0
function AddExtGState($parms) {
	$n = count($this->extgstates);
	// check if graphics state already exists
	for ($i=1; $i<=$n; $i++) {
		if ($this->extgstates[$i]['parms']['ca']==$parms['ca'] && $this->extgstates[$i]['parms']['CA']==$parms['CA'] && $this->extgstates[$i]['parms']['BM']==$parms['BM']) {
			return $i;
		}
	}
	$n++;
	$this->extgstates[$n]['parms'] = $parms;
	return $n;
}

function SetExtGState($gs) {
	$this->_out(sprintf('/GS%d gs', $gs));
}


function Error($msg)
{
	//Fatal error
	header('Content-Type: text/html; charset=utf-8');
	die('<B>mPDF error: </B>'.$msg);
}

function Open()
{
	//Begin document
	if($this->state==0)	$this->_begindoc();
}

function Close()
{
	//Terminate document
	if($this->state==3)	return;
	if($this->page==0) $this->AddPage($this->CurOrientation);
	if (count($this->cellBorderBuffer)) { $this->printcellbuffer(); }
	if (count($this->tablebuffer)) { $this->printtablebuffer(); }
	if (count($this->columnbuffer)) { $this->ColActive = 0; $this->printcolumnbuffer(); }
	// Edited mPDF 1.1 keeping block together on one page
	if (count($this->divbuffer)) { $this->printdivbuffer(); }

	// mPDF 3.0 - BODY Backgrounds
	$s = '';
	$bby = $this->h;
	$bbw = $this->w;
	$bbh = $this->h;
	if ($this->bodyBackgroundColor) {
		$s .= sprintf('%.3f %.3f %.3f rg',$this->bodyBackgroundColor['R']/255,$this->bodyBackgroundColor['G']/255,$this->bodyBackgroundColor['B']/255)."\n";
		$s .= sprintf('%.3f %.3f %.3f %.3f re f',0,$bby*$this->k,$bbw*$this->k,-$bbh*$this->k)."\n";
	}
	// mPDF 3.0 Gradients
	if ($this->bodyBackgroundGradient) { 
		$g = $this->parseBackgroundGradient($this->bodyBackgroundGradient);
		if ($g) {
			$this->pageBackgrounds[0][] = array('gradient'=>true, 'x'=>0, 'y'=>0, 'w'=>$this->w, 'h'=>$this->h, 'gradtype'=>$g['type'], 'col'=>$g['col'], 'col2'=>$g['col2'], 'coords'=>$g['coords'], 'extend'=>$g['extend']);
		}
	}
	if ($this->bodyBackgroundImage) {
		  if ($this->bodyBackgroundImage['image_id']) {	// Background pattern
			$n = count($this->patterns)+1;
			$this->patterns[$n] = array('x'=>$bbx, 'y'=>$bby, 'w'=>$bbw, 'h'=>$bbh, 'image_id'=>$this->bodyBackgroundImage['image_id'], 'orig_w'=>$this->bodyBackgroundImage['orig_w'], 'orig_h'=>$this->bodyBackgroundImage['orig_h'], 'x_pos'=>$this->bodyBackgroundImage['x_pos'], 'y_pos'=>$this->bodyBackgroundImage['y_pos'], 'x_repeat'=>$this->bodyBackgroundImage['x_repeat'], 'y_repeat'=>$this->bodyBackgroundImage['y_repeat']);
			$s .= sprintf('/Pattern cs /P%d scn %.3f %.3f %.3f %.3f re f', $n, 0,$bby*$this->k,$bbw*$this->k,-$bbh*$this->k) ."\n";
		  }
	}


	$s .= $this->PrintPageBackgrounds();
	$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', "\n".$s."\n".'\\1', $this->pages[$this->page]);
	$this->pageBackgrounds = array();


	if (!$this->TOCmark) { //Page footer
		$this->InFooter=true;
		$this->Footer();
		$this->InFooter=false;
	}
	// mPDF 2.3
	if ($this->TOCmark || count($this->m_TOC)) { $this->insertTOC(); }

	//Close page
	$this->_endpage();

	//Close document
	$this->_enddoc();
}


// Added mPDF 3.0
function PrintPageBackgrounds() {
	ksort($this->pageBackgrounds);
	foreach($this->pageBackgrounds AS $bl=>$pbs) {
		foreach ($pbs AS $pb) {
		  if (!$pb['image_id'] && !$pb['gradient']) {	// Background colour
			if ($pb['clippath']) { $s .= $pb['clippath']."\n"; }
			$s .= sprintf('%.3f %.3f %.3f rg',$pb['col']['R']/255,$pb['col']['G']/255,$pb['col']['B']/255)."\n";
			$s .= sprintf('%.3f %.3f %.3f %.3f re f',$pb['x']*$this->k,($this->h-$pb['y'])*$this->k,$pb['w']*$this->k,-$pb['h']*$this->k)."\n";
			if ($pb['clippath']) { $s .= 'Q'."\n"; }
		  }
		}
		// mPDF 3.0 Background Gradients
		foreach ($pbs AS $pb) {
	 	 if ($pb['gradient']) {
			if ($pb['clippath']) { $s .= $pb['clippath']."\n"; }
			$s .= $this->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['col'], $pb['col2'], $pb['coords'], $pb['extend'], true);
			if ($pb['clippath']) { $s .= 'Q'."\n"; }
		  }
		}
		foreach ($pbs AS $pb) {
		  if ($pb['image_id']) {	// Background pattern
			$n = count($this->patterns)+1;
			$this->patterns[$n] = array('x'=>$pb['x'], 'y'=>$pb['y'], 'w'=>$pb['w'], 'h'=>$pb['h'], 'image_id'=>$pb['image_id'], 'orig_w'=>$pb['orig_w'], 'orig_h'=>$pb['orig_h'], 'x_pos'=>$pb['x_pos'], 'y_pos'=>$pb['y_pos'], 'x_repeat'=>$pb['x_repeat'], 'y_repeat'=>$pb['y_repeat']);
			$x = $pb['x']*$this->k;
			$y = ($this->h - $pb['y'])*$this->k;
			$w = $pb['w']*$this->k;
			$h = -$pb['h']*$this->k;
			if ($pb['clippath']) { $s .= $pb['clippath']."\n"; }
			$s .= sprintf('/Pattern cs /P%d scn %.3f %.3f %.3f %.3f re f', $n, $x, $y, $w, $h) ."\n";
			if ($pb['clippath']) { $s .= 'Q'."\n"; }
		  }
		}
	}
	return $s;
}

// Depracated - can use AddPage for all
function AddPages($orientation='',$condition='', $resetpagenum='', $pagenumstyle='', $suppress='',$mgl='',$mgr='',$mgt='',$mgb='',$mgh='',$mgf='',$ohname='',$ehname='',$ofname='',$efname='',$ohvalue=0,$ehvalue=0,$ofvalue=0,$efvalue=0)
{
	$this->AddPage($orientation,$condition,$resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue);
}


function AddPage($orientation='',$condition='', $resetpagenum='', $pagenumstyle='', $suppress='',$mgl='',$mgr='',$mgt='',$mgb='',$mgh='',$mgf='',$ohname='',$ehname='',$ofname='',$efname='',$ohvalue=0,$ehvalue=0,$ofvalue=0,$efvalue=0)
{

	// Added mPDF 3.0 Float DIV
	// Cannot do with columns on, or if any change in page orientation/margins etc.
	// If next page already exists - i.e background /headers and footers already written
	if ($this->state > 0 && $this->page < count($this->pages)) {
		$bak_cml = $this->cMarginL;
		$bak_cmr = $this->cMarginR; 
		$bak_dw = $this->divwidth;
		// Paint Div Border if necessary
   		if ($this->blklvl > 0) {
			$save_tr = $this->table_rotate;
			$this->table_rotate = 0;
			if ($this->y == $this->blk[$this->blklvl]['y0']) {  $this->blk[$this->blklvl]['startpage']++; }
			if (($this->y > $this->blk[$this->blklvl]['y0']) || $this->flowingBlockAttr['is_table'] ) { $toplvl = $this->blklvl; }
			else { $toplvl = $this->blklvl-1; }
			$sy = $this->y;
			for ($bl=1;$bl<=$toplvl;$bl++) {
				$this->PaintDivBB('pagebottom',0,$bl);
			}
			$this->y = $sy;
			$this->table_rotate = $save_tr;
		}
		$s = $this->PrintPageBackgrounds();

		// Writes after the marker so not overwritten later by page background etc.
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
		$this->pageBackgrounds = array();
	
		$family=$this->FontFamily;
		$style=$this->FontStyle.($this->underline ? 'U' : '');
		$size=$this->FontSizePt;
		$lw=$this->LineWidth;
		$dc=$this->DrawColor;
		$fc=$this->FillColor;
		$tc=$this->TextColor;
		$cf=$this->ColorFlag;
		if (count($this->floatbuffer)) {
			$this->objectbuffer = $this->floatbuffer;
			$this->printobjectbuffer(false);
			$this->objectbuffer = array();
			$this->floatbuffer = array();
			$this->floatmargins = array();
		}
	
		//Move to next page
		$this->page++;
	
		$this->ResetMargins();
		$this->SetAutoPageBreak(true,$this->bMargin);
		$this->x=$this->lMargin;
		$this->y=$this->tMargin;
		$this->FontFamily='';
		$this->_out('2 J');
		$this->LineWidth=$lw;
		$this->_out(sprintf('%.3f w',$lw*$this->k));
		if($family)	$this->SetFont($family,$style,$size,true,true);
		$this->DrawColor=$dc;
		if($dc!='0 G') $this->_out($dc);
		$this->FillColor=$fc;
		if($fc!='0 g') $this->_out($fc);
		$this->TextColor=$tc;
		$this->ColorFlag=$cf;
		for($bl=1;$bl<=$this->blklvl;$bl++) {
			$this->blk[$bl]['y0'] = $this->y;
			// Don't correct more than once for background DIV containing a Float
			if (!$this->blk[$bl]['marginCorrected'][$this->page]) { $this->blk[$bl]['x0'] += $this->MarginCorrection; }
			$this->blk[$bl]['marginCorrected'][$this->page] = true; 
		}
		$this->cMarginL = $bak_cml;
		$this->cMarginR = $bak_cmr;
		$this->divwidth = $bak_dw;
		return '';
	}


	//Start a new page
	if($this->state==0) $this->Open();

	// mPDF 3.0 - Moved here from WriteFlowingBlock, FinishFlowingBlock and printbuffer
	$bak_cml = $this->cMarginL;
	$bak_cmr = $this->cMarginR; 
	$bak_dw = $this->divwidth;

	// mPDF 2.2
	$orientation = substr(strtoupper($orientation),0,1);
	$condition = strtoupper($condition);

	// mPDF 2.2
	if ($condition == 'NEXT-EVEN') {	// always adds at least one new page to create an Even page
	   if (!$this->useOddEven) { $condition = ''; }
	   else { 
		// mPDF 2.0 a fix to delay changing @page margins etc until next page
		if ($this->page_box['changed']) { $this->page_box['changed'] = false; $pbch = true; }
		$this->AddPage($this->CurOrientation,'O'); 
		if ($pbch ) { $this->page_box['changed'] = true; }
		$condition = ''; 
	   }
	}
	if ($condition == 'NEXT-ODD') {	// always adds at least one new page to create an Odd page
	   if (!$this->useOddEven) { $condition = ''; }
	   else { 
		// mPDF 2.0 a fix to delay changing @page margins etc until next page
		if ($this->page_box['changed']) { $this->page_box['changed'] = false; $pbch = true; }
		$this->AddPage($this->CurOrientation,'E'); 
		if ($pbch ) { $this->page_box['changed'] = true; }
		$condition = ''; 
	   }
	}


	if ($condition == 'E') {	// only adds new page if needed to create an Even page
	   if (!$this->useOddEven || ($this->page)%2==0) { return false; }
	}
	if ($condition == 'O') {	// only adds new page if needed to create an Odd page
	   if (!$this->useOddEven || ($this->page)%2==1) { return false; }
	}

	if ($resetpagenum || $pagenumstyle || $suppress) {
		$this->PageNumSubstitutions[] = array('from'=>($this->page+1), 'reset'=> $resetpagenum, 'type'=>$pagenumstyle, 'suppress'=>$suppress);
	}



	// Paint Div Border if necessary
   	//PAINTS BACKGROUND COLOUR OR BORDERS for DIV - DISABLED FOR COLUMNS (cf. AcceptPageBreak) AT PRESENT in ->PaintDivBB
   	if (!$this->ColActive && $this->blklvl > 0) {
		$save_tr = $this->table_rotate;
		$this->table_rotate = 0;
		if ($this->y == $this->blk[$this->blklvl]['y0']) {  $this->blk[$this->blklvl]['startpage']++; }
		// Edited mPDF 2.0 - If(this->y > $this->blk[$this->blklvl]['y0']) happens when new block started and triggers addpage
		if (($this->y > $this->blk[$this->blklvl]['y0']) || $this->flowingBlockAttr['is_table'] ) { $toplvl = $this->blklvl; }
		else { $toplvl = $this->blklvl-1; }
		$sy = $this->y;
		for ($bl=1;$bl<=$toplvl;$bl++) {
			$this->PaintDivBB('pagebottom',0,$bl);
		}
		$this->y = $sy;
		// RESET block y0 and x0 - see below
		$this->table_rotate = $save_tr;
	}

	// mPDF 3.0 - BODY Backgrounds
	if ($this->page > 0) {
		$s = '';
		$bby = $this->h;
		$bbw = $this->w;
		$bbh = $this->h;
		if ($this->bodyBackgroundColor) {
			$s .= sprintf('%.3f %.3f %.3f rg',$this->bodyBackgroundColor['R']/255,$this->bodyBackgroundColor['G']/255,$this->bodyBackgroundColor['B']/255)."\n";
			$s .= sprintf('%.3f %.3f %.3f %.3f re f',0,$bby*$this->k,$bbw*$this->k,-$bbh*$this->k)."\n";
		}
		// mPDF 3.0 Gradients
		if ($this->bodyBackgroundGradient) { 
			$g = $this->parseBackgroundGradient($this->bodyBackgroundGradient);
			if ($g) {
				$this->pageBackgrounds[0][] = array('gradient'=>true, 'x'=>0, 'y'=>0, 'w'=>$this->w, 'h'=>$this->h, 'gradtype'=>$g['type'], 'col'=>$g['col'], 'col2'=>$g['col2'], 'coords'=>$g['coords'], 'extend'=>$g['extend']);
			}
		}
		if ($this->bodyBackgroundImage) {
			  if ($this->bodyBackgroundImage['image_id']) {	// Background pattern
				$n = count($this->patterns)+1;
				$this->patterns[$n] = array('x'=>$bbx, 'y'=>$bby, 'w'=>$bbw, 'h'=>$bbh, 'image_id'=>$this->bodyBackgroundImage['image_id'], 'orig_w'=>$this->bodyBackgroundImage['orig_w'], 'orig_h'=>$this->bodyBackgroundImage['orig_h'], 'x_pos'=>$this->bodyBackgroundImage['x_pos'], 'y_pos'=>$this->bodyBackgroundImage['y_pos'], 'x_repeat'=>$this->bodyBackgroundImage['x_repeat'], 'y_repeat'=>$this->bodyBackgroundImage['y_repeat']);
				$s .= sprintf('/Pattern cs /P%d scn %.3f %.3f %.3f %.3f re f', $n, 0,$bby*$this->k,$bbw*$this->k,-$bbh*$this->k) ."\n";
			  }
		}

		$s .= $this->PrintPageBackgrounds();
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', "\n".$s."\n".'\\1', $this->pages[$this->page]);
		$this->pageBackgrounds = array();
	}

	// mPDF 3.0
	$save_cols = false;
	if ($this->ColActive) {
		$save_cols = true;
		$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
		$this->SetColumns(0);
	}

	$family=$this->FontFamily;
	$style=$this->FontStyle.($this->underline ? 'U' : '');
	$size=$this->FontSizePt;
	$this->ColumnAdjust = true;	// enables column height adjustment for the page
	$lw=$this->LineWidth;
	$dc=$this->DrawColor;
	$fc=$this->FillColor;
	$tc=$this->TextColor;
	$cf=$this->ColorFlag;
	if($this->page>0)
	{
		//Page footer
		$this->InFooter=true;

		// mPDF 3.0
		$this->Reset();
		$this->pageoutput[$this->page] = array();

		$this->Footer();
		//Close page
		$this->_endpage();
	}


	// mPDF 2.0 Paged media (page-box)
	if ($this->page_box['changed']) {
		list($orientation,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$mgtfp,$mgbfp,$ohname,$ehname,$ofname,$efname,$bg) = $this->SetPagedMediaCSS();
		if ($ohname) { $ohvalue = 1; } else { $ohvalue = -1; }
		if ($ehname) { $ehvalue = 1; } else { $ehvalue = -1; }
		if ($ofname) { $ofvalue = 1; } else { $ofvalue = -1; }
		if ($efname) { $efvalue = 1; } else { $efvalue = -1; }
  		// PAGED MEDIA - CROP / CROSS MARKS from @PAGE
  		if (strtoupper($this->page_box['CSS']['MARKS']) == 'CROP') {
			$this->show_marks = 'CROP';
  		}
  		else if (strtoupper($this->page_box['CSS']['MARKS']) == 'CROSS') {
			$this->show_marks = 'CROSS';
 		}
		else { $this->show_marks = ''; }

		// mPDF 3.0 - Background color
		if ($bg['BACKGROUND-COLOR']) {
			$cor = ConvertColor($bg['BACKGROUND-COLOR']);
			if ($cor) { 
				$this->bodyBackgroundColor = $cor; 
				$this->bodyBackgroundImage = false; 
				$this->bodyBackgroundGradient = false; 
			}
		}
		// mPDF 3.0
		if ($bg['BACKGROUND-GRADIENT']) { 
			$this->bodyBackgroundGradient = $bg['BACKGROUND-GRADIENT'];
		}
		// mPDF 3.0 - Tiling Patterns
		if ($bg['BACKGROUND-IMAGE']) { 
			$file = $bg['BACKGROUND-IMAGE'];
			$sizesarray = $this->Image($file,0,0,0,0,'','',false);
			if (isset($sizesarray['IMAGE_ID'])) {
				$image_id = $sizesarray['IMAGE_ID'];
				$orig_w = $sizesarray['WIDTH']*$this->k;		// in user units i.e. mm
 				$orig_h = $sizesarray['HEIGHT']*$this->k;		// (using $this->img_dpi)
				$x_repeat = true;
				$y_repeat = true;
				if ($bg['BACKGROUND-REPEAT']=='no-repeat' || $bg['BACKGROUND-REPEAT']=='repeat-x') { $y_repeat = false; }	
				if ($bg['BACKGROUND-REPEAT']=='no-repeat' || $bg['BACKGROUND-REPEAT']=='repeat-y') { $x_repeat = false; }
				$x_pos = 0;
				$y_pos = 0;
				if ($bg['BACKGROUND-POSITION']) { 
					$ppos = preg_split('/\s+/',$bg['BACKGROUND-POSITION']);
					$x_pos = $ppos[0];
					$y_pos = $ppos[1];
					if (!stristr($x_pos ,'%') ) { $x_pos = ConvertSize($x_pos ,$this->pgwidth,$this->FontSize); }
					if (!stristr($y_pos ,'%') ) { $y_pos = ConvertSize($y_pos ,$this->pgwidth,$this->FontSize); }
				}
				$this->bodyBackgroundImage = array('image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat);
				$this->bodyBackgroundGradient = false; 
			}
		}


		$this->page_box['start_page'] = $this->page+1;
	}
	$this->page_box['changed'] = false;


	//Start new page
	$this->_beginpage($orientation,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue,$mgtfp,$mgbfp);
	//Set line cap style to square
	$this->_out('2 J');
	//Set line width
	$this->LineWidth=$lw;
	$this->_out(sprintf('%.3f w',$lw*$this->k));
	//Set font
	if($family)	$this->SetFont($family,$style,$size,true,true);	// forces write
	//Set colors
	$this->DrawColor=$dc;
	if($dc!='0 G') $this->_out($dc);
	$this->FillColor=$fc;
	if($fc!='0 g') $this->_out($fc);
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;

	// mPDF 3.0 - Tiling Patterns
	$this->_out('___BACKGROUND___PATTERNS'.date('jY'));
	$this->pageBackgrounds = array();

	//Page header
	$this->Header();

	//Restore line width
	if($this->LineWidth!=$lw)
	{
		$this->LineWidth=$lw;
		$this->_out(sprintf('%.3f w',$lw*$this->k));
	}
	//Restore font
	if($family)	$this->SetFont($family,$style,$size,true,true);	// forces write
	//Restore colors
	if($this->DrawColor!=$dc)
	{
		$this->DrawColor=$dc;
		$this->_out($dc);
	}
	if($this->FillColor!=$fc)
	{
		$this->FillColor=$fc;
		$this->_out($fc);
	}
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
 	$this->InFooter=false;

	// mPDF 3.0
	if ($save_cols) {
		// Restore columns
		$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
	}
	if ($this->ColActive) { $this->SetCol(0); }

   	//RESET BLOCK BORDER TOP
   	if (!$this->ColActive) {
		for($bl=1;$bl<=$this->blklvl;$bl++) {
			$this->blk[$bl]['y0'] = $this->y;
			$this->blk[$bl]['x0'] += $this->MarginCorrection;
			// Added mPDF 3.0 Float DIV
			$this->blk[$bl]['marginCorrected'][$this->page] = true; 
		}
	}

	// mPDF 3.0 - Moved here from WriteFlowingBlock, FinishFlowingBlock and printbuffer
	$this->cMarginL = $bak_cml;
	$this->cMarginR = $bak_cmr;
	$this->divwidth = $bak_dw;
}


function PageNo()
{
	//Get current page number
	return $this->page;
}

// Edited mPDF 2.0 - Use fourth parameter for CMYK colors
function SetDrawColor($r,$g=-1,$b=-1,$col4=-1)
{
	//Set color for all stroking operations
	if(($r==0 and $g==0 and $b==0 && $col4 == -1) or $g==-1)	$this->DrawColor=sprintf('%.3f G',$r/255);
	else if ($col4 == -1) $this->DrawColor=sprintf('%.3f %.3f %.3f RG',$r/255,$g/255,$b/255);
	else {
		// CMYK
		$this->DrawColor = sprintf('%.3f %.3f %.3f %.3f K', $r/100, $g/100, $b/100, $col4/100);
	}
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['DrawColor'] != $this->DrawColor || $this->keep_block_together)) { $this->_out($this->DrawColor); }
	$this->pageoutput[$this->page]['DrawColor'] = $this->DrawColor;
}

// Edited mPDF 2.0 - Use fourth parameter for CMYK colors
function SetFillColor($r,$g=-1,$b=-1,$col4=-1)
{
	//Set color for all filling operations
	if(($r==0 and $g==0 and $b==0 && $col4 == -1) or $g==-1)	$this->FillColor=sprintf('%.3f g',$r/255);
	else if ($col4 == -1) $this->FillColor=sprintf('%.3f %.3f %.3f rg',$r/255,$g/255,$b/255);
	else {
		// CMYK
		$this->FillColor = sprintf('%.3f %.3f %.3f %.3f k', $r/100, $g/100, $b/100, $col4/100);
	}
	$this->ColorFlag = ($this->FillColor != $this->TextColor);
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['FillColor'] != $this->FillColor || $this->keep_block_together)) { $this->_out($this->FillColor); }
	$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
}

// Edited mPDF 2.0 - Use fourth parameter for CMYK colors
function SetTextColor($r,$g=-1,$b=-1,$col4=-1)
{
	//Set color for text
	if(($r==0 and $g==0 and $b==0 && $col4 == -1) or $g==-1)	$this->TextColor=sprintf('%.3f g',$r/255);
	else if ($col4 == -1) $this->TextColor=sprintf('%.3f %.3f %.3f rg',$r/255,$g/255,$b/255);
	else {
		// CMYK
		$this->TextColor = sprintf('%.3f %.3f %.3f %.3f k', $r/100, $g/100, $b/100, $col4/100);
	}
	$this->ColorFlag = ($this->FillColor != $this->TextColor);
}



function GetStringWidth($s)
{
			//Get width of a string in the current font
			$s = (string)$s;
			$cw = &$this->CurrentFont['cw'];
			$w = 0;
			if ($this->is_MB && !$this->usingCoreFont) {
				$unicode = $this->UTF8StringToArray($s);
				foreach($unicode as $char) {
					// mPDF 2.5 Soft Hyphens
					if ($char == 173) { 
						continue;
					} elseif (isset($cw[$char])) {
						$w+=$cw[$char];
					} elseif(isset($cw[$this->ords[$char]])) {
						$w+=$cw[$this->ords[$char]];
					} elseif(isset($cw[$this->chrs[$char]])) {
						$w+=$cw[$this->chrs[$char]];
					} elseif(isset($this->CurrentFont['desc']['MissingWidth'])) {
						$w += $this->CurrentFont['desc']['MissingWidth']; // set default size
					} elseif(isset($this->CurrentFont['MissingWidth'])) {
						$w += $this->CurrentFont['MissingWidth']; // set default size
					} else {
						$w += 500;
					}
				}
			} 
			else {
				$l = strlen($s);
				for($i=0; $i<$l; $i++) {
					// mPDF 2.5 Soft Hyphens
					// mPDF 3.0 Soft Hyphens chr(173)
					if (substr($s,$i,1) == chr(173) && ($this->FontFamily!='symbol' && $this->FontFamily!='zapfdingbats')) { 
						continue;
					} else if (isset($cw[substr($s,$i,1)])) {
						$w += $cw[substr($s,$i,1)];
					} 
					else if (isset($cw[$this->ords[substr($s,$i,1)]])) {
						$w += $cw[$this->ords[substr($s,$i,1)]];
					}
				}
			}
			// mPDF 2.1
			unset($cw);
			return ($w * $this->FontSize/ 1000);
}

function SetLineWidth($width)
{
	//Set line width
	$this->LineWidth=$width;
	$lwout = (sprintf('%.3f w',$width*$this->k));
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['LineWidth'] != $lwout || $this->keep_block_together)) {
		 $this->_out($lwout); 
	}
	$this->pageoutput[$this->page]['LineWidth'] = $lwout;
}

function Line($x1,$y1,$x2,$y2)
{
	//Draw a line
	$this->_out(sprintf('%.3f %.3f m %.3f %.3f l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

function Arrow($x1,$y1,$x2,$y2,$headsize=3,$fill='B',$angle=25)
{
  //F == fill //S == stroke //B == stroke and fill 
  // angle = splay of arrowhead - 1 - 89 degrees
  $s = '';
  $s.=sprintf('%.3f %.3f m %.3f %.3f l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k);
  $this->_out($s);

  $a = atan2(($y2-$y1),($x2-$x1));
  $b = $a + deg2rad($angle);
  $c = $a - deg2rad($angle);
  $x3 = $x2 - ($headsize* cos($b));
  $y3 = $this->h-($y2 - ($headsize* sin($b)));
  $x4 = $x2 - ($headsize* cos($c));
  $y4 = $this->h-($y2 - ($headsize* sin($c)));

  $s = '';
  $s.=sprintf('%.3f %.3f m %.3f %.3f l %.3f %.3f l %.3f %.3f l ',$x2*$this->k,($this->h-$y2)*$this->k,$x3*$this->k,$y3*$this->k,$x4*$this->k,$y4*$this->k,$x2*$this->k,($this->h-$y2)*$this->k);
  $s.=$fill;
  $this->_out($s);
}


function Rect($x,$y,$w,$h,$style='')
{
	//Draw a rectangle
	if($style=='F')	$op='f';
	elseif($style=='FD' or $style=='DF') $op='B';
	else $op='S';
	$this->_out(sprintf('%.3f %.3f %.3f %.3f re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
}

function AddFont($family,$style='',$file='')
{

	if ($this->isCJK && $this->use_CJK_only) { return; }
	if(empty($family)) { return; }
	//Add a TrueType or Type1 font
	$family = strtolower($family);

	$style=strtoupper($style);
	$style=str_replace('U','',$style);
	if($style=='IB') $style='BI';
	$fontkey = $family.$style;
	// check if the font has been already added
	if(isset($this->fonts[$fontkey])) {
		return;
	}

	if (($this->is_MB) && (!$this->usingCoreFont)) {
			if($file=='') {
				$file = str_replace(' ', '', $family).strtolower($style).'.php';
			}
			if(!file_exists($this->_getfontpath().$file)) {
				// try to load the basic file without styles
				$file = str_replace(' ', '', $family).'.php';
			}
			include($this->_getfontpath().$file);

			if(!isset($name) AND !isset($fpdf_charwidths)) {
				$this->Error('Could not include font definition file');
			}

			$i = count($this->fonts)+1;

			$this->fonts[$fontkey] = array('i'=>$i, 'type'=>$type, 'name'=>$name, 'desc'=>$desc, 'up'=>$up, 'ut'=>$ut, 'cw'=>$cw, 'enc'=>$enc, 'file'=>$file, 'ctg'=>$ctg);
			$fpdf_charwidths[$fontkey] = $cw;

			if(isset($diff) AND (!empty($diff))) {
				//Search existing encodings
				$d=0;
				$nb=count($this->diffs);
				for($i=1;$i<=$nb;$i++) {
					if($this->diffs[$i]==$diff) {
						$d=$i;
						break;
					}
				}
				if($d==0) {
					$d=$nb+1;
					$this->diffs[$d]=$diff;
				}
				$this->fonts[$fontkey]['diff']=$d;
			}
			if(!empty($file)) {
				if((strcasecmp($type,"TrueType") == 0) OR (strcasecmp($type,"TrueTypeUnicode") == 0)) {
					$this->FontFiles[$file]=array('length1'=>$originalsize);
				}
				else {
					$this->FontFiles[$file]=array('length1'=>$size1,'length2'=>$size2);
				}
			}
	}
	else { 	// if not unicode (or embedded)
		if($file=='') {
			$file=str_replace(' ','',$family).strtolower($style);

			if ($this->is_MB) {
				$file=$file.'.php';
			}
			else if ($this->codepage != 'win-1252') {
				$file=$file.'-'.$this->codepage.'.php';
			}
			else {	// is there any other?
				$file=$file.'.php';
			}


		}
		if(defined('FPDF_FONTPATH')) { $file=FPDF_FONTPATH.$file; }
		include($file);
		if(!isset($name))	$this->Error('Could not include font definition file - '.$family.' '.$style);
		$i=count($this->fonts)+1;
		$this->fonts[$family.$style]=array('i'=>$i,'type'=>$type,'name'=>$name,'desc'=>$desc,'up'=>$up,'ut'=>$ut,'cw'=>$cw,'enc'=>$enc,'file'=>$file);
		if($diff)
		{
			//Search existing encodings
			$d=0;
			$nb=count($this->diffs);
			for($i=1;$i<=$nb;$i++)
				if($this->diffs[$i]==$diff)
				{
					$d=$i;
					break;
				}
			if($d==0)
			{
				$d=$nb+1;
				$this->diffs[$d]=$diff;
			}
			$this->fonts[$family.$style]['diff']=$d;
		}
		if($file)
		{
			if($type=='TrueType')	$this->FontFiles[$file]=array('length1'=>$originalsize);
			else $this->FontFiles[$file]=array('length1'=>$size1,'length2'=>$size2);
		}
		// ADDED fontlist is defined in html2fpdf
		if (isset($this->fontlist)) { $this->fontlist[] = strtolower($family); }
	}
}



function SetFont($family,$style='',$size=0, $write=true, $forcewrite=false)
{
	$family=strtolower($family);
	// save previous values
	$this->prevFontFamily = $this->FontFamily;
	$this->prevFontStyle = $this->FontStyle;
	//Select a font; size given in points
	global $fpdf_charwidths;

	if($family=='') { 
		if ($this->FontFamily) { $family=$this->FontFamily; }
		else if ($this->default_font) { $family=$this->default_font; }
		else { die("ERROR - No font or default font set!"); }
	}


	if (($family == 'symbol') || ($family == 'zapfdingbats')  || ($family == 'times')  || ($family == 'courier') || ($family == 'helvetica')) { $this->usingCoreFont = true; }
	else {  $this->usingCoreFont = false; }

	if($family=='symbol' or $family=='zapfdingbats') { $style=''; }
	$style=strtoupper($style);
	if(is_int(strpos($style,'U'))) {
		$this->underline=true;
		$style=str_replace('U','',$style);
	}
	else { $this->underline=false; }
	if ($style=='IB') $style='BI';
	if ($size==0) $size=$this->FontSizePt;


	$fontkey=$family.$style;

	if ($this->is_MB && !$this->usingCoreFont) {
		// CJK fonts
		if (in_array($fontkey,$this->available_CJK_fonts)) {
			if(!isset($this->fonts[$fontkey])) {	// already added
				if (empty($this->Big5_widths)) { require(_MPDF_PATH . 'CJKdata.php'); }
				$this->AddCJKFont($family);	// don't need to add style
			}
			$this->isCJK = true;
			$this->setMBencoding('UTF-8');
		}
		else if ($this->use_CJK_only) {
			$family = $this->default_font;
			$this->isCJK = true;
			$this->setMBencoding('UTF-8');
		}
		// Test to see if requested font/style is available - or substitute
		else if (!in_array($fontkey,$this->available_unifonts)) {
			// If font[nostyle] exists - set it
			if (in_array($family,$this->available_unifonts)) {
				$style = '';
			}

			// Else if only one font available - set it (assumes if only one font available it will not have a style)
			else if (count($this->available_unifonts) == 1) {
				$family = $this->available_unifonts[0];
				$style = '';
			}

			else {
				$found = 0;
				// else substitute font of similar type
				if (in_array($family,$this->sans_fonts)) { 
					$i = array_intersect($this->sans_fonts,$this->available_unifonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_unifonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->serif_fonts)) { 
					$i = array_intersect($this->serif_fonts,$this->available_unifonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_unifonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->mono_fonts)) {
					$i = array_intersect($this->mono_fonts,$this->available_unifonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_unifonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}

				if (!$found) {
					// set first available font
					$fs = $this->available_unifonts[0];
					preg_match('/^([a-z_]+)([BI]{0,2})$/',$fs,$fas);
					// with requested style if possible
					$ws = $fas[1].$style;
					if (in_array($ws,$this->available_unifonts)) {
						$family = $fas[1]; // leave $style as is
					}
					else if (in_array($fas[1],$this->available_unifonts)) {
					// or without style
						$family = $fas[1];
						$style = '';
					}
					else {
					// or with the style specified 
						$family = $fas[1];
						$style = $fas[2];
					}
				}
			}

			$this->isCJK = false;
			$this->setMBencoding('UTF-8');

			$fontkey = $family.$style; 
		}
		else {
			$this->isCJK = false;
			$this->setMBencoding('UTF-8');
		}

		// try to add font (if not already added)
		$this->AddFont($family, $style);

		//Test if font is already selected
		if(($this->FontFamily == $family) AND ($this->FontStyle == $style) AND ($this->FontSizePt == $size) && !$forcewrite) {
			return $family;
		}

		// mPDF 1.1 added line
		$fontkey = $family.$style; 

		//Select it
		$this->FontFamily = $family;
		$this->FontStyle = $style;
		$this->FontSizePt = $size;
		$this->FontSize = $size / $this->k;
		$this->CurrentFont = &$this->fonts[$fontkey];
		if ($write) { 
			$fontout = (sprintf('BT /F%d %.3f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			// Edited mPDF 1.1 keeping block together on one page
			if($this->page>0 && ($this->pageoutput[$this->page]['Font'] != $fontout || $this->keep_block_together)) { $this->_out($fontout); }
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}



		// Added - currentfont (lowercase) used in HTML2PDF
		$this->currentfontfamily=$family;
		$this->currentfontsize=$size;
		$this->currentfontstyle=$style.($this->underline ? 'U' : '');
	}

	else { 	// if not unicode/CJK - or core embedded font
		$this->isCJK = false;
		$this->setMBencoding($this->codepage);

		// Edit mPDF 1.1 - brought forward to increase efficiency
		//Test if font is already selected
		if(($this->FontFamily == $family) AND ($this->FontStyle == $style) AND ($this->FontSizePt == $size) && !$forcewrite) {
			return $family;
		}

		// ALWAYS SUBSTITUTE ARIAL TIMES COURIER IN 1252
		if (!isset($this->CoreFonts[$fontkey]) && ($this->useOnlyCoreFonts) && ($this->codepage == 'win-1252')) {
			if (in_array($family,$this->serif_fonts)) { $family = 'times'; }
			else if (in_array($family,$this->mono_fonts)) { $family = 'courier'; }
			else { $family = 'helvetica'; }
			$this->usingCoreFont = true;
			$fontkey = $family.$style; 
		}

		// Test to see if requested font/style is available - or substitute
		if (!in_array($fontkey,$this->available_fonts) && (!$this->usingCoreFont) ) {

			// If font[nostyle] exists - set it
			if (in_array($family,$this->available_fonts)) {
				$style = '';
			}

			// Else if only one font available - set it (assumes if only one font available it will not have a style)
			else if (count($this->available_fonts) == 1) {
				$family = $this->available_fonts[0];
				$style = '';
			}

			else {
				$found = 0;
				// else substitute font of similar type
				if (in_array($family,$this->sans_fonts)) { 
					$i = array_intersect($this->sans_fonts,$this->available_fonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_fonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->serif_fonts)) { 
					$i = array_intersect($this->serif_fonts,$this->available_fonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_fonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->mono_fonts)) {
					$i = array_intersect($this->mono_fonts,$this->available_fonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_fonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}

				if (!$found) {
					// set first available font
					$fs = $this->available_unifonts[0];
					preg_match('/^([a-z_]+)([BI]{0,2})$/',$fs,$fas);
					// with requested style if possible
					$ws = $fas[1].$style;
					if (in_array($ws,$this->available_fonts)) {
						$family = $fas[1]; // leave $style as is
					}
					else if (in_array($fas[1],$this->available_fonts)) {
					// or without style
						$family = $fas[1];
						$style = '';
					}
					else {
					// or with the style specified 
						$family = $fas[1];
						$style = $fas[2];
					}
				}
			}
			$fontkey = $family.$style; 
		}

		if(!isset($this->fonts[$fontkey])) 	{
			// STANDARD CORE FONTS
			if (isset($this->CoreFonts[$fontkey])) {
				if(!isset($fpdf_charwidths[$fontkey])) {
					//Load metric file
					$file=$family;
					if($family=='times' or $family=='helvetica') { $file.=strtolower($style); }
					$file.='.php';
					if(defined('FPDF_FONTPATH')) $file=FPDF_FONTPATH.$file;
					include($file);
					if(!isset($fpdf_charwidths[$fontkey])) $this->Error('Could not include font metric file');
				}

				$i=count($this->fonts)+1;
				$this->fonts[$fontkey]=array('i'=>$i,'type'=>'core','name'=>$this->CoreFonts[$fontkey],'up'=>-100,'ut'=>50,'cw'=>$fpdf_charwidths[$fontkey]);
			}
			else {
				// try to add font 
				$this->AddFont($family, $style);
			}
		}
		//Test if font is already selected
		if(($this->FontFamily == $family) AND ($this->FontStyle == $style) AND ($this->FontSizePt == $size) && !$forcewrite) {
			return $family;
		}
		//Select it
		$this->FontFamily=$family;
		$this->FontStyle=$style;
		$this->FontSizePt=$size;
		$this->FontSize=$size/$this->k;
		$this->CurrentFont=&$this->fonts[$fontkey];
		if ($write) { 
			$fontout = (sprintf('BT /F%d %.3f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			// Edited mPDF 1.1 keeping block together on one page
			if($this->page>0 && ($this->pageoutput[$this->page]['Font'] != $fontout || $this->keep_block_together)) { $this->_out($fontout); }
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}
		// Added - currentfont (lowercase) used in HTML2PDF
		$this->currentfontfamily=$family;
		$this->currentfontsize=$size;
		$this->currentfontstyle=$style.($this->underline ? 'U' : '');

	}
	return $family;
}

function SetFontSize($size,$write=true)
{
	//Set font size in points
	if($this->FontSizePt==$size) return;
	$this->FontSizePt=$size;
	$this->FontSize=$size/$this->k;
	$this->currentfontsize=$size;
		if ($write) { 
			$fontout = (sprintf('BT /F%d %.3f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			// Edited mPDF 1.1 keeping block together on one page
			if($this->page>0 && ($this->pageoutput[$this->page]['Font'] != $fontout || $this->keep_block_together)) { $this->_out($fontout); }
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}
}

function AddLink()
{
	//Create a new internal link
	$n=count($this->links)+1;
	$this->links[$n]=array(0,0);
	return $n;
}

function SetLink($link,$y=0,$page=-1)
{
	//Set destination of internal link
	if($y==-1) $y=$this->y;
	if($page==-1)	$page=$this->page;
	$this->links[$link]=array($page,$y);
}

function Link($x,$y,$w,$h,$link)
{
	// Edited mPDF 1.1 keeping block together on one page
	if ($this->keep_block_together) {	// Save to array - don't write yet
		$this->ktLinks[$this->page][]=array($x*$this->k,$this->hPt-$y*$this->k,$w*$this->k,$h*$this->k,$link);
		return;
	}
	// Edited mPDF 2.0 save links in buffer when rotating table
	else if ($this->table_rotate) {
		$this->tbrot_Links[$this->page][]=array($x*$this->k,$this->hPt-$y*$this->k,$w*$this->k,$h*$this->k,$link);
		return;
	}
	// Edited mPDF 2.0 for keep-with-table
	else if ($this->kwt) {
		$this->kwt_Links[$this->page][]=array($x*$this->k,$this->hPt-$y*$this->k,$w*$this->k,$h*$this->k,$link);
		return;
	}
	// mPDF 3.0
	if ($this->writingHTMLheader) {
		$this->HTMLheaderPageLinks[]=array($x*$this->k,$this->hPt-$y*$this->k,$w*$this->k,$h*$this->k,$link);
		return;
	}
	//Put a link on the page
	$this->PageLinks[$this->page][]=array($x*$this->k,$this->hPt-$y*$this->k,$w*$this->k,$h*$this->k,$link);
	// Save cross-reference to Column buffer
	$ref = count($this->PageLinks[$this->page])-1;
	$this->columnLinks[$this->CurrCol][INTVAL($this->x)][INTVAL($this->y)] = $ref;

}

function WriteText($x,$y,$txt)
{
	// Output a string using Text() but does encoding and text reversing of RTL
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if (!$this->is_MB) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }
	// DIRECTIONALITY
	$this->magic_reverse_dir($txt);
	$this->Text($x,$y,$txt);
}

function Text($x,$y,$txt)
{
	// Output a string
	// Called (only) by Watermark
	// Expects input to be mb_encoded if necessary and RTL reversed
	// NON_BREAKING SPACE
	if ($this->is_MB && !$this->usingCoreFont) {
	      $txt2 = str_replace($this->chrs[194].$this->chrs[160],$this->chrs[32],$txt); 
		if (!$this->usingCoreFont) {
			//Convert string to UTF-16BE without BOM
			$txt2= $this->UTF8ToUTF16BE($txt2, false);
		}
	}
	else {
	      $txt2 = str_replace($this->chrs[160],$this->chrs[32],$txt);
	}
	$s=sprintf('BT %.3f %.3f Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt2));
	if($this->underline and $txt!='') {
		$s.=' '.$this->_dounderline($x,$y + (0.1* $this->FontSize),$txt);
	}
	if($this->ColorFlag) $s='q '.$this->TextColor.' '.$s.' Q';
	$this->_out($s);
}

function WriteCell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='', $currentx=0) //EDITEI
{
	//Output a cell using Cell() but does encoding and text reversing of RTL
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if (!$this->is_MB) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }
	// DIRECTIONALITY
	$this->magic_reverse_dir($txt);
	$this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link, $currentx);
}


// WORD SPACING
function GetJspacing($nc,$ns,$w) {
	$ws = 0; 
	$charspacing = 0;
	$ww = $this->jSWord;
	$ncx = $nc-1;	// mPDF 2.5
	if ($nc == 0 && $ns == 0) { return array(0,0); }
	if ($nc==1) { $charspacing = $w; }	// mPDF 2.5 nc-1 correction
	else if ($this->jSpacing == 'C') {
		if ($nc) { $charspacing = $w / ($ncx ); }	// mPDF 2.5 nc-1 correction
	}
	else if ($this->jSpacing == 'W') {
		if ($ns) { $ws = $w / $ns; }
	}
	else if (!$ns) {
		if ($nc) { $charspacing = $w / ($ncx ); }	// mPDF 2.5 nc-1 correction
		// mPDF 2.5 Added
		if (($this->jSmaxChar > 0) && ($charspacing > $this->jSmaxChar)) { 
			$charspacing = $this->jSmaxChar;
		}
	}
	else if ($ns == ($ncx )) {	// mPDF 2.5 nc-1 correction
		$charspacing = $w / $ns;
	}
	else {
		if ($nc) { 
		   if ($this->is_MB && !$this->usingCoreFont) {
			$cs = ($w * (1 - $this->jSWord)) / ($ncx -$ns);	// mPDF 2.5 nc-1 correction
			if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
				$cs = $this->jSmaxChar;
				$ww = 1 - (($cs * ($ncx -$ns))/$w);	// mPDF 2.5 nc-1 correction
			}
			$charspacing = $cs; 
			$ws = (($w * ($ww) ) / $ns) - $charspacing;
		   }
		   else {
			$cs = ($w * (1 - $this->jSWord)) / ($ncx );	// mPDF 2.5 nc-1 correction
			if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
				$cs = $this->jSmaxChar;
				$ww = 1 - (($cs * ($ncx ))/$w);	// mPDF 2.5 nc-1 correction
			}
			$charspacing = $cs; 
			$ws = ($w * ($ww) ) / $ns;
		   }
		}
	}
	return array($charspacing,$ws); 
}

function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='', $currentx=0, $lcpaddingL=0, $lcpaddingR=0, $valign='M') //EDITEI
{
	//Output a cell
	// Expects input to be mb_encoded if necessary and RTL reversed
	// NON_BREAKING SPACE
	if ($this->is_MB) {
	      $txt = str_replace($this->chrs[194].$this->chrs[160],$this->chrs[32],$txt); 
	}
	else {
	      $txt = str_replace($this->chrs[160],$this->chrs[32],$txt);
	}

	$k=$this->k;

	$oldcolumn = $this->CurrCol;
	// Automatic page break
	// Allows PAGE-BREAK-AFTER = avoid to work

	if ((($this->y+$this->divheight>$this->PageBreakTrigger) || ($this->y+$h>$this->PageBreakTrigger) || 
		($this->y+($h*2)>$this->PageBreakTrigger && $this->blk[$this->blklvl]['page_break_after_avoid'])) and !$this->InFooter and $this->AcceptPageBreak()) {
		$x=$this->x;//Current X position


		// WORD SPACING
		$ws=$this->ws;//Word Spacing
		if($ws>0) {
			$this->ws=0;
			$this->_out('BT 0 Tw ET'); 
		}
		$charspacing=$this->charspacing;//Character Spacing
		if($charspacing>0) {
			$this->charspacing=0;
			$this->_out('BT 0 Tc ET'); 
		}

		$this->AddPage($this->CurOrientation);
		// Added to correct for OddEven Margins
		$x += $this->MarginCorrection;
		if ($currentx) { 
			$currentx += $this->MarginCorrection;
		} 
		$this->x=$x;
		// WORD SPACING
		if($ws>0) {
			$this->ws=$ws;
			$this->_out(sprintf('BT %.3f Tw ET',$ws)); 
		}
		if($charspacing>0) {
			$this->charspacing=$charspacing;
			$this->_out(sprintf('BT %.3f Tc ET',$charspacing));//add-on 
		}
	}

	// COLS
	// COLUMN CHANGE
	if ($this->CurrCol != $oldcolumn) {
		if ($currentx) { 
			$currentx += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
		} 
		$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
	}

	// COLUMNS Update/overwrite the lowest bottom of printing y value for a column
	if ($this->ColActive) {
		if ($h) { $this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y+$h; }
		else { $this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y+$this->divheight; }
	}

	// Edited mPDF 1.1 keeping block together on one page
	// KEEP BLOCK TOGETHER Update/overwrite the lowest bottom of printing y value on first page
	if ($this->keep_block_together) {
		if ($h) { $this->ktBlock[$this->page]['bottom_margin'] = $this->y+$h; }
//		else { $this->ktBlock[$this->page]['bottom_margin'] = $this->y+$this->divheight; }
	}

	if($w==0) $w = $this->w-$this->rMargin-$this->x;
	$s='';
	if($fill==1 && $this->FillColor) { 
		// Edited mPDF 1.1 keeping block together on one page
		if($this->pageoutput[$this->page]['FillColor'] != $this->FillColor || $this->keep_block_together) { $s .= $this->FillColor.' '; }
		$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
	}
//$fill=1;//DEBUG
	if($fill==1 or $border==1)
	{
		if ($fill==1) $op=($border==1) ? 'B' : 'f';
		else $op='S';
//$op='S'; $this->SetLineWidth(0.02); $this->SetDrawColor(0);//DEBUG

		$s.=sprintf('%.3f %.3f %.3f %.3f re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	}

	if(is_string($border))
	{
		$x=$this->x;
		$y=$this->y;
		if(is_int(strpos($border,'L')))
			$s.=sprintf('%.3f %.3f m %.3f %.3f l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
		if(is_int(strpos($border,'T')))
			$s.=sprintf('%.3f %.3f m %.3f %.3f l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
		if(is_int(strpos($border,'R')))
			$s.=sprintf('%.3f %.3f m %.3f %.3f l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
		if(is_int(strpos($border,'B')))
			$s.=sprintf('%.3f %.3f m %.3f %.3f l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	}

	if($txt!='')
	{

		$stringWidth = $this->GetStringWidth($txt) + ( $this->charspacing * mb_strlen( $txt, $this->mb_encoding ) / $k )
				 + ( $this->ws * mb_substr_count( $txt, ' ', $this->mb_encoding ) / $k );

		// Set x OFFSET FOR PRINTING
		if($align=='R') {
			$dx=$w-$this->cMarginR - $stringWidth - $lcpaddingR;
		}
		elseif($align=='C') {
			$dx=(($w - $stringWidth )/2);
		}
		elseif($align=='L' or $align=='J') $dx=$this->cMarginL + $lcpaddingL;
    		else $dx = 0;
		if($this->ColorFlag) $s.='q '.$this->TextColor.' ';

		// OUTLINE
		if($this->outline_on)
		{
			$s.=' '.sprintf('%.3f w',$this->LineWidth*$k).' ';
			$s.=" $this->DrawColor ";
			$s.=" 2 Tr ";
    		}


		// FONT SIZE - this determines the baseline caculation
		if ($this->linemaxfontsize && !$this->processingHeader) { $bfs = $this->linemaxfontsize; }
		else  { $bfs = $this->FontSize; }

    		//Calculate baseline Superscript and Subscript Y coordinate adjustment
		$bfx = 0.35;
    		$baseline = $bfx*$bfs;
		if($this->SUP) { $baseline += ($bfx-1.05)*$this->FontSize; }
		else if($this->SUB) { $baseline += ($bfx + 0.04)*$this->FontSize; }
		else if($this->bullet) { $baseline += ($bfx-0.7)*$this->FontSize; }

		// Vertical align (for Images)
		if ($this->lineheight_correction) { 
			if ($valign == 'T') { $va = (0.5 * $bfs * $this->lineheight_correction); }
			else if ($valign == 'B') { $va = $h-(0.5 * $bfs * $this->lineheight_correction); }
			else { $va = 0.5*$h; }	// Middle - default
		}
		else { 
			if ($valign == 'T') { $va = (0.5 * $bfs * $this->default_lineheight_correction); }
			else if ($valign == 'B') { $va = $h-(0.5 * $bfs * $this->default_lineheight_correction); }
			else { $va = 0.5*$h; }	// Middle - default
		}
		// THE TEXT
		// WORD SPACING
		// IF multibyte - Tw has no effect - need to do word spacing by setting character spacing for spaces between words
		if ($this->ws && $this->is_MB) {
		  $space = ' ';
		  if ($this->is_MB && !$this->usingCoreFont) {
			//Convert string to UTF-16BE without BOM
			$space= $this->UTF8ToUTF16BE($space , false);
		  }
		  $space=$this->_escape($space ); 

		  $s.=sprintf('BT %.3f %.3f Td',($this->x+$dx)*$k,($this->h-($this->y+$baseline+$va))*$k);
		  $t = preg_split('/[ ]/u',$txt);
		  for($i=0;$i<count($t);$i++) {
			$tx = $t[$i]; 
		  	if ($this->is_MB && !$this->usingCoreFont) {
				//Convert string to UTF-16BE without BOM
				$tx = $this->UTF8ToUTF16BE($tx , false);
			}

			$tx = $this->_escape($tx); 

			$s.=sprintf(' %.3f Tc (%s) Tj',$this->charspacing,$tx);
			if (($i+1)<count($t)) {
				$s.=sprintf(' %.3f Tc (%s) Tj',$this->ws+$this->charspacing,$space);
			}
		  }
		  $s.=' ET';
		}
		else {
		  $txt2= $txt;
		  if ($this->is_MB && !$this->usingCoreFont) {
			//Convert string to UTF-16BE without BOM
			$txt2= $this->UTF8ToUTF16BE($txt2, false);
		  }
		  $txt2=$this->_escape($txt2); 
		  $s.=sprintf('BT %.3f %.3f Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+$baseline+$va))*$k,$txt2);
		}

		// UNDERLINE
		if($this->underline) {
			$s.=' '.$this->_dounderline($this->x+$dx,$this->y+$baseline+$va+ (0.1* $this->FontSize),$txt);
		}

   		// STRIKETHROUGH
		if($this->strike) {
    			//Superscript and Subscript Y coordinate adjustment (now for striked-through texts)
			$ch=$this->CurrentFont['desc']['CapHeight'];
			if (!$ch) {
				if ($this->FontFamily == 'helvetica') { $ch = 716; }
				else if ($this->FontFamily == 'times') { $ch = 662; }
				else if ($this->FontFamily == 'courier') { $ch = 571; }
				else { $ch = 700; }
			}
			$adjusty = (-$ch/1000* $this->FontSize) * 0.35;	

			$s.=' '.$this->_dounderline($this->x+$dx,$this->y+$baseline+$adjusty+$va,$txt);
		}

		// COLOR
		if($this->ColorFlag) $s.=' Q';

		// LINK
		if($link!='') {
			$this->Link($this->x+$dx,$this->y+$va-.5*$this->FontSize,$stringWidth,$this->FontSize,$link);
		}
	}
	if($s) $this->_out($s);

	// WORD SPACING
	if ($this->ws && $this->is_MB) {
		$this->_out(sprintf('BT %.3f Tc ET',$this->charspacing));//add-on 
	}

	$this->lasth=$h;
	if( strpos($txt,"\n") !== false) $ln=1; //EDITEI - cell now recognizes \n! << comes from <BR> tag
	if($ln>0)
	{
		//Go to next line
		$this->y += $h;
		if($ln==1) //EDITEI
		{
			//Move to next line
			if ($currentx != 0) { $this->x=$currentx; }	
			else { $this->x=$this->lMargin; }
   		}
	}
	else $this->x+=$w;


}




function MultiCell($w,$h,$txt,$border=0,$align='',$fill=0,$link='',$directionality='ltr',$encoded=false)
{
	if (!$encoded) {
		$txt = $this->purify_utf8_text($txt);
		if ($this->text_input_as_HTML) {
			$txt = $this->all_entities_to_utf8($txt);
		}
		if (!$this->is_MB) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }
	}


	// Parameter encoded - When called internally from ->Reference mb_encoding already done - but not reverse RTL
	if (!$align) { $align = $this->defaultAlign; }

	//Output text with automatic or explicit line breaks
	$cw=&$this->CurrentFont['cw'];
	if($w==0)	$w=$this->w-$this->rMargin-$this->x;

	if ($this->is_MB) {
			$wmax = ($w - ($this->cMarginL+$this->cMarginR));
	}
	else {
			$wmax=($w- ($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
	}
	if ($this->is_MB)  {
		$s=preg_replace("/\r/u",'',$txt);
		$nb=mb_strlen($s, $this->mb_encoding );
		while($nb>0 and mb_substr($s,$nb-1,1,$this->mb_encoding )=="\n")	$nb--;
	}
	else {
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		while($nb>0 and $s[$nb-1]=="\n")	$nb--;
	}
	$b=0;
	if($border)
	{
		if($border==1)
		{
			$border='LTRB';
			$b='LRT';
			$b2='LR';
		}
		else
		{
			$b2='';
			if(is_int(strpos($border,'L')))	$b2.='L';
			if(is_int(strpos($border,'R')))	$b2.='R';
			$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
		}
	}
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$ns=0;
	$nl=1;


   if ($this->is_MB)  {
	while($i<$nb)
	{
		//Get next character
		$c = mb_substr($s,$i,1,$this->mb_encoding );
		if(preg_match("/[\n]/u", $c)) {
			//Explicit line break
			// WORD SPACING
			if($this->ws>0) {
				$this->ws=0;
				$this->_out('BT 0 Tw ET'); 
			}
			if($this->charspacing>0) {
				$this->charspacing=0;
				$this->_out('BT 0 Tc ET'); 
			}
			$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
			// DIRECTIONALITY
			$this->magic_reverse_dir($tmp);

			$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
			continue;
		}
		if(preg_match("/[ ]/u", $c)) {
			$sep=$i;
			$ls=$l;
			$ns++;
		}

		$l = $this->GetStringWidth(mb_substr($s, $j, $i-$j,$this->mb_encoding ));

		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1) {	// Only one word
				if($i==$j) $i++;
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
				$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
				// DIRECTIONALITY
				$this->magic_reverse_dir($tmp);

				$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
			}
			else {
				$tmp = mb_rtrim(mb_substr($s,$j,$sep-$j,$this->mb_encoding),'UTF-8');
				if($align=='J') {
					//$this->ws=($ns>1) ? ((($wmax-$ls)/($ns-1))) : 0;
					//$this->_out(sprintf('%.3f Tw',$this->ws*$this->k));

					//////////////////////////////////////////
					// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
					// WORD SPACING UNICODE
					// mPDF 2.5 Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
					$tmp = str_replace($this->chrs[194].$this->chrs[160],$this->chrs[32],$tmp ); 
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = mb_strlen( $tmp , $this->mb_encoding ) ;  
					$nb_spaces = mb_substr_count( $tmp ,' ', $this->mb_encoding ) ;  
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
					if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
					else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
					$this->charspacing=$charspacing;
					if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
					else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
					$this->ws=$ws;
					//////////////////////////////////////////
				}

				// DIRECTIONALITY
				$this->magic_reverse_dir($tmp);

				$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
		}
		else $i++;
	}
	//Last chunk
	// WORD SPACING
	if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	}
	if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	}

   }


   else {
	while($i<$nb)
	{
		//Get next character
		$c=substr($s,$i,1);
		if(preg_match("/[\n]/u", $c)) {
			//Explicit line break
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
			continue;
		}
		if(preg_match("/[ ]/u", $c)) {
			$sep=$i;
			$ls=$l;
			$ns++;
		}

		$l+=$cw[$c];
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1)
			{
				if($i==$j) $i++;
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link);
			}
			else
			{
				if($align=='J')
				{
					$tmp = rtrim(substr($s,$j,$sep-$j));
					//////////////////////////////////////////
					// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
					// WORD SPACING NON_UNICDOE/CJK
					// mPDF 2.5 Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
					$tmp = str_replace($this->chrs[160],$this->chrs[32],$tmp);
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = strlen( $tmp ) ;  
					$nb_spaces = substr_count( $tmp ,' ' ) ;  
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
					if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
					else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
					$this->charspacing=$charspacing;
					if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
					else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
					$this->ws=$ws;
					//////////////////////////////////////////
				}
				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill,$link);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
		}
		else $i++;
	}
	//Last chunk
	// WORD SPACING
	if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	}
	if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	}

   }

	//Last chunk
   if($border and is_int(strpos($border,'B')))	$b.='B';
   if ($this->is_MB)  {
		$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
		// DIRECTIONALITY
		$this->magic_reverse_dir($tmp);
   		$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
   }
   else { $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link); }
   $this->x=$this->lMargin;
}




function Write($h,$txt,$currentx=0,$link='',$directionality='ltr',$align='') //EDITEI
{
	if (!$align) { $align = $this->defaultAlign; }	// NB Cannot use Align=J or C using Write??
	if ($h == 0) { $this->SetLineHeight(); $h = $this->lineheight; }
	//Output text in flowing mode
	$cw = &$this->CurrentFont['cw'];
	$w = $this->w - $this->rMargin - $this->x; 

	if ($this->is_MB) {
			$wmax = ($w - ($this->cMarginL+$this->cMarginR));
	}
	else {
			$wmax=($w- ($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
	}

	if ($this->is_MB)  {
		$s=preg_replace("/\r/u",'',$txt);	//????
		$nb=mb_strlen($s, $this->mb_encoding );
			// handle single space character
			if(($nb==1) AND preg_match("/[ ]/u", $s)) {
				$this->x += $this->GetStringWidth($s);
				return;
			}
	}
	else {
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
	}


			$sep=-1;
			$i=0;
			$j=0;
			$l=0;
			$nl=1;


	if ($this->is_MB) {
			while($i<$nb) {
				//Get next character
				$c = mb_substr($s,$i,1,$this->mb_encoding );
				if(preg_match("/[\n]/u", $c)) {
					// WORD SPACING
					if($this->ws>0) {
						$this->ws=0;
						$this->_out('BT 0 Tw ET'); 
					}
					if($this->charspacing>0)
					{
						$this->charspacing=0;
						$this->_out('BT 0 Tc ET'); 
					}
					//Explicit line break
					$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
					if ($this->directionality == 'rtl') {
					   if ($align == 'J') { $align = 'R'; }
					}
					// DIRECTIONALITY
					$this->magic_reverse_dir($tmp);

					$this->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					if($nl == 1) {
						if ($currentx != 0) $this->x=$currentx;//EDITEI
						else $this->x=$this->lMargin;
						$w = $this->w - $this->rMargin - $this->x;
						$wmax = ($w - ($this->cMarginL+$this->cMarginR));
					}
					$nl++;
					continue;
				}
				if(preg_match("/[ ]/u", $c)) {
					$sep= $i;
				}

				$l = $this->GetStringWidth(mb_substr($s, $j, $i-$j,$this->mb_encoding));

				if($l > $wmax) {
					//Automatic line break (word wrapping)
					if($sep == -1) {
						// WORD SPACING
						if($this->ws>0) {
							$this->ws=0;
							$this->_out('BT 0 Tw ET'); 
						}
						if($this->charspacing>0)
						{
							$this->charspacing=0;
							$this->_out('BT 0 Tc ET'); 
						}
						if($this->x > $this->lMargin) {
							//Move to next line
							if ($currentx != 0) $this->x=$currentx;//EDITEI
							else $this->x=$this->lMargin;
							$this->y+=$h;
							$w=$this->w-$this->rMargin-$this->x;
							$wmax = ($w - ($this->cMarginL+$this->cMarginR));
							$i++;
							$nl++;
							continue;
						}
						if($i==$j) {
							$i++;
						}
						$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
						if ($this->directionality == 'rtl') {
						   if ($align == 'J') { $align = 'R'; }
						}
						// DIRECTIONALITY
						$this->magic_reverse_dir($tmp);

						$this->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
					}
					else {
						$tmp = mb_rtrim(mb_substr($s,$j,$sep-$j,$this->mb_encoding),'UTF-8');
						if ($this->directionality == 'rtl') {
						   if ($align == 'J') { $align = 'R'; }
						}
						// DIRECTIONALITY
						$this->magic_reverse_dir($tmp);

						if($align=='J') {
							//////////////////////////////////////////
							// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
							// WORD SPACING
							// mPDF 2.5 Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
						      $tmp = str_replace($this->chrs[194].$this->chrs[160],$this->chrs[32],$tmp ); 
							$len_ligne = $this->GetStringWidth($tmp );
							$nb_carac = mb_strlen( $tmp , $this->mb_encoding ) ;  
							$nb_spaces = mb_substr_count( $tmp ,' ', $this->mb_encoding ) ;  
							list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
							if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
							else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
							$this->charspacing=$charspacing;
							if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
							else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
							$this->ws=$ws;
							//////////////////////////////////////////
						}

						$this->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
						$i=$sep+1;
					}
					$sep = -1;
					$j = $i;
					$l = 0;
					if($nl==1) {
						if ($currentx != 0) $this->x=$currentx;//EDITEI
						else $this->x=$this->lMargin;
						$w=$this->w-$this->rMargin-$this->x;
						$wmax = ($w - ($this->cMarginL+$this->cMarginR));
					}
					$nl++;
				}
				else {
					$i++;
				}
			}


	    //Last chunk
	    // WORD SPACING
	    if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	    }
	    if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	    }

	}


	else {
			while($i<$nb) {
				//Get next character
				$c=substr($s,$i,1);
				if(preg_match("/[\n]/u", $c)) {
					//Explicit line break
					// WORD SPACING
					if($this->ws>0) {
						$this->ws=0;
						$this->_out('BT 0 Tw ET'); 
					}
					if($this->charspacing>0)
					{
						$this->charspacing=0;
						$this->_out('BT 0 Tc ET'); 
					}
					$this->Cell($w, $h, substr($s, $j, $i-$j), 0, 2, $align, $fill, $link);
					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					if($nl == 1) {
						if ($currentx != 0) $this->x=$currentx;//EDITEI
						else $this->x=$this->lMargin;
						$w = $this->w - $this->rMargin - $this->x;
						$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
					}
					$nl++;
					continue;
				}
				if(preg_match("/[ ]/u", $c)) {
					$sep= $i;
				}

				$l += $cw[$c];

				if($l > $wmax) {
					//Automatic line break (word wrapping)
					if($sep == -1) {
						// WORD SPACING
						if($this->ws>0) {
							$this->ws=0;
							$this->_out('BT 0 Tw ET'); 
						}
						if($this->charspacing>0)
						{
							$this->charspacing=0;
							$this->_out('BT 0 Tc ET'); 
						}
						if($this->x > $this->lMargin) {
							//Move to next line
							if ($currentx != 0) $this->x=$currentx;//EDITEI
							else $this->x=$this->lMargin;
							$this->y+=$h;
							$w=$this->w-$this->rMargin-$this->x;
							$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
							$i++;
							$nl++;
							continue;
						}
						if($i==$j) {
							$i++;
						}
						$this->Cell($w, $h, substr($s, $j, $i-$j), 0, 2, $align, $fill, $link);
					}
					else {
						$tmp = substr($s, $j, $sep-$j);
						if($align=='J') {
							//////////////////////////////////////////
							// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
							// WORD SPACING
							// mPDF 2.5 Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
						      $tmp = str_replace($this->chrs[160],$this->chrs[32],$tmp );
							$len_ligne = $this->GetStringWidth($tmp );
							$nb_carac = strlen( $tmp ) ;  
							$nb_spaces = substr_count( $tmp ,' ' ) ;  
							list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
							if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
							else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
							$this->charspacing=$charspacing;
							if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
							else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
							$this->ws=$ws;
							//////////////////////////////////////////
						}

						$this->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
						$i=$sep+1;
					}
					$sep = -1;
					$j = $i;
					$l = 0;
					if($nl==1) {
						if ($currentx != 0) $this->x=$currentx;//EDITEI
						else $this->x=$this->lMargin;
						$w=$this->w-$this->rMargin-$this->x;
						$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
					}
					$nl++;
				}
				else {
					$i++;
				}
			}

	    //Last chunk
	    // WORD SPACING
	    if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	    }
	    if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	    }
	}

	//Last chunk
	if($i!=$j) {
	  if (($this->is_MB) && (!$this->usingCoreFont)) {
		$tmp = mb_substr($s,$j,$i-$j,$this->mb_encoding);
		if ($this->directionality == 'rtl') {
		   if ($align == 'J') { $align = 'R'; }
		}
		// DIRECTIONALITY
		$this->magic_reverse_dir($tmp);

	  }
	  else {
		$tmp = substr($s,$j,$i-$j);	// Including CJK which has processed each byte (not multibyte)
	  }
   	  $this->Cell($this->GetStringWidth($tmp),$h,$tmp,0,0,'C',$fill,$link);
	}
}


function saveInlineProperties()
{
   $saved = array();
   $saved[ 'family' ] = $this->FontFamily;
   $saved[ 'style' ] = $this->FontStyle;
   $saved[ 'sizePt' ] = $this->FontSizePt;
   $saved[ 'size' ] = $this->FontSize;
   $saved[ 'HREF' ] = $this->HREF; 
   $saved[ 'underline' ] = $this->underline; 
   $saved[ 'strike' ] = $this->strike;
   $saved[ 'SUP' ] = $this->SUP; 
   $saved[ 'SUB' ] = $this->SUB; 
   $saved[ 'linewidth' ] = $this->LineWidth;
   $saved[ 'drawcolor' ] = $this->DrawColor;
   $saved[ 'is_outline' ] = $this->outline_on;
   $saved[ 'outlineparam' ] = $this->outlineparam;
   $saved[ 'toupper' ] = $this->toupper;
   $saved[ 'tolower' ] = $this->tolower;

   $saved[ 'I' ] = $this->I;
   $saved[ 'B' ] = $this->B;
   $saved[ 'colorarray' ] = $this->colorarray;
   $saved[ 'bgcolorarray' ] = $this->spanbgcolorarray;
   $saved[ 'color' ] = $this->TextColor; 
   $saved[ 'bgcolor' ] = $this->FillColor;
   // mPDF 2.3
   $saved['lang'] = $this->currentLang;

   return $saved;
}

function restoreInlineProperties( $saved)
{

   $this->FontFamily = $saved[ 'family' ];
   $this->FontStyle = $saved[ 'style' ];
   $this->FontSizePt = $saved[ 'sizePt' ];
   $this->FontSize = $saved[ 'size' ];

   // mPDF 2.3
   $this->currentLang =  $saved['lang'];
   if ($this->useLang && $this->is_MB && $this->currentLang != $this->default_lang && ((strlen($this->currentLang) == 5 && $this->currentLang != 'UTF-8') || strlen($this->currentLang ) == 2)) { 
	list ($codepage,$mpdf_pdf_unifonts,$mpdf_directionality,$mpdf_jSpacing) = GetCodepage($this->currentLang);
	if ($codepage == 'SHIFT_JIS') { $this->FontFamily = 'sjis'; }
	else if ($codepage == 'UHC') { $this->FontFamily = 'uhc'; }
	else if ($codepage == 'BIG5') { $this->FontFamily = 'big5'; }
	else if ($codepage == 'GBK') { $this->FontFamily = 'gb'; }
	else if ($mpdf_pdf_unifonts) { $this->RestrictUnicodeFonts($mpdf_pdf_unifonts); }
	else { $this->RestrictUnicodeFonts($this->default_available_fonts ); }
   }
   else if ($this->useLang && $this->is_MB ) { 
	$this->RestrictUnicodeFonts($this->default_available_fonts ); 
   }

   $this->ColorFlag = ($this->FillColor != $this->TextColor); //Restore ColorFlag as well

   $this->HREF = $saved[ 'HREF' ]; //EDITEI
   $this->underline = $saved[ 'underline' ]; //EDITEI
   $this->strike = $saved[ 'strike' ]; //EDITEI
   $this->SUP = $saved[ 'SUP' ]; //EDITEI
   $this->SUB = $saved[ 'SUB' ]; //EDITEI
   $this->LineWidth = $saved[ 'linewidth' ]; //EDITEI
   $this->DrawColor = $saved[ 'drawcolor' ]; //EDITEI
   $this->outline_on = $saved[ 'is_outline' ]; //EDITEI
   $this->outlineparam = $saved[ 'outlineparam' ];

   $this->toupper = $saved[ 'toupper' ];
   $this->tolower = $saved[ 'tolower' ];

   $this->SetFont($saved[ 'family' ],$saved[ 'style' ].($this->underline ? 'U' : ''),$saved[ 'sizePt' ],false);

   $this->currentfontstyle = $saved[ 'style' ].($this->underline ? 'U' : '');
   $this->currentfontfamily = $saved[ 'family' ];
   $this->currentfontsize = $saved[ 'sizePt' ];
   $this->SetStyle('U',$this->underline);
   $this->SetStyle('B',$saved[ 'B' ]);
   $this->SetStyle('I',$saved[ 'I' ]);

   $this->TextColor = $saved[ 'color' ]; //EDITEI
   $this->FillColor = $saved[ 'bgcolor' ]; //EDITEI
   $this->colorarray = $saved[ 'colorarray' ];
   	$cor = $saved[ 'colorarray' ] ;
   	if ($cor) $this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
   $this->spanbgcolorarray = $saved[ 'bgcolorarray' ];
   	$cor = $saved[ 'bgcolorarray' ] ;
   	if ($cor) $this->SetFillColor($cor['R'],$cor['G'],$cor['B']);
}



// mPDF 3.0
// Used when ColActive for tables - updated to return first block with background fill OR borders
function GetFirstBlockFill() {
	// Returns the first blocklevel that uses a bgcolor fill
	$startfill = 0;
	for ($i=1;$i<=$this->blklvl;$i++) {
		if ($this->blk[$i]['bgcolor'] || $this->blk[$i]['border_left']['w'] || $this->blk[$i]['border_right']['w']  || $this->blk[$i]['border_top']['w']  || $this->blk[$i]['border_bottom']['w']  ) {
			$startfill = $i;
			break;
		}
	}
	return $startfill;
}

function SetBlockFill($blvl) {
	if ($this->blk[$blvl]['bgcolor']) {
		$this->SetFillColor($this->blk[$blvl]['bgcolorarray']['R'],$this->blk[$blvl]['bgcolorarray']['G'],$this->blk[$blvl]['bgcolorarray']['B']);
		return 1;
	}
	else {
		$this->SetFillColor(255);
		return 0;
	}
}


//-------------------------FLOWING BLOCK------------------------------------//
//EDITEI some things (added/changed)                                        //
//The following functions were originally written by Damon Kohler           //
//--------------------------------------------------------------------------//

function saveFont()
{
   $saved = array();
   $saved[ 'family' ] = $this->FontFamily;
   $saved[ 'style' ] = $this->FontStyle;
   $saved[ 'sizePt' ] = $this->FontSizePt;
   $saved[ 'size' ] = $this->FontSize;
   $saved[ 'curr' ] = &$this->CurrentFont;
   $saved[ 'color' ] = $this->TextColor; //EDITEI
   $saved[ 'spanbgcolor' ] = $this->spanbgcolor; //EDITEI
   $saved[ 'spanbgcolorarray' ] = $this->spanbgcolorarray; //EDITEI
   $saved[ 'HREF' ] = $this->HREF; //EDITEI
   $saved[ 'underline' ] = $this->underline; //EDITEI
   $saved[ 'strike' ] = $this->strike; //EDITEI
   $saved[ 'SUP' ] = $this->SUP; //EDITEI
   $saved[ 'SUB' ] = $this->SUB; //EDITEI
   $saved[ 'linewidth' ] = $this->LineWidth; //EDITEI
   $saved[ 'drawcolor' ] = $this->DrawColor; //EDITEI
   $saved[ 'is_outline' ] = $this->outline_on; //EDITEI
   $saved[ 'outlineparam' ] = $this->outlineparam;
   return $saved;
}

function restoreFont( $saved, $write=true)
{

   $this->FontFamily = $saved[ 'family' ];
   $this->FontStyle = $saved[ 'style' ];
   $this->FontSizePt = $saved[ 'sizePt' ];
   $this->FontSize = $saved[ 'size' ];
   $this->CurrentFont = &$saved[ 'curr' ];
   $this->TextColor = $saved[ 'color' ]; //EDITEI
   $this->spanbgcolor = $saved[ 'spanbgcolor' ]; //EDITEI
   $this->spanbgcolorarray = $saved[ 'spanbgcolorarray' ]; //EDITEI
   $this->ColorFlag = ($this->FillColor != $this->TextColor); //Restore ColorFlag as well
   $this->HREF = $saved[ 'HREF' ]; //EDITEI
   $this->underline = $saved[ 'underline' ]; //EDITEI
   $this->strike = $saved[ 'strike' ]; //EDITEI
   $this->SUP = $saved[ 'SUP' ]; //EDITEI
   $this->SUB = $saved[ 'SUB' ]; //EDITEI
   $this->LineWidth = $saved[ 'linewidth' ]; //EDITEI
   $this->DrawColor = $saved[ 'drawcolor' ]; //EDITEI
   $this->outline_on = $saved[ 'is_outline' ]; //EDITEI
   $this->outlineparam = $saved[ 'outlineparam' ];
   if ($write) { 
   	$this->SetFont($saved[ 'family' ],$saved[ 'style' ].($this->underline ? 'U' : ''),$saved[ 'sizePt' ],true,true);	// force output
	$fontout = (sprintf('BT /F%d %.3f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['Font'] != $fontout || $this->keep_block_together)) { $this->_out($fontout); }
	$this->pageoutput[$this->page]['Font'] = $fontout;
   }
   else 
   	$this->SetFont($saved[ 'family' ],$saved[ 'style' ].($this->underline ? 'U' : ''),$saved[ 'sizePt' ]);
}

function newFlowingBlock( $w, $h, $a = '', $is_table = false, $is_list = false, $blockstate = 0, $newblock=true )
{
   if (!$a) { $a = $this->defaultAlign; }
   // cell width in points

   $this->flowingBlockAttr[ 'width' ] = ($w * $this->k);
   // line height in user units
   $this->flowingBlockAttr[ 'is_table' ] = $is_table;
   $this->flowingBlockAttr[ 'is_list' ] = $is_list;
   $this->flowingBlockAttr[ 'height' ] = $h;
   $this->flowingBlockAttr[ 'lineCount' ] = 0;
   $this->flowingBlockAttr[ 'align' ] = $a;
   $this->flowingBlockAttr[ 'font' ] = array();
   $this->flowingBlockAttr[ 'content' ] = array();
   $this->flowingBlockAttr[ 'contentWidth' ] = 0;
   $this->flowingBlockAttr[ 'blockstate' ] = $blockstate;

   $this->flowingBlockAttr[ 'newblock' ] = $newblock;
   $this->flowingBlockAttr[ 'valign' ] = 'M';
}

function finishFlowingBlock($endofblock=false)
{
   $currentx = $this->x;
   //prints out the last chunk
   $is_table = $this->flowingBlockAttr[ 'is_table' ];
   $is_list = $this->flowingBlockAttr[ 'is_list' ];
   $maxWidth =& $this->flowingBlockAttr[ 'width' ];
   $lineHeight =& $this->flowingBlockAttr[ 'height' ];
   $align =& $this->flowingBlockAttr[ 'align' ];
   $content =& $this->flowingBlockAttr[ 'content' ];
   $font =& $this->flowingBlockAttr[ 'font' ];
   $contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
   $lineCount =& $this->flowingBlockAttr[ 'lineCount' ];
   $valign =& $this->flowingBlockAttr[ 'valign' ];
   $blockstate = $this->flowingBlockAttr[ 'blockstate' ];

   $newblock = $this->flowingBlockAttr[ 'newblock' ];



	//*********** BLOCK BACKGROUND COLOR *****************//
	if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
		// mPDF 3.0 - Tiling Patterns
		$fill = 0;
//		$fill = 1;
//		$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
//		$this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
	}
	else {
		$this->SetFillColor(255);
		$fill = 0;
	}

	// set normal spacing
	// WORD SPACING
	if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
	$this->ws=0;
	if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
	$this->charspacing=0;

	// the amount of space taken up so far in user units
	$usedWidth = 0;

	// COLS
	$oldcolumn = $this->CurrCol;

	// mPDF 2.1
	if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }

	// Print out each chunk

	// Edited mPDF 2.0
	if ($is_table) { 
		$ipaddingL = 0; 
		$ipaddingR = 0; 
		$paddingL = 0;
		$paddingR = 0;
	} 
	else { 
		$ipaddingL = $this->blk[$this->blklvl]['padding_left']; 
		$ipaddingR = $this->blk[$this->blklvl]['padding_right']; 
		$paddingL = ($ipaddingL * $this->k); 
		$paddingR = ($ipaddingR * $this->k);
		$this->cMarginL =  $this->blk[$this->blklvl]['border_left']['w'];
		$this->cMarginR =  $this->blk[$this->blklvl]['border_right']['w'];

		// Added mPDF 3.0 Float DIV
		$fpaddingR = 0;
		$fpaddingL = 0;
		if (count($this->floatDivs)) {
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
			if ($r_exists) { $fpaddingR = $r_width; }
			if ($l_exists) { $fpaddingL = $l_width; }
		}

		// mPDF 2.4 Float Images
		$usey = $this->y + 0.002;
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) ) { 
			$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
		}
		// If float exists at this level
		if ($usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) { $fpaddingR += $this->floatmargins['R']['w']; }
		if ($usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) { $fpaddingL += $this->floatmargins['L']['w']; }
	}

		if ($is_list && is_array($this->bulletarray) && $this->list_lineheight[$this->listlvl][$this->bulletarray['occur']]) { 
			$this->lineheight_correction = $this->list_lineheight[$this->listlvl][$this->bulletarray['occur']]; 
		} 
		else if ($is_table) {
			$this->lineheight_correction = $this->table_lineheight; 
		}
		else if ($this->blk[$this->blklvl]['line_height']) {
			$this->lineheight_correction = $this->blk[$this->blklvl]['line_height']; 
		} 
		else {
			$this->lineheight_correction = $this->default_lineheight_correction; 
		}

		//  correct lineheight to maximum fontsize
		$maxlineHeight = 0;
		$maxfontsize = 0;
		foreach ( $content as $k => $chunk )
		{
              $this->restoreFont( $font[ $k ],false );
		  if ($this->objectbuffer[$k]) { 
			$maxlineHeight = max($maxlineHeight,$this->objectbuffer[$k]['OUTER-HEIGHT']);
		  }
              else { 
			// mPDF 2.5 Soft Hyphen
			if ($this->is_MB) {
			      $content[$k] = $chunk = str_replace("\xc2\xad",'',$chunk ); 
			}
			// mPDF 3.0 Soft Hyphens chr(173)
			else if ($this->FontFamily!='symbol' && $this->FontFamily!='zapfdingbats') {
			      $content[$k] = $chunk = str_replace($this->chrs[173],'',$chunk );
			}
			// Special case of sub/sup carried over on its own to last line
			if (($this->SUB || $this->SUP) && count($content)==1) { $actfs = $this->FontSize*100/55; } // 55% is font change for sub/sup
			else { $actfs = $this->FontSize; }
			$maxlineHeight = max($maxlineHeight,$actfs * $this->lineheight_correction ); 
			$maxfontsize = max($maxfontsize,$actfs);
		  }
		}

		// mPDF 2.1 Check Bullet fontsize for List
		if ($is_list && is_array($this->bulletarray)) {
	  		$actfs = $this->bulletarray['fontsize'];
			$maxlineHeight = max($maxlineHeight,$actfs * $this->lineheight_correction ); 
			$maxfontsize = max($maxfontsize,$actfs);
		}


		$lineHeight = $maxlineHeight;
		$this->linemaxfontsize = $maxfontsize;

		// Get PAGEBREAK TO TEST for height including the bottom border/padding
		$check_h = max($this->divheight,$lineHeight);

		// mPDF 2.1
		if ($this->blklvl > 0 && !$is_table) { 
		   if ($endofblock && $blockstate > 1) { 
			if ($this->blk[$this->blklvl]['page_break_after_avoid']) {  $check_h += $lineHeight; }
			$check_h += ($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']);
		   }
		   if (($newblock && ($blockstate==1 || $blockstate==3) && $lineCount == 0) || ($endofblock && $blockstate > 1 && $lineCount == 0)) { 
			$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
		   }
		}


		// PAGEBREAK
		/*'If' below used in order to fix "first-line of other page with justify on" bug*/
		if($this->y+$check_h > $this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak()) {
     	     		$bak_x=$this->x;//Current X position
			// WORD SPACING
			$ws=$this->ws;//Word Spacing
			if($this->ws>0) {
				$this->ws=0;
				$this->_out('BT 0 Tw ET'); 
			}
			$charspacing=$this->charspacing;//Character Spacing
			if($charspacing>0) {
				$this->charspacing=0;
				$this->_out('BT 0 Tc ET'); 
			}

		      $this->AddPage($this->CurOrientation);

		      $this->x=$bak_x;
			// Added to correct for OddEven Margins
			$currentx += $this->MarginCorrection;
			$this->x += $this->MarginCorrection;

			// WORD SPACING
			if($ws>0) {
				$this->ws=$ws;
				$this->_out(sprintf('BT %.3f Tw ET',$ws)); 
			}
			if($charspacing>0) {
				$this->charspacing=$charspacing;
				$this->_out(sprintf('BT %.3f Tc ET',$charspacing));//add-on 
			}
		}

		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			$currentx += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);

			$oldcolumn = $this->CurrCol;
		}

		// mPDF 2.1
		if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }

		// TOP MARGIN
		if ($newblock && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['margin_top']) && $lineCount == 0 && !$is_table && !$is_list) { 
			$this->DivLn($this->blk[$this->blklvl]['margin_top'],$this->blklvl-1,true,$this->blk[$this->blklvl]['margin_collapse']); 
			// mPDF 2.1
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }
		}

		if ($newblock && ($blockstate==1 || $blockstate==3) && $lineCount == 0 && !$is_table && !$is_list) { 
			$this->blk[$this->blklvl]['y0'] = $this->y;
			$this->blk[$this->blklvl]['startpage'] = $this->page;
			// mPDF 2.1
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }
		}

	// ADDED for Paragraph_indent
	$WidthCorrection = 0;
	if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && (!$is_list) && ($align != 'C')) { 
		$WidthCorrection = ($this->blk[$this->blklvl]['text_indent']*$this->k); 
	} 


	// PADDING and BORDER spacing/fill
	if (($newblock) && ($blockstate==1 || $blockstate==3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 0) && (!$is_table) && (!$is_list)) { 
			// mPDF 3.0 Also does border when Columns active
			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'],-3,true,false,1); 
			// mPDF 2.1
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }
			$this->x = $currentx;
	}


	// Added mPDF 3.0 Float DIV
	$fpaddingR = 0;
	$fpaddingL = 0;
	if (count($this->floatDivs)) {
		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
		if ($r_exists) { $fpaddingR = $r_width; }
		if ($l_exists) { $fpaddingL = $l_width; }
	}

	// mPDF 2.4 Float Images
	$usey = $this->y + 0.002;
	if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) ) { 
		$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
	}
	// If float exists at this level
	if ($usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) { $fpaddingR += $this->floatmargins['R']['w']; }
	if ($usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) { $fpaddingL += $this->floatmargins['L']['w']; }

	if ($content) {
		// mPDF 2.4 Float Images
		$empty = $maxWidth - $WidthCorrection - $contentWidth - (($this->cMarginL+$this->cMarginR)* $this->k) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * $this->k) ) ;
		$empty /= $this->k;

		// In FinishFlowing Block no lines are justified as it is always last line
		// but if orphansAllowed have allowed content width to go over max width, use J charspacing to compress line
		// JUSTIFICATION J - NOT!
		$nb_carac = 0;
		$nb_spaces = 0;
		// if it's justified, we need to find the char/word spacing (or if orphans have allowed length of line to go over the maxwidth)
		// If "orphans" in fact is just a final space - ignore this
		if (($contentWidth > $maxWidth) && ($content[count($content)-1] != ' ') )  {
 		  // WORD SPACING
			foreach ( $content as $k => $chunk ) {
		  		if (!$this->objectbuffer[$k]) {
					// mPDF 2.5 Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
					if ($this->is_MB) {
					      $chunk = str_replace($this->chrs[194].$this->chrs[160],$this->chrs[32],$chunk ); 
					}
					else {
					      $chunk = str_replace($this->chrs[160],$this->chrs[32],$chunk );
					}
					$nb_carac += mb_strlen( $chunk, $this->mb_encoding ) ;  
					$nb_spaces += mb_substr_count( $chunk,' ', $this->mb_encoding ) ;  
				}
			}
			// mPDF 2.4 Float Images
			list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,($maxWidth-$contentWidth-$WidthCorrection-(($this->cMarginL+$this->cMarginR)*$this->k)-($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * $this->k) )));
			if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
			$this->charspacing=$charspacing;
			if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
			$this->ws=$ws;
			// mPDF 2.4 Float Images
			$empty = $maxWidth - $WidthCorrection - $contentWidth - (($this->cMarginL+$this->cMarginR)* $this->k) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * $this->k) ) - ( $this->charspacing * $nb_carac) - ( $this->ws * $nb_spaces);
			$empty /= $this->k;
		}

		$arraysize = count($content);

		// mPDF 2.4 Float Images
		$margins = ($this->cMarginL+$this->cMarginR) + ($ipaddingL+$ipaddingR + $fpaddingR + $fpaddingR );

		if (!$is_table) { $this->DivLn($lineHeight,$this->blklvl,false); }	// false -> don't advance y

		// DIRECTIONALITY RTL
		$all_rtl = false;
		$contains_rtl = false;
		// mPDF 2.2 - variable name changed to lowercase first letter
   		if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->biDirectional)))  { 
			$all_rtl = true;
			foreach ( $content as $k => $chunk ) {
				// mPDF 2.3
				$reversed = $this->magic_reverse_dir($chunk, false);
				if ($reversed > 0) { $contains_rtl = true; }
				if ($reversed < 2) { $all_rtl = false; }
				$content[$k] = $chunk;
			}
			if ($this->directionality == 'rtl') { 
				if ($contains_rtl) {
					$content = array_reverse($content,false);
				}
			}
			// mPDF 2.2 - variable name changed to lowercase first letter
			else if (($this->directionality == 'ltr') && ($this->biDirectional)) { 
				if ($all_rtl) {
					$content = array_reverse($content,false);
				}
			}
		}


		// mPDF 2.4 Float Images
		$this->x = $currentx + $this->cMarginL + $ipaddingL + $fpaddingL ;
		if ($align == 'R') { $this->x += $empty; }
		else if ($align == 'J')	{
			if ($this->directionality == 'rtl' && $contains_rtl) { $this->x += $empty; }
			else if ($this->directionality == 'ltr' && $all_rtl) { $this->x += $empty; }
		}
		else if ($align == 'C') { $this->x += ($empty / 2); }

		// Paragraph INDENT
		$WidthCorrection = 0; 
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && (!$is_list) && ($align !='C')) { 
		  	$this->x += $this->blk[$this->blklvl]['text_indent']; 
		}


          foreach ( $content as $k => $chunk )
          {

			// FOR IMAGES
		if (($this->directionality=='rtl' && $contains_rtl) || $all_rtl) { $dirk = $arraysize-1 - $k ; } else { $dirk = $k; }

		if ($this->objectbuffer[$dirk]) {
			$xadj = $this->x - $this->objectbuffer[$dirk]['OUTER-X'] ; 
			$this->objectbuffer[$dirk]['OUTER-X'] += $xadj;
			$this->objectbuffer[$dirk]['BORDER-X'] += $xadj;
			$this->objectbuffer[$dirk]['INNER-X'] += $xadj;
			if ($valign == 'M' || $valign == '') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y'])+($lineHeight - $this->objectbuffer[$dirk]['OUTER-HEIGHT'])/2;
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}
			else if ($valign == 'B') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y'])+($lineHeight - $this->objectbuffer[$dirk]['OUTER-HEIGHT']);
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}
			else if ($valign == 'T') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y']);
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}
		}



			// DIRECTIONALITY RTL
			if ((($this->directionality == 'rtl') && ($contains_rtl )) || ($all_rtl )) { $this->restoreFont( $font[ $arraysize-1 - $k ] ); }
			else { $this->restoreFont( $font[ $k ] ); }
	 		//*********** SPAN BACKGROUND COLOR *****************//
			if ($this->spanbgcolor) { 
				$cor = $this->spanbgcolorarray;
				$this->SetFillColor($cor['R'],$cor['G'],$cor['B']);
				$save_fill = $fill; $spanfill = 1; $fill = 1;
			}
			// WORD SPACING
		      $stringWidth = $this->GetStringWidth($chunk ) + ( $this->charspacing * mb_strlen($chunk,$this->mb_encoding ) / $this->k )  
				+ ( $this->ws * mb_substr_count($chunk,' ',$this->mb_encoding ) / $this->k );
			if ($this->objectbuffer[$dirk]) { $stringWidth = $this->objectbuffer[$dirk]['OUTER-WIDTH']; }

              if ($k == $arraysize-1) $this->Cell( $stringWidth, $lineHeight, $chunk, '', 1, '', $fill, $this->HREF , $currentx,0,0,$valign ); //mono-style line or last part (skips line)
              else $this->Cell( $stringWidth, $lineHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0,0,$valign );//first or middle part


	 		//*********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR *****************//
			if ($spanfill) { 
				$fill = $save_fill; $spanfill = 0; 
				if ($fill) { $this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']); }
			}
          }

	$this->printobjectbuffer($is_table);

	$this->objectbuffer = array();

	// LIST BULLETS/NUMBERS
	if ($is_list && is_array($this->bulletarray) && ($lineCount == 0) ) {
	  $bull = $this->bulletarray;
	  $this->restoreInlineProperties($this->InlineProperties['LIST'][$bull['level']][$bull['occur']]);
	  // mPDF 2.1
	  if ($this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]) { $this->restoreInlineProperties($this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]); }
	  if ($bull['font'] == 'zapfdingbats') {
		$this->bullet = true;
		$this->SetFont('zapfdingbats','',$this->FontSizePt/2.5);
	  }
	  else { $this->SetFont($this->FontFamily,$this->FontStyle,$this->FontSizePt,true,true); }	// force output
        //Output bullet
	  $this->x = $currentx + $bull['x'];
	  $this->y -= $lineHeight;
	  // mPDF 2.1
//        $this->Cell($bull['w'],$bull['h'],$bull['txt'],'','',$bull['align']);
        $this->Cell($bull['w'], $lineHeight,$bull['txt'],'','',$bull['align']);
	  if ($bull['font'] == 'zapfdingbats') {
		$this->bullet = false;
	  }
	  $this->x = $currentx;	// Reset
	  $this->y += $lineHeight;


	  // mPDF 2.1
	  if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }

	  $this->restoreFont( $savedFont );
	  $font = array( $savedFont );
	  $this->bulletarray = array();	// prevents repeat of bullet/number if <li>....<br />.....</li>
	}


	}	// END IF CONTENT

	// mPDF 2.4 Float Images (for skipline)
	// Update values if set to skipline
	if ($this->floatmargins) { $this->_advanceFloatMargins(); }


	// mPDF 2.4 Float Images
	if ($endofblock && $blockstate>1) { 
		// If float exists at this level
		if ($this->y < $this->floatmargins['R']['y1'] || $this->y < $this->floatmargins['L']['y1']) { 
			$drop = max($this->floatmargins['R']['y1'],$this->floatmargins['L']['y1']) - $this->y;
			$this->DivLn($drop); 
			$this->x = $currentx;
		}
	}


	// PADDING and BORDER spacing/fill
	if (($endofblock) && ($blockstate > 1) && (($this->blk[$this->blklvl]['padding_bottom']) || ($this->blk[$this->blklvl]['border_bottom'])) && (!$is_table) && (!$is_list)) { 
			// mPDF 3.0 Also does border when Columns active
			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'],-3,true,false,2); 
			$this->x = $currentx;

			// mPDF 2.1
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }

	}

	// SET Bottom y1 of block (used for painting borders)
	if (($endofblock) && ($blockstate > 1) && (!$is_table) && (!$is_list)) { 
		$this->blk[$this->blklvl]['y1'] = $this->y;
	}

	// BOTTOM MARGIN
	if (($endofblock) && ($blockstate > 1) && ($this->blk[$this->blklvl]['margin_bottom']) && (!$is_table) && (!$is_list)) { 
		if($this->y+$this->blk[$this->blklvl]['margin_bottom'] < $this->PageBreakTrigger and !$this->InFooter) {
		  $this->DivLn($this->blk[$this->blklvl]['margin_bottom'],$this->blklvl-1,true,$this->blk[$this->blklvl]['margin_collapse']); 
		  // mPDF 2.1
		  if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }
		}
	}

	// Reset lineheight
	$lineHeight = $this->divheight;
}





function printobjectbuffer($is_table=false) {
		// mPDF 2.3
		if ($is_table && $this->shrin_k > 1) { $k = $this->shrin_k; } 
		else { $k = 1; }
		$save_y = $this->y;
		$save_x = $this->x;
		// mPDF 2.1 (was incorrectly ->Font;)
		$save_currentfontfamily = $this->FontFamily;
		$save_currentfontsize = $this->FontSizePt;
		$save_currentfontstyle = $this->FontStyle.($this->underline ? 'U' : '');
		if ($this->directionality == 'rtl') { $rtlalign = 'R'; } else { $rtlalign = 'L'; }
		foreach ($this->objectbuffer AS $ib => $objattr) { 
		 // mPDF 2.2 Annotations
		   if ($objattr['type'] == 'annot') {
			if ($objattr['POS-X']) { $x = $objattr['POS-X']; }
			else if ($this->annotMargin<>0) { $x = -$objattr['OUTER-X']; }
			else { $x = $objattr['OUTER-X']; }
			if ($objattr['POS-Y']) { $y = $objattr['POS-Y']; }
			else { $y = $objattr['OUTER-Y'] - $this->FontSize/2; }
			// Create a dummy entry in the _out/columnBuffer with position sensitive data,
			// linking $y-1 in the Columnbuffer with entry in $this->columnAnnots
			// and when columns are split in length will not break annotation from current line
			$this->y = $y-1;
			$this->x = $x-1;
			$this->Line($x-1,$y-1,$x-1,$y-1);
			$this->Annotation($objattr['CONTENT'], $x , $y , $objattr['ICON'], $objattr['AUTHOR'], $objattr['SUBJECT'], $objattr['OPACITY'], $objattr['COLOR']);
		   }
    		   // mPDF 3.0
		   else if ($objattr['type'] == 'bookmark' || $objattr['type'] == 'indexentry' || $objattr['type'] == 'toc') {
			$x = $objattr['OUTER-X']; 
			$y = $objattr['OUTER-Y'];
			$this->y = $y - $this->FontSize/2;
			$this->x = $x;
			if ($objattr['type'] == 'bookmark' ) { $this->Bookmark($objattr['CONTENT'],$objattr['bklevel'] ,$y - $this->FontSize); }
			if ($objattr['type'] == 'indexentry') { $this->IndexEntry($objattr['CONTENT']); }
			if ($objattr['type'] == 'toc') { $this->TOC_Entry($objattr['CONTENT'], $objattr['toclevel'], $objattr['toc_id']); }
		   }
		   else { 
			$y = $objattr['OUTER-Y'];
			$x = $objattr['OUTER-X'];
			$w = $objattr['OUTER-WIDTH'];
			$h = $objattr['OUTER-HEIGHT'];
			$texto = $objattr['text'];
			$this->y = $y;
			$this->x = $x;
			$this->SetFont($objattr['fontfamily'],'',$objattr['fontsize'] );
		   }

// mPDF 3.0 ? doesn't do anything
/*
		// NESTED TABLE
		   if ($objattr['type'] == 'nestedtable') {
      		$this->SetDrawColor($objattr['color']['R'],$objattr['color']['G'],$objattr['color']['B']);
      		switch($objattr['align']) {
      		    case 'C':
      		        $empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
      		        $empty /= 2;
      		        $x += $empty;
      		        break;
      		    case 'R':
      		        $empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
      		        $x += $empty;
      		        break;
      		}
      		$oldlinewidth = $this->LineWidth;
			$this->SetLineWidth($objattr['linewidth']);
			$this->y += ($objattr['linewidth']/2) + $objattr['margin_top'];
			$this->Line($x,$this->y,$x+$objattr['INNER-WIDTH'],$this->y);
			$this->SetLineWidth($oldlinewidth);
			$this->SetDrawColor(0);
		   }
*/

		// HR
		   if ($objattr['type'] == 'hr') {
			$this->SetDrawColor($objattr['color']['R'],$objattr['color']['G'],$objattr['color']['B']);
			switch($objattr['align']) {
      		    case 'C':
      		        $empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
      		        $empty /= 2;
      		        $x += $empty;
     		        	  break;
      		    case 'R':
      		        $empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
      		        $x += $empty;
      		        break;
			}
      		$oldlinewidth = $this->LineWidth;
			$this->SetLineWidth($objattr['linewidth']/$k );
			$this->y += ($objattr['linewidth']/2) + $objattr['margin_top']/$k ;
			$this->Line($x,$this->y,$x+$objattr['INNER-WIDTH'],$this->y);
			$this->SetLineWidth($oldlinewidth);
			$this->SetDrawColor(0);
		   }
		// IMAGE
		   if ($objattr['type'] == 'image') {
			// mPDF 2.3
			if ($objattr['opacity']) { $this->SetAlpha($objattr['opacity']); }
	 		// mPDF 2.2 WMF Images
			if ($objattr['itype']=='wmf') { 
				$sx = $objattr['INNER-WIDTH']*$this->k / $objattr['orig_w'];
				$sy = abs($objattr['INNER-HEIGHT'])*$this->k / abs($objattr['orig_h']);	// mPDF 2.4
				$outstring = sprintf('q %f 0 0 %f %f %f cm /FO%d Do Q', $sx, $sy, $objattr['INNER-X']*$this->k-$sx*$objattr['wmf_x'], (($this->h-$objattr['INNER-Y'])*$this->k)-$sy*$objattr['wmf_y'], $objattr['ID']);
			}
			else { 
				$outstring = sprintf("q %.3f 0 0 %.3f %.3f %.3f cm /I%d Do Q",$objattr['INNER-WIDTH'] *$this->k,$objattr['INNER-HEIGHT'] *$this->k,$objattr['INNER-X'] *$this->k,($this->h-($objattr['INNER-Y'] +$objattr['INNER-HEIGHT'] ))*$this->k,$objattr['ID'] );
			}
			$this->_out($outstring);
			// LINK
			if($objattr['link']) $this->Link($objattr['INNER-X'],$objattr['INNER-Y'],$objattr['INNER-WIDTH'],$objattr['INNER-HEIGHT'],$objattr['link']);
			if ($objattr['BORDER-WIDTH']) { $this->PaintImgBorder($objattr,$is_table); }
			if ($objattr['opacity']) { $this->SetAlpha(1); }
		   }

		// mPDF - 1.4 Active Forms edited all the form elements below
		// TEXT/PASSWORD INPUT
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'TEXT' || $objattr['subtype'] == 'PASSWORD')) {
				$w -= $this->form_element_spacing['input']['outer']['h']*2  /$k ;
				$h -= $this->form_element_spacing['input']['outer']['v']*2 /$k ;
				$this->x += $this->form_element_spacing['input']['outer']['h'] /$k ;
				$this->y += $this->form_element_spacing['input']['outer']['v'] /$k ;
			// Chop texto to max length $w-inner-padding
			while ($this->GetStringWidth($texto) > $w-($this->form_element_spacing['input']['inner']['h']*2)) {
				$texto = mb_substr($texto,0,mb_strlen($texto,$this->mb_encoding)-1,$this->mb_encoding);
			}
			// DIRECTIONALITY
				// mPDF 2.3
			  	$this->SetLineWidth(0.2 /$k );
				$this->magic_reverse_dir($texto, false);
				// mPDF 2.4
				if ($objattr['disabled']) { 
					$this->SetFillColor(225);
					$this->SetTextColor(127);
				}
				else if ($objattr['readonly']) { 
					$this->SetFillColor(225);
					$this->SetTextColor(0);
				}
				else {
					$this->SetFillColor(250);
					$this->SetTextColor(0);
				}
				$this->Cell($w,$h,$texto,1,0,$rtlalign,1,'',0,$this->form_element_spacing['input']['inner']['h'] /$k /*internal text x offset*/,$this->form_element_spacing['input']['inner']['h'] /$k , 'M') ;
				$this->SetFillColor(255);
				// mPDF 2.4
				$this->SetTextColor(0);

		   }
		// SELECT
		   if ($objattr['type'] == 'select') {
			// DIRECTIONALITY
			  // mPDF 2.3
			  $this->magic_reverse_dir($texto, false);
			  $this->SetLineWidth(0.2 /$k );
				// mPDF 2.4
				if ($objattr['disabled']) { 
					$this->SetFillColor(225);
					$this->SetTextColor(127);
				}
				else {
					$this->SetFillColor(250);
					$this->SetTextColor(0);
				}
				$w -= $this->form_element_spacing['select']['outer']['h']*2 /$k ;
				$h -= $this->form_element_spacing['select']['outer']['v']*2 /$k ;
				$this->x += $this->form_element_spacing['select']['outer']['h'] /$k ;
				$this->y += $this->form_element_spacing['select']['outer']['v'] /$k ;
			  $this->Cell($w-($this->FontSize*1.4),$h,$texto,1,0,$rtlalign,1,'',0,$this->form_element_spacing['select']['inner']['h'] /$k /*internal text x offset*/,$this->form_element_spacing['select']['inner']['h'] /$k , 'M') ;
			  $this->SetFillColor(190);
			  $save_font = $this->FontFamily;
           		  $save_currentfont = $this->currentfontfamily;
			  $this->SetFont('zapfdingbats','',0);
			  $this->Cell(($this->FontSize*1.4),$h,$this->chrs[116],1,0,'C',1,'',0,0,0, 'M') ;
			  $this->SetFont($save_font,'',0);
           		  $this->currentfontfamily = $save_currentfont;
			  $this->SetFillColor(255);
			  // mPDF 2.4
			  $this->SetTextColor(0);
		   }


		// INPUT/BUTTON as IMAGE
		   if ($objattr['type'] == 'input' && $objattr['subtype'] == 'IMAGE') {
			$this->y = $objattr['INNER-Y'];
			$this->_out( sprintf("q %.3f 0 0 %.3f %.3f %.3f cm /I%d Do Q",$objattr['INNER-WIDTH'] *$this->k,$objattr['INNER-HEIGHT'] *$this->k,$objattr['INNER-X'] *$this->k,($this->h-($objattr['INNER-Y'] +$objattr['INNER-HEIGHT'] ))*$this->k,$objattr['ID'] ) );
			if ($objattr['BORDER-WIDTH']) { $this->PaintImgBorder($objattr,$is_table); }
		   }




		// BUTTON
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'SUBMIT' || $objattr['subtype'] == 'RESET' || $objattr['subtype'] == 'BUTTON')) {
			   $this->SetLineWidth(0.2 /$k );
			   $this->SetFillColor(190,190,190);
				$w -= $this->form_element_spacing['button']['outer']['h']*2 /$k ;
				$h -= $this->form_element_spacing['button']['outer']['v']*2 /$k ;
				$this->x += $this->form_element_spacing['button']['outer']['h'] /$k ;
				$this->y += $this->form_element_spacing['button']['outer']['v'] /$k ;
			   $this->RoundedRect($this->x, $this->y, $w, $h, 0.5 /$k , 'DF');
				$w -= $this->form_element_spacing['button']['inner']['h']*2 /$k ;
				$h -= $this->form_element_spacing['button']['inner']['v']*2 /$k ;
				$this->x += $this->form_element_spacing['button']['inner']['h'] /$k ;
				$this->y += $this->form_element_spacing['button']['inner']['v'] /$k ;
			   // DIRECTIONALITY
			   // mPDF 2.3
			   $this->magic_reverse_dir($texto, false);
			   $this->Cell($w,$h,$texto,'',0,'C',0,'',0,0,0, 'M') ;
			   $this->SetFillColor(255);
		   }

		// TEXTAREA
		   if ($objattr['type'] == 'textarea') {
			$w -= $this->form_element_spacing['textarea']['outer']['h']*2 /$k ;
			$h -= $this->form_element_spacing['textarea']['outer']['v']*2 /$k ;
         		$this->x += $this->form_element_spacing['textarea']['outer']['h'] /$k ;
         		$this->y += $this->form_element_spacing['textarea']['outer']['v'] /$k ;
			$this->SetLineWidth(0.2 /$k );
			// mPDF 2.4
			if ($objattr['disabled']) { 
				$this->SetFillColor(225);
				$this->SetTextColor(127);
			}
			else if ($objattr['readonly']) { 
				$this->SetFillColor(225);
				$this->SetTextColor(0);
			}
			else {
				$this->SetFillColor(250);
				$this->SetTextColor(0);
			}
			$this->Rect($this->x,$this->y,$w,$h,'DF');
			$w -= $this->form_element_spacing['textarea']['inner']['h']*2 /$k ;
			$this->x += $this->form_element_spacing['textarea']['inner']['h'] /$k ;
			$this->y += $this->form_element_spacing['textarea']['inner']['v'] /$k ;
			$linesneeded = $this->WordWrap($texto,$w);
			if ($linesneeded > $objattr['rows']) { //Too many words inside textarea
				$textoaux = preg_split('/[\n]/u',$texto);
                        $texto = '';
                        for($i=0;$i<$objattr['rows'];$i++) {
                          if ($i == ($objattr['rows']-1)) $texto .= $textoaux[$i];
                          else $texto .= $textoaux[$i] . "\n";
                        }
				$texto = mb_substr($texto,0,mb_strlen($texto,$this->mb_encoding)-4,$this->mb_encoding) . "...";
			}
			if ($texto != '') $this->MultiCell($w,$this->FontSize*$this->textarea_lineheight,$texto,0,'',0,'',$this->directionality,true);
			$this->SetFillColor(255);
			// mPDF 2.4
			$this->SetTextColor(0);
		   }

		// CHECKBOX
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'CHECKBOX')) {
			$iw = $w * 0.7;
			$ih = $h * 0.7;
			$lx = $x + (($w-$iw)/2); 
			$ty = $y + (($h-$ih)/2);
			$rx = $lx + $iw;
			$by = $ty + $ih;
			$this->SetLineWidth(0.2 /$k );
			// mPDF 2.4
			if ($objattr['disabled']) { 
				$this->SetFillColor(225);
				$this->SetDrawColor(127);
			}
			else {
				$this->SetFillColor(250);
				$this->SetDrawColor(0);
			}
			$this->Rect($lx,$ty,$iw,$ih,'DF');
			if ($objattr['checked']) {
				//Round join and cap
				$this->_out('1 J');
				$this->Line($lx,$ty,$rx,$by);
				$this->Line($lx,$by,$rx,$ty);
				//Set line cap style back to square
				$this->_out('2 J');
			}
			$this->SetFillColor(255);
			// mPDF 2.4
			$this->SetDrawColor(0);
		   }
		// RADIO
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'RADIO')) {
				$this->SetLineWidth(0.2 /$k );
				$radius = $this->FontSize *0.35;
				$cx = $x + ($w/2); 
				$cy = $y + ($h/2);
				// mPDF 2.4
				if ($objattr['disabled']) { 
					$this->SetFillColor(127);
					$this->SetDrawColor(127);
				}
				else {
					$this->SetFillColor(0);
					$this->SetDrawColor(0);
				}
				$this->Circle($cx,$cy,$radius,'D');
				if ($objattr['checked']) {
					$this->Circle($cx,$cy,$radius*0.4,'DF');
				}
				$this->SetFillColor(255);
				// mPDF 2.4
				$this->SetDrawColor(0);
		   }
		}
		$this->SetFont($save_currentfontfamily,$save_currentfontstyle,$save_currentfontsize);
		$this->y = $save_y;
		$this->x = $save_x;

		// mPDF 2.1
		unset($content);
}


function WriteFlowingBlock( $s)
{
    $currentx = $this->x; 
    $is_table = $this->flowingBlockAttr[ 'is_table' ];
    $is_list = $this->flowingBlockAttr[ 'is_list' ];
    // width of all the content so far in points
    $contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
    // cell width in points
    $maxWidth =& $this->flowingBlockAttr[ 'width' ];
    $lineCount =& $this->flowingBlockAttr[ 'lineCount' ];
    // line height in user units
    $lineHeight =& $this->flowingBlockAttr[ 'height' ];
    $align =& $this->flowingBlockAttr[ 'align' ];
    $content =& $this->flowingBlockAttr[ 'content' ];
    $font =& $this->flowingBlockAttr[ 'font' ];
    $valign =& $this->flowingBlockAttr[ 'valign' ];
    $blockstate = $this->flowingBlockAttr[ 'blockstate' ];

    $newblock = $this->flowingBlockAttr[ 'newblock' ];

	//*********** BLOCK BACKGROUND COLOR *****************//
	if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
		// mPDF 3.0 - Tiling Patterns
		$fill = 0;
//		$fill = 1;
//		$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
//		$this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
	}
	else {
		$this->SetFillColor(255);
		$fill = 0;
	}


    $font[] = $this->saveFont();
    $content[] = '';

    $currContent =& $content[ count( $content ) - 1 ];

    // where the line should be cutoff if it is to be justified
    $cutoffWidth = $contentWidth;

	$curlyquote = mb_convert_encoding("\xe2\x80\x9e",$this->mb_encoding,'UTF-8');
	$curlylowquote = mb_convert_encoding("\xe2\x80\x9d",$this->mb_encoding,'UTF-8');

	// COLS
	$oldcolumn = $this->CurrCol;

	// mPDF 2.1
	if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }

   // Edited mPDF 2.0
   if ($is_table) { 
	$ipaddingL = 0; 
	$ipaddingR = 0; 
	$paddingL = 0; 
	$paddingR = 0; 
	$cpaddingadjustL = 0;
	$cpaddingadjustR = 0;
   } 
   else { 
		$ipaddingL = $this->blk[$this->blklvl]['padding_left']; 
		$ipaddingR = $this->blk[$this->blklvl]['padding_right']; 
		$paddingL = ($ipaddingL * $this->k); 
		$paddingR = ($ipaddingR * $this->k); 
		$this->cMarginL =  $this->blk[$this->blklvl]['border_left']['w'];
		$cpaddingadjustL = -$this->cMarginL;
		$this->cMarginR =  $this->blk[$this->blklvl]['border_right']['w'];
		$cpaddingadjustR = -$this->cMarginR;

		// Added mPDF 3.0 Float DIV
		$fpaddingR = 0;
		$fpaddingL = 0;
		if (count($this->floatDivs)) {
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
			if ($r_exists) { $fpaddingR = $r_width; }
			if ($l_exists) { $fpaddingL = $l_width; }
		}

		// mPDF 2.4 Float Images
		$usey = $this->y + 0.002;
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) ) { 
			$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
		}
		// If float exists at this level
		if ($usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) { $fpaddingR += $this->floatmargins['R']['w']; }
		if ($usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) { $fpaddingL += $this->floatmargins['L']['w']; }
   }

     //OBJECTS - IMAGES & FORM Elements (NB has already skipped line/page if required - in printbuffer)
	// mPDF 3.0
      if (substr($s,0,3) == "\xbb\xa4\xac") { //identifier has been identified!
		$sccontent = split("\xbb\xa4\xac",$s,2);
		$sccontent = split(",",$sccontent[1],2);
		foreach($sccontent as $scvalue) {
			$scvalue = split("=",$scvalue,2);
			$specialcontent[$scvalue[0]] = $scvalue[1];
		}
		$objattr = unserialize($specialcontent['objattr']);
		$h_corr = 0; 
		if ($is_table) {
			$maximumW = ($maxWidth/$this->k) - ($this->cellPaddingL + $this->cMarginL + $this->cellPaddingR + $this->cMarginR); 
		}
		else {
			if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) && (!$is_table)) { $h_corr = $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w']; }
			// mPDF 2.4 Float Images
			$maximumW = ($maxWidth/$this->k) - ($this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['border_right']['w'] + $fpaddingL + $fpaddingR ); 
		}
		// mPDF 2.4 Float Images
		$objattr = $this->inlineObject($objattr['type'],$this->lMargin + $fpaddingL + ($contentWidth/$this->k),($this->y + $h_corr), $objattr, $this->lMargin,($contentWidth/$this->k),$maximumW,$lineHeight,true,$is_table);

		// SET LINEHEIGHT for this line ================ RESET AT END
		$lineHeight = MAX($lineHeight,$objattr['OUTER-HEIGHT']);
		$this->objectbuffer[count($content)-1] = $objattr;
		$valign = $objattr['vertical-align'];
		$contentWidth += ($objattr['OUTER-WIDTH'] * $this->k);
		return;
	}


   if ($this->is_MB && !$this->usingCoreFont) {
	$tmp = mb_strlen( $s, $this->mb_encoding );
   }
   else {
	$tmp = strlen( $s );
   }

   $orphs = 0; 
   $check = 0;


   // for every character in the string
   for ( $i = 0; $i < $tmp; $i++ )  {

	// extract the current character
	// get the width of the character in points
	if ($this->is_MB && !$this->usingCoreFont) {
	      $c = mb_substr($s,$i,1,$this->mb_encoding );
		$cw = ($this->GetStringWidth($c) * $this->k);
	}
	else {
       	$c = substr($s,$i,1);
		// mPDF 2.5 Soft Hyphens
		// mPDF 3.0 Soft Hyphens chr(173)
		if ($c == chr(173) && ($this->FontFamily!='symbol' && $this->FontFamily!='zapfdingbats')) { $cw = 0;  }
		else { $cw = $this->CurrentFont[ 'cw' ][ $c ] * ( $this->FontSizePt / 1000 ); }
	}
	if ($c==' ') { $check = 1; }

	// CHECK for ORPHANS - edited mPDF 1.1 to add brackets - v2.5 added CJK . and ,
	else if ($c=='.' || $c==',' || $c==')' || $c==';' || $c==':' || $c=='!' || $c=='?'|| $c=='"' || $c==$curlyquote || $c==$curlylowquote || $c=="\xef\xbc\x8c" || $c=="\xe3\x80\x82")  {$check++; }
	else { $check = 0; }

	// There's an orphan '. ' or ', ' or <sup>32</sup> about to be cut off at the end of line
	if($check==1) {
		$currContent .= $c;
		$cutoffWidth = $contentWidth;
		$contentWidth += $cw;
		continue;
	}
	if(($this->SUP || $this->SUB) && ($orphs < $this->orphansAllowed)) {	// ? disable orphans in table if  borders used
		$currContent .= $c;
		$cutoffWidth = $contentWidth;
		$contentWidth += $cw;
		$orphs++;
		continue;
	}
	else { $orphs = 0; }

	// ADDED for Paragraph_indent
	$WidthCorrection = 0; 
	if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && (!$is_list) && ($align != 'C')) { 
		$WidthCorrection = ($this->blk[$this->blklvl]['text_indent']*$this->k); 
	} 

	// Added mPDF 3.0 Float DIV
	$fpaddingR = 0;
	$fpaddingL = 0;
	if (count($this->floatDivs)) {
		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
		if ($r_exists) { $fpaddingR = $r_width; }
		if ($l_exists) { $fpaddingL = $l_width; }
	}

	// mPDF 2.4 Float Images
	$usey = $this->y + 0.002;
	if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) ) { 
		$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
	}

	// If float exists at this level
	if ($usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) { $fpaddingR += $this->floatmargins['R']['w']; }
	if ($usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) { $fpaddingL += $this->floatmargins['L']['w']; }



       // try adding another char
	// mPDF 2.4 Float Images
	if (( $contentWidth + $cw > $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*$this->k) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * $this->k) ) +  0.001))  {// 0.001 is to correct for deviations converting mm=>pts
		// it won't fit, output what we already have
		$lineCount++;
 
		// contains any content that didn't make it into this print
		$savedContent = '';
		$savedFont = array();


		// mPDF 2.3		// Change &#x3000; = CJK space
		if ($this->isCJK) {
			// mPDF 2.5 changed to utf8
			if ($this->currentfontfamily == 'sjis' && preg_match("/( |\xe3\x80\x80)/",$currContent) ) { $words = preg_split( "/( |\xe3\x80\x80)/", $currContent ); }
			// If CJK break at space if in ASCII string else break after current character
			else if ($this->ords[$c]>127) { $words = array(); $words[] = $currContent; }
			else { $words = explode( ' ', $currContent ); }
		}
		// cut off and save any partial words at the end of the string
		else { 
			$words = explode( ' ', $currContent ); 
			///////////////////
			// HYPHENATION
			// mPDF 2.5 Soft Hyphens
			// Soft hyphs
			$currWord = $words[count($words)-1] ;
			$success = false;
			// mPDF 3.0 Soft Hyphens chr(173)
			if (($this->is_MB && preg_match("/\xc2\xad/",$currWord)) || (!$this->is_MB && preg_match("/".chr(173)."/",$currWord) && ($this->FontFamily!='symbol' && $this->FontFamily!='zapfdingbats')) ) {
				$rem = $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*$this->k) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * $this->k) );
				list($success,$pre,$post,$prelength) = $this->softHyphenate($currWord, (($rem-$cutoffWidth)/$this->k -$this->GetStringWidth(" ")) );
			}
			// mPDF 2.5 Automatic hyphens
			if (!$success && ($this->hyphenate || ($this->hyphenateTables && $is_table))) { 
				// Look ahead to get current word
				for($ac = $i; $ac<(mb_strlen($s)-1); $ac++) {
					$addc = mb_substr($s,$ac,1,$this->mb_encoding );
					if ($addc == ' ') { break; }
					$currWord .= $addc;
				}
				$rem = $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*$this->k) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * $this->k) );
				list($success,$pre,$post,$prelength) = $this->hyphenateWord($currWord, (($rem-$cutoffWidth)/$this->k -$this->GetStringWidth(" ")) );
			}
			if ($success) { 
				$already = array_pop( $words );
				$forward = mb_substr($already,$prelength,mb_strlen($already, $this->mb_encoding), $this->mb_encoding);
				$words[] = $pre.'-';
				$words[] = $forward;
				$currContent = mb_substr($currContent,0,mb_strlen($currContent, $this->mb_encoding)-mb_strlen($post, $this->mb_encoding), $this->mb_encoding) . '-';
			}
		}


		// if it looks like we didn't finish any words for this chunk
		if ( count( $words ) == 1 ) {
		  // TO correct for error when word too wide for page - but only when one long word from left to right margin
		  if (count($content) == 1 && $currContent != ' ') {
			// Edited mPDF 2.0
			$lastContent = $words[0]; 
			$savedFont = $this->saveFont();
			// replace the current content with the cropped version
			$currContent = mb_rtrim( $lastContent, $this->mb_encoding );
		  }
		  else {

			/* this was the original with no if-else */
			// save and crop off the content currently on the stack
			$savedContent = array_pop( $content );
			$savedFont = array_pop( $font );
			// trim any trailing spaces off the last bit of content
			$currContent =& $content[ count( $content ) - 1 ];
			$currContent = mb_rtrim( $currContent, $this->mb_encoding );
		  }
		}
		else {	// otherwise, we need to find which bit to cut off
             $lastContent = '';
		  // mPDF 3.0
              for ( $w = 0; $w < count( $words ) - 1; $w++) { $lastContent .= $words[ $w ]." "; }
              $savedContent = $words[ count( $words ) - 1 ];
              $savedFont = $this->saveFont();
              // replace the current content with the cropped version
              $currContent = mb_rtrim( $lastContent, $this->mb_encoding );
		}

		// Set Current lineheight (correction factor)
		if ($is_list && is_array($this->bulletarray) && $this->list_lineheight[$this->listlvl][$this->bulletarray['occur']]) { 
			$this->lineheight_correction = $this->list_lineheight[$this->listlvl][$this->bulletarray['occur']]; 
		} 
		else if ($is_table) {
			$this->lineheight_correction = $this->table_lineheight; 
		}
		else if ($this->blk[$this->blklvl]['line_height']) {
			$this->lineheight_correction = $this->blk[$this->blklvl]['line_height']; 
		} 
		else {
			$this->lineheight_correction = $this->default_lineheight_correction; 
		}

		// update $contentWidth and $cutoffWidth since they changed with cropping
		// Also correct lineheight to maximum fontsize (not for tables)
		$contentWidth = 0;
		$maxlineHeight = 0;
		$maxfontsize = 0;
		foreach ( $content as $k => $chunk )
		{
              $this->restoreFont( $font[ $k ]);
		  if ($this->objectbuffer[$k]) { 
			$contentWidth += $this->objectbuffer[$k]['OUTER-WIDTH'] * $this->k; 
			$maxlineHeight = max($maxlineHeight,$this->objectbuffer[$k]['OUTER-HEIGHT']);
		  }
              else { 
			// mPDF 2.5 Soft Hyphen
			if ($this->is_MB) {
			      $content[$k] = $chunk = str_replace("\xc2\xad",'',$chunk ); 
			}
			// mPDF 3.0 Soft Hyphens chr(173)
			else if ($this->FontFamily!='symbol' && $this->FontFamily!='zapfdingbats') {
			      $content[$k] = $chunk = str_replace($this->chrs[173],'',$chunk );
			}
			$contentWidth += $this->GetStringWidth( $chunk ) * $this->k; 
			$maxlineHeight = max($maxlineHeight,$this->FontSize * $this->lineheight_correction ); 
			$maxfontsize = max($maxfontsize,$this->FontSize); 
		  }
		}
		// mPDF 2.1 Check Bullet fontsize for List
		if ($is_list && is_array($this->bulletarray)) {
	  		$actfs = $this->bulletarray['fontsize'];
			$maxlineHeight = max($maxlineHeight,$actfs * $this->lineheight_correction ); 
			$maxfontsize = max($maxfontsize,$actfs);
		}

		$lineHeight = $maxlineHeight; 
		$cutoffWidth = $contentWidth;
		$this->linemaxfontsize = $maxfontsize;


		// JUSTIFICATION J
		$nb_carac = 0;
		$nb_spaces = 0;
		// if it's justified, we need to find the char/word spacing (or if orphans have allowed length of line to go over the maxwidth)
		// mPDF 2.4 Float Images
		if(( $align == 'J' ) || ($cutoffWidth > $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*$this->k) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * $this->k) ) +  0.001)) {   // 0.001 is to correct for deviations converting mm=>pts
		  // JUSTIFY J (Use character spacing)
 		  // WORD SPACING
			foreach ( $content as $k => $chunk ) {
		  		if (!$this->objectbuffer[$k]) {
					// mPDF 2.5 Change NON_BREAKING SPACE to spaces so they are 'spaced' properly
					if ($this->is_MB) {
					      $chunk = str_replace($this->chrs[194].$this->chrs[160],$this->chrs[32],$chunk ); 
					}
					else {
					      $chunk = str_replace($this->chrs[160],$this->chrs[32],$chunk );
					}
					$nb_carac += mb_strlen( $chunk, $this->mb_encoding ) ;  
					$nb_spaces += mb_substr_count( $chunk,' ', $this->mb_encoding ) ;  
				}
			}
			// mPDF 2.4 Float Images
			list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,($maxWidth-$cutoffWidth-$WidthCorrection-(($this->cMarginL+$this->cMarginR)*$this->k)-($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * $this->k) )));
			if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
			else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
			$this->charspacing=$charspacing;
			if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
			else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
			$this->ws=$ws;
		}

		// otherwise, we want normal spacing
		else {
			if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
			$this->charspacing=0;
			if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
			$this->ws=0;
		}

		// WORD SPACING
		// mPDF 2.4 Float Images
		$empty = $maxWidth - $WidthCorrection - $contentWidth - (($this->cMarginL+$this->cMarginR)* $this->k) - ($paddingL+$paddingR +(($fpaddingL + $fpaddingR) * $this->k) ) - ( $this->charspacing * $nb_carac) - ( $this->ws * $nb_spaces);
		$empty /= $this->k;
		$b = ''; //do not use borders

		// Get PAGEBREAK TO TEST for height including the top border/padding
		$check_h = max($this->divheight,$lineHeight);
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blklvl > 0) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
		}

		// PAGEBREAK
		/*'If' below used in order to fix "first-line of other page with justify on" bug*/
		if(($this->y+$check_h) > $this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak()) {

			$bak_x=$this->x;//Current X position

			// WORD SPACING
			$ws=$this->ws;//Word Spacing
			if($this->ws>0) {
				$this->ws=0;
				$this->_out('BT 0 Tw ET'); 
			}
			$charspacing=$this->charspacing;//Character Spacing
			if($charspacing>0) {
				$this->charspacing=0;
				$this->_out('BT 0 Tc ET'); 
			}

		      $this->AddPage($this->CurOrientation);

		      $this->x = $bak_x;
			// Added to correct for OddEven Margins
			$currentx += $this->MarginCorrection;
			$this->x += $this->MarginCorrection;

			// WORD SPACING
			if($ws>0) {
				$this->ws=$ws;
				$this->_out(sprintf('BT %.3f Tw ET',$ws)); 
			}
			if($charspacing>0) {
				$this->charspacing=$charspacing;
				$this->_out(sprintf('BT %.3f Tc ET',$charspacing));//add-on 
			}
		}

		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			$currentx += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$oldcolumn = $this->CurrCol;
		}

		// mPDF 2.1
		if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }

		// TOP MARGIN
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['margin_top']) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$this->DivLn($this->blk[$this->blklvl]['margin_top'],$this->blklvl-1,true,$this->blk[$this->blklvl]['margin_collapse']); 
			// mPDF 2.1
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }
		}


		// Update y0 for top of block (used to paint border)
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$this->blk[$this->blklvl]['y0'] = $this->y;
			$this->blk[$this->blklvl]['startpage'] = $this->page;
		}

		// TOP PADDING and BORDER spacing/fill
		if (($newblock) && ($blockstate==1 || $blockstate==3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			// mPDF 3.0 Also does border when Columns active
			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'],-3,true,false,1);
			// mPDF 2.1
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }
		}

		$arraysize = count($content);

		// mPDF 2.4 Float Images
		$margins = ($this->cMarginL+$this->cMarginR) + ($ipaddingL+$ipaddingR + $fpaddingR + $fpaddingR );
 
		// PAINT BACKGROUND FOR THIS LINE
		if (!$is_table) { $this->DivLn($lineHeight,$this->blklvl,false); }	// false -> don't advance y

	
		$this->x = $currentx + $this->cMarginL + $ipaddingL + $fpaddingL ;	// mPDF 2.4 Float Images
		if ($align == 'R') { $this->x += $empty; }
		else if ($align == 'C') { $this->x += ($empty / 2); }

		// Paragraph INDENT
		if (($this->blk[$this->blklvl]['text_indent']) && ($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 1) && (!$is_table) && ($this->directionality!='rtl') && ($align !='C')) { 
			$this->x += $this->blk[$this->blklvl]['text_indent'];
		}


		// DIRECTIONALITY RTL
		$all_rtl = false;
		$contains_rtl = false;
		// mPDF 2.2 - variable name changed to lowercase first letter
   		if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->biDirectional)))  { 
			$all_rtl = true;
			foreach ( $content as $k => $chunk ) {
				// mPDF 2.3
				$reversed = $this->magic_reverse_dir($chunk, false);
				if ($reversed > 0) { $contains_rtl = true; }
				if ($reversed < 2) { $all_rtl = false; }
				$content[$k] = $chunk;
			}
			if ($this->directionality == 'rtl') { 
				if ($contains_rtl) {
					$content = array_reverse($content,false);
				}
			}
			// mPDF 2.2 - variable name changed to lowercase first letter
			else if (($this->directionality == 'ltr') && ($this->biDirectional)) { 
				if ($all_rtl) {
					$content = array_reverse($content,false);
				}
			}
		}

		foreach ( $content as $k => $chunk ) {

			// FOR IMAGES - UPDATE POSITION
			if (($this->directionality=='rtl' && $contains_rtl) || $all_rtl) { $dirk = $arraysize-1 - $k ; } else { $dirk = $k; }

			if ($this->objectbuffer[$dirk]) {
			  $xadj = $this->x - $this->objectbuffer[$dirk]['OUTER-X'] ; 

			  $this->objectbuffer[$dirk]['OUTER-X'] += $xadj;
			  $this->objectbuffer[$dirk]['BORDER-X'] += $xadj;
			  $this->objectbuffer[$dirk]['INNER-X'] += $xadj;
			  if ($valign == 'M' || $valign == '') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y'])+($lineHeight - $this->objectbuffer[$dirk]['OUTER-HEIGHT'])/2;
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			  }
			  else if ($valign == 'B') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y'])+($lineHeight - $this->objectbuffer[$dirk]['OUTER-HEIGHT']);
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			  }
			  else if ($valign == 'T') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y']);
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			  }
			}

			// DIRECTIONALITY RTL
			if ((($this->directionality == 'rtl') && ($contains_rtl )) || ($all_rtl )) { $this->restoreFont($font[$arraysize-1 - $k]); }
			else { $this->restoreFont( $font[ $k ] ); }

	 		//*********** SPAN BACKGROUND COLOR *****************//
			if ($this->spanbgcolor) { 
				$cor = $this->spanbgcolorarray;
				$this->SetFillColor($cor['R'],$cor['G'],$cor['B']);
				$save_fill = $fill; $spanfill = 1; $fill = 1;
			}

			// WORD SPACING
		      $stringWidth = $this->GetStringWidth($chunk ) + ( $this->charspacing * mb_strlen($chunk,$this->mb_encoding ) / $this->k )  
				+ ( $this->ws * mb_substr_count($chunk,' ',$this->mb_encoding ) / $this->k );
			if ($this->objectbuffer[$dirk]) { $stringWidth = $this->objectbuffer[$dirk]['OUTER-WIDTH'];  }

			if ($stringWidth > 0) {
                     if ($k == $arraysize-1) { 
				// mPDF 2.5 Correct for character spacing on last letter
				$stringWidth -= ( $this->charspacing / $this->k ); 
				$this->Cell( $stringWidth, $lineHeight, $chunk, '', 1, '', $fill, $this->HREF , $currentx,0,0,$valign ); //mono-style line or last part (skips line)
			   }
                     else $this->Cell( $stringWidth, $lineHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0,0,$valign );//first or middle part
			}
			else {	// If a space started a new chunk at the end of a line
				$this->x = $currentx; $this->y += $lineHeight; 
			}
	 		//*********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR *****************//
			if ($spanfill) { 
				$fill = $save_fill; $spanfill = 0; 
				if ($fill) { $this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']); }
			}
		}
		// move on to the next line, reset variables, tack on saved content and current char

		$this->printobjectbuffer($is_table);
		$this->objectbuffer = array();

		// LIST BULLETS/NUMBERS
		if ($is_list && is_array($this->bulletarray) && ($lineCount == 1) ) {
		  $bull = $this->bulletarray;
		  $this->restoreInlineProperties($this->InlineProperties['LIST'][$bull['level']][$bull['occur']]);
		  // mPDF 2.1
		  if ($this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]) { $this->restoreInlineProperties($this->InlineProperties['LISTITEM'][$bull['level']][$bull['occur']][$bull['num']]); }
		  if ($bull['font'] == 'zapfdingbats') {
			$this->bullet = true;
			$this->SetFont('zapfdingbats','',$this->FontSizePt/2.5);
		  }
		  else { $this->SetFont($this->FontFamily,$this->FontStyle,$this->FontSizePt,true,true); }	// force output
	        //Output bullet
		  $this->x = $currentx + $bull['x'];
		  $this->y -= $lineHeight;
		  // mPDF 2.1
//		  $this->Cell($bull['w'],$bull['h'],$bull['txt'],'','',$bull['align']);
	        $this->Cell($bull['w'],$lineHeight,$bull['txt'],'','',$bull['align']);
		  if ($bull['font'] == 'zapfdingbats') {
			$this->bullet = false;
		  }
		  $this->x = $currentx;	// Reset
		  $this->y += $lineHeight;

		  // mPDF 2.1
		  if ($this->ColActive && !$is_table) { $this->breakpoints[$this->CurrCol][] = $this->y; }

		  $this->bulletarray = array();	// prevents repeat of bullet/number if <li>....<br />.....</li>
		}


		// mPDF 2.4 Float Images (for skipline)
		// Update values if set to skipline
		if ($this->floatmargins) { $this->_advanceFloatMargins(); }

		// Reset lineheight
		$lineHeight = $this->divheight;
		$valign = 'M';


		$this->restoreFont( $savedFont );
		$font = array( $savedFont );
		//****************************//
		$content = array( $savedContent . $c );
		//****************************//

		$currContent =& $content[ 0 ];
		$contentWidth = $this->GetStringWidth( $currContent ) * $this->k;
		$cutoffWidth = $contentWidth;
      }
      // another character will fit, so add it on
	else {
		$contentWidth += $cw;
		$currContent .= $c;
	}
    }
    // mPDF 2.1
    unset($content);
}
//----------------------END OF FLOWING BLOCK------------------------------------//


// mPDF 2.4 Float Images (for skipline)
// Update values if set to skipline
function _advanceFloatMargins() {
	// Update floatmargins - L
	if ($this->floatmargins['L']['skipline'] && $this->floatmargins['L']['y0'] != $this->y) {
		$yadj = $this->y - $this->floatmargins['L']['y0'];
		$this->floatmargins['L']['y0'] = $this->y;
		$this->floatmargins['L']['y1'] += $yadj;

		// Update objattr in floatbuffer
		if ($this->floatbuffer[$this->floatmargins['L']['id']]['border_left']['w']) {
			$this->floatbuffer[$this->floatmargins['L']['id']]['BORDER-Y'] += $yadj;
		}
		$this->floatbuffer[$this->floatmargins['L']['id']]['INNER-Y'] += $yadj;
		$this->floatbuffer[$this->floatmargins['L']['id']]['OUTER-Y'] += $yadj;

		// Unset values
		$this->floatbuffer[$this->floatmargins['L']['id']]['skipline'] = false;
		$this->floatmargins['L']['skipline'] = false;
		$this->floatmargins['L']['id'] = '';
	}
	// Update floatmargins - R
	if ($this->floatmargins['R']['skipline'] && $this->floatmargins['R']['y0'] != $this->y) {
		$yadj = $this->y - $this->floatmargins['R']['y0'];
		$this->floatmargins['R']['y0'] = $this->y;
		$this->floatmargins['R']['y1'] += $yadj;

		// Update objattr in floatbuffer
		if ($this->floatbuffer[$this->floatmargins['R']['id']]['border_left']['w']) {
			$this->floatbuffer[$this->floatmargins['R']['id']]['BORDER-Y'] += $yadj;
		}
		$this->floatbuffer[$this->floatmargins['R']['id']]['INNER-Y'] += $yadj;
		$this->floatbuffer[$this->floatmargins['R']['id']]['OUTER-Y'] += $yadj;

		// Unset values
		$this->floatbuffer[$this->floatmargins['R']['id']]['skipline'] = false;
		$this->floatmargins['R']['skipline'] = false;
		$this->floatmargins['R']['id'] = '';
	}
}



//EDITEI
//Thanks to Ron Korving for the WordWrap() function
////////////////////////////////////////////////////////////////////////////////
// ADDED forcewrap - to call from TABLE functions to breakwords if necessary in cell
////////////////////////////////////////////////////////////////////////////////
function WordWrap(&$text, $maxwidth, $forcewrap = 0)
{
    $biggestword=0;//EDITEI
    $toonarrow=false;//EDITEI

    $text = ltrim($text);
    $text = mb_rtrim($text, $this->mb_encoding);

    if ($text==='') return 0;
    $space = $this->GetStringWidth(' ');
    $lines = explode("\n", $text);
    $text = '';
    $count = 0;
    foreach ($lines as $line) {

	//****************************// Edited mPDF 1.1
	if ($this->is_MB && !$this->usingCoreFont) {
		$words = mb_split(' ', $line);
	}
	else {
		$words = split(' ', $line);
	}
	//****************************//
	$width = 0;
	foreach ($words as $word) {
		$word = mb_rtrim($word, $this->mb_encoding);
		$word = ltrim($word);
		$wordwidth = $this->GetStringWidth($word);

		//EDITEI
		//Warn user that maxwidth is insufficient
		if ($wordwidth > $maxwidth + 0.0001) {
			if ($wordwidth > $biggestword) { $biggestword = $wordwidth; }
			$toonarrow=true;//EDITEI
			// ADDED
			if ($forcewrap) {
			  while($wordwidth > $maxwidth) {
				$chw = 0;	// check width
				for ( $i = 0; $i < mb_strlen($word, $this->mb_encoding ); $i++ ) {
					$chw = $this->GetStringWidth(mb_substr($word,0,$i+1,$this->mb_encoding ));
					if ($chw > $maxwidth ) {
						if ($text) {
							$text = mb_rtrim($text, $this->mb_encoding)."\n".mb_substr($word,0,$i,$this->mb_encoding );
							$count++;
						}
						else {
							$text = mb_substr($word,0,$i,$this->mb_encoding );
						}
						$word = mb_substr($word,$i,mb_strlen($word, $this->mb_encoding )-$i,$this->mb_encoding );
						$wordwidth = $this->GetStringWidth($word);
						$width = $maxwidth; 
						break;
					}
				}
			  }
			}
		}

		if ($width + $wordwidth  < $maxwidth - 0.0001) {
			$width += $wordwidth + $space;
			$text .= $word.' ';
		}
		else {
			$width = $wordwidth + $space;
			$text = mb_rtrim($text, $this->mb_encoding)."\n".$word.' ';
			$count++;
            }
	}

	$text = mb_rtrim($text, $this->mb_encoding)."\n";
	$count++;
    }
    $text = mb_rtrim($text, $this->mb_encoding);

    //Return -(wordsize) if word is bigger than maxwidth 

	// ADDED
	if ($forcewrap) { return $count; }
      if (($toonarrow) && ($this->table_error_report)) {
		die("Word is too long to fit in table - ".$this->table_error_report_param); 
	}
    if ($toonarrow) return -$biggestword;
    else return $count;
}

function _SetTextRendering($mode) { 
	if (!(($mode == 0) || ($mode == 1) || ($mode == 2))) 
	$this->Error("Text rendering mode should be 0, 1 or 2 (value : $mode)"); 
	$tr = ($mode.' Tr'); 
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['TextRendering'] != $tr || $this->keep_block_together)) { $this->_out($tr); }
	$this->pageoutput[$this->page]['TextRendering'] = $tr;

} 

function SetTextOutline($width, $r=0, $g=-1, $b=-1) //EDITEI
{ 
  if ($width == false) //Now resets all values
  { 
    $this->outline_on = false;
    $this->SetLineWidth(0.2); 
    $this->SetDrawColor(0); 
    $this->_setTextRendering(0); 
    $tr = ('0 Tr'); 
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['TextRendering'] != $tr || $this->keep_block_together)) { $this->_out($tr); }
	$this->pageoutput[$this->page]['TextRendering'] = $tr;
  }
  else
  { 
    $this->SetLineWidth($width); 
    $this->SetDrawColor($r, $g , $b); 
    $tr = ('2 Tr'); 
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['TextRendering'] != $tr || $this->keep_block_together)) { $this->_out($tr); }
	$this->pageoutput[$this->page]['TextRendering'] = $tr;
  } 
}

function Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true, $constrain=true, $watermark=false)
{
	//First use of image, get info
	if($type=='') {
		$pos=strrpos($file,'.');
		if(!$pos)	$this->Error('Image file has no extension and no type was specified: '.$file);
		// mPDF 2.5
		$qpos=strrpos($file,'?');
		if ($qpos) {  $type=substr($file,$pos+1,$qpos-$pos-1); }
		else { $type=substr($file,$pos+1); }
	}
	$type=strtolower($type);

      // mPDF 2.2 WMF image
	if(isset($this->images[$file])) { $info=$this->images[$file]; }
	else if (isset($this->formobjects[$file])) { $info=$this->formobjects[$file]; }
	else {
		$mqr=get_magic_quotes_runtime();
		set_magic_quotes_runtime(0);
		if($type=='jpg' or $type=='jpeg')	$info=$this->_parsejpg($file);
		elseif($type=='png') 			$info=$this->_parsepng($file);
		elseif($type=='gif') 			$info=$this->_parsegif($file); 
		elseif($type=='wmf') 			$info=$this->_parsewmf($file); 
		else
		{
			//Allow for additional formats
			$mtd='_parse'.$type;
			if(!method_exists($this,$mtd)) $this->Error('Unsupported image type: '.$type);
			$info=$this->$mtd($file);
		}
		// mPDF 3.0 - Try again PNG
		if ($type=='png' && $info < -1) { 
			$info=$this->_parsepng2($file, $info); 
			if ($info['UID']) { $file = $info['UID']; }	// A unique alpha blended PNG created
		}
		set_magic_quotes_runtime($mqr);
		// mPDF 3.0
		if (!is_array($info) && $info < 0) { 
			if(!$this->shownoimg || !$paint) return false;
			$file = str_replace("\\","/",dirname(__FILE__)) . "/";
			$file .= 'no_img2.gif';
			$type = 'gif';
			if(isset($this->images[$file])) { $info=$this->images[$file]; }
			else {
				set_magic_quotes_runtime(0);
				$info=$this->_parsegif($file); 
				set_magic_quotes_runtime($mqr);
				$info['i']=count($this->images)+1;
				$this->images[$file]=$info;
			}
			$w = (14 * 0.2645); 	// 14 x 16px
			$h = (16 * 0.2645); 	// 14 x 16px
		}
            else if ($type=='wmf') { 
	            $info['i']=count($this->formobjects)+1;
	            $this->formobjects[$file]=$info;
		}
		else {
			$info['i']=count($this->images)+1;
			$this->images[$file]=$info;
		}
	}
	//Automatic width and height calculation if needed
	if($w==0 and $h==0) {
            if ($type=='wmf') { 
			// mPDF 2.2 WMF image
			// WMF units are twips (1/20pt)
			// divide by 20 to get points
			// divide by k to get user units
			$w = abs($info['w'])/(20*$this->k);
			$h = abs($info['h']) / (20*$this->k);
		}
		else {
			//Put image at default dpi
			$w=($info['w']/$this->k) * (72/$this->img_dpi);
			$h=($info['h']/$this->k) * (72/$this->img_dpi);
		}
	}
	if($w==0)	$w=abs($h*$info['w']/$info['h']); // mPDF 2.4 abs for wmf
	if($h==0)	$h=abs($w*$info['h']/$info['w']); // mPDF 2.4 abs for wmf

	if ($watermark) {
	  $maxw = $this->w;
	  $maxh = $this->h;
	  // Size = D PF or array
	  if (is_array($this->watermark_size)) {
		$w = $this->watermark_size[0];
		$h = $this->watermark_size[1];
	  }
	  else if (!is_string($this->watermark_size)) {
		$maxw -= $this->watermark_size*2;
		$maxh -= $this->watermark_size*2;
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
		if ($h > $maxh )  {
			$h = $maxh ; $w=abs($h*$info['w']/$info['h']);
		}
	  }
	  else if ($this->watermark_size == 'F') {
		if ($this->ColActive) { $maxw = $this->w - ($this->DeflMargin + $this->DefrMargin); }
		else { $maxw = $this->pgwidth; }
		$maxh = $this->h - ($this->tMargin + $this->bMargin);
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
		if ($h > $maxh )  {
			$h = $maxh ; $w=abs($h*$info['w']/$info['h']);
		}
	  }
	  else  if ($this->watermark_size == 'P') {	// Default P
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
		if ($h > $maxh )  {
			$h = $maxh ; $w=abs($h*$info['w']/$info['h']);
		}
	  }
	  // Automatically resize to maximum dimensions of page if too large
	  if ($w > $maxw) {
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
	  }
	  if ($h > $maxh )  {
		$h = $maxh ;
		$w=abs($h*$info['w']/$info['h']);
	  }
	  // Position
	  if (is_array($this->watermark_pos)) {
		$x = $this->watermark_pos[0];
		$y = $this->watermark_pos[1];
	  }
	  else if ($this->watermark_pos == 'F')  {	// centred on printable area
		if ($this->ColActive) {
			if (($this->useOddEven) && (($this->page)%2==0)) { $xadj = $this->DeflMargin-$this->DefrMargin; }
			else { $xadj = 0; }
			$x = ($this->DeflMargin - $xadj + ($this->w - ($this->DeflMargin + $this->DefrMargin))/2) - ($w/2);
		}
		else { 
			$x = ($this->lMargin + ($this->pgwidth)/2) - ($w/2);
		}
		$y = ($this->tMargin + ($this->h - ($this->tMargin + $this->bMargin))/2) - ($h/2);
	  }
	  else {	// default P - centred on whole page
		$x = ($this->w/2) - ($w/2);
		$y = ($this->h/2) - ($h/2);
	  }
	  // mPDF 2.2
	  if ($type=='wmf') { 
		$sx = $w*$this->k / $info['w'];
		$sy = -$h*$this->k / $info['h'];
		$outstring = sprintf('q %f 0 0 %f %f %f cm /FO%d Do Q', $sx, $sy, $x*$this->k-$sx*$info['x'], (($this->h-$y)*$this->k)-$sy*$info['y'], $info['i']);
	  }
	  else { 
		$outstring = sprintf("q %.3f 0 0 %.3f %.3f %.3f cm /I%d Do Q",$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']);
	  }
	  $this->_out($outstring);
	  return 0;
	}	// end of IF watermark

	if ($constrain) {
	  // Automatically resize to maximum dimensions of page if too large
	  if ($this->blk[$this->blklvl]['inner_width']) { $maxw = $this->blk[$this->blklvl]['inner_width']; }
	  else { $maxw = $this->pgwidth; }
	  if ($w > $maxw) {
		$w = $maxw;
		$h=abs($w*$info['h']/$info['w']);
	  }

	  if ($h > $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10))  {  // see below - +10 to avoid drawing too close to border of page
		$h = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10) ;
		$w=abs($h*$info['w']/$info['h']);
	  }


	  //Avoid drawing out of the paper(exceeding width limits). //EDITEI
	  //if ( ($x + $w) > $this->fw ) {
	  if ( ($x + $w) > $this->w ) {
		$x = $this->lMargin;
		$y += 5;
	  }

	  $changedpage = false; //EDITEI
	  $oldcolumn = $this->CurrCol;
	  //Avoid drawing out of the page. //EDITEI
	  if($y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak()) {
		$this->AddPage($this->CurOrientation);
		// Added to correct for OddEven Margins
		$x=$x +$this->MarginCorrection;
		$y = $tMargin + $this->margin_header;
		$changedpage = true;
	  }
	  // COLS
	  // COLUMN CHANGE
	  if ($this->CurrCol != $oldcolumn) {
		$y = $this->y0;
		$x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
		$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
	  }
	}	// end of IF constrain

	// mPDF 2.2
	if ($type=='wmf') { 
		$sx = $w*$this->k / $info['w'];
		$sy = -$h*$this->k / $info['h'];
		$outstring = sprintf('q %f 0 0 %f %f %f cm /FO%d Do Q', $sx, $sy, $x*$this->k-$sx*$info['x'], (($this->h-$y)*$this->k)-$sy*$info['y'], $info['i']);
	}
	else { 
		$outstring = sprintf("q %.3f 0 0 %.3f %.3f %.3f cm /I%d Do Q",$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']);
	}

	if($paint) { //EDITEI
		$this->_out($outstring);
		if($link) $this->Link($x,$y,$w,$h,$link);

		//Avoid writing text on top of the image. // THIS WAS OUTSIDE THE if ($paint) bit!!!!!!!!!!!!!!!!
		$this->y = $y + $h;
	}


	//Return width-height array //EDITEI
	$sizesarray['WIDTH'] = $w;
	$sizesarray['HEIGHT'] = $h;
	$sizesarray['X'] = $x; //Position before painting image
	$sizesarray['Y'] = $y; //Position before painting image
	$sizesarray['OUTPUT'] = $outstring;
	// mPDF 3.0 Tiling patterns
	$sizesarray['IMAGE_ID'] = $info['i'];
	return $sizesarray;
}



//=============================================================
//=============================================================
//=============================================================
//=============================================================
//=============================================================

function inlineObject($type,$x,$y,$objattr,$Lmargin,$widthUsed,$maxWidth,$lineHeight,$paint=false,$is_table=false)
{
   // mPDF 2.0
   if ($is_table) { $k = $this->shrin_k; } else { $k = 1; }


   // NB $x is only used when paint=true
	// Lmargin not used
   $w = $objattr['width']/$k;
   $h = abs($objattr['height']/$k);	// mPDF 2.4 abs for wmf

   $widthLeft = $maxWidth - $widthUsed;
   $maxHeight = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10) ;
	// For Images
   $extraWidth = ($objattr['border_left']['w'] + $objattr['border_right']['w'] + $objattr['margin_left']+ $objattr['margin_right'])/$k;
   $extraHeight = ($objattr['border_top']['w'] + $objattr['border_bottom']['w'] + $objattr['margin_top']+ $objattr['margin_bottom'])/$k;


   if ($type == 'image' || $objattr['subtype'] == 'IMAGE') {
    // mPDF 2.2 WMF
    if ($objattr['itype'] == 'wmf') {
	$file = $objattr['file'];
 	$info=$this->formobjects[$file];
   }
    else {
	$file = $objattr['file'];
	$info=$this->images[$file];
    }
   }
    // mPDF 2.2 Annotations
    // mPDF 3.0
    if ($type == 'annot' || $type == 'bookmark' || $type == 'indexentry' || $type == 'toc') {
	$w = 0.00001;
	$h = 0.00001;
   }

   // TEST whether need to skipline
   // mPDF 2.0 don't return new line/page if is_table
   if (!$paint) {
	if ($type == 'hr') {	// always force new line
		if (($y + $h + $lineHeight > $this->PageBreakTrigger) && !$this->InFooter && !$is_table) { return array(-2, $w ,$h ); } // New page + new line
		else { return array(1, $w ,$h ); } // new line
	}
	else {
		// mPDF 2.3
//		if (($widthUsed > 0) && ($w > $widthLeft) && !$is_table)
		if ($widthUsed > 0 && $w > $widthLeft && (!$is_table || $type != 'image')) { 	// New line needed
			if (($y + $h + $lineHeight > $this->PageBreakTrigger) && !$this->InFooter) { return array(-2,$w ,$h ); } // New page + new line
			return array(1,$w ,$h ); // new line
		}
		// Will fit on line but NEW PAGE REQUIRED
		else if (($y + $h > $this->PageBreakTrigger) && !$this->InFooter && !$is_table) { return array(-1,$w ,$h ); }
		else { return array(0,$w ,$h ); }
	}
   }

   // mPDF 2.2 Annotations
    // mPDF 3.0
   if ($type == 'annot' || $type == 'bookmark' || $type == 'indexentry' || $type == 'toc') {
	$w = 0.00001;
	$h = 0.00001;
	$objattr['BORDER-WIDTH'] = 0;
	$objattr['BORDER-HEIGHT'] = 0;
	$objattr['BORDER-X'] = $x;
	$objattr['BORDER-Y'] = $y;
	$objattr['INNER-WIDTH'] = 0;
	$objattr['INNER-HEIGHT'] = 0;
	$objattr['INNER-X'] = $x;
	$objattr['INNER-Y'] = $y;
  }

  if ($type == 'image') {
	// Automatically resize to width remaining
	if ($w > $widthLeft  && !$is_table) {
		$w = $widthLeft ;
		$h=abs($w*$info['h']/$info['w']);	// mPDF 2.4 abs for wmf
	}
	$img_w = $w - $extraWidth ;
	$img_h = $h - $extraHeight ;
	if ($objattr['border_left']['w']) {
		$objattr['BORDER-WIDTH'] = $img_w + (($objattr['border_left']['w']/$k + $objattr['border_right']['w']/$k)/2) ;
		$objattr['BORDER-HEIGHT'] = $img_h + (($objattr['border_top']['w']/$k + $objattr['border_bottom']['w']/$k)/2) ;
		$objattr['BORDER-X'] = $x + $objattr['margin_left']/$k + (($objattr['border_left']['w']/$k)/2) ;
		$objattr['BORDER-Y'] = $y + $objattr['margin_top']/$k + (($objattr['border_top']['w']/$k)/2) ;
	}
	$objattr['INNER-WIDTH'] = $img_w;
	$objattr['INNER-HEIGHT'] = $img_h;
	$objattr['INNER-X'] = $x + $objattr['margin_left']/$k + ($objattr['border_left']['w']/$k);
	$objattr['INNER-Y'] = $y + $objattr['margin_top']/$k + ($objattr['border_top']['w']/$k) ;
	$objattr['ID'] = $info['i'];
   }

   if ($type == 'input' && $objattr['subtype'] == 'IMAGE') { 
	$img_w = $w - $extraWidth ;
	$img_h = $h - $extraHeight ;
	if ($objattr['border_left']['w']) {
		$objattr['BORDER-WIDTH'] = $img_w + (($objattr['border_left']['w']/$k + $objattr['border_right']['w']/$k)/2) ;
		$objattr['BORDER-HEIGHT'] = $img_h + (($objattr['border_top']['w']/$k + $objattr['border_bottom']['w']/$k)/2) ;
		$objattr['BORDER-X'] = $x + $objattr['margin_left']/$k + (($objattr['border_left']['w']/$k)/2) ;
		$objattr['BORDER-Y'] = $y + $objattr['margin_top']/$k + (($objattr['border_top']['w']/$k)/2) ;
	}
	$objattr['INNER-WIDTH'] = $img_w;
	$objattr['INNER-HEIGHT'] = $img_h;
	$objattr['INNER-X'] = $x + $objattr['margin_left']/$k + ($objattr['border_left']['w']/$k);
	$objattr['INNER-Y'] = $y + $objattr['margin_top']/$k + ($objattr['border_top']['w']/$k) ;
	$objattr['ID'] = $info['i'];
   }


   if ($type == 'textarea') {
	// Automatically resize to width remaining
	// mPDF 2.3
	if ($w > $widthLeft && !$is_table) {
		$w = $widthLeft ;
	}
	if (($y + $h > $this->PageBreakTrigger) && !$this->InFooter) {
		$h=$this->h - $y - $this->bMargin;
	}
   }

   if ($type == 'hr') {
	if ($is_table) { 
		$objattr['INNER-WIDTH'] = $maxWidth * $objattr['W-PERCENT']/100; 
		$objattr['width'] = $objattr['INNER-WIDTH']; 
		// mPDF 2.5
		//$w = $objattr['width'];
		// mPDF 3.0
		$w = $maxWidth;
	}
	else { 
		// mPDF 2.4 Image FLoats
		if ($w>$maxWidth) { $w = $maxWidth; }
		$objattr['INNER-WIDTH'] = $w; 
		// mPDF 3.0
		$w = $maxWidth;
	}
  }



   if (($type == 'select') || ($type == 'input' && ($objattr['subtype'] == 'TEXT' || $objattr['subtype'] == 'PASSWORD'))) {
	// Automatically resize to width remaining
	// mPDF 2.3
	if ($w > $widthLeft && !$is_table) {
		$w = $widthLeft;
	}
   }

   if ($type == 'textarea' || $type == 'select' || $type == 'input') {
	$objattr['fontsize'] /= $k;
	$objattr['linewidth'] /= $k;
   }

   //Return width-height array
   $objattr['OUTER-WIDTH'] = $w;
   $objattr['OUTER-HEIGHT'] = $h;
   $objattr['OUTER-X'] = $x;
   $objattr['OUTER-Y'] = $y;
   return $objattr;
}


//=============================================================
//=============================================================
//=============================================================
//=============================================================
//=============================================================




//EDITEI - Done after reading a little about PDF reference guide
function DottedRect($x=100,$y=150,$w=50,$h=50,$dotsize=0.2,$spacing=2)
{
  // $spacing: Spacing between dots in mm
  // dotsize - passed to DrawDot()  radius in mm (user units)
  $x *= $this->k ;
  $y = ($this->h-$y)*$this->k;
  $w *= $this->k ;
  $h *= $this->k ;// - h?
   
  $herex = $x;
  $herey = $y;

  //Make fillcolor == drawcolor
  $bak_fill = $this->FillColor;
  $this->FillColor = $this->DrawColor;
  $this->FillColor = str_replace('RG','rg',$this->FillColor);
  $this->_out($this->FillColor);
 
  while ($herex < ($x + $w)) //draw from upper left to upper right
  {
  $this->DrawDot($herex,$herey,$dotsize);
  $herex += ($spacing *$this->k);
  }
  $herex = $x + $w;
  while ($herey > ($y - $h)) //draw from upper right to lower right
  {
  $this->DrawDot($herex,$herey,$dotsize);
  $herey -= ($spacing *$this->k);
  }
  $herey = $y - $h;
  while ($herex > $x) //draw from lower right to lower left
  {
  $this->DrawDot($herex,$herey,$dotsize);
  $herex -= ($spacing *$this->k);
  }
  $herex = $x;
  while ($herey < $y) //draw from lower left to upper left
  {
  $this->DrawDot($herex,$herey,$dotsize);
  $herey += ($spacing *$this->k);
  }
  $herey = $y;

  $this->FillColor = $bak_fill;
  $this->_out($this->FillColor); //return fillcolor back to normal
}

//EDITEI - Done after reading a little about PDF reference guide
function DrawDot($x,$y,$r=0) //center x, y, $r = radius in mm (user units) Optional
{
  if ($r == 0) { $r = 0.2 * $this->k;  }	// default 0.2mm
  else { $r = $r * $this->k; }  //DOT SIZE = radius
  $op = 'B'; // draw Filled Dots
  //F == fill //S == stroke //B == stroke and fill 
  
  //Start Point
  $x1 = $x - $r;
  $y1 = $y;
  //End Point
  $x2 = $x + $r;
  $y2 = $y;
  //Auxiliar Point
  $x3 = $x;
  $y3 = $y + (2*$r);// 2*raio to make a round (not oval) shape  

  //Round join and cap
  $s="\n".'1 J'."\n";
  $s.='1 j'."\n";

  //Upper circle
  $s.=sprintf('%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
  $s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);//Bezier curve
  //Lower circle
  $y3 = $y - (2*$r);
  $s.=sprintf("\n".'%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
  $s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);
  $s.=$op."\n"; //stroke and fill

  //Draw in PDF file
  $this->_out($s);

  //Set line cap style back to square
  $this->_out('2 J');
}

function SetDash($black=false,$white=false)
{
        if($black and $white) $s=sprintf('[%.3f %.3f] 0 d',$black*$this->k,$white*$this->k);
        else $s='[] 0 d';
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['Dash'] != $s || $this->keep_block_together)) { $this->_out($s); }
	$this->pageoutput[$this->page]['Dash'] = $s;

}

function DisplayPreferences($preferences)
{
    $this->DisplayPreferences .= $preferences;
}


function Ln($h='',$collapsible=0)
{
// Added collapsible to allow collapsible top-margin on new page
	//Line feed; default value is last cell height
	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];
	if ($collapsible && ($this->y==$this->tMargin) && (!$this->ColActive)) { $h = 0; }
	if(is_string($h)) $this->y+=$this->lasth;
	else $this->y+=$h;
}


// mPDF 3.0 Also does border when Columns active
// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
function DivLn($h,$level=-3,$move_y=true,$collapsible=false,$state=0) {
  // this->x is returned as it was
  // adds lines (y) where DIV bgcolors are filled in
  if ($collapsible && ($this->y==$this->tMargin) && (!$this->ColActive)) { return; }
  if ($collapsible && ($this->y==$this->y0) && ($this->ColActive) && $this->CurrCol == 0) { return; }

  // mPDF 3.0
	// Still use this method if columns or page-break-inside: avoid as it allows repositioning later
	// otherwise, now uses PaintDivBB()
  if (!$this->ColActive && !$this->keep_block_together) { 
	if ($move_y && !$this->ColActive) { $this->y += $h; }
	return; 
  }

  if ($level == -3) { $level = $this->blklvl; }
  $firstblockfill = $this->GetFirstBlockFill();
  if ($firstblockfill && $this->blklvl > 0 && $this->blklvl >= $firstblockfill) {
	$last_x = 0;
	$last_w = 0;
	$last_fc = $this->FillColor;
	$bak_x = $this->x;
	$bak_h = $this->divheight;
	$this->divheight = 0;	// Temporarily turn off divheight - as Cell() uses it to check for PageBreak
	for ($blvl=$firstblockfill;$blvl<=$level;$blvl++) {
		$this->SetBlockFill($blvl);
		$this->x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
		if ($last_x != $this->lMargin + $this->blk[$blvl]['outer_left_margin'] || $last_w != $this->blk[$blvl]['width'] || $last_fc != $this->FillColor) {
			$x = $this->x;	// mPDF 3.0
			$this->Cell( ($this->blk[$blvl]['width']), $h, '', '', 0, '', 1);
			// mPDF 3.0 Also does border when Columns active
			if (!$this->keep_block_together && !$this->writingHTMLheader) {
				$this->x = $x;
				// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
				if ($blvl == $this->blklvl) { $this->PaintDivLnBorder($state,$blvl,$h); }
				else { $this->PaintDivLnBorder(0,$blvl,$h); }
			}
		}
		$last_x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
		$last_w = $this->blk[$blvl]['width'];
		$last_fc = $this->FillColor;
	}
	// Reset current block fill
	$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
	$this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
	$this->x = $bak_x;
	$this->divheight = $bak_h;
  }
  if ($move_y) { $this->y += $h; }
}

function GetX()
{
	//Get x position
	return $this->x;
}

function SetX($x)
{
	//Set x position
	if($x >= 0)	$this->x=$x;
	else $this->x = $this->w + $x;
}

function GetY()
{
	//Get y position
	return $this->y;
}

function SetY($y)
{
	//Set y position and reset x
	$this->x=$this->lMargin;
	if($y>=0)
		$this->y=$y;
	else
		$this->y=$this->h+$y;
}

function SetXY($x,$y)
{
	//Set x and y positions
	$this->SetY($y);
	$this->SetX($x);
}


/*	Structure of closing document ----
Output()
 Close()
   _endpage
   _enddoc
	_putpages
		_putannots
	_putresources
		_putextgstates
		_putfonts
		_putimages
		[$this->fonts]
		[$this->extgstates]
		[$this->images]
		_putbookmarks
		_putencryption
	_putinfo	(i.e. metadata)
	_putcatalog
		[ViewerPreferences]
		[Layout]
		[Outlines]
	_puttrailer
*/

function Output($name='',$dest='')
{

	//Output PDF to some destination
	global $_SERVER;
	//Finish document if necessary
	if($this->state < 3) $this->Close();
	// mPDF 2.4 Check if Error occurred
	// fn. error_get_last is only in PHP>=5.2
	if (function_exists('error_get_last') && error_get_last()) {
	   $e = error_get_last(); 
	   if (($e['type'] < 2048 && $e['type'] != 8) || (intval($e['type']) & intval(ini_get("error_reporting")))) {
		echo "<p>Error message detected - PDF file generation aborted.</p>"; 
		exit; 
	   }
	}

	//Normalize parameters
	if(is_bool($dest)) $dest=$dest ? 'D' : 'F';
	$dest=strtoupper($dest);
	if($dest=='')
	{
		if($name=='')
		{
			$name='mpdf.pdf';
			$dest='I';
		}
		else
			$dest='F';
	}
	switch($dest)
	{
		case 'I':
			// mPDF 3.0
			if (!$this->allow_output_buffering && ob_get_contents()) { echo "<p>Output has already been sent from the script - PDF file generation aborted.</p>"; exit; }
			//Send to standard output
			if(isset($_SERVER['SERVER_NAME']))
			{
				//We send to a browser
				Header('Content-Type: application/pdf');
				if(headers_sent())
					$this->Error('Some data has already been output to browser, can\'t send PDF file');
				Header('Content-Length: '.strlen($this->buffer));
				Header('Content-disposition: inline; filename='.$name);
			}
			echo $this->buffer;
			break;
		case 'D':
			//Download file
			if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
				Header('Content-Type: application/force-download');
			else
				Header('Content-Type: application/octet-stream');
			if(headers_sent())
				$this->Error('Some data has already been output to browser, can\'t send PDF file');
			Header('Content-Length: '.strlen($this->buffer));
			Header('Content-disposition: attachment; filename='.$name);
 			echo $this->buffer;
			break;
		case 'F':
			//Save to local file
			$f=fopen($name,'wb');
			if(!$f) $this->Error('Unable to create output file: '.$name);
			fwrite($f,$this->buffer,strlen($this->buffer));
			fclose($f);
			break;
		case 'S':
			//Return as a string
			return $this->buffer;
		default:
			$this->Error('Incorrect output destination: '.$dest);
	}
	return '';
}


/*******************************************************************************
*                                                                              *
*                              Protected methods                               *
*                                                                              *
*******************************************************************************/
function _dochecks()
{
	//Check for locale-related bug
	if(1.1==1)
		$this->Error('Don\'t alter the locale before including class file');
	//Check for decimal separator
	if(sprintf('%.1f',1.0)!='1.0')
		setlocale(LC_NUMERIC,'C');
}

function _begindoc()
{
	//Start document
	$this->state=1;
	$this->_out('%PDF-'.$this->pdf_version);
}

function _putpages()
{
	$nb=$this->page;
	// mPDF 2.2 - variable name changed to lowercase first letter
	if(!empty($this->aliasNbPg))
	{
		//Replace number of pages
		// mPDF 2.2 Added and not CJK
		if ($this->is_MB) { $s = $this->UTF8ToUTF16BE($this->aliasNbPg, false); }
		else { $s = $this->aliasNbPg; }
		if ($this->is_MB) { $r = $this->UTF8ToUTF16BE($nb, false); }
		else { $r = $nb; }
		for($n=1;$n<=$nb;$n++) {
			// mPDF 3.0 match ff= fs= fz=
			if (preg_match_all('/{mpdfheadernbpg (C|R) ff=(\S*) fs=(\S*) fz=(.*?)}/',$this->pages[$n],$m)) {
				for($hi=0;$hi<count($m[0]);$hi++) {
					$pos = $m[1][$hi];
					$hff = $m[2][$hi];
					$hfst = $m[3][$hi];
					$hfsz = $m[4][$hi];
					$this->SetFont($hff,$hfst,$hfsz, false);
					$x1 = $this->GetStringWidth($this->aliasNbPg);
					$x2 = $this->GetStringWidth($nb);
					$xadj = $x1 - $x2;
					if ($pos=='C') { $xadj /= 2; }
					$rep = sprintf(' q 1 0 0 1 %.3f 0 cm ', $xadj*$this->k); 
					$this->pages[$n] = str_replace($m[0][$hi], $rep, $this->pages[$n]);
				}
			}
			$this->pages[$n]=str_replace($s,$r,$this->pages[$n]);
		}
	}
	// mPDF 2.0
	// mPDF 2.2 - variable name changed to lowercase first letter
	if(!empty($this->aliasNbPgGp))
	{
		//Replace number of pages in group
		if ($this->is_MB) { $s = $this->UTF8ToUTF16BE($this->aliasNbPgGp, false); }
		else { $s = $this->aliasNbPgGp; }
		for($n=1;$n<=$nb;$n++) {
			// mPDF 3.0 Add PageNum prefix/suffix
			$nbt = $this->docPageNumTotal($n);
			if ($this->is_MB) { $r = $this->UTF8ToUTF16BE($nbt, false); }
			else { $r = $nbt; }
			// mPDF 3.0 match ff= fs= fz=
			if (preg_match_all('/{mpdfheadernbpggp (C|R) ff=(\S*) fs=(\S*) fz=(.*?)}/',$this->pages[$n],$m)) {
				for($hi=0;$hi<count($m[0]);$hi++) {
					$pos = $m[1][$hi];
					$hff = $m[2][$hi];
					$hfst = $m[3][$hi];
					$hfsz = $m[4][$hi];
					$this->SetFont($hff,$hfst,$hfsz, false);
					$x1 = $this->GetStringWidth($this->aliasNbPgGp);
					// mPDF 3.0
					$x2 = $this->GetStringWidth($nbt);
					$xadj = $x1 - $x2;
					if ($pos=='C') { $xadj /= 2; }
					$rep = sprintf(' q 1 0 0 1 %.3f 0 cm ', $xadj*$this->k); 
					$this->pages[$n] = str_replace($m[0][$hi], $rep, $this->pages[$n]);
				}
			}
			$this->pages[$n]=str_replace($s,$r,$this->pages[$n]);
		}
	}
	if($this->DefOrientation=='P')
	{
		$wPt=$this->fwPt;
		$hPt=$this->fhPt;
	}
	else
	{
		$wPt=$this->fhPt;
		$hPt=$this->fwPt;
	}

	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';

	// mPDF 2.4
	$annotid=(3+2*$nb);

	for($n=1;$n<=$nb;$n++)
	{
		// mPDF 3.0 Remove Pattern marker
		$this->pages[$n] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', "\n", $this->pages[$n]);

		//Page
		$this->_newobj();
		$this->_out('<</Type /Page');
		$this->_out('/Parent 1 0 R');
		// mPDF 2.3
		if(isset($this->OrientationChanges[$n])) {
			$this->_out(sprintf('/MediaBox [0 0 %.3f %.3f]',$hPt,$wPt));
			if ($this->displayDefaultOrientation) {
				if ($this->DefOrientation=='P') { $this->_out('/Rotate 270'); }
				else { $this->_out('/Rotate 90'); }
			}
		}
		$this->_out('/Resources 2 0 R');

		// mPDF 2.4
		$annotsnum = count($this->PageLinks[$n]) + count($this->PageAnnots[$n]);
		if ($annotsnum ) {
			$s = '/Annots [ ';
			for($i=0;$i<$annotsnum;$i++) { 
				$s .= ($annotid + $i) . ' 0 R ';
			} 
			$annotid += $annotsnum;
			$s .= '] ';
			$this->_out($s);
		}

		$this->_out('/Contents '.($this->n+1).' 0 R>>');
		$this->_out('endobj');

		//Page content
		$this->_newobj();
		$p=($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
		$this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
		$this->_putstream($p);
		$this->_out('endobj');
	}
	// mPDF 2.4
	$this->_putannots($n);

	//Pages root
	$this->offsets[1]=strlen($this->buffer);
	$this->_out('1 0 obj');
	$this->_out('<</Type /Pages');
	$kids='/Kids [';
	for($i=0;$i<$nb;$i++)
		$kids.=(3+2*$i).' 0 R ';
	$this->_out($kids.']');
	$this->_out('/Count '.$nb);
	$this->_out(sprintf('/MediaBox [0 0 %.3f %.3f]',$wPt,$hPt));
	$this->_out('>>');
	$this->_out('endobj');

}


// mPDF 2.4 Annotations as Objects
function _putannots($n) {
	$nb=$this->page;
	for($n=1;$n<=$nb;$n++)
	{
		$annotobjs = array();
		if(isset($this->PageLinks[$n]) || isset($this->PageAnnots[$n])) {
			// mPDF 2.2 Annotations
			$wPt=$this->pageDim[$n]['w']*$this->k;
			$hPt=$this->pageDim[$n]['h']*$this->k;

			//Links
			if(isset($this->PageLinks[$n])) {
			   foreach($this->PageLinks[$n] as $key => $pl) {
				$this->_newobj();
				$annot='';
				$rect=sprintf('%.3f %.3f %.3f %.3f',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
				$annot.='<</Type /Annot /Subtype /Link /Rect ['.$rect.']';
				$annot .= ' /Contents '.$this->_UTF16BEtextstring($pl[4]);
				$annot .= ' /NM ('.sprintf('%04u-%04u', $n, $key).')';
				$annot .= ' /M '.$this->_textstring('D:'.date('YmdHis'));
				$annot .= ' /Border [0 0 0]';
				// mPDF 3.0
				if (strpos($pl[4],'@')===0) {
					$p=substr($pl[4],1);
					//	$h=isset($this->OrientationChanges[$p]) ? $wPt : $hPt;
					$htarg=$this->pageDim[$p]['h']*$this->k;
					$annot.=sprintf(' /Dest [%d 0 R /XYZ 0 %.3f null]>>',1+2*$p,$htarg);
				}
				else if(is_string($pl[4])) {
					$annot .= ' /A <</S /URI /URI '.$this->_textstring($pl[4]).'>> >>';
				}
				else {
					$l=$this->links[$pl[4]];
					// mPDF 3.0
					//	$h=isset($this->OrientationChanges[$l[0]]) ? $wPt : $hPt;
					$htarg=$this->pageDim[$l[0]]['h']*$this->k;
					$annot.=sprintf(' /Dest [%d 0 R /XYZ 0 %.3f null]>>',1+2*$l[0],$htarg-$l[1]*$this->k);
				}
				$this->_out($annot);
				$this->_out('endobj');
			   }
			}


			// mPDF 2.2 - Annotations - only does Subtype == Text
			if(isset($this->PageAnnots[$n])) {
			   foreach ($this->PageAnnots[$n] as $key => $pl) {
				$this->_newobj();
				$annot='';
				$pl['opt'] = array_change_key_case($pl['opt'], CASE_LOWER);
				$x = $pl['x']; 
				if ($this->annotMargin <> 0 || $x==0 || $x<0) {	// Odd page
				   $x = ($wPt/$this->k) - $this->annotMargin;
				}
				$w = $h = ($this->annotSize * $this->k);
				$a = $x * $this->k;
				// mPDF 3.0
				$b = $hPt - ($pl['y']  * $this->k);
				$rect = sprintf('%.3f %.3f %.3f %.3f', $a, $b-$h, $a+$w, $b);
				$annot .= '<</Type /Annot /Subtype /Text /Rect ['.$rect.']';
				$annot .= ' /Contents '.$this->_UTF16BEtextstring($pl['txt']);
				$annot .= ' /NM ('.sprintf('%04u-%04u', $n, (2000 + $key)).')';
				$annot .= ' /M '.$this->_textstring('D:'.date('YmdHis'));
				$annot .= ' /CreationDate '.$this->_textstring('D:'.date('YmdHis'));
				$annot .= ' /Border [0 0 0]';
				if ($pl['opt']['ca']>0) {
					$annot .= ' /CA '.$pl['opt']['ca'];
				}
				$annot .= ' /C [';
				if (isset($pl['opt']['c']) AND (is_array($pl['opt']['c']))) {
					foreach ($pl['opt']['c'] as $col) {
						$col = intval($col);
						$color = $col <= 0 ? 0 : ($col >= 255 ? 1 : $col / 255);
						$annot .= sprintf(" %.4f", $color);
					}
				}
				else { $annot .= '100 100 0'; }
				$annot .= ']';
				// Usually Author
				if (isset($pl['opt']['t']) AND is_string($pl['opt']['t'])) {
					$annot .= ' /T '.$this->_UTF16BEtextstring($pl['opt']['t']);
				}
				if (isset($pl['opt']['subj'])) {
					$annot .= ' /Subj '.$this->_UTF16BEtextstring($pl['opt']['subj']);
				}
				$iconsapp = array('Comment', 'Help', 'Insert', 'Key', 'NewParagraph', 'Note', 'Paragraph');
				if (isset($pl['opt']['icon']) AND in_array($pl['opt']['icon'], $iconsapp)) {
					$annot .= ' /Name /'.$pl['opt']['icon'];
				}
				else { $annot .= ' /Name /Note'; }
				$annot .= ' /Open false';
				$annot .= '>>';
				$this->_out($annot);
				$this->_out('endobj');
			   }
			}
		}
	}
}



// mPDF 2.2 Annotations
function Annotation($text, $x=0, $y=0, $icon='Note', $author='', $subject='', $opacity=0, $colarray=array(255,255,0)) {
	if ($x==0) { $x = $this->x; }
	if ($y==0) { $y = $this->y; }
	$page = $this->page;
	if ($page < 1) {	// Document has not been started - assume it's for first page
		$page = 1;
		if ($x==0) { $x = $this->lMargin; }
		if ($y==0) { $y = $this->tMargin; }
	}
	// mPDF 2.3
	if (!$this->annotMargin) { $y -= $this->FontSize / 2; }

	if (!$opacity && $this->annotMargin) { $opacity = 1; }
	else if (!$opacity) { $opacity = $this->annotOpacity; }

	if ($this->keep_block_together) {	// Save to array - don't write yet
		$this->ktAnnots[$this->page][]=array('txt' => $text, 'x' => $x, 'y' => $y, 'opt' => array('Icon'=>$icon, 'T'=>$author, 'Subj'=>$subject, 'C'=>$colarray, 'CA'=>$opacity));
		return;
	}
	else if ($this->table_rotate) {
		$this->tbrot_Annots[$this->page][]=array('txt' => $text, 'x' => $x, 'y' => $y, 'opt' => array('Icon'=>$icon, 'T'=>$author, 'Subj'=>$subject, 'C'=>$colarray, 'CA'=>$opacity));
		return;
	}
	else if ($this->kwt) {
		$this->kwt_Annots[$this->page][]=array('txt' => $text, 'x' => $x, 'y' => $y, 'opt' => array('Icon'=>$icon, 'T'=>$author, 'Subj'=>$subject, 'C'=>$colarray, 'CA'=>$opacity));
		return;
	}
	//Put an Annotation on the page
	$this->PageAnnots[$page][] = array('txt' => $text, 'x' => $x, 'y' => $y, 'opt' => array('Icon'=>$icon, 'T'=>$author, 'Subj'=>$subject, 'C'=>$colarray, 'CA'=>$opacity));
	// Save cross-reference to Column buffer
	$ref = count($this->PageAnnots[$this->page])-1;
	$this->columnAnnots[$this->CurrCol][INTVAL($this->x)][INTVAL($this->y)] = $ref;
}



function _putfonts() {
	if ($this->is_MB) {
			$nf=$this->n;
			foreach($this->diffs as $diff) {
				//Encodings
				$this->_newobj();
				$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
				$this->_out('endobj');
			}
			$mqr=get_magic_quotes_runtime();
			set_magic_quotes_runtime(0);
			foreach($this->FontFiles as $file=>$info) {
				//Font file embedding
				$this->_newobj();
				$this->FontFiles[$file]['n']=$this->n;
				$font='';
				$f=fopen($this->_getfontpath().$file,'rb',1);
				if(!$f) {
					$this->Error('Font file not found');
				}
				// mPDF 2.4
				while(!feof($f)) {
					$font .= fread($f, 2048);
				}
				fclose($f);
				$compressed=(substr($file,-2)=='.z');
				if(!$compressed && isset($info['length2'])) {
					$header=($this->ords[substr($font,0,1)]==128);
					if($header) {
						//Strip first binary header
						$font=substr($font,6);
					}
					if($header && $this->ords[substr($font,$info['length1'],1)]==128) {
						//Strip second binary header
						$font=substr($font,0,$info['length1']).substr($font,$info['length1']+6);
					}
				}
				$this->_out('<</Length '.strlen($font));
				if($compressed) {
					$this->_out('/Filter /FlateDecode');
				}
				$this->_out('/Length1 '.$info['length1']);
				if(isset($info['length2'])) {
					$this->_out('/Length2 '.$info['length2'].' /Length3 0');
				}
				$this->_out('>>');
				$this->_putstream($font);
				$this->_out('endobj');
			}
			set_magic_quotes_runtime($mqr);
			foreach($this->fonts as $k=>$font) {
				//Font objects
				$this->fonts[$k]['n']=$this->n+1;
				$type=$font['type'];
				$name=$font['name'];
				if($type=='Type0') { 
					$this->_newobj();
					$this->_out('<</Type /Font');
					$this->_putType0($font);
				}
				else if($type=='core') {
					//Standard font
					$this->_newobj();
					$this->_out('<</Type /Font');
					$this->_out('/BaseFont /'.$name);
					$this->_out('/Subtype /Type1');
					if($name!='Symbol' && $name!='ZapfDingbats') {
						$this->_out('/Encoding /WinAnsiEncoding');
					}
					$this->_out('>>');
					$this->_out('endobj');
				} elseif($type=='Type1' || $type=='TrueType') {
					//Additional Type1 or TrueType font
					$this->_newobj();
					$this->_out('<</Type /Font');
					$this->_out('/BaseFont /'.$name);
					$this->_out('/Subtype /'.$type);
					$this->_out('/FirstChar 32 /LastChar 255');
					$this->_out('/Widths '.($this->n+1).' 0 R');
					$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
					if($font['enc']) {
						if(isset($font['diff'])) {
							$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
						} else {
							$this->_out('/Encoding /WinAnsiEncoding');
						}
					}
					$this->_out('>>');
					$this->_out('endobj');
					//Widths
					$this->_newobj();
					$cw=&$font['cw'];
					$s='[';
					for($i=32;$i<=255;$i++) {
						$s.=$cw[$this->chrs[$i]].' ';
					}
					$this->_out($s.']');
					$this->_out('endobj');
					//Descriptor
					$this->_newobj();
					$s='<</Type /FontDescriptor /FontName /'.$name;
					foreach($font['desc'] as $k=>$v) {
						$s.=' /'.$k.' '.$v;
					}
					$file = $font['file'];
					if($file) {
						$s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
					}
					$this->_out($s.'>>');
					$this->_out('endobj');
				} 
				else {
					//Allow for additional types
					$mtd='_put'.strtolower($type);
					if(!method_exists($this, $mtd)) {
						$this->Error('Unsupported font type: '.$type.' ('.$name.')');
					}
					$this->$mtd($font);
				}
			}
	}
	else {

		$nf=$this->n;
		foreach($this->diffs as $diff)
		{
			//Encodings
			$this->_newobj();
			$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
			$this->_out('endobj');
		}
		$mqr=get_magic_quotes_runtime();
		set_magic_quotes_runtime(0);
		foreach($this->FontFiles as $file=>$info)
		{
			//Font file embedding
			$this->_newobj();
			$this->FontFiles[$file]['n']=$this->n;
			if(defined('FPDF_FONTPATH'))
				$file=FPDF_FONTPATH.$file;
			$size=filesize($file);
			if(!$size)
				$this->Error('Font file not found');
			$this->_out('<</Length '.$size);
			if(substr($file,-2)=='.z')
				$this->_out('/Filter /FlateDecode');
			$this->_out('/Length1 '.$info['length1']);
			if(isset($info['length2']))
				$this->_out('/Length2 '.$info['length2'].' /Length3 0');
			$this->_out('>>');
			$f=fopen($file,'rb');
			// mPDF 2.4
			$s = '';
			while (!feof($f)) {
				$s .= fread($f, 2048);
			}

			$this->_putstream($s);
			fclose($f);
			$this->_out('endobj');
		}
		set_magic_quotes_runtime($mqr);
		foreach($this->fonts as $k=>$font)
		{
			//Font objects
			$this->fonts[$k]['n']=$this->n+1;
			$type=$font['type'];
			$name=$font['name'];
			if($type=='core')
			{
				//Standard font
				$this->_newobj();
				$this->_out('<</Type /Font');
				$this->_out('/BaseFont /'.$name);
				$this->_out('/Subtype /Type1');
				if($name!='Symbol' and $name!='ZapfDingbats')
					$this->_out('/Encoding /WinAnsiEncoding');
				$this->_out('>>');
				$this->_out('endobj');
			}
			elseif($type=='Type1' or $type=='TrueType')
			{
				//Additional Type1 or TrueType font
				$this->_newobj();
				$this->_out('<</Type /Font');
				$this->_out('/BaseFont /'.$name);
				$this->_out('/Subtype /'.$type);
				$this->_out('/FirstChar 32 /LastChar 255');
				$this->_out('/Widths '.($this->n+1).' 0 R');
				$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
				if($font['enc'])
				{
					if(isset($font['diff']))
						$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
					else
						$this->_out('/Encoding /WinAnsiEncoding');
				}
				$this->_out('>>');
				$this->_out('endobj');
				//Widths
				$this->_newobj();
				$cw=&$font['cw'];
				$s='[';
				for($i=32;$i<=255;$i++)
					$s.=$cw[$this->chrs[$i]].' ';
				$this->_out($s.']');
				$this->_out('endobj');
				//Descriptor
				$this->_newobj();
				$s='<</Type /FontDescriptor /FontName /'.$name;
				foreach($font['desc'] as $k=>$v)
					$s.=' /'.$k.' '.$v;
				$file=$font['file'];
				if($file)
					$s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
				$this->_out($s.'>>');
				$this->_out('endobj');
			}
			else
			{
				//Allow for additional types including TrueTypeUnicode
				$mtd='_put'.strtolower($type);
				if(!method_exists($this,$mtd))
					$this->Error('Unsupported font type: '.$type.' ('.$name.')');
				$this->$mtd($font);
			}
		}


	}
}




// Unicode fonts
function _puttruetypeunicode($font) {
		// Edited mPDF 2.0 (using help from TCPDF) to allow text to be copied from PDF doc to clipboard - adding CIDToGIDMap
			// Type0 Font
			// A composite font - a font composed of other fonts, organized hierarchically
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/Subtype /Type0');
			$this->_out('/BaseFont /'.$font['name'].'');
			$this->_out('/Encoding /Identity-H'); //The horizontal identity mapping for 2-byte CIDs; may be used with CIDFonts using any Registry, Ordering, and Supplement values.
			$this->_out('/DescendantFonts ['.($this->n + 1).' 0 R]');
			$this->_out('/ToUnicode '.($this->n + 2).' 0 R');
			$this->_out('>>');
			$this->_out('endobj');
			
			// CIDFontType2
			// A CIDFont whose glyph descriptions are based on TrueType font technology
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/Subtype /CIDFontType2');
			$this->_out('/BaseFont /'.$font['name'].'');
			$this->_out('/CIDSystemInfo '.($this->n + 2).' 0 R'); 
			$this->_out('/FontDescriptor '.($this->n + 3).' 0 R');
			if (isset($font['desc']['MissingWidth'])){
				$this->_out('/DW '.$font['desc']['MissingWidth'].''); // The default width for glyphs in the CIDFont MissingWidth
			}
			$w = "";
			foreach ($font['cw'] as $cid => $width) {
				$w .= ''.$cid.' ['.$width.'] '; // define a specific width for each individual CID
			}
			$this->_out('/W ['.$w.']'); // A description of the widths for the glyphs in the CIDFont

			$this->_out('/CIDToGIDMap '.($this->n + 4).' 0 R');

			$this->_out('>>');
			$this->_out('endobj');
			
			// Added mPDF 2.0 (from TCPDF)
			// ToUnicode
			// is a stream object that contains the definition of the CMap
			// (PDF Reference 1.3 chap. 5.9)
			$this->_newobj();
			$this->_out('<</Length 345>>');
			$this->_out('stream');
			$this->_out('/CIDInit /ProcSet findresource begin');
			$this->_out('12 dict begin');
			$this->_out('begincmap');
			$this->_out('/CIDSystemInfo');
			$this->_out('<</Registry (Adobe)');
			$this->_out('/Ordering (UCS)');
			$this->_out('/Supplement 0');
			$this->_out('>> def');
			$this->_out('/CMapName /Adobe-Identity-UCS def');
			$this->_out('/CMapType 2 def');
			$this->_out('1 begincodespacerange');
			$this->_out('<0000> <FFFF>');
			$this->_out('endcodespacerange');
			$this->_out('1 beginbfrange');
			$this->_out('<0000> <FFFF> <0000>');
			$this->_out('endbfrange');
			$this->_out('endcmap');
			$this->_out('CMapName currentdict /CMap defineresource pop');
			$this->_out('end');
			$this->_out('end');
			$this->_out('endstream');
			$this->_out('endobj');

			// CIDSystemInfo dictionary
			// A dictionary containing entries that define the character collection of the CIDFont.
			$this->_newobj();
			$this->_out('<</Registry (Adobe)'); // A string identifying an issuer of character collections
			$this->_out('/Ordering (UCS)'); // A string that uniquely names a character collection issued by a specific registry
			$this->_out('/Supplement 0'); // The supplement number of the character collection.
			$this->_out('>>');
			$this->_out('endobj');
			
			// Font descriptor
			// A font descriptor describing the CIDFont's default metrics other than its glyph widths
			$this->_newobj();
			$this->_out('<</Type /FontDescriptor');
			$this->_out('/FontName /'.$font['name']);
			foreach ($font['desc'] as $key => $value) {
				$this->_out('/'.$key.' '.$value);
			}
			if ($font['file']) {
				// A stream containing a TrueType font program
				$this->_out('/FontFile2 '.$this->FontFiles[$font['file']]['n'].' 0 R');
			}
			$this->_out('>>');
			$this->_out('endobj');

			// Embed CIDToGIDMap
			// A specification of the mapping from CIDs to glyph indices
			$this->_newobj();
			$ctgfile = $this->_getfontpath().$font['ctg'];
			if(!file_exists($ctgfile)) {
				$this->Error('Font file not found: '.$ctgfile);
			}
			$size = filesize($ctgfile);
			$this->_out('<</Length '.$size.'');
			if(substr($ctgfile, -2) == '.z') { // check file extension
				/* Decompresses data encoded using the public-domain 
				zlib/deflate compression method, reproducing the 
				original text or binary data */
				$this->_out('/Filter /FlateDecode');
			}
			$this->_out('>>');
			$this->_putstream(file_get_contents($ctgfile));
			$this->_out('endobj');

}


function _putfontwidths($font, $cidoffset=0) {
			ksort($font['cw']);
			$rangeid = 0;
			$range = array();
			$prevcid = -2;
			$prevwidth = -1;
			$interval = false;
			// for each character
			foreach ($font['cw'] as $cid => $width) {
				$cid -= $cidoffset;
				if ($width != $font['dw']) {
					if ($cid == ($prevcid + 1)) {
						// consecutive CID
						if ($width == $prevwidth) {
							if ($width == $range[$rangeid][0]) {
								$range[$rangeid][] = $width;
							} else {
								array_pop($range[$rangeid]);
								// new range
								$rangeid = $prevcid;
								$range[$rangeid] = array();
								$range[$rangeid][] = $prevwidth;
								$range[$rangeid][] = $width;
							}
							$interval = true;
							$range[$rangeid]['interval'] = true;
						} else {
							if ($interval) {
								// new range
								$rangeid = $cid;
								$range[$rangeid] = array();
								$range[$rangeid][] = $width;
							} else {
								$range[$rangeid][] = $width;
							}
							$interval = false;
						}
					} else {
						// new range
						$rangeid = $cid;
						$range[$rangeid] = array();
						$range[$rangeid][] = $width;
						$interval = false;
					}
					$prevcid = $cid;
					$prevwidth = $width;
				}
			}
			// optimize ranges
			$prevk = -1;
			$nextk = -1;
			$prevint = false;
			foreach ($range as $k => $ws) {
				$cws = count($ws);
				if (($k == $nextk) AND (!$prevint) AND ((!isset($ws['interval'])) OR ($cws < 4))) {
					if (isset($range[$k]['interval'])) {
						unset($range[$k]['interval']);
					}
					$range[$prevk] = array_merge($range[$prevk], $range[$k]);
					unset($range[$k]);
				} else {
					$prevk = $k;
				}
				$nextk = $k + $cws;
				if (isset($ws['interval'])) {
					if ($cws > 3) {
						$prevint = true;
					} else {
						$prevint = false;
					}
					unset($range[$k]['interval']);
					--$nextk;
				} else {
					$prevint = false;
				}
			}
			// output data
			$w = '';
			foreach ($range as $k => $ws) {
				if (count(array_count_values($ws)) == 1) {
					// interval mode is more compact
					$w .= ' '.$k.' '.($k + count($ws) - 1).' '.$ws[0];
				} else {
					// range mode
					$w .= ' '.$k.' [ '.implode(' ', $ws).' ]';
				}
			}
			$this->_out('/W ['.$w.' ]');
}


// from class PDF_Chinese CJK EXTENSIONS
function _putType0($font)
{
	//Type0
	$this->_out('/Subtype /Type0');
	$this->_out('/BaseFont /'.$font['name'].'-'.$font['CMap']);
	$this->_out('/Encoding /'.$font['CMap']);
	$this->_out('/DescendantFonts ['.($this->n+1).' 0 R]');
	$this->_out('>>');
	$this->_out('endobj');
	//CIDFont
	$this->_newobj();
	$this->_out('<</Type /Font');
	$this->_out('/Subtype /CIDFontType0');
	$this->_out('/BaseFont /'.$font['name']);

	$cidinfo = '/Registry '.$this->_textstring('Adobe');
	$cidinfo .= ' /Ordering '.$this->_textstring($font['registry']['ordering']);
	$cidinfo .= ' /Supplement '.$font['registry']['supplement'];
	$this->_out('/CIDSystemInfo <<'.$cidinfo.'>>');

	$this->_out('/FontDescriptor '.($this->n+1).' 0 R');
	if (isset($font['MissingWidth'])){
		$this->_out('/DW '.$font['MissingWidth'].''); 
	}
	$this->_putfontwidths($font, 31);
	$this->_out('>>');
	$this->_out('endobj');

	//Font descriptor
	$this->_newobj();
	$s = '<</Type /FontDescriptor /FontName /'.$font['name'];
	foreach ($font['desc'] as $k => $v) {
		if ($k != 'Style') {
			$s .= ' /'.$k.' '.$v.'';
		}
	}
	$this->_out($s.'>>');
	$this->_out('endobj');
}


function _putimages()
{
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	reset($this->images);
	while(list($file,$info)=each($this->images))
	{
		$this->_newobj();
		$this->images[$file]['n']=$this->n;
		$this->_out('<</Type /XObject');
		$this->_out('/Subtype /Image');
		$this->_out('/Width '.$info['w']);
		$this->_out('/Height '.$info['h']);
		if($info['cs']=='Indexed')
			$this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
		else
		{
			$this->_out('/ColorSpace /'.$info['cs']);
			if($info['cs']=='DeviceCMYK')
				$this->_out('/Decode [1 0 1 0 1 0 1 0]');
		}
		$this->_out('/BitsPerComponent '.$info['bpc']);
		$this->_out('/Filter /'.$info['f']);
		if(isset($info['parms']))
			$this->_out($info['parms']);
		if(isset($info['trns']) and is_array($info['trns']))
		{
			$trns='';
			for($i=0;$i<count($info['trns']);$i++)
				$trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
			$this->_out('/Mask ['.$trns.']');
		}
		$this->_out('/Length '.strlen($info['data']).'>>');
		$this->_putstream($info['data']);
		unset($this->images[$file]['data']);
		$this->_out('endobj');
		//Palette
		if($info['cs']=='Indexed')
		{
			$this->_newobj();
			$pal=($this->compress) ? gzcompress($info['pal']) : $info['pal'];
			$this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
			$this->_putstream($pal);
			$this->_out('endobj');
		}
	}
}

function _putinfo()
{
	$this->_out('/Producer '.$this->_UTF16BEtextstring('mPDF '.mPDF_VERSION));
	if(!empty($this->title))
		$this->_out('/Title '.$this->_UTF16BEtextstring($this->title));
	if(!empty($this->subject))
		$this->_out('/Subject '.$this->_UTF16BEtextstring($this->subject));
	if(!empty($this->author))
		$this->_out('/Author '.$this->_UTF16BEtextstring($this->author));
	if(!empty($this->keywords))
		$this->_out('/Keywords '.$this->_UTF16BEtextstring($this->keywords));
	if(!empty($this->creator))
		$this->_out('/Creator '.$this->_UTF16BEtextstring($this->creator));
	$this->_out('/CreationDate '.$this->_textstring('D:'.date('YmdHis')));
	$this->_out('/ModDate '.$this->_textstring('D:'.date('YmdHis')));
}


function _putcatalog()
{
	$this->_out('/Type /Catalog');
	$this->_out('/Pages 1 0 R');
	if($this->ZoomMode=='fullpage')	$this->_out('/OpenAction [3 0 R /Fit]');
	elseif($this->ZoomMode=='fullwidth') $this->_out('/OpenAction [3 0 R /FitH null]');
	elseif($this->ZoomMode=='real')	$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
	elseif(!is_string($this->ZoomMode))	$this->_out('/OpenAction [3 0 R /XYZ null null '.($this->ZoomMode/100).']');
	if($this->LayoutMode=='single')	$this->_out('/PageLayout /SinglePage');
	elseif($this->LayoutMode=='continuous')	$this->_out('/PageLayout /OneColumn');
	elseif($this->LayoutMode=='two') {
	  if ($this->useOddEven) { $this->_out('/PageLayout /TwoColumnRight'); }
	  else { $this->_out('/PageLayout /TwoColumnLeft'); }
	}
  //EDITEI - added lines below
  if(count($this->BMoutlines)>0)
  {
      $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
      $this->_out('/PageMode /UseOutlines');
  }
  if(is_int(strpos($this->DisplayPreferences,'FullScreen'))) $this->_out('/PageMode /FullScreen');


  if($this->DisplayPreferences || ($this->directionality == 'rtl'))
  {
     $this->_out('/ViewerPreferences<<');
     if(is_int(strpos($this->DisplayPreferences,'HideMenubar'))) $this->_out('/HideMenubar true');
     if(is_int(strpos($this->DisplayPreferences,'HideToolbar'))) $this->_out('/HideToolbar true');
     if(is_int(strpos($this->DisplayPreferences,'HideWindowUI'))) $this->_out('/HideWindowUI true');
     if(is_int(strpos($this->DisplayPreferences,'DisplayDocTitle'))) $this->_out('/DisplayDocTitle true');
     if(is_int(strpos($this->DisplayPreferences,'CenterWindow'))) $this->_out('/CenterWindow true');
     if(is_int(strpos($this->DisplayPreferences,'FitWindow'))) $this->_out('/FitWindow true');
     if($this->directionality == 'rtl') $this->_out('/Direction /R2L');
     $this->_out('>>');
  }
}

// mPDF 2.4 - ActiveForms removed
// Inactive function left for backwards compatability
function SetUserRights($enable=true, $annots="", $form="", $signature="") {
	// Does nothing
}

function _enddoc()
{
	$this->_putpages();
	$this->_putresources();
	//Info
	$this->_newobj();
	$this->_out('<<');
	$this->_putinfo();
	$this->_out('>>');
	$this->_out('endobj');
	//Catalog
	$this->_newobj();
	$this->_out('<<');
	$this->_putcatalog();
	$this->_out('>>');
	$this->_out('endobj');
	//Cross-ref
	$o=strlen($this->buffer);
	$this->_out('xref');
	$this->_out('0 '.($this->n+1));
	$this->_out('0000000000 65535 f ');
	for($i=1; $i <= $this->n ; $i++)
		$this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
	//Trailer
	$this->_out('trailer');
	$this->_out('<<');
	$this->_puttrailer();
	$this->_out('>>');
	$this->_out('startxref');
	$this->_out($o);
	$this->_out('%%EOF');
	$this->state=3;
}

function _beginpage($orientation,$mgl='',$mgr='',$mgt='',$mgb='',$mgh='',$mgf='',$ohname='',$ehname='',$ofname='',$efname='',$ohvalue=0,$ehvalue=0,$ofvalue=0,$efvalue=0,$mgtfp=0,$mgbfp=0)
{
	$this->page++;
	$this->pages[$this->page]='';
	$this->state=2;
	$resetHTMLHeadersrequired = false;

	//Page orientation
	if(!$orientation)
		$orientation=$this->DefOrientation;
	else
	{
		$orientation=strtoupper(substr($orientation,0,1));
		if($orientation!=$this->DefOrientation)
			$this->OrientationChanges[$this->page]=true;
	}
	if($orientation!=$this->CurOrientation)
	{
		//Change orientation
		if($orientation=='P')
		{
			$this->wPt=$this->fwPt;
			$this->hPt=$this->fhPt;
			$this->w=$this->fw;
			$this->h=$this->fh;
		   // mPDF 2.3
		   if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation=='P') {
			$this->tMargin = $this->orig_tMargin;
			$this->bMargin = $this->orig_bMargin;
			$this->DeflMargin = $this->orig_lMargin;
			$this->DefrMargin = $this->orig_rMargin;
			$this->margin_header = $this->orig_hMargin;
			$this->margin_footer = $this->orig_fMargin;
		   }
		   else { $resetHTMLHeadersrequired = true; }
		}
		else
		{
			$this->wPt=$this->fhPt;
			$this->hPt=$this->fwPt;
			$this->w=$this->fh;
			$this->h=$this->fw;
		   // mPDF 2.3
		   if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation=='P') {
			$this->tMargin = $this->orig_lMargin;
			$this->bMargin = $this->orig_rMargin;
			$this->DeflMargin = $this->orig_bMargin;
			$this->DefrMargin = $this->orig_tMargin;
			$this->margin_header = $this->orig_hMargin;
			$this->margin_footer = $this->orig_fMargin;
		   }
		   else { $resetHTMLHeadersrequired = true; }

		}
		// Added v1.4
		$this->CurOrientation=$orientation;
		$this->ResetMargins();
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;

		$this->PageBreakTrigger=$this->h-$this->bMargin;

	}

	// mPDF 2.2 Annotations
	$this->pageDim[$this->page]['w']=$this->w ;
	$this->pageDim[$this->page]['h']=$this->h ;

	// If Page Margins are re-defined
	// strlen()>0 is used to pick up (integer) 0, (string) '0', or set value
	if ((strlen($mgl)>0 && $this->DeflMargin != $mgl) || (strlen($mgr)>0 && $this->DefrMargin != $mgr) || (strlen($mgt)>0 && $this->tMargin != $mgt) || (strlen($mgb)>0 && $this->bMargin != $mgb) || (strlen($mgh)>0 && $this->margin_header!=$mgh) || (strlen($mgf)>0 && $this->margin_footer!=$mgf)) {
		if (strlen($mgl)>0)  $this->DeflMargin = $mgl;
		if (strlen($mgr)>0)  $this->DefrMargin = $mgr;
		if (strlen($mgt)>0)  $this->tMargin = $mgt;
		if (strlen($mgb)>0)  $this->bMargin = $mgb;
		if (strlen($mgh)>0)  $this->margin_header=$mgh;
		if (strlen($mgf)>0)  $this->margin_footer=$mgf;
		$this->ResetMargins();
		$this->SetAutoPageBreak(true,$this->bMargin);
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$resetHTMLHeadersrequired = true; 
	}

	// Change Top/Bottom margins for "first page"
	if ($this->page_box['first_page_top_margin'] || $this->page_box['first_page_bottom_margin']) {	// next page of new @page definition
		$this->tMargin -= $this->page_box['first_page_top_margin'];
		$this->bMargin -= $this->page_box['first_page_bottom_margin'];
	}
	$this->page_box['first_page_top_margin']=0;
	$this->page_box['first_page_bottom_margin']=0;
	if ($this->page_box['start_page']==$this->page) {	// first page of new @page definition
		$this->tMargin += $mgtfp;
		$this->bMargin += $mgbfp;
		$this->page_box['first_page_top_margin']=$mgtfp;
		$this->page_box['first_page_bottom_margin']=$mgbfp;
	}

	// Moved in v1.4 to allow for changes in page orientation (Sets lMargin, rMargin, MarginCorrection)
	$this->ResetMargins();
	$this->SetAutoPageBreak(true,$this->bMargin);

	// Reset column top margin
	$this->y0 = $this->tMargin;

	$this->x=$this->lMargin;
	$this->y=$this->tMargin;
	$this->FontFamily='';

	// HEADERS AND FOOTERS
	if ($ohvalue<0 || strtoupper($ohvalue)=='OFF') { 
		$this->HTMLHeader = ''; 
		$this->headerDetails['odd'] = array(); 
		$resetHTMLHeadersrequired = true;
	}
	else if ($ohname && $ohvalue>0) {
	   if (preg_match('/^html_(.*)$/i',$ohname,$n)) {
		$this->HTMLHeader = $this->pageHTMLheaders[$n[1]];
		$this->headerDetails['odd'] = array(); 
		$resetHTMLHeadersrequired = true;
	   }
	   else {
		$this->headerDetails['odd'] = $this->pageheaders[$ohname]; 
		$this->HTMLHeader = ''; 
		$resetHTMLHeadersrequired = false;
	   }
	}

	if ($ehvalue<0 || strtoupper($ehvalue)=='OFF') { 
		$this->HTMLHeaderE = ''; 
		$this->headerDetails['even'] = array(); 
		$resetHTMLHeadersrequired = true;
	}
	else if ($ehname && $ehvalue>0) {
	   if (preg_match('/^html_(.*)$/i',$ehname,$n)) {
		$this->HTMLHeaderE = $this->pageHTMLheaders[$n[1]]; 
		$this->headerDetails['even'] = array(); 
		$resetHTMLHeadersrequired = true;
	   }
	   else {
		$this->headerDetails['even'] = $this->pageheaders[$ehname];
		$this->HTMLHeaderE = ''; 
		$resetHTMLHeadersrequired = false;
	   }
	}

	if ($ofvalue<0 || strtoupper($ofvalue)=='OFF') { 
		$this->HTMLFooter = ''; 
		$this->footerDetails['odd'] = array(); 
		$resetHTMLHeadersrequired = true;
	}
	else if ($ofname && $ofvalue>0) {
	   if (preg_match('/^html_(.*)$/i',$ofname,$n)) {
		$this->HTMLFooter = $this->pageHTMLfooters[$n[1]];
		$this->footerDetails['odd'] = array(); 
		$resetHTMLHeadersrequired = true;
	   }
	   else {
		$this->footerDetails['odd'] = $this->pagefooters[$ofname];
		$this->HTMLFooter = ''; 
		$resetHTMLHeadersrequired = true;
	   }
	}

	if ($efvalue<0 || strtoupper($efvalue)=='OFF') { 
		$this->HTMLFooterE = ''; 
		$this->footerDetails['even'] = array(); 
		$resetHTMLHeadersrequired = true;
	}
	else if ($efname && $efvalue>0) {
	   if (preg_match('/^html_(.*)$/i',$efname,$n)) {
		$this->HTMLFooterE = $this->pageHTMLfooters[$n[1]]; 
		$this->footerDetails['even'] = array(); 
		$resetHTMLHeadersrequired = true;
	   }
	   else {
		$this->footerDetails['even'] = $this->pagefooters[$efname]; 
		$this->HTMLFooterE = ''; 
		$resetHTMLHeadersrequired = true;
	   }
	}

	if ($resetHTMLHeadersrequired && !$this->beforedoc) {	// beforedoc set in SetHTMLHeader() to avoid duplication
		$this->SetHTMLHeader($this->HTMLHeader );
		$this->SetHTMLHeader($this->HTMLHeaderE ,'E');
		$this->SetHTMLFooter($this->HTMLFooter );
		$this->SetHTMLFooter($this->HTMLFooterE ,'E');
	}

}

function _endpage()
{
	// mPDF 2.4 Float Images
	if (count($this->floatbuffer)) {
		$this->objectbuffer = $this->floatbuffer;
		$this->printobjectbuffer(false);
		$this->objectbuffer = array();
		$this->floatbuffer = array();
		$this->floatmargins = array();
	}

	//End of page contents
	$this->state=1;
}

function _newobj()
{
	//Begin a new object
	$this->n++;
	$this->offsets[$this->n]=strlen($this->buffer);
	$this->_out($this->n.' 0 obj');
}

function _dounderline($x,$y,$txt)
{
	// Now print line exactly where $y secifies - called from Text() and Cell() - adjust  position there
	// WORD SPACING
      $w =($this->GetStringWidth($txt)*$this->k) + ($this->charspacing * mb_strlen( $txt, $this->mb_encoding )) 
		 + ( $this->ws * mb_substr_count( $txt, ' ', $this->mb_encoding ));
	return sprintf('%.3f %.3f %.3f %.3f re f',$x*$this->k,($this->h-$y)*$this->k,$w,0.05*$this->FontSizePt);
}

// mPDF 2.5
// mPDF 3.0
function _parsephp($file) {
	list($accessfile, $isTemp) = $this->_get_local_file($file);
	if (!$accessfile) { 
		if ($this->showImageErrors) $this->Error("PHP parser: unable to open file : ".$file); 
		else return -1; 
	}
	if (function_exists('curl_init')) { 
		$c = curl_init();
		curl_setopt($c, CURLOPT_HEADER, true);
		curl_setopt($c, CURLOPT_NOBODY, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_URL, $accessfile);
		$contents = curl_exec($c);
		curl_close($c);
		if ($isTemp) { unlink($accessfile); } 
		preg_match('/Content-Type:\s*image\/(\w+)/i',$contents,$m);
		$type = strtolower(trim($m[1]));
	}
	else {
		$fh = @fopen($accessfile, "rb");
		if ($fh) {
			$hdr = fread($fh, 10);
			fclose($fh);
			if ($isTemp) { unlink($accessfile); } 
			if (substr($hdr, 0, 6)== "GIF87a" || substr($hdr, 0, 6)== "GIF89a") { 
				$type = 'gif';
			}
			else if (substr($hdr, 0, 8)== $this->chrs[137].'PNG'.$this->chrs[13].$this->chrs[10].$this->chrs[26].$this->chrs[10]) { 
				$type = 'png';
			}
			else if (substr($hdr, 6, 4)== 'JFIF') { 
				$type = 'jpeg'; 
			}
			else {
				if ($this->showImageErrors) $this->Error("PHP parser error: unable to determine file type: ".$accessfile); 
				return -1; 
			}
		}
		else {
			if ($this->showImageErrors) $this->Error("PHP parser error: cURL is not enabled and unable to open file: ".$accessfile); 
			return -1; 
		}
	}
	$res = -1;
	if ($type == 'jpeg') { $res = $this->_parsejpg($file); }
	else if ($type == 'gif') { $res = $this->_parsegif($file); }
	else if ($type == 'png') { $res = $this->_parsepng($file); }
	// mPDF 3.0
	else {
		if ($this->showImageErrors) $this->Error("PHP parser: file type not recognised from php Header: ".$file); 
		else return -1; 
	}
	// mPDF 3.0 - Try again PNG
	if ($type=='png' && $res < -1) { 
		$res=$this->_parsepng2($file); 
		if ($res['UID']) { $file = $res['UID']; }	// A unique alpha blended PNG created
	}
	return $res; 
}


function _parsejpg($file) {
	// mPDF 2.0
	list($accessfile, $isTemp) = $this->_get_local_file($file);
 
	if (!$accessfile) { 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error("JPG parser: unable to open file : ".$file); 
		else return -1; 
	}

	$a=GetImageSize($accessfile);

	//Extract info from a JPEG file
	if(!$a) { 
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error('Missing or incorrect JPEG image file: '.$file); 
		else return -1; 
	}
	if($a[2]!=2) { 
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error('Not a JPEG file: '.$file); 
		else return -1; 
	}
	if(!isset($a['channels']) or $a['channels']==3) { $colspace='DeviceRGB'; }	
	elseif($a['channels']==4) { $colspace='DeviceCMYK'; }
	else { $colspace='DeviceGray'; }

	$bpc=isset($a['bits']) ? $a['bits'] : 8;

	//Read whole file
	$data=file_get_contents($accessfile);
	if ($isTemp) { unlink($accessfile); } 
	return array('w'=>$a[0],'h'=>$a[1],'cs'=>$colspace,'bpc'=>$bpc,'f'=>'DCTDecode','data'=>$data);
}

// mPDF 3.0 - Try again PNG
function _parsepng2($file, $problem) {
	list($accessfile, $isTemp) = $this->_get_local_file($file);
	if (!$accessfile) { 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error("PNG parser (2): unable to open file : ".$file); 
		else return -1; 
	}
	$im = imagecreatefrompng($accessfile);
	$w = imagesx($im);
	$h = imagesy($im);
	if ($isTemp) { unlink($accessfile); } 
	if ($im) {
		$dest = imagecreatetruecolor($w, $h);
		if ($problem == -3) {	// Alpha channel set
			$r = $g = $b = 255;
			if ($this->tableLevel) {
				if ($this->cell[$this->row][$this->col]['bgcolor']) {
					$cor = ConvertColor($this->cell[$this->row][$this->col]['bgcolor']);
					if ($cor) {
						$r = $cor['R'];
						$g = $cor['G'];
						$b = $cor['B'];
						$UID = true;
					}
				}
			}
			if (!$UID) {
			   for ($bl=$this->blklvl;$bl>=0;$bl--) {
				if ($this->blk[$bl]['bgcolor']) {
					$r = $this->blk[$bl]['bgcolorarray']['R'];
					$g = $this->blk[$bl]['bgcolorarray']['G'];
					$b = $this->blk[$bl]['bgcolorarray']['B'];
					$UID = true;
					break;
				}
			   }
			}
			$ic = imagecolorallocate($dest, $r,$g,$b);
			imagefill($dest, 0, 0, $ic);
			imagealphablending($dest, 1);
			imagealphablending($im, 1);
		}
		imagecopy($dest, $im, 0, 0, 0, 0, $w, $h);
		$tempfile = '_tempImgPNG'.RAND(1,10000).'.png';
		imagepng($dest, $tempfile );
		imagedestroy($im);
		imagedestroy($dest);
		$ret = $this->_parsepng($tempfile ) ;
		if ($UID) { $ret['UID'] = $tempfile; }	// A unique alpha blended image created
		unlink($tempfile ); 
		return $ret;
	}
	else {
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error("PNG parser (2): PNG error: ".$file); 
		else return -1; 
	}
}

function _parsepng($file) {
	// mPDF 2.0
	list($accessfile, $isTemp) = $this->_get_local_file($file);

	if (!$accessfile) { 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error("PNG parser: unable to open file : ".$file); 
		else return -1; 
	}

	$f=@fopen($accessfile,'rb');

	//Extract info from a PNG file
	// mPDF 2.0
	if (!$f) { 
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error("PNG parser: unable to open file - access-name: $accessfile - filename: ".$file); 
		else return -1; 
	}

	//Check signature
	if(fread($f,8)!=$this->chrs[137].'PNG'.$this->chrs[13].$this->chrs[10].$this->chrs[26].$this->chrs[10]) { 
		fclose($f);
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error('Not a PNG file: '.$file); 
		else return -1; 
	}
	//Read header chunk
	fread($f,4);
	if(fread($f,4)!='IHDR') { 
		fclose($f);
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error('Incorrect PNG file (no IHDR block found): '.$file); 
		else return -1; 
	}
	$w=$this->_freadint($f);
	$h=$this->_freadint($f);
	$bpc=$this->ords[fread($f,1)];
	if($bpc>8) { 
		fclose($f);
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors===2) $this->Error('16-bit depth not supported: '.$file); 
		else return -2; 
	}
	$ct=$this->ords[fread($f,1)];
	if($ct==0) { $colspace='DeviceGray'; }
	elseif($ct==2) { $colspace='DeviceRGB'; }
	elseif($ct==3) { $colspace='Indexed'; }
	else { 
		fclose($f);
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors===2) $this->Error('Alpha channel not supported: '.$file); 
		else return -3; 
	}
	if($this->ords[fread($f,1)]!=0) { 
		fclose($f);
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors===2) $this->Error('Unknown compression method: '.$file); 
		else return -2; 
	}
	if($this->ords[fread($f,1)]!=0) { 
		fclose($f);
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors===2) $this->Error('Unknown PNG filter method: '.$file); 
		else return -2; 
	}
	if($this->ords[fread($f,1)]!=0) { 
		fclose($f);
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors===2) $this->Error('PNG Interlacing not supported: '.$file); 
		else return -2; 
	}
	fread($f,4);
	$parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
	//Scan chunks looking for palette, transparency and image data
	$pal='';
	$trns='';
	$data='';
	do
	{
		$n=$this->_freadint($f);
		$type=fread($f,4);
		if($type=='PLTE')
		{
			//Read palette
			$pal=fread($f,$n);
			fread($f,4);
		}
		elseif($type=='tRNS')
		{
			//Read transparency info
			$t=fread($f,$n);
			if($ct==0) $trns=array($this->ords[substr($t,1,1)]);
			elseif($ct==2) $trns=array($this->ords[substr($t,1,1)],$this->ords[substr($t,3,1)],$this->ords[substr($t,5,1)]);
			else
			{
				$pos=strpos($t,$this->chrs[0]);
				if(is_int($pos)) $trns=array($pos);
			}
			fread($f,4);
		}
		// mPDF 2.4
		elseif($type=='IDAT')
		{
			// mPDF 2.5
			$rem = $n;
			while ($rem > 0) {
			  $x = fread($f, min($rem, 2048));
			  $data.= $x;
			  $rem -= strlen($x);
			}
			fread($f,4);
		}
		elseif($type=='IEND') { break; }
		// mPDF 2.3 - to failsafe for errors
		else if (preg_match('/[a-zA-Z]{4}/',$type)) { fread($f,$n+4); }
		else {
			fclose($f);
			if ($isTemp) { unlink($accessfile); } 
			// mPDF 3.0
			if ($this->showImageErrors) $this->Error("PNG parser: unable to parse file data: ".$file); 
			else return -1; 
		}
	}
	while($n);
	fclose($f);
	if ($isTemp) { unlink($accessfile); } 
	if($colspace=='Indexed' and empty($pal)) {
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error('Missing palette in '.$file);
		else return -1; 
	}
	// mPDF 2.0
	return array('w'=>$w,'h'=>$h,'cs'=>$colspace,'bpc'=>$bpc,'f'=>'FlateDecode','parms'=>$parms,'pal'=>$pal,'trns'=>$trns,'data'=>$data);
}


function _parsegif($file) { //EDITEI - GIF support is now included 
	// mPDF 2.0
	list($accessfile, $isTemp) = $this->_get_local_file($file);
 
	if (!$accessfile) { 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error("GIF parser: unable to open file : ".$file); 
		else return -1; 
	}

	//Function by J�r�me Fenal
	require_once(_MPDF_PATH .'gif.php'); //GIF class in pure PHP from Yamasoft (http://www.yamasoft.com/php-gif.zip)

	$h=0;
	$w=0;
	$gif=new CGIF();
	// mPDF 2.0
	if (!$gif->loadFile($accessfile, 0)) { 
		if ($isTemp) { unlink($accessfile); } 
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error("GIF parser: unable to open file - access-name: $accessfile - filename: ".$file); 
		else return -1; 
	}

	if($gif->m_img->m_gih->m_bLocalClr) {
		$nColors = $gif->m_img->m_gih->m_nTableSize;
		$pal = $gif->m_img->m_gih->m_colorTable->toString();
		if($bgColor != -1) {
			$bgColor = $this->m_img->m_gih->m_colorTable->colorIndex($bgColor);
		}
		$colspace='Indexed';
	} elseif($gif->m_gfh->m_bGlobalClr) {
		$nColors = $gif->m_gfh->m_nTableSize;
		$pal = $gif->m_gfh->m_colorTable->toString();
		if((isset($bgColor)) and $bgColor != -1) {
			$bgColor = $gif->m_gfh->m_colorTable->colorIndex($bgColor);
		}
		$colspace='Indexed';
	} else {
		$nColors = 0;
		$bgColor = -1;
		$colspace='DeviceGray';
		$pal='';
	}

	$trns='';
	if($gif->m_img->m_bTrans && ($nColors > 0)) {
		$trns=array($gif->m_img->m_nTrans);
	}

	$data=$gif->m_img->m_data;
	$w=$gif->m_gfh->m_nWidth;
	$h=$gif->m_gfh->m_nHeight;

	// mPDF 2.0
	if ($isTemp) { unlink($accessfile); } 

	if($colspace=='Indexed' and empty($pal)) {
		// mPDF 3.0
		if ($this->showImageErrors) $this->Error('Missing palette in '.$file);
		else return -1; 
	}

	if ($this->compress) {
		$data=gzcompress($data);
		return array( 'w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>8, 'f'=>'FlateDecode', 'pal'=>$pal, 'trns'=>$trns, 'data'=>$data);
	} 
	else {
		return array( 'w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>8, 'pal'=>$pal, 'trns'=>$trns, 'data'=>$data);
	} 
}

// mPDF 2.2 - WMF
function _parsewmf($file) {
        $this->gdiObjectArray = array();

        $a=unpack('stest',"\1\0");
        if ($a['test']!=1)
            $this->Error('Big-endian architectures are not supported');

	  // mPDF 2.3
	  list($accessfile, $isTemp) = $this->_get_local_file($file);
 
	  if (!$accessfile) { 
		return -1; 
            $this->Error('Can\'t open WMF image file: '.$file);
	  }

        $f=@fopen($accessfile,'rb');
        if(!$f)
            $this->Error('Can\'t open WMF image file: '.$file);

        // check for Aldus placeable metafile header
        $key = unpack('Lmagic', fread($f, 4));
        $headSize = 18 - 4; // WMF header minus four bytes already read
        if ($key['magic'] == (int)0x9AC6CDD7)
            $headSize += 22; // Aldus header

        // strip headers
        fread($f, $headSize);

        // define some state variables
        $wo=null; // window origin
        $we=null; // window extent
        $polyFillMode = 0;
        $nullPen = false;
        $nullBrush = false;

        $endRecord = false;

        $data = '';

        // read the records
        while (!feof($f) && !$endRecord)
        {
            $recordInfo = unpack('Lsize/Sfunc', fread($f, 6));

            // size of record given in WORDs (= 2 bytes)
            $size = $recordInfo['size'];

            // func is number of GDI function
            $func = $recordInfo['func'];

            // parameters are read as one block and processed
            // as necessary by the case statement below.
            // the data are stored in little-endian format and are unpacked using:
            // s - signed 16-bit int
            // S - unsigned 16-bit int (or WORD)
            // L - unsigned 32-bit int (or DWORD)
            // NB. parameters to GDI functions are stored in reverse order
            // however structures are not reversed,
            // e.g. POINT { int x, int y } where x=3000 (0x0BB8) and y=-1200 (0xFB50)
            // is stored as B8 0B 50 FB
            if ($size > 3)
            {
                $parms = fread($f, 2*($size-3));
            }

            // process each record.
            // function numbers are defined in wingdi.h
            switch ($func)
            {
                case 0x020b:  // SetWindowOrg
                    // do not allow window origin to be changed
                    // after drawing has begun
                    if (!$data)
                        $wo = array_reverse(unpack('s2', $parms));
                    break;

                case 0x020c:  // SetWindowExt
                    // do not allow window extent to be changed
                    // after drawing has begun
                    if (!$data)
                        $we = array_reverse(unpack('s2', $parms));
                    break;

                case 0x02fc:  // CreateBrushIndirect
                    $brush = unpack('sstyle/Cr/Cg/Cb/Ca/Shatch', $parms);
                    $brush['type'] = 'B';
                    $this->_AddGDIObject($brush);
                    break;

                case 0x02fa:  // CreatePenIndirect
                    $pen = unpack('Sstyle/swidth/sdummy/Cr/Cg/Cb/Ca', $parms);

                    // convert width from twips to user unit
                    $pen['width'] /= (20 * $this->k);
                    $pen['type'] = 'P';
                    $this->_AddGDIObject($pen);
                    break;

                // MUST create other GDI objects even if we don't handle them
                // otherwise object numbering will get out of sequence
                case 0x06fe: // CreateBitmap
                case 0x02fd: // CreateBitmapIndirect
                case 0x00f8: // CreateBrush
                case 0x02fb: // CreateFontIndirect
                case 0x00f7: // CreatePalette
                case 0x01f9: // CreatePatternBrush
                case 0x06ff: // CreateRegion
                case 0x0142: // DibCreatePatternBrush
                    $dummyObject = array('type'=>'D');
                    $this->_AddGDIObject($dummyObject);
                    break;

                case 0x0106:  // SetPolyFillMode
                    $polyFillMode = unpack('smode', $parms);
                    $polyFillMode = $polyFillMode['mode'];
                    break;

                case 0x01f0:  // DeleteObject
                    $idx = unpack('Sidx', $parms);
                    $idx = $idx['idx'];
                    $this->_DeleteGDIObject($idx);
                    break;

                case 0x012d:  // SelectObject
                    $idx = unpack('Sidx', $parms);
                    $idx = $idx['idx'];
                    $obj = $this->_GetGDIObject($idx);

                    switch ($obj['type'])
                    {
                        case 'B':
                            $nullBrush = false;

                            if ($obj['style'] == 1) // BS_NULL, BS_HOLLOW
                            {
                                $nullBrush = true;
                            }
                            else
                            {
                                $data .= sprintf("%.3f %.3f %.3f rg\n",$obj['r']/255,$obj['g']/255,$obj['b']/255);
                            }
                            break;

                        case 'P':
                            $nullPen = false;
                            $dashArray = array(); 

                            // dash parameters are my own - feel free to change them
                            switch ($obj['style'])
                            {
                                case 0: // PS_SOLID
                                    break;
                                case 1: // PS_DASH
                                    $dashArray = array(3,1);
                                    break;
                                case 2: // PS_DOT
                                    $dashArray = array(0.5,0.5);
                                    break;
                                case 3: // PS_DASHDOT
                                    $dashArray = array(2,1,0.5,1);
                                    break;
                                case 4: // PS_DASHDOTDOT
                                    $dashArray = array(2,1,0.5,1,0.5,1);
                                    break;
                                case 5: // PS_NULL
                                    $nullPen = true;
                                    break;
                            }

                            if (!$nullPen)
                            {
                                $data .= sprintf("%.3f %.3f %.3f RG\n",$obj['r']/255,$obj['g']/255,$obj['b']/255);
                                $data .= sprintf("%.3f w\n",$obj['width']*$this->k);
                            }

                            if (!empty($dashArray))
                            {
                                $s = '[';
                                for ($i=0; $i<count($dashArray);$i++)
                                {
                                    $s .= $dashArray[$i] * $this->k;
                                    if ($i != count($dashArray)-1)
                                        $s .= ' ';
                                }
                                $s .= '] 0 d';
                                $data .= $s."\n";
                            }

                            break;
                    }
                    break;

                case 0x0325: // Polyline
                case 0x0324: // Polygon
                    $coords = unpack('s'.($size-3), $parms);
                    $numpoints = $coords[1];

                    for ($i = $numpoints; $i > 0; $i--)
                    {
                        $px = $coords[2*$i];
                        $py = $coords[2*$i+1];

                        if ($i < $numpoints)
                            $data .= $this->_LineTo($px, $py);
                        else
                            $data .= $this->_MoveTo($px, $py);
                    }

                    if ($func == 0x0325)
                    {
                        $op = 's';
                    }
                    else if ($func == 0x0324)
                    {
                        if ($nullPen)
                        {
                            if ($nullBrush)
                                $op = 'n';  // no op
                            else
                                $op = 'f';  // fill
                        }
                        else
                        {
                            if ($nullBrush)
                                $op = 's';  // stroke
                            else
                                $op = 'b';  // stroke and fill
                        }

                        if ($polyFillMode==1 && ($op=='b' || $op=='f')) 
                            $op .= '*';  // use even-odd fill rule
                    }

                    $data .= $op."\n";
                    break;

                case 0x0538: // PolyPolygon
                    $coords = unpack('s'.($size-3), $parms);

                    $numpolygons = $coords[1];

                    $adjustment = $numpolygons;

                    for ($j = 1; $j <= $numpolygons; $j++)
                    {
                        $numpoints = $coords[$j + 1];

                        for ($i = $numpoints; $i > 0; $i--)
                        {
                            $px = $coords[2*$i   + $adjustment];
                            $py = $coords[2*$i+1 + $adjustment];

                            if ($i == $numpoints)
                                $data .= $this->_MoveTo($px, $py);
                            else
                                $data .= $this->_LineTo($px, $py);
                        }

                        $adjustment += $numpoints * 2;
                    }

                    if ($nullPen)
                    {
                        if ($nullBrush)
                            $op = 'n';  // no op
                        else
                            $op = 'f';  // fill
                    }
                    else
                    {
                        if ($nullBrush)
                            $op = 's';  // stroke
                        else
                            $op = 'b';  // stroke and fill
                    }

                    if ($polyFillMode==1 && ($op=='b' || $op=='f')) 
                        $op .= '*';  // use even-odd fill rule

                    $data .= $op."\n";

                    break;

                case 0x0000:
                    $endRecord = true;
                    break;
            }
        }

        fclose($f);
	  // mPDF 2.3
	  if ($isTemp) { unlink($accessfile); } 
        return array('x'=>$wo[0],'y'=>$wo[1],'w'=>$we[0],'h'=>$we[1],'data'=>$data);
}

function _MoveTo($x, $y) {
        return "$x $y m\n";
}

// a line must have been started using _MoveTo() first
function _LineTo($x, $y) {
        return "$x $y l\n";
}

function _AddGDIObject($obj) {
        // find next available slot
        $idx = 0;
        if (!empty($this->gdiObjectArray))
        {
            $empty = false;
            $i = 0;

            while (!$empty)
            {
                $empty = !isset($this->gdiObjectArray[$i]);
                $i++;
            }
            $idx = $i-1;
        }

        $this->gdiObjectArray[$idx] = $obj;
}

function _GetGDIObject($idx) {
        return $this->gdiObjectArray[$idx];
}

function _DeleteGDIObject($idx) {
        unset($this->gdiObjectArray[$idx]);
}

function _putformobjects() {
        reset($this->formobjects);
        while(list($file,$info)=each($this->formobjects))
        {
            $this->_newobj();
            $this->formobjects[$file]['n']=$this->n;
            $this->_out('<</Type /XObject');
            $this->_out('/Subtype /Form');
            $this->_out('/BBox ['.$info['x'].' '.$info['y'].' '.($info['w']+$info['x']).' '.($info['h']+$info['y']).']');
            if ($this->compress)
                $this->_out('/Filter /FlateDecode');
            $data=($this->compress) ? gzcompress($info['data']) : $info['data'];
            $this->_out('/Length '.strlen($data).'>>');
            $this->_putstream($data);

            unset($this->formobjects[$file]['data']);
            $this->_out('endobj');
        }
}
// END of WMF functions


function _freadint($f)
{
	//Read a 4-byte integer from file
	$i=$this->ords[fread($f,1)]<<24;
	$i+=$this->ords[fread($f,1)]<<16;
	$i+=$this->ords[fread($f,1)]<<8;
	$i+=$this->ords[fread($f,1)];
	return $i;
}

function _UTF16BEtextstring($s) {
	$s = $this->UTF8ToUTF16BE($s, true);
	if ($this->encrypted) {
		$s = $this->_RC4($this->_objectkey($this->n), $s);
	}
	return '('. $this->_escape($s).')';
}

// Added mPDF 2.0
function _textstring($s) {
	if ($this->encrypted) {
		$s = $this->_RC4($this->_objectkey($this->n), $s);
	}
	return '('. $this->_escape($s).')';
}


function _escape($s)
{
	// the chr(13) substitution fixes the Bugs item #1421290.
	return strtr($s, array(')' => '\\)', '(' => '\\(', '\\' => '\\\\', $this->chrs[13] => '\r'));
}

function _putstream($s) {
	if ($this->encrypted) {
		$s = $this->_RC4($this->_objectkey($this->n), $s);
	}
	$this->_out('stream');
	$this->_out($s);
	$this->_out('endstream');
}


// mPDF 2.3 2nd parameter added for Templates
function _out($s,$ln=true) {
	if($this->state==2) {
	   // Added mPDF 1.2 HTML headers and Footers - saves to buffer when writeHTMLHeader/Footer
	   if ($this->bufferoutput) {
		$this->headerbuffer.= $s."\n";
	   }
	   else if (($this->ColActive) && (!$this->processingHeader) && (!$this->processingFooter)) {
		// Captures everything in buffer for columns; Almost everything is sent from fn. Cell() except:
		// Images sent from Image() or
		// later sent as _out($textto) in printbuffer
		// Line()
		if (preg_match('/q \d+\.\d\d+ 0 0 (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ cm \/I\d+ Do Q/',$s,$m)) {	// Image data
			$h = ($m[1]/$this->k);
			// Update/overwrite the lowest bottom of printing y value for a column
			$this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y+$h;
		}
		// mPDF 2.1.
		else if (preg_match('/\d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ ([\-]{0,1}\d+\.\d\d+) re/',$s,$m) && $this->tableLevel>0) { // Rect in table
			$h = ($m[1]/$this->k);
			// Update/overwrite the lowest bottom of printing y value for a column
			$this->ColDetails[$this->CurrCol]['bottom_margin'] = max($this->ColDetails[$this->CurrCol]['bottom_margin'],($this->y+$h));
		}
		else { 	// Td Text Set in Cell()
			$h = $this->ColDetails[$this->CurrCol]['bottom_margin'] - $this->y; 
		}
		if ($h < 0) { $h = -$h; }
		$this->columnbuffer[] = array(
		's' => $s,							/* Text string to output */
		'col' => $this->CurrCol, 				/* Column when printed */
		'x' => $this->x, 						/* x when printed */
		'y' => $this->y,					 	/* this->y when printed (after column break) */
		'h' => $h						 	/* actual y at bottom when printed = y+h */
		);
	   }
	   else if ($this->table_rotate && !$this->processingHeader && !$this->processingFooter) {
		// Captures eveything in buffer for rotated tables; 
		$this->tablebuffer[] = array(
		's' => $s,							/* Text string to output */
		'x' => $this->x, 						/* x when printed */
		'y' => $this->y,					 	/* y when printed (after column break) */
		);
	   }
	   else if ($this->kwt && !$this->processingHeader && !$this->processingFooter) {
		// Captures eveything in buffer for keep-with-table (h1-6); 
		$this->kwt_buffer[] = array(
		's' => $s,							/* Text string to output */
		'x' => $this->x, 						/* x when printed */
		'y' => $this->y,					 	/* y when printed */
		);
	   }
	// Added mPDF 1.1 keeping block together on one page
	   else if (($this->keep_block_together) && (!$this->processingHeader) && (!$this->processingFooter)) {
		// Captures eveything in buffer; 
		if (preg_match('/q \d+\.\d\d+ 0 0 (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ cm \/I\d+ Do Q/',$s,$m)) {	// Image data
			$h = ($m[1]/$this->k);
			// Update/overwrite the lowest bottom of printing y value for Keep together block
			$this->ktBlock[$this->page]['bottom_margin'] = $this->y+$h;
		}
		else { 	// Td Text Set in Cell()
			$h = $this->ktBlock[$this->page]['bottom_margin'] - $this->y; 
		}
		if ($h < 0) { $h = -$h; }
		$this->divbuffer[] = array(
		'page' => $this->page,
		's' => $s,							/* Text string to output */
		'x' => $this->x, 						/* x when printed */
		'y' => $this->y,					 	/* y when printed (after column break) */
		'h' => $h						 	/* actual y at bottom when printed = y+h */
		);
	   }
	   else {
		$this->pages[$this->page] .= $s.($ln == true ? "\n" : '');
	   }

	}
	else {
		$this->buffer .= $s.($ln == true ? "\n" : '');
	}
}

// add a watermark 
function watermark( $texte, $angle=45, $fontsize=96, $alpha=0.2 )
{

	if (!$this->watermark_font) { $this->watermark_font = $this->default_font; }
      $this->SetFont( $this->watermark_font, "B", $fontsize, false );	// Don't output
	$texte= $this->purify_utf8_text($texte);
	if ($this->text_input_as_HTML) {
		$texte= $this->all_entities_to_utf8($texte);
	}
	if (!$this->is_MB) { $texte = mb_convert_encoding($texte,$this->mb_encoding,'UTF-8'); }
	// DIRECTIONALITY
	$this->magic_reverse_dir($texte);

	$this->SetAlpha($alpha);

	$this->SetTextColor(0);
	$szfont = $fontsize;
	$loop   = 0;
	$maxlen = (min($this->w,$this->h) );	// sets max length of text as 7/8 width/height of page
	while ( $loop == 0 )
	{
       $this->SetFont( $this->watermark_font, "B", $szfont, false );	// Don't output
	 $offset =  ((sin(deg2rad($angle))) * ($szfont/$this->k));

       $strlen = $this->GetStringWidth($texte);
       if ( $strlen > $maxlen - $offset  )
          $szfont --;
       else
          $loop ++;
	}

	$this->SetFont( $this->watermark_font, "B", $szfont-0.1, true, true);	// Output The -0.1 is because SetFont above is not written to PDF
											// Repeating it will not output anything as mPDF thinks it is set
	$adj = ((cos(deg2rad($angle))) * ($strlen/2));
	$opp = ((sin(deg2rad($angle))) * ($strlen/2));
	$wx = ($this->w/2) - $adj + $offset/3;
	$wy = ($this->h/2) + $opp;
	$this->Rotate($angle,$wx,$wy);
	$this->Text($wx,$wy,$texte);
	$this->Rotate(0);
	$this->SetTextColor(0,0,0);

	$this->SetAlpha(1);

}

// mPDF 2.2
// add a watermark Image
function watermarkImg( $src, $alpha=0.2 ) {
	$this->SetAlpha($alpha);
	$this->Image($src,0,0,0,0,'','', true, true, true);
	$this->SetAlpha(1);
}


function Rotate($angle,$x=-1,$y=-1)
{
	if($x==-1)
		$x=$this->x;
	if($y==-1)
		$y=$this->y;
	if($this->angle!=0)
		$this->_out('Q');
	$this->angle=$angle;
	if($angle!=0)
	{
		$angle*=M_PI/180;
		$c=cos($angle);
		$s=sin($angle);
		$cx=$x*$this->k;
		$cy=($this->h-$y)*$this->k;
		$this->_out(sprintf('q %.5f %.5f %.5f %.5f %.3f %.3f cm 1 0 0 1 %.3f %.3f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
	}
}

// From Invoice
function RoundedRect($x, $y, $w, $h, $r, $style = '')
{
	$k = $this->k;
	$hp = $this->h;
	if($style=='F')
		$op='f';
	elseif($style=='FD' or $style=='DF')
		$op='B';
	else
		$op='S';
	$MyArc = 4/3 * (sqrt(2) - 1);
	$this->_out(sprintf('%.3f %.3f m',($x+$r)*$k,($hp-$y)*$k ));
	$xc = $x+$w-$r ;
	$yc = $y+$r;
	$this->_out(sprintf('%.3f %.3f l', $xc*$k,($hp-$y)*$k ));

	$this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
	$xc = $x+$w-$r ;
	$yc = $y+$h-$r;
	$this->_out(sprintf('%.3f %.3f l',($x+$w)*$k,($hp-$yc)*$k));
	$this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
	$xc = $x+$r ;
	$yc = $y+$h-$r;
	$this->_out(sprintf('%.3f %.3f l',$xc*$k,($hp-($y+$h))*$k));
	$this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
	$xc = $x+$r ;
	$yc = $y+$r;
	$this->_out(sprintf('%.3f %.3f l',($x)*$k,($hp-$yc)*$k ));
	$this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
	$this->_out($op);
}

function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
{
	$h = $this->h;
	$this->_out(sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', $x1*$this->k, ($h-$y1)*$this->k,
						$x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
}


// mPDF 3.0 added - GRADIENTS

// type = linear:2; radial: 3;
// Linear: $coords - array of the form (x1, y1, x2, y2) which defines the gradient vector (see linear_gradient_coords.jpg). 
//    The default value is from left to right (x1=0, y1=0, x2=1, y2=0).
// Radial: $coords - array of the form (fx, fy, cx, cy, r) where (fx, fy) is the starting point of the gradient with color1, 
//    (cx, cy) is the center of the circle with color2, and r is the radius of the circle (see radial_gradient_coords.jpg). 
//    (fx, fy) should be inside the circle, otherwise some areas will not be defined
// $col = array(R,G,B); or array(G)
function Gradient($x, $y, $w, $h, $type, $col1=array(), $col2=array(), $coords='', $extend='', $return=false) {
	if (strtoupper(substr($type,0,1)) == 'L') { $type = 2; }
	else if (strtoupper(substr($type,0,1)) == 'R') { $type = 3; }
	if ($type < 1) { $type = 2; }
	if (!isset($col1[1])) { $col1[1]=$col1[2]=$col1[0]; }
	if (!isset($col2[1])) { $col2[1] = $col2[2] = $col2[0]; }
	if (!is_array($coords) || count($coords) <1) { 
		if ($type == 2) { $coords=array(0,0,1,0); } 
		else { $coords=array(0.5,0.5,0.5,0.5,1); }
	}
	if (!is_array($extend) || count($extend) <1) { 
		$extend=array('true', 'true');	// These are suuposed to be quoted - appear in PDF file as text
	}
	$s = ' q';
	$s .= sprintf(' %.3F %.3F %.3F %.3F re W n', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k)."\n";
	$s .= sprintf(' %.3F 0 0 %.3F %.3F %.3F cm', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k)."\n";
	$n = count($this->gradients) + 1;
	$this->gradients[$n]['type'] = $type;
	$this->gradients[$n]['col1'] = sprintf('%.3F %.3F %.3F', ($col1[0]/255), ($col1[1]/255), ($col1[2]/255));
	$this->gradients[$n]['col2'] = sprintf('%.3F %.3F %.3F', ($col2[0]/255), ($col2[1]/255), ($col2[2]/255));
	$this->gradients[$n]['coords'] = $coords;
	$this->gradients[$n]['extend'] = $extend;
	//paint the gradient
	$s .= '/Sh'.$n.' sh '."\n";
	//restore previous Graphic State
	$s .= ' Q '."\n";
	if ($return) { return $s; }
	else { $this->_out($s); }
}


//====================================================



// Label and number of invoice/estimate
// mPDF 2.2 - function name changed to capitalise first letter
function Shaded_box( $text,$font='',$fontstyle='B',$szfont='',$width='70%',$style='DF',$radius=2.5,$fill='#FFFFFF',$color='#000000',$pad=2 )
{
// F (shading - no line),S (line, no shading),DF (both)
	if (!$font) { $font= $this->default_font; }
	if (!$szfont) { $szfont = ($this->default_font_size * 1.8); }

	$text = $this->purify_utf8_text($text);
	if ($this->text_input_as_HTML) {
		$text = $this->all_entities_to_utf8($text);
	}
	if (!$this->is_MB) { $text = mb_convert_encoding($text,$this->mb_encoding,'UTF-8'); }
	// DIRECTIONALITY
	$this->magic_reverse_dir($text);
	$text = ' '.$text.' ';
	if (!$width) { $width = $this->pgwidth; } else { $width=ConvertSize($width,$this->pgwidth); }
	$midpt = $this->lMargin+($this->pgwidth/2);
	$r1  = $midpt-($width/2);		//($this->w / 2) - 40;
	$r2  = $r1 + $width; 		//$r1 + 80;
	$y1  = $this->y;


	$mid = ($r1 + $r2 ) / 2;
	$loop   = 0;
    
	while ( $loop == 0 )
	{
		$this->SetFont( $font, $fontstyle, $szfont );
		$sz = $this->GetStringWidth( $text );
		if ( ($r1+$sz) > $r2 )
			$szfont --;
		else
			$loop ++;
	}

	$y2  = $this->FontSize+($pad*2);

	$this->SetLineWidth(0.1);
	$fc = ConvertColor($fill);
	$tc = ConvertColor($color);
	$this->SetFillColor($fc['R'],$fc['G'],$fc['B']);
	$this->SetTextColor($tc['R'],$tc['G'],$tc['B']);
	$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, $radius, $style);
	$this->SetX( $r1);
	$this->Cell($r2-$r1, $y2, $text, 0, 1, "C" );
	$this->SetY($y1+$y2+2);	// +2 = mm margin below shaded box
	$this->Reset();
}





/**
* Converts UTF-8 strings to codepoints array.<br>
 * @author Nicola Asuni
* @since 1.53.0.TC005 (2005-01-05)
*/
function UTF8StringToArray($str) {
			$unicode = array(); // array containing unicode values
			$bytes  = array(); // array containing single character byte sequences
			$numbytes  = 1; // number of octetc needed to represent the UTF-8 character
			
			$str .= ""; // force $str to be a string
			$length = strlen($str);
			
			for($i = 0; $i < $length; $i++) {
				$char = $this->ords[substr($str,$i,1)]; // get one string character at time
				if(count($bytes) == 0) { // get starting octect
					if ($char <= 0x7F) {
						$unicode[] = $char; // use the character "as is" because is ASCII
						$numbytes = 1;
					} elseif (($char >> 0x05) == 0x06) { // 2 bytes character (0x06 = 110 BIN)
						$bytes[] = ($char - 0xC0) << 0x06; 
						$numbytes = 2;
					} elseif (($char >> 0x04) == 0x0E) { // 3 bytes character (0x0E = 1110 BIN)
						$bytes[] = ($char - 0xE0) << 0x0C; 
						$numbytes = 3;
					} elseif (($char >> 0x03) == 0x1E) { // 4 bytes character (0x1E = 11110 BIN)
						$bytes[] = ($char - 0xF0) << 0x12; 
						$numbytes = 4;
					} else {
						// use replacement character for other invalid sequences
						$unicode[] = 0xFFFD;
						$bytes = array();
						$numbytes = 1;
					}
				} elseif (($char >> 0x06) == 0x02) { // bytes 2, 3 and 4 must start with 0x02 = 10 BIN
					$bytes[] = $char - 0x80;
					if (count($bytes) == $numbytes) {
						// compose UTF-8 bytes to a single unicode value
						$char = $bytes[0];
						for($j = 1; $j < $numbytes; $j++) {
							$char += ($bytes[$j] << (($numbytes - $j - 1) * 0x06));
						}
						if ((($char >= 0xD800) AND ($char <= 0xDFFF)) OR ($char >= 0x10FFFF)) {
							/* The definition of UTF-8 prohibits encoding character numbers between
							U+D800 and U+DFFF, which are reserved for use with the UTF-16
							encoding form (as surrogate pairs) and do not directly represent
							characters. */
							$unicode[] = 0xFFFD; // use replacement character
						}
						else {
							$unicode[] = $char; // add char to array
						}
						// reset data for next char
						$bytes = array(); 
						$numbytes = 1;
					}
				} else {
					// use replacement character for other invalid sequences
					$unicode[] = 0xFFFD;
					$bytes = array();
					$numbytes = 1;
				}
			}
			return $unicode;
}



/**
* Converts UTF-8 strings to UTF16-BE.
*/
function UTF8ToUTF16BE($str, $setbom=true) {
	$outstr = ""; // string to be returned
	if ($setbom) {
		$outstr .= "\xFE\xFF"; // Byte Order Mark (BOM)
	}

	// mPDF 2.5 Why not use this??? *************
	$outstr .= mb_convert_encoding($str, 'UTF-16BE', 'UTF-8');
	return $outstr;

	
			$unicode = $this->UTF8StringToArray($str); // array containing UTF-8 unicode values
			$numitems = count($unicode);
			
			foreach($unicode as $char) {
				if($char == 0xFFFD) {
					$outstr .= "\xFF\xFD"; // replacement character
				} elseif ($char < 0x10000) {
					$outstr .= $this->chrs[$char >> 0x08];
					$outstr .= $this->chrs[$char & 0xFF];
				} else {
					$char -= 0x10000;
					$w1 = 0xD800 | ($char >> 0x10);
					$w2 = 0xDC00 | ($char & 0x3FF);	
					$outstr .= $this->chrs[$w1 >> 0x08];
					$outstr .= $this->chrs[$w1 & 0xFF];
					$outstr .= $this->chrs[$w2 >> 0x08];
					$outstr .= $this->chrs[$w2 & 0xFF];
				}
			}
			return $outstr;
}




function _getfontpath() {
	if(!defined('FPDF_FONTPATH') AND is_dir(dirname(__FILE__).'/font')) {
		define('FPDF_FONTPATH', dirname(__FILE__).'/font/');
	}
	return defined('FPDF_FONTPATH') ? FPDF_FONTPATH : '';
}




// ====================================================
// ====================================================
// from class PDF_Chinese CJK EXTENSIONS

var $Big5_widths;
var $GB_widths;
var $SJIS_widths;
var $UHC_widths;



function AddCIDFont($family,$style,$name,$cw,$CMap,$registry,$desc)
{
	$fontkey=strtolower($family).strtoupper($style);
	if(isset($this->fonts[$fontkey]))
		$this->Error("Font already added: $family $style");
	$i=count($this->fonts)+1;
	$name=str_replace(' ','',$name);
	if ($family == 'sjis') { $up = -120; } else { $up = -130; }
	// ? 'up' and 'ut' do not seem to be referenced anywhere
	$this->fonts[$fontkey]=array('i'=>$i,'type'=>'Type0','name'=>$name,'up'=>$up,'ut'=>40,'cw'=>$cw,'CMap'=>$CMap,'registry'=>$registry,'MissingWidth'=>1000,'desc'=>$desc);
}

function AddCJKFont($family) {
	if ($family == 'big5') { $this->AddBig5Font(); }
	else if ($family == 'gb') { $this->AddGBFont(); }
	else if ($family == 'sjis') { $this->AddSJISFont(); }
	else if ($family == 'uhc') { $this->AddUHCFont(); }
}

function AddBig5Font()
{
	//Add Big5 font with proportional Latin
	$family='big5';
	$name='MSungStd-Light-Acro';
	$cw=$this->Big5_widths;
	//$CMap='ETenms-B5-H';
	$CMap='UniCNS-UTF16-H';
	$desc = array(
	'Ascent' => 880,
	'Descent' => -120,
	'CapHeight' => 880,
	'Flags' => 6,
	'FontBBox' => '[-160 -249 1015 1071]',
	'ItalicAngle' => 0,
	'StemV' => 93,
	);
	$registry=array('ordering'=>'CNS1','supplement'=>0);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry,$desc);
}


function AddGBFont()
{
	//Add GB font with proportional Latin
	$family='gb';
	$name='STSongStd-Light-Acro';
	$cw=$this->GB_widths;
	//$CMap='GBKp-EUC-H';
	$CMap='UniGB-UTF16-H';
	$registry=array('ordering'=>'GB1','supplement'=>2);
	$desc = array(
	'Ascent' => 752,
	'Descent' => -271,
	'CapHeight' => 737,
	'Flags' => 6,
	'FontBBox' => '[-25 -254 1000 880]',
	'ItalicAngle' => 0,
	'StemV' => 58,
	'Style' => '<< /Panose <000000000400000000000000> >>',
	);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry,$desc);
}


function AddSJISFont()
{
	//Add SJIS font with proportional Latin
	$family='sjis';
	$name='KozMinPro-Regular-Acro';
	$cw=$this->SJIS_widths;
	//$CMap='90msp-RKSJ-H';
	$CMap='UniJIS-UTF16-H';
	$desc = array(
	'Ascent' => 880,
	'Descent' => -120,
	'CapHeight' => 740,
	'Flags' => 6,
	'FontBBox' => '[-195 -272 1110 1075]',
	'ItalicAngle' => 0,
	'StemV' => 86,
	'XHeight' => 502,
	);
	$registry=array('ordering'=>'Japan1','supplement'=>2);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry,$desc);
}

function AddUHCFont()
{
	//Add UHC font with proportional Latin
	$family='uhc';
	$name='HYSMyeongJoStd-Medium-Acro';
	$cw=$this->UHC_widths;
	//$CMap='KSCms-UHC-H';
	$CMap='UniKS-UTF16-H';
	$registry=array('ordering'=>'Korea1','supplement'=>1);
	$desc = array(
	'Ascent' => 880,
	'Descent' => -120,
	'CapHeight' => 720,
	'Flags' => 6,
	'FontBBox' => '[-28 -148 1001 880]',
	'ItalicAngle' => 0,
	'StemV' => 60,
	'Style' => '<< /Panose <000000000600000000000000> >>',
	);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry,$desc);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry,$desc);
}





//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
// mPDF 2.3
function SetAutoFont($af = AUTOFONT_ALL) {
	if (!$this->is_MB) { return false; }
	if (!$af && $af !== 0) { $af = AUTOFONT_ALL; }
	$this->autoFontGroups = $af;
	if ($this->autoFontGroups ) { 
		$this->useSubstitutions = false; 
		$this->useLang = true;
		if (AUTOFONT_RTL) { $this->biDirectional = true; }
	}
}


function SetDefaultFont($font) {
	// Disallow embedded fonts to be used as defaults except in win-1252
	if ($this->codepage != 'win-1252') {
		if (strtolower($font) == 'times') { $font = 'serif'; }
		if (strtolower($font) == 'courier') { $font = 'monospace'; }
		if ((strtolower($font) == 'arial') || (strtolower($font) == 'helvetica')) { $font = 'sans-serif'; }
	}
  	$font = $this->SetFont($font);	// returns substituted font if necessary
	$this->default_font = $font;
	$this->original_default_font = $font;
	if (!$this->watermark_font ) { $this->watermark_font = $font; }
	$this->SetSubstitutions(GetSubstitutions($this->codepage,$this->default_font));
}

function SetDefaultFontSize($fontsize) {
	$this->default_font_size = $fontsize;
	$this->original_default_font_size = $fontsize;
	$this->SetFontSize($fontsize);
	// Added mPDF 1.1 allows SetDefaultFont to override that set in defaultCSS
	$this->defaultCSS['BODY']['FONT-SIZE'] = $fontsize . 'pt';
}

function SetDirectionality($dir='ltr') {
	if (strtolower($dir) == 'rtl') { 
		$this->directionality = 'rtl'; 
		$this->defaultAlign = 'R';
		$this->defaultTableAlign = 'R';
		// Swop L/R Margins so page 1 RTL is an 'even' page
		$tmp = $this->DeflMargin;
		$this->DeflMargin = $this->DefrMargin; 
		$this->DefrMargin = $tmp; 
		// Added mPDF 2.0
		$this->orig_lMargin = $this->DeflMargin;
		$this->orig_rMargin = $this->DefrMargin;

		$this->SetMargins($this->DeflMargin,$this->DefrMargin,$this->tMargin);
	}
	else  { 
		$this->directionality = 'ltr'; 
		$this->defaultAlign = 'L';
		$this->defaultTableAlign = 'L';
	}
}

function reverse_align(&$align) {
	if (strtolower($align) == 'right') { $align = 'left'; }
	else if (strtolower($align) == 'left') { $align = 'right'; }
	if (strtoupper($align) == 'R') { $align = 'L'; }
	else if (strtoupper($align) == 'L') { $align = 'R'; }
}


// Added to set line-height-correction
function SetLineHeightCorrection($val) {
	if ($val > 0) { $this->default_lineheight_correction = $val; }
	else { $this->default_lineheight_correction = 1.2; }
}

// Added to Set the lineheight - either to named fontsize(pts) or default
function SetLineHeight($FontPt='',$spacing = '') {
   if ($spacing > 0) { 
	if ($FontPt) { $this->lineheight = (($FontPt/2.834) *$spacing); }
	else { $this->lineheight = (($this->FontSizePt/2.834) *$spacing); }
   }
   else {
	if ($FontPt) { $this->lineheight = (($FontPt/2.834) *$this->default_lineheight_correction); }
	else { $this->lineheight = (($this->FontSizePt/2.834) *$this->default_lineheight_correction); }
   }
}

// mPDF 2.2 - function name changed to capitalise first letter
function SetBasePath($str='') {
  //mPDF 2.1
  $str = preg_replace('/\?.*/','',$str);
  // Edited mPDF 2.0
  if ($_SERVER['HTTP_HOST'] ) { $host = $_SERVER['HTTP_HOST']; }
  else { $host = $_SERVER['SERVER_NAME']; }
  if ($_SERVER['SCRIPT_NAME']) { $currentPath = dirname($_SERVER['SCRIPT_NAME']); }
  else { $currentPath = dirname($_SERVER['PHP_SELF']); }
  $currpath = 'http://' . $host . $currentPath .'/';
  if (!$str) { 
	$this->basepath = $currpath; 
	$this->basepathIsLocal = true; 
	return; 
  }
  if (!preg_match('/(http|https|ftp):\/\/.*\//i',$str)) { $str .= '/'; } 
  $str .= 'xxx';	// in case $str ends in / e.g. http://www.bbc.co.uk/
  $this->basepath = dirname($str) . "/";	// returns e.g. e.g. http://www.google.com/dir1/dir2/dir3/
  $this->basepath = str_replace("\\","/",$this->basepath); //If on Windows

  // Added mPDF 2.0
  $tr = parse_url($this->basepath);
  if ($tr['host'] == $host) { $this->basepathIsLocal = true; }
  else { $this->basepathIsLocal = false; }
}


// Added mPDF 2.0 - checks if file exists locally or remote
function _file_exists($srcpath) {
		// mPDF 2.3 - for IIS
		if ($fh = @fopen($srcpath,"rb")) { fclose($fh); return true; }
		// If local file try using local path (? quicker, but also allowed even if allow_url_fopen false)
		if ($this->basepathIsLocal) {
			$tr = parse_url($srcpath);
			// mPDF 2.3 - for IIS
			$lp=getenv("SCRIPT_NAME");
			$ap=realpath($lp);
			$ap=str_replace("\\","/",$ap);
			$docroot=substr($ap,0,strpos($ap,$lp));

			// WriteHTML parses all paths to full URLs; may be local file name from calling ->Image() directly
			if ($tr['scheme'] && $tr['host'] && $_SERVER["DOCUMENT_ROOT"] ) { 
				$localsrcpath = $_SERVER["DOCUMENT_ROOT"] . $tr['path']; 
			}
			// DOCUMENT_ROOT is not returned on IIS
			else if ($docroot) {
				$localsrcpath = $docroot . $tr['path'];
			}
			else { $localsrcpath = $srcpath; }
			if (file_exists($localsrcpath)) { return true; }
		}
		// if not use full URL
		if (!ini_get('allow_url_fopen') && function_exists("curl_init")) {
			$ch = curl_init($srcpath);
			curl_setopt($ch, CURLOPT_HEADER, 0);
      			curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
			$test = curl_exec($ch);
			curl_close($ch);
			if ($test) { return true; }
		}
		else if(function_exists("curl_init") && substr(strtoupper($srcpath), 0, 4)=='HTTP') {
			$ch = curl_init($srcpath);
			curl_setopt($ch, CURLOPT_HEADER, 0);
      			curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
			$test = curl_exec($ch);
			curl_close($ch);
			if ($test) { return true; }
		}

		return false;
}

// Edited mPDF 2.0
// Used for external CSS files
function _get_file($path) {
	// If local file try using local path (? quicker, but also allowed even if allow_url_fopen false)
	$contents = '';
	// mPDF 2.3 - for IIS
	$contents = @file_get_contents($path);
	if ($contents) { return $contents; }
	if ($this->basepathIsLocal) {
		$tr = parse_url($path);
		// mPDF 2.3 - for IIS
		$lp=getenv("SCRIPT_NAME");
		$ap=realpath($lp);
		$ap=str_replace("\\","/",$ap);
		$docroot=substr($ap,0,strpos($ap,$lp));
		// WriteHTML parses all paths to full URLs; may be local file name from calling ->Image() directly
		// mPDF 2.3
		if ($tr['scheme'] && $tr['host'] && $_SERVER["DOCUMENT_ROOT"] ) { 
			$localpath = $_SERVER["DOCUMENT_ROOT"] . $tr['path']; 
		}
		// DOCUMENT_ROOT is not returned on IIS
		else if ($docroot) {
			$localpath = $docroot . $tr['path'];
		}
		else { $localpath = $path; }
		$contents = @file_get_contents($localpath);
	}
	// if not use full URL
	else if (!$contents && !ini_get('allow_url_fopen') && function_exists("curl_init"))  {
		$ch = curl_init($path);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
		$contents = curl_exec($ch);
		curl_close($ch);
	}
	return $contents;
}
// mPDF 2.0 returns name of local path, or URI if can be accessed, or copies file to temporary local file and returns filename
// returns array of filename to access, and true/false if temporary local file created
// called by _parsegif, _parsejpg and _parsepng
function _get_local_file($file) {
	// mPDF 2.3 - for IIS
		if ($fh = @fopen($file,"rb")) { fclose($fh); return array($file,false); }
		// If local file try using local path (? quicker, but also allowed even if allow_url_fopen false)
		if ($this->basepathIsLocal) {
			$tr = parse_url($file);
			// mPDF 2.3 - for IIS
			$lp=getenv("SCRIPT_NAME");
			$ap=realpath($lp);
			$ap=str_replace("\\","/",$ap);
			$docroot=substr($ap,0,strpos($ap,$lp));
			// WriteHTML parses all paths to full URLs; may be local file name from calling ->Image() directly
			// mPDF 2.3
			if ($tr['scheme'] && $tr['host'] && $_SERVER["DOCUMENT_ROOT"] ) { 
				$localfile = $_SERVER["DOCUMENT_ROOT"] . $tr['path']; 
			}
			// DOCUMENT_ROOT is not returned on IIS
			else if ($docroot) {
				// mPDF 2.3
				$localfile = $docroot . $tr['path'];
			}
			else { $localfile = $file; }
			if (file_exists($localfile)) { return array($localfile,false); }
		}
		// if not use full URL
		else if (!ini_get('allow_url_fopen') && function_exists("curl_init") && preg_match('/^http.*?\/([^\/]*)$/',$file,$match)) {
			$localfile = dirname(__FILE__).'/graph_cache/_tmpImage_'.time().'_'.$match[1];
			$ch = curl_init($file);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$lFile = fopen( $localfile, 'wb' );
      		curl_setopt ( $ch , CURLOPT_FILE , $lFile );
			$data = curl_exec($ch);
			curl_close($ch);
			fclose( $lFile );
			return array($localfile, true); 
		}
		return array(false,false);
}




function ShowNOIMG_GIF($opt=true)
{
  $this->shownoimg=$opt;
}

function UseCSS($opt=true)
{
  $this->usecss=$opt;
}

function UseTableHeader($opt=true)
{
  $this->usetableheader=$opt;
}

function UsePRE($opt=true)
{
  $this->usepre=$opt;
}

// Added mPDF 1.3
// Edited mPDF 3.0 - added offset & extras (prefix/suffix)
function docPageNum($num = 0, $extras = false) {
	if ($num < 1) { $num = $this->page; }
	$type = '1';	// set default decimal
	$ppgno = $num;
	$suppress = 0;
	$offset = 0;
	foreach($this->PageNumSubstitutions AS $psarr) {
		if ($num >= $psarr['from']) {
			if ($psarr['reset']) { 
				if ($psarr['reset']>1) { $offset = $psarr['reset']-1; }
				$ppgno = $num - $psarr['from'] + 1 + $offset; 
			}
			if ($psarr['type']) { $type = $psarr['type']; }
			if (strtoupper($psarr['suppress'])=='ON' || $psarr['suppress']==1) { $suppress = 1; }
			else if (strtoupper($psarr['suppress'])=='OFF') { $suppress = 0; }
		}
	}
	if ($suppress) { return ''; }
	if ($type=='A') { $ppgno = dec2alpha($ppgno,true); }
	else if ($type=='a') { $ppgno = dec2alpha($ppgno,false);}
	else if ($type=='I') { $ppgno = dec2roman($ppgno,true); }
	else if ($type=='i') { $ppgno = dec2roman($ppgno,false); }
	if ($extras) { $ppgno = $this->pagenumPrefix . $ppgno . $this->pagenumSuffix; }
	return $ppgno;
}

// mPDF 3.0
function docPageSettings($num = 0) {
	// Retruns current type (numberstyle), suppression state for this page number; 
	// reset is only returned if set for this page number
	if ($num < 1) { $num = $this->page; }
	$type = '1';	// set default decimal
	$ppgno = $num;
	$suppress = 0;
	$offset = 0;
	$reset = '';
	foreach($this->PageNumSubstitutions AS $psarr) {
		if ($num >= $psarr['from']) {
			if ($psarr['reset']) { 
				if ($psarr['reset']>1) { $offset = $psarr['reset']-1; }
				$ppgno = $num - $psarr['from'] + 1 + $offset; 
			}
			if ($psarr['type']) { $type = $psarr['type']; }
			if (strtoupper($psarr['suppress'])=='ON' || $psarr['suppress']==1) { $suppress = 1; }
			else if (strtoupper($psarr['suppress'])=='OFF') { $suppress = 0; }
		}
		if ($num == $psarr['from']) { $reset = $psarr['reset']; }
	}
	if ($suppress) { $suppress = 'on'; }
	else { $suppress = 'off'; }
	return array($type, $suppress, $reset);
}

// Added mPDF 2.0
// Edited mPDF 3.0 - added offset & extras (prefix/suffix)
function docPageNumTotal($num = 0, $extras = false) {
	if ($num < 1) { $num = $this->page; }
	$type = '1';	// set default decimal
	$ppgstart = 1;
	$ppgend = count($this->pages)+1; 
	$suppress = 0;
	$offset = 0;
	foreach($this->PageNumSubstitutions AS $psarr) {
		if ($num >= $psarr['from']) {
			if ($psarr['reset']) { 
				if ($psarr['reset']>1) { $offset = $psarr['reset']-1; }
				$ppgstart = $psarr['from'] + $offset; 
				$ppgend = count($this->pages)+1 + $offset; 
			}
			if ($psarr['type']) { $type = $psarr['type']; }
			if (strtoupper($psarr['suppress'])=='ON' || $psarr['suppress']==1) { $suppress = 1; }
			else if (strtoupper($psarr['suppress'])=='OFF') { $suppress = 0; }
		}
		if ($num < $psarr['from']) {
			if ($psarr['reset']) { 
				$ppgend = $psarr['from'] + $offset; 
				break;
			}
		}
	}
	if ($suppress) { return ''; }
	$ppgno = $ppgend-$ppgstart+$offset; 
	if ($extras) { $ppgno = $this->nbpgPrefix . $ppgno . $this->nbpgSuffix; }
	return $ppgno;
}

// mPDF 2.4. Reusing DocTemplate
function RestartDocTemplate() {
	$this->docTemplateStart = $this->page;
}



//Page header
function Header($content='') {

	// mPDF 2.3
	$this->cMarginL = 0;
	$this->cMarginR = 0;

	// mPDF 2.3 Templates in mFPDI
	if ($this->docTemplate) {
		$pagecount = $this->SetSourceFile($this->docTemplate);
		// mPDF 2.4. Reusing DocTemplate
		if (($this->page - $this->docTemplateStart) > $pagecount) {
			if ($this->docTemplateContinue) { 
				$tplIdx = $this->ImportPage($pagecount);
				$this->UseTemplate($tplIdx);
			}
		}
		else {
			// mPDF 2.4. Reusing DocTemplate
			$tplIdx = $this->ImportPage(($this->page - $this->docTemplateStart));
			$this->UseTemplate($tplIdx);
		}
	}
	if ($this->pageTemplate) {
		$this->UseTemplate($this->pageTemplate);
	}


  // Added mPDF 2.0 HTML headers and Footers
  if (($this->useOddEven && ($this->page%2==0) && $this->HTMLHeaderE) || ($this->useOddEven && ($this->page%2==1) && $this->HTMLHeader) || (!$this->useOddEven && $this->HTMLHeader)) {
	$this->writeHTMLHeaders(); 
	return;
  }
  $this->processingHeader=true;
  $h = $this->headerDetails;
  if(count($h)) {

	// mPDF 2.3
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->_out(sprintf('q 0 -1 1 0 0 %.3f cm ',($this->h*$this->k)));
		$yadj = $this->w - $this->h;
		$headerpgwidth = $this->h - $this->orig_lMargin - $this->orig_rMargin;
		if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
			$headerlmargin = $this->orig_rMargin;
		}
		else {
			$headerlmargin = $this->orig_lMargin;
		}
	}
	else { 
		$yadj = 0; 
		$headerpgwidth = $this->pgwidth;
		$headerlmargin = $this->lMargin;
	}

	$this->y = $this->margin_header - $yadj ;
	$this->SetTextColor(0);
    	$this->SUP = false;
	$this->SUB = false;
	$this->bullet = false;

	// only show pagenumber if numbering on
	// mPDF 3.0 Add PageNum prefix/suffix
	$pgno = $this->docPageNum($this->page, true); 

	if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
			$side = 'even';
	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$side = 'odd';
	}
	$maxfontheight = 0;
	foreach(array('L','C','R') AS $pos) {
	  if ($h[$side][$pos]['content']) {
		if ($h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		else { $hfsz = $this->default_font_size; }
		$maxfontheight = max($maxfontheight,$hfsz);
	  }
	}
	// LEFT-CENTER-RIGHT
	foreach(array('L','C','R') AS $pos) {
	  if ($h[$side][$pos]['content']) {
		$hd = str_replace('{PAGENO}',$pgno,$h[$side][$pos]['content']);
		// mPDF 3.0
		$hd = str_replace($this->aliasNbPgGp,$this->nbpgPrefix.$this->aliasNbPgGp.$this->nbpgSuffix,$hd);	// {nbpg}
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		if ($h[$side][$pos]['font-family']) { $hff = $h[$side][$pos]['font-family']; }
		// mPDF 3.0 original_ in case pagebreak in middle of table
		else { $hff = $this->original_default_font; }
		if ($h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		// mPDF 3.0 original_ in case pagebreak in middle of table
		else { $hfsz = $this->original_default_font_size; }	// pts
		$maxfontheight = max($maxfontheight,$hfsz);
		$hfst = $h[$side][$pos]['font-style'];
		if (!$hfst) { $hfst = ''; }
		if ($h[$side][$pos]['color']) { 
			$hfcol = $h[$side][$pos]['color']; 
			$cor = ConvertColor($hfcol);
			if ($cor) { $this->SetTextColor($cor['R'],$cor['G'],$cor['B']); }
		}
		else { $hfcol = ''; }
		// mPDF 3.0 Force output
		$this->SetFont($hff,$hfst,$hfsz,true,true);
		// mPDF 2.3
		$this->x = $headerlmargin ;
		$this->y = $this->margin_header - $yadj ;

		$hd = $this->purify_utf8_text($hd);
		if ($this->text_input_as_HTML) {
			$hd = $this->all_entities_to_utf8($hd);
		}
		// CONVERT CODEPAGE
		if (!$this->is_MB) { $hd = mb_convert_encoding($hd,$this->mb_encoding,'UTF-8'); }
		// DIRECTIONALITY RTL
		$this->magic_reverse_dir($hd);
		$align = $pos;
		if ($this->directionality == 'rtl') { 
			if ($pos == 'L') { $align = 'R'; }
			else if ($pos == 'R') { $align = 'L'; }
		}
		// mPDF 2.4
		if ($pos!='L' && (stripos($hd,$this->aliasNbPg)!==false || stripos($hd,$this->aliasNbPgGp)!==false)) { 
			if (stripos($hd,$this->aliasNbPgGp)!==false) { $type= 'nbpggp'; } else { $type= 'nbpg'; }
			$this->_out('{mpdfheader'.$type.' '.$pos.' ff='.$hff.' fs='.$hfst.' fz='.$hfsz.'}'); 
			$this->Cell($headerpgwidth ,$maxfontheight/$this->k ,$hd,0,0,$align,0,'',0,0,0,'M');
			$this->_out('Q');
		}
		else { 
		// mPDF 2.3
			$this->Cell($headerpgwidth ,$maxfontheight/$this->k ,$hd,0,0,$align,0,'',0,0,0,'M');
		}
		if ($hfcol) { $this->SetTextColor(0); }
	  }
	}
	//Return Font to normal
	$this->SetFont($this->default_font,'',$this->original_default_font_size);
	// LINE
	if ($h[$side]['line']) { 
		$this->SetLineWidth(0.1);
		$this->SetDrawColor(0);
		// mPDF 2.3
		$this->Line($headerlmargin , $this->margin_header + ($maxfontheight*(1+$this->header_line_spacing)/$this->k) - $yadj , $headerlmargin + $headerpgwidth, $this->margin_header + ($maxfontheight*(1+$this->header_line_spacing)/$this->k) - $yadj  );
	}
	// mPDF 2.3
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->_out('Q');
	}
  }
  $this->SetY($this->tMargin);
  if ($this->ColActive) { $this->pgwidth = $this->ColWidth; }

  $this->processingHeader=false;
}



function TableHeader($content='',$tablestartpage='',$tablestartcolumn ='') {
  if($this->usetableheader and $content != '')
  {

	// mPDF 2.0
	$table = &$this->table[1][1];
	// Advance down page by half width of top border
	// mPDF 3.0 Add table border and padding
	//if ($table['borders_separate']) { $adv = $content[0][0]['border_details']['T']['w'] + $table['border_spacing_V']/2;  }
	//else { $adv = $content[0][0]['border_details']['T']['w'] /2; }
	if ($table['borders_separate']) { $adv = $table['border_spacing_V']/2 + $table['border_details']['T']['w'] + $table['padding']['T'];  }
	else { $adv = $table['max_cell_border_width']['T'] /2 ; }
	if ($adv) { 
	   if ($this->table_rotate) {
		$this->y += ($adv);
	   }
	   else {
		$this->DivLn($adv,$this->blklvl,true); 
	   }
	}

   // mPDF 2.2.1
   $topy = $content[0][0]['y']-$this->y;

   // mPDF 2.1 - Multiple Header rows
   for ($i=0; $i<count($content); $i++) {

    $y = $this->y;

	// mPDF 3.0 - If outside columns, this is done in PaintDivBB
	if ($this->ColActive) {
	//OUTER FILL BGCOLOR of DIVS
	 if ($this->blklvl > 0) {
	  $firstblockfill = $this->GetFirstBlockFill();
	  if ($firstblockfill && $this->blklvl >= $firstblockfill) {
		$divh = $content[$i][0]['h'];
		$bak_x = $this->x;
		$this->DivLn($divh,-3,false);
		// Reset current block fill
		$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
		$this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
		$this->x = $bak_x;
	  }
	 }
	}

    $colctr = 0;
    foreach($content[$i] as $tableheader)
    {
	$colctr++;
	// mPDF 2.2 Added in case of rowspan used in header
	// mPDF 2.2.1
	$y = $tableheader['y'] - $topy;

      $this->y = $y;
      //Set some cell values
      $x = $tableheader['x'];
	if (($this->useOddEven) && ($tablestartpage == 'ODD') && (($this->page)%2==0)) {	// EVEN
		$x = $x +$this->MarginCorrection;
	}
	else if (($this->useOddEven) && ($tablestartpage == 'EVEN') && (($this->page)%2==1)) {	// ODD
		$x = $x +$this->MarginCorrection;
	}
	// Added to correct for Columns
	if ($this->ColActive) {
	   if ($this->directionality == 'rtl') {
		$x -= ($this->CurrCol - $tablestartcolumn) * ($this->ColWidth+$this->ColGap);
	   }
	   else {
		$x += ($this->CurrCol - $tablestartcolumn) * ($this->ColWidth+$this->ColGap);
	   }
	}

      $w = $tableheader['w'];
      $h = $tableheader['h'];
      $va = $tableheader['va'];
	// Edited mPDF 1.3 for rotated text in cell
      $R = $tableheader['R'];
      $mih = $tableheader['mih'];
      $fill = $tableheader['bgcolor'];
      $border = $tableheader['border'];
      $border_details = $tableheader['border_details'];
      $padding = $tableheader['padding'];
	$this->tabletheadjustfinished = true;

      $textbuffer = $tableheader['textbuffer'];

      $align = $tableheader['a'];
      //Align
      $this->divalign=$align;
	$this->x = $x;

	//mPDF 2.0 FILL BGCOLOR - for cellSpacing
	if ($table['borders_separate']) { 
		 $tablefill = isset($table['bgcolor'][$i]) ? $table['bgcolor'][$i]
  					: (isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0);
		 if ($tablefill) {
  				$color = ConvertColor($tablefill);
  				if ($color) $this->SetFillColor($color['R'],$color['G'],$color['B']);
				$xadj = ($table['border_spacing_H']/2);
				$yadj = ($table['border_spacing_V']/2);
				$wadj = $table['border_spacing_H'];
				$hadj = $table['border_spacing_V'];
				$yadj += $table['padding']['T'];
				$hadj += $table['padding']['T'];
			   	if ($colctr == 1) {		// Left
					$xadj += $table['padding']['L'];
					$wadj += $table['padding']['L'];
			   	}
			   	if ($colctr == count($content[$i]) ) {	// Right
					$wadj += $table['padding']['R'];
			   	}
				$this->Rect($x - $xadj, $y - $yadj, $w + $wadj, $h + $hadj, 'F');
		 }
	}

	// TABLE BORDER - if separate
 	if ($table['borders_separate'] && $table['border']) { 
			$halfspaceL = $table['padding']['L'] + ($table['border_spacing_H']/2);
			$halfspaceR = $table['padding']['R'] + ($table['border_spacing_H']/2);
			$halfspaceT = $table['padding']['T'] + ($table['border_spacing_V']/2);
			$halfspaceB = $table['padding']['B'] + ($table['border_spacing_V']/2);
			$tbx = $x;
			$tby = $y;
			$tbw = $w;
			$tbh = $h;
			$tab_bord = 0;
			// mPDF 3.0
			$corner = 'T';
			$tby -= $halfspaceT + ($table['border_details']['T']['w']/2);
			$tbh += $halfspaceT + ($table['border_details']['T']['w']/2);
			if ($i==0) $this->setBorder ($tab_bord , _BORDER_TOP); 
			if ($colctr == 1) {	// Top Left
				$tbx -= $halfspaceL + ($table['border_details']['L']['w']/2);
				$tbw += $halfspaceL + ($table['border_details']['L']['w']/2);
				$this->setBorder ($tab_bord , _BORDER_LEFT); 
				$corner .= 'L';
			}
			else if ($colctr == count($content[$i])) {	// Right
				$tbw += $halfspaceR + ($table['border_details']['R']['w']/2);
				$this->setBorder ($tab_bord , _BORDER_RIGHT); 
				$corner .= 'R';
			}
			// mPDF 3.0
			$this->_tableRect($tbx, $tby, $tbw, $tbh, $tab_bord , $table['border_details'], false, $table['borders_separate'], 'table', $corner, $table['border_spacing_V'], $table['border_spacing_H'] );
	}

	// mPDF 2.0 Set flag for empty-cells:hide
	if ($table['empty_cells']!='hide' || !empty($textbuffer) || !$table['borders_separate']) { $paintcell = true; }
	else { $paintcell = false; } 

	//Vertical align
	// Added mPDF 2.0 for rotated text in cell
	if ($R && INTVAL($R) > 0 && isset($va) && $va!='B') { $va='B';}

	if (!isset($va) || $va=='M') $this->y += ($h-$mih)/2;
      elseif (isset($va) && $va=='B') $this->y += $h-$mih;
	if ($fill && $paintcell)
      {
 		$color = ConvertColor($fill);
 		if ($color) $this->SetFillColor($color['R'],$color['G'],$color['B']);
 		if ($table['borders_separate']) { 
 			$this->Rect($x+ ($table['border_spacing_H']/2), $y+ ($table['border_spacing_V']/2), $w- $table['border_spacing_H'], $h- $table['border_spacing_V'], 'F');
		}
 		else { 
	 		$this->Rect($x, $y, $w, $h, 'F');
		}
	}

   	//Border
 	if ($table['borders_separate'] && $paintcell) { 
 		$this->_tableRect($x+ ($table['border_spacing_H']/2)+($border_details['L']['w'] /2), $y+ ($table['border_spacing_V']/2)+($border_details['T']['w'] /2), $w-$table['border_spacing_H']-($border_details['L']['w'] /2)-($border_details['R']['w'] /2), $h- $table['border_spacing_V']-($border_details['T']['w'] /2)-($border_details['B']['w']/2), $border, $border_details, false, $table['borders_separate']);
	}
 	else if ($paintcell) { 
		// mPDF 2.1 - Save to buffer
		$this->_tableRect($x, $y, $w, $h, $border, $border_details, true, $table['borders_separate']);  	// true causes buffer
	}

 	//Print cell content
      $this->divheight = $this->table_lineheight*$this->lineheight;
      if (!empty($textbuffer)) {

		// Edited mPDF 1.3 for rotated text in cell
		if ($R) {
					$cellPtSize = $textbuffer[0][11] / $this->shrin_k;
					$cellFontHeight = ($cellPtSize/$this->k);
					$opx = $this->x;
					$opy = $this->y;
					$angle = INTVAL($R);
					// Only allow 45 - 90 degrees (when bottom-aligned) or -90
					if ($angle > 90) { $angle = 90; }
					else if ($angle > 0 && (isset($va) && $va!='B')) { $angle = 90; }
					else if ($angle > 0 && $angle <45) { $angle = 45; }
					else if ($angle < 0) { $angle = -90; }
					$offset = ((sin(deg2rad($angle))) * 0.37 * $cellFontHeight);
					if (!isset($align) || $align =='R') { 
						$this->x += ($w) + ($offset) - ($cellFontHeight/3) - ($padding['R'] + $border_details['R']['w']); 
					}
					else if (!isset($align ) || $align =='C') { 
						$this->x += ($w/2) + ($offset); 
					}
					else { 
						$this->x += ($offset) + ($cellFontHeight/3)+($padding['L'] + $border_details['L']['w']); 
					}
					$str = ltrim(implode(' ',$tableheader['text']));
					$str = mb_rtrim($str ,$this->mb_encoding);

					if (!isset($va) || $va=='M') { 
						$this->y -= ($h-$mih)/2; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += (($h-$mih)/2)+($padding['T'] + $border_details['T']['w']) + ($mih-($padding['T'] + $border_details['T']['w']+$border_details['B']['w']+$padding['B'])); }
						else if ($angle < 0) { $this->y += (($h-$mih)/2)+($padding['T'] + $border_details['T']['w']); }
					}
					else if (isset($va) && $va=='B') { 
						$this->y -= $h-$mih; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += $h-($border_details['B']['w']+$padding['B']); }
						else if ($angle < 0) { $this->y += $h-$mih+($padding['T'] + $border_details['T']['w']); }
					}
					else if (isset($va) && $va=='T') { 
						if ($angle > 0) { $this->y += $mih-($border_details['B']['w']+$padding['B']); }
						else if ($angle < 0) { $this->y += ($padding['T'] + $border_details['T']['w']); }
					}

					$this->Rotate($angle,$this->x,$this->y);
					$s_fs = $this->FontSizePt;
					$s_f = $this->Font;
					$s_st = $this->Style;
					$this->SetFont($textbuffer[0][4],$textbuffer[0][2],$cellPtSize,true,true);
					$this->Text($this->x,$this->y,$str);
					$this->Rotate(0);
					$this->SetFont($s_f,$s_st,$s_fs,true,true);
					$this->x = $opx;
					$this->y = $opy;
		}
		else {
			// Added mPDF 2.0
			if ($table['borders_separate']) {	// NB twice border width
				$xadj = $border_details['L']['w'] + $padding['L'] +($table['border_spacing_H']/2);
				$wadj = $border_details['L']['w'] + $border_details['R']['w'] + $padding['L'] +$padding['R'] + $table['border_spacing_H'];
				$yadj = $border_details['T']['w'] + $padding['T'] + ($table['border_spacing_H']/2);
			}
			else {
				$xadj = $border_details['L']['w']/2 + $padding['L'];
				$wadj = ($border_details['L']['w'] + $border_details['R']['w'])/2 + $padding['L'] + $padding['R'];
				$yadj = $border_details['T']['w']/2 + $padding['T'];
			}

			$this->divwidth=$w-($wadj);
			$this->x += $xadj;
			$this->y += $yadj;
			$this->printbuffer($textbuffer,'',true/*inside a table*/);
		}

	}
      $textbuffer = array();
     }
     $this->y = $y + $h; //Update y coordinate
   // mPDF 2.1 - Multiple Header rows
   }

  }//end of 'if usetableheader ...'
}

// Added mPDF 1.2 HTML headers and Footers
function SetHTMLHeader($Hhtml='',$OE='',$write=false) {
	if ($OE == 'E') {
		$this->HTMLHeaderE = $Hhtml;
		$this->saveHTMLHeaderE_N = '';
		$this->saveHTMLHeaderE_NN = '';
		$this->saveHTMLHeaderE_NNN = '';
		$this->saveHTMLHeaderE_NNNN = '';
		$this->saveHTMLHeaderE_NNNNN = '';
		$this->saveHeaderLinksE_N = array();
		$this->saveHeaderLinksE_NN = array();
		$this->saveHeaderLinksE_NNN = array();
		$this->saveHeaderLinksE_NNNN = array();
		$this->saveHeaderLinksE_NNNNN = array();
		$usehtml = $this->HTMLHeaderE ;
	}
	else {
		$this->HTMLHeader = $Hhtml;
		$this->saveHTMLHeader_N = '';
		$this->saveHTMLHeader_NN = '';
		$this->saveHTMLHeader_NNN = '';
		$this->saveHTMLHeader_NNNN = '';
		$this->saveHTMLHeader_NNNNN = '';
		$this->saveHeaderLinks_N = array();
		$this->saveHeaderLinks_NN = array();
		$this->saveHeaderLinks_NNN = array();
		$this->saveHeaderLinks_NNNN = array();
		$this->saveHeaderLinks_NNNNN = array();
		$usehtml = $this->HTMLHeader ;
	}
	if (!$this->useOddEven && $OE == 'E') { return; }
	if ($Hhtml=='') { return; }

	if($this->state==0) { 
		$this->beforedoc = true;
		$firstpage = true;
		$this->AddPage($this->CurOrientation);
		$this->beforedoc = false;
	}
	else { $firstpage = false; }

	if ($OE == 'E') {
		$this->headerDetails['even'] = array();	// override and clear any other non-HTML header/footer
	}
	else {
		$this->headerDetails['odd'] = array();	// override and clear any non-HTML other header/footer
	}
	$save_y = $this->y;

	$save_cols = false;
	if ($this->ColActive) {
		$save_cols = true;
		$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
		$this->SetColumns(0);
	}

	// SET MARGINS + TOP, LEFT position (ODD / EVEN)
	for($i = 1;$i<=5;$i++) {
		if ($OE == 'E') {// EVEN
			$this->lMargin=$this->DefrMargin;
			$this->rMargin=$this->DeflMargin;
			$this->MarginCorrection = $this->DefrMargin-$this->DeflMargin;
		}
		else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$this->lMargin=$this->DeflMargin;
			$this->rMargin=$this->DefrMargin;
			if ($this->useOddEven) { $this->MarginCorrection = $this->DeflMargin-$this->DefrMargin; }
		}
		// SET POSITION & FONT VALUES
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->x = $this->lMargin;
		$this->y = $this->margin_header;

		$printpageno = str_repeat($this->headerPageNoMarker,$i);
		// mPDF 3.0
		$hd = str_replace('{PAGENO}',$this->pagenumPrefix.$printpageno.$this->pagenumSuffix,$usehtml);
		$hd = str_replace($this->aliasNbPgGp,$this->nbpgPrefix.$this->aliasNbPgGp.$this->nbpgSuffix,$hd);	// {nbpg}
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		// mPDF 3.0
		$this->writingHTMLheader = true;
		// mPDF 3.0
		$this->HTMLheaderPageLinks = array();
		$save_bgs = $this->pageBackgrounds;
		$this->pageBackgrounds = array();

		$this->writeHTML($hd , 4);	// parameter 4 saves output to $this->headerbuffer

		// mPDF 3.0 - BODY Backgrounds
		$s = $this->PrintPageBackgrounds();
		$this->headerbuffer = $s . $this->headerbuffer;
		$this->pageBackgrounds = array();
		$this->pageBackgrounds = $save_bgs;
		$this->pageoutput[$this->page]=array();


		// mPDF 3.0
		$this->writingHTMLheader = false;

		if ($OE == 'E') {// EVEN
			if ($i==1) { 
				$this->saveHTMLHeaderE_N = $this->headerbuffer;
				$this->saveHeaderLinksE_N = $this->HTMLheaderPageLinks;
			}
			else if ($i==2) { 
				$this->saveHTMLHeaderE_NN = $this->headerbuffer;
				$this->saveHeaderLinksE_NN = $this->HTMLheaderPageLinks;
			}
			else if ($i==3) { 
				$this->saveHTMLHeaderE_NNN = $this->headerbuffer;
				$this->saveHeaderLinksE_NNN = $this->HTMLheaderPageLinks;
			}
			else if ($i==4) { 
				$this->saveHTMLHeaderE_NNNN = $this->headerbuffer;
				$this->saveHeaderLinksE_NNNN = $this->HTMLheaderPageLinks;
			}
			else if ($i==5) { 
				$this->saveHTMLHeaderE_NNNNN = $this->headerbuffer;
				$this->saveHeaderLinksE_NNNNN = $this->HTMLheaderPageLinks;
			}
		}
		else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			if ($i==1) { 
				$this->saveHTMLHeader_N = $this->headerbuffer;
				$this->saveHeaderLinks_N = $this->HTMLheaderPageLinks;
			}
			else if ($i==2) { 
				$this->saveHTMLHeader_NN = $this->headerbuffer;
				$this->saveHeaderLinks_NN = $this->HTMLheaderPageLinks;
			}
			else if ($i==3) { 
				$this->saveHTMLHeader_NNN = $this->headerbuffer;
				$this->saveHeaderLinks_NNN = $this->HTMLheaderPageLinks;
			}
			else if ($i==4) { 
				$this->saveHTMLHeader_NNNN = $this->headerbuffer;
				$this->saveHeaderLinks_NNNN = $this->HTMLheaderPageLinks;
			}
			else if ($i==5) { 
				$this->saveHTMLHeader_NNNNN = $this->headerbuffer;
				$this->saveHeaderLinks_NNNNN = $this->HTMLheaderPageLinks;
			}
		}
	}
	$this->ResetMargins();
	$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
	if ($firstpage) { 
		$this->page=0;
		$this->pages[1]='';
		$this->state=0;
		// mPDF 3.0
		$this->buffer='';
		return;
	}
	else if ($write && (($this->useOddEven && $OE == 'E' && ($this->page)%2==0) || ($this->useOddEven && $OE != 'E' && ($this->page)%2==1) || !$this->useOddEven)) { $this->writeHTMLHeaders(); $this->SetY($save_y) ; }
	else $this->SetY($save_y) ; 
	$this->pageoutput[$this->page]['Font']='';
	if ($save_cols) {
		// Restore columns
		$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
	}
}

function SetHTMLFooter($Fhtml='',$OE='') {
	if ($OE == 'E') {
		$this->HTMLFooterE = $Fhtml;
		$this->saveHTMLFooterE_N = '';
		$this->saveHTMLFooterE_NN = '';
		$this->saveHTMLFooterE_NNN = '';
		$this->saveHTMLFooterE_NNNN = '';
		$this->saveHTMLFooterE_NNNNN = '';
		$this->saveFooterLinksE_N = array();
		$this->saveFooterLinksE_NN = array();
		$this->saveFooterLinksE_NNN = array();
		$this->saveFooterLinksE_NNNN = array();
		$this->saveFooterLinksE_NNNNN = array();
		$usehtml = $this->HTMLFooterE ;
	}
	else {
		$this->HTMLFooter = $Fhtml;
		$this->saveHTMLFooter_N = '';
		$this->saveHTMLFooter_NN = '';
		$this->saveHTMLFooter_NNN = '';
		$this->saveHTMLFooter_NNNN = '';
		$this->saveHTMLFooter_NNNNN = '';
		$this->saveFooterLinks_N = array();
		$this->saveFooterLinks_NN = array();
		$this->saveFooterLinks_NNN = array();
		$this->saveFooterLinks_NNNN = array();
		$this->saveFooterLinks_NNNNN = array();
		$usehtml = $this->HTMLFooter ;
	}
	if (!$this->useOddEven && $OE == 'E') { return; }
	if ($Fhtml=='') { return false; }

	if($this->state==0) { 
		$this->beforedoc = true;
		$firstpage = true;
		$this->AddPage($this->CurOrientation);
		$this->beforedoc = false;
	}
	else { $firstpage = false; }

	if ($OE == 'E') {
		$this->footerDetails['even'] = array();	// override and clear any other header/footer
	}
	else {
		$this->footerDetails['odd'] = array();	// override and clear any other header/footer
	}

	$this->InFooter = true;
	$save_y = $this->y;
	$save_cols = false;
	if ($this->ColActive) {
		$save_cols = true;
		$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
		$this->SetColumns(0);
	}
	for($i = 1;$i<=5;$i++) {
		if ($OE == 'E') {// EVEN
			$this->lMargin=$this->DefrMargin;
			$this->rMargin=$this->DeflMargin;
			$this->MarginCorrection = $this->DefrMargin-$this->DeflMargin;
		}
		else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$this->lMargin=$this->DeflMargin;
			$this->rMargin=$this->DefrMargin;
			if ($this->useOddEven) { $this->MarginCorrection = $this->DeflMargin-$this->DefrMargin; }
		}
		// SET POSITION & FONT VALUES
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->x=$this->lMargin;
		$top_y = $this->y = $this->h - $this->margin_footer;

		$printpageno = str_repeat($this->headerPageNoMarker,$i);
		// mPDF 3.0
		$hd = str_replace('{PAGENO}',$this->pagenumPrefix.$printpageno.$this->pagenumSuffix,$usehtml);
		$hd = str_replace($this->aliasNbPgGp,$this->nbpgPrefix.$this->aliasNbPgGp.$this->nbpgSuffix,$hd);	// {nbpg}
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		// mPDF 3.0
		$this->writingHTMLheader = true;

		// mPDF 3.0
		$this->HTMLheaderPageLinks = array();
		$this->pageoutput[$this->page]=array();
		$save_bgs = $this->pageBackgrounds;
		$this->pageBackgrounds = array();

		$this->writeHTML($hd , 4);	// parameter 4 saves output to $this->headerbuffer

		// mPDF 3.0 - BODY Backgrounds
		$s = $this->PrintPageBackgrounds();
		$this->headerbuffer = $s . $this->headerbuffer;
		$this->pageBackgrounds = array();
		$this->pageBackgrounds = $save_bgs;

		// mPDF 3.0
		$this->writingHTMLheader = false;
		if ($OE == 'E') {// EVEN
			$this->saveHTMLFooterE_height = $this->y - $top_y;
			if ($i==1) {
				$this->saveHTMLFooterE_N = $this->headerbuffer;
				$this->saveFooterLinksE_N = $this->HTMLheaderPageLinks;
			}
			else if ($i==2) {
				$this->saveHTMLFooterE_NN = $this->headerbuffer;
				$this->saveFooterLinksE_NN = $this->HTMLheaderPageLinks;
			}
			else if ($i==3) {
				$this->saveHTMLFooterE_NNN = $this->headerbuffer;
				$this->saveFooterLinksE_NNN = $this->HTMLheaderPageLinks;
			}
			else if ($i==4) {
				$this->saveHTMLFooterE_NNNN = $this->headerbuffer;
				$this->saveFooterLinksE_NNNN = $this->HTMLheaderPageLinks;
			}
			else if ($i==5) {
				$this->saveHTMLFooterE_NNNNN = $this->headerbuffer;
				$this->saveFooterLinksE_NNNNN = $this->HTMLheaderPageLinks;
			}
		}
		else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$this->saveHTMLFooter_height = $this->y - $top_y;
			if ($i==1) {
				$this->saveHTMLFooter_N = $this->headerbuffer;
				$this->saveFooterLinks_N = $this->HTMLheaderPageLinks;
			}
			else if ($i==2) {
				$this->saveHTMLFooter_NN = $this->headerbuffer;
				$this->saveFooterLinks_NN = $this->HTMLheaderPageLinks;
			}
			else if ($i==3) {
				$this->saveHTMLFooter_NNN = $this->headerbuffer;
				$this->saveFooterLinks_NNN = $this->HTMLheaderPageLinks;
			}
			else if ($i==4) {
				$this->saveHTMLFooter_NNNN = $this->headerbuffer;
				$this->saveFooterLinks_NNNN = $this->HTMLheaderPageLinks;
			}
			else if ($i==5) {
				$this->saveHTMLFooter_NNNNN = $this->headerbuffer;
				$this->saveFooterLinks_NNNNN = $this->HTMLheaderPageLinks;
			}
		}
	}

	$this->InFooter = false;
	$this->ResetMargins();
	$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
	$this->SetY($save_y) ;
	$this->pageoutput[$this->page]['Font']='';
	if ($firstpage) { 
		$this->page=0;
		$this->pages[1]='';
		$this->state=0;
		// mPDF 3.0
		$this->buffer='';
		return;
	}
	if ($save_cols) {
		// Restore columns
		$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
	}
}

// Called internally from Header
function writeHTMLHeaders() {
	// mPDF 3.0 Add PageNum prefix/suffix
	$pgno = $this->docPageNum($this->page); 
	if ($this->useOddEven && ($this->page)%2==0) {	// EVEN
		if (strlen($pgno)==1) $s = $this->saveHTMLHeaderE_N ;
		else if (strlen($pgno)==2) $s = $this->saveHTMLHeaderE_NN ;
		else if (strlen($pgno)==3) $s = $this->saveHTMLHeaderE_NNN ;
		else if (strlen($pgno)==4) $s = $this->saveHTMLHeaderE_NNNN ;
		else { $s = $this->saveHTMLHeaderE_NNNNN ; }
	}
	else {
		if (strlen($pgno)==1) $s = $this->saveHTMLHeader_N ;
		else if (strlen($pgno)==2) $s = $this->saveHTMLHeader_NN ;
		else if (strlen($pgno)==3) $s = $this->saveHTMLHeader_NNN ;
		else if (strlen($pgno)==4) $s = $this->saveHTMLHeader_NNNN ;
		else { $s = $this->saveHTMLHeader_NNNNN ; }
	}
	$os = '';
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$os = sprintf('q 0 -1 1 0 0 %.3f cm ',($this->h*$this->k));
	}
	if ($this->is_MB) {
		$chars = preg_split('//', $this->headerPageNoMarker,-1,PREG_SPLIT_NO_EMPTY);
		$patt = "\x00" . implode("\x00",$chars);
		$os .= preg_replace('/('.preg_quote($patt,'/').'){1,5}/',$this->UTF8ToUTF16BE($pgno,false),$s);
	}
	else {
		$os .= preg_replace('/('.preg_quote($this->headerPageNoMarker,'/').'){1,5}/',$pgno,$s); 
	}
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$os .= 'Q' . "\n";
	}
	$this->pages[$this->page] .= $os;
	$this->pageoutput[$this->page]['Font']='';
	// mPDF 3.0
	if ($this->useOddEven && ($this->page)%2==0) {	// EVEN
		if (strlen($pgno)==1) $lks = $this->saveHeaderLinksE_N ;
		else if (strlen($pgno)==2) $lks = $this->saveHeaderLinksE_NN ;
		else if (strlen($pgno)==3) $lks = $this->saveHeaderLinksE_NNN ;
		else if (strlen($pgno)==4) $lks = $this->saveHeaderLinksE_NNNN ;
		else { $lks = $this->saveHeaderLinksE_NNNNN ; }
	}
	else {
		if (strlen($pgno)==1) $lks = $this->saveHeaderLinks_N ;
		else if (strlen($pgno)==2) $lks = $this->saveHeaderLinks_NN ;
		else if (strlen($pgno)==3) $lks = $this->saveHeaderLinks_NNN ;
		else if (strlen($pgno)==4) $lks = $this->saveHeaderLinks_NNNN ;
		else { $lks = $this->saveHeaderLinks_NNNNN ; }
	}
	foreach($lks AS $lk) {
		if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
			$lw = $lk[2]/$this->k;
			$lh = $lk[3]/$this->k;
			$ax = ($lk[0]/$this->k);
			$ay = (($this->hPt-$lk[1])/$this->k);
			$bx = $this->h-$ay-$lh;
			$by = $ax;
			$lk[0] = $bx*$this->k;
			$lk[1] = ($this->h-$by)*$this->k;
			$lk[2] = $lh*$this->k;	// swap width and height
			$lk[3] = $lw*$this->k;
		}
		$this->PageLinks[$this->page][]=$lk;
	}
}

function writeHTMLFooters() {
	// mPDF 3.0 Add PageNum prefix/suffix
	$pgno = $this->docPageNum($this->page); 
	if ($this->useOddEven && ($this->page)%2==0) {	// EVEN
		if (strlen($pgno)==1) $s = $this->saveHTMLFooterE_N ;
		else if (strlen($pgno)==2) $s = $this->saveHTMLFooterE_NN ;
		else if (strlen($pgno)==3) $s = $this->saveHTMLFooterE_NNN ;
		else if (strlen($pgno)==4) $s = $this->saveHTMLFooterE_NNNN ;
		else { $s = $this->saveHTMLFooterE_NNNNN ; }
		$h = $this->saveHTMLFooterE_height;
	}
	else {
		if (strlen($pgno)==1) $s = $this->saveHTMLFooter_N ;
		else if (strlen($pgno)==2) $s = $this->saveHTMLFooter_NN ;
		else if (strlen($pgno)==3) $s = $this->saveHTMLFooter_NNN ;
		else if (strlen($pgno)==4) $s = $this->saveHTMLFooter_NNNN ;
		else { $s = $this->saveHTMLFooter_NNNNN ; }
		$h = $this->saveHTMLFooter_height;
	}

	$os = '';
	$os .= $this->StartTransform(true)."\n";
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$os .= sprintf('q 0 -1 1 0 0 %.3f cm ',($this->h*$this->k));
	}
	$os .= $this->transformTranslate(0, -$h, true)."\n";
	if ($this->is_MB) {
		$chars = preg_split('//', $this->headerPageNoMarker,-1,PREG_SPLIT_NO_EMPTY);
		$patt = "\x00" . implode("\x00",$chars);
		$os .= preg_replace('/('.preg_quote($patt,'/').'){1,5}/',$this->UTF8ToUTF16BE($pgno,false),$s);
	}
	else {
		$os .= preg_replace('/('.preg_quote($this->headerPageNoMarker,'/').'){1,5}/',$pgno,$s); 
	}
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$os .= 'Q' . "\n";
	}
	//Stop Transformation
	$os .= $this->StopTransform(true)."\n";
	$this->pages[$this->page] .= $os;
	$this->pageoutput[$this->page]['Font']='';
	// mPDF 3.0
	if ($this->useOddEven && ($this->page)%2==0) {	// EVEN
		if (strlen($pgno)==1) $lks = $this->saveFooterLinksE_N ;
		else if (strlen($pgno)==2) $lks = $this->saveFooterLinksE_NN ;
		else if (strlen($pgno)==3) $lks = $this->saveFooterLinksE_NNN ;
		else if (strlen($pgno)==4) $lks = $this->saveFooterLinksE_NNNN ;
		else { $lks = $this->saveFooterLinksE_NNNNN ; }
	}
	else {
		if (strlen($pgno)==1) $lks = $this->saveFooterLinks_N ;
		else if (strlen($pgno)==2) $lks = $this->saveFooterLinks_NN ;
		else if (strlen($pgno)==3) $lks = $this->saveFooterLinks_NNN ;
		else if (strlen($pgno)==4) $lks = $this->saveFooterLinks_NNNN ;
		else { $lks = $this->saveFooterLinks_NNNNN ; }
	}
	foreach($lks AS $lk) {
		$lk[1] += $h*$this->k;
		if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
			$lw = $lk[2]/$this->k;
			$lh = $lk[3]/$this->k;
			$ax = ($lk[0]/$this->k);
			$ay = (($this->hPt-$lk[1])/$this->k);
			$bx = $this->h-$ay-$lh;
			$by = $ax;
			$lk[0] = $bx*$this->k;
			$lk[1] = ($this->h-$by)*$this->k;
			$lk[2] = $lh*$this->k;	// swap width and height
			$lk[3] = $lw*$this->k;
		}
		$this->PageLinks[$this->page][]=$lk;
	}
}

// mPDF 2.2 - function name changed to capitalise first letter
function DefHeaderByName($name,$arr) {
	if (!$name) { $name = '_default'; }
	$this->pageheaders[$name] = $arr;
}

// mPDF 2.2 - function name changed to capitalise first letter
function DefFooterByName($name,$arr) {
	if (!$name) { $name = '_default'; }
	$this->pagefooters[$name] = $arr;
}

// mPDF 2.2 - function name changed to capitalise first letter
function SetHeaderByName($name,$side='O',$write=false) {
	if (!$name) { $name = '_default'; }
	if ($side=='E') { $this->headerDetails['even'] = $this->pageheaders[$name]; }
	else { $this->headerDetails['odd'] = $this->pageheaders[$name]; }
	if ($write) { $this->Header(); }
}

// mPDF 2.2 - function name changed to capitalise first letter
function SetFooterByName($name,$side='O') {
	if (!$name) { $name = '_default'; }
	if ($side=='E') { $this->footerDetails['even'] = $this->pagefooters[$name]; }
	else { $this->footerDetails['odd'] = $this->pagefooters[$name]; }
}

// mPDF 2.2 - function name changed to capitalise first letter
function DefHTMLHeaderByName($name,$html) {
	if (!$name) { $name = '_default'; }
	$this->pageHTMLheaders[$name] = $html;
}

// mPDF 2.2 - function name changed to capitalise first letter
function DefHTMLFooterByName($name,$html) {
	if (!$name) { $name = '_default'; }
	$this->pageHTMLfooters[$name] = $html;
}

// mPDF 2.2 - function name changed to capitalise first letter
function SetHTMLHeaderByName($name,$side='O',$write=false) {
	if (!$name) { $name = '_default'; }
	$this->SetHTMLHeader($this->pageHTMLheaders[$name],$side,$write);
}

// mPDF 2.2 - function name changed to capitalise first letter
function SetHTMLFooterByName($name,$side='O') {
	if (!$name) { $name = '_default'; }
	$this->SetHTMLFooter($this->pageHTMLfooters[$name],$side);
}


// mPDF 2.2 - function name changed to capitalise first letter
function SetHeader($Harray=array(),$side='',$write=false) {
  if (is_string($Harray)) {
    if (strlen($Harray)==0) {
	if ($side=='O') { $this->headerDetails['odd'] = array(); }
	else if ($side=='E') { $this->headerDetails['even'] = array(); }
	else { $this->headerDetails = array(); }
   }
   else if (strpos($Harray,'|') || strpos($Harray,'|')===0) {
	$hdet = explode('|',$Harray);
	$this->headerDetails = array (
  		'odd' => array (
	'L' => array ('content' => $hdet[0], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'C' => array ('content' => $hdet[1], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'R' => array ('content' => $hdet[2], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
  		),
  		'even' => array (
	'R' => array ('content' => $hdet[0], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'C' => array ('content' => $hdet[1], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'L' => array ('content' => $hdet[2], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
		)
	);
    }
    else {
	$this->headerDetails = array (
  		'odd' => array (
	'R' => array ('content' => $Harray, 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
  		),
  		'even' => array (
	'L' => array ('content' => $Harray, 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
		)
	);
    }
  }
  else if (is_array($Harray)) {
	if ($side=='O') { $this->headerDetails['odd'] = $Harray; }
	else if ($side=='E') { $this->headerDetails['even'] = $Harray; }
	else { $this->headerDetails = $Harray; }
  }
  // Overwrite any HTML Header previously set
  if ($side=='E') { $this->SetHTMLHeader('','E'); }
  else if ($side=='O') {  $this->SetHTMLHeader(''); }
  else {
	$this->SetHTMLHeader('');
	$this->SetHTMLHeader('','E');
  }

  if ($write) { 
	$save_y = $this->y;
	$this->Header();
	$this->SetY($save_y) ; 
  }
}

// mPDF 2.2 - function name changed to capitalise first letter
function SetFooter($Farray=array(),$side='') {
  if (is_string($Farray)) {
    if (strlen($Farray)==0) {
	if ($side=='O') { $this->footerDetails['odd'] = array(); }
	else if ($side=='E') { $this->footerDetails['even'] = array(); }
	else { $this->footerDetails = array(); }
    }
    else if (strpos($Farray,'|') || strpos($Farray,'|')===0) {
	$fdet = explode('|',$Farray);
	$this->footerDetails = array (
		'odd' => array (
	'L' => array ('content' => $fdet[0], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'C' => array ('content' => $fdet[1], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'R' => array ('content' => $fdet[2], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		),
		'even' => array (
	'R' => array ('content' => $fdet[0], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'C' => array ('content' => $fdet[1], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'L' => array ('content' => $fdet[2], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		)
	);
    }
    else {
	$this->footerDetails = array (
		'odd' => array (
	'R' => array ('content' => $Farray, 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		),
		'even' => array (
	'L' => array ('content' => $Farray, 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		)
	);
    }
  }
  else if (is_array($Farray)) {
	if ($side=='O') { $this->footerDetails['odd'] = $Farray; }
	else if ($side=='E') { $this->footerDetails['even'] = $Farray; }
	else { $this->footerDetails = $Farray; }
  }
  // Overwrite any HTML Footer previously set
  if ($side=='E') { $this->SetHTMLFooter('','E'); }
  else if ($side=='O') {  $this->SetHTMLFooter(''); }
  else {
	$this->SetHTMLFooter('');
	$this->SetHTMLFooter('','E');
  }
}


function setUnvalidatedText($txt, $alpha=-1) {
	if ($alpha>=0) $this->watermarkTextAlpha = $alpha;
	$this->watermarkText = $txt;
}
// mPDF 2.2 Better-named alternative to setUnvalidatedText
function SetWatermarkText($txt, $alpha=-1) {
	if ($alpha>=0) $this->watermarkTextAlpha = $alpha;
	$this->watermarkText = $txt;
}

// mPDF 2.2
function SetWatermarkImage($src, $alpha=-1, $size='D', $pos='F') {
	if ($alpha>=0) $this->watermarkImageAlpha = $alpha;
	$this->watermarkImage = $src;
	$this->watermark_size = $size;
	$this->watermark_pos = $pos;
}

//Page footer
function Footer() {
  // PAGED MEDIA - CROP / CROSS MARKS from @PAGE
  if ($this->show_marks == 'CROP') {
	// Show TICK MARKS
	$this->SetLineWidth(0.1);	// = 0.1 mm
	$this->SetDrawColor(0);
	$l = 18;	// Default length in mm of crop line
	$m = 8;	// Distance of crop mark from margin in mm
	$b = 8;	// Non-printable border at edge of paper sheet in mm
	$ax1 = $b;
	$bx = $this->page_box['outer_width_LR'] - $m;
	$ax = max($ax1, $bx-$l);
	$cx1 = $this->w - $b;
	$dx = $this->w - $this->page_box['outer_width_LR'] + $m;
	$cx = min($cx1, $dx+$l);
	$ay1 = $b;
	$by = $this->page_box['outer_width_TB'] - $m;
	$ay = max($ay1, $by-$l);
	$cy1 = $this->h - $b;
	$dy = $this->h - $this->page_box['outer_width_TB'] + $m;
	$cy = min($cy1, $dy+$l);

	$this->Line($ax, $this->page_box['outer_width_TB'], $bx, $this->page_box['outer_width_TB']);
	$this->Line($cx, $this->page_box['outer_width_TB'], $dx, $this->page_box['outer_width_TB']);
	$this->Line($ax, $this->h - $this->page_box['outer_width_TB'], $bx, $this->h - $this->page_box['outer_width_TB']);
	$this->Line($cx, $this->h - $this->page_box['outer_width_TB'], $dx, $this->h - $this->page_box['outer_width_TB']);
	$this->Line($this->page_box['outer_width_LR'], $ay, $this->page_box['outer_width_LR'], $by);
	$this->Line($this->page_box['outer_width_LR'], $cy, $this->page_box['outer_width_LR'], $dy);
	$this->Line($this->w - $this->page_box['outer_width_LR'], $ay, $this->w - $this->page_box['outer_width_LR'], $by);
	$this->Line($this->w - $this->page_box['outer_width_LR'], $cy, $this->w - $this->page_box['outer_width_LR'], $dy);
  }
  if ($this->show_marks == 'CROSS') {
	$this->SetLineWidth(0.1);	// = 0.1 mm
	$this->SetDrawColor(0);
	$l = 14 /2;	// longer length of the cross line (half)
	$w = 6 /2;	// shorter width of the cross line (half)
	$r = 1.2;	// radius of circle
	$m = 10;	// Distance of cross mark from margin in mm
	$x1 = $this->page_box['outer_width_LR'] - $m;
	$x2 = $this->w - $this->page_box['outer_width_LR'] + $m;
	$y1 = $this->page_box['outer_width_TB'] - $m;
	$y2 = $this->h - $this->page_box['outer_width_TB'] + $m;
	// Left
	$this->Circle($x1, $this->h/2, $r, 'S') ;
	$this->Line($x1-$w, $this->h/2, $x1+$w, $this->h/2);
	$this->Line($x1, $this->h/2-$l, $x1, $this->h/2+$l);
	// Right
	$this->Circle($x2, $this->h/2, $r, 'S') ;
	$this->Line($x2-$w, $this->h/2, $x2+$w, $this->h/2);
	$this->Line($x2, $this->h/2-$l, $x2, $this->h/2+$l);
	// Top
	$this->Circle($this->w/2, $y1, $r, 'S') ;
	$this->Line($this->w/2, $y1-$w, $this->w/2, $y1+$w);
	$this->Line($this->w/2-$l, $y1, $this->w/2+$l, $y1);
	// Bottom
	$this->Circle($this->w/2, $y2, $r, 'S') ;
	$this->Line($this->w/2, $y2-$w, $this->w/2, $y2+$w);
	$this->Line($this->w/2-$l, $y2, $this->w/2+$l, $y2);
  }


	// mPDF 2.0 If @page set non-HTML headers/footers named, they were not read until later in the HTML code - so now set them
	if ($this->page==1) {
		if ($this->firstPageBoxHeader) {
			$this->headerDetails['odd'] = $this->pageheaders[$this->firstPageBoxHeader]; 
  			$this->Header();
		}
		if ($this->firstPageBoxHeaderEven) {
			$this->headerDetails['even'] = $this->pageheaders[$this->firstPageBoxHeaderEven];
		}
		if ($this->firstPageBoxFooter) {
			$this->footerDetails['odd'] = $this->pagefooters[$this->firstPageBoxFooter];
		}
		if ($this->firstPageBoxFooterEven) {
			$this->footerDetails['even'] = $this->pagefooters[$this->firstPageBoxFooterEven]; 
		}
		$this->firstPageBoxHeader='';
		$this->firstPageBoxHeaderEven='';
		$this->firstPageBoxFooter='';
		$this->firstPageBoxFooterEven='';
	}



  // Added mPDF 2.0 HTML headers and Footers
  if (($this->useOddEven && ($this->page%2==0) && $this->HTMLFooterE) || ($this->useOddEven && ($this->page%2==1) && $this->HTMLFooter) || (!$this->useOddEven && $this->HTMLFooter)) {
	$this->writeHTMLFooters(); 
  	if (($this->watermarkText) && ($this->showWatermarkText)) {
		$this->watermark( $this->watermarkText, 45, 120, $this->watermarkTextAlpha);	// Watermark text
  	}
	if (($this->watermarkImage) && ($this->showWatermarkImage)) {
		$this->watermarkImg( $this->watermarkImage, $this->watermarkImageAlpha);	// Watermark image
	}
	return;
  }

  $this->processingHeader=true;
  $this->ResetMargins();	// necessary after columns
  $this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
  if (($this->watermarkText) && ($this->showWatermarkText)) {
	$this->watermark( $this->watermarkText, 45, 120, $this->watermarkTextAlpha);	// Watermark text
  }
  if (($this->watermarkImage) && ($this->showWatermarkImage)) {
	$this->watermarkImg( $this->watermarkImage, $this->watermarkImageAlpha);	// Watermark image
  }

  $h = $this->footerDetails;
  if(count($h)) {

	// mPDF 2.3
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->_out(sprintf('q 0 -1 1 0 0 %.3f cm ',($this->h*$this->k)));
		$headerpgwidth = $this->h - $this->orig_lMargin - $this->orig_rMargin;
		if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
			$headerlmargin = $this->orig_rMargin;
		}
		else {
			$headerlmargin = $this->orig_lMargin;
		}
	}
	else { 
		$yadj = 0; 
		$headerpgwidth = $this->pgwidth;
		$headerlmargin = $this->lMargin;
	}
	$this->SetY(-$this->margin_footer);

	$this->SetTextColor(0);
    	$this->SUP = false;
	$this->SUB = false;
	$this->bullet = false;

	// only show pagenumber if numbering on
	// mPDF 3.0 Add PageNum prefix/suffix
	$pgno = $this->docPageNum($this->page, true); 

	if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
			$side = 'even';
	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$side = 'odd';
	}
	$maxfontheight = 0;
	foreach(array('L','C','R') AS $pos) {
	  if ($h[$side][$pos]['content']) {
		if ($h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		else { $hfsz = $this->default_font_size; }
		$maxfontheight = max($maxfontheight,$hfsz);
	  }
	}
	// LEFT-CENTER-RIGHT
	foreach(array('L','C','R') AS $pos) {
	  if ($h[$side][$pos]['content']) {
		$hd = str_replace('{PAGENO}',$pgno,$h[$side][$pos]['content']);
		// mPDF 3.0
		$hd = str_replace($this->aliasNbPgGp,$this->nbpgPrefix.$this->aliasNbPgGp.$this->nbpgSuffix,$hd);	// {nbpg}
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		if ($h[$side][$pos]['font-family']) { $hff = $h[$side][$pos]['font-family']; }
		// mPDF 3.0 original_ in case pagebreak in middle of table
		else { $hff = $this->original_default_font; }
		if ($h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		// mPDF 3.0 original_ in case pagebreak in middle of table
		else { $hfsz = $this->original_default_font_size; }
		$maxfontheight = max($maxfontheight,$hfsz);
		if ($h[$side][$pos]['font-style']) { $hfst = $h[$side][$pos]['font-style']; }
		else { $hfst = ''; }
		if ($h[$side][$pos]['color']) { 
			$hfcol = $h[$side][$pos]['color']; 
			$cor = ConvertColor($hfcol);
			if ($cor) { $this->SetTextColor($cor['R'],$cor['G'],$cor['B']); }
		}
		else { $hfcol = ''; }
		// mPDF 3.0 Force output
		$this->SetFont($hff,$hfst,$hfsz,true,true);
		// mPDF 2.3
		$this->x = $headerlmargin ;
		$this->y = $this->h - $this->margin_footer - ($maxfontheight/$this->k * 0.5) - 0.5;
		$hd = $this->purify_utf8_text($hd);
		if ($this->text_input_as_HTML) {
			$hd = $this->all_entities_to_utf8($hd);
		}
		// CONVERT CODEPAGE
		if (!$this->is_MB) { $hd = mb_convert_encoding($hd,$this->mb_encoding,'UTF-8'); }
		// DIRECTIONALITY RTL
		$this->magic_reverse_dir($hd);
		$align = $pos;
		if ($this->directionality == 'rtl') { 
			if ($pos == 'L') { $align = 'R'; }
			else if ($pos == 'R') { $align = 'L'; }
		}

		// mPDF 2.4
		if ($pos!='L' && (stripos($hd,$this->aliasNbPg)!==false || stripos($hd,$this->aliasNbPgGp)!==false)) { 
			if (stripos($hd,$this->aliasNbPgGp)!==false) { $type= 'nbpggp'; } else { $type= 'nbpg'; }
			$this->_out('{mpdfheader'.$type.' '.$pos.' ff='.$hff.' fs='.$hfst.' fz='.$hfsz.'}'); 
			$this->Cell($headerpgwidth ,0,$hd,0,0,$align);
			$this->_out('Q');
		}
		else { 
		// mPDF 2.3
			$this->Cell($headerpgwidth ,0,$hd,0,0,$align);
		}
		if ($hfcol) { $this->SetTextColor(0); }
	  }
	}
	//Return Font to normal
	$this->SetFont($this->default_font,'',$this->original_default_font_size);

	// LINE
	if ($h[$side]['line']) { 
		$this->SetLineWidth(0.1);
		$this->SetDrawColor(0);
		// mPDF 2.3
		$this->Line($headerlmargin , $this->y-($maxfontheight/$this->k), $headerlmargin +$headerpgwidth, $this->y-($maxfontheight/$this->k));
	}
	// mPDF 2.3
	if ($this->forcePortraitHeaders && $this->CurOrientation=='L' && $this->CurOrientation!=$this->DefOrientation) {
		$this->_out('Q');
	}
  }
  $this->processingHeader=false;

}

///////////////////
///////////////////
// HYPHENATION
// mPDF 2.5 Soft Hyphens
///////////////////
// Soft hyphs
function softHyphenate($word, $maxWidth) {
	// Don't hyphenate web addresses
	if (preg_match('/^(http:|www\.)/',$word)) { return array(false,'','',''); }

	// Get dictionary
	$poss = array();
	$softhyphens = array();
	$offset = 0;
	$p = true;
	if ($this->is_MB) { 
		$wl = mb_strlen($word,'UTF-8');
	}
	else {
		$wl = strlen($word);
	}
	while($offset < $wl) {
		if ($this->is_MB) { 
			$p = mb_strpos($word, "\xc2\xad", $offset, 'UTF-8');
		}
		// mPDF 3.0 Soft Hyphens chr(173)
		else if ($this->FontFamily!='symbol' && $this->FontFamily!='zapfdingbats') {
			$p = strpos($word, chr(173), $offset);
		}
		if ($p !== false) { $poss[] = $p - count($poss); }
		else { break; }
		$offset = $p+1;
	}
	$success = false;
	foreach($poss AS $i) {
			if ($this->is_MB) { 
				$a = mb_substr($word,0,$i,'UTF-8');
				if ($this->GetStringWidth($a.'-') > $maxWidth) { break ; }
				$pre = $a;
				$post = mb_substr($word,$i+1,mb_strlen($word,'UTF-8'),'UTF-8');
				$prelength = mb_strlen($pre, 'UTF-8');
			}
			else { 
				$a = substr($word,0,$i);
				if ($this->GetStringWidth($a.'-') > $maxWidth) { break ; }
				$pre = $a;
				$post = substr($word,$i+1,strlen($word));
				$prelength = strlen($pre);
			}
			$success = true;
	}
	return array($success,$pre,$post,$prelength);
}

///////////////////
// Word hyphenation
function hyphenateWord($word, $maxWidth) {
	// Do everything inside this function in utf-8
	// Don't hyphenate web addresses
	if (preg_match('/^(http:|www\.)/',$word)) { return array(false,'','',''); }


	// Get dictionary
	if (!$this->loadedSHYdictionary) {
		if (file_exists(_MPDF_PATH.'patterns/dictionary.txt')) {
			$this->SHYdictionary = file(_MPDF_PATH.'patterns/dictionary.txt',FILE_SKIP_EMPTY_LINES);
			foreach($this->SHYdictionary as $entry) {
				$entry = trim($entry);
				$poss = array();
				$offset = 0;
				$p = true;
				$wl = mb_strlen($entry ,'UTF-8');
				while($offset < $wl) {
					$p = mb_strpos($entry, '/', $offset, 'UTF-8');
					if ($p !== false) { $poss[] = $p - count($poss); }
					else { break; }
					$offset = $p+1;
				}
				if (count($poss)) { $this->SHYdictionaryWords[str_replace('/', '', mb_strtolower($entry))] = $poss; }
			}
		}
		$this->loadedSHYdictionary = true;
	}

	if (!in_array($this->SHYlang,$this->SHYlanguages)) { return array(false,'','',''); }
	// If no pattern loaded or not the best one
	if (count($this->SHYpatterns) < 1  || ($this->loadedSHYpatterns && $this->loadedSHYpatterns != $this->SHYlang)) {
		include(_MPDF_PATH."patterns/" . $this->SHYlang . ".php"); 
		$patterns = preg_split('/[ ]/u', $patterns);
		$new_patterns = array();
		for($i = 0; $i < count($patterns); $i++) {
			$value = $patterns[$i];
			$new_patterns[preg_replace('/[0-9]/u', '', $value)] = $value;
		}
		$this->SHYpatterns = $new_patterns;
		$this->loadedSHYpatterns = $this->SHYlang;
	}

	if (!$this->is_MB) { $word = mb_convert_encoding($word,'UTF-8',$this->mb_encoding); }

	$prepre = '';
	$postpost = '';
	$startpunctuation = "\xc2\xab\xc2\xbf\xe2\x80\x98\xe2\x80\x9b\xe2\x80\x9c\xe2\x80\x9f";
	$endpunctuation = "\xe2\x80\x9e\xe2\x80\x9d\xe2\x80\x9a\xe2\x80\x99\xc2\xbb";

	if (preg_match('/^(["\''.$startpunctuation .'])+(.{'.$this->SHYcharmin.',})$/u',$word,$m)) {
		$prepre = $m[1];
		$word = $m[2];
	}
	if (preg_match('/^(.{'.$this->SHYcharmin.',})([\'\.,;:!?"'.$endpunctuation .']+)$/u',$word,$m)) {
		$word = $m[1];
		$postpost = $m[2];
	}
	if(mb_strlen($word,'UTF-8') < $this->SHYcharmin) {
			return array(false,'','','');
	}
	$success = false;

	if(isset($this->SHYdictionaryWords[mb_strtolower($word)])) {
	   foreach($this->SHYdictionaryWords[mb_strtolower($word)] AS $i) {
			$a = $prepre . mb_substr($word,0,$i,'UTF-8');
			if (!$this->is_MB) { $testa = mb_convert_encoding($a,$this->mb_encoding,'UTF-8'); }
			else { $testa = $a; }
			if ($this->GetStringWidth($testa.'-') > $maxWidth) { break ; }
			$pre = $a;
			$post = mb_substr($word,$i+1,mb_strlen($word,'UTF-8'),'UTF-8') . $postpost;
			$success = true;
	   }
	}

	if (!$success) {
	   $text_word = '_' . $word . '_';
	   $word_length = mb_strlen($text_word,'UTF-8');

	   $single_character = preg_split('//u', $text_word);

	   $text_word = mb_strtolower($text_word,'UTF-8');
	   $hyphenated_word = array();
	   $numb3rs = array('0' => true, '1' => true, '2' => true, '3' => true, '4' => true, '5' => true, '6' => true, '7' => true, '8' => true, '9' => true);
	   for($position = 0; $position <= ($word_length - $this->SHYcharmin); $position++) {
		$maxwins = min(($word_length - $position), $this->SHYcharmax);
		for($win = $this->SHYcharmin; $win <= $maxwins; $win++) {
			if(isset($this->SHYpatterns[mb_substr($text_word, $position, $win,'UTF-8')])) {
				$pattern = $this->SHYpatterns[mb_substr($text_word, $position, $win,'UTF-8')];
				$digits = 1;
				$pattern_length = mb_strlen($pattern,'UTF-8');
							
				for($i = 0; $i < $pattern_length; $i++) {
					$char = $pattern[$i];
					if(isset($numb3rs[$char])) {
						$zero = ($i == 0) ? $position - 1 : $position + $i - $digits;
						if(!isset($hyphenated_word[$zero]) || $hyphenated_word[$zero] != $char) $hyphenated_word[$zero] = $char;
						$digits++;				
					}
				}
			}
		}
	   }

	   for($i = $this->SHYleftmin; $i <= (mb_strlen($word,'UTF-8') - $this->SHYrightmin); $i++) {
		if(isset($hyphenated_word[$i]) && $hyphenated_word[$i] % 2 != 0) {
			$a = $prepre . mb_substr($word,0,$i,'UTF-8');
			if (!$this->is_MB) { $testa = mb_convert_encoding($a,$this->mb_encoding,'UTF-8'); }
			else { $testa = $a; }
			if ($this->GetStringWidth($testa.'-') > $maxWidth + 0.0001) { break ; }
			$pre = $a;
			$post = mb_substr($word,$i+1,mb_strlen($word,'UTF-8'),'UTF-8') . $postpost;
			$success = true;
		}
	   }
	}
	if (!$this->is_MB) { 
		$pre = mb_convert_encoding($pre,$this->mb_encoding,'UTF-8'); 
		$post = mb_convert_encoding($post,$this->mb_encoding,'UTF-8'); 
		$prelength = strlen($pre);
	}
	else {
		$prelength = mb_strlen($pre);
	}
	return array($success,$pre,$post,$prelength);

}




///////////////////
/// HTML parser ///
///////////////////
function WriteHTML($html,$sub=0,$init=true,$close=true) {
				// $sub ADDED - 0 = default; 1=headerCSS only; 2=HTML body (parts) only; 3 - HTML parses only
				// 4 - writes HTML headers
				// mPDF 2.1 Added 
				// $close Leaves buffers etc. in current state, so that it can continue a block etc.
				// $init - Clears and sets buffers to Top level block etc.
	if ($init) $this->headerbuffer='';
	if ($init) $this->textbuffer = array();

	// mPDF 2.0 moved outside if (allow charset conversion)
	if ($sub == 1) { $html = '<style> '.$html.' </style>'; }	// stylesheet only

	if ($this->allow_charset_conversion) {
		if ($sub < 1) { 	// Edited mPDF 2.0 was sub < 2 ? not needed for CSS
			$this->ReadCharset($html); 
		}
		if ($this->charset_in) { 
			$success = iconv($this->charset_in,'UTF-8//TRANSLIT',$html); 
			if ($success) { $html = $success; }
		}
	}

	$html = $this->purify_utf8($html,false);
	if ($init) {
		$this->blklvl = 0;
		$this->lastblocklevelchange = 0;
		$this->blk = array();
		$this->blk[0]['width'] =& $this->pgwidth;
		$this->blk[0]['inner_width'] =& $this->pgwidth;
		// mPDF 3.0
		$this->blk[0]['blockContext'] = $this->blockContext;
	}

	if ($sub < 2) { 
		$this->ReadMetaTags($html); 

		// mPDF 2.0 - allows $this->useDefaultCSS2 to change defaultCSS values - (needs to update default font/font-size)
		//if ($this->useDefaultCSS2) { $this->defaultCSS = array_merge_recursive_unique($this->defaultCSS,$this->defaultCSS2); }
		// mPDF 2.2
		// default stylesheet now in mPDF.css - read on initialising class

		$html = $this->ReadCSS($html); 
		// SET Blocklevel[0] CSS if defined in <body> or from default
		$properties = $this->MergeCSS('BLOCK','BODY','');

		if ($sub == 1) { $this->setCSS($properties,'','BODY'); return ''; }

		// mPDF 2.3
		if ($this->useLang && $this->is_MB && preg_match('/<html [^>]*lang=[\'\"](.*?)[\'\"]/ism',$html,$m)) { 
			$html_lang = $m[1]; 
		}

		// Edited mPDF 2.0 to allow in-line CSS for body tag to be parsed // Get <body> tag inline CSS
		if (preg_match('/<body([^>]*)>(.*?)<\/body>/ism',$html,$m) || preg_match('/<body([^>]*)>(.*)$/ism',$html,$m)) { 
			$html = $m[2]; 
			// mPDF 3.0 - Tiling Patterns
			// Changed to allow style="background: url('bg.jpg')"
			if (preg_match('/style=[\"](.*?)[\"]/ism',$m[1],$mm) || preg_match('/style=[\'](.*?)[\']/ism',$m[1],$mm)) { 
				$zproperties = $this->readInlineCSS($mm[1]); 
				$properties = array_merge_recursive_unique($properties,$zproperties); 
			}
			// mPDF 2.3
			if ($html_lang) { $properties['LANG'] = $html_lang; }
			if ($this->useLang && $this->is_MB && preg_match('/lang=[\'\"](.*?)[\'\"]/ism',$m[1],$mm)) { 
				$properties['LANG'] = $mm[1]; 
			}
		}

		$this->setCSS($properties,'','BODY'); 
		$this->CSS['BODY'] = array_merge_recursive_unique($this->CSS['BODY'], $properties); 
		// mPDF 3.0
		if ($properties['BACKGROUND-GRADIENT']) { 
			$this->bodyBackgroundGradient = $properties['BACKGROUND-GRADIENT'];
		}
		// mPDF 3.0 - Tiling Patterns
		if ($properties['BACKGROUND-IMAGE']) { 
			$file = $properties['BACKGROUND-IMAGE'];
			$sizesarray = $this->Image($file,0,0,0,0,'','',false);
			if (isset($sizesarray['IMAGE_ID'])) {
				$image_id = $sizesarray['IMAGE_ID'];
				$orig_w = $sizesarray['WIDTH']*$this->k;		// in user units i.e. mm
 				$orig_h = $sizesarray['HEIGHT']*$this->k;		// (using $this->img_dpi)
				$x_repeat = true;
				$y_repeat = true;
				if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-x') { $y_repeat = false; }
				if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-y') { $x_repeat = false; }
				$x_pos = 0;
				$y_pos = 0;
				if ($properties['BACKGROUND-POSITION']) { 
					$ppos = preg_split('/\s+/',$properties['BACKGROUND-POSITION']);
					$x_pos = $ppos[0];
					$y_pos = $ppos[1];
					if (!stristr($x_pos ,'%') ) { $x_pos = ConvertSize($x_pos ,$this->pgwidth,$this->FontSize); }
					if (!stristr($y_pos ,'%') ) { $y_pos = ConvertSize($y_pos ,$this->pgwidth,$this->FontSize); }
				}
				$this->bodyBackgroundImage = array('image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat);
			}
		}
		// mPDF 2.0 Paged media (page-box)
		$pm = $this->GetPagedMediaCSS('');
		$this->page_box['changed'] = false;

		$this->page_box['last_name'] = $this->page_box['name'] = '';
		// If page-box is set
		if ($this->state==0 && $pm['ISSET']) {
			$this->page_box['CSS'] = $pm;
			// mPDF 3.0
			list($pborientation,$pbmgl,$pbmgr,$pbmgt,$pbmgb,$pbmgh,$pbmgf,$pbmgtfp,$pbmgbfp,$ohname,$ehname,$ofname,$efname,$bg) = $this->SetPagedMediaCSS();
			$this->CurOrientation = $this->DefOrientation = $pborientation; 
			$this->DeflMargin = $pbmgl; 
			$this->DefrMargin = $pbmgr; 
			$this->tMargin = $pbmgt;
			$this->bMargin = $pbmgb;
			$this->margin_header = $pbmgh; 
			$this->margin_footer = $pbmgf; 

			if ($ohname && !preg_match('/^html_(.*)$/i',$ohname)) $this->firstPageBoxHeader = $ohname;
			if ($ehname && !preg_match('/^html_(.*)$/i',$ehname)) $this->firstPageBoxHeaderEven = $ehname;
			if ($ofname && !preg_match('/^html_(.*)$/i',$ofname)) $this->firstPageBoxFooter = $ofname;
			if ($efname && !preg_match('/^html_(.*)$/i',$efname)) $this->firstPageBoxFooterEven = $efname;

			$this->SetMargins($this->DeflMargin,$this->DefrMargin,$this->tMargin);	// sets l r t margin
			$this->SetAutoPageBreak(true,$this->bMargin);	// sets $this->bMargin & PageBreakTrigger
			$this->pgwidth = $this->w - $this->lMargin - $this->rMargin ;
			$this->x = $this->lMargin;
			$this->y = $this->tMargin;

			// mPDF 3.0 - Background color
			if ($bg['BACKGROUND-COLOR']) {
				$cor = ConvertColor($bg['BACKGROUND-COLOR']);
				if ($cor) { 
					$this->bodyBackgroundColor = $cor; 
					$this->bodyBackgroundImage = false; 
					$this->bodyBackgroundGradient = false; 
				}
			}
			// mPDF 3.0
			if ($bg['BACKGROUND-GRADIENT']) { 
				$this->bodyBackgroundGradient = $bg['BACKGROUND-GRADIENT'];
			}
			// mPDF 3.0 - Tiling Patterns
			if ($bg['BACKGROUND-IMAGE']) { 
				$file = $bg['BACKGROUND-IMAGE'];
				$sizesarray = $this->Image($file,0,0,0,0,'','',false);
				if (isset($sizesarray['IMAGE_ID'])) {
					$image_id = $sizesarray['IMAGE_ID'];
					$orig_w = $sizesarray['WIDTH']*$this->k;		// in user units i.e. mm
 					$orig_h = $sizesarray['HEIGHT']*$this->k;		// (using $this->img_dpi)
					$x_repeat = true;
					$y_repeat = true;
					if ($bg['BACKGROUND-REPEAT']=='no-repeat' || $bg['BACKGROUND-REPEAT']=='repeat-x') { $y_repeat = false; }	
					if ($bg['BACKGROUND-REPEAT']=='no-repeat' || $bg['BACKGROUND-REPEAT']=='repeat-y') { $x_repeat = false; }
					$x_pos = 0;
					$y_pos = 0;
					if ($bg['BACKGROUND-POSITION']) { 
						$ppos = preg_split('/\s+/',$bg['BACKGROUND-POSITION']);
						$x_pos = $ppos[0];
						$y_pos = $ppos[1];
						if (!stristr($x_pos ,'%') ) { $x_pos = ConvertSize($x_pos ,$this->pgwidth,$this->FontSize); }
						if (!stristr($y_pos ,'%') ) { $y_pos = ConvertSize($y_pos ,$this->pgwidth,$this->FontSize); }
					}
					$this->bodyBackgroundImage = array('image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat);
					$this->bodyBackgroundGradient = false; 
				}
			}


			$this->page_box['changed'] = true;
		}

	}

	// mPDF 2.0 Moved from the top of this function to come after the PagedMedia i.e. first new page not added until @page set
	// mPDF 1.2 added $sub = 4 used to buffer output for HTML Headers and Footers
	if($this->state==0 && $sub!=1 && $sub!=3 && $sub!=4) {
		$this->AddPage($this->CurOrientation);
	}


	// Edited mPDF 1.2 HTML headers and Footers
	$this->parseonly = false; 
	$this->bufferoutput = false; 
	if ($sub == 3) { 
		$this->parseonly = true; 
		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
		// Output any text left in buffer
		if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); }
		$this->textbuffer=array();
	} 
	else if ($sub == 4) { 
		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
		// Output any text left in buffer
		if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); }
		$this->bufferoutput = true; 
		$this->textbuffer=array();
		$this->headerbuffer='';
		$properties = $this->MergeCSS('BLOCK','BODY','');
		$this->setCSS($properties,'','BODY'); 
	} 

	mb_internal_encoding('UTF-8'); 

	$html = AdjustHTML($html,$this->directionality,$this->usepre, $this->tabSpaces); //Try to make HTML look more like XHTML

	// mPDF 2.3
	if ($this->autoFontGroups) { $html = $this->AutoFont($html); }
	// mPDF 2.0 Added to set HTML page headers/footers
	preg_match_all('/<htmlpageheader([^>]*)>(.*?)<\/htmlpageheader>/si',$html,$h);
	for($i=0;$i<count($h[1]);$i++) {
		if (preg_match('/name=[\'|\"](.*?)[\'|\"]/',$h[1][$i],$n)) {
			$this->pageHTMLheaders[$n[1]] = $h[2][$i]; 
		}
	}
	preg_match_all('/<htmlpagefooter([^>]*)>(.*?)<\/htmlpagefooter>/si',$html,$f);
	for($i=0;$i<count($f[1]);$i++) {
		if (preg_match('/name=[\'|\"](.*?)[\'|\"]/',$f[1][$i],$n)) {
			$this->pageHTMLfooters[$n[1]] = $f[2][$i]; 
		}
	}
	$html = preg_replace('/<htmlpageheader.*?<\/htmlpageheader>/si','',$html);
	$html = preg_replace('/<htmlpagefooter.*?<\/htmlpagefooter>/si','',$html);

	//if (SetHTMLHeadersrequired) 
	if (preg_match('/^html_(.*)$/i',$ohname,$n)) $this->SetHTMLHeader($this->pageHTMLheaders[$n[1]],'O',true);
	if (preg_match('/^html_(.*)$/i',$ehname,$n)) $this->SetHTMLHeader($this->pageHTMLheaders[$n[1]],'E');
	if (preg_match('/^html_(.*)$/i',$ofname,$n)) $this->SetHTMLFooter($this->pageHTMLfooters[$n[1]],'O');
	if (preg_match('/^html_(.*)$/i',$efname,$n)) $this->SetHTMLFooter($this->pageHTMLfooters[$n[1]],'E');

	$html=str_replace('<?','< ',$html); //Fix '<?XML' bug from HTML code generated by MS Word
	$html = $this->SubstituteChars($html);

	// Don't allow non-breaking spaces that are converted to substituted chars or will break anyway and mess up table width calc.
	$html = str_replace('<tta>160</tta>',$this->chrs[32],$html); 
	$html = str_replace('</tta><tta>','|',$html); 
	$html = str_replace('</tts><tts>','|',$html); 
	$html = str_replace('</ttz><ttz>','|',$html); 

	//Add new supported tags in the DisableTags function
	$html=strip_tags($html,$this->enabledtags); //remove all unsupported tags, but the ones inside the 'enabledtags' string

	//Explode the string in order to parse the HTML code
	// mPDF 2.1 Unnecessarily using /u modifier slowed it down
	$a=preg_split('/<(.*?)>/ms',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	// ? more accurate regexp that allows e.g. <a name="Silly <name>">
	// if changing - also change in fn.SubstituteChars()
	// $a = preg_split ('/<((?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+)>/ms', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

	if ($this->mb_encoding) { 
		mb_internal_encoding($this->mb_encoding); 
	}

	foreach($a as $i => $e) {

		if($i%2==0) {
		//TEXT
			if (strlen($e) == 0) { continue; }

			// mPDF 2.1 DISPLAY NONE
			if ($this->blk[$this->blklvl]['hide']) { continue; }

			$e = strcode2utf($e);	
			$e = lesser_entity_decode($e);
			// mPDF 2.3 // Change Arabic + Persian. to Presentation Forms
   			if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->biDirectional)))  { 
	   			if ($this->rtlAsArabicFarsi || !preg_match("/[".$this->pregNonARABICchars ."]/u", $e) ) {
					$e = preg_replace("/([\x{0600}-\x{06FF}\x{0750}-\x{077F}]+)/ue", '$this->ArabJoin(stripslashes(\'\\1\'))', $e);
	   			}
			}

			// CONVERT CODEPAGE
			if (!$this->is_MB) { $e = mb_convert_encoding($e,$this->mb_encoding,'UTF-8'); }
			if (($this->is_MB && !$this->isCJK) && (!$this->usingCoreFont)) {
				if ($this->toupper) { $e = mb_strtoupper($e,$this->mb_encoding); }
				if ($this->tolower) { $e = mb_strtolower($e,$this->mb_encoding); }
			}
			else if (!$this->isCJK) {
				if ($this->toupper) { $e = strtoupper($e); }
				if ($this->tolower) { $e = strtolower($e); }
			}
			if (($this->tts) || ($this->ttz) || ($this->tta)) {
				$es = explode('|',$e);
				$e = '';
				foreach($es AS $val) {
					$e .= $this->chrs[$val];
				}
			}
			//Adjust lineheight
      		//$this->SetLineHeight($this->FontSizePt); //should be inside printbuffer? // does nothing

			//  FORM ELEMENTS
  			if ($this->specialcontent) {
			   //SELECT tag (form element)
			   if ($this->specialcontent == "type=select") { 
				$e = ltrim($e); 
				$stringwidth = $this->GetStringWidth($e);
				if (!isset($this->selectoption['MAXWIDTH']) or $stringwidth > $this->selectoption['MAXWIDTH']) { $this->selectoption['MAXWIDTH'] = $stringwidth; }
				if (!isset($this->selectoption['SELECTED']) or $this->selectoption['SELECTED'] == '') { $this->selectoption['SELECTED'] = $e; }
				// mPDD 1.4 Active Forms
				if ($this->selectoption['ACTIVE']) {
					$this->selectoption['ITEMS'][]=array('exportValue'=>$this->selectoption['currentVAL'], 'content'=>$e, 'selected'=>$this->selectoption['currentSEL']);
				}
			   }
			   // TEXTAREA
			   else { 
				$objattr = unserialize($this->specialcontent);
				$objattr['text'] = $e;
				$te = "\xbb\xa4\xactype=textarea,objattr=".serialize($objattr)."\xbb\xa4\xac";
				if ($this->tdbegin) {
	  				$this->cell[$this->row][$this->col]['textbuffer'][] = array($te,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
				}
				else {
					$this->textbuffer[] = array($te,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
				}
			   }
		      }
			// TABLE
			else if ($this->tableLevel) {
				// mPDF 2.2
				if ($this->tdbegin) {
     				   if (($this->ignorefollowingspaces) and !$this->ispre) { $e = ltrim($e); }
				   // Edited mPDF 2.0 to allow to show '0'
				   if ($e || $e==='0') {
					// mPDF 3.0
				      if (($this->blockjustfinished || $this->listjustfinished) && $this->cell[$this->row][$this->col]['s']>0) {
	  					$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
  						$this->cell[$this->row][$this->col]['text'][] = "\n";
						if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
							$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
						}
						elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
							$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];  
						}
						$this->cell[$this->row][$this->col]['s'] = 0;// reset
				      }
					// mPDF 3.0
					$this->blockjustfinished=false;
					$this->listjustfinished=false;

	  				$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
  					$this->cell[$this->row][$this->col]['text'][] = $e;
					// Edited mPDF 1.3 for rotated text in cell
            			if (!$this->cell[$this->row][$this->col]['R']) {
						$this->cell[$this->row][$this->col]['s'] += $this->GetStringWidth($e);
					}
					// mPDF 2.4 JPGRAPH
					if ($this->tableLevel==1 && $this->useGraphs) { 
						$this->graphs[$this->currentGraphId]['data'][$this->row][$this->col] = $e;
					}
					// mPDF 2.2
					$this->nestedtablejustfinished = false;
					// mPDF 3.0
					$this->linebreakjustfinished=false;
				   }
				}
			}
			// ALL ELSE
			else {
     				if ($this->ignorefollowingspaces and !$this->ispre) { $e = ltrim($e); }
				// Edited mPDF 2.0 to allow to show '0'
				if ($e || $e==='0') $this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
			}
		}


		else { // TAG **
		   if(substr($e,0,1)=='/') { // END TAG
		    // Check for tags where HTML specifies optional end tags,
    		    // and/or does not allow nesting e.g. P inside P, or 
		    $endtag = strtoupper(substr($e,1));
		    // mPDF 2.1 DISPLAY NONE
		    if($this->blk[$this->blklvl]['hide']) { 
			if (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags)) { 
				unset($this->blk[$this->blklvl]);
				$this->blklvl--; 
			}
			continue; 
		    }

		    if ($this->allow_html_optional_endtags && !$this->parseonly) {
			if (($endtag == 'DIV' || $endtag =='FORM' || $endtag =='CENTER') && $this->lastoptionaltag == 'P') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'LI' && $endtag == 'OL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'LI' && $endtag == 'UL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'DD' && $endtag == 'DL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'DT' && $endtag == 'DL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'OPTION' && $endtag == 'SELECT') { $this->CloseTag($this->lastoptionaltag ); }
			if ($endtag == 'TABLE') {
				if ($this->lastoptionaltag == 'THEAD' || $this->lastoptionaltag == 'TBODY' || $this->lastoptionaltag == 'TFOOT') { 
					$this->CloseTag($this->lastoptionaltag);
				}
				if ($this->lastoptionaltag == 'TR') { $this->CloseTag('TR'); }
				if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); $this->CloseTag('TR'); }
			}
			if ($endtag == 'THEAD' || $endtag == 'TBODY' || $endtag == 'TFOOT') { 
				if ($this->lastoptionaltag == 'TR') { $this->CloseTag('TR'); }
				if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); $this->CloseTag('TR'); }
			}
			if ($endtag == 'TR') {
				if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); }
			}
		    }
		    $this->CloseTag($endtag); 
		   }

		   else {	// OPENING TAG
			// mPDF 2.1 DISPLAY NONE
			if($this->blk[$this->blklvl]['hide']) { 
				if (strpos($e,' ')) { $te = strtoupper(substr($e,0,strpos($e,' '))); }
				else { $te = strtoupper($e); } 
				if (in_array($te, $this->outerblocktags) || in_array($te, $this->innerblocktags)) { 
					$this->blklvl++; 	
					$this->blk[$this->blklvl]['hide']=true;
				}
				continue; 
			}

			$regexp = '|=\'(.*?)\'|s'; // eliminate single quotes, if any
      		$e = preg_replace($regexp,"=\"\$1\"",$e);
			// changes anykey=anyvalue to anykey="anyvalue" (only do this inside tags)
			// mPDF 2.0 Don't do this to HTMLHeaders which have been htmlspecialchars()
			if (substr($e,0,10)!='pageheader' && substr($e,0,10)!='pagefooter') {
				$regexp = '| (\\w+?)=([^\\s>"]+)|si'; 
	      		$e = preg_replace($regexp," \$1=\"\$2\"",$e);
			}

      		//Fix path values, if needed
			if ((stristr($e,"href=") !== false) or (stristr($e,"src=") !== false) ) {
				$regexp = '/ (href|src)="(.*?)"/i';
				preg_match($regexp,$e,$auxiliararray);
				$path = $auxiliararray[2];
				$path = str_replace("\\","/",$path); //If on Windows
				//Get link info and obtain its absolute path
				$regexp = '|^./|';
				$path = preg_replace($regexp,'',$path);
				if(substr($path,0,1) != '#') { //It is not an Internal Link
				  if (strpos($path,"../") !== false ) { //It is a Relative Link
					$backtrackamount = substr_count($path,"../");
					$maxbacktrack = substr_count($this->basepath,"/") - 1;
					$filepath = str_replace("../",'',$path);
					$path = $this->basepath;
					//If it is an invalid relative link, then make it go to directory root
					if ($backtrackamount > $maxbacktrack) $backtrackamount = $maxbacktrack;
					//Backtrack some directories
					for( $i = 0 ; $i < $backtrackamount + 1 ; $i++ ) $path = substr( $path, 0 , strrpos($path,"/") );
					$path = $path . "/" . $filepath; //Make it an absolute path
				  }
				  elseif( strpos($path,":/") === false || strpos($path,":/") > 10) //It is a Local Link
				  {
					if (substr($path,0,1) == "/") { 
						$tr = parse_url($this->basepath);
						$root = $tr['scheme'].'://'.$tr['host'];
						$path = $root . $path; 
					}
					// mPDF 3.0
					else if (stristr($path,"mailto:") === false) { $path = $this->basepath . $path; }
				  }
				  //Do nothing if it is an Absolute Link
				}
				$regexp = '/ (href|src)="(.*?)"/i';
				$e = preg_replace($regexp,' \\1="'.$path.'"',$e);
			}//END of Fix path values


			//Extract attributes
			$contents=array();
			// mPDF 3.0 - Tiling Patterns
			// Changed to allow style="background: url('bg.jpg')"
			preg_match_all('/\\S*=["][^"]*["]/',$e,$contents1);
			preg_match_all('/\\S*=[\'][^\']*[\']/',$e,$contents2);
			$contents = array_merge($contents1, $contents2);
			preg_match('/\\S+/',$e,$a2);
			$tag=strtoupper($a2[0]);
			$attr=array();
			if (!empty($contents)) {
				foreach($contents[0] as $v) {
					// mPDF 3.0 - Tiling Patterns
					// Changed to allow style="background: url('bg.jpg')"
 					if(preg_match('/^([^=]*)=["]?([^"]*)["]?$/',$v,$a3) || preg_match('/^([^=]*)=[\']?([^\']*)[\']?$/',$v,$a3)) {
						// mPDF 2.0 <div class="class1 class2"> cf. MergeCSS
 						if (strtoupper($a3[1])=='ID' || strtoupper($a3[1])=='STYLE' || strtoupper($a3[1])=='CLASS') {
   							$attr[strtoupper($a3[1])]=trim(strtoupper($a3[2]));
						}
						// includes header-style-right etc. used for <pageheader>
 						else if (preg_match('/^(HEADER|FOOTER)-STYLE/i',$a3[1])) {
   							$attr[strtoupper($a3[1])]=trim(strtoupper($a3[2]));
						}
						else {
    							$attr[strtoupper($a3[1])]=trim($a3[2]);
						}
     					}
  				}
			}
			$this->OpenTag($tag,$attr);
		   }

		} // end TAG
	} //end of	foreach($a as $i=>$e)

	if ($close) {

		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }

		// Output any text left in buffer
		if (count($this->textbuffer) && !$this->parseonly) { $this->printbuffer($this->textbuffer); }
		if (!$this->parseonly) $this->textbuffer=array();

		// Added mPDF 3.0 Float DIV
		// If ended with a float, need to move to end page
		$currpos = $this->page*1000 + $this->y;
		if ($this->blk[$this->blklvl]['float_endpos'] && $this->blk[$this->blklvl]['float_endpos'] > $currpos) {
			$old_page = $this->page;
			$new_page = intval($this->blk[$this->blklvl]['float_endpos'] /1000);
			if ($old_page != $new_page) {
				$s = $this->PrintPageBackgrounds();
				// Writes after the marker so not overwritten later by page background etc.
				$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
				$this->pageBackgrounds = array();
				$this->page = $new_page;
				$this->ResetMargins();
				$this->Reset();
				$this->pageoutput[$this->page] = array();
			}
			$this->y = (($this->blk[$this->blklvl]['float_endpos'] *1000) % 1000000)/1000;	// mod changes operands to integers before processing
		}

		// mPDF 2.4 Float Images
		if (count($this->floatbuffer)) {
			$this->objectbuffer = $this->floatbuffer;
			$this->printobjectbuffer(false);
			$this->objectbuffer = array();
			$this->floatbuffer = array();
			$this->floatmargins = array();
		}

		//Create Internal Links, if needed
		if (!empty($this->internallink) ) {
			foreach($this->internallink as $k=>$v) {
				if (strpos($k,"#") !== false ) { continue; } //ignore
				$ypos = $v['Y'];
				$pagenum = $v['PAGE'];
				$sharp = "#";
				while (array_key_exists($sharp.$k,$this->internallink)) {
					$internallink = $this->internallink[$sharp.$k];
					$this->SetLink($internallink,$ypos,$pagenum);
					$sharp .= "#";
				}
			}
		}

		$this->linemaxfontsize = '';
		$this->lineheight_correction = $this->default_lineheight_correction;

		// mPDF 2.0 Added in case writeHTML() is called from within the program i.e. PAGE-BREAK-BEFORE -> AddPage -> ResetHtmlHeaders
		$this->bufferoutput = false; 

	}
}



// NEW FUNCTION FOR BORDER-DETAILS
function border_details($bd) {
	$prop = explode(' ',trim($bd));
	if ( count($prop) == 1 ) { 
		$bsize = ConvertSize($prop[0],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		if ($bsize > 0) {
			return array('s' => 1, 'w' => $bsize, 'c' => array('R'=>0,'G'=>0,'B'=>0), 'style'=>'solid');
		}
		else { return array(); }
	}
	if ( count($prop) != 3 ) { return array(); } 
	// Change #000000 1px solid to 1px solid #000000 (proper)
	if (substr($prop[0],0,1) == '#') { $tmp = $prop[0]; $prop[0] = $prop[1]; $prop[1] = $prop[2]; $prop[2] = $tmp; }
	// Added mPDF 2.0
	// Change solid #000000 1px to 1px solid #000000 (proper)
	else if (substr($prop[0],1,1) == '#') { $tmp = $prop[1]; $prop[0] = $prop[2]; $prop[1] = $prop[0]; $prop[2] = $tmp; }
	// Change solid 1px #000000 to 1px solid #000000 (proper)
	else if (in_array($prop[0],$this->tblborderstyles) || $prop[0] == 'none' || $prop[0] == 'hidden' ) { 
		$tmp = $prop[0]; $prop[0] = $prop[1]; $prop[1] = $tmp; 
	}
	// Size
	$bsize = ConvertSize($prop[0]);
	//color
	$coul = ConvertColor($prop[2]);	// returns array
	// Style
	$prop[1] = strtolower($prop[1]);
	if (in_array($prop[1],$this->tblborderstyles) && $bsize > 0) { $on = 1; } 
	else if ($prop[1] == 'hidden') { $on = 1; $bsize = 0; $coul = ''; } 
	else if ($prop[1] == 'none') { $on = 0; $bsize = 0; $coul = ''; } 
	else { $on = 0; $bsize = 0; $coul = ''; $prop[1] = ''; }
	return array('s' => $on, 'w' => $bsize, 'c' => $coul, 'style'=> $prop[1] );
}


// NEW FUNCTION FOR CSS MARGIN or PADDING called from SetCSS
function fixCSS($prop) {
	if (!is_array($prop) || (count($prop)==0)) return array(); 
	$newprop = array(); 
	foreach($prop AS $k => $v) {
		// mPDF 2.3 Font-family
		if ($k == 'FONT-FAMILY') {
			$aux_fontlist = explode(",",$v);
			$fonttype = $aux_fontlist[0];
			$aux_fontlist = explode(" ",$fonttype);
			$fonttype = $aux_fontlist[0];
			$v = strtolower(trim($fonttype));
			if (($this->is_MB && in_array($v,$this->available_unifonts)) || 
				(!$this->is_MB && in_array($v,$this->available_fonts)) || in_array($v, array('sjis','uhc','big5','gb')) || 
				in_array($v,$this->sans_fonts) || in_array($v,$this->serif_fonts) || in_array($v,$this->mono_fonts) ) { 
				$newprop[$k] = $v; 
			}
		}
		else if ($k == 'MARGIN') {
			$tmp =  $this->margin_padding_expand($v);
			$newprop['MARGIN-TOP'] = $tmp['T'];
			$newprop['MARGIN-RIGHT'] = $tmp['R'];
			$newprop['MARGIN-BOTTOM'] = $tmp['B'];
			$newprop['MARGIN-LEFT'] = $tmp['L'];
		}
		// mPDF 3.0
		else if ($k == 'BORDER-RADIUS' || $k == 'BORDER-TOP-LEFT-RADIUS' || $k == 'BORDER-TOP-RIGHT-RADIUS' || $k == 'BORDER-BOTTOM-LEFT-RADIUS' || $k == 'BORDER-BOTTOM-RIGHT-RADIUS') {
			$tmp =  $this->border_radius_expand($v,$k);
			if (isset($tmp['TL-H'])) $newprop['BORDER-TOP-LEFT-RADIUS-H'] = $tmp['TL-H'];
			if (isset($tmp['TL-V'])) $newprop['BORDER-TOP-LEFT-RADIUS-V'] = $tmp['TL-V'];
			if (isset($tmp['TR-H'])) $newprop['BORDER-TOP-RIGHT-RADIUS-H'] = $tmp['TR-H'];
			if (isset($tmp['TR-V'])) $newprop['BORDER-TOP-RIGHT-RADIUS-V'] = $tmp['TR-V'];
			if (isset($tmp['BL-H'])) $newprop['BORDER-BOTTOM-LEFT-RADIUS-H'] = $tmp['BL-H'];
			if (isset($tmp['BL-V'])) $newprop['BORDER-BOTTOM-LEFT-RADIUS-V'] = $tmp['BL-V'];
			if (isset($tmp['BR-H'])) $newprop['BORDER-BOTTOM-RIGHT-RADIUS-H'] = $tmp['BR-H'];
			if (isset($tmp['BR-V'])) $newprop['BORDER-BOTTOM-RIGHT-RADIUS-V'] = $tmp['BR-V'];
		}
		else if ($k == 'PADDING') {
			$tmp =  $this->margin_padding_expand($v);
			$newprop['PADDING-TOP'] = $tmp['T'];
			$newprop['PADDING-RIGHT'] = $tmp['R'];
			$newprop['PADDING-BOTTOM'] = $tmp['B'];
			$newprop['PADDING-LEFT'] = $tmp['L'];
		}
		else if ($k == 'BORDER') {
			if ($v == '1') { $v = '1px solid #000000'; }
			// Added mPDF 1.3
			// Changed mPDF 2.0
			if (preg_match('/ none /i',$v)) { $v = '0px none #000000'; }
			$newprop['BORDER-TOP'] = $v;
			$newprop['BORDER-RIGHT'] = $v;
			$newprop['BORDER-BOTTOM'] = $v;
			$newprop['BORDER-LEFT'] = $v;
		}
		else if ($k == 'BORDER-SPACING') {
			$prop = explode(' ',trim($v));
			// Added mPDF 2.0
			if (count($prop) == 1 ) { 
				$newprop['BORDER-SPACING-H'] = $prop[0];
				$newprop['BORDER-SPACING-V'] = $prop[0];
			}
			else if (count($prop) == 2 ) { 
				$newprop['BORDER-SPACING-H'] = $prop[0];
				$newprop['BORDER-SPACING-V'] = $prop[1];
			}
		}
		else if ($k == 'SIZE') {	// mPDF 2.0 Added for paged media
			$prop = explode(' ',trim($v));
			if (preg_match('/(auto|portrait|landscape)/',$prop[0])) {
				$newprop['SIZE'] = strtoupper($prop[0]);
			}
			else if (count($prop) == 1 ) {
				$newprop['SIZE']['W'] = ConvertSize($prop[0]);
				$newprop['SIZE']['H'] = ConvertSize($prop[0]);
			}
			else if (count($prop) == 2 ) {
				$newprop['SIZE']['W'] = ConvertSize($prop[0]);
				$newprop['SIZE']['H'] = ConvertSize($prop[1]);
			}
		}
		// mPDF 3.0 - Tiling Patterns
		else if ($k == 'BACKGROUND-IMAGE') {
			$v = strtolower($v);
			if (preg_match('/url\([\'\"]{0,1}(.*?)[\'\"]{0,1}\)/',$v,$m)) { 
				$newprop['BACKGROUND-IMAGE'] = $m[1]; 
			}
		}
		// mPDF 3.0 - Tiling Patterns
		else if ($k == 'BACKGROUND') {
			$bg = $this->parseCSSbackground($v);
			if ($bg['c']) { $newprop['BACKGROUND-COLOR'] = $bg['c']; }
			if ($bg['i']) { 
				$newprop['BACKGROUND-IMAGE'] = $bg['i']; 
				if ($bg['r']) { $newprop['BACKGROUND-REPEAT'] = $bg['r']; }
				if ($bg['p']) { $newprop['BACKGROUND-POSITION'] = $bg['p']; }
			}
		}
		else { 
			$newprop[$k] = $v; 
		}
	}
	return $newprop;
}


// mPDF 3.0 - Tiling Patterns
function parseCSSbackground($s) {
	$s = strtolower($s);
	$bg = array('c'=>false, 'i'=>false, 'r'=>false, 'p'=>false, );
	if (preg_match('/url\(/',$s)) {
		// If color, set and strip it off
		if (preg_match('/^\s*(#[0-9a-fA-F]{3,6}|rgb\(.*?\)|[a-zA-Z]{3,})\s+(url\(.*)/',$s,$m)) { 
			$bg['c'] = $m[1]; 
			$s = $m[2];
		}
		if (preg_match('/url\([\'\"]{0,1}(.*?)[\'\"]{0,1}\)\s*(.*)/',$s,$m)) { 
			$bg['i'] = $m[1]; 
			$s = $m[2];
			if (preg_match('/(repeat-x|repeat-y|no-repeat|repeat)/',$s,$m)) { 
				$bg['r'] = $m[1];
			}
			// Remove repeat, attachment (discarded) and also any inherit
			$s = preg_replace('/(repeat-x|repeat-y|no-repeat|repeat|scroll|fixed|inherit)/','',$s);
			$bits = preg_split('/\s+/',trim($s));
			// These should be Position x1 or x2
			if (count($bits)==1) {
				if (preg_match('/bottom/',$bits[0])) { $bg['p'] = '50% 100%'; }
				else if (preg_match('/top/',$bits[0])) { $bg['p'] = '50% 0%'; }
				else { $bg['p'] = $bits[0] . ' 50%'; }
			}
			else if (count($bits)==2) {
				// Can be either right center or center right
				if (preg_match('/(top|bottom)/',$bits[0]) || preg_match('/(left|right)/',$bits[1])) { 
					$bg['p'] = $bits[1] . ' '.$bits[0]; 
				}
				else { 
					$bg['p'] = $bits[0] . ' '.$bits[1]; 
				}
			}
			if ($bg['p']) {
				$bg['p'] = preg_replace('/(left|top)/','0%',$bg['p']);
				$bg['p'] = preg_replace('/(right|bottom)/','100%',$bg['p']);
				$bg['p'] = preg_replace('/(center)/','50%',$bg['p']);
				if (!preg_match('/[\-]{0,1}\d+(in|cm|mm|pt|pc|em|ex|px|%)* [\-]{0,1}\d+(in|cm|mm|pt|pc|em|ex|px|%)*/',$bg['p'])) {
					$bg['p'] = false;
				}
			}
		}
	}
	else {
		if (preg_match('/^\s*(#[0-9a-fA-F]{3,6}|rgb\(.*?\)|[a-zA-Z]{3,})/',$s,$m)) { $bg['c'] = $m[1]; }
	}
	return ($bg);
}

// mPDF 3.0 Gradients
function parseBackgroundGradient($bg) {
	// background-gradient: linear #00FFFF #FFFF00 0 0.5 1 0.5;  or
	// background-gradient: radial #00FFFF #FFFF00 0.5 0.5 1 1 1.2;
	$v = trim($bg);
	$bgr = preg_split('/ /',$v);
	$g = array();
	if (count($bgr)> 6) {  
		if (strtoupper(substr($bgr[0],0,1)) == 'L' && count($bgr)==7) {  // linear
			$g['type'] = 2;
			//$coords = array(0,0,1,1 );	// 0 0 1 0 or 0 1 1 1 is L 2 R; 1,1,0,1 is R2L; 1,1,1,0 is T2B; 1,0,1,1 is B2T
			// Linear: $coords - array of the form (x1, y1, x2, y2) which defines the gradient vector (see linear_gradient_coords.jpg). 
			//    The default value is from left to right (x1=0, y1=0, x2=1, y2=0).
			$g['coords'] = array($bgr[3], $bgr[4], $bgr[5], $bgr[6]);
		}
		else if (count($bgr)==8) {	// radial
			$g['type'] = 3;
			// Radial: $coords - array of the form (fx, fy, cx, cy, r) where (fx, fy) is the starting point of the gradient with color1, 
			//    (cx, cy) is the center of the circle with color2, and r is the radius of the circle (see radial_gradient_coords.jpg). 
			//    (fx, fy) should be inside the circle, otherwise some areas will not be defined
			$g['coords'] = array($bgr[3], $bgr[4], $bgr[5], $bgr[6], $bgr[7]);
		}
		$cor = ConvertColor($bgr[1]);
		if ($cor) { $g['col'] = array($cor['R'],$cor['G'],$cor['B']); }
		else { $g['col'] = array(255); }
		$cor = ConvertColor($bgr[2]);
		if ($cor) { $g['col2'] = array($cor['R'],$cor['G'],$cor['B']); }
		else { $g['col2'] = array(255); }
		$g['extend'] = array('true','true');
		return $g;
	}
	return false;
}

function margin_padding_expand($mp) {
	$prop = explode(' ',trim($mp));
	if (count($prop) == 1 ) { 
		return array('T' => $prop[0], 'R' => $prop[0], 'B' => $prop[0], 'L'=> $prop[0]);
	}
	if (count($prop) == 2 ) { 
		return array('T' => $prop[0], 'R' => $prop[1], 'B' => $prop[0], 'L'=> $prop[1]);
	}
	if (count($prop) == 4 ) { 
		return array('T' => $prop[0], 'R' => $prop[1], 'B' => $prop[2], 'L'=> $prop[3]);
	}
	return array(); 
}

function border_radius_expand($val,$k) {
	$b = array();
	if ($k == 'BORDER-RADIUS') {
		$hv = explode('/',trim($val));
		$prop = explode(' ',trim($hv[0]));
		if (count($prop)==1) {
			$b['TL-H'] = $b['TR-H'] = $b['BR-H'] = $b['BL-H'] = $prop[0];
		}
		else if (count($prop)==2) {
			$b['TL-H'] = $b['BR-H'] = $prop[0];
			$b['TR-H'] = $b['BL-H'] = $prop[1];
		}
		else if (count($prop)==3) {
			$b['TL-H'] = $prop[0];
			$b['TR-H'] = $b['BL-H'] = $prop[1];
			$b['BR-H'] = $prop[2];
		}
		else if (count($prop)==4) {
			$b['TL-H'] = $prop[0];
			$b['TR-H'] = $prop[1];
			$b['BR-H'] = $prop[2];
			$b['BL-H'] = $prop[3];
		}
		if (count($hv)==2) {
			$prop = explode(' ',trim($hv[1]));
			if (count($prop)==1) {
				$b['TL-V'] = $b['TR-V'] = $b['BR-V'] = $b['BL-V'] = $prop[0];
			}
			else if (count($prop)==2) {
				$b['TL-V'] = $b['BR-V'] = $prop[0];
				$b['TR-V'] = $b['BL-V'] = $prop[1];
			}
			else if (count($prop)==3) {
				$b['TL-V'] = $prop[0];
				$b['TR-V'] = $b['BL-V'] = $prop[1];
				$b['BR-V'] = $prop[2];
			}
			else if (count($prop)==4) {
				$b['TL-V'] = $prop[0];
				$b['TR-V'] = $prop[1];
				$b['BR-V'] = $prop[2];
				$b['BL-V'] = $prop[3];
			}
		}
		else {
			$b['TL-V'] = $b['TL-H'];
			$b['TR-V'] = $b['TR-H'];
			$b['BL-V'] = $b['BL-H'];
			$b['BR-V'] = $b['BR-H'];
		}
		return $b;
	}

	// Parse 2
	$h = 0;
	$v = 0;
	$prop = explode(' ',trim($val));
	if (count($prop)==1) { $h = $v = $val; }
	else { $h = $prop[0]; $v = $prop[1]; }
	if ($h==0 || $v==0) { $h = $v = 0; }
	if ($k == 'BORDER-TOP-LEFT-RADIUS') {
		$b['TL-H'] = $h;
		$b['TL-V'] = $v;
	}
	else if ($k == 'BORDER-TOP-RIGHT-RADIUS') {
		$b['TR-H'] = $h;
		$b['TR-V'] = $v;
	}
	else if ($k == 'BORDER-BOTTOM-LEFT-RADIUS') {
		$b['BL-H'] = $h;
		$b['BL-V'] = $v;
	}
	else if ($k == 'BORDER-BOTTOM-RIGHT-RADIUS') {
		$b['BR-H'] = $h;
		$b['BR-V'] = $v;
	}
	return $b;

}

// mPDF 3.0
function _borderPadding($a, $b, &$px, &$py) {
	// $px and py are padding long axis (x) and short axis (y)
	$added = 0;	// extra padding

	$x = $a-$px;
	$y = $b-$py;
	// Check if Falls within ellipse of border radius
	if ( ( (($x+$added)*($x+$added))/($a*$a) + (($y+$added)*($y+$added))/($b*$b) ) <=1 ) { return false; }

	$t = atan2($y,$x);

	$newx = $b / sqrt((($b*$b)/($a*$a)) + ( tan($t) * tan($t) )  );
	$newy = $a / sqrt((($a*$a)/($b*$b)) + ( (1/tan($t)) * (1/tan($t)) )  );
	$px = max($px, $a - $newx + $added);
	$py = max($py, $b - $newy + $added);
}


function setBorderDominance($prop, $val) {
	if ($prop['BORDER-LEFT']) { $this->cell_border_dominance_L = $val; }
	if ($prop['BORDER-RIGHT']) { $this->cell_border_dominance_R = $val; }
	if ($prop['BORDER-TOP']) { $this->cell_border_dominance_T = $val; }
	if ($prop['BORDER-BOTTOM']) { $this->cell_border_dominance_B = $val; }
}

function MergeCSS($inherit,$tag,$attr) {
	// Extensively Rewritten in mPDF 1.2 
	$p = array();
	$zp = array(); 

	$classes = array();
	if (isset($attr['CLASS'])) {
		$classes = preg_split('/\s+/',$attr['CLASS']);
	}

	//===============================================
	// Set Inherited properties
	if ($inherit == 'TOPTABLE') {	// $tag = TABLE
		//===============================================
		// Save Cascading CSS e.g. "div.topic p" at this block level
		// Error edited in mPDF 2.0

		if (isset($this->blk[$this->blklvl]['cascadeCSS'])) {
			$this->tablecascadeCSS[0] = $this->blk[$this->blklvl]['cascadeCSS'];
		}
		else {
			$this->tablecascadeCSS[0] = $this->cascadeCSS;
		}
	}
	//===============================================
	// Set Inherited properties
	if ($inherit == 'TOPTABLE' || $inherit == 'TABLE') {
		//Cascade everything from last level that is not an actual property, or defined by current tag/attributes
		// mPDF 2.1 Corrected - cascade everything
		if (is_array($this->tablecascadeCSS[$this->tbCSSlvl-1])) {
		   foreach($this->tablecascadeCSS[$this->tbCSSlvl-1] AS $k=>$v) {
//			if ($k != $tag && !preg_match('/^CLASS>>('.implode('|',$classes).')$/i',$k) && !preg_match('/^'.$tag.'>>CLASS>>('.implode('|',$classes).')$/i',$k) && $k != 'ID>>'.$attr['ID'] && $k != $tag.'>>ID>>'.$attr['ID'] && (preg_match('/(ID|CLASS)>>/',$k) || preg_match('/^('.$this->allowedCSStags.')(>>.*){0,1}$/',$k))) {
				$this->tablecascadeCSS[$this->tbCSSlvl][$k] = $v;
//			}
		   }
		}
		if ($this->cascadeCSS[$tag]) {
		   $carry = $this->cascadeCSS[$tag];
		   if ($this->tablecascadeCSS[$this->tbCSSlvl]) {
			$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		   }
		   else {
			$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
		   }
		}
		foreach($classes AS $class) {
			if ($this->cascadeCSS['CLASS>>'.$class]) {
		   		$carry = $this->cascadeCSS['CLASS>>'.$class];
		   		if ($this->tablecascadeCSS[$this->tbCSSlvl]) {
					$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		 		}
		 		else {
					$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>CLASS>>'.$class]) {
		   		$carry = $this->cascadeCSS[$tag.'>>CLASS>>'.$class];
		   		if ($this->tablecascadeCSS[$this->tbCSSlvl]) {
					$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		 		}
		 		else {
					$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
				}
			}
		}
		if (isset($attr['ID'])) {
			if ($this->cascadeCSS['ID>>'.$attr['ID']]) {
		   		$carry = $this->cascadeCSS['ID>>'.$attr['ID']];
		   		if ($this->tablecascadeCSS[$this->tbCSSlvl]) {
					$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		 		}
		 		else {
					$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']]) {
		   		$carry = $this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']];
		   		if ($this->tablecascadeCSS[$this->tbCSSlvl]) {
					$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		 		}
		 		else {
					$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
				}
			}
		}
		//===============================================
		// Cascading forward CSS e.g. "table.topic td" for this table in $this->tablecascadeCSS 
		//===============================================
		// STYLESHEET TAG e.g. table
		if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1][$tag])) { 
		   $carry = $this->tablecascadeCSS[$this->tbCSSlvl-1][$tag];
		   if ($this->tablecascadeCSS[$this->tbCSSlvl]) {
			$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		   }
		   else {
			$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
		   }
		}
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach($classes AS $class) {
		  if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1]['CLASS>>'.$class])) { 
		   $carry = $this->tablecascadeCSS[$this->tbCSSlvl-1]['CLASS>>'.$class];
		   if ($this->tablecascadeCSS[$this->tbCSSlvl] ) {
			$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		   }
		   else {
			$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
		   }
		  }
		}
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1]['ID>>'.$attr['ID']])) { 
		   $carry = $this->tablecascadeCSS[$this->tbCSSlvl-1]['ID>>'.$attr['ID']];
		   if ($this->tablecascadeCSS[$this->tbCSSlvl] ) {
			$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		   }
		   else {
			$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
		   }
		  }
		}
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach($classes AS $class) {
		  if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1][$tag.'>>CLASS>>'.$class])) { 
		   $carry = $this->tablecascadeCSS[$this->tbCSSlvl-1][$tag.'>>CLASS>>'.$class];
		   if ($this->tablecascadeCSS[$this->tbCSSlvl] ) {
			$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		   }
		   else {
			$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
		   }
		  }
		}
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1][$tag.'>>ID>>'.$attr['ID']])) { 
		   $carry = $this->tablecascadeCSS[$this->tbCSSlvl-1][$tag.'>>ID>>'.$attr['ID']];
		   if ($this->tablecascadeCSS[$this->tbCSSlvl] ) {
			$this->tablecascadeCSS[$this->tbCSSlvl] = array_merge_recursive_unique($this->tablecascadeCSS[$this->tbCSSlvl], $carry);
		   }
		   else {
			$this->tablecascadeCSS[$this->tbCSSlvl] = $carry;
		   }
		  }
		}
		//===============================================
	}
	//===============================================
	// Set Inherited properties
	if ($inherit == 'TOPLIST') {	// $tag = UL,OL
		//===============================================
		// Save Cascading CSS e.g. "div.topic p" at this block level
		if (isset($this->blk[$this->blklvl]['cascadeCSS'])) {
			$this->listcascadeCSS[0] = $this->blk[$this->blklvl]['cascadeCSS'];
		}
		else {
			$this->listcascadeCSS[0] = $this->cascadeCSS;
		}
	}
	//===============================================
	// Set Inherited properties
	if ($inherit == 'TOPLIST' || $inherit == 'LIST') {
		//Cascade everything from last level that is not an actual property, or defined by current tag/attributes
		if (is_array($this->listcascadeCSS[$this->listCSSlvl-1])) {
		   foreach($this->listcascadeCSS[$this->listCSSlvl-1] AS $k=>$v) {
				$this->listcascadeCSS[$this->listCSSlvl][$k] = $v;
		   }
		}
		if ($this->cascadeCSS[$tag]) {
		   $carry = $this->cascadeCSS[$tag];
		   if ($this->listcascadeCSS[$this->listCSSlvl]) {
			$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		   }
		   else {
			$this->listcascadeCSS[$this->listCSSlvl] = $carry;
		   }
		}
		foreach($classes AS $class) {
			if ($this->cascadeCSS['CLASS>>'.$class]) {
		   		$carry = $this->cascadeCSS['CLASS>>'.$class];
		   		if ($this->listcascadeCSS[$this->listCSSlvl]) {
					$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		 		}
		 		else {
					$this->listcascadeCSS[$this->listCSSlvl] = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>CLASS>>'.$class]) {
		   		$carry = $this->cascadeCSS[$tag.'>>CLASS>>'.$class];
		   		if ($this->listcascadeCSS[$this->listCSSlvl]) {
					$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		 		}
		 		else {
					$this->listcascadeCSS[$this->listCSSlvl] = $carry;
				}
			}
		}
		if (isset($attr['ID'])) {
			if ($this->cascadeCSS['ID>>'.$attr['ID']]) {
		   		$carry = $this->cascadeCSS['ID>>'.$attr['ID']];
		   		if ($this->listcascadeCSS[$this->listCSSlvl]) {
					$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		 		}
		 		else {
					$this->listcascadeCSS[$this->listCSSlvl] = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']]) {
		   		$carry = $this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']];
		   		if ($this->listcascadeCSS[$this->listCSSlvl]) {
					$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		 		}
		 		else {
					$this->listcascadeCSS[$this->listCSSlvl] = $carry;
				}
			}
		}
		//===============================================
		// Cascading forward CSS e.g. "table.topic td" for this list in $this->listcascadeCSS 
		//===============================================
		// STYLESHEET TAG e.g. table
		if (isset($this->listcascadeCSS[$this->listCSSlvl-1][$tag])) { 
		   $carry = $this->listcascadeCSS[$this->listCSSlvl-1][$tag];
		   if ($this->listcascadeCSS[$this->listCSSlvl]) {
			$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		   }
		   else {
			$this->listcascadeCSS[$this->listCSSlvl] = $carry;
		   }
		}
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach($classes AS $class) {
		  if (isset($this->listcascadeCSS[$this->listCSSlvl-1]['CLASS>>'.$class])) { 
		   $carry = $this->listcascadeCSS[$this->listCSSlvl-1]['CLASS>>'.$class];
		   if ($this->listcascadeCSS[$this->listCSSlvl] ) {
			$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		   }
		   else {
			$this->listcascadeCSS[$this->listCSSlvl] = $carry;
		   }
		  }
		}
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->listcascadeCSS[$this->listCSSlvl-1]['ID>>'.$attr['ID']])) { 
		   $carry = $this->listcascadeCSS[$this->listCSSlvl-1]['ID>>'.$attr['ID']];
		   if ($this->listcascadeCSS[$this->listCSSlvl] ) {
			$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		   }
		   else {
			$this->listcascadeCSS[$this->listCSSlvl] = $carry;
		   }
		  }
		}
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach($classes AS $class) {
		  if (isset($this->listcascadeCSS[$this->listCSSlvl-1][$tag.'>>CLASS>>'.$class])) { 
		   $carry = $this->listcascadeCSS[$this->listCSSlvl-1][$tag.'>>CLASS>>'.$class];
		   if ($this->listcascadeCSS[$this->listCSSlvl] ) {
			$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		   }
		   else {
			$this->listcascadeCSS[$this->listCSSlvl] = $carry;
		   }
		  }
		}
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->listcascadeCSS[$this->listCSSlvl-1][$tag.'>>ID>>'.$attr['ID']])) { 
		   $carry = $this->listcascadeCSS[$this->listCSSlvl-1][$tag.'>>ID>>'.$attr['ID']];
		   if ($this->listcascadeCSS[$this->listCSSlvl] ) {
			$this->listcascadeCSS[$this->listCSSlvl] = array_merge_recursive_unique($this->listcascadeCSS[$this->listCSSlvl], $carry);
		   }
		   else {
			$this->listcascadeCSS[$this->listCSSlvl] = $carry;
		   }
		  }
		}
		//===============================================
	}
	//===============================================
	// Set Inherited properties
	if ($inherit == 'BLOCK') {
		// mPDF 2.0 Cascade everything from last block that is not an actual property, or defined by current tag/attributes
		// mPDF 2.1 Corrected - cascade everything
		if (is_array($this->blk[$this->blklvl-1]['cascadeCSS'])) {
		   foreach($this->blk[$this->blklvl-1]['cascadeCSS'] AS $k=>$v) {
//			if ($k != $tag && !preg_match('/^CLASS>>('.implode('|',$classes).')$/i',$k) && !preg_match('/^'.$tag.'>>CLASS>>('.implode('|',$classes).')$/i',$k) && $k != 'ID>>'.$attr['ID'] && $k != $tag.'>>ID>>'.$attr['ID'] && (preg_match('/(ID|CLASS)>>/',$k) || preg_match('/^('.$this->allowedCSStags.')(>>.*){0,1}$/',$k))) {
				$this->blk[$this->blklvl]['cascadeCSS'][$k] = $v;
//			}

		   }
		}

		//===============================================
		// Save Cascading CSS e.g. "div.topic p" at this block level

		if ($this->cascadeCSS[$tag]) {
			$carry =  $this->cascadeCSS[$tag];
			if ($this->blk[$this->blklvl]['cascadeCSS']) {
				$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   	}
		   	else {
				$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
			}
		}
		foreach($classes AS $class) {
			if ($this->cascadeCSS['CLASS>>'.$class]) {
				$carry =  $this->cascadeCSS['CLASS>>'.$class];
				if ($this->blk[$this->blklvl]['cascadeCSS']) {
					$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   		}
		   		else {
					$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>CLASS>>'.$class]) {
				$carry =  $this->cascadeCSS[$tag.'>>CLASS>>'.$class];
				if ($this->blk[$this->blklvl]['cascadeCSS']) {
					$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   		}
		   		else {
					$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
				}
			}
		}
		if (isset($attr['ID'])) {
			if ($this->cascadeCSS['ID>>'.$attr['ID']]) {
				$carry =  $this->cascadeCSS['ID>>'.$attr['ID']];
				if ($this->blk[$this->blklvl]['cascadeCSS']) {
					$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   		}
		   		else {
					$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']]) {
				$carry =  $this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']];
				if ($this->blk[$this->blklvl]['cascadeCSS']) {
					$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   		}
		   		else {
					$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
				}
			}
		}
		//===============================================
		// Cascading forward CSS
		//===============================================
		// STYLESHEET TAG e.g. h1  p  div  table
		//if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag]) && !$this->blk[$this->blklvl-1]['cascadeCSS'][$tag]['depth']) { 
		if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag])) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach($classes AS $class) {
		  //if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$class]) && !$this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$class]['depth']) { 
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$class])) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$class];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  //if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']]) && !$this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']]['depth']) { 
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']])) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. div.smallone{}  p.redletter{}
		foreach($classes AS $class) {
		  //if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$class]) && !$this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$class]['depth']) { 
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$class])) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$class];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. div#smallone{}  p#redletter{}
		if (isset($attr['ID'])) {
		  //if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]) && !$this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]['depth']) { 
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']])) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		  }
		}
		//===============================================
		  // Block properties
		  if ($this->blk[$this->blklvl-1]['margin_collapse']) { $p['MARGIN-COLLAPSE'] = 'COLLAPSE'; }	// custom tag, but follows CSS principle that border-collapse is inherited
		  if ($this->blk[$this->blklvl-1]['line_height']) { $p['LINE-HEIGHT'] = $this->blk[$this->blklvl-1]['line_height']; }	
		  if ($this->blk[$this->blklvl-1]['align']) { 
			if ($this->blk[$this->blklvl-1]['align'] == 'L') { $p['TEXT-ALIGN'] = 'left'; } 
			else if ($this->blk[$this->blklvl-1]['align'] == 'J') { $p['TEXT-ALIGN'] = 'justify'; } 
			else if ($this->blk[$this->blklvl-1]['align'] == 'R') { $p['TEXT-ALIGN'] = 'right'; } 
			else if ($this->blk[$this->blklvl-1]['align'] == 'C') { $p['TEXT-ALIGN'] = 'center'; } 
		  }
		  // mPDF 3.0
		  if ($this->ColActive || $this->keep_block_together) { 
		  	if ($this->blk[$this->blklvl-1]['bgcolor']) { // Doesn't officially inherit, but default value is transparent (?=inherited)
				$cor = $this->blk[$this->blklvl-1]['bgcolorarray' ];
				$p['BACKGROUND-COLOR'] = 'RGB('.$cor['R'].','.$cor['G'].','.$cor['B'].')';
			}
		  }

		// Text characterisics (and text-indent) are only inherited by P or DIV blocks
//		  if ($this->blk[$this->blklvl]['tag'] == 'P' || $this->blk[$this->blklvl]['tag'] == 'DIV') {

		    if ($this->blk[$this->blklvl-1]['TEXT-INDENT']) { $p['TEXT-INDENT'] = $this->blk[$this->blklvl-1]['TEXT-INDENT']; }
		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'family' ]) {
			$p['FONT-FAMILY'] = $this->blk[$this->blklvl-1]['InlineProperties'][ 'family' ];
		    }
   		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'I' ]) {
			$p['FONT-STYLE'] = 'italic';
		    }
   		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'sizePt' ]) {
			$p['FONT-SIZE'] = $this->blk[$this->blklvl-1]['InlineProperties'][ 'sizePt' ] . 'pt';
		    }
   		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'B' ]) {
			$p['FONT-WEIGHT'] = 'bold';
		    }
   		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'colorarray' ]) {
			$cor = $this->blk[$this->blklvl-1]['InlineProperties'][ 'colorarray' ];
			$p['COLOR'] = 'RGB('.$cor['R'].','.$cor['G'].','.$cor['B'].')';
		    }
		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'toupper' ]) {
			$p['TEXT-TRANSFORM'] = 'uppercase';
		    }
		    else if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'tolower' ]) {
			$p['TEXT-TRANSFORM'] = 'lowercase';
		    }
			// CSS says text-decoration is not inherited, but IE7 does??
		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'underline' ]) {
			$p['TEXT-DECORATION'] = 'underline';
		    }
//		  }

	}
	//===============================================
	//===============================================
	// Set Inherited properties
	if ($inherit == 'LIST') {
		if ($this->listlvl == 1) {	// listlvl has not been incremented yet
		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'family' ]) {
			$p['FONT-FAMILY'] = $this->blk[$this->blklvl]['InlineProperties'][ 'family' ];
		    }
   		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'I' ]) {
			$p['FONT-STYLE'] = 'italic';
		    }
   		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'sizePt' ]) {
			$p['FONT-SIZE'] = $this->blk[$this->blklvl]['InlineProperties'][ 'sizePt' ] . 'pt';
		    }
   		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'B' ]) {
			$p['FONT-WEIGHT'] = 'bold';
		    }
   		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'colorarray' ]) {
			$cor = $this->blk[$this->blklvl]['InlineProperties'][ 'colorarray' ];
			$p['COLOR'] = 'RGB('.$cor['R'].','.$cor['G'].','.$cor['B'].')';
		    }
		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'toupper' ]) {
			$p['TEXT-TRANSFORM'] = 'uppercase';
		    }
		    else if ($this->blk[$this->blklvl]['InlineProperties'][ 'tolower' ]) {
			$p['TEXT-TRANSFORM'] = 'lowercase';
		    }
			// CSS says text-decoration is not inherited, but IE7 does??
		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'underline' ]) {
			$p['TEXT-DECORATION'] = 'underline';
		    }
		}
	}
	//===============================================
	//===============================================
	// DEFAULT for this TAG set in DefaultCSS
	if (isset($this->defaultCSS[$tag])) { 
			$zp = $this->fixCSS($this->defaultCSS[$tag]);
			if (($this->directionality == 'rtl') && ($this->rtlCSS == 0)) { 
				$this->reverse_align($zp['TEXT-ALIGN']);
				$pl =  $zp['PADDING-LEFT'];
				$pr =  $zp['PADDING-RIGHT'];
				if ($pl || $pr) { $zp['PADDING-RIGHT'] = $pl; $zp['PADDING-LEFT'] = $pr; }
				$ml =  $zp['MARGIN-LEFT'];
				$mr =  $zp['MARGIN-RIGHT'];
				if ($ml || $mr) { $zp['MARGIN-RIGHT'] = $ml; $zp['MARGIN-LEFT'] = $mr; }
				$bl =  $zp['BORDER-LEFT'];
				$br =  $zp['BORDER-RIGHT'];
				if ($bl || $br) { $zp['BORDER-RIGHT'] = $bl; $zp['BORDER-LEFT'] = $br; }
			}
			if (is_array($zp)) { $p = array_merge($zp,$p); }	// Inherited overwrites default
	}
	//===============================================
	// STYLESHEET TAG e.g. h1  p  div  table
	if (isset($this->CSS[$tag])) { 
			$zp = $this->CSS[$tag];
			if ($tag=='TD' || $tag=='TH')  { $this->setBorderDominance($zp, 9); }	// 6
			if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	//===============================================
	// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
	foreach($classes AS $class) {
			// Edited mPDF 1.2 to allow tag, class and ID to be distinct
			$zp = $this->CSS['CLASS>>'.$class];
			if ($tag=='TD' || $tag=='TH')  { $this->setBorderDominance($zp, 9); }	// 7.1
			if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	//===============================================
	// STYLESHEET ID e.g. #smallone{}  #redletter{}
	if (isset($attr['ID'])) {
			// Edited mPDF 1.2 to allow tag, class and ID to be distinct
			$zp = $this->CSS['ID>>'.$attr['ID']];
			if ($tag=='TD' || $tag=='TH')  { $this->setBorderDominance($zp, 9); }	// 7.2
			if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	//===============================================
	// STYLESHEET CLASS e.g. p.smallone{}  div.redletter{}
	foreach($classes AS $class) {
			// Edited mPDF 1.2 to allow tag, class and ID to be distinct
			$zp = $this->CSS[$tag.'>>CLASS>>'.$class];
			if ($tag=='TD' || $tag=='TH')  { $this->setBorderDominance($zp, 9); }	// 7.3
			if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	//===============================================
	// STYLESHEET CLASS e.g. p#smallone{}  div#redletter{}
	if (isset($attr['ID'])) {
			// Edited mPDF 1.2 to allow tag, class and ID to be distinct
			$zp = $this->CSS[$tag.'>>ID>>'.$attr['ID']];
			if ($tag=='TD' || $tag=='TH')  { $this->setBorderDominance($zp, 9); }	// 7.4
			if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	//===============================================
	// Cascaded e.g. div.class p only works for block level
	if ($inherit == 'BLOCK') {
		//===============================================
		// STYLESHEET TAG e.g. div h1    div p
		if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag]) && $this->blk[$this->blklvl-1]['cascadeCSS'][$tag]['depth']>1) { 
			$zp = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag];
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach($classes AS $class) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$class]) && $this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$class]['depth']>1) { 
			$zp = $this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$class];
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']]) && $this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']]['depth']>1) { 
			$zp = $this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']];
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. div.smallone{}  p.redletter{}
		foreach($classes AS $class) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$class]) && $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$class]['depth']>1) { 
			$zp = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$class];
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. div#smallone{}  p#redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]) && $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]['depth']>1) { 
			$zp = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']];
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
	}
	else if ($inherit == 'TOPTABLE' || $inherit == 'TABLE') { // NB looks at $this->tablecascadeCSS-1 for cascading CSS
		//===============================================
		// STYLESHEET TAG e.g. h1  p  div  table td
		if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1][$tag])) { 
			$zp = $this->tablecascadeCSS[$this->tbCSSlvl-1][$tag];
			$this->setBorderDominance($zp, 9);	// 8.1
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach($classes AS $class) {
		  if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1]['CLASS>>'.$class])) { 
			$zp = $this->tablecascadeCSS[$this->tbCSSlvl-1]['CLASS>>'.$class];
			$this->setBorderDominance($zp, 9);	// 8.2
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1]['ID>>'.$attr['ID']])) { 
			$zp = $this->tablecascadeCSS[$this->tbCSSlvl-1]['ID>>'.$attr['ID']];
			$this->setBorderDominance($zp, 9);	// 8.3
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. td.smallone{}  td.redletter{}
		foreach($classes AS $class) {
		  if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1][$tag.'>>CLASS>>'.$class])) { 
			$zp = $this->tablecascadeCSS[$this->tbCSSlvl-1][$tag.'>>CLASS>>'.$class];
			$this->setBorderDominance($zp, 9);	// 8.4
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. td#smallone{}  td#redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->tablecascadeCSS[$this->tbCSSlvl-1][$tag.'>>ID>>'.$attr['ID']])) { 
			$zp = $this->tablecascadeCSS[$this->tbCSSlvl-1][$tag.'>>ID>>'.$attr['ID']];
			$this->setBorderDominance($zp, 9);	// 8.5
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
	}
	//===============================================
	else if ($inherit == 'TOPLIST' || $inherit == 'LIST') { // NB looks at $this->listcascadeCSS-1 for cascading CSS
		//===============================================
		// STYLESHEET TAG e.g. h1  p  div  table td
		if (isset($this->listcascadeCSS[$this->listCSSlvl-1][$tag])) { 
			$zp = $this->listcascadeCSS[$this->listCSSlvl-1][$tag];
			$this->setBorderDominance($zp, 9);	// 8.1
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach($classes AS $class) {
		  if (isset($this->listcascadeCSS[$this->listCSSlvl-1]['CLASS>>'.$class])) { 
			$zp = $this->listcascadeCSS[$this->listCSSlvl-1]['CLASS>>'.$class];
			$this->setBorderDominance($zp, 9);	// 8.2
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->listcascadeCSS[$this->listCSSlvl-1]['ID>>'.$attr['ID']])) { 
			$zp = $this->listcascadeCSS[$this->listCSSlvl-1]['ID>>'.$attr['ID']];
			$this->setBorderDominance($zp, 9);	// 8.3
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. td.smallone{}  td.redletter{}
		foreach($classes AS $class) {
		  if (isset($this->listcascadeCSS[$this->listCSSlvl-1][$tag.'>>CLASS>>'.$class])) { 
			$zp = $this->listcascadeCSS[$this->listCSSlvl-1][$tag.'>>CLASS>>'.$class];
			$this->setBorderDominance($zp, 9);	// 8.4
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. td#smallone{}  td#redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->listcascadeCSS[$this->listCSSlvl-1][$tag.'>>ID>>'.$attr['ID']])) { 
			$zp = $this->listcascadeCSS[$this->listCSSlvl-1][$tag.'>>ID>>'.$attr['ID']];
			$this->setBorderDominance($zp, 9);	// 8.5
			if (is_array($zp)) { $p = array_merge($p,$zp); }
		  }
		}
		//===============================================
	}
	//===============================================
	if (($this->directionality == 'rtl') && ($this->rtlCSS == 1)) { $this->reverse_align($p['TEXT-ALIGN']); }
	//===============================================
	// INLINE STYLE e.g. style="CSS:property"
	if (isset($attr['STYLE'])) {
			$zp = $this->readInlineCSS($attr['STYLE']);
			if ($tag=='TD' || $tag=='TH')  { 
				$this->setBorderDominance($zp, 9);	// 9
			}
			if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	//===============================================
	if (($this->directionality == 'rtl') && ($this->rtlCSS == 2)) { $this->reverse_align($p['TEXT-ALIGN']); }
	//===============================================
	// INLINE ATTRIBUTES e.g. .. ALIGN="CENTER">
	// mPDF 2.3
	if (isset($attr['LANG']) and $attr['LANG']!='') {
			$p['LANG'] = $attr['LANG'];
	}
	if (isset($attr['COLOR']) and $attr['COLOR']!='') {
			$p['COLOR'] = $attr['COLOR'];
	}
	if ($tag != 'INPUT') {
		if (isset($attr['WIDTH']) and $attr['WIDTH']!='') {
			$p['WIDTH'] = $attr['WIDTH'];
		}
		if (isset($attr['HEIGHT']) and $attr['HEIGHT']!='') {
			$p['HEIGHT'] = $attr['HEIGHT'];
		}
	}
	if ($tag == 'FONT') {
		if (isset($attr['FACE'])) {
			$p['FONT-FAMILY'] = $attr['FACE'];
		}
		if (isset($attr['SIZE']) and $attr['SIZE']!='') {
			$s = '';
			if ($attr['SIZE'] === '+1') { $s = '120%'; }
			else if ($attr['SIZE'] === '-1') { $s = '86%'; }
			else if ($attr['SIZE'] === '1') { $s = 'XX-SMALL'; }
			else if ($attr['SIZE'] == '2') { $s = 'X-SMALL'; }
			else if ($attr['SIZE'] == '3') { $s = 'SMALL'; }
			else if ($attr['SIZE'] == '4') { $s = 'MEDIUM'; }
			else if ($attr['SIZE'] == '5') { $s = 'LARGE'; }
			else if ($attr['SIZE'] == '6') { $s = 'X-LARGE'; }
			else if ($attr['SIZE'] == '7') { $s = 'XX-LARGE'; }
			if ($s) $p['FONT-SIZE'] = $s;
		}
	}
	if (isset($attr['VALIGN']) and $attr['VALIGN']!='') {
		$p['VERTICAL-ALIGN'] = $attr['VALIGN'];
	}
	if (isset($attr['VSPACE']) and $attr['VSPACE']!='') {
		$p['MARGIN-TOP'] = $attr['VSPACE'];
		$p['MARGIN-BOTTOM'] = $attr['VSPACE'];
	}
	if (isset($attr['HSPACE']) and $attr['HSPACE']!='') {
		$p['MARGIN-LEFT'] = $attr['HSPACE'];
		$p['MARGIN-RIGHT'] = $attr['HSPACE'];
	}
	//===============================================
	return $p;
}


function GetPagedMediaCSS($tag='') {
	$tag = strtoupper($tag);
	$first = array();
	$p = array();
	$p['SIZE'] = 'AUTO';
	$p['ISSET']=false; 

	// Uses mPDF original margins as default
	$p['MARGIN-RIGHT'] = strval($this->orig_rMargin).'mm';
	$p['MARGIN-LEFT'] = strval($this->orig_lMargin).'mm';
	$p['MARGIN-TOP'] = strval($this->orig_tMargin).'mm';
	$p['MARGIN-BOTTOM'] = strval($this->orig_bMargin).'mm';
	$p['MARGIN-HEADER'] = strval($this->orig_hMargin).'mm';
	$p['MARGIN-FOOTER'] = strval($this->orig_fMargin).'mm';

	$zp = array(); 

	// Basic page + selector
	$zp = $this->CSS['@PAGE'];
	if (is_array($zp)) { $p = array_merge($p,$zp); $p['ISSET']=true; }
	// If right/Odd page
	$zp = $this->CSS['@PAGE>>PSEUDO>>RIGHT'];
	if (is_array($zp)) { $p = array_merge($p,$zp); $p['ISSET']=true; }

	// If left/Even page
	$zp = $this->CSS['@PAGE>>PSEUDO>>LEFT'];
	// Mirror the values
	if ($zp['MARGIN-LEFT'] || $zp['MARGIN-RIGHT']) { 
		$tmp = $zp['MARGIN-RIGHT']; 
		$zp['MARGIN-RIGHT'] = $zp['MARGIN-LEFT']; 
		$zp['MARGIN-LEFT'] = $tmp; 
	}
	if (is_array($zp)) { $p = array_merge($p,$zp); $p['ISSET']=true; }

	// If first page
	$zp = $this->CSS['@PAGE>>PSEUDO>>FIRST'];
	if (is_array($zp)) { $first = array_merge($p,$zp); $p['ISSET']=true; }
	if ($tag) {
		// If named page
		$zp = $this->CSS['@PAGE>>NAMED>>'.$tag];
		if (is_array($zp)) { $p = array_merge($p,$zp); $p['ISSET']=true; }

		// If named right/Odd page
		$zp = $this->CSS['@PAGE>>NAMED>>'.$tag.'>>PSEUDO>>RIGHT'];
		if (is_array($zp)) { $p = array_merge($p,$zp); $p['ISSET']=true; }

		// If named left/Even page
		$zp = $this->CSS['@PAGE>>NAMED>>'.$tag.'>>PSEUDO>>LEFT'];
		if ($zp['MARGIN-LEFT'] || $zp['MARGIN-RIGHT']) { 
			$tmp = $zp['MARGIN-RIGHT']; 
			$zp['MARGIN-RIGHT'] = $zp['MARGIN-LEFT']; 
			$zp['MARGIN-LEFT'] = $tmp; 
		}
		if (is_array($zp)) { $p = array_merge($p,$zp); $p['ISSET']=true; }

		// If named first page
		$zp = $this->CSS['@PAGE>>NAMED>>'.$tag.'>>PSEUDO>>FIRST'];
		if (is_array($zp)) { $first = array_merge($first,$p,$zp); $p['ISSET']=true; }
	}
	if (is_array($p['SIZE'])) {
		if ($p['SIZE']['W'] > $p['SIZE']['H']) { $p['ORIENTATION'] = 'L'; $p['ISSET']=true; }
		else { $p['ORIENTATION'] = 'P'; }
	}
	$p['FIRST'] = $first;
	return $p;
}


function SetPagedMediaCSS() {
		$pm = $this->page_box['CSS'];	
		$bg = array();
		if (is_array($pm['SIZE'])) {
			if ($pm['SIZE']['W'] > $this->wPt/$this->k) { $pm['SIZE']['W'] = $this->wPt/$this->k; }
			if ($pm['SIZE']['H'] > $this->hPt/$this->k) { $pm['SIZE']['H'] = $this->hPt/$this->k; }
			if ($pm['ORIENTATION']==$this->DefOrientation) {
				$outer_width_LR = ($this->wPt/$this->k - $pm['SIZE']['W'])/2;
				$outer_width_TB = ($this->hPt/$this->k - $pm['SIZE']['H'])/2;
			}
			else {
				$outer_width_LR = ($this->hPt/$this->k - $pm['SIZE']['W'])/2;
				$outer_width_TB = ($this->wPt/$this->k - $pm['SIZE']['H'])/2;
			}
			$pgw = $pm['SIZE']['W'];
			$pgh = $pm['SIZE']['H'];
		}
		else {	// AUTO LANDSCAPE PORTRAIT
			$outer_width_LR = 0;
			$outer_width_TB = 0;
			if (strtoupper($pm['SIZE']) == 'AUTO') { $pm['ORIENTATION']=$this->DefOrientation; }
			else if (strtoupper($pm['SIZE']) == 'LANDSCAPE') { $pm['ORIENTATION']='L'; }
			else { $pm['ORIENTATION']='P'; }
			if ($pm['ORIENTATION']==$this->DefOrientation) {
				$pgw = $this->wPt/$this->k;
				$pgh = $this->hPt/$this->k;
			}
			else {
				$pgw = $this->hPt/$this->k;
				$pgh = $this->wPt/$this->k;
			}
		}

		if ($pm['ODD-HEADER-NAME']) { $ohname = $pm['ODD-HEADER-NAME']; }
		if ($pm['EVEN-HEADER-NAME']) { $ehname = $pm['EVEN-HEADER-NAME']; }
		if ($pm['ODD-FOOTER-NAME']) { $ofname = $pm['ODD-FOOTER-NAME']; }
		if ($pm['EVEN-FOOTER-NAME']) { $efname = $pm['EVEN-FOOTER-NAME']; }

		// mPDF 3.0
		if ($pm['BACKGROUND-COLOR']) { $bg['BACKGROUND-COLOR'] = $pm['BACKGROUND-COLOR']; }
		if ($pm['BACKGROUND-GRADIENT']) { $bg['BACKGROUND-GRADIENT'] = $pm['BACKGROUND-GRADIENT']; }
		if ($pm['BACKGROUND-IMAGE']) { $bg['BACKGROUND-IMAGE'] = $pm['BACKGROUND-IMAGE']; }
		if ($pm['BACKGROUND-REPEAT']) { $bg['BACKGROUND-REPEAT'] = $pm['BACKGROUND-REPEAT']; }
		if ($pm['BACKGROUND-POSITION']) { $bg['BACKGROUND-POSITION'] = $pm['BACKGROUND-POSITION']; }

		$mgl = ConvertSize($pm['MARGIN-LEFT'],$pgw) + $outer_width_LR;
		$mgr = ConvertSize($pm['MARGIN-RIGHT'],$pgw) + $outer_width_LR;
		$mgb = ConvertSize($pm['MARGIN-BOTTOM'],$pgh) + $outer_width_TB;
		$mgt = ConvertSize($pm['MARGIN-TOP'],$pgh) + $outer_width_TB;
		$mgh = ConvertSize($pm['MARGIN-HEADER'],$pgh) + $outer_width_TB;
		$mgf = ConvertSize($pm['MARGIN-FOOTER'],$pgh) + $outer_width_TB;
		$mgtfp = 0;	// first page margintop (difference from mgt)
		$mgbfp = 0;	// first page marginbottom (difference from mgb)

		if ($pm['ORIENTATION']) { $orientation = $pm['ORIENTATION']; }
		$this->page_box['outer_width_LR'] = $outer_width_LR;
		$this->page_box['outer_width_TB'] = $outer_width_TB;
		if ($pm['FIRST']) {
			if ($pm['FIRST']['MARGIN-BOTTOM']) { $mgbfp = ConvertSize($pm['FIRST']['MARGIN-BOTTOM'],$pgh) + $outer_width_TB - $mgb; }
			if ($pm['FIRST']['MARGIN-TOP']) { $mgtfp = ConvertSize($pm['FIRST']['MARGIN-TOP'],$pgh) + $outer_width_TB - $mgt; }
		}
		return array($orientation,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$mgtfp,$mgbfp,$ohname,$ehname,$ofname,$efname,$bg);
}

function PreviewBlockCSS($tag,$attr) {
	// Looks ahead from current block level to a new level
	$p = array();
	$zp = array(); 
	$oldcascadeCSS = $this->blk[$this->blklvl]['cascadeCSS'];
	$classes = array();
	if (isset($attr['CLASS'])) { $classes = preg_split('/\s+/',$attr['CLASS']); }
	//===============================================
	// DEFAULT for this TAG set in DefaultCSS
	if (isset($this->defaultCSS[$tag])) { 
		$zp = $this->fixCSS($this->defaultCSS[$tag]);
		if (is_array($zp)) { $p = array_merge($zp,$p); }	// Inherited overwrites default
	}
	// STYLESHEET TAG e.g. h1  p  div  table
	if (isset($this->CSS[$tag])) { 
		$zp = $this->CSS[$tag];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
	foreach($classes AS $class) {
		$zp = $this->CSS['CLASS>>'.$class];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	// STYLESHEET ID e.g. #smallone{}  #redletter{}
	if (isset($attr['ID'])) {
		$zp = $this->CSS['ID>>'.$attr['ID']];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	// STYLESHEET CLASS e.g. p.smallone{}  div.redletter{}
	foreach($classes AS $class) {
		$zp = $this->CSS[$tag.'>>CLASS>>'.$class];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	// STYLESHEET CLASS e.g. p#smallone{}  div#redletter{}
	if (isset($attr['ID'])) {
		$zp = $this->CSS[$tag.'>>ID>>'.$attr['ID']];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	//===============================================
	// STYLESHEET TAG e.g. div h1    div p
	if (isset($oldcascadeCSS[$tag]) && $oldcascadeCSS[$tag]['depth']>1) { 
		$zp = $oldcascadeCSS[$tag];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
	foreach($classes AS $class) {
	  if (isset($oldcascadeCSS['CLASS>>'.$class]) && $oldcascadeCSS['CLASS>>'.$class]['depth']>1) { 
		$zp = $oldcascadeCSS['CLASS>>'.$class];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	  }
	}
	// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
	if (isset($attr['ID'])) {
	  if (isset($oldcascadeCSS['ID>>'.$attr['ID']]) && $oldcascadeCSS['ID>>'.$attr['ID']]['depth']>1) { 
		$zp = $oldcascadeCSS['ID>>'.$attr['ID']];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	  }
	}
	// STYLESHEET CLASS e.g. div.smallone{}  p.redletter{}
	foreach($classes AS $class) {
	  if (isset($oldcascadeCSS[$tag.'>>CLASS>>'.$class]) && $oldcascadeCSS[$tag.'>>CLASS>>'.$class]['depth']>1) { 
		$zp = $oldcascadeCSS[$tag.'>>CLASS>>'.$class];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	  }
	}
	// STYLESHEET CLASS e.g. div#smallone{}  p#redletter{}
	if (isset($attr['ID'])) {
	  if (isset($oldcascadeCSS[$tag.'>>ID>>'.$attr['ID']]) && $oldcascadeCSS[$tag.'>>ID>>'.$attr['ID']]['depth']>1) { 
		$zp = $oldcascadeCSS[$tag.'>>ID>>'.$attr['ID']];
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	  }
	}
	//===============================================
	// INLINE STYLE e.g. style="CSS:property"
	if (isset($attr['STYLE'])) {
		$zp = $this->readInlineCSS($attr['STYLE']);
		if (is_array($zp)) { $p = array_merge($p,$zp); }
	}
	//===============================================
	return $p;
}




// Added mPDF 3.0 Float DIV - CLEAR
function ClearFloats($clear, $blklvl=0) {
	list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($blklvl,true);
	$end = $currpos = ($this->page*1000 + $this->y);
	if ($clear == 'BOTH' && ($l_exists || $r_exists)) {
		$this->Reset();
		$this->pageoutput[$this->page] = array();
		$end = max($l_max, $r_max, $currpos);
	}
	else if ($clear == 'RIGHT' && $r_exists) {
		$this->Reset();
		$this->pageoutput[$this->page] = array();
		$end = max($r_max, $currpos);
	}
	else if ($clear == 'LEFT' && $l_exists ) {
		$this->Reset();
		$this->pageoutput[$this->page] = array();
		$end = max($l_max, $currpos);
	}
	else { return; }
	$old_page = $this->page;
	$new_page = intval($end/1000);
	if ($old_page != $new_page) {
		$s = $this->PrintPageBackgrounds();
		// Writes after the marker so not overwritten later by page background etc.
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
		$this->pageBackgrounds = array();
		$this->page = $new_page;
	}
	$this->ResetMargins();
	$this->Reset();
	$this->pageoutput[$this->page] = array();
	$this->y = (($end*1000) % 1000000)/1000;	// mod changes operands to integers before processing
}


// Added mPDF 3.0 Float DIV
function GetFloatDivInfo($blklvl=0,$clear=false) {
	// If blklvl specified, only returns floats at that level - for ClearFloats
	$l_exists = false;
	$r_exists = false;
	$l_max = 0;
	$r_max = 0;
	$l_width = 0;
	$r_width = 0;
	if (count($this->floatDivs)) {
	  $currpos = ($this->page*1000 + $this->y);
	  foreach($this->floatDivs AS $f) {
	    if (($clear && $f['blockContext'] == $this->blk[$blklvl]['blockContext']) || (!$clear && $currpos >= $f['startpos'] && $currpos < ($f['endpos']-0.001) && $f['blklvl'] > $blklvl && $f['blockContext'] == $this->blk[$blklvl]['blockContext'])) {
		if ($f['side']=='L') {
			$l_exists= true;
			$l_max = max($l_max, $f['endpos']);
			$l_width = max($l_width , $f['w']);
		}
		if ($f['side']=='R') {
			$r_exists= true;
			$r_max = max($r_max, $f['endpos']);
			$r_width = max($r_width , $f['w']);
		}
	    }
	  }
	}
	return array($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width);
}



function OpenTag($tag,$attr)
{

  // What this gets: < $tag $attr['WIDTH']="90px" > does not get content here </closeTag here>
  // Correct tags where HTML specifies optional end tags,
  // and/or does not allow nesting e.g. P inside P, or 
  if ($this->allow_html_optional_endtags) {
    if (($tag == 'P' || $tag == 'DIV' || $tag == 'H1' || $tag == 'H2' || $tag == 'H3' || $tag == 'H4' || $tag == 'H5' || $tag == 'H6' || $tag == 'UL' || $tag == 'OL' || $tag == 'TABLE' || $tag=='PRE' || $tag=='FORM' || $tag=='ADDRESS' || $tag=='BLOCKQUOTE' || $tag=='CENTER' || $tag=='DL' || $tag == 'HR' ) && $this->lastoptionaltag == 'P') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DD' && $this->lastoptionaltag == 'DD') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DD' && $this->lastoptionaltag == 'DT') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DT' && $this->lastoptionaltag == 'DD') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DT' && $this->lastoptionaltag == 'DT') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'LI' && $this->lastoptionaltag == 'LI') { $this->CloseTag($this->lastoptionaltag ); }
    if (($tag == 'TD' || $tag == 'TH') && $this->lastoptionaltag == 'TD') { $this->CloseTag($this->lastoptionaltag ); }
    if (($tag == 'TD' || $tag == 'TH') && $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'TR' && $this->lastoptionaltag == 'TR') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'TR' && $this->lastoptionaltag == 'TD') { $this->CloseTag($this->lastoptionaltag );  $this->CloseTag('TR'); $this->CloseTag('THEAD'); }
    if ($tag == 'TR' && $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag );  $this->CloseTag('TR'); $this->CloseTag('THEAD'); }
    if ($tag == 'OPTION' && $this->lastoptionaltag == 'OPTION') { $this->CloseTag($this->lastoptionaltag ); }
  }


  $align = array('left'=>'L','center'=>'C','right'=>'R','top'=>'T','text-top'=>'T','middle'=>'M','baseline'=>'M','bottom'=>'B','text-bottom'=>'B','justify'=>'J');

  $this->ignorefollowingspaces=false;

  //Opening tag
  switch($tag){


     case 'PAGEHEADER': //added custom-tag mPDF 2.0
     case 'PAGEFOOTER':
	$this->ignorefollowingspaces = true; 
	if ($attr['NAME']) { $pname = $attr['NAME']; }
	else { $pname = '_default'; }

		if ($tag=='PAGEHEADER') { $p = &$this->pageheaders[$pname]; }
		else { $p = &$this->pagefooters[$pname]; }

		if ($attr['CONTENT-LEFT']) {
			$p['L']=array();
			$p['L']['content'] = $attr['CONTENT-LEFT'];
		}
		if ($attr['CONTENT-CENTER']) {
			$p['C']=array();
			$p['C']['content'] = $attr['CONTENT-CENTER'];
		}
		if ($attr['CONTENT-RIGHT']) {
			$p['R']=array();
			$p['R']['content'] = $attr['CONTENT-RIGHT'];
		}

		if ($attr['HEADER-STYLE'] || $attr['FOOTER-STYLE']) {	// font-family,size,weight,style,color
			if ($tag=='PAGEHEADER') { $properties = $this->readInlineCSS($attr['HEADER-STYLE']); }
			else { $properties = $this->readInlineCSS($attr['FOOTER-STYLE']); }
			if ($properties['FONT-FAMILY']) { 
				$p['L']['font-family'] = $properties['FONT-FAMILY']; 
				$p['C']['font-family'] = $properties['FONT-FAMILY']; 
				$p['R']['font-family'] = $properties['FONT-FAMILY']; 
			}
			if ($properties['FONT-SIZE']) { 
				$p['L']['font-size'] = ConvertSize($properties['FONT-SIZE']) * $this->k; 
				$p['C']['font-size'] = ConvertSize($properties['FONT-SIZE']) * $this->k; 
				$p['R']['font-size'] = ConvertSize($properties['FONT-SIZE']) * $this->k; 
			}
			if ($properties['FONT-WEIGHT']=='BOLD') { 
				$p['L']['font-style'] = 'B'; 
				$p['C']['font-style'] = 'B'; 
				$p['R']['font-style'] = 'B'; 
			}
			if ($properties['FONT-STYLE']=='ITALIC') { 
				$p['L']['font-style'] .= 'I'; 
				$p['C']['font-style'] .= 'I'; 
				$p['R']['font-style'] .= 'I'; 
			}
			if ($properties['COLOR']) { 
				$p['L']['color'] = $properties['COLOR']; 
				$p['C']['color'] = $properties['COLOR']; 
				$p['R']['color'] = $properties['COLOR']; 
			}
		}
		if ($attr['HEADER-STYLE-LEFT'] || $attr['FOOTER-STYLE-LEFT']) {
			if ($tag=='PAGEHEADER') { $properties = $this->readInlineCSS($attr['HEADER-STYLE-LEFT']); }
			else { $properties = $this->readInlineCSS($attr['FOOTER-STYLE-LEFT']); }
			if ($properties['FONT-FAMILY']) { $p['L']['font-family'] = $properties['FONT-FAMILY']; }
			if ($properties['FONT-SIZE']) { $p['L']['font-size'] = ConvertSize($properties['FONT-SIZE']) * $this->k; }
			if ($properties['FONT-WEIGHT']=='BOLD') { $p['L']['font-style'] ='B'; }
			if ($properties['FONT-STYLE']=='ITALIC') { $p['L']['font-style'] .='I'; }
			if ($properties['COLOR']) { $p['L']['color'] = $properties['COLOR']; }
		}
		if ($attr['HEADER-STYLE-CENTER'] || $attr['FOOTER-STYLE-CENTER']) {
			if ($tag=='PAGEHEADER') { $properties = $this->readInlineCSS($attr['HEADER-STYLE-CENTER']); }
			else { $properties = $this->readInlineCSS($attr['FOOTER-STYLE-CENTER']); }
			if ($properties['FONT-FAMILY']) { $p['C']['font-family'] = $properties['FONT-FAMILY']; }
			if ($properties['FONT-SIZE']) { $p['C']['font-size'] = ConvertSize($properties['FONT-SIZE']) * $this->k; }
			if ($properties['FONT-WEIGHT']=='BOLD') { $p['C']['font-style'] = 'B'; }
			if ($properties['FONT-STYLE']=='ITALIC') { $p['C']['font-style'] .= 'I'; }
			if ($properties['COLOR']) { $p['C']['color'] = $properties['COLOR']; }
		}
		if ($attr['HEADER-STYLE-RIGHT'] || $attr['FOOTER-STYLE-RIGHT']) {
			if ($tag=='PAGEHEADER') { $properties = $this->readInlineCSS($attr['HEADER-STYLE-RIGHT']); }
			else { $properties = $this->readInlineCSS($attr['FOOTER-STYLE-RIGHT']); }
			if ($properties['FONT-FAMILY']) { $p['R']['font-family'] = $properties['FONT-FAMILY']; }
			if ($properties['FONT-SIZE']) { $p['R']['font-size'] = ConvertSize($properties['FONT-SIZE']) * $this->k; }
			if ($properties['FONT-WEIGHT']=='BOLD') { $p['R']['font-style'] = 'B'; }
			if ($properties['FONT-STYLE']=='ITALIC') { $p['R']['font-style'] .= 'I'; }
			if ($properties['COLOR']) { $p['R']['color'] = $properties['COLOR']; }
		}
		if ($attr['LINE']) {	// 0|1|on|off
			if ($attr['LINE']=='1' || strtoupper($attr['LINE'])=='ON') { $lineset=1; }
			else { $lineset=0; }
			$p['line'] = $lineset;
		}
	break;


     case 'SETHTMLPAGEHEADER': //added custom-tag mPDF 2.0
     case 'SETHTMLPAGEFOOTER':
	$this->ignorefollowingspaces = true; 
	if ($attr['NAME']) { $pname = $attr['NAME']; }
	else { $pname = '_default'; }
	if ($attr['PAGE']) { 	// O|odd|even|E|ALL|[blank]
		if (strtoupper($attr['PAGE'])=='O' || strtoupper($attr['PAGE'])=='ODD') { $side='odd'; }
		else if (strtoupper($attr['PAGE'])=='E' || strtoupper($attr['PAGE'])=='EVEN') { $side='even'; }
		else if (strtoupper($attr['PAGE'])=='ALL') { $side='both'; }
		else { $side='odd'; }
	}
	else { $side='odd'; }
	if ($attr['VALUE']) { 	// -1|1|on|off
		if ($attr['VALUE']=='1' || strtoupper($attr['VALUE'])=='ON') { $set=1; }
		else if ($attr['VALUE']=='-1' || strtoupper($attr['VALUE'])=='OFF') { $set=0; }
		else { $set=1; }
	}
	else { $set=1; }
	if ($attr['SHOW-THIS-PAGE'] && $tag=='SETHTMLPAGEHEADER') { $write = 1; }
	else { $write = 0; }
	if ($side=='odd' || $side=='both') {
		if ($set && $tag=='SETHTMLPAGEHEADER') { $this->SetHTMLHeader($this->pageHTMLheaders[$pname],'O',$write); }
		else if ($set && $tag=='SETHTMLPAGEFOOTER') { $this->SetHTMLFooter($this->pageHTMLfooters[$pname],'O'); }
		else if ($tag=='SETHTMLPAGEHEADER') { $this->SetHTMLHeader('','O'); }
		else { $this->SetHTMLFooter('','O'); }
	}
	if ($side=='even' || $side=='both') {
		if ($set && $tag=='SETHTMLPAGEHEADER') { $this->SetHTMLHeader($this->pageHTMLheaders[$pname],'E',$write); }
		else if ($set && $tag=='SETHTMLPAGEFOOTER') { $this->SetHTMLFooter($this->pageHTMLfooters[$pname],'E'); }
		else if ($tag=='SETHTMLPAGEHEADER') { $this->SetHTMLHeader('','E'); }
		else { $this->SetHTMLFooter('','E'); }
	}
	break;

     case 'SETPAGEHEADER': //added custom-tag mPDF 2.0
     case 'SETPAGEFOOTER':
	$this->ignorefollowingspaces = true; 
	if ($attr['NAME']) { $pname = $attr['NAME']; }
	else { $pname = '_default'; }
	if ($attr['PAGE']) { 	// O|odd|even|E|ALL|[blank]
		if (strtoupper($attr['PAGE'])=='O' || strtoupper($attr['PAGE'])=='ODD') { $side='odd'; }
		else if (strtoupper($attr['PAGE'])=='E' || strtoupper($attr['PAGE'])=='EVEN') { $side='even'; }
		else if (strtoupper($attr['PAGE'])=='ALL') { $side='both'; }
		else { $side='odd'; }
	}
	else { $side='odd'; }
	if ($attr['VALUE']) { 	// -1|1|on|off
		if ($attr['VALUE']=='1' || strtoupper($attr['VALUE'])=='ON') { $set=1; }
		else if ($attr['VALUE']=='-1' || strtoupper($attr['VALUE'])=='OFF') { $set=0; }
		else { $set=1; }
	}
	else { $set=1; }
	if ($side=='odd' || $side=='both') {
		if ($set && $tag=='SETPAGEHEADER') { $this->headerDetails['odd'] = $this->pageheaders[$pname]; }
		else if ($set && $tag=='SETPAGEFOOTER') { $this->footerDetails['odd'] = $this->pagefooters[$pname]; }
		else if ($tag=='SETPAGEHEADER') { $this->headerDetails['odd'] = array(); }
		else { $this->footerDetails['odd'] = array(); }
	}
	if ($side=='even' || $side=='both') {
		if ($set && $tag=='SETPAGEHEADER') { $this->headerDetails['even'] = $this->pageheaders[$pname]; }
		else if ($set && $tag=='SETPAGEFOOTER') { $this->footerDetails['even'] = $this->pagefooters[$pname]; }
		else if ($tag=='SETPAGEHEADER') { $this->headerDetails['even'] = array(); }
		else { $this->footerDetails['even'] = array(); }
	}
	if ($attr['SHOW-THIS-PAGE'] && $tag=='SETPAGEHEADER') {
		$this->Header();
	}
	break;


     case 'TOC': //added custom-tag - set Marker for insertion later of ToC
	if ($attr['FONT-SIZE']) { $tocfontsize = $attr['FONT-SIZE']; } else { $tocfontsize = ''; }
	if ($attr['FONT']) { $tocfont = $attr['FONT']; } else { $tocfont = ''; }
	if ($attr['INDENT']) { $tocindent = $attr['INDENT']; } else { $tocindent = ''; }
	if ($attr['RESETPAGENUM']) { $resetpagenum = $attr['RESETPAGENUM']; } else { $resetpagenum = ''; }
	if ($attr['PAGENUMSTYLE']) { $pagenumstyle = $attr['PAGENUMSTYLE']; } else { $pagenumstyle= ''; }
	if ($attr['SUPPRESS']) { $suppress = $attr['SUPPRESS']; } else { $suppress = ''; }
	if ($attr['TOC-ORIENTATION']) { $toc_orientation = $attr['TOC-ORIENTATION']; } else { $toc_orientation = ''; }
	if (isset($attr['PAGING']) && (strtoupper($attr['PAGING'])=='OFF' || $attr['PAGING']==='0')) { $paging = false; }
	else { $paging = true; }
	if (strtoupper($attr['LINKS'])=='ON' || $attr['LINKS']==1) { $links = true; }
	else { $links = false; }
	// mPDF 2.3
	if ($attr['NAME']) { $toc_id = strtolower($attr['NAME']); } else { $toc_id = 0; }
	$this->TOC($tocfont,$tocfontsize,$tocindent,$resetpagenum, $pagenumstyle, $suppress, $toc_orientation, $paging, $links, $toc_id);
	break;



     case 'TOCPAGEBREAK': // custom-tag - set Marker for insertion later of ToC AND adds PAGEBREAK
	// mPDF 2.3
	if ($attr['NAME']) { $toc_id = strtolower($attr['NAME']); } else { $toc_id = 0; }
	if ($toc_id) {
	  if ($attr['FONT-SIZE']) { $this->m_TOC[$toc_id]['TOCfontsize'] = $attr['FONT-SIZE']; } else { $this->m_TOC[$toc_id]['TOCfontsize'] = $this->default_font_size; }
	  if ($attr['FONT']) { $this->m_TOC[$toc_id]['TOCfont'] = $attr['FONT']; } else { $this->m_TOC[$toc_id]['TOCfont'] = $this->default_font; }
	  if ($attr['INDENT']) { $this->m_TOC[$toc_id]['TOCindent'] = $attr['INDENT']; } else { $this->m_TOC[$toc_id]['TOCindent'] = ''; }
	  if ($attr['TOC-ORIENTATION']) { $this->m_TOC[$toc_id]['TOCorientation'] = $attr['TOC-ORIENTATION']; } else { $this->m_TOC[$toc_id]['TOCorientation'] = ''; }
	  if (isset($attr['PAGING']) && (strtoupper($attr['PAGING'])=='OFF' || $attr['PAGING']==='0')) { $this->m_TOC[$toc_id]['TOCusePaging'] = false; }
	  else { $this->m_TOC[$toc_id]['TOCusePaging'] = true; }
	  if (strtoupper($attr['LINKS'])=='ON' || $attr['LINKS']==1) { $this->m_TOC[$toc_id]['TOCuseLinking'] = true; }
	  else { $this->m_TOC[$toc_id]['TOCuseLinking'] = false; }

	  // Added/Edited mPDF 2.0
	  $this->m_TOC[$toc_id]['TOC_margin_left'] = $this->m_TOC[$toc_id]['TOC_margin_right'] = $this->m_TOC[$toc_id]['TOC_margin_top'] = $this->m_TOC[$toc_id]['TOC_margin_bottom'] = $this->m_TOC[$toc_id]['TOC_margin_header'] = $this->m_TOC[$toc_id]['TOC_margin_footer'] = '';
	  if (isset($attr['TOC-MARGIN-RIGHT'])) { $this->m_TOC[$toc_id]['TOC_margin_right'] = ConvertSize($attr['TOC-MARGIN-RIGHT'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-LEFT'])) { $this->m_TOC[$toc_id]['TOC_margin_left'] = ConvertSize($attr['TOC-MARGIN-LEFT'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-TOP'])) { $this->m_TOC[$toc_id]['TOC_margin_top'] = ConvertSize($attr['TOC-MARGIN-TOP'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-BOTTOM'])) { $this->m_TOC[$toc_id]['TOC_margin_bottom'] = ConvertSize($attr['TOC-MARGIN-BOTTOM'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-HEADER'])) { $this->m_TOC[$toc_id]['TOC_margin_header'] = ConvertSize($attr['TOC-MARGIN-HEADER'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-FOOTER'])) { $this->m_TOC[$toc_id]['TOC_margin_footer'] = ConvertSize($attr['TOC-MARGIN-FOOTER'],$this->w,$this->FontSize,false); }
	  // Added mPDF 2.0 for headers and footers
	  $this->m_TOC[$toc_id]['TOC_odd_header_name'] = $this->m_TOC[$toc_id]['TOC_even_header_name'] = $this->m_TOC[$toc_id]['TOC_odd_footer_name'] = $this->m_TOC[$toc_id]['TOC_even_header_value'] = '';
	  if ($attr['TOC-ODD-HEADER-NAME']) { $this->m_TOC[$toc_id]['TOC_odd_header_name'] = $attr['TOC-ODD-HEADER-NAME']; }
	  if ($attr['TOC-EVEN-HEADER-NAME']) { $this->m_TOC[$toc_id]['TOC_even_header_name'] = $attr['TOC-EVEN-HEADER-NAME']; }
	  if ($attr['TOC-ODD-FOOTER-NAME']) { $this->m_TOC[$toc_id]['TOC_odd_footer_name'] = $attr['TOC-ODD-FOOTER-NAME']; }
	  if ($attr['TOC-EVEN-FOOTER-NAME']) { $this->m_TOC[$toc_id]['TOC_even_footer_name'] = $attr['TOC-EVEN-FOOTER-NAME']; }
	  $this->m_TOC[$toc_id]['TOC_odd_header_value'] = $this->m_TOC[$toc_id]['TOC_even_header_value'] = $this->m_TOC[$toc_id]['TOC_odd_footer_value'] = $this->m_TOC[$toc_id]['TOC_even_footer_value'] = 0;
	  if ($attr['TOC-ODD-HEADER-VALUE']=='1' || strtoupper($attr['TOC-ODD-HEADER-VALUE'])=='ON') { $this->m_TOC[$toc_id]['TOC_odd_header_value'] = 1; }
	  else if ($attr['TOC-ODD-HEADER-VALUE']=='-1' || strtoupper($attr['TOC-ODD-HEADER-VALUE'])=='OFF') { $this->m_TOC[$toc_id]['TOC_odd_header_value'] = -1; }
	  if ($attr['TOC-EVEN-HEADER-VALUE']=='1' || strtoupper($attr['TOC-EVEN-HEADER-VALUE'])=='ON') { $this->m_TOC[$toc_id]['TOC_even_header_value'] = 1; }
	  else if ($attr['TOC-EVEN-HEADER-VALUE']=='-1' || strtoupper($attr['TOC-EVEN-HEADER-VALUE'])=='OFF') { $this->m_TOC[$toc_id]['TOC_even_header_value'] = -1; }
	  if ($attr['TOC-ODD-FOOTER-VALUE']=='1' || strtoupper($attr['TOC-ODD-FOOTER-VALUE'])=='ON') { $this->m_TOC[$toc_id]['TOC_odd_footer_value'] = 1; }
	  else if ($attr['TOC-ODD-FOOTER-VALUE']=='-1' || strtoupper($attr['TOC-ODD-FOOTER-VALUE'])=='OFF') { $this->m_TOC[$toc_id]['TOC_odd_footer_value'] = -1; }
	  if ($attr['TOC-EVEN-FOOTER-VALUE']=='1' || strtoupper($attr['TOC-EVEN-FOOTER-VALUE'])=='ON') { $this->m_TOC[$toc_id]['TOC_even_footer_value'] = 1; }
	  else if ($attr['TOC-EVEN-FOOTER-VALUE']=='-1' || strtoupper($attr['TOC-EVEN-FOOTER-VALUE'])=='OFF') { $this->m_TOC[$toc_id]['TOC_even_footer_value'] = -1; }

	  if ($attr['TOC-PREHTML']) { $this->m_TOC[$toc_id]['TOCpreHTML'] = htmlspecialchars_decode($attr['TOC-PREHTML'],ENT_QUOTES); }
	  if ($attr['TOC-POSTHTML']) { $this->m_TOC[$toc_id]['TOCpostHTML'] = htmlspecialchars_decode($attr['TOC-POSTHTML'],ENT_QUOTES); }
	  if ($attr['TOC-BOOKMARKTEXT']) { $this->m_TOC[$toc_id]['TOCbookmarkText'] = $attr['TOC-BOOKMARKTEXT']; }
	}
	else {
	  if ($attr['FONT-SIZE']) { $this->TOCfontsize = $attr['FONT-SIZE']; } else { $this->TOCfontsize = $this->default_font_size; }
	  if ($attr['FONT']) { $this->TOCfont = $attr['FONT']; } else { $this->TOCfont = $this->default_font; }
	  if ($attr['INDENT']) { $this->TOCindent = $attr['INDENT']; } else { $this->TOCindent = ''; }
	  if ($attr['TOC-ORIENTATION']) { $this->TOCorientation = $attr['TOC-ORIENTATION']; } else { $this->TOCorientation = ''; }
	  if (isset($attr['PAGING']) && (strtoupper($attr['PAGING'])=='OFF' || $attr['PAGING']==='0')) { $this->TOCusePaging = false; }
	  else { $this->TOCusePaging = true; }
	  if (strtoupper($attr['LINKS'])=='ON' || $attr['LINKS']==1) { $this->TOCuseLinking = true; }
	  else { $this->TOCuseLinking = false; }

	  // Added/Edited mPDF 2.0
	  $this->TOC_margin_left = $this->TOC_margin_right = $this->TOC_margin_top = $this->TOC_margin_bottom = $this->TOC_margin_header = $this->TOC_margin_footer = '';
	  if (isset($attr['TOC-MARGIN-RIGHT'])) { $this->TOC_margin_right = ConvertSize($attr['TOC-MARGIN-RIGHT'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-LEFT'])) { $this->TOC_margin_left = ConvertSize($attr['TOC-MARGIN-LEFT'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-TOP'])) { $this->TOC_margin_top = ConvertSize($attr['TOC-MARGIN-TOP'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-BOTTOM'])) { $this->TOC_margin_bottom = ConvertSize($attr['TOC-MARGIN-BOTTOM'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-HEADER'])) { $this->TOC_margin_header = ConvertSize($attr['TOC-MARGIN-HEADER'],$this->w,$this->FontSize,false); }
	  if (isset($attr['TOC-MARGIN-FOOTER'])) { $this->TOC_margin_footer = ConvertSize($attr['TOC-MARGIN-FOOTER'],$this->w,$this->FontSize,false); }
	  // Added mPDF 2.0 for headers and footers
	  $this->TOC_odd_header_name = $this->TOC_even_header_name = $this->TOC_odd_footer_name = $this->TOC_even_header_value = '';
	  if ($attr['TOC-ODD-HEADER-NAME']) { $this->TOC_odd_header_name = $attr['TOC-ODD-HEADER-NAME']; }
	  if ($attr['TOC-EVEN-HEADER-NAME']) { $this->TOC_even_header_name = $attr['TOC-EVEN-HEADER-NAME']; }
	  if ($attr['TOC-ODD-FOOTER-NAME']) { $this->TOC_odd_footer_name = $attr['TOC-ODD-FOOTER-NAME']; }
	  if ($attr['TOC-EVEN-FOOTER-NAME']) { $this->TOC_even_footer_name = $attr['TOC-EVEN-FOOTER-NAME']; }
	  $this->TOC_odd_header_value = $this->TOC_even_header_value = $this->TOC_odd_footer_value = $this->TOC_even_footer_value = 0;
	  if ($attr['TOC-ODD-HEADER-VALUE']=='1' || strtoupper($attr['TOC-ODD-HEADER-VALUE'])=='ON') { $this->TOC_odd_header_value = 1; }
	  else if ($attr['TOC-ODD-HEADER-VALUE']=='-1' || strtoupper($attr['TOC-ODD-HEADER-VALUE'])=='OFF') { $this->TOC_odd_header_value = -1; }
	  if ($attr['TOC-EVEN-HEADER-VALUE']=='1' || strtoupper($attr['TOC-EVEN-HEADER-VALUE'])=='ON') { $this->TOC_even_header_value = 1; }
	  else if ($attr['TOC-EVEN-HEADER-VALUE']=='-1' || strtoupper($attr['TOC-EVEN-HEADER-VALUE'])=='OFF') { $this->TOC_even_header_value = -1; }
	  if ($attr['TOC-ODD-FOOTER-VALUE']=='1' || strtoupper($attr['TOC-ODD-FOOTER-VALUE'])=='ON') { $this->TOC_odd_footer_value = 1; }
	  else if ($attr['TOC-ODD-FOOTER-VALUE']=='-1' || strtoupper($attr['TOC-ODD-FOOTER-VALUE'])=='OFF') { $this->TOC_odd_footer_value = -1; }
	  if ($attr['TOC-EVEN-FOOTER-VALUE']=='1' || strtoupper($attr['TOC-EVEN-FOOTER-VALUE'])=='ON') { $this->TOC_even_footer_value = 1; }
	  else if ($attr['TOC-EVEN-FOOTER-VALUE']=='-1' || strtoupper($attr['TOC-EVEN-FOOTER-VALUE'])=='OFF') { $this->TOC_even_footer_value = -1; }

	  if ($attr['TOC-PREHTML']) { $this->TOCpreHTML = htmlspecialchars_decode($attr['TOC-PREHTML'],ENT_QUOTES); }
	  if ($attr['TOC-POSTHTML']) { $this->TOCpostHTML = htmlspecialchars_decode($attr['TOC-POSTHTML'],ENT_QUOTES); }
	  if ($attr['TOC-BOOKMARKTEXT']) { $this->TOCbookmarkText = $attr['TOC-BOOKMARKTEXT']; }
	}
	// mPDF 3.0
	if ($this->y == $this->tMargin && (!$this->useOddEven ||($this->useOddEven && $this->page % 2==1))) { 
		if ($toc_id) { $this->m_TOC[$toc_id]['TOCmark'] = $this->page; }
		else { $this->TOCmark = $this->page; }
		// Don't add a page
		if ($this->page==1 && count($this->PageNumSubstitutions)==0) { 
			$resetpagenum = '';
			$pagenumstyle = '';
			$suppress = '';
			if (isset($attr['RESETPAGENUM'])) { $resetpagenum = $attr['RESETPAGENUM']; }
			if (isset($attr['PAGENUMSTYLE'])) { $pagenumstyle = $attr['PAGENUMSTYLE']; }
			if (isset($attr['SUPPRESS'])) { $suppress = $attr['SUPPRESS']; }
			if (!$suppress) { $suppress = 'off'; }
			if (!$resetpagenum) { $resetpagenum= 1; }
			$this->PageNumSubstitutions[] = array('from'=>1, 'reset'=> $resetpagenum, 'type'=>$pagenumstyle, 'suppress'=> $suppress);
		}
		break;
	}
	// No break - continues as PAGEBREAK...

    case 'PAGE_BREAK': //custom-tag
    case 'PAGEBREAK': //custom-tag
    case 'NEWPAGE': //custom-tag
    case 'FORMFEED': //custom-tag

	// mPDF 2.3
	$save_blklvl = $this->blklvl;
	$save_blk = $this->blk;
	$save_silp = $this->saveInlineProperties();
	$save_spanlvl = $this->spanlvl;
	$save_ilp = $this->InlineProperties;

	// Close any open block tags
	for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
	if(!empty($this->textbuffer))  {	//Output previously buffered content
   	  	$this->printbuffer($this->textbuffer);
        	$this->textbuffer=array(); 
      }
	$this->ignorefollowingspaces = true;
	$save_cols = false;
	if ($this->ColActive) {
		$save_cols = true;
		$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
		$this->SetColumns(0);
	}
	// Added/Edited mPDF 2.0
	$mgr = $mgl = $mgt = $mgb = $mgh = $mgf = '';
	if (isset($attr['MARGIN-RIGHT'])) { $mgr = ConvertSize($attr['MARGIN-RIGHT'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-LEFT'])) { $mgl = ConvertSize($attr['MARGIN-LEFT'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-TOP'])) { $mgt = ConvertSize($attr['MARGIN-TOP'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-BOTTOM'])) { $mgb = ConvertSize($attr['MARGIN-BOTTOM'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-HEADER'])) { $mgh = ConvertSize($attr['MARGIN-HEADER'],$this->w,$this->FontSize,false); }
	if (isset($attr['MARGIN-FOOTER'])) { $mgf = ConvertSize($attr['MARGIN-FOOTER'],$this->w,$this->FontSize,false); }
	// Added mPDF 2.0 for headers and footers
	$ohname = $ehname = $ofname = $efname = '';
	if ($attr['ODD-HEADER-NAME']) { $ohname = $attr['ODD-HEADER-NAME']; }
	if ($attr['EVEN-HEADER-NAME']) { $ehname = $attr['EVEN-HEADER-NAME']; }
	if ($attr['ODD-FOOTER-NAME']) { $ofname = $attr['ODD-FOOTER-NAME']; }
	if ($attr['EVEN-FOOTER-NAME']) { $efname = $attr['EVEN-FOOTER-NAME']; }
	$ohvalue = $ehvalue = $ofvalue = $efvalue = 0;
	if ($attr['ODD-HEADER-VALUE']=='1' || strtoupper($attr['ODD-HEADER-VALUE'])=='ON') { $ohvalue = 1; }
	else if ($attr['ODD-HEADER-VALUE']=='-1' || strtoupper($attr['ODD-HEADER-VALUE'])=='OFF') { $ohvalue = -1; }
	if ($attr['EVEN-HEADER-VALUE']=='1' || strtoupper($attr['EVEN-HEADER-VALUE'])=='ON') { $ehvalue = 1; }
	else if ($attr['EVEN-HEADER-VALUE']=='-1' || strtoupper($attr['EVEN-HEADER-VALUE'])=='OFF') { $ehvalue = -1; }
	if ($attr['ODD-FOOTER-VALUE']=='1' || strtoupper($attr['ODD-FOOTER-VALUE'])=='ON') { $ofvalue = 1; }
	else if ($attr['ODD-FOOTER-VALUE']=='-1' || strtoupper($attr['ODD-FOOTER-VALUE'])=='OFF') { $ofvalue = -1; }
	if ($attr['EVEN-FOOTER-VALUE']=='1' || strtoupper($attr['EVEN-FOOTER-VALUE'])=='ON') { $efvalue = 1; }
	else if ($attr['EVEN-FOOTER-VALUE']=='-1' || strtoupper($attr['EVEN-FOOTER-VALUE'])=='OFF') { $efvalue = -1; }

	if (strtoupper($attr['ORIENTATION'])=='L' || strtoupper($attr['ORIENTATION'])=='LANDSCAPE') { $orient = 'L'; }
	else if (strtoupper($attr['ORIENTATION'])=='P' || strtoupper($attr['ORIENTATION'])=='PORTRAIT') { $orient = 'P'; }
	else { $orient = $this->CurOrientation; }
	// Added/Edited mPDF 1.3
	$resetpagenum = '';
	$pagenumstyle = '';
	$suppress = '';
	if (isset($attr['RESETPAGENUM'])) { $resetpagenum = $attr['RESETPAGENUM']; }
	if (isset($attr['PAGENUMSTYLE'])) { $pagenumstyle = $attr['PAGENUMSTYLE']; }
	if (isset($attr['SUPPRESS'])) { $suppress = $attr['SUPPRESS']; }

	if ($tag == 'TOCPAGEBREAK') { $type = 'NEXT-ODD'; }
	else { $type = strtoupper($attr['TYPE']); }

	if ($type == 'E' || $type == 'EVEN') { $this->AddPage($orient,'E', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue); }
	else if ($type == 'O' || $type == 'ODD') { $this->AddPage($orient,'O', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue); }
	// mPDF 2.2 Use AddPage not AddPages
	else if ($type == 'NEXT-ODD') { $this->AddPage($orient,'NEXT-ODD', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue); }
	else if ($type == 'NEXT-EVEN') { $this->AddPage($orient,'NEXT-EVEN', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue); }
	else { $this->AddPage($orient,'', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue); }

	if ($tag == 'TOCPAGEBREAK') { 
		// mPDF 2.3
		if ($toc_id) { $this->m_TOC[$toc_id]['TOCmark'] = $this->page; }
		else { $this->TOCmark = $this->page; }
	}

	if ($save_cols) {
		// Restore columns
		$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
	}
	// mPDF 2.3
	if (($tag == 'FORMFEED' || $this->restoreBlockPagebreaks) && !$this->tableLevel && !$this->listlvl) {
		$this->blk = $save_blk;
		// Close any open block tags
		$t = $this->blk[0]['tag'];
		$a = $this->blk[0]['attr'];
		$this->blklvl = 0; 
		for ($b=0; $b<=$save_blklvl;$b++) {
			$tc = $t;
			$ac = $a;
			$t = $this->blk[$b+1]['tag'];
			$a = $this->blk[$b+1]['attr'];
			unset($this->blk[$b+1]);
			$this->OpenTag($tc,$ac); 
		}
		$this->spanlvl = $save_spanlvl;
		$this->InlineProperties = $save_ilp;
		$this->restoreInlineProperties($save_silp);
	}

	break;


     case 'TOCENTRY':
	if ($attr['CONTENT']) {
		if ($attr['LEVEL']) { $toclevel = $attr['LEVEL']; } else { $toclevel = 0; }
		// mPDF 2.3
		if ($attr['NAME']) { $toc_id = strtolower($attr['NAME']); } else { $toc_id = 0; }
	//	$this->TOC_Entry(htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES),$toclevel, $toc_id);
		// mPDF 3.0
		$objattr = array();
		$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES);
		$objattr['type'] = 'toc';
		if ($attr['LEVEL']) { $objattr['toclevel'] = $attr['LEVEL']; } else { $objattr['toclevel'] = 0; }
		if ($attr['NAME']) { $objattr['toc_id'] = $attr['NAME']; } else { $objattr['toc_id'] = 0; }
		$e = "\xbb\xa4\xactype=toc,objattr=".serialize($objattr)."\xbb\xa4\xac";
		if($this->tableLevel) {
			$this->cell[$this->row][$this->col]['textbuffer'][] = array($e);
		}
		else  {
			$this->textbuffer[] = array($e);
		}
	}
	break;

     case 'INDEXENTRY':
	if ($attr['CONTENT']) {
		// mPDF 3.0 (changed Reference() to IndexEntry() )
	//	$this->IndexEntry(htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES));
		// mPDF 3.0
		$objattr = array();
		$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES);
		$objattr['type'] = 'indexentry';
		$e = "\xbb\xa4\xactype=indexentry,objattr=".serialize($objattr)."\xbb\xa4\xac";
		if($this->tableLevel) {
			$this->cell[$this->row][$this->col]['textbuffer'][] = array($e);
		}
		else  {
			$this->textbuffer[] = array($e);
		}
	}
	break;

     case 'BOOKMARK':
	if ($attr['CONTENT']) {
		if ($attr['LEVEL']) { $bklevel = $attr['LEVEL']; } else { $bklevel = 0; }
	//	$this->Bookmark(htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES),$bklevel,'-1');
		// mPDF 3.0
		$objattr = array();
		$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES);
		$objattr['type'] = 'bookmark';
		if ($attr['LEVEL']) { $objattr['bklevel'] = $attr['LEVEL']; } else { $objattr['bklevel'] = 0; }
		$e = "\xbb\xa4\xactype=bookmark,objattr=".serialize($objattr)."\xbb\xa4\xac";
		if($this->tableLevel) {
			$this->cell[$this->row][$this->col]['textbuffer'][] = array($e);
		}
		else  {
			$this->textbuffer[] = array($e);
		}
	}
	break;

     // mPDF 2.2 Annotations
     case 'ANNOTATION':
	if ($attr['CONTENT']) {
		$objattr = array();
		// mPDF 2.3
		$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'],ENT_QUOTES);
		$objattr['type'] = 'annot';
	}
	else { break; }
	if ($attr['POS-X']) { $objattr['POS-X'] = $attr['POS-X']; } else { $objattr['POS-X'] = 0; }
	if ($attr['POS-Y']) { $objattr['POS-Y'] = $attr['POS-Y']; } else { $objattr['POS-Y'] = 0; }
	if ($attr['ICON']) { $objattr['ICON'] = $attr['ICON']; } else { $objattr['ICON'] = 'Note'; }
	if ($attr['AUTHOR']) { $objattr['AUTHOR'] = $attr['AUTHOR']; } else  { $objattr['AUTHOR'] = ''; }
	if ($attr['SUBJECT']) { $objattr['SUBJECT'] = $attr['SUBJECT']; } else  { $objattr['SUBJECT'] = ''; }
	if ($attr['OPACITY']>0 && $attr['OPACITY']<=1) { $objattr['OPACITY'] = $attr['OPACITY']; } 
	else if ($this->annotMargin) { $objattr['OPACITY'] = 1; }
	else { $objattr['OPACITY'] = $this->annotOpacity; }
	if ($attr['COLOR']) { 
		$cor = ConvertColor($attr['COLOR']);
		if ($cor) {  $objattr['COLOR'] = array($cor['R'],$cor['G'],$cor['B']); }
		else  { $objattr['COLOR'] = array(255,255,0); }
	} 
	else  { $objattr['COLOR'] = array(255,255,0); }
	$e = "\xbb\xa4\xactype=annot,objattr=".serialize($objattr)."\xbb\xa4\xac";
	if($this->tableLevel) {
		$this->cell[$this->row][$this->col]['textbuffer'][] = array($e);
	}
	else  {
		$this->textbuffer[] = array($e);
	}
	break;


    case 'COLUMNS': //added custom-tag
	if ($attr['COLUMN-COUNT'] || $attr['COLUMN-COUNT']==='0') {
		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
		// mPDF 2.0
		if(!empty($this->textbuffer))  {	//Output previously buffered content
    		  	$this->printbuffer($this->textbuffer);
      	  	$this->textbuffer=array(); 
      	}

		if ($attr['VALIGN']) { 
			if ($attr['VALIGN'] == 'J') { $valign = 'J'; }
			else { $valign = $align[$attr['VALIGN']]; }
		}
 		else { $valign = ''; }
		if ($attr['COLUMN-GAP']) { $this->SetColumns($attr['COLUMN-COUNT'],$valign,$attr['COLUMN-GAP']); }
		else { $this->SetColumns($attr['COLUMN-COUNT'],$valign); }
	}
	$this->ignorefollowingspaces = true;
	break;

    case 'COLUMN_BREAK': //custom-tag
    case 'COLUMNBREAK': //custom-tag
    case 'NEWCOLUMN': //custom-tag
	$this->ignorefollowingspaces = true;
	$this->NewColumn();
	$this->ColumnAdjust = false;	// disables all column height adjustment for the page.
	break;



    case 'BDO':
	// mPDF 2.2 - variable name changed to lowercase first letter
	$this->biDirectional = true;
	break;


    case 'TTZ':
	$this->ttz = true;
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$this->setCSS(array('FONT-FAMILY'=>'zapfdingbats','FONT-WEIGHT'=>'normal','FONT-STYLE'=>'normal'),'INLINE');
	break;

    case 'TTS':
	$this->tts = true;
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$this->setCSS(array('FONT-FAMILY'=>'symbol','FONT-WEIGHT'=>'normal','FONT-STYLE'=>'normal'),'INLINE');
	break;

    case 'TTA':
	$this->tta = true;
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$this->setCSS(array('FONT-FAMILY'=>'helvetica-embedded','FONT-WEIGHT'=>'normal','FONT-STYLE'=>'normal'),'INLINE');
	break;



    // INLINE PHRASES OR STYLES
    case 'SUB':
    case 'SUP':
    case 'ACRONYM':
    case 'BIG':
    case 'SMALL':
    case 'INS':
    case 'S':
    case 'STRIKE':
    case 'DEL':
    case 'STRONG':
    case 'CITE':
    case 'Q':
    case 'EM':
    case 'B':
    case 'I':
    case 'U':
    case 'SAMP':
    case 'CODE':
    case 'KBD':
    case 'TT':
    case 'VAR':
    case 'FONT':
    case 'SPAN':
	// mPDF 2.2 Annotations
	if ($this->title2annots && $attr['TITLE']) {
		$objattr = array();
		$objattr['CONTENT'] = $attr['TITLE'];
		$objattr['type'] = 'annot';
		$objattr['POS-X'] = 0;
		$objattr['POS-Y'] = 0;
		$objattr['ICON'] = 'Comment';
		$objattr['AUTHOR'] = '';
		$objattr['SUBJECT'] = '';
		$objattr['OPACITY'] = $this->annotOpacity; 
		$objattr['COLOR'] = array(255,255,0); 
		$annot = "\xbb\xa4\xactype=annot,objattr=".serialize($objattr)."\xbb\xa4\xac";
	}

	if ($tag == 'SPAN') {
		$this->spanlvl++;
		$this->InlineProperties['SPAN'][$this->spanlvl] = $this->saveInlineProperties();
		// mPDF 2.2 Annotations
		if ($annot) { $this->InlineAnnots[$tag][$this->spanlvl] = $annot; }
	}
	else { 
		$this->InlineProperties[$tag] = $this->saveInlineProperties(); 
		// mPDF 2.2 Annotations
		if ($annot) { $this->InlineAnnots[$tag] = $annot; }
	}


	$properties = $this->MergeCSS('',$tag,$attr);
	if (!empty($properties)) $this->setCSS($properties,'INLINE');
	break;


    case 'A':
	if (isset($attr['NAME']) and $attr['NAME'] != '') { 
		// mPDF 3.0
		$e = '';
		if ($this->anchor2Bookmark) { 
			$objattr = array();
			$objattr['CONTENT'] = htmlspecialchars_decode($attr['NAME'],ENT_QUOTES);
			$objattr['type'] = 'bookmark';
			if ($attr['LEVEL']) { $objattr['bklevel'] = $attr['LEVEL']; } else { $objattr['bklevel'] = 0; }
			$e = "\xbb\xa4\xactype=bookmark,objattr=".serialize($objattr)."\xbb\xa4\xac";
		}
		if($this->tableLevel) {
			$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,'','',array(),'',false,false,$attr['NAME']); //an internal link (adds a space for recognition)

		}
		else  {
			$this->textbuffer[] = array($e,'','',array(),'',false,false,$attr['NAME']); //an internal link (adds a space for recognition)
		}
	}
	if (isset($attr['HREF'])) { 
		$this->InlineProperties['A'] = $this->saveInlineProperties();
		$properties = $this->MergeCSS('',$tag,$attr);
		if (!empty($properties)) $this->setCSS($properties,'INLINE');
		$this->HREF=$attr['HREF'];
	}
	break;



    case 'BR':
	// Added mPDF 3.0 Float DIV - CLEAR
	if (isset($attr['STYLE'])) {
		$properties = $this->readInlineCSS($attr['STYLE']);
		if (isset($properties['CLEAR'])) { $this->ClearFloats(strtoupper($properties['CLEAR']),$this->blklvl); }
	}


	if($this->tableLevel) {
	   // mPDF 3.0
	   if ($this->blockjustfinished || $this->listjustfinished) {
		$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['text'][] = "\n";
	   }

		$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['text'][] = "\n";
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];  
		}
		$this->cell[$this->row][$this->col]['s'] = 0 ;// reset
	}
	else  {
		$this->textbuffer[] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}
	$this->ignorefollowingspaces = true; 
	$this->blockjustfinished=false;
	$this->listjustfinished=false;
	// mPDF 3.0
	$this->linebreakjustfinished=true;
	break;


	// *********** BLOCKS  ********************

	//NB $outerblocktags = array('DIV','FORM','CENTER','DL');
	//NB $innerblocktags = array('P','BLOCKQUOTE','ADDRESS','PRE',''H1','H2','H3','H4','H5','H6','DT','DD');

    case 'PRE':
	$this->ispre=true;	// ADDED - Prevents left trim of textbuffer in printbuffer()

    case 'DIV':
    case 'FORM':
    case 'CENTER':

    case 'BLOCKQUOTE':
    case 'ADDRESS': 

    case 'P':
    case 'H1':
    case 'H2':
    case 'H3':
    case 'H4':
    case 'H5':
    case 'H6':
    case 'DL':
    case 'DT':
    case 'DD':


	// mPDF 2.1 DISPLAY NONE
	$p = $this->PreviewBlockCSS($tag,$attr);
	if(strtolower($p['DISPLAY'])=='none') { 
		$this->blklvl++;
		$this->blk[$this->blklvl]['hide'] = true; 
		return; 
	}

	// Start Block
	$this->InlineProperties = array(); 
	$this->spanlvl = 0;
	$this->ignorefollowingspaces = true; 
	$this->blockjustfinished=false;
	$this->listjustfinished=false;
	$this->divbegin=true;
	// mPDF 3.0
	$this->linebreakjustfinished=false;

	if ($this->tableLevel) {
	   // mPDF 3.0
	   // If already something on the line
	   if ($this->cell[$this->row][$this->col]['s'] > 0  && !$this->nestedtablejustfinished ) {
		$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['text'][] = "\n";
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		$this->cell[$this->row][$this->col]['s'] = 0 ;// reset
	   }
	   // Cannot set block properties inside table - use Bold to indicate h1-h6
	   if ($tag == 'CENTER' && $this->tdbegin) { $this->cell[$this->row][$this->col]['a'] = $align['center']; }

		$this->InlineProperties['BLOCKINTABLE'] = $this->saveInlineProperties();
		$properties = $this->MergeCSS('',$tag,$attr);
		if (!empty($properties)) $this->setCSS($properties,'INLINE');


	   break;
	}

	if ($tag == 'P' || $tag == 'DT' || $tag == 'DD') { $this->lastoptionaltag = $tag; } // Save current HTML specified optional endtag
	else { $this->lastoptionaltag = ''; }


	if ($this->lastblocklevelchange == 1) { $blockstate = 1; }	// Top margins/padding only
	else if ($this->lastblocklevelchange < 1) { $blockstate = 0; }	// NO margins/padding
	$this->printbuffer($this->textbuffer,$blockstate);
	$this->textbuffer=array();

	$this->blklvl++;
	$this->blk[$this->blklvl]['tag'] = $tag;
	// mPDF 2.3
	$this->blk[$this->blklvl]['attr'] = $attr;

	$this->Reset();
	$properties = $this->MergeCSS('BLOCK',$tag,$attr);

	// mPDF 2.0 Paged media (page-box)
	if ($properties['PAGE']) { $pmtag = $properties['PAGE']; } else { $pmtag = ''; }
	$pm = $this->GetPagedMediaCSS($pmtag);

	$this->page_box['last_name'] = $this->page_box['name'] ;
	$this->page_box['name'] = $pmtag;

	// If page-box has changed AND/OR PAGE-BREAK-BEFORE
	$save_cols = false;
	if (($pmtag && $this->page_box['last_name'] != $this->page_box['name']) || $properties['PAGE-BREAK-BEFORE']) {
		if ($this->blklvl>1) {
			// Close any open block tags
			for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
			// Output any text left in buffer
			if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); $this->textbuffer=array(); }
		}
		if ($this->ColActive) {
			$save_cols = true;
			$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
			$this->SetColumns(0);
		}

		if ($pmtag && $this->page_box['last_name'] != $this->page_box['name']) {
			$this->page_box['changed'] = true;
			// Set orientation and page size/margins
			$this->page_box['CSS'] = $pm;
		}
		// Must Add new page if changed page properties
		// mPDF 2.2 Use AddPage not AddPages
		if (strtoupper($properties['PAGE-BREAK-BEFORE']) == 'RIGHT') { $this->AddPage($this->CurOrientation,'NEXT-ODD'); }
		else if (strtoupper($properties['PAGE-BREAK-BEFORE']) == 'LEFT') { $this->AddPage($this->CurOrientation,'NEXT-EVEN'); }
		else if (strtoupper($properties['PAGE-BREAK-BEFORE']) == 'ALWAYS') { $this->AddPage(); }
		else if ($this->page_box['changed']) { $this->AddPage(); }
		$this->page_box['changed'] = false;	// Also Set back to false in AddPage

		// if using htmlheaders, the headers need to be rewritten when new page
		// done by calling writeHTML() within resethtmlheaders
		// so block is reset to 0 - now we need to resurrect it
		// As in writeHTML() initialising
		$this->blklvl = 0;
		$this->lastblocklevelchange = 0;
		$this->blk = array();
		$this->blk[0]['width'] =& $this->pgwidth;
		$this->blk[0]['inner_width'] =& $this->pgwidth;
		$properties = $this->MergeCSS('BLOCK','BODY','');
		$this->setCSS($properties,'','BODY'); 
		$this->blklvl++;
		$this->blk[$this->blklvl]['tag'] = $tag;
		$this->Reset();
		$properties = $this->MergeCSS('BLOCK',$tag,$attr);
		if ($save_cols) {
			// Restore columns
			$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
		}
	}


	// Added mPDF 1.1 keeping block together on one page
	if (strtoupper($properties['PAGE-BREAK-INSIDE']) == 'AVOID' && !$this->ColActive && !$this->keep_block_together) {
		$this->blk[$this->blklvl]['keep_block_together'] = 1;
		$this->blk[$this->blklvl]['y00'] = $this->y;
		$this->keep_block_together = 1;
		$this->divbuffer = array();
		$this->ktLinks = array();
		// mPDF 2.2 Annotations
		$this->ktAnnots = array();
		$this->ktBlock = array();
		$this->ktReference = array();
		$this->ktBMoutlines = array();
		$this->_kttoc = array();
	}

	$this->setCSS($properties,'BLOCK',$tag); //name(id/class/style) found in the CSS array!
	$this->blk[$this->blklvl]['InlineProperties'] = $this->saveInlineProperties();


	if(isset($attr['ALIGN'])) { $this->blk[$this->blklvl]['block-align'] = $align[strtolower($attr['ALIGN'])]; }

	// Added mPDF 3.0 Float DIV
	$this->blk[$this->blklvl]['blockContext'] = $this->blk[$this->blklvl-1]['blockContext'] ;
	if (isset($properties['CLEAR'])) { $this->ClearFloats(strtoupper($properties['CLEAR']), $this->blklvl-1); }

	$container_w = $this->blk[$this->blklvl-1]['inner_width'];
	$bdr = $this->blk[$this->blklvl]['border_right']['w'];
	$bdl = $this->blk[$this->blklvl]['border_left']['w'];
	$pdr = $this->blk[$this->blklvl]['padding_right'];
	$pdl = $this->blk[$this->blklvl]['padding_left'];

	if (strtoupper($properties['FLOAT']) == 'RIGHT' && !$this->ColActive) {
		// Cancel Keep-Block-together
		$this->blk[$this->blklvl]['keep_block_together'] = false;
		$this->blk[$this->blklvl]['y00'] = '';
		$this->keep_block_together = 0;

		$this->blockContext++;
		$this->blk[$this->blklvl]['blockContext'] = $this->blockContext;

		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);

		// DIV is too narrow for text to fit!
		$maxw = $container_w - $l_width - $r_width;
		if (($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr) > $maxw || ($maxw - ($this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr)) < $this->getStringWidth('WW')) { 
			// Too narrow to fit - try to move down past L or R float
			if ($l_max < $r_max && ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width) && (($container_w - $r_width) - ($this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > $this->getStringWidth('WW')) {
				$this->ClearFloats('LEFT', $this->blklvl-1); 
			}
			else if ($r_max < $l_max && ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr)  <= ($container_w - $l_width) && (($container_w - $l_width) - ($this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > $this->getStringWidth('WW')) {
				$this->ClearFloats('RIGHT', $this->blklvl-1); 
			}
			else { $this->ClearFloats('BOTH', $this->blklvl-1); }
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);
		}

		if ($r_exists) { $this->blk[$this->blklvl]['margin_right'] += $r_width; }

		$this->blk[$this->blklvl]['float'] = 'R';
		$this->blk[$this->blklvl]['float_start_y'] = $this->y;
		if ($this->blk[$this->blklvl]['css_set_width']) {
			$this->blk[$this->blklvl]['margin_left'] = $container_w - ($this->blk[$this->blklvl]['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $this->blk[$this->blklvl]['margin_right']);
			$this->blk[$this->blklvl]['float_width'] = ($this->blk[$this->blklvl]['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $this->blk[$this->blklvl]['margin_right']);
		}
		else {
			// *** If no width set - would need to buffer and keep track of max width, then Right-align if not full width
			// and do borders and backgrounds - For now - just set to maximum width left

			if ($l_exists) { $this->blk[$this->blklvl]['margin_left'] += $l_width; }
			$this->blk[$this->blklvl]['css_set_width'] = $container_w - ($this->blk[$this->blklvl]['margin_left'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr);

			$this->blk[$this->blklvl]['float_width'] = ($this->blk[$this->blklvl]['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $this->blk[$this->blklvl]['margin_right']);
		}
	}
	else if (strtoupper($properties['FLOAT']) == 'LEFT' && !$this->ColActive) {
		// Cancel Keep-Block-together
		$this->blk[$this->blklvl]['keep_block_together'] = false;
		$this->blk[$this->blklvl]['y00'] = '';
		$this->keep_block_together = 0;

		$this->blockContext++;
		$this->blk[$this->blklvl]['blockContext'] = $this->blockContext;

		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);

		// DIV is too narrow for text to fit!
		$maxw = $container_w - $l_width - $r_width;
		if (($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['margin_left'] + $bdl + $pdl + $bdr + $pdr) > $maxw || ($maxw - ($this->blk[$this->blklvl]['margin_left'] + $bdl + $pdl + $bdr + $pdr)) < $this->getStringWidth('WW')) { 
			// Too narrow to fit - try to move down past L or R float
			if ($l_max < $r_max && ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width) && (($container_w - $r_width) - ($this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > $this->getStringWidth('WW')) {
				$this->ClearFloats('LEFT', $this->blklvl-1); 
			}
			else if ($r_max < $l_max && ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width) && (($container_w - $l_width) - ($this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > $this->getStringWidth('WW')) {
				$this->ClearFloats('RIGHT', $this->blklvl-1); 
			}
			else { $this->ClearFloats('BOTH', $this->blklvl-1); }
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);
		}

		if ($l_exists) { $this->blk[$this->blklvl]['margin_left'] += $l_width; }

		$this->blk[$this->blklvl]['float'] = 'L';
		$this->blk[$this->blklvl]['float_start_y'] = $this->y;
		if ($this->blk[$this->blklvl]['css_set_width']) {
			$this->blk[$this->blklvl]['margin_right'] = $container_w - ($this->blk[$this->blklvl]['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $this->blk[$this->blklvl]['margin_left']);
			$this->blk[$this->blklvl]['float_width'] = ($this->blk[$this->blklvl]['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $this->blk[$this->blklvl]['margin_left']);
		}
		else {
			// *** If no width set - would need to buffer and keep track of max width, then Right-align if not full width
			// and do borders and backgrounds - For now - just set to maximum width left

			if ($r_exists) { $this->blk[$this->blklvl]['margin_right'] += $r_width; }
			$this->blk[$this->blklvl]['css_set_width'] = $container_w - ($this->blk[$this->blklvl]['margin_left'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr);

			$this->blk[$this->blklvl]['float_width'] = ($this->blk[$this->blklvl]['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $this->blk[$this->blklvl]['margin_left']);
		}
	}

	else {
		// Don't allow overlap - if floats present - adjust padding to avoid overlap with Floats
		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);
		$maxw = $container_w - $l_width - $r_width;
		if (($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['margin_left'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr) > $maxw || ($maxw - ($this->blk[$this->blklvl]['margin_right'] + $this->blk[$this->blklvl]['margin_left'] + $bdl + $pdl + $bdr + $pdr)) < $this->getStringWidth('WW')) { 
			// Too narrow to fit - try to move down past L or R float
			if ($l_max < $r_max && ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['margin_left'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width) && (($container_w - $r_width) - ($this->blk[$this->blklvl]['margin_right'] + $this->blk[$this->blklvl]['margin_left'] + $bdl + $pdl + $bdr + $pdr)) > $this->getStringWidth('WW')) {
				$this->ClearFloats('LEFT', $this->blklvl-1); 
			}
			else if ($r_max < $l_max && ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['margin_left'] + $this->blk[$this->blklvl]['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width) && (($container_w - $l_width) - ($this->blk[$this->blklvl]['margin_right'] + $this->blk[$this->blklvl]['margin_left'] + $bdl + $pdl + $bdr + $pdr)) > $this->getStringWidth('WW')) {
				$this->ClearFloats('RIGHT', $this->blklvl-1); 
			}
			else { $this->ClearFloats('BOTH', $this->blklvl-1); }
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl-1);
		}
		if ($r_exists) { $this->blk[$this->blklvl]['padding_right'] = max(($r_width-$this->blk[$this->blklvl]['margin_right']-$bdr), $pdr); }
		if ($l_exists) { $this->blk[$this->blklvl]['padding_left'] = max(($l_width-$this->blk[$this->blklvl]['margin_left']-$bdl), $pdl); }
	}


	// mPDF 3.0
	// Automatically increase padding if required for border-radius
	if ($this->autoPadding && !$this->ColActive && !$this->keep_block_together) {
	  if ($this->blk[$this->blklvl]['border_radius_TL_H']>$this->blk[$this->blklvl]['padding_left'] && $this->blk[$this->blklvl]['border_radius_TL_V']>$this->blk[$this->blklvl]['padding_top']) {
		if ($this->blk[$this->blklvl]['border_radius_TL_H']>$this->blk[$this->blklvl]['border_radius_TL_V']) {
			$this->_borderPadding($this->blk[$this->blklvl]['border_radius_TL_H'],$this->blk[$this->blklvl]['border_radius_TL_V'], $this->blk[$this->blklvl]['padding_left'], $this->blk[$this->blklvl]['padding_top']);
		}
		else {
			$this->_borderPadding($this->blk[$this->blklvl]['border_radius_TL_V'],$this->blk[$this->blklvl]['border_radius_TL_H'], $this->blk[$this->blklvl]['padding_top'], $this->blk[$this->blklvl]['padding_left']);
		}
	  }
	  if ($this->blk[$this->blklvl]['border_radius_TR_H']>$this->blk[$this->blklvl]['padding_right'] && $this->blk[$this->blklvl]['border_radius_TR_V']>$this->blk[$this->blklvl]['padding_top']) {
		if ($this->blk[$this->blklvl]['border_radius_TR_H']>$this->blk[$this->blklvl]['border_radius_TR_V']) {
			$this->_borderPadding($this->blk[$this->blklvl]['border_radius_TR_H'],$this->blk[$this->blklvl]['border_radius_TR_V'], $this->blk[$this->blklvl]['padding_right'], $this->blk[$this->blklvl]['padding_top']);
		}
		else {
			$this->_borderPadding($this->blk[$this->blklvl]['border_radius_TR_V'],$this->blk[$this->blklvl]['border_radius_TR_H'], $this->blk[$this->blklvl]['padding_top'], $this->blk[$this->blklvl]['padding_right']);
		}
	  }
	  if ($this->blk[$this->blklvl]['border_radius_BL_H']>$this->blk[$this->blklvl]['padding_left'] && $this->blk[$this->blklvl]['border_radius_BL_V']>$this->blk[$this->blklvl]['padding_bottom']) {
		if ($this->blk[$this->blklvl]['border_radius_BL_H']>$this->blk[$this->blklvl]['border_radius_BL_V']) {
			$this->_borderPadding($this->blk[$this->blklvl]['border_radius_BL_H'],$this->blk[$this->blklvl]['border_radius_BL_V'], $this->blk[$this->blklvl]['padding_left'], $this->blk[$this->blklvl]['padding_bottom']);
		}
		else {
			$this->_borderPadding($this->blk[$this->blklvl]['border_radius_BL_V'],$this->blk[$this->blklvl]['border_radius_BL_H'], $this->blk[$this->blklvl]['padding_bottom'], $this->blk[$this->blklvl]['padding_left']);
		}
	  }
	  if ($this->blk[$this->blklvl]['border_radius_BR_H']>$this->blk[$this->blklvl]['padding_right'] && $this->blk[$this->blklvl]['border_radius_BR_V']>$this->blk[$this->blklvl]['padding_bottom']) {
		if ($this->blk[$this->blklvl]['border_radius_BR_H']>$this->blk[$this->blklvl]['border_radius_BR_V']) {
			$this->_borderPadding($this->blk[$this->blklvl]['border_radius_BR_H'],$this->blk[$this->blklvl]['border_radius_BR_V'], $this->blk[$this->blklvl]['padding_right'], $this->blk[$this->blklvl]['padding_bottom']);
		}
		else {
			$this->_borderPadding($this->blk[$this->blklvl]['border_radius_BR_V'],$this->blk[$this->blklvl]['border_radius_BR_H'], $this->blk[$this->blklvl]['padding_bottom'], $this->blk[$this->blklvl]['padding_right']);
		}
	  }
	}


	// Hanging indent - if negative indent: ensure padding is >= indent
	if ($this->blk[$this->blklvl]['text_indent'] < 0) {
	  $hangind = -($this->blk[$this->blklvl]['text_indent']);
	  if ($this->directionality == 'rtl') {
		$this->blk[$this->blklvl]['padding_right'] = max($this->blk[$this->blklvl]['padding_right'],$hangind);
	  }
	  else {
		$this->blk[$this->blklvl]['padding_left'] = max($this->blk[$this->blklvl]['padding_left'],$hangind);
	  }
	}

	// mPDF 2.2 rewritten for margin:auto
	if ($this->blk[$this->blklvl]['css_set_width']) {
	  if (strtolower($properties['MARGIN-LEFT'])=='auto' && strtolower($properties['MARGIN-RIGHT'])=='auto') { 
		  // Try to reduce margins to accomodate - if still too wide, set margin-right/left=0 (reduces width)
		  $anyextra = $this->blk[$this->blklvl-1]['inner_width'] - ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right']);
		  if ($anyextra>0) {
			$this->blk[$this->blklvl]['margin_left'] = $this->blk[$this->blklvl]['margin_right'] = $anyextra /2;
		  }
		  else {
			$this->blk[$this->blklvl]['margin_left'] = $this->blk[$this->blklvl]['margin_right'] = 0;
		  }
	  }
	  else if (strtolower($properties['MARGIN-LEFT'])=='auto') { 
		  // Try to reduce margin-left to accomodate - if still too wide, set margin-left=0 (reduces width)
		  $this->blk[$this->blklvl]['margin_left'] = $this->blk[$this->blklvl-1]['inner_width'] - ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['margin_right']);
		  if ($this->blk[$this->blklvl]['margin_left'] < 0) {
			$this->blk[$this->blklvl]['margin_left'] = 0;
		  }
	  }
	  else if (strtolower($properties['MARGIN-RIGHT'])=='auto') { 
		  // Try to reduce margin-right to accomodate - if still too wide, set margin-right=0 (reduces width)
		  $this->blk[$this->blklvl]['margin_right'] = $this->blk[$this->blklvl-1]['inner_width'] - ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['margin_left']);
		  if ($this->blk[$this->blklvl]['margin_right'] < 0) {
			$this->blk[$this->blklvl]['margin_right'] = 0;
		  }
	  }
	  else { 
	    if ($this->directionality == 'rtl') {
		// Try to reduce margin-left to accomodate - if still too wide, set margin-left=0 (reduces width)
		$this->blk[$this->blklvl]['margin_left'] = $this->blk[$this->blklvl-1]['inner_width'] - ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['margin_right']);
		if ($this->blk[$this->blklvl]['margin_left'] < 0) {
			$this->blk[$this->blklvl]['margin_left'] = 0;
		}
	    }
	    else {
		  // Try to reduce margin-right to accomodate - if still too wide, set margin-right=0 (reduces width)
		  $this->blk[$this->blklvl]['margin_right'] = $this->blk[$this->blklvl-1]['inner_width'] - ($this->blk[$this->blklvl]['css_set_width'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['margin_left']);
		  if ($this->blk[$this->blklvl]['margin_right'] < 0) {
			$this->blk[$this->blklvl]['margin_right'] = 0;
		  }
	    }
	  }
	}


	$this->blk[$this->blklvl]['outer_left_margin'] = $this->blk[$this->blklvl-1]['outer_left_margin'] + $this->blk[$this->blklvl]['margin_left'] + $this->blk[$this->blklvl-1]['border_left']['w'] + $this->blk[$this->blklvl-1]['padding_left'];
	$this->blk[$this->blklvl]['outer_right_margin'] = $this->blk[$this->blklvl-1]['outer_right_margin']  + $this->blk[$this->blklvl]['margin_right'] + $this->blk[$this->blklvl-1]['border_right']['w'] + $this->blk[$this->blklvl-1]['padding_right'];

	$this->blk[$this->blklvl]['width'] = $this->pgwidth - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['outer_left_margin']);
	$this->blk[$this->blklvl]['inner_width'] = $this->blk[$this->blklvl]['width'] - ($this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right']);

	// Check DIV is not now too narrow to fit text
	$mw = $this->getStringWidth('WW');
	if ($this->blk[$this->blklvl]['inner_width'] < $mw) {
		$this->blk[$this->blklvl]['padding_left'] = 0;
		$this->blk[$this->blklvl]['padding_right'] = 0;
		$this->blk[$this->blklvl]['border_left']['w'] = 0.2;
		$this->blk[$this->blklvl]['border_right']['w'] = 0.2;
		$this->blk[$this->blklvl]['margin_left'] = 0;
		$this->blk[$this->blklvl]['margin_right'] = 0;
		$this->blk[$this->blklvl]['outer_left_margin'] = $this->blk[$this->blklvl-1]['outer_left_margin'] + $this->blk[$this->blklvl]['margin_left'] + $this->blk[$this->blklvl-1]['border_left']['w'] + $this->blk[$this->blklvl-1]['padding_left'];
		$this->blk[$this->blklvl]['outer_right_margin'] = $this->blk[$this->blklvl-1]['outer_right_margin']  + $this->blk[$this->blklvl]['margin_right'] + $this->blk[$this->blklvl-1]['border_right']['w'] + $this->blk[$this->blklvl-1]['padding_right'];
		$this->blk[$this->blklvl]['width'] = $this->pgwidth - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['outer_left_margin']);
		$this->blk[$this->blklvl]['inner_width'] = $this->pgwidth - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right']);
		if ($this->blk[$this->blklvl]['inner_width'] < $mw) { die("DIV is too narrow for text to fit!"); }
	}

	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

	// mPDF 3.0 - Tiling Patterns
	if ($properties['BACKGROUND-IMAGE'] && !$this->kwt && !$this->ColActive && !$this->keep_block_together) { 
		$file = $properties['BACKGROUND-IMAGE'];
		$sizesarray = $this->Image($file,0,0,0,0,'','',false);
		if (isset($sizesarray['IMAGE_ID'])) {
			$image_id = $sizesarray['IMAGE_ID'];
			$orig_w = $sizesarray['WIDTH']*$this->k;		// in user units i.e. mm
 			$orig_h = $sizesarray['HEIGHT']*$this->k;		// (using $this->img_dpi)
			$x_repeat = true;
			$y_repeat = true;
			if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-x') { $y_repeat = false; }
			if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-y') { $x_repeat = false; }
			$x_pos = 0;
			$y_pos = 0;
			if ($properties['BACKGROUND-POSITION']) { 
				$ppos = preg_split('/\s+/',$properties['BACKGROUND-POSITION']);
				$x_pos = $ppos[0];
				$y_pos = $ppos[1];
				if (!stristr($x_pos ,'%') ) { $x_pos = ConvertSize($x_pos ,$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
				if (!stristr($y_pos ,'%') ) { $y_pos = ConvertSize($y_pos ,$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
			}
			$this->blk[$this->blklvl]['background-image'] = array('image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat);
		}
	}

	// Added mPDF 2.0 keeping heading together with following table
	if ($this->use_kwt && $attr['KEEP-WITH-TABLE'] && !$this->ColActive && !$this->keep_block_together) {
		$this->kwt = true;
		$this->kwt_y0 = $this->y;
		$this->kwt_x0 = $this->x;
		$this->kwt_height = 0;
		$this->kwt_buffer = array();
		$this->kwt_Links = array();
		// mPDF 2.2 Annotations
		$this->kwt_Annots = array();
		$this->kwt_moved = false;
		$this->kwt_saved = false;
		// mPDF 3.0
		$this->kwt_Reference = array();
		$this->kwt_BMoutlines = array();
		$this->kwt_toc = array();
	}
	else { $this->kwt = false; }

	//Save x,y coords in case we need to print borders...
	$this->blk[$this->blklvl]['y0'] = $this->y;
	$this->blk[$this->blklvl]['x0'] = $this->x;
	$this->blk[$this->blklvl]['startpage'] = $this->page;
	$this->oldy = $this->y;

	$this->lastblocklevelchange = 1 ;

	break;



    case 'HR':
	// Added mPDF 3.0 Float DIV - CLEAR
	if (isset($attr['STYLE'])) {
		$properties = $this->readInlineCSS($attr['STYLE']);
		if (isset($properties['CLEAR'])) { $this->ClearFloats(strtoupper($properties['CLEAR']),$this->blklvl); }
	}

	$this->ignorefollowingspaces = true; 

	$objattr = array();
	$properties = $this->MergeCSS('',$tag,$attr);
	if ($properties['MARGIN-TOP']) { $objattr['margin_top'] = ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
	if ($properties['MARGIN-BOTTOM']) { $objattr['margin_bottom'] = ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
	if ($properties['WIDTH']) { $objattr['width'] = ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']); }
	if ($properties['TEXT-ALIGN']) { $objattr['align'] = $align[strtolower($properties['TEXT-ALIGN'])]; }
	// mPDF 3.0
	if (isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT'])=='auto') { 
		$objattr['align'] = 'R';
	}
	if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT'])=='auto') { 
		$objattr['align'] = 'L';
		if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT'])=='auto' && isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT'])=='auto') { 
			$objattr['align'] = 'C';
		}
	}
	if ($properties['COLOR']) { $objattr['color'] = ConvertColor($properties['COLOR']); }
	if ($properties['HEIGHT']) { $objattr['linewidth'] = ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

	if($attr['WIDTH'] != '') $objattr['width'] = ConvertSize($attr['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
	if($attr['ALIGN'] != '') $objattr['align'] = $align[strtolower($attr['ALIGN'])];
	if($attr['COLOR'] != '') $objattr['color'] = ConvertColor($attr['COLOR']);

	if ($this->tableLevel) {
		$objattr['W-PERCENT'] = 100;
		if (stristr($properties['WIDTH'],'%')) { 
			$properties['WIDTH'] += 0;  //make "90%" become simply "90" 
			$objattr['W-PERCENT'] = $properties['WIDTH'];
		}
		if (stristr($attr['WIDTH'],'%')) { 
			$attr['WIDTH'] += 0;  //make "90%" become simply "90" 
			$objattr['W-PERCENT'] = $attr['WIDTH'];
		}
	}

	$objattr['type'] = 'hr';
	$objattr['height'] = $objattr['linewidth'] + $objattr['margin_top'] + $objattr['margin_bottom'];
	$e = "\xbb\xa4\xactype=image,objattr=".serialize($objattr)."\xbb\xa4\xac";

	// Clear properties - tidy up
	$properties = array();

	// Output it to buffers
	if ($this->tableLevel) {
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		$this->cell[$this->row][$this->col]['s'] = 0 ;// reset
		$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}
	else {
		$this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}

	break;



	// *********** FORM ELEMENTS ********************

    case 'SELECT':
	$this->lastoptionaltag = ''; // Save current HTML specified optional endtag
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$properties = $this->MergeCSS('',$tag,$attr);
	if ($properties['FONT-FAMILY']) { 
	   if (!$this->isCJK) { 
		$this->SetFont($properties['FONT-FAMILY'],$this->FontStyle,0,false);
	   }
	}
	if ($properties['FONT-SIZE']) { 
		$mmsize = ConvertSize($properties['FONT-SIZE'],$this->default_font_size/$this->k);
  		$this->SetFontSize($mmsize*$this->k,false);
	}
	if ($properties['COLOR']) { $this->selectoption['COLOR'] = ConvertColor($properties['COLOR']); }
	$this->specialcontent = "type=select"; 
	if(isset($attr['DISABLED'])) { $this->selectoption['DISABLED'] = $attr['DISABLED']; }
	if(isset($attr['TITLE'])) { $this->selectoption['TITLE'] = $attr['TITLE']; }
	if(isset($attr['MULTIPLE'])) { $this->selectoption['MULTIPLE'] = $attr['MULTIPLE']; }
	if(isset($attr['SIZE']) && $attr['SIZE']>1) { $this->selectoption['SIZE'] = $attr['SIZE']; }

	$properties = array();
	break;

    case 'OPTION':
	$this->lastoptionaltag = 'OPTION'; // Save current HTML specified optional endtag
	$this->selectoption['ACTIVE'] = true;
	$this->selectoption['currentSEL'] = false;	// mPDF 2.0 Active Forms
	if (empty($this->selectoption)) {
		$this->selectoption['MAXWIDTH'] = '';
		$this->selectoption['SELECTED'] = '';
	}
	if (isset($attr['SELECTED'])) { 
		$this->selectoption['SELECTED'] = '';
		$this->selectoption['currentSEL'] = true;	// mPDF 2.0 Active Forms
	}
	// mPDD 1.4 Active Forms
	$this->selectoption['currentVAL'] = $attr['VALUE'];
	break;

    case 'TEXTAREA':
	$objattr = array();
	if(isset($attr['DISABLED'])) { $objattr['disabled'] = true; }
	if(isset($attr['READONLY'])) { $objattr['readonly'] = true; }
	if(isset($attr['TITLE'])) { $objattr['title'] = $attr['TITLE']; }
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$properties = $this->MergeCSS('',$tag,$attr);
	if ($properties['FONT-FAMILY']) { 
	   if (!$this->isCJK) { 
		$this->SetFont($properties['FONT-FAMILY'],'',0,false);
	   }
	}
	if ($properties['FONT-SIZE']) { 
		$mmsize = ConvertSize($properties['FONT-SIZE'],$this->default_font_size/$this->k);
  		$this->SetFontSize($mmsize*$this->k,false);
	}
	if ($properties['COLOR']) { $objattr['color'] = ConvertColor($properties['COLOR']); }
	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;

	$this->SetLineHeight('',$this->textarea_lineheight); 
	// mPDF 2.0 Active Forms
	$formLineHeight = $this->lineheight;

	$w = 0;
	$h = 0;
	if(isset($properties['WIDTH'])) $w = ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
	if(isset($properties['HEIGHT'])) $h = ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);
	if ($properties['VERTICAL-ALIGN']) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }


	$colsize = 20; //HTML default value 
	$rowsize = 2; //HTML default value
	if (isset($attr['COLS'])) $colsize = intval($attr['COLS']);
	if (isset($attr['ROWS'])) $rowsize = intval($attr['ROWS']);

	$charsize = $this->GetStringWidth('w');
	if ($w) { $colsize = round(($w-($this->form_element_spacing['textarea']['outer']['h']*2)-($this->form_element_spacing['textarea']['inner']['h']*2))/$charsize); }
	if ($h) { $rowsize = round(($h-($this->form_element_spacing['textarea']['outer']['v']*2)-($this->form_element_spacing['textarea']['inner']['v']*2))/$formLineHeight); }

	$objattr['type'] = 'textarea';
	$objattr['width'] = ($colsize * $charsize) + ($this->form_element_spacing['textarea']['outer']['h']*2)+($this->form_element_spacing['textarea']['inner']['h']*2);
	$objattr['height'] = ($rowsize * $formLineHeight) + ($this->form_element_spacing['textarea']['outer']['v']*2)+($this->form_element_spacing['textarea']['inner']['v']*2);
	$objattr['rows'] = $rowsize;
	$objattr['cols'] = $colsize;

	$this->specialcontent = serialize($objattr); 

	if ($this->tableLevel) {
		$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
	}

	// Clear properties - tidy up
	$properties = array();
	break;



	// *********** FORM - INPUT ********************

    case 'INPUT':
	if (!isset($attr['TYPE'])) $attr['TYPE'] == 'TEXT'; 	// Edited mPDF 2.0 - HTML spec.
	$objattr = array();
	$objattr['type'] = 'input';
	if(isset($attr['DISABLED'])) { $objattr['disabled'] = true; }
	if(isset($attr['READONLY'])) { $objattr['readonly'] = true; }
	if(isset($attr['TITLE'])) { $objattr['title'] = $attr['TITLE']; }
	else if(isset($attr['ALT'])) { $objattr['title'] = $attr['ALT']; }

	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$properties = $this->MergeCSS('',$tag,$attr);
	$objattr['vertical-align'] = '';

	if ($properties['FONT-FAMILY']) { 
	   if (!$this->isCJK) { 
		$this->SetFont($properties['FONT-FAMILY'],$this->FontStyle,0,false);
	   }
	}
	if ($properties['FONT-SIZE']) { 
		$mmsize = ConvertSize($properties['FONT-SIZE'],($this->default_font_size/$this->k));
  		$this->SetFontSize($mmsize*$this->k,false);
	}
	if ($properties['COLOR']) { $objattr['color'] = ConvertColor($properties['COLOR']); }
	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;


	$type = '';
      $texto='';
	$height = $this->FontSize;
	$width = 0;
	$spacesize = $this->GetStringWidth(' ');

	$w = 0;
	if(isset($properties['WIDTH'])) $w = ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']);

	if ($properties['VERTICAL-ALIGN']) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }

	switch(strtoupper($attr['TYPE'])){
	   case 'HIDDEN':
      		$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
			if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
			unset($this->InlineProperties[$tag]);
			break 2;
	   case 'CHECKBOX': //Draw Checkbox
			$type = 'CHECKBOX';
			if (isset($attr['VALUE'])) $objattr['value'] = $attr['VALUE'];
			if (isset($attr['CHECKED'])) { $objattr['checked'] = true; } 
			else { $objattr['checked'] = false; }
			$width = $this->FontSize;
			$height = $this->FontSize;
			break;

	   case 'RADIO': //Draw Radio button
			$type = 'RADIO';
			if (isset($attr['CHECKED'])) $objattr['checked'] = true;
			$width = $this->FontSize;
			$height = $this->FontSize;
			break;

	   case 'IMAGE': // Draw an Image button
	if(isset($attr['SRC']))	{
		$type = 'IMAGE';
     		$srcpath = $attr['SRC'];
		// VSPACE and HSPACE converted to margins in MergeCSS
		if ($properties['MARGIN-TOP']) { $objattr['margin_top']=ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if ($properties['MARGIN-BOTTOM']) { $objattr['margin_bottom'] = ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if ($properties['MARGIN-LEFT']) { $objattr['margin_left'] = ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if ($properties['MARGIN-RIGHT']) { $objattr['margin_right'] = ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }


		if ($properties['BORDER-TOP']) { $objattr['border_top'] = $this->border_details($properties['BORDER-TOP']); }
		if ($properties['BORDER-BOTTOM']) { $objattr['border_bottom'] = $this->border_details($properties['BORDER-BOTTOM']); }
		if ($properties['BORDER-LEFT']) { $objattr['border_left'] = $this->border_details($properties['BORDER-LEFT']); }
		if ($properties['BORDER-RIGHT']) { $objattr['border_right'] = $this->border_details($properties['BORDER-RIGHT']); }

		if ($properties['VERTICAL-ALIGN']) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }

		$w = 0;
		$h = 0;
		if(isset($properties['WIDTH'])) $w = ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
		if(isset($properties['HEIGHT'])) $h = ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);

		$extraheight = $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
		$extrawidth = $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

		// Image file
		// Check if file is available
		$found_img = false;
		// Edited mPDF 2.0
		$found_img = $this->_file_exists($srcpath);
		$imageset = false;
		while(!$imageset) {
			if (!$found_img) {
				if(!$this->shownoimg) break;
				$srcpath = str_replace("\\","/",dirname(__FILE__)) . "/";
				$srcpath .= 'no_img2.gif';
				$w = (14 * 0.2645); 	// 14 x 16px
				$h = (16 * 0.2645); 	// 14 x 16px
				$imageset = true;
			}
			// Gets Image Info
			if(!isset($this->images[$srcpath])) {
				//First use of image, get info
				$pos=strrpos($srcpath,'.');
				if(!$pos) { 
					$found_img = false; 
					// mPDF 3.0
					if ($this->showImageErrors) $this->Error('Image file has no extension and no type was specified: '.$srcpath);
					else continue; 
				}
				// mPDF 2.5
				$qpos=strrpos($srcpath,'?');
				if ($qpos) {  $itype=substr($srcpath,$pos+1,$qpos-$pos-1); }
				else { $itype=substr($srcpath,$pos+1); }
				$itype=strtolower($itype);
				$mqr=get_magic_quotes_runtime();
				set_magic_quotes_runtime(0);
				if($itype=='jpg' or $itype=='jpeg')	$info=$this->_parsejpg($srcpath);
				elseif($itype=='png') $info=$this->_parsepng($srcpath);
				elseif($itype=='gif') $info=$this->_parsegif($srcpath); 
				elseif($itype=='wmf')  { $found_img = false; continue; }
				else { 
					//Allow for additional formats
					$mtd='_parse'.$itype;
					if(method_exists($this,$mtd)) { $info=$this->$mtd($srcpath); }
					else { 
						// mPDF 3.0
						if ($this->showImageErrors) $this->Error('Unsupported image type: '.$itype);
						else $info = -1; 
					}
				}
				// mPDF 3.0 - Try again PNG
				if ($itype=='png' && $info < -1) { $info=$this->_parsepng2($srcpath,$info); }
				set_magic_quotes_runtime($mqr);
				if (!is_array($info) && $info < 0) { $found_img = false; continue; }
				else {
					$info['i']=count($this->images)+1;
					$this->images[$srcpath]=$info;
					$imageset = true;
				}
			}
			else {
				$info=$this->images[$srcpath];
				$imageset = true;
			}
		}


		$objattr['file'] = $srcpath;
		//Default width and height calculation if needed
		if($w==0 and $h==0) {
			//Put image at default dpi
			$w=($info['w']/$this->k) * (72/$this->img_dpi);
			$h=($info['h']/$this->k) * (72/$this->img_dpi);
		}
		// IF WIDTH OR HEIGHT SPECIFIED
		if($w==0)	$w=$h*$info['w']/$info['h'];
		if($h==0)	$h=$w*$info['h']/$info['w'];
		// Resize to maximum dimensions of page
		$maxWidth = $this->blk[$this->blklvl]['inner_width'];
   		$maxHeight = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10) ;
		if ($w + $extrawidth > $maxWidth ) {
			$w = $maxWidth - $extrawidth;
			$h=$w*$info['h']/$info['w'];
		}
		if ($h + $extraheight > $maxHeight ) {
			$h = $maxHeight - $extraheight;
			$w=$h*$info['w']/$info['h'];
		}
		$height = $h + $extraheight;
		$width = $w + $extrawidth;
		$objattr['image_height'] = $h;
		$objattr['image_width'] = $w;
		$objattr['ID'] = $info['i'];
		$texto = 'X';
		break;
	}

	   case 'BUTTON': // Draw a button
	   case 'SUBMIT': 
	   case 'RESET': 
			$type = strtoupper($attr['TYPE']);
			if ($type=='IMAGE') { $type = 'BUTTON'; } // src path not found
			if (isset($attr['VALUE'])) {
				$objattr['value'] = $attr['VALUE'];
			}
			else {
				$objattr['value'] = ucfirst(strtolower($type));
			}
			$texto = " " . $objattr['value'] . " ";
			$width = $this->GetStringWidth($texto) + ($this->form_element_spacing['button']['outer']['h']*2)+($this->form_element_spacing['button']['inner']['h']*2);
			$height = $this->FontSize + ($this->form_element_spacing['button']['outer']['v']*2)+($this->form_element_spacing['button']['inner']['v']*2);
			break;


	   case 'PASSWORD':
	   case 'TEXT': 
	   default:
                if ($type == '') { $type = 'TEXT'; }
		    if(strtoupper($attr['TYPE'])=='PASSWORD') { $type = 'PASSWORD'; }
               if (isset($attr['VALUE'])) {
			if ($type == 'PASSWORD') {
                    $num_stars = strlen($attr['VALUE']);
                    $texto = str_repeat('*',$num_stars);
			}
			else { $texto = $attr['VALUE']; }
                }

		    $xw = ($this->form_element_spacing['input']['outer']['h']*2)+($this->form_element_spacing['input']['inner']['h']*2);
		    $xh = ($this->form_element_spacing['input']['outer']['v']*2)+($this->form_element_spacing['input']['inner']['v']*2);
		    if ($w) { $width = $w + $xw; } 
		    else { $width = (20 * $spacesize) + $xw; }	// Default width in chars
                if (isset($attr['SIZE']) and ctype_digit($attr['SIZE']) ) $width = ($attr['SIZE'] * $spacesize) + $xw;
		    $height = $this->FontSize + $xh;
                break;
	}

	$objattr['subtype'] = $type;
	$objattr['text'] = $texto;
	$objattr['width'] = $width;
	$objattr['height'] = $height;
	$e = "\xbb\xa4\xactype=input,objattr=".serialize($objattr)."\xbb\xa4\xac";

	// Clear properties - tidy up
	$properties = array();

	// Output it to buffers
	if ($this->tableLevel) {
		$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);

		$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;

	}
	else {
		$this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}

	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);

	break;	// END of INPUT


	// *********** GRAPH  ********************
     // mPDF 2.4 JPGRAPH
     case 'JPGRAPH':
	if (!$this->useGraphs) { break; }
	if ($attr['TABLE']) { $gid = strtoupper($attr['TABLE']); }
	else { $gid = '0'; }
	if (!is_array($this->graphs[$gid]) || count($this->graphs[$gid])==0 ) { break; }
	include_once(_MPDF_PATH.'graph.php');
	$this->graphs[$gid]['attr'] = $attr;
	$graph_img = print_graph($this->graphs[$gid],$this->blk[$this->blklvl]['inner_width']);
	if ($graph_img) { 
		$attr['SRC'] = $graph_img['file']; 
		$attr['WIDTH'] = $graph_img['w']; 
		$attr['HEIGHT'] = $graph_img['h']; 
	}
	else { break; }

	// *********** IMAGE  ********************

    case 'IMG':
	$objattr = array();
	if(isset($attr['SRC']))	{
     		$srcpath = $attr['SRC'];
		$properties = $this->MergeCSS('',$tag,$attr);
		// mPDF 2.3 DISPLAY NONE
		if(strtolower($properties ['DISPLAY'])=='none') { 
			return; 
		}
		// VSPACE and HSPACE converted to margins in MergeCSS
		if ($properties['MARGIN-TOP']) { $objattr['margin_top']=ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if ($properties['MARGIN-BOTTOM']) { $objattr['margin_bottom'] = ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if ($properties['MARGIN-LEFT']) { $objattr['margin_left'] = ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		if ($properties['MARGIN-RIGHT']) { $objattr['margin_right'] = ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }

		if ($properties['BORDER-TOP']) { $objattr['border_top'] = $this->border_details($properties['BORDER-TOP']); }
		if ($properties['BORDER-BOTTOM']) { $objattr['border_bottom'] = $this->border_details($properties['BORDER-BOTTOM']); }
		if ($properties['BORDER-LEFT']) { $objattr['border_left'] = $this->border_details($properties['BORDER-LEFT']); }
		if ($properties['BORDER-RIGHT']) { $objattr['border_right'] = $this->border_details($properties['BORDER-RIGHT']); }

		if ($properties['VERTICAL-ALIGN']) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }
		$w = 0;
		$h = 0;
		if(isset($properties['WIDTH'])) $w = ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
		if(isset($properties['HEIGHT'])) $h = ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);

		// mPDF 2.0
		if(isset($attr['WIDTH'])) $w = ConvertSize($attr['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
		if(isset($attr['HEIGHT'])) $h = ConvertSize($attr['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);

		// mPDF 2.3
		if ($properties['OPACITY'] > 0 && $properties['OPACITY'] <= 1) { $objattr['opacity'] = $properties['OPACITY']; }

		if ($this->HREF) { $objattr['link'] = $this->HREF; }	// ? this isn't used

		$extraheight = $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
		$extrawidth = $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];
		// Image file
		// Check if file is available
		// Edited mPDF 2.0
		$found_img = $this->_file_exists($srcpath);
		$imageset = false;
		while(!$imageset) {
			if (!$found_img) {
				if(!$this->shownoimg) break;
				$srcpath = str_replace("\\","/",dirname(__FILE__)) . "/";
				$srcpath .= 'no_img2.gif';
				$w = (14 * 0.2645); 	// 14 x 16px
				$h = (16 * 0.2645); 	// 14 x 16px
				$imageset = true;
			}
			// Gets Image Info
      		// mPDF 2.2 WMF image
			//First use of image, get info
			$pos=strrpos($srcpath,'.');
			if(!$pos) { 
				$found_img = false; 
				// mPDF 3.0
				if ($this->showImageErrors) $this->Error('Image file has no extension and no type was specified: '.$srcpath);
				else continue; 
			}
			// mPDF 2.5
			$qpos=strrpos($srcpath,'?');
			if ($qpos) {  $itype=substr($srcpath,$pos+1,$qpos-$pos-1); }
			else { $itype=substr($srcpath,$pos+1); }

			$itype=strtolower($itype);
			if(isset($this->images[$srcpath])) { $info=$this->images[$srcpath]; $imageset = true; }
			else if (isset($this->formobjects[$srcpath])) { $info=$this->formobjects[$srcpath]; $imageset = true; }
			else {
				$mqr=get_magic_quotes_runtime();
				set_magic_quotes_runtime(0);
				if($itype=='jpg' or $itype=='jpeg')	$info=$this->_parsejpg($srcpath);
				elseif($itype=='png') $info=$this->_parsepng($srcpath);
				elseif($itype=='gif') $info=$this->_parsegif($srcpath); 
				elseif($itype=='wmf') $info=$this->_parsewmf($srcpath); 
				else { 
					//Allow for additional formats
					$mtd='_parse'.$itype;
					if(method_exists($this,$mtd)) { $info=$this->$mtd($srcpath); }
					else { 
						// mPDF 3.0
						if ($this->showImageErrors) $this->Error('Unsupported image type: '.$itype);
						else $info = -1; 
					}
				}
				// mPDF 3.0 - Try again PNG
				if ($itype=='png' && $info < -1) { 
					$info=$this->_parsepng2($srcpath, $info); 
					if ($info['UID']) { $srcpath = $info['UID']; }	// A unique alpha blended PNG created
				}
				set_magic_quotes_runtime($mqr);
				if (!is_array($info) && $info < 0) { 
					$found_img = false; 
					continue; 
				}
            		else if ($itype=='wmf') { 
	            		$info['i']=count($this->formobjects)+1;
	            		$this->formobjects[$srcpath]=$info;
					$imageset = true;
				}
				else {
					$info['i']=count($this->images)+1;
					$this->images[$srcpath]=$info;
					$imageset = true;
				}
			}
		}

		$objattr['file'] = $srcpath;
		//Default width and height calculation if needed
		// mPDF 2.2 WMF
		if($w==0 and $h==0) {
      	      if ($itype=='wmf') { 
				// mPDF 2.2 WMF image
				// WMF units are twips (1/20pt)
				// divide by 20 to get points
				// divide by k to get user units
				$w = abs($info['w'])/(20*$this->k);
				$h = abs($info['h']) / (20*$this->k);
			}
			else {
				//Put image at default dpi
				$w=($info['w']/$this->k) * (72/$this->img_dpi);
				$h=($info['h']/$this->k) * (72/$this->img_dpi);
			}
		}
		// IF WIDTH OR HEIGHT SPECIFIED
		if($w==0)	$w=abs($h*$info['w']/$info['h']);	// mPDF 2.4
		if($h==0)	$h=abs($w*$info['h']/$info['w']);	// mPDF 2.4

		// Resize to maximum dimensions of page
		$maxWidth = $this->blk[$this->blklvl]['inner_width'];
   		$maxHeight = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10) ;
		if ($w + $extrawidth > $maxWidth ) {
			$w = $maxWidth - $extrawidth;
			$h=abs($w*$info['h']/$info['w']);
		}

		if ($h + $extraheight > $maxHeight ) {
			$h = $maxHeight - $extraheight;
			$w=abs($h*$info['w']/$info['h']);
		}
		$objattr['type'] = 'image';
		// mPDF 2.2 Req. for WMF image
		$objattr['itype'] = $itype;
		$objattr['orig_h'] = $info['h'];
		$objattr['orig_w'] = $info['w'];
		if ($itype=='wmf') {
			$objattr['wmf_x'] = $info['x'];
			$objattr['wmf_y'] = $info['y'];
		}
		$objattr['height'] = $h + $extraheight;
		$objattr['width'] = $w + $extrawidth;
		$objattr['image_height'] = $h;
		$objattr['image_width'] = $w;

		// mPDF 2.4 Float Image
		if (!$this->ColActive && !$this->tableLevel && !$this->listlvl && !$this->kwt && !$this->keep_block_together) {
		  if (strtoupper($properties['FLOAT']) == 'RIGHT' || strtoupper($properties['FLOAT']) == 'LEFT') {
			$objattr['float'] = substr(strtoupper($properties['FLOAT']),0,1);
		  }
		}

		$e = "\xbb\xa4\xactype=image,objattr=".serialize($objattr)."\xbb\xa4\xac";

		// Clear properties - tidy up
		$properties = array();

		// Output it to buffers
		if ($this->tableLevel) {
			$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
			$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
		}
		else {
			$this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);

		}
		// mPDF 2.2 Annotations
		if ($this->title2annots && $attr['TITLE']) {
			$objattr = array();
			$objattr['CONTENT'] = $attr['TITLE'];
			$objattr['type'] = 'annot';
			$objattr['POS-X'] = 0;
			$objattr['POS-Y'] = 0;
			$objattr['ICON'] = 'Comment';
			$objattr['AUTHOR'] = '';
			$objattr['SUBJECT'] = '';
			$objattr['OPACITY'] = $this->annotOpacity; 
			$objattr['COLOR'] = array(255,255,0); 
			$e = "\xbb\xa4\xactype=annot,objattr=".serialize($objattr)."\xbb\xa4\xac";
			if($this->tableLevel) {
				$this->cell[$this->row][$this->col]['textbuffer'][] = array($e);
			}
			else  {
				$this->textbuffer[] = array($e);
			}
		}
	}
	break;


	// *********** TABLES ********************

    case 'TABLE': // TABLE-BEGIN
	$this->tdbegin = false;
	$this->lastoptionaltag = '';
	// Disable vertical justification in columns
	if ($this->ColActive) { $this->colvAlign = ''; }

	// mPDF 2.0 To fix <div> Text in no tag <table>...
	if ($this->lastblocklevelchange == 1) { $blockstate = 1; }	// Top margins/padding only
	else if ($this->lastblocklevelchange < 1) { $blockstate = 0; }	// NO margins/padding
	// called from block after new div e.g. <div> ... <table> ...    Outputs block top margin/border and padding
	if (count($this->textbuffer) == 0 && $this->lastblocklevelchange == 1 && !$this->tableLevel && !$this->kwt) {
		$this->newFlowingBlock( $this->block[$this->blocklevel]['width'],$this->lineheight,'',false,false,1,true);	// true = newblock
		$this->finishFlowingBlock(true);	// true = END of flowing block
	}
	// mpDF 2.1
	else if (!$this->tableLevel && count($this->textbuffer)) { $this->printbuffer($this->textbuffer,$blockstate); }
	//else if (!$this->tableLevel) { $this->printbuffer($this->textbuffer,$blockstate); }

	$this->textbuffer=array();
	$this->lastblocklevelchange = -1;
	if ($this->tableLevel) {	// i.e. now a nested table coming...
		// Save current level table
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['baseProperties']= $this->base_table_properties;
	//	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['tablecascadeCSS'] = $this->tablecascadeCSS;
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cells'] = $this->cell;
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['currrow'] = $this->row;
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['currcol'] = $this->col;
	}
	$this->tableLevel++;
	// mPDF 2.1. Table CSS
	$this->tbCSSlvl++;

	$this->tbctr[$this->tableLevel]++;
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['level'] = $this->tableLevel;
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['levelid'] = $this->tbctr[$this->tableLevel];

	if ($this->tableLevel > $this->innermostTableLevel) { $this->innermostTableLevel = $this->tableLevel; }
	if ($this->tableLevel > 1) { 
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nestedpos'] = array($this->row,$this->col,$this->tbctr[($this->tableLevel-1)]); 
	}
	//++++++++++++++++++++++++++++

	// mPDF 2.0 - Nested tables - extra values to reset
	$this->cell = array();
	$this->col=-1; //int
	$this->row=-1; //int
	$table = &$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]];
	$this->Reset();
	$this->table_lineheight = $this->default_lineheight_correction; 
	$this->InlineProperties = array();
	$this->spanlvl = 0;
	$table['nc'] = $table['nr'] = 0;
	$this->tablethead = 0;
	$this->tabletfoot = 0;
	$this->tabletheadjustfinished = false;

	// Added mPDF 1.2 
	// mPDF 2.1. Table CSS
//	$this->tablecascadeCSS = array();
	// Added mPDF 2.0 - for color, font-style and font-weight
	if ($this->tableLevel ==1) $this->base_table_properties = array();

		// ADDED CSS FUNCIONS FOR TABLE // mPDF 1.2 add parameter 'TABLE'
		// mPDF 2.1. Table CSS
		if ($this->tbCSSlvl==1) {
			$properties = $this->MergeCSS('TOPTABLE',$tag,$attr);
		}
		else {
			$properties = $this->MergeCSS('TABLE',$tag,$attr);
		}
		// mPDF 2.2
		$w = '';
		if ($properties['WIDTH']) { $w = $properties['WIDTH']; }

		if ($properties['BACKGROUND-COLOR']) { $table['bgcolor'][-1] = $properties['BACKGROUND-COLOR'];	}
		// Added mPDF 2.0
		else if ($properties['BACKGROUND']) { $table['bgcolor'][-1] = $properties['BACKGROUND'];	}
		if ($properties['VERTICAL-ALIGN']) { $table['va'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }
		if ($properties['TEXT-ALIGN']) { $table['txta'] = $align[strtolower($properties['TEXT-ALIGN'])]; }
		if ($properties['AUTOSIZE'] && $this->tableLevel ==1)	{ 
			$this->shrink_this_table_to_fit = $properties['AUTOSIZE']; 
			if ($this->shrink_this_table_to_fit < 1) { $this->shrink_this_table_to_fit = 0; }
		}
		if ($properties['ROTATE'] && $this->tableLevel ==1)	{ 
			$this->table_rotate = $properties['ROTATE']; 
		}
		if ($properties['TOPNTAIL']) { $table['topntail'] = $properties['TOPNTAIL']; }
		if ($properties['THEAD-UNDERLINE']) { $table['thead-underline'] = $properties['THEAD-UNDERLINE']; }

		if ($properties['BORDER']) { 
			$bord = $this->border_details($properties['BORDER']);
			if ($bord['s']) {
				$table['border'] = _BORDER_ALL;
				$table['border_details']['R'] = $bord;
				$table['border_details']['L'] = $bord;
				$table['border_details']['T'] = $bord;
				$table['border_details']['B'] = $bord;
			}
		}
		if ($properties['BORDER-RIGHT']) { 
		  if ($this->directionality == 'rtl') { 
			$table['border_details']['R'] = $this->border_details($properties['BORDER-LEFT']);
			$this->setBorder ($table['border'], _BORDER_RIGHT, $table['border_details']['R']['s']); 
		  }
		  else {
			$table['border_details']['R'] = $this->border_details($properties['BORDER-RIGHT']);
			$this->setBorder ($table['border'], _BORDER_RIGHT, $table['border_details']['R']['s']); 
		  }
		}
		if ($properties['BORDER-LEFT']) { 
		  if ($this->directionality == 'rtl') { 
			$table['border_details']['L'] = $this->border_details($properties['BORDER-RIGHT']);
			$this->setBorder ($table['border'], _BORDER_LEFT, $table['border_details']['L']['s']); 
		  }
		  else {
			$table['border_details']['L'] = $this->border_details($properties['BORDER-LEFT']);
			$this->setBorder ($table['border'], _BORDER_LEFT, $table['border_details']['L']['s']); 
		  }
		}
		if ($properties['BORDER-BOTTOM']) { 
			$table['border_details']['B'] = $this->border_details($properties['BORDER-BOTTOM']);
			$this->setBorder ($table['border'], _BORDER_BOTTOM, $table['border_details']['B']['s']); 
		}
		if ($properties['BORDER-TOP']) { 
			$table['border_details']['T'] = $this->border_details($properties['BORDER-TOP']);
			$this->setBorder ($table['border'], _BORDER_TOP, $table['border_details']['T']['s']); 
		}
		if ($table['border']){ 
			// Edited mPDF 1.1 for correct table border inheritance
			  $this->table_border_css_set = 1;
		}
		// Edited mPDF 1.1 for correct table border inheritance
		else {
		  $this->table_border_css_set = 0;
		}

		if ($properties['FONT-FAMILY']) { 
		   if (!$this->isCJK) { 
			$this->default_font = $properties['FONT-FAMILY'];
			$this->SetFont($this->default_font,'',0,false);
			$this->base_table_properties['FONT-FAMILY'] = $properties['FONT-FAMILY'];
		   }
		}
		if ($properties['FONT-SIZE']) { 
		   $mmsize = ConvertSize($properties['FONT-SIZE'],$this->default_font_size/$this->k);
		   // mPDF 2.3
		   if ($mmsize) {
			$this->default_font_size = $mmsize*(72/25.4);
   			$this->SetFontSize($this->default_font_size,false);
			$this->base_table_properties['FONT-SIZE'] = $properties['FONT-SIZE'];
		   }
		}

		// Added mPDF 1.2 to add CSS
		// Edited mPDF 2.0
		if ($properties['FONT-WEIGHT']) {
			if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->base_table_properties['FONT-WEIGHT'] = 'BOLD'; }
		}
		if ($properties['FONT-STYLE']) {
			if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $this->base_table_properties['FONT-STYLE'] = 'ITALIC'; }
		}
		if ($properties['COLOR']) {
			$this->base_table_properties['COLOR'] = $properties['COLOR'];
		}


		if ($properties['PADDING-LEFT']) { 
			$table['padding']['L'] = ConvertSize($properties['PADDING-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		}
		if ($properties['PADDING-RIGHT']) { 
			$table['padding']['R'] = ConvertSize($properties['PADDING-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		}
		if ($properties['PADDING-TOP']) { 
			$table['padding']['T'] = ConvertSize($properties['PADDING-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		}
		if ($properties['PADDING-BOTTOM']) { 
			$table['padding']['B'] = ConvertSize($properties['PADDING-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		}

		if ($properties['MARGIN-TOP']) { 
			$table['margin']['T'] = ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
		}

		if ($properties['MARGIN-BOTTOM']) { 
			$table['margin']['B'] = ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
		}
		if ($properties['MARGIN-TOP']) { 
			$table['margin']['L'] = ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
		}

		if ($properties['MARGIN-BOTTOM']) { 
			$table['margin']['R'] = ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
		}
		// mPDF 2.2
		if (strtolower($properties['MARGIN-LEFT'])=='auto' && strtolower($properties['MARGIN-RIGHT'])=='auto') { 
			$table['a'] = 'C'; 
		}
		else if (strtolower($properties['MARGIN-LEFT'])=='auto') { 
			$table['a'] = 'R'; 
		}
		else if (strtolower($properties['MARGIN-RIGHT'])=='auto') { 
			$table['a'] = 'L'; 
		}

		if ($properties['LINE-HEIGHT']) { 
			// Edited mPDF 2.0
			if (preg_match('/^[0-9\.,]*$/',$properties['LINE-HEIGHT']) && $properties['LINE-HEIGHT'] >= 1) { $this->table_lineheight = $properties['LINE-HEIGHT'] + 0; }
			else if (preg_match('/%/',$properties['LINE-HEIGHT']) && $properties['LINE-HEIGHT'] >= 100) { $this->table_lineheight = ($properties['LINE-HEIGHT'] + 0)/100; }
			else if (strtoupper($properties['LINE-HEIGHT']) == 'NORMAL') { $this->table_lineheight = $this->default_lineheight_correction; }
			else { $this->table_lineheight = $this->default_lineheight_correction; }
		}

		// mPDF 2.0 Added
		if (strtoupper($properties['BORDER-COLLAPSE'])=='SEPARATE') { 
			$table['borders_separate'] = true; 
		}
		else { 
			$table['borders_separate'] = false; 
		}

		if ($properties['BORDER-SPACING-H']) { 
			$table['border_spacing_H'] = ConvertSize($properties['BORDER-SPACING-H'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
		}
		if ($properties['BORDER-SPACING-V']) { 
			$table['border_spacing_V'] = ConvertSize($properties['BORDER-SPACING-V'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
		}

		if ($properties['EMPTY-CELLS']) { 
			$table['empty_cells'] = strtolower($properties['EMPTY-CELLS']); 	// 'hide'  or 'show'
		}
		else { $table['empty_cells'] = ''; } 

		if (strtoupper($properties['PAGE-BREAK-INSIDE'])=='AVOID' && $this->tableLevel==1) { 
			$this->table_keep_together = true; 
		}
		else if ($this->tableLevel==1) { 
			$this->table_keep_together = false; 
		}

	$properties = array();

	// mPDF 2.0 Added
	if (!$table['borders_separate']) { $table['border_spacing_H'] = $table['border_spacing_V'] = 0; }	
	else if (isset($attr['CELLSPACING'])) { 
		$table['border_spacing_H'] = $table['border_spacing_V'] = ConvertSize($attr['CELLSPACING'],$this->blk[$this->blklvl]['inner_width']); 
	}


	// mPDF 2.0 Added
	if (isset($attr['CELLPADDING'])) {
		$this->cellPaddingSetInTable = ConvertSize($attr['CELLPADDING'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
	}
	else {
		$this->cellPaddingSetInTable = false;
	}

	if (isset($attr['BORDER'])) {
		// Edited mPDF 1.1 for correct table border inheritance
		  $this->table_border_attr_set = 1;
		if ($attr['BORDER']=='1') {
			$bord = $this->border_details('#000000 1px solid');
		   if ($bord['s']) {
			$table['border'] = _BORDER_ALL;
			$table['border_details']['R'] = $bord;
			$table['border_details']['L'] = $bord;
			$table['border_details']['T'] = $bord;
			$table['border_details']['B'] = $bord;
		   }
		}
	}
	// Edited mPDF 1.1 for correct table border inheritance
	else {
	  $this->table_border_attr_set = 0;
	}
	if (isset($attr['REPEAT_HEADER']) and $attr['REPEAT_HEADER'] == true) { $this->UseTableHeader(true); } 
		else { $this->UseTableHeader(false); }


	if (isset($attr['ALIGN']))	$table['a']	= $align[strtolower($attr['ALIGN'])];
	if (!$table['a']) { $table['a'] = $this->defaultTableAlign; }
	if (isset($attr['BGCOLOR'])) $table['bgcolor'][-1]	= $attr['BGCOLOR'];
	if (isset($attr['HEIGHT']))	$table['h']	= ConvertSize($attr['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);


	// mPDF 2.2
	if ($attr['WIDTH']) { $w = $attr['WIDTH']; }
	if ($w) { // set here or earlier in $properties
		$maxwidth = $this->blk[$this->blklvl]['inner_width'];
		if ($table['borders_separate']) { 
			$tblblw = $table['margin']['L'] + $table['margin']['R'] + $table['border_details']['L']['w']/2 + $table['border_details']['R']['w']/2;
		}
		else { 
			$tblblw = $table['margin']['L'] + $table['margin']['R'] + $table['max_cell_border_width']['L']/2 + $table['max_cell_border_width']['R']/2;
		}
		if (strpos($w,'%') && $this->tableLevel == 1 && !$this->ignore_table_percents ) { 
			// % needs to be of inner box without table margins etc.
			$maxwidth -= $tblblw ;
			$wmm = ConvertSize($w,$maxwidth,$this->FontSize,false);
			$table['w'] = $wmm + $tblblw ;
		}
		if (strpos($w,'%') && $this->tableLevel > 1 && !$this->ignore_table_percents && $this->keep_table_proportions) { 
			$table['wpercent'] = $w + 0; 	// makes 80% -> 80
		}
		if (!strpos($w,'%') && !$this->ignore_table_widths ) {
			$wmm = ConvertSize($w,$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
			$table['w'] = $wmm + $tblblw ;
		}
		if (!$this->keep_table_proportions) {
			if ($table['w'] > $this->blk[$this->blklvl]['inner_width']) { $table['w'] = $this->blk[$this->blklvl]['inner_width']; }
		}
	}


	if (isset($attr['AUTOSIZE']) && $this->tableLevel==1)	{ 
		$this->shrink_this_table_to_fit = $attr['AUTOSIZE']; 
		if ($this->shrink_this_table_to_fit < 1) { $this->shrink_this_table_to_fit = 0; }
	}
	if (isset($attr['ROTATE']) && $this->tableLevel==1)	{ 
		$this->table_rotate = $attr['ROTATE']; 
	}

	//++++++++++++++++++++++++++++
	// Added mPDF 1.1 keeping block together on one page
	// mPDF 2.0 Autosize is now forced therefore keep block together disabled
	if ($this->keep_block_together) {
		$this->keep_block_together = 0;
		$this->printdivbuffer();
		$this->blk[$this->blklvl]['keep_block_together'] = 0;
	}
	// mPDF 2.0
	if ($this->table_rotate) {
		$this->tbrot_Links = array();
		// mPDF 2.2 Annotations
		$this->tbrot_Annots = array();
		// mPDF 3.0
		$this->tbrot_Reference = array();
		$this->tbrot_BMoutlines = array();
		$this->tbrot_toc = array();
	}

	if ($this->kwt) {
		if ($this->table_rotate) { $this->table_keep_together = true; }
		$this->kwt = false;
		$this->kwt_saved = true;
	}

	// mPDF 2.4 JPGRAPH
	if ($this->tableLevel==1 && $this->useGraphs) { 
		if ($attr['ID']) { $this->currentGraphId = strtoupper($attr['ID']); }
		else { $this->currentGraphId = '0'; }
		$this->graphs[$this->currentGraphId] = array();
	}

	//++++++++++++++++++++++++++++
	// mPDF 2.1 Save Plain Cell CSS
	$this->plainCell_properties = array();


	break;



    case 'THEAD':
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	// mPDF 2.1. Table CSS
	$this->tbCSSlvl++;
	$this->tablethead = 1;
	$this->UseTableHeader(true);
	$properties = $this->MergeCSS('TABLE',$tag,$attr);
	if ($properties['FONT-WEIGHT']) {
		if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->thead_font_weight = 'B'; }
		else { $this->thead_font_weight = ''; }
	}

	// Added in mPDF 1.1
	if ($properties['FONT-STYLE']) {
		if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $this->thead_font_style = 'I'; }
		else { $this->thead_font_style = ''; }
	}

	if ($properties['VERTICAL-ALIGN']) {
		$this->thead_valign_default = $properties['VERTICAL-ALIGN'];
	}
	if ($properties['TEXT-ALIGN']) {
		$this->thead_textalign_default = $properties['TEXT-ALIGN'];
	}
	$properties = array();
	break;


    case 'TFOOT':
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	// mPDF 2.1. Table CSS
	$this->tbCSSlvl++;
	$this->tabletfoot = 1;
	$properties = $this->MergeCSS('TABLE',$tag,$attr);
	if ($properties['FONT-WEIGHT']) {
		if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->tfoot_font_weight = 'B'; }
		else { $this->tfoot_font_weight = ''; }
	}

	// Added in mPDF 1.1
	if ($properties['FONT-STYLE']) {
		if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $this->tfoot_font_style = 'I'; }
		else { $this->tfoot_font_style = ''; }
	}

	if ($properties['VERTICAL-ALIGN']) {
		$this->tfoot_valign_default = $properties['VERTICAL-ALIGN'];
	}
	if ($properties['TEXT-ALIGN']) {
		$this->tfoot_textalign_default = $properties['TEXT-ALIGN'];
	}
	$properties = array();
	break;


    case 'TBODY':
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	// mPDF 2.1. Table CSS
	$this->tbCSSlvl++;
	$this->MergeCSS('TABLE',$tag,$attr);
	break;


    case 'TR':
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	// mPDF 2.1. Table CSS
	$this->tbCSSlvl++;
	$this->row++;
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nr']++;
	$this->col = -1;
	// ADDED CSS FUNCIONS FOR TABLE // mPDF 1.2 add parameter 'TR // mPDF 2.1
	$properties = $this->MergeCSS('TABLE',$tag,$attr);
	if ($properties['BACKGROUND-COLOR']) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['bgcolor'][$this->row] = $properties['BACKGROUND-COLOR']; }
	// Added mPDF 2.0
	else if ($properties['BACKGROUND']) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['bgcolor'][$this->row] = $properties['BACKGROUND']; }
	// Edited mPDF 1.3 for rotated text in cell
	if ($properties['TEXT-ROTATE']) {
		$this->trow_text_rotate = $properties['TEXT-ROTATE'];
	}
	if (isset($attr['TEXT-ROTATE'])) $this->trow_text_rotate = $attr['TEXT-ROTATE'];

	if (isset($attr['BGCOLOR'])) $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['bgcolor'][$this->row]	= $attr['BGCOLOR'];
	// mPDF 2.1 Set row as thead
	if ($this->tablethead) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['is_thead'][$this->row] = true; }
	$properties = array();
	break;



    case 'TH':
    case 'TD':
	$this->ignorefollowingspaces = true; 
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	// mPDF 2.1. Table CSS
	$this->tbCSSlvl++;
	$this->InlineProperties = array();
	$this->spanlvl = 0;
	$this->tdbegin = true;
	$this->col++;
	while (isset($this->cell[$this->row][$this->col])) { $this->col++; }
	//Update number column
	if ($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'] < $this->col+1) { $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'] = $this->col+1; }
	$this->cell[$this->row][$this->col] = array();
	$this->cell[$this->row][$this->col]['text'] = array();
	$this->cell[$this->row][$this->col]['s'] = 0 ;

	$table = &$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]];
	$cell = &$this->cell[$this->row][$this->col];

	// INHERITED TABLE PROPERTIES (or ROW for BGCOLOR)
	// If cell bgcolor not set specifically, set to TR row bgcolor (if set)
	if ((!$cell['bgcolor']) && ($table['bgcolor'][$this->row])) {
		$cell['bgcolor'] = $table['bgcolor'][$this->row];
	}
	else if ($table['bgcolor'][-1]) { $cell['bgcolor'] = $table['bgcolor'][-1]; }

	if ($table['va']) { $cell['va'] = $table['va']; }
	if ($table['txta']) { $cell['a'] = $table['txta']; }
	// Edited mPDF 1.1 for correct table border inheritance
	if ($this->table_border_attr_set) {
	  if ($table['border_details']) {
		$cell['border_details']['R'] = $table['border_details']['R'];
		$cell['border_details']['L'] = $table['border_details']['L'];
		$cell['border_details']['T'] = $table['border_details']['T'];
		$cell['border_details']['B'] = $table['border_details']['B'];
		$cell['border'] = $table['border']; 
		$cell['border_details']['L']['dom'] = 1; 
		$cell['border_details']['R']['dom'] = 1; 
		$cell['border_details']['T']['dom'] = 1; 
		$cell['border_details']['B']['dom'] = 1; 
	  }
	} 

	// INHERITED THEAD CSS Properties
	if ($this->tablethead) { 
		if ($this->thead_valign_default) $cell['va'] = $align[strtolower($this->thead_valign_default)]; 
		if ($this->thead_textalign_default) $cell['a'] = $align[strtolower($this->thead_textalign_default)]; 
		if ($this->thead_font_weight == 'B') { $this->SetStyle('B',true); }
		// ADDED in mPDF 1.1
		if ($this->thead_font_style == 'I') { $this->SetStyle('I',true); }
	}

	// INHERITED TFOOT CSS Properties
	if ($this->tabletfoot) { 
		if ($this->tfoot_valign_default) $cell['va'] = $align[strtolower($this->tfoot_valign_default)]; 
		if ($this->tfoot_textalign_default) $cell['a'] = $align[strtolower($this->tfoot_textalign_default)]; 
		if ($this->tfoot_font_weight == 'B') { $this->SetStyle('B',true); }
		// ADDED in mPDF 1.1
		if ($this->tfoot_font_style == 'I') { $this->SetStyle('I',true); }
	}


	// Edited mPDF 1.3 for rotated text in cell
	if ($this->trow_text_rotate) {
		$cell['R'] = $this->trow_text_rotate; 
	}

	// ADDED CSS FUNCIONS FOR TABLE // mPDF 1.2 add 1st parameter 'TD or TH as $tag
		$this->cell_border_dominance_L = 0; 
		$this->cell_border_dominance_R = 0; 
		$this->cell_border_dominance_T = 0; 
		$this->cell_border_dominance_B = 0; 

		// mPDF 2.1. Table CSS
		$properties = $this->MergeCSS('TABLE',$tag,$attr);
		// Added mPDF 2.0 - font-weight, font-style and color set in TABLE
		// Changed mPDF 2.1 _recursive_unique
		$properties = array_merge_recursive_unique($this->base_table_properties, $properties);
		if ($properties['BACKGROUND-COLOR']) { $cell['bgcolor'] = $properties['BACKGROUND-COLOR']; }
		// ADDED in mPDF 2.0
		else if ($properties['BACKGROUND']) { $cell['bgcolor'] = $properties['BACKGROUND']; }


	// mPDF 3.0 - Tiling Patterns
	if ($properties['BACKGROUND-IMAGE'] && !$this->ColActive && !$this->keep_block_together) { 
		$file = $properties['BACKGROUND-IMAGE'];
		$sizesarray = $this->Image($file,0,0,0,0,'','',false);
		if (isset($sizesarray['IMAGE_ID'])) {
			$image_id = $sizesarray['IMAGE_ID'];
			$orig_w = $sizesarray['WIDTH']*$this->k;		// in user units i.e. mm
 			$orig_h = $sizesarray['HEIGHT']*$this->k;		// (using $this->img_dpi)
			$x_repeat = true;
			$y_repeat = true;
			if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-x') { $y_repeat = false; }
			if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-y') { $x_repeat = false; }
			$x_pos = 0;
			$y_pos = 0;
			if ($properties['BACKGROUND-POSITION']) { 
				$ppos = preg_split('/\s+/',$properties['BACKGROUND-POSITION']);
				$x_pos = $ppos[0];
				$y_pos = $ppos[1];
				if (!stristr($x_pos ,'%') ) { $x_pos = ConvertSize($x_pos ,$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
				if (!stristr($y_pos ,'%') ) { $y_pos = ConvertSize($y_pos ,$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
			}
			$cell['background-image'] = array('image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat);
		}
	}





		if ($properties['VERTICAL-ALIGN']) { $cell['va']=$align[strtolower($properties['VERTICAL-ALIGN'])]; }
		if ($properties['TEXT-ALIGN']) { $cell['a'] = $align[strtolower($properties['TEXT-ALIGN'])]; }

		// Added mPDF 1.3 for rotated text in cell
		if ($properties['TEXT-ROTATE'])	{ 
			$cell['R'] = $properties['TEXT-ROTATE']; 
		}
		if ($properties['BORDER']) { 
			$bord = $this->border_details($properties['BORDER']);
			if ($bord['s']) {
				$cell['border'] = _BORDER_ALL;
				$cell['border_details']['R'] = $bord;
				$cell['border_details']['L'] = $bord;
				$cell['border_details']['T'] = $bord;
				$cell['border_details']['B'] = $bord;
				$cell['border_details']['L']['dom'] = $this->cell_border_dominance_L; 
				$cell['border_details']['R']['dom'] = $this->cell_border_dominance_R; 
				$cell['border_details']['T']['dom'] = $this->cell_border_dominance_T; 
				$cell['border_details']['B']['dom'] = $this->cell_border_dominance_B; 
			}
		}

		if ($properties['BORDER-RIGHT']) { 
			$cell['border_details']['R'] = $this->border_details($properties['BORDER-RIGHT']);
			$this->setBorder ($cell['border'], _BORDER_RIGHT, $cell['border_details']['R']['s']); 
			$cell['border_details']['R']['dom'] = $this->cell_border_dominance_R; 
		}
		if ($properties['BORDER-LEFT']) { 
			$cell['border_details']['L'] = $this->border_details($properties['BORDER-LEFT']);
			$this->setBorder ($cell['border'], _BORDER_LEFT, $cell['border_details']['L']['s']); 
			$cell['border_details']['L']['dom'] = $this->cell_border_dominance_L; 
		}
		if ($properties['BORDER-BOTTOM']) { 
			$cell['border_details']['B'] = $this->border_details($properties['BORDER-BOTTOM']);
			$this->setBorder ($cell['border'], _BORDER_BOTTOM, $cell['border_details']['B']['s']); 
			$cell['border_details']['B']['dom'] = $this->cell_border_dominance_B; 
		}
		if ($properties['BORDER-TOP']) { 
			$cell['border_details']['T'] = $this->border_details($properties['BORDER-TOP']);
			$this->setBorder ($cell['border'], _BORDER_TOP, $cell['border_details']['T']['s']); 
			$cell['border_details']['T']['dom'] = $this->cell_border_dominance_T; 
		}

		if ($properties['PADDING-LEFT']) { 
			$cell['padding']['L'] = ConvertSize($properties['PADDING-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		}
		if ($properties['PADDING-RIGHT']) { 
			$cell['padding']['R'] = ConvertSize($properties['PADDING-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		}
		if ($properties['PADDING-BOTTOM']) { 
			$cell['padding']['B'] = ConvertSize($properties['PADDING-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		}
		if ($properties['PADDING-TOP']) { 
			$cell['padding']['T'] = ConvertSize($properties['PADDING-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false);
		}

		// mPDF 2.2
		$w = '';
		if ($properties['WIDTH']) { $w = $properties['WIDTH']; }
		if ($attr['WIDTH']) { $w = $attr['WIDTH']; }
		if ($w) { 
			if (strpos($w,'%') && !$this->ignore_table_percents ) { $cell['wpercent'] = $w + 0; }	// makes 80% -> 80
			else if (!strpos($w,'%') && !$this->ignore_table_widths ) { $cell['w'] = ConvertSize($w,$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
		}

		// Added mPDF 1.2 to add CSS
		if ($properties['COLOR']) {
		  $cor = ConvertColor($properties['COLOR']);
		  if ($cor) { 
			$this->colorarray = $cor;
			$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
			$this->issetcolor=true;
		  }
		}
		if ($properties['FONT-FAMILY']) { 			// NOT CHANGE DEFAULT
		   if (!$this->isCJK) { 
			$this->SetFont($properties['FONT-FAMILY'],'',0,false);
		   }
		}
		if ($properties['FONT-SIZE']) { 
		   $mmsize = ConvertSize($properties['FONT-SIZE'],$this->default_font_size/$this->k);
 		   // mPDF 2.3
		   if ($mmsize) {
  			$this->SetFontSize($mmsize*(72/25.4),false);
		   }
		}

		if ($properties['FONT-WEIGHT']) {
			if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->SetStyle('B',true); }
		}
		if ($properties['FONT-STYLE']) {
			if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $this->SetStyle('I',true); }
		}

		// Added mPDF 2.0
		if ($properties['WHITE-SPACE']) {
			if (strtoupper($properties['WHITE-SPACE']) == 'NOWRAP') { $cell['nowrap']= 1; }
		}

		$properties = array();


	if (isset($attr['HEIGHT'])) $cell['h']	= ConvertSize($attr['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);

	if (isset($attr['ALIGN'])) $cell['a'] = $align[strtolower($attr['ALIGN'])];
	if (isset($attr['VALIGN'])) $cell['va'] = $align[strtolower($attr['VALIGN'])];


	// mPDF 2.0 Added
	if ($this->cellPaddingSetInTable) {
		$cell['padding']['L'] = $this->cellPaddingSetInTable;
		$cell['padding']['R'] = $this->cellPaddingSetInTable;
		$cell['padding']['T'] = $this->cellPaddingSetInTable;
		$cell['padding']['B'] = $this->cellPaddingSetInTable;
	}


	if (isset($attr['BGCOLOR'])) $cell['bgcolor'] = $attr['BGCOLOR'];



	$cs = $rs = 1;
	if (isset($attr['COLSPAN']) && $attr['COLSPAN']>1)	$cs = $cell['colspan']	= $attr['COLSPAN'];

	// mPDF 2.1 Update number columns
	if ($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'] < $this->col+$cs) { 
		$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'] = $this->col+$cs; 
		for($l=$this->col; $l < $this->col+$cs ;$l++) {
			if ($l-$this->col) $this->cell[$this->row][$l] = 0;
		}
	}

	if (isset($attr['ROWSPAN']) && $attr['ROWSPAN']>1)	$rs = $cell['rowspan']	= $attr['ROWSPAN'];
	for ($k=$this->row ; $k < $this->row+$rs ;$k++) {
		for($l=$this->col; $l < $this->col+$cs ;$l++) {
			if ($k-$this->row || $l-$this->col)	$this->cell[$k][$l] = 0;
		}
	}

	// Added mPDF 1.3 for rotated text in cell
	if (isset($attr['TEXT-ROTATE']))	{ 
		$cell['R'] = $attr['TEXT-ROTATE']; 
	}
	if (isset($attr['NOWRAP'])) $cell['nowrap']= 1;
	// mPDF 2.1 Free resources
	unset($cell );
	break;



    // *********** LISTS ********************

    case 'OL':
    case 'UL':
	$this->listjustfinished = false;
	// mPDF 3.0
	$this->linebreakjustfinished=false;
	$this->lastoptionaltag = ''; // Save current HTML specified optional endtag
	$this->listCSSlvl++;
	if((!$this->tableLevel) && ($this->listlvl == 0)) {
	  //++++++++++++++++++++++++++++
	  // mPDF 2.1
	  if ($this->lastblocklevelchange == 1) { $blockstate = 1; }	// Top margins/padding only
	  else if ($this->lastblocklevelchange < 1) { $blockstate = 0; }	// NO margins/padding
	  // called from block after new div e.g. <div> ... <ol> ...    Outputs block top margin/border and padding
	  if (count($this->textbuffer) == 0 && $this->lastblocklevelchange == 1 && !$this->tableLevel && !$this->kwt) {
		$this->newFlowingBlock( $this->block[$this->blocklevel]['width'],$this->lineheight,'',false,false,1,true);	// true = newblock
		$this->finishFlowingBlock(true);	// true = END of flowing block
	  }
	  else if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer,$blockstate); }

	  $this->textbuffer=array();
	  $this->lastblocklevelchange = -1;
	  //++++++++++++++++++++++++++++
	}
	// ol and ul types are mixed here
	if ($this->listlvl == 0) {
		$this->list_indent = array();
		$this->list_align = array();
		$this->list_lineheight = array();
		$this->InlineProperties['LIST'] = array();
		$this->InlineProperties['LISTITEM'] = array();
	}

	// A simple list for inside a table
	if($this->tableLevel) {
		$this->list_indent[$this->listlvl] = 0;	// mm default indent for each level
		if ($tag == 'OL') $this->listtype = '1';
		else if ($tag == 'UL') $this->listtype = 'disc';
      	if ($this->listlvl > 0) {
			$this->listlist[$this->listlvl]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
		}
		$this->listlvl++;
		$this->listnum = 0; // reset
		$this->listlist[$this->listlvl] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
		break;
	}

	if ($this->listCSSlvl==1) {
		$properties = $this->MergeCSS('TOPLIST',$tag,$attr);
	}
	else {
		$properties = $this->MergeCSS('LIST',$tag,$attr);
	}
	if (!empty($properties)) $this->setCSS($properties,'INLINE');

	// List-type
	if ($attr['TYPE']) { $this->listtype = $attr['TYPE']; }
	else if ($properties['LIST-STYLE-TYPE'] || $properties['LIST-STYLE']) { 
	  if (stristr($properties['LIST-STYLE'],'disc') || stristr($properties['LIST-STYLE-TYPE'],'disc')) { $this->listtype = 'disc'; }
	  else if (stristr($properties['LIST-STYLE'],'circle') || stristr($properties['LIST-STYLE-TYPE'],'circle')) { $this->listtype = 'circle'; }
	  else if (stristr($properties['LIST-STYLE'],'square') || stristr($properties['LIST-STYLE-TYPE'],'square')) { $this->listtype = 'square'; }
	  else if (stristr($properties['LIST-STYLE'],'decimal') || stristr($properties['LIST-STYLE-TYPE'],'decimal')) { $this->listtype = '1'; }
	  else if (stristr($properties['LIST-STYLE'],'lower-roman') || stristr($properties['LIST-STYLE-TYPE'],'lower-roman')) { $this->listtype = 'i'; }
	  else if (stristr($properties['LIST-STYLE'],'upper-roman') || stristr($properties['LIST-STYLE-TYPE'],'upper-roman')) { $this->listtype = 'I'; }
	  else if (stristr($properties['LIST-STYLE'],'lower-latin') || stristr($properties['LIST-STYLE-TYPE'],'lower-latin') || stristr($properties['LIST-STYLE'],'lower-alpha') || stristr($properties['LIST-STYLE-TYPE'],'lower-alpha')) { $this->listtype = 'a'; }
	  else if (stristr($properties['LIST-STYLE'],'upper-latin') || stristr($properties['LIST-STYLE-TYPE'],'upper-latin') || stristr($properties['LIST-STYLE'],'upper-alpha') || stristr($properties['LIST-STYLE-TYPE'],'upper-alpha')) { $this->listtype = 'A'; }
	  else if (stristr($properties['LIST-STYLE'],'none') || stristr($properties['LIST-STYLE-TYPE'],'none')) { $this->listtype = 'none'; }
	}
	else if ($tag == 'OL') $this->listtype = '1';
	else if ($tag == 'UL') {
		if ($this->listlvl == 0) $this->listtype = 'disc';
		elseif ($this->listlvl == 1) $this->listtype = 'circle';
		else $this->listtype = 'square';
	}

      if ($this->listlvl == 0)
      {
	  $this->inherit_lineheight = 0;
        $this->listlvl++; // first depth level
        $this->listnum = 0; // reset
        $occur = $this->listoccur[$this->listlvl] = 1;
        $this->listlist[$this->listlvl][1] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
      }
      else
      {
        if (!empty($this->textbuffer))
        {
		$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl],$this->listitemtype);
		$this->listnum++;
        }
	  // Save current lineheight to inherit
	  $this->textbuffer = array();
  	  $occur = $this->listoccur[$this->listlvl];
        $this->listlist[$this->listlvl][$occur]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
        $this->listlvl++;
        $this->listnum = 0; // reset

        if ($this->listoccur[$this->listlvl] == 0) $this->listoccur[$this->listlvl] = 1;
        else $this->listoccur[$this->listlvl]++;
  	  $occur = $this->listoccur[$this->listlvl];
        $this->listlist[$this->listlvl][$occur] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
      }


	// TOP LEVEL ONLY
	if ($this->listlvl == 1) {
	   if ($properties['MARGIN-TOP']) { 
		$this->DivLn(ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false),$this->blklvl,true,1); 	// collapsible
	   }
	   if ($properties['MARGIN-BOTTOM']) { 
		$this->list_margin_bottom = ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); 
	   }
	}
	$this->list_indent[$this->listlvl][$occur] = 5;	// mm default indent for each level
	if ($properties['TEXT-INDENT']) { $this->list_indent[$this->listlvl][$occur] = ConvertSize($properties['TEXT-INDENT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize,false); }
	if ($properties['TEXT-ALIGN']) { $this->list_align[$this->listlvl][$occur] = $align[strtolower($properties['TEXT-ALIGN'])]; }

	if ($properties['LINE-HEIGHT']) { 
		// Edited mPDF 2.0
		if (preg_match('/^[0-9\.,]*$/',$properties['LINE-HEIGHT']) && $properties['LINE-HEIGHT'] >= 1) { $this->list_lineheight[$this->listlvl][$occur] = $properties['LINE-HEIGHT'] + 0; }
		else if (preg_match('/%/',$properties['LINE-HEIGHT']) && $properties['LINE-HEIGHT'] >= 100) { $this->list_lineheight[$this->listlvl][$occur] = ($properties['LINE-HEIGHT'] + 0)/100; }
		else if (strtoupper($properties['LINE-HEIGHT']) == 'NORMAL') { $this->list_lineheight[$this->listlvl][$occur] = $this->default_lineheight_correction; }
		else { $this->list_lineheight[$this->listlvl][$occur] = $this->default_lineheight_correction; }
	}

	$this->InlineProperties['LIST'][$this->listlvl][$occur] = $this->saveInlineProperties();

	$properties = array();

     break;



    case 'LI':
	// Start Block
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
      $this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	// A simple list for inside a table
	if($this->tableLevel) {
	   // mPDF 1.1 Prevents newline after first bullet of list within table
	   $this->blockjustfinished=false;

	   // If already something in the Cell
	   if ((isset($this->cell[$this->row][$this->col]['maxs']) && $this->cell[$this->row][$this->col]['maxs'] > 0 ) || $this->cell[$this->row][$this->col]['s'] > 0 ) {
			$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
			$this->cell[$this->row][$this->col]['text'][] = "\n";
			if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
				$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
			}
			elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
				$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
			}
			$this->cell[$this->row][$this->col]['s'] = 0 ;
		}
		if ($this->listlvl == 0) { //in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
			$this->listlvl++; // first depth level
			$this->listnum = 0; // reset
			$this->listlist[$this->listlvl] = array('TYPE'=>'disc','MAXNUM'=>$this->listnum);
		}
		$this->listnum++;
		switch($this->listlist[$this->listlvl]['TYPE']) {
		case 'A':
			$blt = dec2alpha($this->listnum,true).$this->list_number_suffix;
			break;
		case 'a':
			$blt = dec2alpha($this->listnum,false).$this->list_number_suffix;
			break;
		case 'I':
			$blt = dec2roman($this->listnum,true).$this->list_number_suffix;
			break;
		case 'i':
			$blt = dec2roman($this->listnum,false).$this->list_number_suffix;
			break;
		case '1':
			$blt = $this->listnum.$this->list_number_suffix;
            	break;
		default:
			$blt = '-';
			break;
		}

		// mPDF 3.0 - change to &nbsp; spaces
		if ($this->is_MB) { $ls = str_repeat("\xc2\xa0\xc2\xa0",($this->listlvl-1)*2) . $blt . ' '; }
		else { $ls = str_repeat(chr(160).chr(160),($this->listlvl-1)*2) . $blt . ' '; }

		$this->cell[$this->row][$this->col]['textbuffer'][] = array($ls,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['text'][] = $ls;
		$this->cell[$this->row][$this->col]['s'] += $this->GetStringWidth($ls);
		break;
	}
	//Observation: </LI> is ignored
	if ($this->listlvl == 0) { //in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
	//First of all, skip a line
		$this->listlvl++; // first depth level
		$this->listnum = 0; // reset
		$this->listoccur[$this->listlvl] = 1;
		$this->listlist[$this->listlvl][1] = array('TYPE'=>'disc','MAXNUM'=>$this->listnum);
	}
	if ($this->listnum == 0) {
		$this->listnum++;
		$this->textbuffer = array();
	}
	else {
		if (!empty($this->textbuffer)) {
			$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl],$this->listitemtype);
			$this->listnum++;
		}
		$this->textbuffer = array();
      }

	// mPDF 2.1
	$this->listCSSlvl++;
	$properties = $this->MergeCSS('LIST',$tag,$attr);
	if (!empty($properties)) $this->setCSS($properties,'INLINE');
	$this->InlineProperties['LISTITEM'][$this->listlvl][$this->listoccur[$this->listlvl]][$this->listnum] = $this->saveInlineProperties();

	// List-type
	if ($attr['TYPE']) { $this->listitemtype = $attr['TYPE']; }
	else if ($properties['LIST-STYLE-TYPE'] || $properties['LIST-STYLE']) { 
	  if (stristr($properties['LIST-STYLE'],'disc') || stristr($properties['LIST-STYLE-TYPE'],'disc')) { $this->listitemtype = 'disc'; }
	  else if (stristr($properties['LIST-STYLE'],'circle') || stristr($properties['LIST-STYLE-TYPE'],'circle')) { $this->listitemtype = 'circle'; }
	  else if (stristr($properties['LIST-STYLE'],'square') || stristr($properties['LIST-STYLE-TYPE'],'square')) { $this->listitemtype = 'square'; }
	  else if (stristr($properties['LIST-STYLE'],'decimal') || stristr($properties['LIST-STYLE-TYPE'],'decimal')) { $this->listitemtype = '1'; }
	  else if (stristr($properties['LIST-STYLE'],'lower-roman') || stristr($properties['LIST-STYLE-TYPE'],'lower-roman')) { $this->listitemtype = 'i'; }
	  else if (stristr($properties['LIST-STYLE'],'upper-roman') || stristr($properties['LIST-STYLE-TYPE'],'upper-roman')) { $this->listitemtype = 'I'; }
	  else if (stristr($properties['LIST-STYLE'],'lower-latin') || stristr($properties['LIST-STYLE-TYPE'],'lower-latin') || stristr($properties['LIST-STYLE'],'lower-alpha') || stristr($properties['LIST-STYLE-TYPE'],'lower-alpha')) { $this->listitemtype = 'a'; }
	  else if (stristr($properties['LIST-STYLE'],'upper-latin') || stristr($properties['LIST-STYLE-TYPE'],'upper-latin') || stristr($properties['LIST-STYLE'],'upper-alpha') || stristr($properties['LIST-STYLE-TYPE'],'upper-alpha')) { $this->listitemtype = 'A'; }
	  else if (stristr($properties['LIST-STYLE'],'none') || stristr($properties['LIST-STYLE-TYPE'],'none')) { $this->listitemtype = 'none'; }
	}
	else $this->listitemtype = '';

      break;

  }//end of switch
}



function CloseTag($tag)
{
	$this->ignorefollowingspaces = false; //Eliminate exceeding left-side spaces
    //Closing tag
    if($tag=='OPTION') { $this->selectoption['ACTIVE'] = false; 	$this->lastoptionaltag = ''; }

    if($tag=='TTS' or $tag=='TTA' or $tag=='TTZ') {
	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);
	$ltag = strtolower($tag);
	$this->$ltag = false;
    }


    if($tag=='FONT' || $tag=='SPAN' || $tag=='CODE' || $tag=='KBD' || $tag=='SAMP' || $tag=='TT' || $tag=='VAR' 
	|| $tag=='INS' || $tag=='STRONG' || $tag=='CITE' || $tag=='SUB' || $tag=='SUP' || $tag=='S' || $tag=='STRIKE' || $tag=='DEL'
	|| $tag=='Q' || $tag=='EM' || $tag=='B' || $tag=='I' || $tag=='U' | $tag=='SMALL' || $tag=='BIG' || $tag=='ACRONYM') {

	if ($tag == 'SPAN') {
		if ($this->InlineProperties['SPAN'][$this->spanlvl]) { $this->restoreInlineProperties($this->InlineProperties['SPAN'][$this->spanlvl]); }
		unset($this->InlineProperties['SPAN'][$this->spanlvl]);
		if ($this->InlineAnnots['SPAN'][$this->spanlvl]) { $annot = $this->InlineAnnots['SPAN'][$this->spanlvl]; }
		unset($this->InlineAnnots['SPAN'][$this->spanlvl]);
		$this->spanlvl--;
	}
	else { 
		if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
		unset($this->InlineProperties[$tag]);
		if ($this->InlineAnnots[$tag]) { $annot = $this->InlineAnnots[$tag]; }
		unset($this->InlineAnnots[$tag]);
	}
	// mPDF 2.2 Annotations
	if ($annot) {
		if($this->tableLevel) {
			$this->cell[$this->row][$this->col]['textbuffer'][] = array($annot);
		}
		else  {
			$this->textbuffer[] = array($annot);
		}
	}
    }


    if($tag=='A') {
	$this->HREF=''; 
	if ($this->InlineProperties['A']) { $this->restoreInlineProperties($this->InlineProperties['A']); }
	unset($this->InlineProperties['A']);
    }



	// *********** FORM ELEMENTS ********************

    if($tag=='TEXTAREA') {
	$this->specialcontent = '';
	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);
    }


    if($tag=='SELECT') {
	$this->lastoptionaltag = '';
	$texto = $this->selectoption['SELECTED'];
	$w = $this->GetStringWidth($texto);
	if ($w == 0) { $w = 5; }
	$objattr['type'] = 'select';	// need to add into objattr
	$objattr['text'] = $texto;
	// mPDD 1.4 Active Forms
	$objattr['name'] = $this->selectoption['NAME'];
	$objattr['items'] = $this->selectoption['ITEMS'];
	$objattr['multiple'] = $this->selectoption['MULTIPLE'];
	$objattr['disabled'] = $this->selectoption['DISABLED'];
	$objattr['title'] = $this->selectoption['TITLE'];
	$objattr['color'] = $this->selectoption['COLOR'];
	$objattr['size'] = $this->selectoption['SIZE'];
	if ($objattr['size']>1) { $rows=$objattr['size']; } else { $rows = 1; }

	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;

	$objattr['width'] = $w + ($this->form_element_spacing['select']['outer']['h']*2)+($this->form_element_spacing['select']['inner']['h']*2) + ($this->FontSize*1.4);
	$objattr['height'] = ($this->FontSize*$rows) + ($this->form_element_spacing['select']['outer']['v']*2)+($this->form_element_spacing['select']['inner']['v']*2);
	$e = "\xbb\xa4\xactype=select,objattr=".serialize($objattr)."\xbb\xa4\xac";

	// Clear properties - tidy up
	$properties = array();

	// Output it to buffers
	if ($this->tableLevel) {
		$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
	}
	else {
		$this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}

	$this->selectoption = array();
	$this->specialcontent = '';

	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);

    }


	// *********** BLOCKS ********************

    if($tag=='P' || $tag=='DIV' || $tag=='H1' || $tag=='H2' || $tag=='H3' || $tag=='H4' || $tag=='H5' || $tag=='H6' || $tag=='PRE' 
	 || $tag=='FORM' || $tag=='ADDRESS' || $tag=='BLOCKQUOTE' || $tag=='CENTER' || $tag=='DT'  || $tag=='DD'  || $tag=='DL' ) { 
	$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	$this->blockjustfinished=true;
	if($this->tableLevel) {
		// mPDF 3.0
		if ($this->linebreakjustfinished) { $this->blockjustfinished=false; }
		if ($this->InlineProperties['BLOCKINTABLE']) { $this->restoreInlineProperties($this->InlineProperties['BLOCKINTABLE']); }
		unset($this->InlineProperties['BLOCKINTABLE']);
		return;
	}
	$this->lastoptionaltag = '';
	$this->divbegin=false;
	// mPDF 3.0
	$this->linebreakjustfinished=false;

	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

	// Added mPDF 3.0 Float DIV
	// If float contained in a float, need to extend bottom to allow for it
	$currpos = $this->page*1000 + $this->y;
	if ($this->blk[$this->blklvl]['float_endpos'] && $this->blk[$this->blklvl]['float_endpos'] > $currpos) {
		$old_page = $this->page;
		$new_page = intval($this->blk[$this->blklvl]['float_endpos'] /1000);
		if ($old_page != $new_page) {
			$s = $this->PrintPageBackgrounds();
			// Writes after the marker so not overwritten later by page background etc.
			$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
			$this->pageBackgrounds = array();
			$this->page = $new_page;
			$this->ResetMargins();
			$this->Reset();
			$this->pageoutput[$this->page] = array();
		}
		$this->y = (($this->blk[$this->blklvl]['float_endpos'] *1000) % 1000000)/1000;	// mod changes operands to integers before processing
	}

	//Print content
	if ($this->lastblocklevelchange == 1) { $blockstate = 3; }	// Top & bottom margins/padding
	else if ($this->lastblocklevelchange == -1) { $blockstate = 2; }	// Bottom margins/padding only
	// called from after e.g. </table> </div> </div> ...    Outputs block margin/border and padding

	// mPDF 2.5  right trim last item in text buffer...
	if (count($this->textbuffer) && $this->textbuffer[count($this->textbuffer)-1]) {
	  // mPDF 3.0 ...as long as not special content
	  if (substr($this->textbuffer[count($this->textbuffer)-1],0,3) == "\xbb\xa4\xac") {	// special content
	   if ($this->is_MB) {
		$this->textbuffer[count($this->textbuffer)-1][0] = preg_replace('/[ ]+$/u', '', $this->textbuffer[count($this->textbuffer)-1][0]);
	   }
	   else {
		$this->textbuffer[count($this->textbuffer)-1][0] = preg_replace('/[ ]+$/', '', $this->textbuffer[count($this->textbuffer)-1][0]);
	   }
	  }
	}

	// Edited mPDF 1.1 - </div></div> CSS padding from 1st block was not printing
	if (count($this->textbuffer) == 0 && $this->lastblocklevelchange != 0) {
		$this->newFlowingBlock( $this->block[$this->blocklevel]['width'],$this->lineheight,'',false,false,2,true);	// true = newblock
		$this->finishFlowingBlock(true);	// true = END of flowing block
		$this->PaintDivBB('',$blockstate);
	}
	else {
		$this->printbuffer($this->textbuffer,$blockstate); 
	}


	$this->textbuffer=array();

	// Added mPDF 1.1 keeping block together on one page
	if ($this->blk[$this->blklvl]['keep_block_together']) {
		$this->printdivbuffer(); 
	}

	// mPDF 2.0 Keep-with-table
	if ($this->kwt) {
		$this->kwt_height = $this->y - $this->kwt_y0;
	}

	// mPDF 3.0 Float Images
	if (count($this->floatbuffer)) {
		$this->objectbuffer = $this->floatbuffer;
		$this->printobjectbuffer(false);
		$this->objectbuffer = array();
		$this->floatbuffer = array();
		$this->floatmargins = array();
	}

	if($tag=='PRE') { $this->ispre=false; }

	// Added mPDF 3.0 Float DIV
	if ($this->blk[$this->blklvl]['float'] == 'R') {
		// If width not set, here would need to adjust and output buffer
		$s = $this->PrintPageBackgrounds();
		// Writes after the marker so not overwritten later by page background etc.
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
		$this->pageBackgrounds = array();
		$this->Reset();
		$this->pageoutput[$this->page] = array();

		for($i=($this->blklvl-1); $i >= 0; $i--) {
			$this->blk[$i]['float_endpos'] = max($this->blk[$i]['float_endpos'], ($this->page*1000 + $this->y));
		}

		$this->floatDivs[] = array(
			'side'=>'R',
			'startpage'=>$this->blk[$this->blklvl]['startpage'] ,
			'y0'=>$this->blk[$this->blklvl]['float_start_y'] ,
			'startpos'=> ($this->blk[$this->blklvl]['startpage']*1000 + $this->blk[$this->blklvl]['float_start_y']),
			'endpage'=>$this->page ,
			'y1'=>$this->y ,
			'endpos'=> ($this->page*1000 + $this->y),
			'w'=> $this->blk[$this->blklvl]['float_width'],
			'blklvl'=>$this->blklvl,
			'blockContext' => $this->blk[$this->blklvl-1]['blockContext']
		);

		$this->y = $this->blk[$this->blklvl]['float_start_y'] ;
		$this->page = $this->blk[$this->blklvl]['startpage'] ;
		$this->ResetMargins();
		$this->pageoutput[$this->page] = array();
	}
	if ($this->blk[$this->blklvl]['float'] == 'L') {
		// If width not set, here would need to adjust and output buffer
		$s = $this->PrintPageBackgrounds();
		// Writes after the marker so not overwritten later by page background etc.
		$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.date('jY').')/', '\\1'."\n".$s."\n", $this->pages[$this->page]);
		$this->pageBackgrounds = array();
		$this->Reset();
		$this->pageoutput[$this->page] = array();

		for($i=($this->blklvl-1); $i >= 0; $i--) {
			$this->blk[$i]['float_endpos'] = max($this->blk[$i]['float_endpos'], ($this->page*1000 + $this->y));
		}

		$this->floatDivs[] = array(
			'side'=>'L',
			'startpage'=>$this->blk[$this->blklvl]['startpage'] ,
			'y0'=>$this->blk[$this->blklvl]['float_start_y'] ,
			'startpos'=> ($this->blk[$this->blklvl]['startpage']*1000 + $this->blk[$this->blklvl]['float_start_y']),
			'endpage'=>$this->page ,
			'y1'=>$this->y ,
			'endpos'=> ($this->page*1000 + $this->y),
			'w'=> $this->blk[$this->blklvl]['float_width'],
			'blklvl'=>$this->blklvl,
			'blockContext' => $this->blk[$this->blklvl-1]['blockContext']
		);

		$this->y = $this->blk[$this->blklvl]['float_start_y'] ;
		$this->page = $this->blk[$this->blklvl]['startpage'] ;
		$this->ResetMargins();
		$this->pageoutput[$this->page] = array();
	}

	//Reset values
	$this->Reset();

	if ($this->blklvl > 0) {	// ==0 SHOULDN'T HAPPEN - NOT XHTML 
	   if ($this->blk[$this->blklvl]['tag'] == $tag) {
		unset($this->blk[$this->blklvl]);
		$this->blklvl--;
	   }
	   //else { echo $tag; exit; }	// debug - forces error if incorrectly nested html tags
	}

	$this->lastblocklevelchange = -1 ;
	// Reset Inline-type properties
	if ($this->blk[$this->blklvl]['InlineProperties']) { $this->restoreInlineProperties($this->blk[$this->blklvl]['InlineProperties']); }

	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

    }


	// *********** TABLES ********************

    if($tag=='TH') $this->SetStyle('B',false);

    if(($tag=='TH' or $tag=='TD') && $this->tableLevel) {
	$this->lastoptionaltag = 'TR';
	// mPDF 2.1. Table CSS
	unset($this->tablecascadeCSS[$this->tbCSSlvl]);
	$this->tbCSSlvl--;
	$this->tdbegin = false;

	// Added for correct calculation of cell column width - otherwise misses the last line if not end </p> etc.
	if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
		$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
	}
	elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
		$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
	}

	// Remove last <br> if at end of cell
	$ntb = count($this->cell[$this->row][$this->col]['textbuffer']);
	// mPDF 3.0 ... but only the last one
	if ($ntb>1 && $this->cell[$this->row][$this->col]['textbuffer'][$ntb-1][0] == "\n") {
		unset($this->cell[$this->row][$this->col]['textbuffer'][$ntb-1]);
	}

	// Added mPDF 2.0
	$this->Reset();
    }

    if($tag=='TR' && $this->tableLevel) {
	$this->lastoptionaltag = '';
	// mPDF 2.1. Table CSS
	unset($this->tablecascadeCSS[$this->tbCSSlvl]);
	$this->tbCSSlvl--;
	// Edited mPDF 1.3 for rotated text in cell
	$this->trow_text_rotate = '';
	$this->tabletheadjustfinished = false;
   }

    if($tag=='TBODY') {
	$this->lastoptionaltag = '';
	// mPDF 2.1. Table CSS
	unset($this->tablecascadeCSS[$this->tbCSSlvl]);
	$this->tbCSSlvl--;
    }

    if($tag=='THEAD') {
	$this->lastoptionaltag = '';
	// mPDF 2.1. Table CSS
	unset($this->tablecascadeCSS[$this->tbCSSlvl]);
	$this->tbCSSlvl--;
	$this->tablethead = 0;
	$this->tabletheadjustfinished = true;
	$this->thead_font_weight = '';
	$this->SetStyle('B',false);
	// Added mPDF 1.1
	$this->thead_font_style = '';
	$this->SetStyle('I',false);

	$this->thead_valign_default = '';
	$this->thead_textalign_default = '';
    }

    if($tag=='TFOOT') {
	$this->lastoptionaltag = '';
	// mPDF 2.1. Table CSS
	unset($this->tablecascadeCSS[$this->tbCSSlvl]);
	$this->tbCSSlvl--;
	$this->tfoot_font_weight = '';
	$this->SetStyle('B',false);
	// Added mPDF 1.1
	$this->tfoot_font_style = '';
	$this->SetStyle('I',false);

	$this->tfoot_valign_default = '';
	$this->tfoot_textalign_default = '';
    }



    if($tag=='TABLE') { // TABLE-END (
	$this->lastoptionaltag = '';
	// mPDF 2.1. Table CSS
	unset($this->tablecascadeCSS[$this->tbCSSlvl]);
	$this->tbCSSlvl--;
	$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cells'] = $this->cell;
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['wc'] = array_pad(array(),$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nc'],array('miw'=>0,'maw'=>0));
	$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['hr'] = array_pad(array(),$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['nr'],0);

	// Fix Borders *********************************************
	$this->_fixTableBorders($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]);


	if ($this->ColActive) { $this->table_rotate = 0; }
	if ($this->table_rotate <> 0) {
		$this->tablebuffer = array();
		// Max width for rotated table
		$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5);
		$this->tbrot_maxh = $this->blk[$this->blklvl]['inner_width'] ;		// Max width for rotated table
		// Added mPDF 2.0
		$this->tbrot_align = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['a'] ;
	}
	$this->shrin_k = 1;

	if (!$this->shrink_this_table_to_fit) { $this->shrink_this_table_to_fit = $this->shrink_tables_to_fit; }

	// mPDF 2.0 - Nested tables
	if ($this->tableLevel>1) {
		// deal with nested table

		$this->_tableColumnWidth($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]],true);

		$tmiw = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['miw'];
		$tmaw = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['maw'];
		$tl = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['tl'];

		// Go down to lower table level
		$this->tableLevel--;

		// Reset lower level table
		$this->base_table_properties = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['baseProperties'];
		$this->cell = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['cells'];
		$this->row = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['currrow'];
		$this->col = $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['currcol'];

		// mPDF 2.2
		$objattr['type'] = 'nestedtable';
		$objattr['nestedcontent'] = $this->tbctr[($this->tableLevel+1)];
		$objattr['table'] = $this->tbctr[$this->tableLevel];
		$objattr['row'] = $this->row;
		$objattr['col'] = $this->col;
		$objattr['level'] = $this->tableLevel;
		$e = "\xbb\xa4\xactype=nestedtable,objattr=".serialize($objattr)."\xbb\xa4\xac";
		$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['s'] += $tl ;
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		$this->cell[$this->row][$this->col]['s'] = 0;// reset
		if ($this->cell[$this->row][$this->col]['nestedmaw'] < $tmaw) { $this->cell[$this->row][$this->col]['nestedmaw'] = $tmaw ; }
		if ($this->cell[$this->row][$this->col]['nestedmiw'] < $tmiw) { $this->cell[$this->row][$this->col]['nestedmiw'] = $tmiw ; }
		$this->cell[$this->row][$this->col]['nestedcontent'][] = $this->tbctr[($this->tableLevel+1)];
		$this->tdbegin = true;
		$this->nestedtablejustfinished = true;
		$this->ignorefollowingspaces = true;
		return;
	}
	$this->cMarginL = 0;
	$this->cMarginR = 0;
	$this->cMarginT = 0;
	$this->cMarginB = 0;
	$this->cellPaddingL = 0;
	$this->cellPaddingR = 0;
	$this->cellPaddingT = 0;
	$this->cellPaddingB = 0;

	// mPDF 2.0 Keep-with-table
	if (!$this->kwt_saved) { $this->kwt_height = 0; }

	// mPDF 2.0 - Nested tables
	list($check,$tablemiw) = $this->_tableColumnWidth($this->table[1][1],true);

	// mPDF 2.1 - was using serialize to save, because $save_table was being altered
	// Now unset($c) in _tableColumnWidth stops this happening - a lot faster now!
	$save_table = $this->table;

	$reset_to_minimum_width = false;
	$added_page = false;

	if ($check > 1) {	
		if ($check > $this->shrink_this_table_to_fit && $this->table_rotate) { 
				$this->AddPage($this->CurOrientation);
				$added_page = true;
				$this->kwt_moved = true; 
				$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				// mPDF 3.0
				//$check = $tablemiw/$this->tbrot_maxw; 	// undo any shrink
				$check = 1; 	// undo any shrink
		}
		$reset_to_minimum_width = true;
	}

	if ($reset_to_minimum_width) {

		$this->shrin_k = $check;

 		$this->default_font_size /= $this->shrin_k;
		$this->SetFontSize($this->default_font_size, false );

		$this->shrinkTable($this->table[1][1],$this->shrin_k);

		$this->_tableColumnWidth($this->table[1][1],false);	// repeat

		// mPDF 2.0 - Nested tables
		// Starting at $this->innermostTableLevel
		// Shrink table values - and redo columnWidth
		for($lvl=2;$lvl<=$this->innermostTableLevel;$lvl++) {
			for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
				$this->shrinkTable($this->table[$lvl][$nid],$this->shrin_k);
				$this->_tableColumnWidth($this->table[$lvl][$nid],false);
			}
		}
	}


	// mPDF 2.0 - Nested tables
	// Set table cell widths for top level table
	// Use $shrin_k to resize but don't change again
	$this->SetLineHeight('',$this->table_lineheight);

	// Top level table
	$this->_tableWidth($this->table[1][1]);

	// Now work through any nested tables setting child table[w'] = parent cell['w']
	// Now do nested tables _tableWidth
	for($lvl=2;$lvl<=$this->innermostTableLevel;$lvl++) {
		for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
			// HERE set child table width = cell width

			list($parentrow, $parentcol, $parentnid) = $this->table[$lvl][$nid]['nestedpos'];
			// mPDF 2.3
			if ($this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['colspan']> 1) {
			   $parentwidth = 0;
			   for($cs=0;$cs<$this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['colspan'] ; $cs++) {
				$parentwidth += $this->table[($lvl-1)][$parentnid]['wc'][$parentcol+$cs]; 
			   }
			}
			else { $parentwidth = $this->table[($lvl-1)][$parentnid]['wc'][$parentcol]; }


			//$parentwidth -= ALLOW FOR PADDING ETC.in parent cell
			if ($this->table[$lvl-1][$parentnid]['borders_separate']) {
			  $parentwidth -= $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['border_details']['L']['w']
				+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['border_details']['R']['w']
				+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['padding']['L']
				+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['padding']['R']
				+ $this->table[($lvl-1)][$parentnid]['border_spacing_H'];
			}
			else {
			  $parentwidth -= $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['border_details']['L']['w']/2
				+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['border_details']['R']['w']/2
				+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['padding']['L']
				+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['padding']['R'];
			}
			// mPDF 2.2 - leave ['w'] as maximum if a table percentage width is set
			if ($this->table[$lvl][$nid]['wpercent'] && $lvl>1) {
				$this->table[$lvl][$nid]['w'] = $parentwidth;
			}
			else if ($parentwidth > $this->table[$lvl][$nid]['maw']) {
				$this->table[$lvl][$nid]['w'] = $this->table[$lvl][$nid]['maw'];
			}
			else {
				$this->table[$lvl][$nid]['w'] = $parentwidth;
			}

			$this->_tableWidth($this->table[$lvl][$nid]);
		}
	}

	// mPDF 2.0 - Nested tables
	// Starting at $this->innermostTableLevel
	// Cascade back up nested tables: setting heights back up the tree
	for($lvl=$this->innermostTableLevel;$lvl>0;$lvl--) {
		for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
			list($tableheight,$maxrowheight,$fullpage,$remainingpage) = $this->_tableHeight($this->table[$lvl][$nid]);
			// mpdf2.2 - Nested table height is now dealt with in TableWordWrap
		}
	}

	$recalculate = 1;
	// mPDF 2.0 - RESIZING ALGORITHM
	if ($maxrowheight > $fullpage) { 
		$recalculate = $this->tbsqrt($maxrowheight / $fullpage, 1); 
	}
	else if ($this->table_rotate) {	// NB $remainingpage == $fullpage == the width of the page
		if ($tableheight > $remainingpage) { 
			// If can fit on remainder of page whilst respecting autsize value..
			if (($this->shrin_k * $this->tbsqrt($tableheight / $remainingpage, 1)) <= $this->shrink_this_table_to_fit) {
				$recalculate = $this->tbsqrt($tableheight / $remainingpage, 1); 
			}
			else if (!$added_page) {
				$this->AddPage($this->CurOrientation);
				$added_page = true;
				$this->kwt_moved = true; 
				$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				// mPDF 3.0 added 0.001 to force it to recalculate
				$recalculate = (1 / $this->shrin_k) + 0.001; 	// undo any shrink
			}
		}
	}
	else if ($this->table_keep_together) {
		if ($tableheight > $fullpage) { 
			// mPDF 2.2
			if (($this->shrin_k * $this->tbsqrt($tableheight / $fullpage, 1)) <= $this->shrink_this_table_to_fit) {
				$recalculate = $this->tbsqrt($tableheight / $fullpage, 1); 
			}
			else {
				$this->AddPage($this->CurOrientation);
				$added_page = true;
				$this->kwt_moved = true; 
				$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				$recalculate = $this->tbsqrt($tableheight / $fullpage, 1); 
			}
		}
		else if ($tableheight > $remainingpage) { 
			// If can fit on remainder of page whilst respecting autsize value..
			if (($this->shrin_k * $this->tbsqrt($tableheight / $remainingpage, 1)) <= $this->shrink_this_table_to_fit) {
				$recalculate = $this->tbsqrt($tableheight / $remainingpage, 1); 
			}
			else {
				$this->AddPage($this->CurOrientation);
				$added_page = true;
				$this->kwt_moved = true; 
				$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				// mPDF 2.2
				// mPDF 3.0 added 0.001 to force it to recalculate
				$recalculate = (1 / $this->shrin_k) + 0.001; 	// undo any shrink
			}
		}
	}
	else { $recalculate = 1; }

	if ($recalculate > $this->shrink_this_table_to_fit) { $recalculate = $this->shrink_this_table_to_fit; }

	$iteration = 2;

	// mPDF 2.0 - RECALCULATE
	while($recalculate <> 1) {
		$this->shrin_k1 = $recalculate ;
		$this->shrin_k *= $recalculate ;
		// Added mPDF 2.0
 		$this->default_font_size /= ($this->shrin_k1) ;
		$this->SetFontSize($this->default_font_size, false );
		$this->SetLineHeight('',$this->table_lineheight);

		// mPDF 2.1
		$this->table = $save_table;
		if ($this->shrin_k <> 1) { $this->shrinkTable($this->table[1][1],$this->shrin_k); }
		$this->_tableColumnWidth($this->table[1][1],false);	// repeat

		// mPDF 2.0 - Nested tables
		// Starting at $this->innermostTableLevel
		// Shrink table values - and redo columnWidth
		for($lvl=2;$lvl<=$this->innermostTableLevel;$lvl++) {
			for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
				if ($this->shrin_k <> 1) { $this->shrinkTable($this->table[$lvl][$nid],$this->shrin_k); }
				$this->_tableColumnWidth($this->table[$lvl][$nid],false);
			}
		}
		// mPDF 2.0 - Nested tables
		// Set table cell widths for top level table

		// Top level table
		$this->_tableWidth($this->table[1][1]);

		// Now work through any nested tables setting child table[w'] = parent cell['w']
		// Now do nested tables _tableWidth
		for($lvl=2;$lvl<=$this->innermostTableLevel;$lvl++) {
			for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
				// HERE set child table width = cell width

				list($parentrow, $parentcol, $parentnid) = $this->table[$lvl][$nid]['nestedpos'];
				// mPDF 2.3
				if ($this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['colspan']> 1) {
				   $parentwidth = 0;
				   for($cs=0;$cs<$this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['colspan'] ; $cs++) {
					$parentwidth += $this->table[($lvl-1)][$parentnid]['wc'][$parentcol+$cs]; 
				   }
				}
				else { $parentwidth = $this->table[($lvl-1)][$parentnid]['wc'][$parentcol]; }

				//$parentwidth -= ALLOW FOR PADDING ETC.in parent cell
				if ($this->table[$lvl-1][$parentnid]['borders_separate']) {
				  $parentwidth -= $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['border_details']['L']['w']
					+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['border_details']['R']['w']
					+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['padding']['L']
					+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['padding']['R']
					+ $this->table[($lvl-1)][$parentnid]['border_spacing_H'];
				}
				else {
				  $parentwidth -= ($this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['border_details']['L']['w']
					+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['border_details']['R']['w']) /2
					+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['padding']['L']
					+ $this->table[($lvl-1)][$parentnid]['cells'][$parentrow][$parentcol]['padding']['R'];
				}
	
				// mPDF 2.2 - leave ['w'] as maximum if a table percentage width is set
				if ($this->table[$lvl][$nid]['wpercent'] && $lvl>1) {
					$this->table[$lvl][$nid]['w'] = $parentwidth;
				}
				else if ($parentwidth > $this->table[$lvl][$nid]['maw']) {
					$this->table[$lvl][$nid]['w'] = $this->table[$lvl][$nid]['maw'] ;
				}
				else {
					$this->table[$lvl][$nid]['w'] = $parentwidth;
				}
				$this->_tableWidth($this->table[$lvl][$nid]);
			}
		}
	

		// mPDF 2.0 - Nested tables
		// Starting at $this->innermostTableLevel
		// Cascade back up nested tables: setting heights back up the tree
		for($lvl=$this->innermostTableLevel;$lvl>0;$lvl--) {
			for ($nid=1; $nid<=$this->tbctr[$lvl]; $nid++) {
				list($tableheight,$maxrowheight,$fullpage,$remainingpage) = $this->_tableHeight($this->table[$lvl][$nid]);
				// mpdf2.2 - Nested table height is now dealt with in TableWordWrap
			}
		}

		// mPDF 2.0 - RESIZING ALGORITHM

		if ($maxrowheight > $fullpage) { $recalculate = $this->tbsqrt($maxrowheight / $fullpage, $iteration); $iteration++; }
		else if ($this->table_rotate && $tableheight > $remainingpage && !$added_page) { 
			// If can fit on remainder of page whilst respecting autosize value..
			if (($this->shrin_k * $this->tbsqrt($tableheight / $remainingpage, $iteration)) <= $this->shrink_this_table_to_fit) {
				$recalculate = $this->tbsqrt($tableheight / $remainingpage, $iteration); $iteration++; 
			}
			else {
				if (!$added_page) { 
					$this->AddPage($this->CurOrientation); 
					$added_page = true;
					$this->kwt_moved = true; 
					$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				}
				// mPDF 3.0 added 0.001 to force it to recalculate
				$recalculate = (1 / $this->shrin_k) + 0.001; 	// undo any shrink
			}
		}
		else if ($this->table_keep_together) {
			if ($tableheight > $fullpage) { 
				if (($this->shrin_k * $this->tbsqrt($tableheight / $fullpage, $iteration)) <= $this->shrink_this_table_to_fit) {
					$recalculate = $this->tbsqrt($tableheight / $fullpage, $iteration); $iteration++; 
				}
				else {
				   if (!$added_page) { 
					$this->AddPage($this->CurOrientation);
					$added_page = true;
					$this->kwt_moved = true; 
					$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
				   }
				   $recalculate = $this->tbsqrt($tableheight / $fullpage, $iteration); $iteration++; 
				}
			}
			else if ($tableheight > $remainingpage) { 
				// If can fit on remainder of page whilst respecting autosize value..
				if (($this->shrin_k * $this->tbsqrt($tableheight / $remainingpage, $iteration)) <= $this->shrink_this_table_to_fit) {
					// mPDF 2.2 Add iteration++
					$recalculate = $this->tbsqrt($tableheight / $remainingpage, $iteration);  $iteration++; 
				}
				else {
					if (!$added_page) { 
						$this->AddPage($this->CurOrientation); 
						$added_page = true;
						$this->kwt_moved = true; 
						$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5) - $this->kwt_height;
					}
					$recalculate = $this->tbsqrt($tableheight / $fullpage, $iteration); $iteration++; 
				}
			}
			else { $recalculate = 1; }
		}
		else { $recalculate = 1; }

	}

	// mPDF 2.0 keep-with-table: if page has advanced, print out buffer now, else done in fn. _Tablewrite()
	if ($this->kwt_saved && $this->kwt_moved) {
		$this->printkwtbuffer();
		$this->kwt_moved = false;
		$this->kwt_saved = false;
	}

	// Recursively writes all tables starting at top level
	$this->_tableWrite($this->table[1][1]);

	if ($this->table_rotate && count($this->tablebuffer)) {
		$this->PageBreakTrigger=$this->h-$this->bMargin;
		$save_tr = $this->table_rotate;
		$save_y = $this->y;
		$this->table_rotate = 0;
		$this->y = $this->tbrot_y0;
		$h = 	$this->tbrot_w;
		$this->DivLn($h,$this->blklvl,true);

		$this->table_rotate = $save_tr;
		$this->y = $save_y;

		$this->printtablebuffer();
	}

	$this->table_rotate = 0;	// flag used for table rotation

	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

	//Reset values

	// mPDF 2.0 Keep-with-table
	$this->kwt = false;
	$this->kwt_y0 = 0;
	$this->kwt_x0 = 0;
	$this->kwt_height = 0;
	$this->kwt_buffer = array();
	$this->kwt_Links = array();
	// mPDF 2.2 Annotations
	$this->kwt_Annots = array();
	$this->kwt_moved = false;
	$this->kwt_saved = false;
	// mPDF 3.0
	$this->kwt_Reference = array();
	$this->kwt_BMoutlines = array();
	$this->kwt_toc = array();

	$this->shrin_k = 1;
	$this->shrink_this_table_to_fit = 0;

	// mPDF 2.1
	unset($this->table);
	$this->table=array(); //array 
	$this->tableLevel=0;
	$this->tbctr=array();
	$this->innermostTableLevel=0;
	// mPDF 2.1. Table CSS
	$this->tbCSSlvl = 0;
	$this->tablecascadeCSS = array();

	// mPDF 2.1
	unset($this->cell);
	$this->cell=array(); //array 

	$this->col=-1; //int
	$this->row=-1; //int
	$this->Reset();

 	$this->cellPaddingL = 0;
 	$this->cellPaddingT = 0;
 	$this->cellPaddingR = 0;
 	$this->cellPaddingB = 0;
 	$this->cMarginL = 0;
 	$this->cMarginT = 0;
 	$this->cMarginR = 0;
 	$this->cMarginB = 0;
	// Added mPDF 2.0
 	$this->default_font_size = $this->original_default_font_size;
	$this->default_font = $this->original_default_font;
   	$this->SetFontSize($this->default_font_size, false);
	$this->SetFont($this->default_font,'',0,false);
	$this->SetLineHeight();
	if ($this->blk[$this->blklvl]['InlineProperties']) { $this->restoreInlineProperties($this->blk[$this->blklvl]['InlineProperties']);}

    }


	// *********** LISTS ********************

    if($tag=='LI') { 
	$this->lastoptionaltag = ''; 
	// mPDF 2.1
	unset($this->listcascadeCSS[$this->listCSSlvl]);
	$this->listCSSlvl--;
	if ($this->InlineProperties['LIST'][$this->listlvl][$this->listoccur[$this->listlvl]]) { $this->restoreInlineProperties($this->InlineProperties['LIST'][$this->listlvl][$this->listoccur[$this->listlvl]]); } 
    }


    if(($tag=='UL') or ($tag=='OL')) {
      $this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	unset($this->listcascadeCSS[$this->listCSSlvl]);
	$this->listCSSlvl--;

	$this->lastoptionaltag = '';
	// A simple list for inside a table
	if($this->tableLevel) {
		$this->listlist[$this->listlvl]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
		unset($this->listlist[$this->listlvl]);
		$this->listlvl--;
		$this->listnum = $this->listlist[$this->listlvl]['MAXNUM']; // restore previous levels
		// Added mPDF 2.0
		if ($this->listlvl == 0) { $this->listjustfinished = true; }
		return;
	}

	if ($this->listlvl > 1) { // returning one level
		$this->listjustfinished=true;
		if (!empty($this->textbuffer)) { 
			$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl],$this->listitemtype);
		}
		// mPDF 3.0
		else { 
			$this->listnum--;
		}

		$this->textbuffer = array();
		$occur = $this->listoccur[$this->listlvl]; 
		$this->listlist[$this->listlvl][$occur]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
		$this->listlvl--;
		$occur = $this->listoccur[$this->listlvl];
		$this->listnum = $this->listlist[$this->listlvl][$occur]['MAXNUM']; // recover previous level's number
		$this->listtype = $this->listlist[$this->listlvl][$occur]['TYPE']; // recover previous level's type
		if ($this->InlineProperties['LIST'][$this->listlvl][$occur]) { $this->restoreInlineProperties($this->InlineProperties['LIST'][$this->listlvl][$occur]); }

	}
	else { // We are closing the last OL/UL tag
		if (!empty($this->textbuffer)) {
			$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl],$this->listitemtype);
		}
		// mPDF 3.0
		else { 
			$this->listnum--;
		}

		// mPDF 2.1
		$occur = $this->listoccur[$this->listlvl]; 
		$this->listlist[$this->listlvl][$occur]['MAXNUM'] = $this->listnum;
		$this->textbuffer = array();
		$this->listlvl--;

		$this->printlistbuffer();
		unset($this->InlineProperties['LIST']);
		// SPACING AFTER LIST (Top level only)
		$this->Ln(0);
		if ($this->list_margin_bottom) {
			$this->DivLn($this->list_margin_bottom,$this->blklvl,true,1); 	// collapsible
		}
		if ($this->blk[$this->blklvl]['InlineProperties']) { $this->restoreInlineProperties($this->blk[$this->blklvl]['InlineProperties']);}
		// Added mPDF 2.0
		$this->listjustfinished = true; 
		$this->listCSSlvl = 0;
		$this->listcascadeCSS = array();
	}
    }


}


// This function determines the shrink factor when resizing tables
// val is the table_height / page_height_available
// returns a scaling factor used as $shrin_k to resize the table
// Overcompensating will be quicker but may unnecessarily shrink table too much
// Undercompensating means it will reiterate more times (taking more processing time)
/*  original - takes along time if single cell as needs to return $val itself - or close to it
function tbsqrt($val) {
	// Probably best guess and most accurate
//	return sqrt($val);
	// Faster than using sqrt (because it won't undercompensate), and gives reasonable results
	return 1+(($val-1)/2);
}
*/
function tbsqrt($val, $iteration=3) {
	$k = 4;	// Alters number of iterations until it returns $val itself - Must be > 2
	// Probably best guess and most accurate
	if ($iteration==1) return sqrt($val);
	// Faster than using sqrt (because it won't undercompensate), and gives reasonable results
	//return 1+(($val-1)/2);
	if (2-(($iteration-2)/($k-2)) == 0) { return $val; }
	return 1+(($val-1)/(2-(($iteration-2)/($k-2))));
}


function printlistbuffer()
{
    //Save x coordinate
    $x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];
    $this->cMarginL = 0;
    $this->cMarginR = 0;
    $currIndentLvl = -1;
    $lastIndent = array();
    $bak_page = $this->page;
    foreach($this->listitem as $item)
    {
	// COLS
	$oldcolumn = $this->CurrCol;

	  $this->bulletarray = array();
        //Get list's buffered data
        $this->listlvl = $lvl = $item[0];
        $num = $item[1];
        $this->textbuffer = $item[2];
        $occur = $item[3];
	  if ($item[4]) { $type = $item[4]; }	// listitemtype
	  else { $type = $this->listlist[$lvl][$occur]['TYPE']; }
        $maxnum = $this->listlist[$lvl][$occur]['MAXNUM'];
	  $this->restoreInlineProperties($this->InlineProperties['LIST'][$lvl][$occur]);

	  // mPDF 2.1 - use list's default font-size to set spacing for indent / bullet position
	  // $this->InlineProperties['LISTITEM'] is restored in write/finishflowingblock()

	  $this->SetFont($this->FontFamily,$this->FontStyle,$this->FontSizePt,true,true);	// force to write

	  $clh = $this->FontSize;

	  // Set the bullet fontsize
	  $bullfs = $this->InlineProperties['LISTITEM'][$lvl][$occur][$num]['size'];

        $space_width = $this->GetStringWidth(' ') * 1.5;

        //Set default width & height values
        $this->divwidth = $this->blk[$this->blklvl]['inner_width'];
        $this->divheight = $this->lineheight;
	  $typefont = $this->FontFamily;
        switch($type) //Format type
        {
          case 'none':
		  $type = '';
  	        $blt_width = 0;
             break;
          case 'A':
              $anum = dec2alpha($num,true);
              $maxnum = dec2alpha($maxnum,true);
		  if ($this->directionality == 'rtl') { $type = $this->list_number_suffix . $anum; }
		  else { $type = $anum . $this->list_number_suffix; }
  	        $blt_width = $this->GetStringWidth(str_repeat('W',strlen($maxnum)).$this->list_number_suffix);
             break;
          case 'a':
              $anum = dec2alpha($num,false);
              $maxnum = dec2alpha($maxnum,false);
		  if ($this->directionality == 'rtl') { $type = $this->list_number_suffix . $anum; }
		  else { $type = $anum . $this->list_number_suffix; }
 	        $blt_width = $this->GetStringWidth(str_repeat('w',strlen($maxnum)).$this->list_number_suffix);
             break;
          case 'I':
              $anum = dec2roman($num,true);
		  if (($maxnum>7 && $maxnum<10) || ($maxnum%10) > 7) { $lbit = 8; } // VIII
		  else if (($maxnum>6 && $maxnum<10) || ($maxnum%10) > 6) { $lbit = 7; } // VII
		  else if (($maxnum%10) >3) { $lbit = 4; } // IV
		  else { $lbit = $maxnum%10; }
		  if ($maxnum > 29) { $hbit = 30; } // XXX
		  else if ($maxnum >79) { $hbit = 80; } // LXXX
		  else { $hbit = floor(($maxnum-3)/10) * 10; }
              $maxlnum = dec2roman(($hbit+$lbit),true);
		  if ($this->directionality == 'rtl') { $type = $this->list_number_suffix . $anum; }
		  else { $type = $anum . $this->list_number_suffix; }
	        $blt_width = $this->GetStringWidth($maxlnum.$this->list_number_suffix);
              break;
          case 'i':
              $anum = dec2roman($num,false);
		  if (($maxnum>7 && $maxnum<10) || ($maxnum%10) > 7) { $lbit = 8; } // VIII
		  else if (($maxnum>6 && $maxnum<10) || ($maxnum%10) > 6) { $lbit = 7; } // VII
		  else if (($maxnum%10) >3) { $lbit = 4; } // IV
		  else { $lbit = $maxnum%10; }
		  if ($maxnum > 29) { $hbit = 30; } // XXX
		  else if ($maxnum >79) { $hbit = 80; } // LXXX
		  else { $hbit = floor(($maxnum-3)/10) * 10; }
              $maxlnum = dec2roman(($hbit+$lbit),false);
		  if ($this->directionality == 'rtl') { $type = $this->list_number_suffix . $anum; }
		  else { $type = $anum . $this->list_number_suffix; }
	        $blt_width = $this->GetStringWidth(str_repeat('X',strlen($maxnum)).$this->list_number_suffix);
              break;
          case 'disc':
              $type = $this->chrs[108]; // bullet disc in Zapfdingbats  'l'
		  $typefont = 'zapfdingbats';
		  $blt_width = (0.791 * $this->FontSize/2.5); 
              break;
          case 'circle':
              $type = $this->chrs[109]; // circle in Zapfdingbats   'm'
		  $typefont = 'zapfdingbats';
		  $blt_width = (0.873 * $this->FontSize/2.5); 
              break;
          case 'square':
              $type = $this->chrs[110]; //black square in Zapfdingbats font   'n'
		  $typefont = 'zapfdingbats';
		  $blt_width = (0.761 * $this->FontSize/2.5); 
              break;
          case '1':
	    default:
		  if ($this->directionality == 'rtl') { $type = $this->list_number_suffix . $num; }
		  else { $type = $num . $this->list_number_suffix; }
	        $blt_width = $this->GetStringWidth(str_repeat('5',strlen($maxnum)).$this->list_number_suffix);
              break;
        }


	if ($currIndentLvl < $lvl) {
		if ($lvl > 1 || $this->list_indent_first_level) { 
			$indent += $this->list_indent[$lvl][$occur]; 
			$lastIndent[$lvl] = $this->list_indent[$lvl][$occur];
		}
	}
	else if ($currIndentLvl > $lvl) {
	  while ($currIndentLvl > $lvl) {
		$indent -= $lastIndent[$currIndentLvl];
		$currIndentLvl--;
	  }
	}
	$currIndentLvl = $lvl;

	$this->divalign = $this->list_align[$this->listlvl][$occur];
	if ($this->directionality == 'rtl') { 
	  if ($this->list_align_style == 'L') { $lalign = 'R'; $xadj = 0; }
	  else { $lalign = 'L';  $xadj = $space_width; }
        $this->divwidth = $this->blk[$this->blklvl]['width'] - ($indent + $blt_width + $space_width) ;
        $xb = $this->blk[$this->blklvl]['inner_width'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] - $indent - $blt_width; //Bullet position (relative)
        //Output bullet
	  $this->bulletarray = array('w'=>$blt_width,'h'=>$clh,'txt'=>$type,'x'=>$xb,'align'=>$lalign,'font'=>$typefont,'level'=>$lvl, 'occur'=>$occur, 'num'=>$num, 'fontsize'=>$bullfs );
	  $this->x = $x;
	}
	else {
	  if ($this->list_align_style == 'L') { $lalign = 'L'; $xadj = 0; }
	  else { $lalign = 'R';  $xadj = $space_width; }
	  $blt_width += $space_width;
        $this->divwidth = $this->blk[$this->blklvl]['width'] - ($indent + $blt_width) ;
	  $xb =  $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] - $blt_width - $xadj; 
        //Output bullet
	  $this->bulletarray = array('w'=>$blt_width,'h'=>$clh,'txt'=>$type,'x'=>$xb,'align'=>$lalign,'font'=>$typefont,'level'=>$lvl, 'occur'=>$occur, 'num'=>$num, 'fontsize'=>$bullfs );
	  $this->x = $x + $indent + $blt_width;
	}

      //Print content
  	$this->printbuffer($this->textbuffer,'',false,true);
      $this->textbuffer=array();

	// Added to correct for OddEven Margins
   	if  ($this->page != $bak_page) {
		$x=$x +$this->MarginCorrection;
		$bak_page = $this->page;
	}
	// OR COLUMN CHANGE
	if ($this->CurrCol != $oldcolumn) {
		if ($this->directionality == 'rtl') {
			$x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
		}
		else {
			$x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
		}
		$oldcolumn = $this->CurrCol;
	}

    }

    //Reset all used values
    $this->listoccur = array();
    $this->listitem = array();
    $this->listlist = array();
    $this->listlvl = 0;
    $this->listnum = 0;
    $this->listtype = '';
    $this->textbuffer = array();
    $this->divwidth = 0;
    $this->divheight = 0;
    $this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];
}




function printbuffer($arrayaux,$blockstate=0,$is_table=false,$is_list=false)
{
// $blockstate = 0;	// NO margins/padding
// $blockstate = 1;	// Top margins/padding only
// $blockstate = 2;	// Bottom margins/padding only
// $blockstate = 3;	// Top & bottom margins/padding
	$this->spanbgcolorarray = array();
	$this->spanbgcolor = false;

	// mPDF 3.0 Float DIV
	if (count($this->floatDivs)) {
		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
		if (($this->blk[$this->blklvl]['inner_width']-$l_width-$r_width) < $this->getStringWidth('WW')) {
			// Too narrow to fit - try to move down past L or R float
			if ($l_max < $r_max && ($this->blk[$this->blklvl]['inner_width']-$r_width) > $this->getStringWidth('WW')) {
				$this->ClearFloats('LEFT', $this->blklvl); 
			}
			else if ($r_max < $l_max && ($this->blk[$this->blklvl]['inner_width']-$l_width) > $this->getStringWidth('WW')) {
				$this->ClearFloats('RIGHT', $this->blklvl); 
			}
			else { $this->ClearFloats('BOTH', $this->blklvl); }
		}
	}

    	$bak_y = $this->y;
	$bak_x = $this->x;
	if (!$is_table && !$is_list) {
		if ($this->blk[$this->blklvl]['align']) { $align = $this->blk[$this->blklvl]['align']; }
		// Block-align is set by e.g. <.. align="center"> Takes priority for this block but not inherited
		if ($this->blk[$this->blklvl]['block-align']) { $align = $this->blk[$this->blklvl]['block-align']; }
		$this->divwidth = $this->blk[$this->blklvl]['width'];
	}
	else {
		$align = $this->divalign;
	}
	$oldpage = $this->page;

	// ADDED for Out of Block now done as Flowing Block
	if ($this->divwidth == 0) { 
		$this->divwidth = $this->pgwidth; 
	}

	if (!$is_table && !$is_list) { $this->SetLineHeight($this->FontSizePt,$this->blk[$this->blklvl]['line_height']); }
	$this->divheight = $this->lineheight;
	$old_height = $this->divheight;

    // As a failsafe - if font has been set but not output to page
    $this->SetFont($this->default_font,'',$this->default_font_size,true,true);	// force output to page

    $array_size = count($arrayaux);
    $this->newFlowingBlock( $this->divwidth,$this->divheight,$align,$is_table,$is_list,$blockstate,true);	// true = newblock

	// Added in mPDF 1.1 - Otherwise <div><div><p> did not output top margins/padding for 1st/2nd div
    if ($array_size == 0) { $this->finishFlowingBlock(true); }	// true = END of flowing block

    for($i=0;$i < $array_size; $i++)
    {

	// COLS
	$oldcolumn = $this->CurrCol;

      $vetor = $arrayaux[$i];
      if ($i == 0 and $vetor[0] != "\n" and !$this->ispre) {
		$vetor[0] = ltrim($vetor[0]);
	}

	// FIXED TO ALLOW IT TO SHOW '0' 
      if (empty($vetor[0]) && !($vetor[0]==='0') && empty($vetor[7])) { //Ignore empty text and not carrying an internal link
		//Check if it is the last element. If so then finish printing the block
	     	if ($i == ($array_size-1)) { $this->finishFlowingBlock(true); }	// true = END of flowing block
		continue;
	}


      //Activating buffer properties
      if(isset($vetor[11]) and $vetor[11] != '') { 	 // Font Size
		if ($is_table && $this->shrin_k) {
			$this->SetFontSize($vetor[11]/$this->shrin_k,false); 
		}
		else {
			$this->SetFontSize($vetor[11],false); 
		}
	}
      if(isset($vetor[10]) and !empty($vetor[10])) //Background color
      {
		$this->spanbgcolorarray = $vetor[10];
		$this->spanbgcolor = true;
      }
      if(isset($vetor[9]) and !empty($vetor[9])) // Outline parameters
      {
          $cor = $vetor[9]['COLOR'];
          $outlinewidth = $vetor[9]['WIDTH'];
          $this->SetTextOutline($outlinewidth,$cor['R'],$cor['G'],$cor['B']);
          $this->outline_on = true;
      }
      if(isset($vetor[8]) and $vetor[8] === true) // strike-through the text
      {
          $this->strike = true;
      }
      if(isset($vetor[7]) and $vetor[7] != '') // internal link: <a name="anyvalue">
      {
	  if ($this->ColActive) { $ily = $this->y0; } else { $ily = $this->y; }	// use top of columns
        $this->internallink[$vetor[7]] = array("Y"=>$ily,"PAGE"=>$this->page );
	  // mPDF 3.0
	//  if ($this->anchor2Bookmark ==1) {
	//	$this->Bookmark($vetor[7],0,$ily);
	//  }
	//  else if ($this->anchor2Bookmark == 2) {
	//	$this->Bookmark($vetor[7]." (p.$this->page)",0,$ily);
	//  }
        if (empty($vetor[0])) { //Ignore empty text
		//Check if it is the last element. If so then finish printing the block
      	if ($i == ($array_size-1)) { $this->finishFlowingBlock(true); }	// true = END of flowing block
		continue;
	  }
      }
      if(isset($vetor[6]) and $vetor[6] === true) // Subscript 
      {
  		$this->SUB = true;
      }
      if(isset($vetor[5]) and $vetor[5] === true) // Superscript
      {
		$this->SUP = true;
      }
      if(isset($vetor[4]) and $vetor[4] != '') {  // Font Family
		$font = $this->SetFont($vetor[4],$this->FontStyle,0,false); 
	}
      if (!empty($vetor[3])) //Font Color
      {
		$cor = $vetor[3];
		$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
      }
      if(isset($vetor[2]) and $vetor[2] != '') //Bold,Italic,Underline styles
      {
          if (strpos($vetor[2],"B") !== false) $this->SetStyle('B',true);
          if (strpos($vetor[2],"I") !== false) $this->SetStyle('I',true);
          if (strpos($vetor[2],"U") !== false) $this->SetStyle('U',true); 
      }
      if(isset($vetor[1]) and $vetor[1] != '') //LINK
      {
	  // mPDF 3.0
        if (strpos($vetor[1],".") === false && strpos($vetor[1],"@") !== 0) //assuming every external link has a dot indicating extension (e.g: .html .txt .zip www.somewhere.com etc.) 
        {
          //Repeated reference to same anchor?
          while(array_key_exists($vetor[1],$this->internallink)) $vetor[1]="#".$vetor[1];
          $this->internallink[$vetor[1]] = $this->AddLink();
          $vetor[1] = $this->internallink[$vetor[1]];
        }
        $this->HREF = $vetor[1];					// HREF link style set here ******
      }

	// SPECIAL CONTENT - IMAGES & FORM OBJECTS
      //Print-out special content
	// mPDF 3.0
      if (substr($vetor[0],0,3) == "\xbb\xa4\xac") { //identifier has been identified!
        $content = split("\xbb\xa4\xac",$vetor[0],2);
        $content = split(",",$content[1],2);
        foreach($content as $value) {
          $value = split("=",$value,2);
          $specialcontent[$value[0]] = $value[1];
        }

	  $objattr = unserialize($specialcontent['objattr']);

	  // mPDF 2.2
	  if ($objattr['type'] == 'nestedtable') {
		$level = $objattr['level'];
		$table = &$this->table[$level][$objattr['table']];
		$cell = &$table['cells'][$objattr['row']][$objattr['col']];
		if ($objattr['nestedcontent']) {
            	$this->finishFlowingBlock();
			$save_dw = $this->divwidth ;
			$save_buffer = $this->cellBorderBuffer;
			$this->cellBorderBuffer = array();
			$ncx = $this->x;
			// HORIZONTAL X adjustment for Cell align
			$w = $cell['w'];	// parent cell width
			list($dummyx,$w) = $this->_tableGetWidth($table, $objattr['row'], $objattr['col']);
			$ntw = $this->table[($level+1)][$objattr['nestedcontent']]['w'];	// nested table width
			if ($table['borders_separate']) { 
				$innerw = $w - $cell['border_details']['L']['w'] - $cell['border_details']['R']['w'] - $cell['padding']['L'] - $cell['padding']['R'] - $table['border_spacing_H'];
			}
			else {
				$innerw = $w - $cell['border_details']['L']['w']/2 - $cell['border_details']['R']['w']/2 - $cell['padding']['L'] - $cell['padding']['R'];
			}
			if ($cell['a']=='C' || $this->table[($level+1)][$objattr['nestedcontent']]['a']=='C') { 
				$ncx += ($innerw-$ntw)/2; 
			}
			elseif ($cell['a']=='R' || $this->table[($level+1)][$objattr['nestedcontent']]['a']=='R') {
				$ncx += $innerw- $ntw; 
			} 
			$this->x = $ncx ;

			$this->_tablewrite($this->table[($level+1)][$objattr['nestedcontent']]);
			$this->cellBorderBuffer = $save_buffer;
			$this->x = $bak_x ;
			$this->divwidth  = $save_dw;
			$this->newFlowingBlock( $this->divwidth,$this->divheight,$align,$is_table,$is_list,$blockstate,false); 
		}
	  }
	  else {
		if ($is_table) {
			// mPDF 2.0
			$maxWidth = $this->divwidth; 
		}
		else {
			$maxWidth = $this->divwidth - ($this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['border_right']['w']); 
		}

		// mPDF 2.4
		// If float (already) exists at this level
		if ($this->y <= $this->floatmargins['R']['y1'] && $this->y >= $this->floatmargins['R']['y0']) { $maxWidth -= $this->floatmargins['R']['w']; }
		if ($this->y <= $this->floatmargins['L']['y1'] && $this->y >= $this->floatmargins['L']['y0']) { $maxWidth -= $this->floatmargins['L']['w']; }


		list($skipln) = $this->inlineObject($objattr['type'], '', $this->y, $objattr,$this->lMargin, ($this->flowingBlockAttr['contentWidth']/$this->k), $maxWidth, $this->flowingBlockAttr['height'], false, $is_table);
		//  1 -> New line needed because of width
		// -1 -> Will fit width on line but NEW PAGE REQUIRED because of height
		// -2 -> Will not fit on line therefore needs new line but thus NEW PAGE REQUIRED

		$iby = $this->y;
		$oldpage = $this->page;
		// mPDF 2.4 Float Images
		if (($skipln == 1 || $skipln == -2) && !$objattr['float']) {
            	$this->finishFlowingBlock();
	           	$this->newFlowingBlock( $this->divwidth,$this->divheight,$align,$is_table,$is_list,$blockstate,false); //false=newblock
		}
		$thispage = $this->page;

		if ($skipln <0 && $this->AcceptPageBreak() && $thispage==$oldpage) { 	// the previous lines can already have triggered page break

			$this->AddPage($this->CurOrientation); 

	  		// Added to correct Images already set on line before page advanced
			// i.e. if second inline image on line is higher than first and forces new page
			if (count($this->objectbuffer)) {
				$yadj = $iby - $this->y;
				foreach($this->objectbuffer AS $ib=>$val) {
					if ($this->objectbuffer[$ib]['OUTER-Y'] ) $this->objectbuffer[$ib]['OUTER-Y'] -= $yadj;
					if ($this->objectbuffer[$ib]['BORDER-Y']) $this->objectbuffer[$ib]['BORDER-Y'] -= $yadj;
					if ($this->objectbuffer[$ib]['INNER-Y']) $this->objectbuffer[$ib]['INNER-Y'] -= $yadj;
				}
			}
		}


	  	// Added to correct for OddEven Margins
   	  	if  ($this->page != $oldpage) {
			$bak_x += $this->MarginCorrection;
			$oldpage = $this->page;
				$y = $this->tMargin - $paint_ht_corr ;
				$this->oldy = $this->tMargin - $paint_ht_corr ;
				$old_height = 0;
		}
		$this->x = $bak_x;
		// COLS
		// OR COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($this->directionality == 'rtl') {
				$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			else {
				$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			$this->x = $bak_x;
			$oldcolumn = $this->CurrCol;
			$y = $this->y0 - $paint_ht_corr ;
			$this->oldy = $this->y0 - $paint_ht_corr ;
			$old_height = 0;
		}

		// mPDF 2.4 Float Images
		if ($objattr['type'] == 'image' && $objattr['float']) { 
		  $fy = $this->y;

		  // DIV TOP MARGIN/BORDER/PADDING
		  if ($this->flowingBlockAttr['newblock'] && ($this->flowingBlockAttr['blockstate']==1 || $this->flowingBlockAttr['blockstate']==3) && $this->flowingBlockAttr['lineCount']== 0) { 
			$fy += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
		  }

		  if ($objattr['float']=='R') {
			$fx = $this->w - $this->rMargin - $objattr['width'] - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right']);


		  }
		  else if ($objattr['float']=='L') {
			$fx = $this->lMargin + ($this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left']);
		  }
		  $w = $objattr['width'];
		  $h = abs($objattr['height']); 

		  $widthLeft = $maxWidth - ($this->flowingBlockAttr['contentWidth']/$this->k);
		  $maxHeight = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10) ;
		  // For Images
		  $extraWidth = ($objattr['border_left']['w'] + $objattr['border_right']['w'] + $objattr['margin_left']+ $objattr['margin_right']);
		  $extraHeight = ($objattr['border_top']['w'] + $objattr['border_bottom']['w'] + $objattr['margin_top']+ $objattr['margin_bottom']);

		  if ($objattr['itype'] == 'wmf') {
		  	$file = $objattr['file'];
 		  	$info=$this->formobjects[$file];
		  }
		  else {
		  	$file = $objattr['file'];
		  	$info=$this->images[$file];
		  }
		  // Automatically resize to width remaining - ********** If > maxWidth *******
//		  if ($w > $widthLeft) {
//		  	$w = $widthLeft ;
//		  	$h=abs($w*$info['h']/$info['w']);
//		  }
		  $img_w = $w - $extraWidth ;
		  $img_h = $h - $extraHeight ;
		  if ($objattr['border_left']['w']) {
		  	$objattr['BORDER-WIDTH'] = $img_w + (($objattr['border_left']['w'] + $objattr['border_right']['w'])/2) ;
		  	$objattr['BORDER-HEIGHT'] = $img_h + (($objattr['border_top']['w'] + $objattr['border_bottom']['w'])/2) ;
		  	$objattr['BORDER-X'] = $fx + $objattr['margin_left'] + (($objattr['border_left']['w'])/2) ;
		  	$objattr['BORDER-Y'] = $fy + $objattr['margin_top'] + (($objattr['border_top']['w'])/2) ;
		  }
		  $objattr['INNER-WIDTH'] = $img_w;
		  $objattr['INNER-HEIGHT'] = $img_h;
		  $objattr['INNER-X'] = $fx + $objattr['margin_left'] + ($objattr['border_left']['w']);
		  $objattr['INNER-Y'] = $fy + $objattr['margin_top'] + ($objattr['border_top']['w']) ;
		  $objattr['ID'] = $info['i'];
		  $objattr['OUTER-WIDTH'] = $w;
		  $objattr['OUTER-HEIGHT'] = $h;
		  $objattr['OUTER-X'] = $fx;
		  $objattr['OUTER-Y'] = $fy;
		  if ($objattr['float']=='R') {
			// If R float already exists at this level
			if ($this->floatmargins['R']['y1'] > 0 && $fy < $this->floatmargins['R']['y1']) {
				$this->WriteFlowingBlock($vetor[0]); 
			}
			// If L float already exists at this level
			else if ($this->floatmargins['L']['y1'] > 0 && $fy < $this->floatmargins['L']['y1']) {
				// Final check distance between floats is not now too narrow to fit text
				$mw = $this->getStringWidth('WW');
				if (($this->blk[$this->blklvl]['inner_width'] - $w - $this->floatmargins['L']['w']) < $mw) {
					$this->WriteFlowingBlock($vetor[0]); 
				}
				else {
		  			$this->floatmargins['R']['x'] = $fx;
		  			$this->floatmargins['R']['w'] = $w;
		  			$this->floatmargins['R']['y0'] = $fy;
		  			$this->floatmargins['R']['y1'] = $fy + $h;
		 			if ($skipln == 1) {
		 			 	$this->floatmargins['R']['skipline'] = true;
		 			 	$this->floatmargins['R']['id'] = count($this->floatbuffer)+0;
						$objattr['skipline'] = true;
					}
					$this->floatbuffer[] = $objattr;
				}
			}
			else {
		  		$this->floatmargins['R']['x'] = $fx;
		  		$this->floatmargins['R']['w'] = $w;
		  		$this->floatmargins['R']['y0'] = $fy;
		  		$this->floatmargins['R']['y1'] = $fy + $h;
		 		if ($skipln == 1) {
		 		 	$this->floatmargins['R']['skipline'] = true;
		 		 	$this->floatmargins['R']['id'] = count($this->floatbuffer)+0;
					$objattr['skipline'] = true;
				}
				$this->floatbuffer[] = $objattr;
			}
		  }
		  else if ($objattr['float']=='L') {
			// If L float already exists at this level
			if ($this->floatmargins['L']['y1'] > 0 && $fy < $this->floatmargins['L']['y1']) {
				$this->WriteFlowingBlock($vetor[0]); 
			}
			// If R float already exists at this level
			else if ($this->floatmargins['R']['y1'] > 0 && $fy < $this->floatmargins['R']['y1']) {
				// Final check distance between floats is not now too narrow to fit text
				$mw = $this->getStringWidth('WW');
				if (($this->blk[$this->blklvl]['inner_width'] - $w - $this->floatmargins['R']['w']) < $mw) {
					$this->WriteFlowingBlock($vetor[0]); 
				}
				else {
		  			$this->floatmargins['L']['x'] = $fx + $w;
		  			$this->floatmargins['L']['w'] = $w;
		  			$this->floatmargins['L']['y0'] = $fy;
		  			$this->floatmargins['L']['y1'] = $fy + $h;
		 			if ($skipln == 1) {
		 			 	$this->floatmargins['L']['skipline'] = true;
		 			 	$this->floatmargins['L']['id'] = count($this->floatbuffer)+0;
						$objattr['skipline'] = true;
					}
					$this->floatbuffer[] = $objattr;
				}
			}
			else {
		  		$this->floatmargins['L']['x'] = $fx + $w;
		  		$this->floatmargins['L']['w'] = $w;
		  		$this->floatmargins['L']['y0'] = $fy;
		  		$this->floatmargins['L']['y1'] = $fy + $h;
		 		if ($skipln == 1) {
		 		 	$this->floatmargins['L']['skipline'] = true;
		 		 	$this->floatmargins['L']['id'] = count($this->floatbuffer)+0;
					$objattr['skipline'] = true;
				}
				$this->floatbuffer[] = $objattr;
			}
		  }
		}
		else {
			$this->WriteFlowingBlock($vetor[0]); 
		}
	  }

      }	// END If special content

      else { //THE text
	  if ($this->tableLevel) { $paint_ht_corr = 0; }	// To move the y up when new column/page started if div border needed
	  else { $paint_ht_corr = $this->blk[$this->blklvl]['border_top']['w']; }

        if ($vetor[0] == "\n") { //We are reading a <BR> now turned into newline ("\n")
		if ($this->flowingBlockAttr['content']) {
			$this->finishFlowingBlock();
		}
		// mPDF 3.0
	//	else if ($is_table && $i > 0 && ($i != ($array_size-1))) {
	//		$this->y+=$this->lineheight;
		else if ($is_table) {
			$this->y+= ($this->table_lineheight * $this->FontSize);
		}
		else if (!$is_table) {
			$this->DivLn($this->lineheight); 
			// mPDF 2.1
			if ($this->ColActive) { $this->breakpoints[$this->CurrCol][] = $this->y; }
		}
	  	// Added to correct for OddEven Margins
   	  	if  ($this->page != $oldpage) {
			$bak_x=$bak_x +$this->MarginCorrection;
			$oldpage = $this->page;
				$y = $this->tMargin - $paint_ht_corr ;
				$this->oldy = $this->tMargin - $paint_ht_corr ;
				$old_height = 0;
		}
		$this->x = $bak_x;
		// COLS
		// OR COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($this->directionality == 'rtl') {
				$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			else {
				$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			$this->x = $bak_x;
			$oldcolumn = $this->CurrCol;
			$y = $this->y0 - $paint_ht_corr ;
			$this->oldy = $this->y0 - $paint_ht_corr ;
			$old_height = 0;
		}
		$this->newFlowingBlock( $this->divwidth,$this->divheight,$align,$is_table,$is_list,$blockstate,false);	// false = newblock
         }
          else {
		$this->WriteFlowingBlock( $vetor[0]);

		  // Added to correct for OddEven Margins
   		  if  ($this->page != $oldpage) {
			$bak_x=$bak_x +$this->MarginCorrection;
			$this->x = $bak_x;
			$oldpage = $this->page;
				$y = $this->tMargin - $paint_ht_corr ;
				$this->oldy = $this->tMargin - $paint_ht_corr ;
				$old_height = 0;
		  }
		// COLS
		// OR COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($this->directionality == 'rtl') {
				$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			else {
				$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			$this->x = $bak_x;
			$oldcolumn = $this->CurrCol;
			$y = $this->y0 - $paint_ht_corr ;
			$this->oldy = $this->y0 - $paint_ht_corr ;
			$old_height = 0;
		}
	    }


      }

      //Check if it is the last element. If so then finish printing the block
      if ($i == ($array_size-1)) {
		$this->finishFlowingBlock(true);	// true = END of flowing block
		  // Added to correct for OddEven Margins
   		  if  ($this->page != $oldpage) {
			$bak_x += $this->MarginCorrection;
			$this->x = $bak_x;
			$oldpage = $this->page;
				$y = $this->tMargin - $paint_ht_corr ;
				$this->oldy = $this->tMargin - $paint_ht_corr ;
				$old_height = 0;
		  }

		// COLS
		// OR COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($this->directionality == 'rtl') {
				$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			else {
				$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			$this->x = $bak_x;
			$oldcolumn = $this->CurrCol;
			$y = $this->y0 - $paint_ht_corr ;
			$this->oldy = $this->y0 - $paint_ht_corr ;
			$old_height = 0;
		}

	}


	// RESETTING VALUES
	$this->SetTextColor(0);
	$this->SetDrawColor(0);
	$this->SetFillColor(255);
	$this->colorarray = array();
	$this->spanbgcolorarray = array();
	$this->spanbgcolor = false;
	$this->issetcolor = false;
	$this->HREF = '';
	$this->outlineparam = array();
	$this->SetTextOutline(false);
      $this->outline_on = false;
	$this->SUP = false;
	$this->SUB = false;

	$this->strike = false;

	$this->currentfontfamily = '';  
	$this->currentfontsize = '';  
	$this->currentfontstyle = '';  
	if ($this->tableLevel) {
		$this->SetLineHeight('',$this->table_lineheight);
	}
	else {
		$this->SetLineHeight('',$this->blk[$this->blklvl]['line_height']);	// sets default line height
	}
	$this->SetStyle('B',false);
	$this->SetStyle('I',false);
	$this->SetStyle('U',false);
	$this->toupper = false;
	$this->tolower = false;
	$this->SetDash(); //restore to no dash
	$this->dash_on = false;
	$this->dotted_on = false;

    }//end of for(i=0;i<arraysize;i++)



    // PAINT DIV BORDER	// DISABLED IN COLUMNS AS DOESN'T WORK WHEN BROKEN ACROSS COLS??
    // mPDF 3.0 Border OR background
    if (($this->blk[$this->blklvl]['border'] || $this->blk[$this->blklvl]['bgcolor']) && $blockstate  && ($this->y != $this->oldy)) {
	$bottom_y = $this->y;	// Does not include Bottom Margin

	if ($this->blk[$this->blklvl]['startpage'] != $this->page) {
		$this->PaintDivBB('pagetop',$blockstate);
	}
	// mPDF 3.0
	else if ($blockstate != 1) {
		$this->PaintDivBB('',$blockstate);
	}
	$this->y = $bottom_y; 
	$this->x = $bak_x;
    }

    // Reset Font
    $this->SetFontSize($this->default_font_size,false); 


}


// mPDF 3.0 Borders & Background
function PaintDivBB($divider='',$blockstate=0,$blvl=0) {
	// Borders are disabled in columns - messes up the repositioning in printcolumnbuffer
	if ($this->ColActive) { return ; }
	$save_y = $this->y;
	if (!$blvl) { $blvl = $this->blklvl; }

	// Added mPDF 3.0 Float DIV
	if ($this->blk[$blvl]['bb_painted'][$this->page]) { return; }

	$x0 = $this->blk[$blvl]['x0'];	// left
	$y1 = $this->blk[$blvl]['y1'];	// bottom

	// Added mPDF 3.0 Float DIV - ensures backgrounds/borders are drawn to bottom of page
	if (!$y1) { 
		if ($divider=='pagebottom') { $y1 = $this->h-$this->bMargin; }
		else { $y1 = $this->y; }
	}

	// mPDF 3.0
	//if ($this->blk[$this->blklvl]['startpage'] != $this->page) { $continuingpage = true; } else { $continuingpage = false; } 
	if ($this->blk[$blvl]['startpage'] != $this->page) { $continuingpage = true; } else { $continuingpage = false; } 

	$y0 = $this->blk[$blvl]['y0'];
	$h = $y1 - $y0;
	$w = $this->blk[$blvl]['width'];
	$x1 = $x0 + $w;

	// Set border-widths as used here
	$border_top = $this->blk[$blvl]['border_top']['w'];
	$border_bottom = $this->blk[$blvl]['border_bottom']['w'];
	$border_left = $this->blk[$blvl]['border_left']['w'];
	$border_right = $this->blk[$blvl]['border_right']['w'];
	if (!$this->blk[$blvl]['border_top'] || $divider == 'pagetop' || $continuingpage) {
		$border_top = 0;
	}
	if (!$this->blk[$blvl]['border_bottom'] || $blockstate == 1 || $divider == 'pagebottom') { 
		$border_bottom = 0;
	}

	// Border-radius
	$brTL_H = $this->blk[$blvl]['border_radius_TL_H'];
	$brTL_V = $this->blk[$blvl]['border_radius_TL_V'];
	$brTR_H = $this->blk[$blvl]['border_radius_TR_H'];
	$brTR_V = $this->blk[$blvl]['border_radius_TR_V'];
	$brBR_H = $this->blk[$blvl]['border_radius_BR_H'];
	$brBR_V = $this->blk[$blvl]['border_radius_BR_V'];
	$brBL_H = $this->blk[$blvl]['border_radius_BL_H'];
	$brBL_V = $this->blk[$blvl]['border_radius_BL_V'];

	if (!$this->blk[$blvl]['border_top'] || $divider == 'pagetop' || $continuingpage || $this->keep_block_together) {
		$brTL_H = 0;
		$brTL_V = 0;
		$brTR_H = 0;
		$brTR_V = 0;
	}
	if (!$this->blk[$blvl]['border_bottom'] || $blockstate == 1 || $divider == 'pagebottom' || $this->keep_block_together) { 
		$brBL_H = 0;
		$brBL_V = 0;
		$brBR_H = 0;
		$brBR_V = 0;
	}

	// Disallow border-radius if it is smaller than the border width.
	if ($brTL_H < min($border_left, $border_top)) { $brTL_H = $brTL_V = 0; }
	if ($brTL_V < min($border_left, $border_top)) { $brTL_V = $brTL_H = 0; }
	if ($brTR_H < min($border_right, $border_top)) { $brTR_H = $brTR_V = 0; }
	if ($brTR_V < min($border_right, $border_top)) { $brTR_V = $brTR_H = 0; }
	if ($brBL_H < min($border_left, $border_bottom)) { $brBL_H = $brBL_V = 0; }
	if ($brBL_V < min($border_left, $border_bottom)) { $brBL_V = $brBL_H = 0; }
	if ($brBR_H < min($border_right, $border_bottom)) { $brBR_H = $brBR_V = 0; }
	if ($brBR_V < min($border_right, $border_bottom)) { $brBR_V = $brBR_H = 0; }

	// CHECK FOR radii that sum to > width or height of div ********
	$f = min($h/($brTL_V + $brBL_V + 0.001), $h/($brTR_V + $brBR_V + 0.001), $w/($brTL_H + $brTR_H + 0.001),  $w/($brBL_H + $brBR_H + 0.001));
	if ($f < 1) {
		$brTL_H *= $f;
		$brTL_V *= $f;
		$brTR_H *= $f;
		$brTR_V *= $f;
		$brBL_H *= $f;
		$brBL_V *= $f;
		$brBR_H *= $f;
		$brBR_V *= $f;
	}

		// BORDERS
		$y0 = $this->blk[$blvl]['y0'];
		$h = $y1 - $y0;
		$w = $this->blk[$blvl]['width'];

		//if ($this->blk[$blvl]['border_top']) {
		// Reinstate line above for dotted line divider when block border crosses a page
		if ($this->blk[$blvl]['border_top'] && $divider != 'pagetop' && !$continuingpage) {
			$tbd = $this->blk[$blvl]['border_top'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if (($tbd['style'] == 'dashed' && $divider != 'pagetop' && !$continuingpage)) {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted' || $divider == 'pagetop' || $continuingpage) {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					// mPDF 2.0 Changed from $this->LineWidth*3
					$this->SetDash(0.001,($this->LineWidth*3));
				}

 				else if (($brTL_V && $brTL_H) || ($brTR_V && $brTR_H)) { $this->_out("\n".'0 J'."\n".'0 j'."\n"); }

				if ($brTR_H && $brTR_V) {
					$this->_out($this->_EllipseArc($x0 + $w - $brTR_H, $y0 + $brTR_V, $brTR_H - $border_top/2 , $brTR_V - $border_top/2 , 1, 2, true));
				}
				else {
					$this->_out(sprintf('%.3f %.3f m ',($x0 + $w - ($border_top/2))*$this->k, ($this->h-($y0 + ($border_top/2)))*$this->k));
				}
				if ($brTL_H && $brTL_V ) {
					$this->_out(sprintf('%.3f %.3f l ',($x0 + ($border_top/2) + $brTL_H )*$this->k, ($this->h-($y0 + ($border_top/2)))*$this->k));
					$this->_out($this->_EllipseArc($x0 + $brTL_H, $y0 + $brTL_V, $brTL_H - $border_top/2 , $brTL_V - $border_top/2 , 2, 1));
				}
				else {
					$this->_out(sprintf('%.3f %.3f l ',($x0 + ($border_top/2))*$this->k, ($this->h-($y0 + ($border_top/2)))*$this->k));
				}
				$this->_out('S');


				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		//if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1) { 
		// Reinstate line above for dotted line divider when block border crosses a page
		if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1 && $divider != 'pagebottom') { 
			$tbd = $this->blk[$blvl]['border_bottom'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if (($tbd['style'] == 'dashed' && $divider != 'pagebottom')) {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted' || $divider == 'pagebottom') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}

 				else if (($brBL_V && $brBL_H) || ($brBR_V && $brBR_H)) { $this->_out("\n".'0 J'."\n".'0 j'."\n"); }

				if ($brBL_H && $brBL_V) {
					$this->_out($this->_EllipseArc($x0 + $brBL_H, $y0 + $h - $brBL_V, $brBL_H - $border_bottom/2 , $brBL_V - $border_bottom/2 , 3, 2, true));
				}
				else {
					$this->_out(sprintf('%.3f %.3f m ',($x0 + ($border_bottom/2))*$this->k, ($this->h-($y0 + $h - ($border_bottom/2)))*$this->k));
				}
				if ($brBR_H && $brBR_V ) {
					$this->_out(sprintf('%.3f %.3f l ',($x0 + $w - ($border_bottom/2) - $brBR_H )*$this->k, ($this->h-($y0 + $h - ($border_bottom/2)))*$this->k));
					$this->_out($this->_EllipseArc($x0 + $w - $brBR_H, $y0 + $h - $brBR_V, $brBR_H - $border_bottom/2 , $brBR_V - $border_bottom/2 , 4, 1));
				}
				else {
					$this->_out(sprintf('%.3f %.3f l ',($x0 + $w - ($border_bottom/2) )*$this->k, ($this->h-($y0 + $h - ($border_bottom/2)))*$this->k));
				}
				$this->_out('S');

				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($this->blk[$blvl]['border_left']) { 
			$tbd = $this->blk[$blvl]['border_left'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
 				else if (($brTL_V && $brTL_H) || ($brBL_V && $brBL_H)) { $this->_out("\n".'0 J'."\n".'0 j'."\n"); }

				if ($brTL_V && $brTL_H) {
					$this->_out($this->_EllipseArc($x0 + $brTL_H, $y0 + $brTL_V, $brTL_H - $border_left/2 , $brTL_V - $border_left/2, 2, 2, true));
				}
				else {
					$this->_out(sprintf('%.3f %.3f m ',($x0 + ($border_left/2))*$this->k, ($this->h-($y0 + ($border_left/2)))*$this->k));
				}
				if ($brBL_V && $brBL_H ) {
					$this->_out(sprintf('%.3f %.3f l ',($x0 + ($border_left/2))*$this->k, ($this->h-($y0 + $h - ($border_left/2)- $brBL_V) )*$this->k));
					$this->_out($this->_EllipseArc($x0 + $brBL_H, $y0 + $h - $brBL_V, $brBL_H - $border_left/2 , $brBL_V - $border_left/2, 3, 1));
				}
				else {
					$this->_out(sprintf('%.3f %.3f l ',($x0 + ($border_left/2))*$this->k, ($this->h-($y0 + $h - ($border_left/2)) )*$this->k));
				}
				$this->_out('S');

				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($this->blk[$blvl]['border_right']) { 
			$tbd = $this->blk[$blvl]['border_right'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}

 				else if (($brTR_V && $brTR_H) || ($brBR_V && $brBR_H)) { $this->_out("\n".'0 J'."\n".'0 j'."\n"); }

				if ($brBR_V && $brBR_H) {
					$this->_out($this->_EllipseArc($x0 + $w - $brBR_H, $y0 + $h - $brBR_V, $brBR_H - $border_right/2 , $brBR_V - $border_right/2, 4, 2, true));
				}
				else {
					$this->_out(sprintf('%.3f %.3f m ',($x0 + $w - ($border_right/2))*$this->k, ($this->h-($y0 + $h - ($border_right/2)))*$this->k));
				}
				if ($brTR_V && $brTR_H ) {
					$this->_out(sprintf('%.3f %.3f l ',($x0 + $w - ($border_right/2))*$this->k, ($this->h-($y0 + ($border_right/2) + $brTR_V) )*$this->k));
					$this->_out($this->_EllipseArc($x0 + $w - $brTR_H, $y0 + $brTR_V, $brTR_H - $border_right/2 , $brTR_V - $border_right/2, 1, 1));
				}
				else {
					$this->_out(sprintf('%.3f %.3f l ',($x0 + $w - ($border_right/2))*$this->k, ($this->h-($y0 + ($border_right/2)) )*$this->k));
				}
				$this->_out('S');

				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		$this->SetDash(); 
	$this->y = $save_y; 


	// BACKGROUNDS are disabled in columns/kbt/headers - messes up the repositioning in printcolumnbuffer
	if ($this->ColActive || $this->keep_block_together) { return ; }

	$bgx0 = $x0;
	$bgx1 = $x1;
	$bgy0 = $y0;
	$bgy1 = $y1;

	// Defined br values represent the radius of the outer curve - need to take border-width/2 from each radius for drawing the borders
	if ($this->blk[$blvl]['background_clip'] == 'padding-box') {
		$brbgTL_H = max(0, $brTL_H - $this->blk[$blvl]['border_left']['w']);
		$brbgTL_V = max(0, $brTL_V - $this->blk[$blvl]['border_top']['w']);
		$brbgTR_H = max(0, $brTR_H - $this->blk[$blvl]['border_right']['w']);
		$brbgTR_V = max(0, $brTR_V - $this->blk[$blvl]['border_top']['w']);
		$brbgBL_H = max(0, $brBL_H - $this->blk[$blvl]['border_left']['w']);
		$brbgBL_V = max(0, $brBL_V - $this->blk[$blvl]['border_bottom']['w']);
		$brbgBR_H = max(0, $brBR_H - $this->blk[$blvl]['border_right']['w']);
		$brbgBR_V = max(0, $brBR_V - $this->blk[$blvl]['border_bottom']['w']);
		$bgx0 += $this->blk[$blvl]['border_left']['w'];
		$bgx1 -= $this->blk[$blvl]['border_right']['w'];
		if ($this->blk[$blvl]['border_top'] && $divider != 'pagetop' && !$continuingpage) {
			$bgy0 += $this->blk[$blvl]['border_top']['w'];
		}
		if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1 && $divider != 'pagebottom') { 
			$bgy1 -= $this->blk[$blvl]['border_bottom']['w'];
		}
	}
	else {
		$brbgTL_H = $brTL_H;
		$brbgTL_V = $brTL_V;
		$brbgTR_H = $brTR_H;
		$brbgTR_V = $brTR_V;
		$brbgBL_H = $brBL_H;
		$brbgBL_V = $brBL_V;
		$brbgBR_H = $brBR_H;
		$brbgBR_V = $brBR_V;
	}

	// Set clipping path
	$s = ' q 0 w ';	// Line width=0
	$s .= sprintf('%.3f %.3f m ', ($bgx0+$brbgTL_H )*$this->k, ($this->h-$bgy0)*$this->k);	// start point TL before the arc
	if ($brbgTL_H || $brbgTL_V) {
		$s .= $this->_EllipseArc($bgx0+$brbgTL_H, $bgy0+$brbgTL_V, $brbgTL_H , $brbgTL_V , 2);	// segment 2 TL
	}
	$s .= sprintf('%.3f %.3f l ', ($bgx0)*$this->k, ($this->h-($bgy1-$brbgBL_V ))*$this->k);	// line to BL
	if ($brbgBL_H || $brbgBL_V) {
		$s .= $this->_EllipseArc($bgx0+$brbgBL_H, $bgy1-$brbgBL_V, $brbgBL_H , $brbgBL_V , 3);	// segment 3 BL
	}
	$s .= sprintf('%.3f %.3f l ', ($bgx1-$brbgBR_H )*$this->k, ($this->h-($bgy1))*$this->k);	// line to BR
	if ($brbgBR_H || $brbgBR_V) {
		$s .= $this->_EllipseArc($bgx1-$brbgBR_H, $bgy1-$brbgBR_V, $brbgBR_H , $brbgBR_V , 4);	// segment 4 BR
	}
	$s .= sprintf('%.3f %.3f l ', ($bgx1)*$this->k, ($this->h-($bgy0+$brbgTR_V))*$this->k);	// line to TR
	if ($brbgTR_H || $brbgTR_V) {
		$s .= $this->_EllipseArc($bgx1-$brbgTR_H, $bgy0+$brbgTR_V, $brbgTR_H , $brbgTR_V , 1);	// segment 1 TR
	}
	$s .= sprintf('%.3f %.3f l ', ($bgx0+$brbgTL_H )*$this->k, ($this->h-$bgy0)*$this->k);	// line to TL
	$s .= ' W n ';	// Ends path no-op & Sets the clipping path

	if ($this->blk[$blvl]['bgcolor']) { 
		$this->pageBackgrounds[$blvl][] = array('x'=>$x0, 'y'=>$y0, 'w'=>$w, 'h'=>$h, 'col'=>$this->blk[$blvl]['bgcolorarray'], 'clippath'=>$s);
	}
	// mPDF 3.0 Gradients
	if ($this->blk[$blvl]['gradient']) { 
		$g = $this->parseBackgroundGradient($this->blk[$blvl]['gradient']);
		if ($g) {
			$gx = $x0;
			$gy = $y0;
			$this->pageBackgrounds[$blvl][] = array('gradient'=>true, 'x'=>$gx, 'y'=>$gy, 'w'=>$w, 'h'=>$h, 'gradtype'=>$g['type'], 'col'=>$g['col'], 'col2'=>$g['col2'], 'coords'=>$g['coords'], 'extend'=>$g['extend'], 'clippath'=>$s);
		}
	}

	if ($this->blk[$blvl]['background-image']) { 
		$image_id = $this->blk[$blvl]['background-image']['image_id'];
		$orig_w = $this->blk[$blvl]['background-image']['orig_w'];
		$orig_h = $this->blk[$blvl]['background-image']['orig_h'];
		$x_pos = $this->blk[$blvl]['background-image']['x_pos'];
		$y_pos = $this->blk[$blvl]['background-image']['y_pos'];
		$x_repeat = $this->blk[$blvl]['background-image']['x_repeat'];
		$y_repeat = $this->blk[$blvl]['background-image']['y_repeat'];
		$this->pageBackgrounds[$blvl][] = array('x'=>$x0, 'y'=>$y0, 'w'=>$w, 'h'=>$h, 'image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'clippath'=>$s);
	}

	// Added mPDF 3.0 Float DIV
	$this->blk[$blvl]['bb_painted'][$this->page] = true;

}

// mPDF 3.0
function _EllipseArc($x0, $y0, $rx, $ry, $seg = 1, $part=false, $start=false) {	// Anticlockwise segment 1-4 TR-TL-BL-BR (part=1 or 2)
	$s = '';
   	if ($rx<0) { $rx = 0; }
	if ($ry<0) { $ry = 0; }
	$rx *= $this->k;
	$ry *= $this->k;
	$astart = 0;
	if ($seg == 1) {	// Top Right
		$afinish = 90;
		$nSeg = 4; 
	}
	else if ($seg == 2) {	// Top Left
		$afinish = 180;
		$nSeg = 8; 
	}
	else if ($seg == 3) {	// Bottom Left
		$afinish = 270;
		$nSeg = 12; 
	}
	else {			// Bottom Right
		$afinish = 360;
		$nSeg = 16; 
	}
	$astart = deg2rad((float) $astart);
	$afinish = deg2rad((float) $afinish);
	$totalAngle = $afinish - $astart;
	$dt = $totalAngle / $nSeg;	// segment angle
	$dtm = $dt/3;
	$x0 *= $this->k;
	$y0 = ($this->h - $y0) * $this->k;
	$t1 = $astart;
	$a0 = $x0 + ($rx * cos($t1));
	$b0 = $y0 + ($ry * sin($t1));
	$c0 = -$rx * sin($t1);
	$d0 = $ry * cos($t1);
	$op = false;
	for ($i = 1; $i <= $nSeg; $i++) {
		// Draw this bit of the total curve
		$t1 = ($i * $dt) + $astart;
		$a1 = $x0 + ($rx * cos($t1));
		$b1 = $y0 + ($ry * sin($t1));
		$c1 = -$rx * sin($t1);
		$d1 = $ry * cos($t1);
		if ($i>($nSeg-4) && (!$part || ($part == 1 && $i<=$nSeg-2) || ($part == 2 && $i>$nSeg-2))) { 
			if ($start && !$op) {
           			$s .= sprintf('%.3f %.3f m ', $a0, $b0);
			}
			$s .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ', ($a0 + ($c0 * $dtm)), ($b0 + ($d0 * $dtm)), ($a1 - ($c1 * $dtm)) , ($b1 - ($d1 * $dtm)), $a1 , $b1 );
			$op = true;
		}
		$a0 = $a1;
		$b0 = $b1;
		$c0 = $c1;
		$d0 = $d1;
	}
	return $s;
}



function PaintDivLnBorder($state=0,$blvl=0,$h) {
	// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom

	$this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y + $h; 

	$save_y = $this->y;

	$w = $this->blk[$blvl]['width'];
	$x0 = $this->x;				// left
	$y0 = $this->y;				// top
	$x1 = $this->x + $w;			// bottom
	$y1 = $this->y + $h;			// bottom

		if ($this->blk[$blvl]['border_top'] && ($state==1 || $state==3)) {
			$tbd = $this->blk[$blvl]['border_top'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				$this->y = $y0 + ($tbd['w']/2);
				if (($tbd['style'] == 'dashed' && !$continuingpage)) {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					// mPDF 2.0 Changed from $this->LineWidth*3
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + ($tbd['w']/2) , $this->y , $x0 + $w - ($tbd['w']/2), $this->y);
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}

		if ($this->blk[$blvl]['border_left']) { 
			$tbd = $this->blk[$blvl]['border_left'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				$this->y = $y0 + ($tbd['w']/2);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + ($tbd['w']/2), $this->y, $x0 + ($tbd['w']/2), $y0 + $h -($tbd['w']/2));
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($this->blk[$blvl]['border_right']) { 
			$tbd = $this->blk[$blvl]['border_right'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
			 	$this->y = $y0 + ($tbd['w']/2);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + $w - ($tbd['w']/2), $this->y, $x0 + $w - ($tbd['w']/2), $y0 + $h - ($tbd['w']/2));
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($this->blk[$blvl]['border_bottom'] && $state > 1) { 
			$tbd = $this->blk[$blvl]['border_bottom'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
				$this->y = $y0 + $h - ($tbd['w']/2);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if (($tbd['style'] == 'dashed')) {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + ($tbd['w']/2) , $this->y, $x0 + $w - ($tbd['w']/2), $this->y);
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		$this->SetDash(); 


	$this->y = $save_y; 
}


function PaintImgBorder($objattr,$is_table) {
	// Borders are disabled in columns - messes up the repositioning in printcolumnbuffer
	if ($this->ColActive) { return ; }
	// mPDF 2.0
	if ($is_table) { $k = $this->shrin_k; } else { $k = 1; }
		$h = $objattr['BORDER-HEIGHT'];
		$w = $objattr['BORDER-WIDTH'];
		$x0 = $objattr['BORDER-X'];
		$y0 = $objattr['BORDER-Y'];

		// BORDERS
		if ($objattr['border_top']) { 
			$tbd = $objattr['border_top'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']/$k);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0, $y0, $x0 + $w, $y0);
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($objattr['border_left']) { 
			$tbd = $objattr['border_left'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']/$k);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0, $y0, $x0, $y0 + $h);
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($objattr['border_right']) { 
			$tbd = $objattr['border_right'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']/$k);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + $w, $y0, $x0 + $w, $y0 + $h);
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($objattr['border_bottom']) { 
			$tbd = $objattr['border_bottom'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']/$k);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0, $y0 + $h, $x0 + $w, $y0 + $h);
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		$this->SetDash(); 

}





function Reset()
{
	$this->SetTextColor(0);
	$this->colorarray = array();
	$this->issetcolor = false;

	$this->SetDrawColor(0);

	$this->SetFillColor(255);
	$this->spanbgcolorarray = array();
	$this->spanbgcolor = false;

	$this->SetStyle('B',false);
	$this->SetStyle('I',false);
	$this->SetStyle('U',false);

	$this->HREF = '';
	$this->outlineparam = array();
      $this->outline_on = false;
	$this->SetTextOutline(false);

	$this->SUP = false;
	$this->SUB = false;
	$this->strike = false;

	$this->SetFont($this->default_font,'',0,false);
	$this->SetFontSize($this->default_font_size,false);

	$this->currentfontfamily = '';  
	$this->currentfontsize = '';  
	if ($this->tableLevel) {
		$this->SetLineHeight('',$this->table_lineheight);
	}
	else {
		$this->SetLineHeight('',$this->blk[$this->blklvl]['line_height']);	// sets default line height
	}
	$this->toupper = false;
	$this->tolower = false;
	$this->SetDash(); //restore to no dash
	$this->dash_on = false;
	$this->dotted_on = false;
	$this->divwidth = 0;
	$this->divheight = 0;
	$this->divalign = $this->defaultAlign;
	$this->divrevert = false;
	$this->oldy = -1;

	// mPDF 2.0 Allows BODY CSS for font weight/style and color
	$bodystyle = array();
	if ($this->CSS['BODY']['FONT-STYLE']) { $bodystyle['FONT-STYLE'] = $this->CSS['BODY']['FONT-STYLE']; }
	if ($this->CSS['BODY']['FONT-WEIGHT']) { $bodystyle['FONT-WEIGHT'] = $this->CSS['BODY']['FONT-WEIGHT']; }
	if ($this->CSS['BODY']['COLOR']) { $bodystyle['COLOR'] = $this->CSS['BODY']['COLOR']; }
	if ($bodystyle) { $this->setCSS($bodystyle,'BLOCK','BODY'); }

}

function ReadMetaTags($html)
{
	// changes anykey=anyvalue to anykey="anyvalue" (only do this when this happens inside tags)
	$regexp = '/ (\\w+?)=([^\\s>"]+)/si'; 
 	$html = preg_replace($regexp," \$1=\"\$2\"",$html);

	if (preg_match('/<title>(.*?)<\/title>/si',$html,$m)) {
		$this->SetTitle($m[1]); 
	}

  preg_match_all('/<meta [^>]*?(name|content)="([^>]*?)" [^>]*?(name|content)="([^>]*?)".*?>/si',$html,$aux);
  $firstattr = $aux[1];
  $secondattr = $aux[3];
  for( $i = 0 ; $i < count($aux[0]) ; $i++)
  {

     $name = ( strtoupper($firstattr[$i]) == "NAME" )? strtoupper($aux[2][$i]) : strtoupper($aux[4][$i]);
     $content = ( strtoupper($firstattr[$i]) == "CONTENT" )? $aux[2][$i] : $aux[4][$i];
     switch($name)
     {
       case "KEYWORDS": $this->SetKeywords($content); break;
       case "AUTHOR": $this->SetAuthor($content); break;
       case "DESCRIPTION": $this->SetSubject($content); break;
     }
  }
}


function ReadCharset($html)
{
	// Charset conversion
	if ($this->allow_charset_conversion) {
	   if (preg_match('/charset=([^\'\"\s]*)/si',$html,$m)) {
		if (strtoupper($m[1]) != 'UTF-8') {
			$this->charset_in = strtoupper($m[1]); 
		}
	   }
	}

}

//////////////////
/// CSS parser ///
//////////////////
//////////////////
/// CSS parser ///
//////////////////
//////////////////
/// CSS parser ///
//////////////////

// mPDF 2.2 To read default mpdf.css stylesheet
function ReadDefaultCSS($CSSstr) {
    $CSS = array();
    $CSSstr = preg_replace('|/\*.*?\*/|s',' ',$CSSstr);
    $CSSstr = preg_replace('/[\s\n\r\t\f]/s',' ',$CSSstr);

    // Added mPDF 2.0 to remove <!-- --> (but not in between)
    $CSSstr = preg_replace('/(<\!\-\-|\-\->)/s',' ',$CSSstr);
    if ($CSSstr ) {
	preg_match_all('/(.*?)\{(.*?)\}/',$CSSstr,$styles);
	for($i=0; $i < count($styles[1]) ; $i++)  {
	 	$stylestr= trim($styles[2][$i]);
		$stylearr = explode(';',$stylestr);
		foreach($stylearr AS $sta) {
			// mPDF 3.0
			// Changed to allow style="background: url('http://www.bpm1.com/bg.jpg')"
			list($property,$value) = explode(':',$sta,2);
			$property = trim($property);
			$value = preg_replace('/\s*!important/i','',$value);
			$value = trim($value);
			if ($property && ($value || $value==='0')) {
	  			$classproperties[strtoupper($property)] = $value;
			}
		}
		$classproperties = $this->fixCSS($classproperties);
		$tagstr = strtoupper(trim($styles[1][$i]));
		$tagarr = explode(',',$tagstr);
		foreach($tagarr AS $tg) {
		  $tags = preg_split('/\s+/',trim($tg));
		  $level = count($tags);
		  if ($level == 1) {		// e.g. p or .class or #id or p.class or p#id
		     $t = trim($tags[0]);
		     if ($t) {
			$tag = '';
			if (preg_match('/^('.$this->allowedCSStags.')$/',$t)) { $tag= $t; }
			if ($this->CSS[$tag] && $tag) { $CSS[$tag] = array_merge_recursive_unique($CSS[$tag], $classproperties); }
			else if ($tag) { $CSS[$tag] = $classproperties; }
		     }
		  }
		}
  		$properties = array();
  		$values = array();
  		$classproperties = array();
	}

    } // end of if
    return $CSS;
}


function ReadCSS($html)
{
/*
* Rewritten in mPDF 1.2 This version supports:  .class {...} / #id { .... }
* ADDED p {...}  h1[-h6] {...}  a {...}  table {...}   thead {...}  th {...}  td {...}  hr {...}
* body {...} sets default font and fontsize
* It supports some cascaded CSS e.g. div.topic table.type1 td
* Does not support non-block level e.g. a#hover { ... }
*/


	// mPDF 2.0 - Edit this if you want to exclude stylsheets for different media e.g. print
	$html = preg_replace('/<link[^>]*media=(\'|\")(aural|braille|handheld)(\'|\").*?>/is','',$html);
	$html = preg_replace('/<style[^>]*media=(\'|\")(aural|braille|handheld)(\'|\").*?<\/style>/is','',$html);
	if ($this->disablePrintCSS) {
		$html = preg_replace('/<link[^>]*media=(\'|\")print(\'|\").*?>/is','',$html);
		$html = preg_replace('/<style[^>]*media=(\'|\")print(\'|\").*?<\/style>/is','',$html);
	}

	$match = 0; // no match for instance
	$regexp = ''; // This helps debugging: showing what is the REAL string being processed
	$CSSext = array(); 

	//CSS inside external files
	// mPDF 3.0 Allow single or double quotes
	$regexp = '/<link rel=["\']stylesheet["\'][^>]*href=["\'](.+?)["\'].*?>/si'; 
	$x = preg_match_all($regexp,$html,$cxt);
	if ($x) { 
		$match += $x; 
		$CSSext = $cxt[1];
	}

	// Check for alternative order of attributes mPDF 2.0
	// mPDF 3.0 Allow single or double quotes
	$regexp = '/<link[^>]*href=["\'](.+?)["\'][^>]*rel=["\']stylesheet["\'].*?>/si'; 
	$x = preg_match_all($regexp,$html,$cxt);
	if ($x) { 
		$match += $x; 
		$CSSext = array_merge($CSSext,$cxt[1]);
	}

	// Edited mPDF 2.0
	// look for @import stylesheets
	$regexp = '/@import url\([\'\"]{0,1}(.*?\.css)[\'\"]{0,1}\)/si';
	$x = preg_match_all($regexp,$html,$cxt);
	if ($x) { 
		$match += $x; 
		$CSSext = array_merge($CSSext,$cxt[1]);
	}

  $ind = 0;
  $CSSstr = '';

  // Edited mPDF v1.4
  if (!is_array($this->cascadeCSS)) $this->cascadeCSS = array();

    while($match){
	//Fix path value
	$path = $CSSext[$ind];
	$path = str_replace("\\","/",$path); //If on Windows
	//Get link info and obtain its absolute path
	$regexp = '|^./|';
	$path = preg_replace($regexp,'',$path);
	if (strpos($path,"../") !== false ) { //It is a Relative Link
       $backtrackamount = substr_count($path,"../");
       $maxbacktrack = substr_count($this->basepath,"/") - 1;
       $filepath = str_replace("../",'',$path);
       $path = $this->basepath;
       //If it is an invalid relative link, then make it go to directory root
       if ($backtrackamount > $maxbacktrack) $backtrackamount = $maxbacktrack;
       //Backtrack some directories
       for( $i = 0 ; $i < $backtrackamount + 1 ; $i++ ) $path = substr( $path, 0 , strrpos($path,"/") );
       $path = $path . "/" . $filepath; //Make it an absolute path
	}
	else if( strpos($path,":/") === false) { //It is a Local Link
					if (substr($path,0,1) == "/") { 
						$tr = parse_url($this->basepath);
						$root = $tr['scheme'].'://'.$tr['host'];
						$path = $root . $path; 
					}
					else { $path = $this->basepath . $path; }
	}
	//Do nothing if it is an Absolute Link
	//END of fix path value
	// Edited mPDF 1.1 in case PHP_INI allow_url_fopen set to false
	// Edited mPDF 2.0
	$CSSextblock = $this->_get_file($path);

	if ($CSSextblock) {
		$CSSstr .= ' '.$CSSextblock;
	}	

	$match--;
	$ind++;
    } //end of match

    $match = 0; // reset value, if needed

	// CSS as <style> in HTML document
    $regexp = '/<style.*?>(.*?)<\/style>/si'; 
    $match = preg_match_all($regexp,$html,$CSSblock);
    if ($match) {
		$CSSstr .= ' '.implode(' ',$CSSblock[1]);
    }

    // Remove comments
    $CSSstr = preg_replace('|/\*.*?\*/|s',' ',$CSSstr);
    $CSSstr = preg_replace('/[\s\n\r\t\f]/s',' ',$CSSstr);

    // Added mPDF 2.0 to remove <!-- --> (but not in between)
    $CSSstr = preg_replace('/(<\!\-\-|\-\->)/s',' ',$CSSstr);
    if ($CSSstr ) {

	preg_match_all('/(.*?)\{(.*?)\}/',$CSSstr,$styles);
	for($i=0; $i < count($styles[1]) ; $i++)  {
		// SET array e.g. $classproperties['COLOR'] = '#ffffff';
	 	$stylestr= trim($styles[2][$i]);
		$stylearr = explode(';',$stylestr);
		foreach($stylearr AS $sta) {
			// mPDF 3.0
			// Changed to allow style="background: url('http://www.bpm1.com/bg.jpg')"
			list($property,$value) = explode(':',$sta,2);
			$property = trim($property);
			// mPDF 2.0
			$value = preg_replace('/\s*!important/i','',$value);
			$value = trim($value);
			if ($property && ($value || $value==='0')) {
	  			$classproperties[strtoupper($property)] = $value;
			}
		}
		$classproperties = $this->fixCSS($classproperties);
		$tagstr = strtoupper(trim($styles[1][$i]));
		$tagarr = explode(',',$tagstr);
		$pageselectors = false;	// used to turn on $this->useOddEven
		foreach($tagarr AS $tg) {
		  $tags = preg_split('/\s+/',trim($tg));
		  $level = count($tags);
		  // mPDF 2.0 Paged media
		  if (trim($tags[0])=='@PAGE') {
			$t = trim($tags[0]);
			$t2 = trim($tags[1]);
			$t3 = trim($tags[2]);
			$tag = '';
			if ($level==1) { $tag = $t; }
			else if ($level==2 && preg_match('/^[:](.*)$/',$t2,$m)) { 
				$tag = $t.'>>PSEUDO>>'.$m[1]; 
				if ($m[1]=='LEFT' || $m[1]=='RIGHT') { $pageselectors = true; }	// used to turn on $this->useOddEven 
			}
			else if ($level==2) { $tag = $t.'>>NAMED>>'.$t2; }
			else if ($level==3 && preg_match('/^[:](.*)$/',$t3,$m)) { 
				$tag = $t.'>>NAMED>>'.$t2.'>>PSEUDO>>'.$m[1]; 
				if ($m[1]=='LEFT' || $m[1]=='RIGHT') { $pageselectors = true; }	// used to turn on $this->useOddEven
			}
			if ($this->CSS[$tag] && $tag) { $this->CSS[$tag] = array_merge_recursive_unique($this->CSS[$tag], $classproperties); }
			else if ($tag) { $this->CSS[$tag] = $classproperties; }
		  }

		  else if ($level == 1) {		// e.g. p or .class or #id or p.class or p#id
		     $t = trim($tags[0]);
		     if ($t) {
			$tag = '';
			if (preg_match('/^[.](.*)$/',$t,$m)) { $tag = 'CLASS>>'.$m[1]; }
			else if (preg_match('/^[#](.*)$/',$t,$m)) { $tag = 'ID>>'.$m[1]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[.](.*)$/',$t,$m)) { $tag = $m[1].'>>CLASS>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[#](.*)$/',$t,$m)) { $tag = $m[1].'>>ID>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')$/',$t)) { $tag= $t; }

			if ($this->CSS[$tag] && $tag) { $this->CSS[$tag] = array_merge_recursive_unique($this->CSS[$tag], $classproperties); }
			else if ($tag) { $this->CSS[$tag] = $classproperties; }
		     }
		  }
		  else {
		   $tmp = array();
		   for($n=0;$n<$level;$n++) {
		     $t = trim($tags[$n]);
		     if ($t) {
			$tag = '';
			if (preg_match('/^[.](.*)$/',$t,$m)) { $tag = 'CLASS>>'.$m[1]; }
			else if (preg_match('/^[#](.*)$/',$t,$m)) { $tag = 'ID>>'.$m[1]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[.](.*)$/',$t,$m)) { $tag = $m[1].'>>CLASS>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[#](.*)$/',$t,$m)) { $tag = $m[1].'>>ID>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')$/',$t)) { $tag= $t; }

			if ($tag) $tmp[] = $tag;
		    }
		   }
		   $x = &$this->cascadeCSS; 
		   foreach($tmp AS $tp) {
			$x = &$x[$tp];
		   }
		   $x = array_merge_recursive_unique($x, $classproperties); 
		   $x['depth'] = $level;
		  }


		}
		if ($pageselectors) { $this->useOddEven = true; }
  		$properties = array();
  		$values = array();
  		$classproperties = array();
	}

    } // end of if
    //Remove CSS (tags and content), if any
    $regexp = '/<style.*?>(.*?)<\/style>/si'; // it can be <style> or <style type="txt/css"> 
    $html = preg_replace($regexp,'',$html);
//print_r($this->CSS); exit;
//print_r($this->cascadeCSS); exit;
    return $html;
}

function readInlineCSS($html)
{
  //Fix incomplete CSS code
  $size = strlen($html)-1;
  if (substr($html,$size,1) != ';') $html .= ';';
  //Make CSS[Name-of-the-class] = array(key => value)
  $regexp = '|\\s*?(\\S+?):(.+?);|i';
	preg_match_all( $regexp, $html, $styleinfo);
	$properties = $styleinfo[1];
	$values = $styleinfo[2];
	//Array-properties and Array-values must have the SAME SIZE!
	$classproperties = array();
	for($i = 0; $i < count($properties) ; $i++) $classproperties[strtoupper($properties[$i])] = trim($values[$i]);
  return $this->fixCSS($classproperties);
}



function setCSS($arrayaux,$type='',$tag='')	// type= INLINE | BLOCK // tag= BODY
{
	if (!is_array($arrayaux)) return; //Removes PHP Warning
	// Set font size first so that e.g. MARGIN 0.83em works on font size for this element
	if ($arrayaux['FONT-SIZE']) {
		$v = $arrayaux['FONT-SIZE'];
		if(is_numeric(substr($v,0,1))) {
			$mmsize = ConvertSize($v,$this->FontSize);
			$this->SetFontSize( $mmsize*(72/25.4),false ); //Get size in points (pt)
		}
		else{
  			$v = strtoupper($v);
  			switch($v) {
  				case 'XX-SMALL': $this->SetFontSize( (0.7)* $this->default_font_size,false);
  		             break;
                		case 'X-SMALL': $this->SetFontSize( (0.77) * $this->default_font_size,false);
		             break;
				case 'SMALL': $this->SetFontSize( (0.86)* $this->default_font_size,false);
  		             break;
  				case 'MEDIUM': $this->SetFontSize($this->default_font_size,false);
  		             break;
  				case 'LARGE': $this->SetFontSize( (1.2)*$this->default_font_size,false);
  		             break;
  				case 'X-LARGE': $this->SetFontSize( (1.5)*$this->default_font_size,false);
  		             break;
  				case 'XX-LARGE': $this->SetFontSize( 2*$this->default_font_size,false);
				 break;
			}
		}
		if ($tag == 'BODY') { $this->SetDefaultFontSize($this->FontSizePt); }
	}


	// mPDF 2.3
	if ($this->useLang && $arrayaux['LANG'] && $this->is_MB && $arrayaux['LANG'] != $this->default_lang && ((strlen($arrayaux['LANG']) == 5 && $arrayaux['LANG'] != 'UTF-8') || strlen($arrayaux['LANG']) == 2)) {
		list ($codepage,$mpdf_pdf_unifonts,$mpdf_directionality,$mpdf_jSpacing) = GetCodepage($arrayaux['LANG']);
		if ($codepage == 'SHIFT_JIS') { $this->SetFont('sjis',$this->currentfontstyle,0,false); }
		else if ($codepage == 'UHC') { $this->SetFont('uhc',$this->currentfontstyle,0,false); }
		else if ($codepage == 'BIG5') { $this->SetFont('big5',$this->currentfontstyle,0,false); }
		else if ($codepage == 'GBK') { $this->SetFont('gb',$this->currentfontstyle,0,false); }
		else if ($mpdf_pdf_unifonts) { $this->RestrictUnicodeFonts($mpdf_pdf_unifonts); }
		else { $this->RestrictUnicodeFonts($this->default_available_fonts ); }
		if ($mpdf_directionality == 'rtl') { $this->biDirectional = true; }
		if ($tag == 'BODY') {
			$this->jSpacing = $mpdf_jSpacing;
			$this->SetDirectionality($mpdf_directionality);
			$this->currentLang = $codepage;
			$this->default_lang = $codepage;
			$this->default_jSpacing = $mpdf_jSpacing;
			if ($mpdf_pdf_unifonts) { $this->default_available_fonts = $mpdf_pdf_unifonts; }
			$this->default_dir = $mpdf_directionality;
		}
		else if ($type == 'BLOCK') {
			$this->jSpacing = $mpdf_jSpacing;
		}
		else {	// INLINE
			if ($this->disableMultilingualJustify && $mpdf_jSpacing != $this->jSpacing && $this->blk[$this->blklvl]['align']=="J") {
          			$this->blk[$this->blklvl]['align']="";
			}
		}
	}
	else if ($this->useLang && $this->is_MB ) { 
		$this->RestrictUnicodeFonts($this->default_available_fonts ); 
		$this->jSpacing = $this->default_jSpacing;
	}


	// FOR INLINE and BLOCK OR 'BODY'
	if ($arrayaux['FONT-FAMILY']) {
		$v = $arrayaux['FONT-FAMILY'];
		//If it is a font list, get all font types
		$aux_fontlist = explode(",",$v);
		$fonttype = $aux_fontlist[0];
		$fonttype = strtolower(trim($fonttype));
		if(($fonttype == 'helvetica') || ($fonttype == 'arial')) { $fonttype = 'sans-serif'; }
		else if($fonttype == 'helvetica-embedded')  { $fonttype = 'helvetica'; }
		else if($fonttype == 'times')  { $fonttype = 'serif'; }
		else if($fonttype == 'courier')  { $fonttype = 'monospace'; }
		if ($tag == 'BODY') { 
			$this->SetDefaultFont($fonttype); 
		}
		// mPDF 2.0 - undoes underline
		//$this->SetFont($fonttype,$this->FontStyle,0,false);
		$this->SetFont($fonttype,$this->currentfontstyle,0,false);
	}
	// mPDF 2.3
	else { 
		$this->SetFont($this->currentfontfamily,$this->currentfontstyle,0,false); 
	}

   foreach($arrayaux as $k => $v) {
	if ($type != 'INLINE' && $tag != 'BODY') {
	  switch($k){
		// BORDERS
		case 'BORDER-TOP':
			$this->blk[$this->blklvl]['border_top'] = $this->border_details($v);
			if ($this->blk[$this->blklvl]['border_top']['s']) { $this->blk[$this->blklvl]['border'] = 1; }
			break;
		case 'BORDER-BOTTOM':
			$this->blk[$this->blklvl]['border_bottom'] = $this->border_details($v);
			if ($this->blk[$this->blklvl]['border_bottom']['s']) { $this->blk[$this->blklvl]['border'] = 1; }
			break;
		case 'BORDER-LEFT':
			$this->blk[$this->blklvl]['border_left'] = $this->border_details($v);
			if ($this->blk[$this->blklvl]['border_left']['s']) { $this->blk[$this->blklvl]['border'] = 1; }
			break;
		case 'BORDER-RIGHT':
			$this->blk[$this->blklvl]['border_right'] = $this->border_details($v);
			if ($this->blk[$this->blklvl]['border_right']['s']) { $this->blk[$this->blklvl]['border'] = 1; }
			break;

		// PADDING
		case 'PADDING-TOP':
			$this->blk[$this->blklvl]['padding_top'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'PADDING-BOTTOM':
			$this->blk[$this->blklvl]['padding_bottom'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'PADDING-LEFT':
			$this->blk[$this->blklvl]['padding_left'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'PADDING-RIGHT':
			$this->blk[$this->blklvl]['padding_right'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;

		// MARGINS
		case 'MARGIN-TOP':
			$this->blk[$this->blklvl]['margin_top'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'MARGIN-BOTTOM':
			$this->blk[$this->blklvl]['margin_bottom'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'MARGIN-LEFT':
			$this->blk[$this->blklvl]['margin_left'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'MARGIN-RIGHT':
			$this->blk[$this->blklvl]['margin_right'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;

		// mPDF 3.0 BORDER-RADIUS
		case 'BORDER-TOP-LEFT-RADIUS-H':
			$this->blk[$this->blklvl]['border_radius_TL_H'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'BORDER-TOP-LEFT-RADIUS-V':
			$this->blk[$this->blklvl]['border_radius_TL_V'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'BORDER-TOP-RIGHT-RADIUS-H':
			$this->blk[$this->blklvl]['border_radius_TR_H'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'BORDER-TOP-RIGHT-RADIUS-V':
			$this->blk[$this->blklvl]['border_radius_TR_V'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'BORDER-BOTTOM-LEFT-RADIUS-H':
			$this->blk[$this->blklvl]['border_radius_BL_H'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'BORDER-BOTTOM-LEFT-RADIUS-V':
			$this->blk[$this->blklvl]['border_radius_BL_V'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'BORDER-BOTTOM-RIGHT-RADIUS-H':
			$this->blk[$this->blklvl]['border_radius_BR_H'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;
		case 'BORDER-BOTTOM-RIGHT-RADIUS-V':
			$this->blk[$this->blklvl]['border_radius_BR_V'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width'],$this->FontSize,false);
			break;

		case 'BACKGROUND-CLIP':
			if (strtoupper($v) == 'PADDING-BOX') { $this->blk[$this->blklvl]['background_clip'] = 'padding-box'; }
			break;

		case 'PAGE-BREAK-AFTER':
			if (strtoupper($v) == 'AVOID') { $this->blk[$this->blklvl]['page_break_after_avoid'] = true; }
			break;

		case 'WIDTH':
			$this->blk[$this->blklvl]['css_set_width'] = ConvertSize($v,$this->blk[$this->blklvl-1]['inner_width']);
			break;

		case 'TEXT-INDENT':
			$this->blk[$this->blklvl]['text_indent'] = ConvertSize($v,$this->blk[$this->blklvl]['inner_width'],$this->FontSize);
			break;

	  }//end of switch($k)
	}


	if ($type != 'INLINE') {	// includes BODY tag
	  switch($k){

		case 'MARGIN-COLLAPSE':	// Custom tag to collapse margins at top and bottom of page
			if (strtoupper($v) == 'COLLAPSE') { $this->blk[$this->blklvl]['margin_collapse'] = true; }
			break;

		case 'LINE-HEIGHT':	
			// Edited mPDF 2.0
			if (preg_match('/^[0-9\.,]*$/',$v) && $v >= 1) { $this->blk[$this->blklvl]['line_height'] = $v + 0; }
			else if (preg_match('/%/',$v) && $v >= 100) { $this->blk[$this->blklvl]['line_height'] = ($v + 0)/100; }
			else if (strtoupper($v) == 'NORMAL') { $this->blk[$this->blklvl]['line_height'] = $this->default_lineheight_correction; }
			else { $this->blk[$this->blklvl]['line_height'] = $this->default_lineheight_correction; }
			break;

		case 'TEXT-ALIGN': //left right center justify
			switch (strtoupper($v)) {
				case 'LEFT': 
                        $this->blk[$this->blklvl]['align']="L";
                        break;
				case 'CENTER': 
                        $this->blk[$this->blklvl]['align']="C";
                        break;
				case 'RIGHT': 
                        $this->blk[$this->blklvl]['align']="R";
                        break;
				case 'JUSTIFY': 
                        $this->blk[$this->blklvl]['align']="J";
                        break;
			}
			break;

	  }//end of switch($k)
	}


	// FOR INLINE and BLOCK
	  switch($k){
		// mPDF 2.3 - For autoSpanFont
		case 'TEXT-ALIGN': //left right center justify
			if (strtoupper($v) == 'NOJUSTIFY' && $this->blk[$this->blklvl]['align']=="J") {
                        $this->blk[$this->blklvl]['align']="";
			}
			break;
		// bgcolor only - to stay consistent with original html2fpdf
		case 'BACKGROUND': 
		case 'BACKGROUND-COLOR': 
			$cor = ConvertColor($v);
			if ($cor) { 
			   // mPDF 3.0
			   if ($tag  == 'BODY') {
				$this->bodyBackgroundColor = $cor;
			   }
			   else if ($type == 'INLINE') {
				$this->spanbgcolorarray = $cor;
				$this->spanbgcolor = true;
			   }
			   else {
				$this->blk[$this->blklvl]['bgcolorarray'] = $cor;
				$this->blk[$this->blklvl]['bgcolor'] = true;
			   }
			}
			else if ($type != 'INLINE') {
		  		// mPDF 3.0
		  		if ($this->ColActive || $this->keep_block_together) { 
					$this->blk[$this->blklvl]['bgcolorarray'] = $this->blk[$this->blklvl-1]['bgcolorarray'] ;
					$this->blk[$this->blklvl]['bgcolor'] = $this->blk[$this->blklvl-1]['bgcolor'] ;
				}
			}
			break;

		// mPDF 3.0
		case 'BACKGROUND-GRADIENT': 
			if ($type  == 'BLOCK') {
				$this->blk[$this->blklvl]['gradient'] = $v;
			}
			break;


		case 'FONT-STYLE': // italic normal oblique
			switch (strtoupper($v)) {
				case 'ITALIC': 
				case 'OBLIQUE': 
            			$this->SetStyle('I',true);
					break;
				// mPDF 2.1 Force normal to turn property off
				case 'NORMAL': 
            			$this->SetStyle('I',false);
					break;
			}
			break;

		case 'FONT-WEIGHT': // normal bold //Does not support: bolder, lighter, 100..900(step value=100)
			switch (strtoupper($v))	{
				case 'BOLD': 
            			$this->SetStyle('B',true);
					break;
				// mPDF 2.1 Force normal to turn property off
				case 'NORMAL': 
            			$this->SetStyle('B',false);
					break;
			}
			break;

		case 'VERTICAL-ALIGN': //super and sub only dealt with here e.g. <SUB> and <SUP>
			switch (strtoupper($v)) {
				case 'SUPER': 
                        $this->SUP=true;
                        break;
				case 'SUB': 
                        $this->SUB=true;
                        break;
			}
			break;

		case 'TEXT-DECORATION': // none underline line-through (strikeout) //Does not support: overline, blink
			if (stristr($v,'LINE-THROUGH')) {
					$this->strike = true;
			}
			if (stristr($v,'UNDERLINE')) {
            			$this->SetStyle('U',true);
			}
			break;

		case 'TEXT-TRANSFORM': // none uppercase lowercase //Does not support: capitalize
			switch (strtoupper($v)) { //Not working 100%
				case 'UPPERCASE':
					$this->toupper=true;
					break;
				case 'LOWERCASE':
 					$this->tolower=true;
					break;
				case 'NONE': break;
			}
			break;

		case 'OUTLINE-WIDTH': 
			switch(strtoupper($v)) {
				case 'THIN': $v = '0.03em'; break;
				case 'MEDIUM': $v = '0.05em'; break;
				case 'THICK': $v = '0.07em'; break;
			}
			$this->outlineparam['WIDTH'] = ConvertSize($v,$this->blk[$this->blklvl]['inner_width'],$this->FontSize);
			break;

		case 'OUTLINE-COLOR': 
			if (strtoupper($v) == 'INVERT') {
			   if ($this->colorarray) {
				$cor = $this->colorarray;
				$this->outlineparam['COLOR'] = array('R'=> (255-$cor['R']), 'G'=> (255-$cor['G']), 'B'=> (255-$cor['B']));
			   }
			   else {
				$this->outlineparam['COLOR'] = array('R'=> 255, 'G'=> 255, 'B'=> 255);
			   }
			}
			else { 
		  	  $cor = ConvertColor($v);
			  if ($cor) { $this->outlineparam['COLOR'] = $cor ; }	  
			}
			break;

		case 'COLOR': // font color
		  $cor = ConvertColor($v);
			if ($cor) { 
				$this->colorarray = $cor;
				$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
				$this->issetcolor = true;
			}
		  break;

		case 'DIR': 
			// mPDF 2.2 - variable name changed to lowercase first letter
			$this->biDirectional = true;
			break;

	  }//end of switch($k)


   }//end of foreach
}

function SetStyle($tag,$enable)
{
	//Modify style and select corresponding font
	$this->$tag+=($enable ? 1 : -1);
	$style='';
  //Fix some SetStyle misuse
	if ($this->$tag < 0) $this->$tag = 0;
	if ($this->$tag > 1) $this->$tag = 1;
	foreach(array('B','I','U') as $s) {
		if($this->$s>0) {
			$style.=$s;
		}
	}
	$this->currentfontstyle=$style;
	$this->SetFont('',$style,0,false);
}

function GetStyle()
{
	$style='';
	foreach(array('B','I','U') as $s) {
		if($this->$s>0) {
			$style.=$s;
		}
	}
	return($style);
}


function DisableTags($str='')
{
  if ($str == '') //enable all tags
  {
    //Insert new supported tags in the long string below.
	///////////////////////////////////////////////////////
	// Added custom tags <indexentry>
    $this->enabledtags = "<span><s><strike><del><bdo><big><small><ins><cite><acronym><font><sup><sub><b><u><i><a><strong><em><code><samp><tt><kbd><var><q><table><thead><tfoot><tbody><tr><th><td><ol><ul><li><dl><dt><dd><form><input><select><textarea><option><div><p><h1><h2><h3><h4><h5><h6><pre><center><blockquote><address><hr><img><br><indexentry><bookmark><tts><ttz><tta><column_break><columnbreak><newcolumn><newpage><page_break><pagebreak><formfeed><columns><toc><tocentry><tocpagebreak><pageheader><pagefooter><setpageheader><setpagefooter><sethtmlpageheader><sethtmlpagefooter><annotation><template><jpgraph>";
  }
  else
  {
    $str = explode(",",$str);
    foreach($str as $v) $this->enabledtags = str_replace(trim($v),'',$this->enabledtags);
  }
}




function TableWordWrap($maxwidth, $forcewrap = 0, $textbuffer = '', $returnarray=false)
{
   $biggestword=0;//EDITEI
   $toonarrow=false;//EDITEI

   $curlyquote = mb_convert_encoding("\xe2\x80\x9e",$this->mb_encoding,'UTF-8');
   $curlylowquote = mb_convert_encoding("\xe2\x80\x9d",$this->mb_encoding,'UTF-8');

   // mPDF 3.0 Don't use ltrim as this gets rid of \n - new line from <br>
   //$textbuffer[0][0] = ltrim($textbuffer[0][0]);
   $textbuffer[0][0] = preg_replace('/^[ ]*/','',$textbuffer[0][0]);

   if ((count($textbuffer) == 0) or ((count($textbuffer) == 1) && ($textbuffer[0][0] == ''))) { return 0; }

   $text = '';
   $lh = 0; //mPDF 2.1 edited - was = $this->lineheight;
   $ch = 0;
   $width = 0;
   $ln = 1;	// Counts line number
   $mxw = $this->getStringWidth('W');	// Keep tabs on Maxwidth of actual text
   foreach ($textbuffer as $cctr=>$chunk) {
		$line = $chunk[0];

		//IMAGE
		// mPDF 3.0
      	if (substr($line,0,3) == "\xbb\xa4\xac") { //identifier has been identified!
	        $sccontent = split("\xbb\xa4\xac",$line,2);
      	  $sccontent = split(",",$sccontent[1],2);
      	  foreach($sccontent as $scvalue) {
      	    $scvalue = split("=",$scvalue,2);
      	    $specialcontent[$scvalue[0]] = $scvalue[1];
      	  }
			$objattr = unserialize($specialcontent['objattr']);

			// mPDF 2.2
			if ($objattr['type'] == 'nestedtable') {
				$text = "";
				$width = 0;
				$ch += $lh;
				$level = $objattr['level'];
				$ih = $this->table[($level+1)][$objattr['nestedcontent']]['h'];	// nested table width
				$ch += $ih;
				// mPDF 2.5
				//$lh = $this->lineheight; // Reset lineheight
				$lh = 0; // Reset lineheight
				$ln++;
				continue;
			}

			list($skipln,$iw,$ih) = $this->inlineObject($specialcontent['type'],0,0, $objattr, $this->lMargin,$width,$maxwidth,$lh,false,true);
			// mPDF 2.0
			if ($objattr['type']=='image') { 
				$lh = $ih;
			}

			if ($objattr['type'] == 'hr') {
				$text = "";
				$width = 0;
				$ch += $lh;
				$ch += $ih;
				// mPDF 2.5
				//$lh = $this->lineheight; // Reset lineheight
				$lh = 0; // Reset lineheight
				$ln++;
				continue;
			}
			if ($skipln==1 || $skipln==-2) {
				// Finish last line
				$text = "";
				$width = 0;
				$ch += $lh;
				$ln++;
				// mPDF 2.5
				//$lh = $this->lineheight; // Reset lineheight
				$lh = 0; // Reset lineheight
			}
			$lh = max($lh,$ih);
			$width += $iw;
			continue;
		}



		// SET FONT SIZE/STYLE from $chunk[n]
		// FONTSIZE
	      if(isset($chunk[11]) and $chunk[11] != '') { 
		   if ($this->shrin_k) {
			$this->SetFontSize($chunk[11]/$this->shrin_k,false); 
		   }
		   else {
			$this->SetFontSize($chunk[11],false); 
		   }
		}

		$fh = $this->table_lineheight*$this->FontSize; // Reset lineheight
		$lh = MAX($lh,$fh);

		// mPDF 3.0 Moved to after set FontSize
		if ($line == "\n") {
			$text = "";
			$width = 0;
			// mPDF 3.0
			if ($lh==0) { $lh = $this->table_lineheight*$this->FontSize; }
			$ch += $lh;
			$ln++;
			// mPDF 2.5
			//$lh = $this->lineheight; // Reset lineheight
			$lh = 0; // Reset lineheight
			// mPDF 3.0 Reset FontSize
			if(isset($chunk[11]) and $chunk[11] != '') { 
				$this->SetFontSize($this->default_font_size,false);
			}
			continue;
		}

		// FONTFAMILY
	      if(isset($chunk[4]) and $chunk[4] != '') { $font = $this->SetFont($chunk[4],$this->FontStyle,0,false); }

		// FONT STYLE B I U
	      if(isset($chunk[2]) and $chunk[2] != '') {
	          if (strpos($chunk[2],"B") !== false) $this->SetStyle('B',true); 

	          if (strpos($chunk[2],"I") !== false) $this->SetStyle('I',true); 

	      }

		$space = $this->GetStringWidth(' ');

	if (mb_substr($line,0,1,$this->mb_encoding ) == ' ') { 	// line (chunk) starts with a space
		$width += $space;
		$text .= ' ';
	}

	if (mb_substr($line,(mb_strlen($line,$this->mb_encoding )-1),1,$this->mb_encoding ) == ' ') { $lsend = true; }	// line (chunk) ends with a space
	else { $lsend = false; }
	$line= ltrim($line);
	$line= mb_rtrim($line, $this->mb_encoding);
	if ($line == '') { continue; }

	//****************************// Edited mPDF 1.1
	if ($this->is_MB && !$this->usingCoreFont) {
		$words = mb_split(' ', $line);
	}
	else {
		$words = split(' ', $line);
	}
	//****************************//
	foreach ($words as $word) {
		$word = mb_rtrim($word, $this->mb_encoding);
		$word = ltrim($word);
		$wordwidth = $this->GetStringWidth($word);
		//maxwidth is insufficient for one word
		if ($wordwidth > $maxwidth + 0.0001) {
			while($wordwidth > $maxwidth) {
				$chw = 0;	// check width
				for ( $i = 0; $i < mb_strlen($word, $this->mb_encoding ); $i++ ) {
					$chw = $this->GetStringWidth(mb_substr($word,0,$i+1,$this->mb_encoding ));
					if ($chw > $maxwidth) {
						if ($text) {
							$ch += $lh;
							// mPDF 2.5
							$lh = $fh ; // Reset lineheight
							//$lh = $this->lineheight; // Reset lineheight
							$ln++;
							$mxw = $maxwidth;
						}
						$text = mb_substr($word,0,$i,$this->mb_encoding );
						$word = mb_substr($word,$i,mb_strlen($word, $this->mb_encoding )-$i,$this->mb_encoding );
						$wordwidth = $this->GetStringWidth($word);
						$width = $maxwidth; 
						// mPDF 2.1
						break;
					}
				}
			}
		}
		// Word fits on line...
		if ($width + $wordwidth  < $maxwidth + 0.0001) {
			$mxw = max($mxw, ($width+$wordwidth));
			$width += $wordwidth + $space;
			$text .= $word.' ';
		}
		// Word does not fit on line...
		else {
			$alloworphans = false;
			// In case of orphan punctuation or SUB/SUP
			// Strip end punctuation
			// mPDF 2.5 add CJK , and .
			$tmp = preg_replace('/[\.,;:!?"'.$curlyquote . $curlylowquote ."\xef\xbc\x8c\xe3\x80\x82".']*$/','',$word);
			if ($tmp != $word) {
				$tmpwidth = $this->GetStringWidth($tmp);
				if ($width + $tmpwidth  < $maxwidth + 0.0001) { $alloworphans = true; }
			}
			// If line = SUB/SUP to max of orphansallowed ( $this->SUP || $this->SUB )
			if(( (isset($chunk[5]) and $chunk[5]) || (isset($chunk[6]) and $chunk[6])) && $orphs <= $this->orphansAllowed) {
				$alloworphans = true;
			}


			// if [stripped] word fits
			if ($alloworphans) {
				$mxw = $maxwidth;
				$width += $wordwidth + $space;
				$text .= $word.' ';
			}
			else {
				// mPDF 2.5 Soft hyphens
				// mPDF 3.0 Soft Hyphens chr(173)
				if (($this->is_MB && preg_match("/\xc2\xad/",$word)) || (!$this->is_MB && preg_match("/".chr(173)."/",$word) && ($this->FontFamily!='symbol' && $this->FontFamily!='zapfdingbats')) ) {
					list($success,$pre,$post,$prelength) = $this->softHyphenate($word, ($maxwidth - $width));
					if ($success) { 
						$text .= $pre.'-';
						$word = $post;
						$wordwidth = $this->GetStringWidth($word);
					}
				}
				// mPDF 2.5 Automatic hyphens
				else if ($this->hyphenate || ($this->hyphenateTables)) { 
					list($success,$pre,$post,$prelength) = $this->hyphenateWord($word, ($maxwidth - $width));
					if ($success) { 
						$text .= $pre.'-';
						$word = $post;
						$wordwidth = $this->GetStringWidth($word);
					}
				}
				$width = $wordwidth + $space;
				$text = $word.' ';
				$ch += $lh;
				$ln++;
				$mxw = $maxwidth;
				// mPDF 2.5
				$lh = $fh; // Reset lineheight
				// mPDF 2.2
				//	$lh = $this->lineheight; // Reset lineheight
			}
            }
	}

	// End of textbuffer chunk
	if (!$lsend) {
		$width -= $space;
		$text = mb_rtrim($text , $this->mb_encoding);
	}

	// RESET FONT SIZE/STYLE
	// RESETTING VALUES
	//Now we must deactivate what we have used
	if(isset($chunk[2]) and $chunk[2] != '') {
		$this->SetStyle('B',false);
		$this->SetStyle('I',false);
	}
	if(isset($chunk[4]) and $chunk[4] != '') {
		$this->SetFont($this->default_font,$this->FontStyle,0,false);
	}
	if(isset($chunk[11]) and $chunk[11] != '') { 
		$this->SetFontSize($this->default_font_size,false);
	}
   }

   // mPDF 2.2
   if ($width) {
	if ($returnarray) { return array(($ch + $lh),$ln,$mxw); }
	else { return ($ch + $lh) ; }
   }
   else {
	if ($returnarray) { return array(($ch),$ln,$mxw); }
	else { return ($ch) ; }
   }

}


function TableCheckMinWidth(&$text, $maxwidth, $forcewrap = 0, $textbuffer = '')
{
    $biggestword=0;//EDITEI
    $toonarrow=false;//EDITEI
	if ((count($textbuffer) == 0) or ((count($textbuffer) == 1) && ($textbuffer[0][0] == ''))) { return 0; }

    foreach ($textbuffer as $chunk) {

		$line = $chunk[0];

		// IMAGES & FORM ELEMENTS
		// mPDF 3.0
      	if (substr($line,0,3) == "\xbb\xa4\xac") { //inline object - FORM element or IMAGE!
			$sccontent = split("\xbb\xa4\xac",$line,2);
			$sccontent = split(",",$sccontent[1],2);
			foreach($sccontent as $scvalue) {
				$scvalue = split("=",$scvalue,2);
				$specialcontent[$scvalue[0]] = $scvalue[1];
			}
			$objattr = unserialize($specialcontent['objattr']);

			// mPDF 2.3
//			if ($objattr['type']=='image' && ($objattr['width']/$this->shrin_k) > ($maxwidth + 0.0001) ) { 
			if ($objattr['type']!='hr' && ($objattr['width']/$this->shrin_k) > ($maxwidth + 0.0001) ) { 
				if (($objattr['width']/$this->shrin_k) > $biggestword) { $biggestword = ($objattr['width']/$this->shrin_k); }
				$toonarrow=true;//EDITEI
			}
			continue;
		}

		if ($line == "\n") {
			continue;
		}
    		$line = ltrim($line );
    		$line = mb_rtrim($line , $this->mb_encoding);
		// SET FONT SIZE/STYLE from $chunk[n]

		// FONTSIZE
	      if(isset($chunk[11]) and $chunk[11] != '') { 
		   if ($this->shrin_k) {
			$this->SetFontSize($chunk[11]/$this->shrin_k,false); 
		   }
		   else {
			$this->SetFontSize($chunk[11],false); 
		   }
		}
		// FONTFAMILY
	      if(isset($chunk[4]) and $chunk[4] != '') { $font = $this->SetFont($chunk[4],$this->FontStyle,0,false); }
		// B I U
	      if(isset($chunk[2]) and $chunk[2] != '') {
	          if (strpos($chunk[2],"B") !== false) $this->SetStyle('B',true);
	          if (strpos($chunk[2],"I") !== false) $this->SetStyle('I',true);
	      }

	//****************************// Edited mPDF 1.1
	if ($this->is_MB && !$this->usingCoreFont) {
		$words = mb_split(' ', $line);
	}
	else {
		$words = split(' ', $line);
	}
	//****************************//
	foreach ($words as $word) {
		$word = mb_rtrim($word, $this->mb_encoding);
		$word = ltrim($word);
		$wordwidth = $this->GetStringWidth($word);

		//EDITEI
		//Warn user that maxwidth is insufficient
		if ($wordwidth > $maxwidth + 0.0001) {
			if ($wordwidth > $biggestword) { $biggestword = $wordwidth; }
			$toonarrow=true;//EDITEI
		}

	}
	// RESET FONT SIZE/STYLE
	// RESETTING VALUES
	//Now we must deactivate what we have used
	if(isset($chunk[2]) and $chunk[2] != '') {
	       $this->SetStyle('B',false);
	       $this->SetStyle('I',false);
	}
	if(isset($chunk[4]) and $chunk[4] != '') {
		$this->SetFont($this->default_font,$this->FontStyle,0,false);
	}
	if(isset($chunk[11]) and $chunk[11] != '') { 
		$this->SetFontSize($this->default_font_size,false);
	}
    }

    //Return -(wordsize) if word is bigger than maxwidth 
	// ADDED
      if (($toonarrow) && ($this->table_error_report)) {
		die("Word is too long to fit in table - ".$this->table_error_report_param); 
	}
    if ($toonarrow) return -$biggestword;
    else return 1;
}

// Added mPDF 2.0
function shrinkTable(&$table,$k) {
 		$table['border_spacing_H'] /= $k;
 		$table['border_spacing_V'] /= $k;

		$table['padding']['T'] /= $k;
		$table['padding']['R'] /= $k;
		$table['padding']['B'] /= $k;
		$table['padding']['L'] /= $k;

		$table['margin']['T'] /= $k;
		$table['margin']['R'] /= $k;
		$table['margin']['B'] /= $k;
		$table['margin']['L'] /= $k;

		$table['border_details']['T']['w'] /= $k;
		$table['border_details']['R']['w'] /= $k;
		$table['border_details']['B']['w'] /= $k;
		$table['border_details']['L']['w'] /= $k;

		$table['max_cell_border_width']['T'] /= $k;
		$table['max_cell_border_width']['R'] /= $k;
		$table['max_cell_border_width']['B'] /= $k;
		$table['max_cell_border_width']['L'] /= $k;

		$table['miw'] /= $k;
		$table['maw'] /= $k;

	//	unset($table['miw']);
	//	unset($table['maw']);
	//	$table['wc'] = array_pad(array(),$table['nc'],array('miw'=>0,'maw'=>0));

		for($j = 0 ; $j < $table['nc'] ; $j++ ) { //columns

		   // mPDF 2.2 - Edited
		   $table['wc'][$j]['miw'] /= $k;
		   $table['wc'][$j]['maw'] /= $k;

		   if ($table['wc'][$j]['absmiw'] ) $table['wc'][$j]['absmiw'] /= $k;

		   for($i = 0 ; $i < $table['nr']; $i++ ) { //rows
			$c = &$table['cells'][$i][$j];
			if (isset($c) && $c)  {
				// Added mPDF 2.0
				$c['border_details']['T']['w'] /= $k;
				$c['border_details']['R']['w'] /= $k;
				$c['border_details']['B']['w'] /= $k;
				$c['border_details']['L']['w'] /= $k;
				$c['border_details']['mbw']['T']['L'] /= $k;
				$c['border_details']['mbw']['T']['R'] /= $k;
				$c['border_details']['mbw']['B']['L'] /= $k;
				$c['border_details']['mbw']['B']['R'] /= $k;
				$c['border_details']['mbw']['L']['T'] /= $k;
				$c['border_details']['mbw']['L']['B'] /= $k;
				$c['border_details']['mbw']['R']['T'] /= $k;
				$c['border_details']['mbw']['R']['B'] /= $k;
				$c['padding']['T'] /= $k;
				$c['padding']['R'] /= $k;
				$c['padding']['B'] /= $k;
				$c['padding']['L'] /= $k;
				$c['maxs'] /= $k;
		  		// mPDF 2.2
				if ($c['w']) { $c['w'] /= $k; }
				$c['s'] /= $k;
				$c['maw'] /= $k;
				$c['miw'] /= $k;
				if ($c['absmiw'] ) $c['absmiw'] /= $k;
				$c['nestedmaw'] /= $k;
				$c['nestedmiw'] /= $k;
			}
		   }//rows
		}//columns
		// mPDF 2.1
		unset($c);
}


////////////////////////TABLE CODE (from PDFTable)/////////////////////////////////////
////////////////////////TABLE CODE (from PDFTable)/////////////////////////////////////
////////////////////////TABLE CODE (from PDFTable)/////////////////////////////////////
//table		Array of (w, h, bc, nr, wc, hr, cells)
//w			Width of table
//h			Height of table
//nc			Number column
//nr			Number row
//hr			List of height of each row
//wc			List of width of each column
//cells		List of cells of each rows, cells[i][j] is a cell in the table
function _tableColumnWidth(&$table,$firstpass=false){
	$cs = &$table['cells'];

	$nc = $table['nc'];
	$nr = $table['nr'];
	$listspan = array();

	// Added mPDF 2.2 - table border width if separate
	if ($table['borders_separate']) { 
		$tblbw = $table['border_details']['L']['w'] + $table['border_details']['R']['w'] + $table['margin']['L'] + $table['margin']['R'] +  $table['padding']['L'] + $table['padding']['R'] + $table['border_spacing_H'];
	}
	else { $tblbw = $table['max_cell_border_width']['L']/2 + $table['max_cell_border_width']['R']/2 + $table['margin']['L'] + $table['margin']['R']; }

	// ADDED table['l'][colno] 
	// = total length of text approx (using $c['s']) in that column - used to approximately distribute col widths in _tableWidth
	//
	for($j = 0 ; $j < $nc ; $j++ ) { //columns
		$wc = &$table['wc'][$j];
		for($i = 0 ; $i < $nr ; $i++ ) { //rows
			if (isset($cs[$i][$j]) && $cs[$i][$j])  {
				$c = &$cs[$i][$j];


				// Added mPDF 2.0
				if ($table['borders_separate']) {	// NB twice border width
					$extrcw = $c['border_details']['L']['w'] + $c['border_details']['R']['w'] + $c['padding']['L'] + $c['padding']['R'] + $table['border_spacing_H'];
				}
				else {
					$extrcw = $c['border_details']['L']['w']/2 + $c['border_details']['R']['w']/2 + $c['padding']['L'] + $c['padding']['R'];
				}

				// mPDF 2.0 - sets the absolute minimum width
				$c['absmiw'] = $mw;

				// Added mPDF 1.3 for rotated text in cell
				if ($c['R']) {
					$c['maw'] = $c['miw'] = $this->FontSize + $extrcw ;
					if (isset($c['w'])) {	// If cell width is specified
						if ($c['miw'] <$c['w'])	{ $c['miw'] = $c['w']; }
					}
					if (!isset($c['colspan'])) {
						if ($wc['miw'] < $c['miw']) { $wc['miw']	= $c['miw']; }
						if ($wc['maw'] < $c['maw']) { $wc['maw']	= $c['maw']; }

						if ($firstpass) { 
						   if (isset($table['l'][$j]) ) { 
							$table['l'][$j] += $c['miw'] ;
						   }
						   else {
							$table['l'][$j] = $c['miw'] ;
						   }
						}
					}
					if ($c['miw'] > $wc['miw']) { $wc['miw'] = $c['miw']; } 
        				if ($wc['miw'] > $wc['maw']) { $wc['maw'] = $wc['miw']; }
					continue;
				}

				// mPDF 2.0 This was previously done earlier
				if ($firstpass) {
					if (isset($c['s'])) { $c['s'] += $extrcw; }
					if (isset($c['maxs'])) { $c['maxs'] += $extrcw; }
					if (isset($c['nestedmiw'])) { $c['nestedmiw'] += $extrcw; }
					if (isset($c['nestedmaw'])) { $c['nestedmaw'] += $extrcw; }
				}


				// mPDF 2.0
				// If minimum width has already been set by a nested table or inline object (image/form), use it
				if (isset($c['nestedmiw'])) { $miw = $c['nestedmiw']; }
				else  { $miw = $mw; }

				if (isset($c['maxs']) && $c['maxs'] != '') { $c['s'] = $c['maxs']; }

				// mPDF 2.0
				// If maximum width has already been set by a nested table, use it
				if (isset($c['nestedmaw'])) { $c['maw'] = $c['nestedmaw']; }
				else $c['maw'] = $c['s'];

				if (isset($c['nowrap'])) { $miw = $c['maw']; }

				// mPDF 2.2
				if ($c['wpercent'] && $firstpass) {
	 				if (isset($c['colspan'])) {	// Not perfect - but % set on colspan is shared equally on cols.
					   for($k=0;$k<$c['colspan'];$k++) {
						$table['wc'][($j+$k)]['wpercent'] = $c['wpercent'] / $c['colspan'];
					   }
					}
	 				else {
						if ($table['w']) { $c['w'] = $c['wpercent']/100 * ($table['w'] - $tblbw ); }
						$wc['wpercent'] = $c['wpercent'];
					}
				}


				if (isset($c['w'])) {	// If cell width is specified
					if ($miw<$c['w'])	{ $c['miw'] = $c['w']; }	// Cell min width = that specified
					if ($miw>$c['w'])	{ $c['miw'] = $c['w'] = $miw; } // If width specified is less than minimum allowed (W) increase it
					if (!isset($wc['w'])) { $wc['w'] = 1; }		// If the Col width is not specified = set it to 1

				}
				else { $c['miw'] = $miw; }	// If cell width not specified -> set Cell min width it to minimum allowed (W)

				if ($c['maw']  < $c['miw']) { $c['maw'] = $c['miw']; }	// If Cell max width < Minwidth - increase it to =
				if (!isset($c['colspan'])) {
					if ($wc['miw'] < $c['miw']) { $wc['miw']	= $c['miw']; }	// Update Col Minimum and maximum widths
					if ($wc['maw'] < $c['maw']) { $wc['maw']	= $c['maw']; }
					if ($wc['absmiw'] < $c['absmiw']) { $wc['absmiw'] = $c['absmiw']; }	// Update Col Minimum and maximum widths

					if (isset($table['l'][$j]) ) { 
						$table['l'][$j] += $c['s'];
	
					}
					else {
						$table['l'][$j] = $c['s'];
					}

				}
				else { 
					$listspan[] = array($i,$j);
				}

 			//Check if minimum width of the whole column is big enough for largest word to fit
        			$auxtext = implode("",$c['text']);
	       		$minwidth = $this->TableCheckMinWidth($auxtext,$wc['miw']- $extrcw ,0,$c['textbuffer']); 
        			if ($minwidth < 0) { 
					//increase minimum width
					if (!isset($c['colspan'])) {
						$wc['miw'] = max($wc['miw'],((-$minwidth) + $extrcw) );  
					}
				}
 				if (!isset($c['colspan'])) {
	        			if ($wc['miw'] > $wc['maw']) { $wc['maw'] = $wc['miw']; } //update maximum width, if needed
				}
			}
			// mPDF 2.1
			unset($c);
		}//rows
	}//columns


	// COLUMN SPANS
	$wc = &$table['wc'];
	foreach ($listspan as $span) {
		list($i,$j) = $span;
		$c = &$cs[$i][$j];
		$lc = $j + $c['colspan'];
		if ($lc > $nc) { $lc = $nc; }
		
		$wis = $wisa = 0;
		$was = $wasa = 0;
		$list = array();
		for($k=$j;$k<$lc;$k++) {
			if (isset($table['l'][$k]) ) { 
				// Added mPDF 1.3 for rotated text in cell
				if ($c['R']) { $table['l'][$k] += $c['miw']/$c['colspan'] ; }
				else { $table['l'][$k] += $c['s']/$c['colspan']; }
			}
			else {
				// Added mPDF 1.3 for rotated text in cell
				if ($c['R']) { $table['l'][$k] = $c['miw']/$c['colspan'] ; }
				else { $table['l'][$k] = $c['s']/$c['colspan']; }
			}
			$wis += $wc[$k]['miw'];
			$was += $wc[$k]['maw'];
			if (!isset($c['w'])) {
				$list[] = $k;
				$wisa += $wc[$k]['miw'];
				$wasa += $wc[$k]['maw'];
			}
		}
		if ($c['miw'] > $wis) {
			if (!$wis) {
				for($k=$j;$k<$lc;$k++) { $wc[$k]['miw'] = $c['miw']/$c['colspan']; }
			}
			else if (!count($list)) {
				$wi = $c['miw'] - $wis;
				for($k=$j;$k<$lc;$k++) { $wc[$k]['miw'] += ($wc[$k]['miw']/$wis)*$wi; }
			}
			else {
				$wi = $c['miw'] - $wis;
				foreach ($list as $k) { $wc[$k]['miw'] += ($wc[$k]['miw']/$wisa)*$wi; }
			}
		}
		if ($c['maw'] > $was) {
			if (!$wis) {
				for($k=$j;$k<$lc;$k++) { $wc[$k]['maw'] = $c['maw']/$c['colspan']; }
			}
			else if (!count($list)) {
				$wi = $c['maw'] - $was;
				for($k=$j;$k<$lc;$k++) { $wc[$k]['maw'] += ($wc[$k]['maw']/$was)*$wi; }
			}
			else {
				$wi = $c['maw'] - $was;
				foreach ($list as $k) { $wc[$k]['maw'] += ($wc[$k]['maw']/$wasa)*$wi; }
			}
		}
		// mPDF 2.1
		unset($c);
	}


	$checkminwidth = 0;
	$checkmaxwidth = 0;
	$totallength = 0;

	for( $i = 0 ; $i < $nc ; $i++ ) {
		$checkminwidth += $table['wc'][$i]['miw'];
		$checkmaxwidth += $table['wc'][$i]['maw'];
		$totallength += $table['l'][$i];
	}

	// mPDF 2.2 Force percents
	if (!$table['w'] && $firstpass) {
	   $sumpc = 0;
	   for( $i = 0 ; $i < $nc ; $i++ ) {
		  if ($table['wc'][$i]['wpercent']) {
			$sumpc += $table['wc'][$i]['wpercent'];
		  }
	   }
	   if ($sumpc) {	// if any percents are set
		$sumnonpc = (100 - $sumpc);
		$sumpc = max($sumpc,100);
	      $miwleft = 0;
		$miwleftcount = 0;
		$miwsurplusnonpc = 0;
		$maxcalcmiw  = 0;
	      $mawleft = 0;
		$mawleftcount = 0;
		$mawsurplusnonpc = 0;
		$maxcalcmaw  = 0;
		for( $i = 0 ; $i < $nc ; $i++ ) {
		  if ($table['wc'][$i]['wpercent']) {
			$maxcalcmiw = max($maxcalcmiw, ($table['wc'][$i]['miw'] * $sumpc /$table['wc'][$i]['wpercent']) );
			$maxcalcmaw = max($maxcalcmaw, ($table['wc'][$i]['maw'] * $sumpc /$table['wc'][$i]['wpercent']) );
		  }
		  else {
			$miwleft += $table['wc'][$i]['miw'];
			$mawleft += $table['wc'][$i]['maw'];
		  	if (!$table['wc'][$i]['w']) { $miwleftcount++; $mawleftcount++; }
		  }
		}
		if ($miwleft && $sumnonpc > 0) { $miwnon = $miwleft * 100 / $sumnonpc; }
		if ($mawleft && $sumnonpc > 0) { $mawnon = $mawleft * 100 / $sumnonpc; }
		if (($miwnon > $checkminwidth || $maxcalcmiw > $checkminwidth) && $this->keep_table_proportions) {
			if ($miwnon > $maxcalcmiw) { 
				$miwsurplusnonpc = round((($miwnon * $sumnonpc / 100) - $miwleft),3); 
				$checkminwidth = $miwnon; 
			}
			else { $checkminwidth = $maxcalcmiw; }
			for( $i = 0 ; $i < $nc ; $i++ ) {
			  if ($table['wc'][$i]['wpercent']) {
				$newmiw = $checkminwidth * $table['wc'][$i]['wpercent']/100;
				if ($table['wc'][$i]['miw'] < $newmiw) {
				  $table['wc'][$i]['miw'] = $newmiw;
				}
				$table['wc'][$i]['w'] = 1;
			  }
			  else if ($miwsurplusnonpc && !$table['wc'][$i]['w']) {
				$table['wc'][$i]['miw'] +=  $miwsurplusnonpc / $miwleftcount;
			  }
			}
		}
		if (($mawnon > $checkmaxwidth || $maxcalcmaw > $checkmaxwidth )) {
			if ($mawnon > $maxcalcmaw) { 
				$mawsurplusnonpc = round((($mawnon * $sumnonpc / 100) - $mawleft),3); 
				$checkmaxwidth = $mawnon; 
			}
			else { $checkmaxwidth = $maxcalcmaw; }
			for( $i = 0 ; $i < $nc ; $i++ ) {
			  if ($table['wc'][$i]['wpercent']) {
				$newmaw = $checkmaxwidth * $table['wc'][$i]['wpercent']/100;
				if ($table['wc'][$i]['maw'] < $newmaw) {
				  $table['wc'][$i]['maw'] = $newmaw;
				}
				$table['wc'][$i]['w'] = 1;
			  }
			  else if ($mawsurplusnonpc && !$table['wc'][$i]['w']) {
				$table['wc'][$i]['maw'] +=  $mawsurplusnonpc / $mawleftcount;
			  }
			  if ($table['wc'][$i]['maw'] < $table['wc'][$i]['miw']) { $table['wc'][$i]['maw'] = $table['wc'][$i]['miw']; }
			}
		}
		if ($checkminwidth > $checkmaxwidth) { $checkmaxwidth = $checkminwidth; }
	   }
	}

	// mPDF 2.2
	if ($table['wpercent']) {
		$checkminwidth *= (100 / $table['wpercent']);
		$checkmaxwidth *= (100 / $table['wpercent']);
	}


	$checkminwidth += $tblbw ;
	$checkmaxwidth += $tblbw ;

	// mPDF 2.2  Table['miw'] set by percent in first pass may be larger than sum of column miw
	if ($checkminwidth > $table['miw']) $table['miw'] = $checkminwidth;
	if ($checkmaxwidth> $table['maw']) $table['maw'] = $checkmaxwidth;
	$table['tl'] = $totallength ;

	// mPDF 2.2
	if (!$this->isCJK) {
		if ($this->table_rotate) {
			$mxw = $this->tbrot_maxw;
		}
		else {
			$mxw = $this->blk[$this->blklvl]['inner_width'];
		}
		if (isset($table['w']) && $table['w'] ) {
			if ($table['w'] >= $checkminwidth && $table['w'] <= $mxw) { $mxw = $table['w']; }
			else if ($table['w'] >= $checkminwidth && $table['w'] > $mxw && $this->keep_table_proportions) { $checkminwidth = $table['w']; }
			else {  
				unset($table['w']); 
			}
		}
		$ratio = $checkminwidth/$mxw;
		if ($checkminwidth > $mxw) { return array(($ratio +0.001),$checkminwidth); }	// 0.001 to allow for rounded numbers when resizing
	}
	return array(0,0);
}



function _tableWidth(&$table){
	$widthcols = &$table['wc'];
	$numcols = $table['nc'];
	$tablewidth = 0;
	// Added mPDF1.4 - table border width if separate
	if ($table['borders_separate']) { 
		$tblbw = $table['border_details']['L']['w'] + $table['border_details']['R']['w'] + $table['margin']['L'] + $table['margin']['R'] +  $table['padding']['L'] + $table['padding']['R'] + $table['border_spacing_H'];
	}
	else { $tblbw = $table['max_cell_border_width']['L']/2 + $table['max_cell_border_width']['R']/2 + $table['margin']['L'] + $table['margin']['R']; }


	if ($table['level']>1 && isset($table['w'])) { 
		if ($table['wpercent']) { 
			$table['w'] = $temppgwidth = (($table['w']-$tblbw) * $table['wpercent'] / 100) + $tblbw ;  
		}
		else { 
			$temppgwidth = $table['w'] ;  
		}
	}
	else if ($this->table_rotate) {
		$temppgwidth = $this->tbrot_maxw;
		// If it is less than 1/20th of the remaining page height to finish the DIV (i.e. DIV padding + table bottom margin)
		// then allow for this
		$enddiv = $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'];
		if ($enddiv/$temppgwidth <0.05) { $temppgwidth -= $enddiv; }
	}
	else {
		// mPDF 2.2 ??? why the  - $this->kwt_height
		//$temppgwidth = $this->blk[$this->blklvl]['inner_width'] - $this->kwt_height ;
		if (isset($table['w']) && $table['w']< $this->blk[$this->blklvl]['inner_width']) { 
			$notfullwidth = 1;
			$temppgwidth = $table['w'] ;  
		}
		else { $temppgwidth = $this->blk[$this->blklvl]['inner_width']; }
	}

	// Removed mPDF 2.0 Inaccurate as does not calculate minimum cell width for cells based on individual cell padding/border widths etc.
	// Final check - If table cannot fit
//	$mw = $this->getStringWidth('W') + $extrw ; // Added
//	$checkwidth = ($mw * $table['nc']) + $table['border_spacing_H'] + $tblbw ;
//	if ($checkwidth > $this->blk[$this->blklvl]['inner_width']) { 
//	 die("Cannot fit table in width of page"); 
//	}


	$totaltextlength = 0;	// Added - to sum $table['l'][colno]
	$totalatextlength = 0;	// Added - to sum $table['l'][colno] for those columns where width not set
	// mPDF 2.2
	$percentages_set = 0; 
	for ( $i = 0 ; $i < $numcols ; $i++ ) {
		// mPDF 2.2
		if ($widthcols[$i]['wpercent'])  { $tablewidth += $widthcols[$i]['maw']; $percentages_set = 1; }
		else if (isset($widthcols[$i]['w']))  { $tablewidth += $widthcols[$i]['miw']; }
		else { $tablewidth += $widthcols[$i]['maw']; }
		$totaltextlength += $table['l'][$i];
	}
	if (!$totaltextlength) { $totaltextlength =1; }
	$tablewidth += $tblbw;	// Outer half of table borders

	// IF table width set by DEFINED or by sum of MAX widths of columns is too wide for page: set table width as pagewidth (see above)
	// mPDF 2.0 only do this for top level table - otherwise respect ['w'] set

	if ($tablewidth > $temppgwidth) { 
		$table['w'] = $temppgwidth; 
	}
	// mPDF 2.2	- if any widths set as percentages and max width fits < page width
	else if ($tablewidth < $temppgwidth && !$table['w'] && $percentages_set) {
		$table['w'] = $table['maw'];
	}
	// mPDF 2.2	- if table width is set and is > allowed width
	if ($table['w'] > $temppgwidth) { $table['w'] = $temppgwidth; }

	// IF the table width is now set - Need to distribute columns widths
	if (isset($table['w'])) {
		$wis = $wisa = 0;
		$list = array();
		$notsetlist = array();
		for( $i = 0 ; $i < $numcols ; $i++ ) {
			$wis += $widthcols[$i]['miw'];
			if (!isset($widthcols[$i]['w']) || ($widthcols[$i]['w'] && $table['w'] > $temppgwidth && !$this->keep_table_proportions && !$notfullwidth )){ 
				$list[] = $i;  
				$wisa += $widthcols[$i]['miw'];
				$totalatextlength += $table['l'][$i];
			}
		}
		if (!$totalatextlength) { $totalatextlength =1; }

		// Allocate spare (more than col's minimum width) across the cols according to their approx total text length
		// Do it by setting minimum width here
		if ($table['w'] > $wis + $tblbw) {
			// mPDF 2.2	First set any cell widths set as percentages
			if ($table['w'] < $temppgwidth || $this->keep_table_proportions) {
				for($k=0;$k<$numcols;$k++) {
					if ($widthcols[$k]['wpercent']) {
						$curr = $widthcols[$k]['miw'];
						$widthcols[$k]['miw'] = ($table['w']-$tblbw) * $widthcols[$k]['wpercent']/100;
						$wis += $widthcols[$k]['miw'] - $curr;
						$wisa += $widthcols[$k]['miw'] - $curr;
					}
				}
			}
			// Now allocate surplus up to maximum width of each column
			$surplus = 0;  $ttl = 0;	// number of surplus columns
			if (!count($list)) {
				$wi = ($table['w']-($wis + $tblbw));	//	i.e. extra space to distribute
				for($k=0;$k<$numcols;$k++) {
					// mPDF 2.0
					$spareratio = ($table['l'][$k] / $totaltextlength); //  gives ratio to divide up free space
					// Don't allocate more than Maximum required width - save rest in surplus
					if ($widthcols[$k]['miw'] + ($wi * $spareratio) > $widthcols[$k]['maw']) {
						$surplus += ($wi * $spareratio) - ($widthcols[$k]['maw']-$widthcols[$k]['miw']);
						$widthcols[$k]['miw'] = $widthcols[$k]['maw'];
					}
					else { 
						$notsetlist[] = $k;  
						$ttl += $table['l'][$k];
						$widthcols[$k]['miw'] += ($wi * $spareratio); 
					}

				}
			}
			else {
				// mPDF 2.2	This was $wisa (not $wis) ? doesn't work 
				$wi = ($table['w'] - ($wis + $tblbw));	//	i.e. extra space to distribute
				foreach ($list as $k) {
					// mPDF 2.0
					$spareratio = ($table['l'][$k] / $totalatextlength); //  gives ratio to divide up free space
					// Don't allocate more than Maximum required width - save rest in surplus
					if ($widthcols[$k]['miw'] + ($wi * $spareratio) > $widthcols[$k]['maw']) {
						$surplus += ($wi * $spareratio) - ($widthcols[$k]['maw']-$widthcols[$k]['miw']);
						$widthcols[$k]['miw'] = $widthcols[$k]['maw'];
					}
					else { 
						$notsetlist[] = $k;  
						$ttl += $table['l'][$k];
						$widthcols[$k]['miw'] += ($wi * $spareratio); 
					}
				}
			}
			// If surplus still left over apportion it across columns
			if ($surplus) { 
			   // mPDF 2.1 if some are set only add to remaining - otherwise add to all of them
			   if (count($notsetlist) && count($notsetlist) < $numcols) {
				foreach ($notsetlist AS $i) {
					if ($ttl) $widthcols[$i]['miw'] += $surplus * $table['l'][$i] / $ttl ;
				}
			   }
			   // mPDF 2.2 If some widths are defined, and others have been added up to their maxmum
			   else if (count($list) && count($list) < $numcols) {
				foreach ($list AS $i) {
					$widthcols[$i]['miw'] += $surplus / count($list) ;
				}
			   }
			   else if ($numcols) {	// If all columns
				$ttl = array_sum($table['l']);
				for ($i=0;$i<$numcols;$i++) {
					// mPDF 2.2
					$widthcols[$i]['miw'] += $surplus * $table['l'][$i] / $ttl;
				}
			   }
			}

		}

		// This sets the columns all to minimum width (which has been increased above if appropriate)
		for ($i=0;$i<$numcols;$i++) {
			$widthcols[$i] = $widthcols[$i]['miw'];
		}

		// TABLE NOT WIDE ENOUGH EVEN FOR MINIMUM CONTENT WIDTH
		// If sum of column widths set are too wide for table
		// mPDF 2.0 ? should never get here as will have resized
		$checktablewidth = 0;
		for ( $i = 0 ; $i < $numcols ; $i++ ) {
			$checktablewidth += $widthcols[$i];
		}
		if ($checktablewidth > ($temppgwidth + 0.001 - $tblbw)) { 
		   $usedup = 0; $numleft = 0;
		   for ($i=0;$i<$numcols;$i++) {
			if (($widthcols[$i] > (($temppgwidth - $tblbw) / $numcols)) && (!isset($widthcols[$i]['w']))) { 
				$numleft++; 
				unset($widthcols[$i]); 
			}
			else { $usedup += $widthcols[$i]; }
		   }
		   for ($i=0;$i<$numcols;$i++) {
			if (!$widthcols[$i]) { 
				$widthcols[$i] = ((($temppgwidth - $tblbw) - $usedup)/ ($numleft)); 
			}
		   }
		}

	}
	else { //table has no width defined
		$table['w'] = $tablewidth;  
		for ( $i = 0 ; $i < $numcols ; $i++) {
			// mPDF 2.2
			if ($widthcols[$i]['wpercent'] && $this->keep_table_proportions)  { $colwidth = $widthcols[$i]['maw']; }
			else if (isset($widthcols[$i]['w']))  { $colwidth = $widthcols[$i]['miw']; }
			else { $colwidth = $widthcols[$i]['maw']; }
			unset($widthcols[$i]);
			$widthcols[$i] = $colwidth;
		}
	}
}
	
function _tableHeight(&$table){
	// mPDF 2.0
	$level = $table['level'];
	$levelid = $table['levelid'];
	$cells = &$table['cells'];
	$numcols = $table['nc'];
	$numrows = $table['nr'];
	$listspan = array();
	$checkmaxheight = 0;
	$headerrowheight = 0;
	$checkmaxheightplus = 0;
	$headerrowheightplus = 0;
	if ($this->table_rotate) {
		$temppgheight = $this->tbrot_maxh;
		$remainingpage = $this->tbrot_maxh;
	}
	else {
		$temppgheight = ($this->h - $this->bMargin - $this->tMargin) - $this->kwt_height;
		$remainingpage = ($this->h - $this->bMargin - $this->y) - $this->kwt_height;
		// If it is less than 1/20th of the remaining page height to finish the DIV (i.e. DIV padding + table bottom margin)
		// then allow for this
		$enddiv = $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'] + $table['margin']['B'];
		if ($enddiv/$remainingpage <0.05) { $remainingpage -= $enddiv; }
		if ($enddiv/$temppgheight <0.05) { $temppgheight -= $enddiv; }
	}


	for( $i = 0 ; $i < $numrows ; $i++ ) { //rows
		$heightrow = &$table['hr'][$i];
		for( $j = 0 ; $j < $numcols ; $j++ ) { //columns
			if (isset($cells[$i][$j]) && $cells[$i][$j]) {
				$c = &$cells[$i][$j];


				// Added mPDF 2.0
				if ($table['borders_separate']) {	// NB twice border width
					$extraWLR = ($c['border_details']['L']['w']+$c['border_details']['R']['w']) + ($c['padding']['L']+$c['padding']['R'])+$table['border_spacing_H'];
					$extrh = ($c['border_details']['T']['w']+$c['border_details']['B']['w']) + ($c['padding']['T']+$c['padding']['B'])+$table['border_spacing_V'];
				}
				else {
					$extraWLR = ($c['border_details']['L']['w']+$c['border_details']['R']['w'])/2 + ($c['padding']['L']+$c['padding']['R']);
					$extrh = ($c['border_details']['T']['w']+$c['border_details']['B']['w'])/2 + ($c['padding']['T']+$c['padding']['B']);
				}


				list($x,$cw) = $this->_tableGetWidth($table, $i,$j);
				//Check whether width is enough for this cells' text
				$auxtext = implode("",$c['text']);
				$auxtext2 = $auxtext; //in case we have text with styles

				$aux3 = $auxtext; //in case we have text with styles


				// Get CELL HEIGHT = NO OF LINES
				// ++ extra parameter forces wrap to break word
				// Added mPDF 1.3 for rotated text in cell
				if ($c['R']) {
					$aux4 = implode(" ",$c['text']);
					$s_fs = $this->FontSizePt;
					$s_f = $this->Font;
					$s_st = $this->Style;
					$this->SetFont($c['textbuffer'][0][4],$c['textbuffer'][0][2],$c['textbuffer'][0][11] / $this->shrin_k,true,true);
					$aux4 = ltrim($aux4);
					$aux4= mb_rtrim($aux4,$this->mb_encoding);
	       			$tempch = $this->GetStringWidth($aux4);
					if ($c['R'] >= 45 && $c['R'] < 90) {
						$tempch = ((sin(deg2rad($c['R']))) * $tempch ) + ((sin(deg2rad($c['R']))) * (($c['textbuffer'][0][11]/$this->k) / $this->shrin_k));
					} 
					$this->SetFont($s_f,$s_st,$s_fs,true,true);
					$ch = ($tempch ) + $extrh ;  
				}
				else {
					$tempch = $this->TableWordWrap(($cw-$extraWLR),1,$c['textbuffer']);  
					// Added cellpadding top and bottom. (Lineheight already adjusted to table_lineheight)
					$ch = $tempch + $extrh ;
				}

				// mPDF 2.0 If cell minimum height [mih] set by a nested table, use it
				// mpdf2.2 - This is now dealt with in TableWordWrap
				// if (isset($c['mih']) && ($c['mih']+ $extrh )>$ch) { $ch = $c['mih']+ $extrh ; }

				//If height is bigger than page height...
				// mPDF 2.0 - Nested tables 
		//		if ($ch > $temppgheight) { $ch = $temppgheight; }

				//If height is defined and it is bigger than calculated $ch then update values
				if (isset($c['h']) && $c['h'] > $ch) {
					$c['mih'] = $ch; //in order to keep valign working
					$ch = $c['h'];
				}
				else $c['mih'] = $ch;
				if (isset($c['rowspan']))	$listspan[] = array($i,$j);
				elseif ($heightrow < $ch) $heightrow = $ch;
	
				// mPDF 2.0 this is the extra used in _tableWrite to determine whether to trigger a page change
				if ($table['borders_separate']) { 
				  // mPDF 3.0 - bug fix
				  //if ($i == ($numrows-1) || ($i+$cell['rowspan']) == ($numrows) ) {
				  if ($i == ($numrows-1) || ($i+$c['rowspan']) == ($numrows) ) {
					$extra = $table['margin']['B'] + $table['padding']['B'] + $table['border_details']['B']['w'] + $table['border_spacing_V']/2; 
				  }
				  else {
					$extra = $table['border_spacing_V']/2; 
				  }
				}
	  			else { $extra = $table['border_details']['B']['w'] /2; }

				// mPDF 3.0
				if ($i <$this->tableheadernrows && $this->usetableheader) {
					$headerrowheight = max($headerrowheight,$ch);
					$headerrowheightplus = max($headerrowheightplus,$ch+$extra);
				}
				else {
					$checkmaxheight = max($checkmaxheight,$ch);
					$checkmaxheightplus = max($checkmaxheightplus,$ch+$extra);
				}

				// mPDF 2.1 Free resources
				unset($c);
			}
		}//end of columns
	}//end of rows
	$heightrow = &$table['hr'];
	foreach ($listspan as $span) {
		list($i,$j) = $span;
		$c = &$cells[$i][$j];
		$lr = $i + $c['rowspan'];
		if ($lr > $numrows) $lr = $numrows;
		$hs = $hsa = 0;
		$list = array();
		for($k=$i;$k<$lr;$k++) {
			$hs += $heightrow[$k];
			if (!isset($c['h'])) {
				$list[] = $k;
				$hsa += $heightrow[$k];
			}
		}
		if ($c['mih'] > $hs) {
			if (!$hs) {
				for($k=$i;$k<$lr;$k++) $heightrow[$k] = $c['mih']/$c['rowspan'];
			}
			elseif (!count($list)) {
				$hi = $c['mih'] - $hs;
				for($k=$i;$k<$lr;$k++) $heightrow[$k] += ($heightrow[$k]/$hs)*$hi;
			}
			else {
				$hi = $c['mih'] - $hsa;
				foreach ($list as $k) $heightrow[$k] += ($heightrow[$k]/$hsa)*$hi;
			}
		}
		// mPDF 2.1 Free resources
		unset($c);
	}

	// mPDF 2.0 Nested Tables - Total Table Height
	$table['h'] = array_sum($table['hr']);
	if ($table['borders_separate']) { 
		$table['h'] += $table['margin']['T'] + $table['margin']['B'] + $table['border_details']['T']['w'] + $table['border_details']['B']['w'] + $table['border_spacing_V'] + $table['padding']['T'] +  $table['padding']['B'];
	}
	else { 
		$table['h'] += $table['margin']['T'] + $table['margin']['B'] + $table['max_cell_border_width']['T']/2 + $table['max_cell_border_width']['B']/2;
	}

	// mPDF 2.0
	$maxrowheight = $checkmaxheightplus + $headerrowheightplus;
	return array($table['h'],$maxrowheight,$temppgheight,$remainingpage);
}

function _tableGetWidth(&$table, $i,$j){
	$cell = &$table['cells'][$i][$j];
	if ($cell) {
		if (isset($cell['x0'])) return array($cell['x0'], $cell['w0']);
		$x = 0;
		$widthcols = &$table['wc'];
		for( $k = 0 ; $k < $j ; $k++ ) $x += $widthcols[$k];
		$w = $widthcols[$j];
		if (isset($cell['colspan'])) {
			 for ( $k = $j+$cell['colspan']-1 ; $k > $j ; $k-- )	$w += $widthcols[$k];
		}
		$cell['x0'] = $x;
		$cell['w0'] = $w;
		return array($x, $w);
	}
	return array(0,0);
}

function _tableGetHeight(&$table, $i,$j){
	$cell = &$table['cells'][$i][$j];
	if ($cell){
		if (isset($cell['y0'])) return array($cell['y0'], $cell['h0']);
		$y = 0;
		$heightrow = &$table['hr'];
		for ($k=0;$k<$i;$k++) $y += $heightrow[$k];
		$h = $heightrow[$i];
		if (isset($cell['rowspan'])){
			for ($k=$i+$cell['rowspan']-1;$k>$i;$k--)
				$h += $heightrow[$k];
		}
		$cell['y0'] = $y;
		$cell['h0'] = $h;
		return array($y, $h);
	}
	return array(0,0);
}


// Changed mPDF 2.0 to use bitwise flags
// CHANGED TO ALLOW TABLE BORDER TO BE SPECIFIED CORRECTLY - added border_details
// mPDF 3.0 Table borders need additional parameters - either corner (TLBR) and/or border-spacing-H or -V ($bsh/$bsv)
function _tableRect($x, $y, $w, $h, $bord=-1, $details=array(), $buffer=false, $bSeparate=false, $cort='cell', $tablecorner='', $bsv=0, $bsh=0){
	// mPDF 2.1
	// mPDF 3.0 Disabled again - buffer is printed at end of each table row - in fn. _tablewrite()
//	if ($this->ColActive) { $buffer = false; }

	if ($bord==-1) { $this->Rect($x, $y, $w, $h); }
	else if ($bord){

		// mPDF 2.0. Buffers cell borders to enable output in order of dominance
	   if (!$bSeparate && $buffer) {
		$priority = 'LRTB';
		for($p=0;$p<strlen($priority);$p++) {
			$side = substr($priority,$p,1);
			$details['p'] = $side ;

			// mPDF 2.1 color dominance - darker > white
			// mPDF 3.0 Slight precedence to R>G>B otherwise can randomly vary
			$coldom = (1-((($details[$side]['c']['R']*1.02)+($details[$side]['c']['G']*1.01)+$details[$side]['c']['B'])/765)); // 1 black - 0 white
			if ($coldom) { $dom += 2; }

			$dom = ($details[$side]['w'] * 100000) + (array_search($details[$side]['style'],$this->tblborderstyles)*100) + ($details[$side]['dom']*10) + $coldom;
			$save = false;
			if ($side == 'T' && $this->issetBorder($bord, _BORDER_TOP)) { $cbord = _BORDER_TOP; $dom += 1; $save = true; }
			else if ($side == 'L' && $this->issetBorder($bord, _BORDER_LEFT)) { $cbord = _BORDER_LEFT; $dom += 1; $save = true; }
			else if ($side == 'R' && $this->issetBorder($bord, _BORDER_RIGHT)) { $cbord = _BORDER_RIGHT; $save = true; }
			else if ($side == 'B' && $this->issetBorder($bord, _BORDER_BOTTOM)) { $cbord = _BORDER_BOTTOM; $save = true; }


			if ($save) {
			   $this->cellBorderBuffer[] = array(
				'side' => $side,
				'dom' => $dom,
				'x' => $x, 
				'y' => $y, 
				'w' => $w, 
				'h' => $h, 
				'bord' => $cbord, 
				'details' => $details,
				'borders_separate' => $bSeparate
			   );
			   if ($details[$side]['style'] == 'ridge' || $details[$side]['style'] == 'groove' || $details[$side]['style'] == 'inset' || $details[$side]['style'] == 'outset' || $details[$side]['style'] == 'double' ) {
			    $details[$side]['overlay'] = true;
			    $this->cellBorderBuffer[] = array(
				'side' => $side,
				'dom' => $dom+4,
				'x' => $x, 
				'y' => $y, 
				'w' => $w, 
				'h' => $h, 
				'bord' => $cbord, 
				'details' => $details,
				'borders_separate' => $bSeparate
			    );
			   }
			}
		}
		return;
	   }

	   if (isset($details['p']) && strlen($details['p'])>1) { $priority = $details['p']; }
	   else { $priority='LTRB'; }
	   $Tw = 0; 
	   $Rw = 0; 
	   $Bw = 0; 
	   $Lw = 0; 
		$Tw = $details['T']['w'];
		$Rw = $details['R']['w'];
		$Bw = $details['B']['w'];
		$Lw = $details['L']['w'];

	   $x2 = $x + $w; $y2 = $y + $h;
	   $oldlinewidth = $this->LineWidth;
	   // mPDF 2.0 
	   // $details['mbw']['L']['T'] = meeting border width - Left border - Top end

	   for($p=0;$p<strlen($priority);$p++) {
		$side = substr($priority,$p,1);
		$xadj = 0;
		$xadj2 = 0;
		$yadj = 0;
		$yadj2 = 0;
		$print = false;
		if ($Tw && $side=='T' && $this->issetBorder($bord, _BORDER_TOP)) {	// TOP
			$ly1 = $y;
			$ly2 = $y;
			$lx1 = $x;
			$lx2 = $x2;
			$this->SetLineWidth($Tw);
			// mPDF 3.0
			if ($cort == 'cell' || strpos($tablecorner,'L')!==false) {
				if ($Tw > $Lw) $xadj = ($Tw - $Lw)/2;
				if ($Tw < $Lw) $xadj = ($Tw + $Lw)/2;
			}
			else { $xadj = $Tw/2 - $bsh/2; }
			// mPDF 3.0
			if ($cort == 'cell' || strpos($tablecorner,'R')!==false) {
				if ($Tw > $Rw) $xadj2 = ($Tw - $Rw)/2;
				if ($Tw < $Rw) $xadj2 = ($Tw + $Rw)/2;
			}
			else { $xadj2 = $Tw/2 - $bsh/2; }
			if (!$bSeparate && $details['mbw']['T']['L']) {
				$xadj = ($Tw - $details['mbw']['T']['L'])/2 ;
			}
			if (!$bSeparate && $details['mbw']['T']['R']) {
				$xadj2 = ($Tw - $details['mbw']['T']['R'])/2;
			}
			$print = true;
		}
		if ($Lw && $side=='L' && $this->issetBorder($bord, _BORDER_LEFT)) {	// LEFT
			$ly1 = $y;
			$ly2 = $y2;
			$lx1 = $x;
			$lx2 = $x;
			$this->SetLineWidth($Lw);
			// mPDF 3.0
			if ($cort == 'cell' || strpos($tablecorner,'T')!==false) {
				if ($Lw > $Tw) $yadj = ($Lw - $Tw)/2;
				if ($Lw < $Tw) $yadj = ($Lw + $Tw)/2;
			}
			else { $yadj = $Lw/2 - $bsv/2; }
			// mPDF 3.0
			if ($cort == 'cell' || strpos($tablecorner,'B')!==false) {
				if ($Lw > $Bw) $yadj2 = ($Lw - $Bw)/2;
				if ($Lw < $Bw) $yadj2 = ($Lw + $Bw)/2;
			}
			else { $yadj2 = $Lw/2 - $bsv/2; }
			if (!$bSeparate && $details['mbw']['L']['T']) {
				$yadj = ($Lw - $details['mbw']['L']['T'])/2;
			}
			if (!$bSeparate && $details['mbw']['L']['B']) {
				$yadj2 = ($Lw - $details['mbw']['L']['B'])/2;
			}
			$print = true;
		}
		if ($Rw && $side=='R' && $this->issetBorder($bord, _BORDER_RIGHT)) {	// RIGHT
			$ly1 = $y;
			$ly2 = $y2;
			$lx1 = $x2;
			$lx2 = $x2;
			$this->SetLineWidth($Rw);
			// mPDF 3.0
			if ($cort == 'cell' || strpos($tablecorner,'T')!==false) {
				if ($Rw < $Tw) $yadj = ($Rw + $Tw)/2;
				if ($Rw > $Tw) $yadj = ($Rw - $Tw)/2;
			}
			else { $yadj = $Rw/2 - $bsv/2; }

			// mPDF 3.0
			if ($cort == 'cell' || strpos($tablecorner,'B')!==false) {
				if ($Rw > $Bw) $yadj2 = ($Rw - $Bw)/2;
				if ($Rw < $Bw) $yadj2 = ($Rw + $Bw)/2;
			}
			else { $yadj2 = $Rw/2 - $bsv/2; }

			if (!$bSeparate && $details['mbw']['R']['T']) {
				$yadj = ($Rw - $details['mbw']['R']['T'])/2;
			}
			if (!$bSeparate && $details['mbw']['R']['B']) {
				$yadj2 = ($Rw - $details['mbw']['R']['B'])/2;
			}
			$print = true;
		}
		if ($Bw && $side=='B' && $this->issetBorder($bord, _BORDER_BOTTOM)) {	// BOTTOM
			$ly1 = $y2;
			$ly2 = $y2;
			$lx1 = $x;
			$lx2 = $x2;
			$this->SetLineWidth($Bw);
			// mPDF 3.0
			if ($cort == 'cell' || strpos($tablecorner,'L')!==false) {
				if ($Bw > $Lw) $xadj = ($Bw - $Lw)/2;
				if ($Bw < $Lw) $xadj = ($Bw + $Lw)/2;
			}
			else { $xadj = $Bw/2 - $bsh/2; }
			// mPDF 3.0
			if ($cort == 'cell' || strpos($tablecorner,'R')!==false) {
				if ($Bw > $Rw) $xadj2 = ($Bw - $Rw)/2;
				if ($Bw < $Rw) $xadj2 = ($Bw + $Rw)/2;
			}
			else { $xadj2 = $Bw/2 - $bsh/2; }
			if (!$bSeparate && $details['mbw']['B']['L']) {
				$xadj = ($Bw - $details['mbw']['B']['L'])/2;
			}
			if (!$bSeparate && $details['mbw']['B']['R']) {
				$xadj2 = ($Bw - $details['mbw']['B']['R'])/2;
			}
			$print = true;
		}

		// Now draw line
		if ($print) {
		 if ($details[$side]['style'] == 'double') {
		   if (!$details[$side]['overlay'] || $bSeparate) {
			if ($details[$side]['c']) { 
				$this->SetDrawColor($details[$side]['c']['R'],$details[$side]['c']['G'],$details[$side]['c']['B']);
			}
			else { $this->SetDrawColor(0); }
			$this->Line($lx1 + $xadj, $ly1 + $yadj, $lx2 - $xadj2, $ly2 - $yadj2);
		   }
		   if ($details[$side]['overlay'] || $bSeparate) {
			if ($bSeparate && $cort=='table') {
				if ($side=='T') {
				   $xadj -= $this->LineWidth/2;
				   $xadj2 -= $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_LEFT)) {
					$xadj += $this->LineWidth/2; 
				   }
				   if ($this->issetBorder($bord, _BORDER_RIGHT)) {
					$xadj2 += $this->LineWidth; 
				   }
				}
				if ($side=='L') {
				   $yadj -= $this->LineWidth/2;
				   $yadj2 -= $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_TOP)) {
					$yadj += $this->LineWidth/2; 
				   }
				   if ($this->issetBorder($bord, _BORDER_BOTTOM)) {
					$yadj2 += $this->LineWidth; 
				   }
				}
				if ($side=='B') {
				   $xadj -= $this->LineWidth/2;
				   $xadj2 -= $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_LEFT)) {
					$xadj += $this->LineWidth/2;
				   }
				   if ($this->issetBorder($bord, _BORDER_RIGHT)) {
					$xadj2 += $this->LineWidth; 
				   }
				}
				if ($side=='R') {
				   $yadj -= $this->LineWidth/2;
				   $yadj2 -= $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_TOP)) {
					$yadj += $this->LineWidth/2;
				   }
				   if ($this->issetBorder($bord, _BORDER_BOTTOM)) {
					$yadj2 += $this->LineWidth; 
				   }
				}
			}

			$this->SetLineWidth($this->LineWidth/3);

			if ($bSeparate) {
			   $cellBorderOverlay[] = array(
				'x' => $lx1 + $xadj, 
				'y' => $ly1 + $yadj, 
				'x2' => $lx2 - $xadj2, 
				'y2' => $ly2 - $yadj2,
				'col' => array(255,255,255), 
				'lw' => $this->LineWidth,
			   );
			}
			else { 
				$this->SetDrawColor(255); 
				$this->Line($lx1 + $xadj, $ly1 + $yadj, $lx2 - $xadj2, $ly2 - $yadj2);
			}
		   }
		 }


		 else if (($details[$side]['style'] == 'ridge' || $details[$side]['style'] == 'groove' || $details[$side]['style'] == 'inset' || $details[$side]['style'] == 'outset')) {
		   if (!$details[$side]['overlay'] || $bSeparate) {
			if ($details[$side]['c']) { 
				$this->SetDrawColor($details[$side]['c']['R'],$details[$side]['c']['G'],$details[$side]['c']['B']);
			}
			else { $this->SetDrawColor(0); }
			if ($details[$side]['style'] == 'outset' || $details[$side]['style'] == 'groove') {
				$nc = $this->_darkenColor($details[$side]['c']);
				$this->SetDrawColor($nc[0],$nc[1],$nc[2]); 
			}
			else if ($details[$side]['style'] == 'ridge' || $details[$side]['style'] == 'inset') {
				$nc = $this->_lightenColor($details[$side]['c']);
				$this->SetDrawColor($nc[0],$nc[1],$nc[2]); 
			}
			$this->Line($lx1 + $xadj, $ly1 + $yadj, $lx2 - $xadj2, $ly2 - $yadj2);
		   }
		   if ($details[$side]['overlay'] || $bSeparate) {
			if ($details[$side]['c']) { 
				$this->SetDrawColor($details[$side]['c']['R'],$details[$side]['c']['G'],$details[$side]['c']['B']);
			}
			else { $this->SetDrawColor(0); }
			$doubleadj = ($this->LineWidth)/3;
			$this->SetLineWidth($this->LineWidth/2);
			$xadj3 = $yadj3 = $wadj3 = $hadj3 = 0;

			if ($details[$side]['style'] == 'ridge' || $details[$side]['style'] == 'inset') {
			   $nc = $this->_darkenColor($details[$side]['c']);

			   if ($bSeparate && $cort=='table') {
				if ($side=='T') {
				   $yadj3 = $this->LineWidth/2; 
				   $xadj3 = -$this->LineWidth/2;
				   $wadj3 = $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_LEFT)) {
					$xadj3 += $this->LineWidth; $wadj3 -= $this->LineWidth; 
				   }
				   if ($this->issetBorder($bord, _BORDER_RIGHT)) {
					$wadj3 -= $this->LineWidth*2; 
				   }
				}
				if ($side=='L') {
				   $xadj3 = $this->LineWidth/2; 
				   $yadj3 = -$this->LineWidth/2;
				   $hadj3 = $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_TOP)) {
					$yadj3 += $this->LineWidth; $hadj3 -= $this->LineWidth; 
				   }
				   if ($this->issetBorder($bord, _BORDER_BOTTOM)) {
					$hadj3 -= $this->LineWidth*2; 
				   }
				}
				if ($side=='B') {
				   $yadj3 = $this->LineWidth/2; 
				   $xadj3 = -$this->LineWidth/2;
				   $wadj3 = $this->LineWidth;
				}
				if ($side=='R') {
				   $xadj3 = $this->LineWidth/2; 
				   $yadj3 = -$this->LineWidth/2;
				   $hadj3 = $this->LineWidth;
				}
			   }

			   else if ($side=='T') { $yadj3 = $this->LineWidth/2; $xadj3 = $this->LineWidth/2; $wadj3 = -$this->LineWidth*2; }
			   else if ($side=='L') { $xadj3 = $this->LineWidth/2; $yadj3 = $this->LineWidth/2; $hadj3 = -$this->LineWidth*2; }

			   else if ($side=='B' && $bSeparate) { $yadj3 = $this->LineWidth/2; $wadj3 = $this->LineWidth/2; }
			   else if ($side=='R' && $bSeparate) { $xadj3 = $this->LineWidth/2; $hadj3 = $this->LineWidth/2; }

			   else if ($side=='B') { $yadj3 = $this->LineWidth/2; $xadj3 = $this->LineWidth/2; }
			   else if ($side=='R') { $xadj3 = $this->LineWidth/2; $yadj3 = $this->LineWidth/2; }
			}
			else {
			   $nc = $this->_lightenColor($details[$side]['c']);

			   if ($bSeparate && $cort=='table') {
				if ($side=='T') {
				   $yadj3 = $this->LineWidth/2; 
				   $xadj3 = -$this->LineWidth/2;
				   $wadj3 = $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_LEFT)) {
					$xadj3 += $this->LineWidth; $wadj3 -= $this->LineWidth; 
				   }
				}
				if ($side=='L') {
				   $xadj3 = $this->LineWidth/2; 
				   $yadj3 = -$this->LineWidth/2;
				   $hadj3 = $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_TOP)) {
					$yadj3 += $this->LineWidth; $hadj3 -= $this->LineWidth; 
				   }
				}
				if ($side=='B') {
				   $yadj3 = $this->LineWidth/2; 
				   $xadj3 = -$this->LineWidth/2;
				   $wadj3 = $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_LEFT)) {
					$xadj3 += $this->LineWidth; $wadj3 -= $this->LineWidth; 
				   }
				}
				if ($side=='R') {
				   $xadj3 = $this->LineWidth/2; 
				   $yadj3 = -$this->LineWidth/2;
				   $hadj3 = $this->LineWidth;
				   if ($this->issetBorder($bord, _BORDER_TOP)) {
					$yadj3 += $this->LineWidth; $hadj3 -= $this->LineWidth; 
				   }
				}
			   }

			   else if ($side=='T') { $yadj3 = $this->LineWidth/2; $xadj3 = $this->LineWidth/2; }
			   else if ($side=='L') { $xadj3 = $this->LineWidth/2; $yadj3 = $this->LineWidth/2; }

			   else if ($side=='B' && $bSeparate) { $yadj3 = $this->LineWidth/2; $xadj3 = $this->LineWidth/2; }
			   else if ($side=='R' && $bSeparate) { $xadj3 = $this->LineWidth/2; $yadj3 = $this->LineWidth/2; }

			   else if ($side=='B') { $yadj3 = $this->LineWidth/2; $xadj3 = -$this->LineWidth/2; $wadj3 = $this->LineWidth; }
			   else if ($side=='R') { $xadj3 = $this->LineWidth/2; $yadj3 = -$this->LineWidth/2;  $hadj3 = $this->LineWidth; }

			}

			if ($bSeparate) {
			   $cellBorderOverlay[] = array(
				'x' => $lx1 + $xadj + $xadj3, 
				'y' => $ly1 + $yadj + $yadj3, 
				'x2' => $lx2 - $xadj2 + $xadj3 + $wadj3, 
				'y2' => $ly2 - $yadj2 + $yadj3 + $hadj3,
				'col' => array($nc[0],$nc[1],$nc[2]), 
				'lw' => $this->LineWidth,
			   );
			}
			else { 
			   $this->SetDrawColor($nc[0],$nc[1],$nc[2]); 
			   $this->Line($lx1 + $xadj + $xadj3, $ly1 + $yadj + $yadj3, $lx2 - $xadj2 + $xadj3 + $wadj3, $ly2 - $yadj2 + $yadj3 + $hadj3);
			}
		   }
		 }


		 else {
		   if ($details[$side]['style'] == 'dashed') {
			$dashsize = 2;	// final dash will be this + 1*linewidth
			$dashsizek = 1.5;	// ratio of Dash/Blank
			$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
		   }
		   else if ($details[$side]['style'] == 'dotted') {
  			$this->_out("\n".'1 J'."\n".'1 j'."\n");
			// mPDF 2.0 Changed from $this->LineWidth*3
			$this->SetDash(0.001,($this->LineWidth*2));
		   }
		   if ($details[$side]['c']) { 
			$this->SetDrawColor($details[$side]['c']['R'],$details[$side]['c']['G'],$details[$side]['c']['B']);
		   }
		   else { $this->SetDrawColor(0); }
		   $this->Line($lx1 + $xadj, $ly1 + $yadj, $lx2 - $xadj2, $ly2 - $yadj2);
		 }

 	   	  // Reset Corners
	   	  $this->SetDash(); 
  		  //BUTT style line cap
  		  $this->_out('2 J');
		}
	   }


	   if ($bSeparate && count($cellBorderOverlay)) {
		foreach($cellBorderOverlay AS $cbo) {
			$this->SetLineWidth($cbo['lw']);
			$this->SetDrawColor($cbo['col'][0],$cbo['col'][1],$cbo['col'][2]); 
			$this->Line($cbo['x'], $cbo['y'], $cbo['x2'], $cbo['y2']);
		}
	   }

	   $this->SetLineWidth($oldlinewidth);
	   $this->SetDrawColor(0);
	}
}
// new mPDF 2.0
function _lightenColor($c) {
	$r = $c['R']; $g = $c['G']; $b = $c['B'];
	$var_r = $r / 255;
	$var_g = $g / 255;
	$var_b = $b / 255;
	list($h,$s,$l) = rgb2hsl($var_r,$var_g,$var_b);

	$l += ((1 - $l)*0.8);

	list($r,$g,$b) = hsl2rgb($h,$s,$l);
	return array($r,$g,$b);
}


function _darkenColor($c) {
	$r = $c['R']; $g = $c['G']; $b = $c['B'];
	$var_r = $r / 255;
	$var_g = $g / 255;
	$var_b = $b / 255;
	list($h,$s,$l) = rgb2hsl($var_r,$var_g,$var_b);

	$s *= 0.25;
	$l *= 0.75;

	list($r,$g,$b) = hsl2rgb($h,$s,$l);
	return array($r,$g,$b);
}




/*
// Added mPDF 1.1 for correct table border inheritance
function bord_bitadd($x,$y) {
	$x = str_pad($x,4,'0',STR_PAD_LEFT);
	$y = str_pad($y,4,'0',STR_PAD_LEFT);
	if (intval(substr($x,0,1)) || intval(substr($y,0,1))) { $a = '1'; } else { $a = '0'; }
	if (intval(substr($x,1,1)) || intval(substr($y,1,1))) { $b = '1'; } else { $b = '0'; }
	if (intval(substr($x,2,1)) || intval(substr($y,2,1))) { $c = '1'; } else { $c = '0'; }
	if (intval(substr($x,3,1)) || intval(substr($y,3,1))) { $d = '1'; } else { $d = '0'; }
	return $a . $b . $c . $d; 
}
*/

// Added mPDF 2.0 
function setBorder (&$var, $flag, $set = true) {
	$flag = intval($flag);
	if ($set) { $set = true; }
	$var = intval($var);
	$var = $set ? ($var | $flag) : ($var & ~$flag);
}
function issetBorder($var, $flag) {
	$flag = intval($flag);
	$var = intval($var);
	return (($var & $flag) == $flag);
}


// FIX BORDERS ********************************************
function _fixTableBorders(&$table){

	$cells = &$table['cells'];
	$numcols = $table['nc'];
	$numrows = $table['nr'];

	// Edited mPDF 2.0
	if (!$table['borders_separate'] && $table['border_details']['L']['w']) {
		$table['max_cell_border_width']['L'] = $table['border_details']['L']['w']; 
	}	
	if (!$table['borders_separate'] && $table['border_details']['R']['w']) {
		$table['max_cell_border_width']['R'] = $table['border_details']['R']['w']; 
	}	
	if (!$table['borders_separate'] && $table['border_details']['T']['w']) {
		$table['max_cell_border_width']['T'] = $table['border_details']['T']['w']; 
	}	
	if (!$table['borders_separate'] && $table['border_details']['B']['w']) {
		$table['max_cell_border_width']['B'] = $table['border_details']['B']['w']; 
	}	

	for( $i = 0 ; $i < $numrows ; $i++ ) { //Rows
	  for( $j = 0 ; $j < $numcols ; $j++ ) { //Columns
		if (isset($cells[$i][$j]) && $cells[$i][$j] && !$cells[$i][$j]['border']) {
  			if (isset($table['border']) && $table['border'] && $this->table_border_attr_set) {
				$cells[$i][$j]['border'] = $table['border'];
				$cells[$i][$j]['border_details'] = $table['border_details'];
			}
		}
	   }
	}


	for( $i = 0 ; $i < $numrows ; $i++ ) { //Rows
	  for( $j = 0 ; $j < $numcols ; $j++ ) { //Columns
		if (isset($cells[$i][$j]) && $cells[$i][$j]) {
			$cell = &$cells[$i][$j];

			 if ($cell['colspan']>1) { $ccolsp = $cell['colspan']; }
			 else { $ccolsp = 1; }
			 if ($cell['rowspan']>1) { $crowsp = $cell['rowspan']; }
			 else { $crowsp = 1; }

			// Inherit Cell border from Table border
			// Edited mPDF 2.0 - collapsed borders
			if ($this->table_border_css_set && !$table['borders_separate']) {
				if ($i == 0) {
				  if ($table['border_details']['T'] && $table['border_details']['T']['w'] > $cell['border_details']['T']['w']) {
					$cell['border_details']['T'] = $table['border_details']['T'];
					$this->setBorder ($cell['border'], _BORDER_TOP); 
				  }
				  else if ($table['border_details']['T'] && $table['border_details']['T']['w'] == $cell['border_details']['T']['w'] 
					&& array_search($table['border_details']['T']['style'],$this->tblborderstyles) > array_search($cell['border_details']['T']['style'],$this->tblborderstyles)) {

					$cell['border_details']['T'] = $table['border_details']['T'];
					$this->setBorder ($cell['border'], _BORDER_TOP); 
				  }
				}
				// Edited mPDF 1.1 for correct table border inheritance
				if ($i == ($numrows-1) || ($i+$crowsp) == ($numrows) ) {
				  if ($table['border_details']['B'] && $table['border_details']['B']['w'] > $cell['border_details']['B']['w']) {
					$cell['border_details']['B'] = $table['border_details']['B'];
					$this->setBorder ($cell['border'], _BORDER_BOTTOM); 
				  }
				  else if ($table['border_details']['B'] && $table['border_details']['B']['w'] == $cell['border_details']['B']['w'] 
					&& array_search($table['border_details']['B']['style'],$this->tblborderstyles) > array_search($cell['border_details']['B']['style'],$this->tblborderstyles)) {
					$cell['border_details']['B'] = $table['border_details']['B'];
					$this->setBorder ($cell['border'], _BORDER_BOTTOM); 
				  }
				}
				if ($j == 0) {
				  if ($table['border_details']['L'] && $table['border_details']['L']['w'] > $cell['border_details']['L']['w']) {
					$cell['border_details']['L'] = $table['border_details']['L'];
					$this->setBorder ($cell['border'], _BORDER_LEFT); 
				  }
				  else if ($table['border_details']['L'] && $table['border_details']['L']['w'] == $cell['border_details']['L']['w'] 
					&& array_search($table['border_details']['L']['style'],$this->tblborderstyles) > array_search($cell['border_details']['L']['style'],$this->tblborderstyles)) {
					$cell['border_details']['L'] = $table['border_details']['L'];
					$this->setBorder ($cell['border'], _BORDER_LEFT); 
				  }
				}
				// Edited mPDF 1.1 for correct table border inheritance
				if ($j == ($numcols-1) || ($j+$ccolsp) == ($numcols) ) {
				  if ($table['border_details']['R'] && $table['border_details']['R']['w'] > $cell['border_details']['R']['w']) {
					$cell['border_details']['R'] = $table['border_details']['R'];
					$this->setBorder ($cell['border'], _BORDER_RIGHT); 
				  }
				  else if ($table['border_details']['R'] && $table['border_details']['R']['w'] == $cell['border_details']['R']['w'] 
					&& array_search($table['border_details']['R']['style'],$this->tblborderstyles) > array_search($cell['border_details']['R']['style'],$this->tblborderstyles)) {
					$cell['border_details']['R'] = $table['border_details']['R'];
					$this->setBorder ($cell['border'], _BORDER_RIGHT); 
				  }
				}
			}



			if (isset($table['topntail'])) {
				if ($i == 0) {
				  $cell['border_details']['T'] = $this->border_details($table['topntail']);
				  $this->setBorder ($cell['border'], _BORDER_TOP); 
				  if ($table['borders_separate']) {
					$cell['border_details']['B'] = $this->border_details($table['topntail']);
					$this->setBorder ($cell['border'], _BORDER_BOTTOM); 
				  }
				}
				else if (($i == $this->tableheadernrows) && $this->usetableheader) {
				  if (!$table['borders_separate']) {
					$cell['border_details']['T'] = $this->border_details($table['topntail']);
					$this->setBorder ($cell['border'], _BORDER_TOP); 
				  }
				}
				// Added v1.4 for TFOOT
				else if (($i == ($numrows-1) ) && $this->tabletfoot) {
					$cell['border_details']['T'] = $this->border_details($table['topntail']);
					$this->setBorder ($cell['border'], _BORDER_TOP); 
				}
				else if ($this->tabletheadjustfinished) {	// $this->tabletheadjustfinished called from tableheader
				  if (!$table['borders_separate']) {
					$cell['border_details']['T'] = $this->border_details($table['topntail']);
					$this->setBorder ($cell['border'], _BORDER_TOP); 
				  }
				}
				// edited mPDF 1.1 for correct rowspan
				if ($i == ($numrows-1) || ($i+$crowsp) == ($numrows) ) {
					$cell['border_details']['B'] = $this->border_details($table['topntail']);
					$this->setBorder ($cell['border'], _BORDER_BOTTOM); 
				}
			}

			// Edited mPDF 1.1 add special CSS style thead-underline
			if (isset($table['thead-underline'])) {
				if ($table['borders_separate']) {
				  if ($i == 0) {
					$cell['border_details']['B'] = $this->border_details($table['thead-underline']);
					$this->setBorder ($cell['border'], _BORDER_BOTTOM); 
				  }
				}
				else  {
				  if (($i == $this->tableheadernrows) && $this->usetableheader) {
					$cell['border_details']['T'] = $this->border_details($table['thead-underline']);
					$this->setBorder ($cell['border'], _BORDER_TOP); 
				  }
				  else if ($this->tabletheadjustfinished) {	// $this->tabletheadjustfinished called from tableheader
					$cell['border_details']['T'] = $this->border_details($table['thead-underline']);
					$this->setBorder ($cell['border'], _BORDER_TOP); 
				  }
				}
			}


			// Collapse Border - Algorithm for conflicting borders
			// Hidden >>> Width > double>solid>dashed>dotted... >> style set on cell>table >> top/left>bottom/right
			// mPDF 3.0
			// Do not turn off border which is overridden
			// Needed for page break for TOP/BOTTOM both to be defined in Collapsed borders
			// Means it is painted twice. (Left/Right can still disable overridden border)
			if (!$table['borders_separate'] && (!$this->usetableheader || $i>($this->tableheadernrows-1)) ) {
			  if ($i < ($numrows-1)  || ($i+$crowsp) < $numrows ) {	// Bottom
			   for ($cspi = 0; $cspi<$ccolsp; $cspi++) {
				// already defined Top for adjacent cell below
				if ((is_array($cells[($i+$crowsp)][$j+$cspi]['border_details']['T'])) && ($cells[$i+$crowsp][$j+$cspi]['border_details']['T']['s'] == 1))  {
				   $csadj = $cells[($i+$crowsp)][$j+$cspi]['border_details']['T']['w'];
				   $csthis = $cell['border_details']['B']['w'];
				   // Hidden
				   if ($cell['border_details']['B']['style']=='hidden') {
					$cells[($i+$crowsp)][$j+$cspi]['border_details']['T'] = $cell['border_details']['B'];
					$this->setBorder ($cells[($i+$crowsp)][$j+$cspi]['border'] , _BORDER_TOP, false); 
					$this->setBorder ($cell['border'] , _BORDER_BOTTOM , false); 
				   }
				   else if ($cells[$i+$crowsp][$j+$cspi]['border_details']['T']['style']=='hidden') {
					$cell['border_details']['B'] = $cells[($i+$crowsp)][$j+$cspi]['border_details']['T'];
					$this->setBorder ($cell['border'] , _BORDER_BOTTOM , false); 
					$this->setBorder ($cells[($i+$crowsp)][$j+$cspi]['border'] , _BORDER_TOP, false); 
				   }
				   // Width
				   else if ($csthis > $csadj) {
				    if ($cells[($i+$crowsp)][$j+$cspi]['colspan']<2) {	// don't overwrite bordering cells that span
					$cells[($i+$crowsp)][$j+$cspi]['border_details']['T'] = $cell['border_details']['B'];
			// mPDF 3.0		$this->setBorder ($cells[($i+$crowsp)][$j+$cspi]['border'] , _BORDER_TOP, false); 
					$this->setBorder ($cell['border'] , _BORDER_BOTTOM); 
				    }
				   }
				   else if ($csadj > $csthis) {
				    if ($ccolsp < 2) {	// don't overwrite this cell if it spans
					$cell['border_details']['B'] = $cells[($i+$crowsp)][$j+$cspi]['border_details']['T'];
			// mPDF 3.0		$this->setBorder ($cell['border'] , _BORDER_BOTTOM, false); 
					$this->setBorder ($cells[($i+$crowsp)][$j+$cspi]['border'] , _BORDER_TOP); 
				    }
				   }

				   // double>solid>dashed>dotted... 
				   else if (array_search($cell['border_details']['B']['style'],$this->tblborderstyles) > array_search($cells[($i+$crowsp)][$j+$cspi]['border_details']['T']['style'],$this->tblborderstyles)) {
					$cells[($i+$crowsp)][$j+$cspi]['border_details']['T'] = $cell['border_details']['B'];
					$this->setBorder ($cell['border'] , _BORDER_BOTTOM ); 
			// mPDF 3.0		$this->setBorder ($cells[($i+$crowsp)][$j+$cspi]['border'] , _BORDER_TOP, false); 
				   }
				   else if (array_search($cells[($i+$crowsp)][$j+$cspi]['border_details']['T']['style'],$this->tblborderstyles) > array_search($cell['border_details']['B']['style'],$this->tblborderstyles)) {
					$cell['border_details']['B'] = $cells[($i+$crowsp)][$j+$cspi]['border_details']['T'];
					$this->setBorder ($cells[($i+$crowsp)][$j+$cspi]['border'] , _BORDER_TOP); 
			// mPDF 3.0		$this->setBorder ($cell['border'] , _BORDER_BOTTOM , false); 
				   }



				   // Style set on cell vs. table
				   else if ($cells[$i+$crowsp][$j+$cspi]['border_details']['T']['dom'] > $cell['border_details']['B']['dom']) {
				    if ($ccolsp < 2) {	// don't overwrite this cell if it spans
					$cell['border_details']['B'] = $cells[($i+$crowsp)][$j+$cspi]['border_details']['T'];
					$this->setBorder ($cells[($i+$crowsp)][$j+$cspi]['border'] , _BORDER_TOP); 
			// mPDF 3.0		$this->setBorder ($cell['border'] , _BORDER_BOTTOM , false); 
				    }
				   }
				   // Style set on cell vs. table  - OR - LEFT/TOP in preference to BOTTOM/RIGHT
				   else if ($cell['border_details']['B']['dom'] >= $cells[$i+$crowsp][$j+$cspi]['border_details']['T']['dom'] ) {
				    if ($cells[($i+$crowsp)][$j+$cspi]['colspan']<2) {	// don't overwrite bordering cells that span
					$cells[($i+$crowsp)][$j+$cspi]['border_details']['T'] = $cell['border_details']['B'];
			// mPDF 3.0		$this->setBorder ($cells[($i+$crowsp)][$j+$cspi]['border'] , _BORDER_TOP, false); 
					$this->setBorder ($cell['border'] , _BORDER_BOTTOM ); 
				    }
				   }
				}
				else {
				   // if below-cell border is not set
				   if (is_array($cells[($i+$crowsp)][$j+$cspi])) {	// check there is a cell n.b. colspan/rowspan
				    if ($cells[($i+$crowsp)][$j+$cspi]['colspan']<2) {	// don't overwrite bordering cells that span
					$cells[($i+$crowsp)][$j+$cspi]['border_details']['T'] = $cell['border_details']['B'];
			// mPDF 3.0		$this->setBorder ($cells[($i+$crowsp)][$j+$cspi]['border'] , _BORDER_TOP , false); 
				    }
				   }
				}
			   }
			  }

			  if ($j < ($numcols-1)  || ($j+$ccolsp) < $numcols ) {	// Right
			   for ($cspi = 0; $cspi<$crowsp; $cspi++) {
				// already defined Left for adjacent cell to R
				if ((is_array($cells[$i+$cspi][$j+$ccolsp]['border_details']['L'])) && ($cells[$i+$cspi][$j+$ccolsp]['border_details']['L']['s'] == 1)) {	
				   $csadj = $cells[$i+$cspi][$j+$ccolsp]['border_details']['L']['w'];
				   $csthis = $cell['border_details']['R']['w'];
				   // Hidden
				   if ($cell['border_details']['R']['style']=='hidden') {
					$cells[($i+$cspi)][$j+$ccolsp]['border_details']['L'] = $cell['border_details']['R'];
					$this->setBorder ($cells[($i+$cspi)][$j+$ccolsp]['border'] , _BORDER_LEFT, false); 
					$this->setBorder ($cell['border'] , _BORDER_RIGHT , false); 
				   }
				   else if ($cells[$i+$cspi][$j+$ccolsp]['border_details']['L']['style']=='hidden') {
					$cell['border_details']['R'] = $cells[($i+$cspi)][$j+$ccolsp]['border_details']['L'];
					$this->setBorder ($cell['border'] , _BORDER_RIGHT , false); 
					$this->setBorder ($cells[($i+$cspi)][$j+$ccolsp]['border'] , _BORDER_LEFT, false); 
				   }
				   // Width
				   else if ($csthis > $csadj) {
				    if ($cells[($i+$cspi)][$j+$ccolsp]['rowspan']<2) {	// don't overwrite bordering cells that span
					$cells[$i+$cspi][$j+$ccolsp]['border_details']['L'] = $cell['border_details']['R'];
					$this->setBorder ($cell['border'] , _BORDER_RIGHT); 
					$this->setBorder ($cells[$i+$cspi][$j+$ccolsp]['border'] , _BORDER_LEFT, false); 
				    }
				   }
				   else if ($csadj > $csthis) {
				    if ($crowsp < 2) {	// don't overwrite this cell if it spans
					$cell['border_details']['R'] = $cells[$i+$cspi][$j+$ccolsp]['border_details']['L'];
					$this->setBorder ($cell['border'] , _BORDER_RIGHT, false); 
					$this->setBorder ($cells[$i+$cspi][$j+$ccolsp]['border'] , _BORDER_LEFT); 
				    }
				   }

				   // double>solid>dashed>dotted... 
				   else if (array_search($cell['border_details']['R']['style'],$this->tblborderstyles) > array_search($cells[($i+$cspi)][$j+$ccolsp]['border_details']['L']['style'],$this->tblborderstyles)) {
					$cells[($i+$cspi)][$j+$ccolsp]['border_details']['L'] = $cell['border_details']['R'];
					$this->setBorder ($cells[($i+$cspi)][$j+$ccolsp]['border'] , _BORDER_LEFT, false); 
					$this->setBorder ($cell['border'] , _BORDER_RIGHT); 
				   }
				   else if (array_search($cells[($i+$cspi)][$j+$ccolsp]['border_details']['L']['style'],$this->tblborderstyles) > array_search($cell['border_details']['R']['style'],$this->tblborderstyles)) {
					$cell['border_details']['R'] = $cells[($i+$cspi)][$j+$ccolsp]['border_details']['L'];
					$this->setBorder ($cell['border'] , _BORDER_RIGHT , false); 
					$this->setBorder ($cells[($i+$cspi)][$j+$ccolsp]['border'] , _BORDER_LEFT); 
				   }


				   // Style set on cell vs. table
				   else if ($cells[$i+$cspi][$j+$ccolsp]['border_details']['L']['dom'] > $cell['border_details']['R']['dom']) {
				    if ($crowsp < 2) {	// don't overwrite this cell if it spans
					$cell['border_details']['R'] = $cells[$i+$cspi][$j+$ccolsp]['border_details']['L'];
					$this->setBorder ($cell['border'] , _BORDER_RIGHT , false); 
					$this->setBorder ($cells[$i+$cspi][$j+$ccolsp]['border'] , _BORDER_LEFT); 
				    }
				   }
				   // Style set on cell vs. table  - OR - LEFT/TOP in preference to BOTTOM/RIGHT
				   else if ($cell['border_details']['R']['dom'] >= $cells[$i+$cspi][$j+$ccolsp]['border_details']['L']['dom'] ) {
				    if ($cells[($i+$cspi)][$j+$ccolsp]['rowspan']<2) {	// don't overwrite bordering cells that span
					$cells[$i+$cspi][$j+$ccolsp]['border_details']['L'] = $cell['border_details']['R'];
					$this->setBorder ($cells[$i+$cspi][$j+$ccolsp]['border'] , _BORDER_LEFT , false); 
					$this->setBorder ($cell['border'] , _BORDER_RIGHT); 
				    }
				   }
				}
				else {
				   // if right-cell border is not set
				   if (is_array($cells[$i+$cspi][$j+$ccolsp])) {	// check there is a cell n.b. colspan/rowspan
				    if ($cells[($i+$cspi)][$j+$ccolsp]['rowspan']<2) {	// don't overwrite bordering cells that span
					$cells[$i+$cspi][$j+$ccolsp]['border_details']['L'] = $cell['border_details']['R'];
					$this->setBorder ($cells[$i+$cspi][$j+$ccolsp]['border'] , _BORDER_LEFT , false); 
				    }
				   }
				}
			   }
			  }
			}



			// Edited mPDF 2.0
			// Set maximum cell border width meeting at LRTB edges of cell - used for extended cell border
			// ['border_details']['mbw']['L']['T'] = meeting border width - Left border - Top end
			if (!$table['borders_separate']) {
			  $cell['border_details']['mbw']['B']['L'] = max($cell['border_details']['mbw']['B']['L'], $cell['border_details']['L']['w']);
			  $cell['border_details']['mbw']['B']['R'] = max($cell['border_details']['mbw']['B']['R'], $cell['border_details']['R']['w']);
			  $cell['border_details']['mbw']['R']['T'] = max($cell['border_details']['mbw']['R']['T'], $cell['border_details']['T']['w']);
			  $cell['border_details']['mbw']['R']['B'] = max($cell['border_details']['mbw']['R']['B'], $cell['border_details']['B']['w']);
			  $cell['border_details']['mbw']['T']['L'] = max($cell['border_details']['mbw']['T']['L'], $cell['border_details']['L']['w']);
			  $cell['border_details']['mbw']['T']['R'] = max($cell['border_details']['mbw']['T']['R'], $cell['border_details']['R']['w']);
			  $cell['border_details']['mbw']['L']['T'] = max($cell['border_details']['mbw']['L']['T'], $cell['border_details']['T']['w']);
			  $cell['border_details']['mbw']['L']['B'] = max($cell['border_details']['mbw']['L']['B'], $cell['border_details']['B']['w']);

			  if (($i+$crowsp) < $numrows ) {	// Has Bottom adjoining cell
					$cell['border_details']['mbw']['B']['L'] = max($cell['border_details']['mbw']['B']['L'], $cells[$i+$crowsp][$j]['border_details']['L']['w'], $cells[$i+$crowsp][$j]['border_details']['mbw']['T']['L']);
					$cell['border_details']['mbw']['B']['R'] = max($cell['border_details']['mbw']['B']['R'], $cells[$i+$crowsp][$j]['border_details']['R']['w'], $cells[$i+$crowsp][$j]['border_details']['mbw']['T']['R']);
					$cell['border_details']['mbw']['L']['B'] = max($cell['border_details']['mbw']['L']['B'], $cells[$i+$crowsp][$j]['border_details']['mbw']['L']['T']);
					$cell['border_details']['mbw']['R']['B'] = max($cell['border_details']['mbw']['R']['B'], $cells[$i+$crowsp][$j]['border_details']['mbw']['R']['T']);
			  }	

			  if (($j+$ccolsp) < $numcols ) {	// Has Right adjoining cell
					$cell['border_details']['mbw']['R']['T'] = max($cell['border_details']['mbw']['R']['T'], $cells[$i][$j+$ccolsp]['border_details']['T']['w'], $cells[$i][$j+$ccolsp]['border_details']['mbw']['L']['T']);
					$cell['border_details']['mbw']['R']['B'] = max($cell['border_details']['mbw']['R']['B'], $cells[$i][$j+$ccolsp]['border_details']['B']['w'], $cells[$i][$j+$ccolsp]['border_details']['mbw']['L']['B']);
					$cell['border_details']['mbw']['T']['R'] = max($cell['border_details']['mbw']['T']['R'], $cells[$i][$j+$ccolsp]['border_details']['mbw']['T']['L']);
					$cell['border_details']['mbw']['B']['R'] = max($cell['border_details']['mbw']['B']['R'], $cells[$i][$j+$ccolsp]['border_details']['mbw']['B']['L']);
			  }

			  if ($i > 0) {	// Has Top adjoining cell
					$cell['border_details']['mbw']['T']['L'] = max($cell['border_details']['mbw']['T']['L'], $cells[$i-1][$j]['border_details']['L']['w'], $cells[$i-1][$j]['border_details']['mbw']['B']['L']);
					$cell['border_details']['mbw']['T']['R'] = max($cell['border_details']['mbw']['T']['R'], $cells[$i-1][$j]['border_details']['R']['w'], $cells[$i-1][$j]['border_details']['mbw']['B']['R']);
					$cell['border_details']['mbw']['L']['T'] = max($cell['border_details']['mbw']['L']['T'], $cells[$i-1][$j]['border_details']['mbw']['L']['B']);
					$cell['border_details']['mbw']['R']['T'] = max($cell['border_details']['mbw']['R']['T'], $cells[$i-1][$j]['border_details']['mbw']['R']['B']);

				if ($cells[$i-1][$j]['border_details']['mbw']['B']['L']) {
					$cells[$i-1][$j]['border_details']['mbw']['B']['L'] = max($cell['border_details']['mbw']['T']['L'], $cells[$i-1][$j]['border_details']['mbw']['B']['L']);
				}
				if ($cells[$i-1][$j]['border_details']['mbw']['B']['R'] ) {
					$cells[$i-1][$j]['border_details']['mbw']['B']['R'] = max($cells[$i-1][$j]['border_details']['mbw']['B']['R'], $cell['border_details']['mbw']['T']['R']);
				}


			  }	
			  if ($j > 0) {	// Has Left adjoining cell
					$cell['border_details']['mbw']['L']['T'] = max($cell['border_details']['mbw']['L']['T'], $cells[$i][$j-1]['border_details']['T']['w'], $cells[$i][$j-1]['border_details']['mbw']['R']['T']);
					$cell['border_details']['mbw']['L']['B'] = max($cell['border_details']['mbw']['L']['B'], $cells[$i][$j-1]['border_details']['B']['w'], $cells[$i][$j-1]['border_details']['mbw']['R']['B']);
					$cell['border_details']['mbw']['B']['L'] = max($cell['border_details']['mbw']['B']['L'], $cells[$i][$j-1]['border_details']['mbw']['B']['R']);
					$cell['border_details']['mbw']['T']['L'] = max($cell['border_details']['mbw']['T']['L'], $cells[$i][$j-1]['border_details']['mbw']['T']['R']);

				if ($cells[$i][$j-1]['border_details']['mbw']['R']['T']) {
					$cells[$i][$j-1]['border_details']['mbw']['R']['T'] = max($cells[$i][$j-1]['border_details']['mbw']['R']['T'], $cell['border_details']['mbw']['L']['T']);
				}
				if ($cells[$i][$j-1]['border_details']['mbw']['R']['B']) {
					$cells[$i][$j-1]['border_details']['mbw']['R']['B'] = max($cells[$i][$j-1]['border_details']['mbw']['R']['B'], $cell['border_details']['mbw']['L']['B']);
				}
			  }	

			}	

			// Edited mPDF 2.0
			// Update maximum cell border width at LRTB edges of table - used for overall table width
			if (!$table['borders_separate']) {
			  if ($j == 0 && $cell['border_details']['L']['w']) {
				$table['max_cell_border_width']['L'] = max($table['max_cell_border_width']['L'],$cell['border_details']['L']['w']); 
			  }	
			  if (($j == ($numcols-1) || ($j+$ccolsp) == $numcols ) && $cell['border_details']['R']['w']) {
				$table['max_cell_border_width']['R'] = max($table['max_cell_border_width']['R'],$cell['border_details']['R']['w']); 
			  }	
			  if ($i == 0 && $cell['border_details']['T']['w']) {
				$table['max_cell_border_width']['T'] = max($table['max_cell_border_width']['T'],$cell['border_details']['T']['w']); 
			  }	
			  if (($i == ($numrows-1) || ($i+$crowsp) == $numrows ) && $cell['border_details']['B']['w']) {
				$table['max_cell_border_width']['B'] = max($table['max_cell_border_width']['B'],$cell['border_details']['B']['w']); 
			  }	
			}	

			// mPDF 2.1 Free resources
			unset($cell );
		}
	  }
	}


}
// END FIX BORDERS ************************************************************************************

function _tableWrite(&$table){
	// mPDF 2.0
	$level = $table['level'];
	$levelid = $table['levelid'];

	$cells = &$table['cells'];
	$numcols = $table['nc'];
	$numrows = $table['nr'];

	// mPDF 2.1
	if ($this->ColActive && $level==1) { $this->breakpoints[$this->CurrCol][] = $this->y; }

	// TABLE TOP MARGIN
	if ($table['margin']['T']) {
	   if (!$this->table_rotate && $level==1) {
		$this->DivLn($table['margin']['T'],$this->blklvl,true,1); 	// collapsible
	   }
	   else {
		$this->y += ($table['margin']['T']);
	   }
	}

	// Advance down page by half width of top border
	if ($table['borders_separate']) { 
		$adv = $table['padding']['T'] + $table['border_details']['T']['w'] + $table['border_spacing_V']/2; 
	}
	else { 
		$adv = $table['max_cell_border_width']['T']/2; 
	}
	if (!$this->table_rotate && $level==1) { $this->DivLn($adv); }
	else { $this->y += $adv; }


	// mPDF 2.0 - Nested tables
	if ($level==1) {
		$this->x = $this->lMargin  + $this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'];
		$x0 = $this->x; 
		$y0 = $this->y;
		$right = $x0 + $this->blk[$this->blklvl]['inner_width'];
		$outerfilled = $this->y;	// Keep track of how far down the outer DIV bgcolor is painted (NB rowspans)
		$this->outerfilled = $this->y;
	}
	else {
		$x0 = $this->x; 
		$y0 = $this->y;
		$right = $x0 + $table['w'];		// ????
		// $outerfilled = $this->y;	// Keep track of how far down the outer DIV bgcolor is painted (NB rowspans)
	}

	if ($this->table_rotate) {
		$temppgwidth = $this->tbrot_maxw;
		$this->PageBreakTrigger = $pagetrigger = $y0 + ($this->blk[$this->blklvl]['inner_width']);
	   if ($level==1) {
		$this->tbrot_y0 = $this->y - $adv - $table['margin']['T'] ;
		$this->tbrot_x0 = $this->x;
		$this->tbrot_w = $table['w'];
		if ($table['borders_separate']) { $this->tbrot_h = $table['margin']['T'] + $table['padding']['T'] + $table['border_details']['T']['w'] + $table['border_spacing_V']/2; }
		else { $this->tbrot_h = $table['margin']['T'] + $table['padding']['T'] + $table['max_cell_border_width']['T']; }
	   }
	}
	else {
		$this->PageBreakTrigger = $pagetrigger = ($this->h - $this->bMargin);
	   	if ($level==1) {
			$temppgwidth = $this->blk[$this->blklvl]['inner_width'];
	   		if (isset($table['a']) and ($table['w'] < $this->blk[$this->blklvl]['inner_width'])) {
				if ($table['a']=='C') { $x0 += ((($right-$x0) - $table['w'])/2); }
				else if ($table['a']=='R') { $x0 = $right - $table['w']; }
			}
	   	}
		else {
			$temppgwidth = $table['w'];
		}
	}

	if ($table['borders_separate']) { $indent = $table['margin']['L'] + $table['border_details']['L']['w'] + $table['padding']['L'] + $table['border_spacing_H']/2; }
	else { $indent = $table['margin']['L'] + $table['max_cell_border_width']['L']/2; }
	$x0 += $indent;

	$returny = 0;
	$tableheader = array();
	//Draw Table Contents and Borders
	for( $i = 0 ; $i < $numrows ; $i++ ) { //Rows

	  // Get Maximum row/cell height in row - including rowspan>1 + 1 overlapping
	  $maxrowheight = 0;
	  for( $j = 0 ; $j < $numcols ; $j++ ) { //Columns
		list($y,$h) = $this->_tableGetHeight($table, $i, $j);
		$maxrowheight = max($maxrowheight,$h);
	  }

	  $skippage = false;
	  $newpagestarted = false;
	  for( $j = 0 ; $j < $numcols ; $j++ ) { //Columns
		if (isset($cells[$i][$j]) && $cells[$i][$j]) {
			$cell = &$cells[$i][$j];
			list($x,$w) = $this->_tableGetWidth($table, $i, $j);
			list($y,$h) = $this->_tableGetHeight($table, $i, $j);
			$x += $x0;
			$y += $y0;
			$y -= $returny;
			// mPDF 2.0 Extra to test whether next row fits on page
			if ($table['borders_separate']) { 
			  if ($i == ($numrows-1) || ($i+$cell['rowspan']) == ($numrows) ) {
				// mPDF 2.1 Not including table margin
				$extra = $table['padding']['B'] + $table['border_details']['B']['w'] + $table['border_spacing_V']/2; 
				//$extra = $table['margin']['B'] + $table['padding']['B'] + $table['border_details']['B']['w'] + $table['border_spacing_V']/2; 
			  }
			  else {
				$extra = $table['border_spacing_V']/2; 
			  }
			}
	  		else { $extra = $table['max_cell_border_width']['B']/2; }
			if ((($y + $maxrowheight + $extra ) > $pagetrigger) && ($y0 >0 || $x0 > 0) && !$this->InFooter) {
				if (!$skippage) {
					$y -= $y0;
					$returny += $y;

					$oldcolumn = $this->CurrCol;
					if ($this->AcceptPageBreak()) {
	  					$newpagestarted = true;
						$this->y = $y + $y0;

						// mPDF 3.0 - Move down to account for border-spacing or 
						// extra half border width in case page breaks in middle
						if($i>0 && !$this->table_rotate && $level==1 && !$this->ColActive) {
							if ($table['borders_separate']) { $adv = $table['border_spacing_V']/2; }
							else { 
								$maxbwbottom = 0;
								for( $ctj = 0 ; $ctj < $numcols ; $ctj++ ) {
									if (isset($cells[$i][$ctj]) && $cells[$i][$ctj]) {
										$maxbwbottom = max($maxbwbottom , $cells[$i][$ctj]['border_details']['T']['w']); 
									}
								}
								$adv = $maxbwbottom /2;
							}
							$this->y += $adv;
						}

						// mPDF 3.0 Rotated table split over pages - needs this->y for borders/backgrounds
						if($i>0 && $this->table_rotate && $level==1) {
							$this->y = $y0 + $this->tbrot_w;
						}

						$this->AddPage($this->CurOrientation);
						// Added to correct for OddEven Margins
						$x=$x +$this->MarginCorrection;
						$x0=$x0 +$this->MarginCorrection;

/*
? Where is adv set

						// mPDF 2.0 - To develop - This adds a blank / background fill at top of new page
						// to cover border-spacing or extra half border width
						// mPDF 2.1 Not if Columns on
						if($i==0 && !$this->table_rotate && !$this->ColActive) {
							if (!$this->table_rotate && $level==1) { $this->DivLn($adv); }
							else { $this->y += $adv; }
						}
*/

						// mPDF 3.0 - Move down to account for half of top border-spacing or 
						// extra half border width in case page was broken in middle
						if($i>0 && !$this->table_rotate && $level==1 && !$this->usetableheader) {
							if ($table['borders_separate']) { $adv = $table['border_spacing_V']/2; }
							else { 
								$maxbwtop = 0;
								for( $ctj = 0 ; $ctj < $numcols ; $ctj++ ) {
									if (isset($cells[$i][$ctj]) && $cells[$i][$ctj]) {
										$maxbwtop = max($maxbwtop, $cells[$i][$ctj]['border_details']['T']['w']); 
									}
								}
								$adv = $maxbwtop /2;
							}
							$this->y += $adv;
						}


						if ($this->table_rotate) {
							// mPDF 3.0 Rotated table
							//$this->tbrot_x0 = $x0;
							$this->tbrot_x0 = $this->lMargin  + $this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'];
							if ($table['borders_separate']) { $this->tbrot_h = $table['margin']['T'] + $table['padding']['T'] + $table['border_details']['T']['w'] + $table['border_spacing_V']/2; }
							else { $this->tbrot_h = $table['margin']['T'] + $table['max_cell_border_width']['T'] ; }
							$this->tbrot_y0 = $this->y;
							$pagetrigger = $y0 + ($this->blk[$this->blklvl]['inner_width']);
						}
						// mPDF 2.1
						else {
							$pagetrigger = $this->PageBreakTrigger;
						}

						// mPDF 2.0 Keep-with-table
						if ($this->kwt_saved && $level==1) {
							$this->kwt_moved = true;
						}

						// Added mPDF 1.1 keeping block together on one page
						// Disable Table header repeat if Keep Block together
             				if ($this->usetableheader && !$this->keep_block_together) { 
							$this->TableHeader($tableheader,$tablestartpage,$tablestartcolumn);

							if ($this->table_rotate) {
								$this->tbrot_h += $tableheader[0][0]['h'];
							}

						}
						$outerfilled = 0;
						$y0 = $this->y; 
						$y = $y0;
					}
					// COLS
					// COLUMN CHANGE
					if ($this->CurrCol != $oldcolumn) {
						// Added to correct for Columns
						$x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
						$x0 += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
						if ($this->CurrCol == 0) { 	// just added a page - possibly with tableheader
							$y0 = $this->y; 	// this->y0 is global used by Columns - $y0 is internal to tablewrite
						}
						else {
							$y0 = $this->y0; 	// this->y0 is global used by Columns - $y0 is internal to tablewrite
						}
						$y = $y0;
						$outerfilled = 0;
					}
				}
				$skippage = true;
			}

			$this->x = $x; 
			$this->y = $y;

			// mPDF 2.0 Keep-with-table
			if ($this->kwt_saved && $level==1) {
				$this->printkwtbuffer();
				$x0 = $x = $this->x; 
				$y0 = $y = $this->y;
				$this->kwt_moved = false;
				$this->kwt_saved = false;
			}



			// Set the Page & Column where table starts
			if ($i==0 && $j==0 && $level==1) {
				if (($this->useOddEven) && (($this->page)%2==0)) {				// EVEN
					$tablestartpage = 'EVEN'; 
				}
				else if (($this->useOddEven) && (($this->page)%2==1)) {				// ODD
					$tablestartpage = 'ODD'; 
				}
				else { $tablestartpage = ''; }
				if ($this->ColActive) { $tablestartcolumn = $this->CurrCol; }
			}


			//ALIGN
			$align = $cell['a'];


			// mPDF 3.0 - If outside columns, this is done in PaintDivBB
			if ($this->ColActive) {
			 //OUTER FILL BGCOLOR of DIVS
			 if ($this->blklvl > 0 && ($j==0) && !$this->table_rotate && $level==1) {
			  $firstblockfill = $this->GetFirstBlockFill();
			  if ($firstblockfill && $this->blklvl >= $firstblockfill) {
			   $divh = $maxrowheight;
			   // Last row
	  		   if (($i == $numrows-1 && $cell['rowspan']<2) || ($cell['rowspan']>1 && ($i + $cell['rowspan']-1) == $numrows-1)) { 
				if ($table['borders_separate']) { 
					$adv = $table['margin']['B'] + $table['padding']['B'] + $table['border_details']['B']['w'] + $table['border_spacing_V']/2; 
				}
				else { 
					$adv = $table['margin']['B'] + $table['max_cell_border_width']['B']/2; 
				}
				$divh += $adv;  //last row: fill bottom half of bottom border (y advanced at end)
			   }

			   if (($this->y + $divh) > $outerfilled ) {	// if not already painted by previous rowspan
				$bak_x = $this->x;
				$bak_y = $this->y;
				if ($outerfilled > $this->y) { 
					$divh = ($this->y + $divh) - $outerfilled;
					$this->y = $outerfilled; 
				}

				$this->DivLn($divh,-3,false);
				$outerfilled = $this->y + $divh;
				// Reset current block fill
				$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
				if ($bcor ) $this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
				$this->x = $bak_x;
				$this->y = $bak_y;
			    }
			  }
			 }
			}


			//mPDF 2.0 TABLE BACKGROUND FILL BGCOLOR - for cellSpacing
			if ($table['borders_separate']) { 
			   $fill = isset($table['bgcolor'][$i]) ? $table['bgcolor'][$i]
  					: (isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0);
			   if ($fill) {
  				$color = ConvertColor($fill);
  				if ($color) $this->SetFillColor($color['R'],$color['G'],$color['B']);
				$xadj = ($table['border_spacing_H']/2);
				$yadj = ($table['border_spacing_V']/2);
				$wadj = $table['border_spacing_H'];
				$hadj = $table['border_spacing_V'];
 			   	if ($i == 0) {		// Top
					$yadj += $table['padding']['T'];
					$hadj += $table['padding']['T'];
			   	}
			   	if ($j == 0) {		// Left
					$xadj += $table['padding']['L'];
					$wadj += $table['padding']['L'];
			   	}
			   	if ($i == ($numrows-1) || ($i+$cell['rowspan']) == ($numrows) ) {	// Bottom
					$hadj += $table['padding']['B'];
			   	}
			   	if ($j == ($numcols-1) || ($j+$cell['colspan']) == ($numcols) ) {	// Right
					$wadj += $table['padding']['R'];
			   	}
				$this->Rect($x - $xadj, $y - $yadj, $w + $wadj, $h + $hadj, 'F');
			   }
			}

			// TABLE BORDER - if separate
 			if ($table['borders_separate'] && $table['border']) { 
			   $halfspaceL = $table['padding']['L'] + ($table['border_spacing_H']/2);
			   $halfspaceR = $table['padding']['R'] + ($table['border_spacing_H']/2);
			   $halfspaceT = $table['padding']['T'] + ($table['border_spacing_V']/2);
			   $halfspaceB = $table['padding']['B'] + ($table['border_spacing_V']/2);
			   $tbx = $x;
			   $tby = $y;
			   $tbw = $w;
			   $tbh = $h;
			   $tab_bord = 0;
			   // mPDF 3.0
			   $corner = '';
 			   if ($i == 0) {		// Top
				$tby -= $halfspaceT + ($table['border_details']['T']['w']/2);
				$tbh += $halfspaceT + ($table['border_details']['T']['w']/2);
				$this->setBorder ($tab_bord , _BORDER_TOP); 
				$corner .= 'T';
			   }
			   if ($i == ($numrows-1) || ($i+$cell['rowspan']) == ($numrows) ) {	// Bottom
				$tbh += $halfspaceB + ($table['border_details']['B']['w']/2);
				$this->setBorder ($tab_bord , _BORDER_BOTTOM); 
				$corner .= 'B';
			   }
			   if ($j == 0) {		// Left
				$tbx -= $halfspaceL + ($table['border_details']['L']['w']/2);
				$tbw += $halfspaceL + ($table['border_details']['L']['w']/2);
				$this->setBorder ($tab_bord , _BORDER_LEFT); 
				$corner .= 'L';
			   }
			   if ($j == ($numcols-1) || ($j+$cell['colspan']) == ($numcols) ) {	// Right
				$tbw += $halfspaceR + ($table['border_details']['R']['w']/2);
				$this->setBorder ($tab_bord , _BORDER_RIGHT); 
				$corner .= 'R';
			   }
			   $this->_tableRect($tbx, $tby, $tbw, $tbh, $tab_bord , $table['border_details'], false, $table['borders_separate'], 'table', $corner, $table['border_spacing_V'], $table['border_spacing_H'] );
			}

			// mPDF 2.0 Set flag for empty-cells:hide
			if ($table['empty_cells']!='hide' || !empty($cell['textbuffer']) || $cell['nestedcontent'] || !$table['borders_separate']  ) { $paintcell = true; }
			else { $paintcell = false; } 

			// mPDF 2.1 - Moved from after CELL FILL BGCOLOR - so can adjust for borders
			//Set Borders
			$bord = 0; 
			$bord_det = array();
  			if ($cell['border']) {
				$bord = $cell['border'];
				$bord_det = $cell['border_details'];
			}

			//CELL FILL BGCOLOR
			$fill = isset($cell['bgcolor']) ? $cell['bgcolor']
  					: (isset($table['bgcolor'][$i]) ? $table['bgcolor'][$i]
  					: (isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0));


			if ($fill && $paintcell) {
  				$color = ConvertColor($fill);
  				if ($color) $this->SetFillColor($color['R'],$color['G'],$color['B']);
 				if ($table['borders_separate']) { 
 					$this->Rect($x+ ($table['border_spacing_H']/2), $y+ ($table['border_spacing_V']/2), $w- $table['border_spacing_H'], $h- $table['border_spacing_V'], 'F');
				}
 				else { 
	 				$this->Rect($x, $y, $w, $h, 'F');
				}
			}

			// mPDF 3.0 - Tiling Patterns
			if ($cell['background-image'] && $paintcell) {
			  if ($cell['background-image']['image_id']) {	// Background pattern
				$n = count($this->patterns)+1;
 				if ($table['borders_separate']) { 
 					$px = $x+ ($table['border_spacing_H']/2);
					$py = $y+ ($table['border_spacing_V']/2);
					$pw = $w- $table['border_spacing_H'];
					$ph = $h- $table['border_spacing_V'];
				}
				$this->patterns[$n] = array('x'=>$px*$this->k, 'y'=>$py*$this->k, 'w'=>$pw*$this->k, 'h'=>$ph*$this->k, 'image_id'=>$cell['background-image']['image_id'], 'orig_w'=>$cell['background-image']['orig_w'], 'orig_h'=>$cell['background-image']['orig_h'], 'x_pos'=>$cell['background-image']['x_pos'], 'y_pos'=>$cell['background-image']['y_pos'], 'x_repeat'=>$cell['background-image']['x_repeat'], 'y_repeat'=>$cell['background-image']['y_repeat']);
				$this->_out(sprintf('/Pattern cs /P%d scn %.3f %.3f %.3f %.3f re f', $n, $px*$this->k, ($this->h-$py)*$this->k, $pw*$this->k, -$ph*$this->k));
			  }
			}

			// Added mPDF 1.1 for correct table border inheritance
			 if ($cell['colspan']>1) { $ccolsp = $cell['colspan']; }
			 else { $ccolsp = 1; }
			 if ($cell['rowspan']>1) { $crowsp = $cell['rowspan']; }
			 else { $crowsp = 1; }


			// mPDF 2.0 Borders are now fixed in fn. CloseTag 'TABLE' using fn. _fixTableBorders
			// but still need to do this for repeated headers...
			if (!$table['borders_separate'] && $this->tabletheadjustfinished) { // $this->tabletheadjustfinished from tableheader
			  if (isset($table['topntail'])) {
					$bord_det['T'] = $this->border_details($table['topntail']);
					$bord_det['T']['w'] /= $this->shrin_k;
					$this->setBorder ($bord, _BORDER_TOP); 
			  }
			  if (isset($table['thead-underline'])) {
					$bord_det['T'] = $this->border_details($table['thead-underline']);
					$bord_det['T']['w'] /= $this->shrin_k;
					$this->setBorder ($bord, _BORDER_TOP); 
			  }
			}




			//Get info of first row ==>> table header
			// mPDF 2.1 Use > 1 row if THEAD
			if ($this->usetableheader && ($i == 0  || $table['is_thead'][$i]) && $level==1) {
				$tableheader[$i][$j]['x'] = $x;
				$tableheader[$i][$j]['y'] = $y;
				$tableheader[$i][$j]['h'] = $h;
				$tableheader[$i][$j]['w'] = $w;
				$tableheader[$i][$j]['text'] = $cell['text'];
				$tableheader[$i][$j]['textbuffer'] = $cell['textbuffer'];
				$tableheader[$i][$j]['a'] = $cell['a'];
				// Added mPDF 1.3 for rotated text in cell
				$tableheader[$i][$j]['R'] = $cell['R'];

				$tableheader[$i][$j]['va'] = $cell['va'];
				$tableheader[$i][$j]['mih'] = $cell['mih'];
				$tableheader[$i][$j]['bgcolor'] = $fill;

				$tableheader[$i][$j]['border'] = $bord;
				$tableheader[$i][$j]['border_details'] = $bord_det;
				$tableheader[$i][$j]['padding'] = $cell['padding'];
				// mPDF 3.0
				$this->tableheadernrows = max($this->tableheadernrows, ($i+1));
			}


			// CELL BORDER
			if ($bord || $bord_det) { 
 				if ($table['borders_separate'] && $paintcell) { 
 					$this->_tableRect($x + ($table['border_spacing_H']/2)+($bord_det['L']['w'] /2), $y+ ($table['border_spacing_V']/2)+($bord_det['T']['w'] /2), $w-$table['border_spacing_H']-($bord_det['L']['w'] /2)-($bord_det['R']['w'] /2), $h- $table['border_spacing_V']-($bord_det['T']['w'] /2)-($bord_det['B']['w']/2), $bord, $bord_det, false, $table['borders_separate']);
				}
 				else if (!$table['borders_separate']) { 
					$this->_tableRect($x, $y, $w, $h, $bord, $bord_det, true, $table['borders_separate']); 	// true causes buffer
				}

			}

			//VERTICAL ALIGN
			// Added mPDF 1.3 for rotated text in cell
			// mPDF v2.1 - Does not override vertical-align to Bottom for rotate == 90
			if ($cell['R'] && INTVAL($cell['R']) > 0 && INTVAL($cell['R']) < 90 && isset($cell['va']) && $cell['va']!='B') { $cell['va']='B';}

			if (!isset($cell['va']) || $cell['va']=='M') $this->y += ($h-$cell['mih'])/2;
			elseif (isset($cell['va']) && $cell['va']=='B') $this->y += $h-$cell['mih'];

			// NESTED CONTENT mPDF 2.2 Moved to printbuffer->printobject


			// TEXT (and nested tables)

			$this->divalign=$align;

			$this->divwidth=$w;
			if (!empty($cell['textbuffer'])) {
				$opy = $this->y;
				// Edited mPDF 1.3 for rotated text in cell
				if ($cell['R']) {
					$cellPtSize = $cell['textbuffer'][0][11] / $this->shrin_k;
					// mPDF v2.1
					if (!$cellPtSize) { $cellPtSize = $this->default_font_size; }
					$cellFontHeight = ($cellPtSize/$this->k);
					$opx = $this->x;
					$angle = INTVAL($cell['R']);
					// Only allow 45 to 89 degrees (when bottom-aligned) or exactly 90 or -90
					if ($angle > 90) { $angle = 90; }
					else if ($angle > 0 && $angle <45) { $angle = 45; }
					else if ($angle < 0) { $angle = -90; }
					$offset = ((sin(deg2rad($angle))) * 0.37 * $cellFontHeight);
					// mPDF v2.1
					if (isset($cell['a']) && $cell['a']=='R') { 
						$this->x += ($w) + ($offset) - ($cellFontHeight/3) - ($cell['padding']['R'] + ($table['border_spacing_H']/2)); 
					}
					else if (!isset($cell['a']) || $cell['a']=='C') { 
						$this->x += ($w/2) + ($offset); 
					}
					else { 
						$this->x += ($offset) + ($cellFontHeight/3)+($cell['padding']['L'] +($table['border_spacing_H']/2)); 
					}
					$str = ltrim(implode(' ',$cell['text']));
					$str = mb_rtrim($str,$this->mb_encoding);
					if (!isset($cell['va']) || $cell['va']=='M') { 
						$this->y -= ($h-$cell['mih'])/2; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += (($h-$cell['mih'])/2) + $cell['padding']['T'] + ($cell['mih']-($cell['padding']['T'] + $cell['padding']['B'])); }
						else if ($angle < 0) { $this->y += (($h-$cell['mih'])/2)+ ($cell['padding']['T'] + ($table['border_spacing_V']/2)); }
					}
					elseif (isset($cell['va']) && $cell['va']=='B') { 
						$this->y -= $h-$cell['mih']; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += $h-($cell['padding']['B'] + ($table['border_spacing_V']/2)); }
						else if ($angle < 0) { $this->y += $h-$cell['mih'] + ($cell['padding']['T'] + ($table['border_spacing_V']/2)); }
					}
					elseif (isset($cell['va']) && $cell['va']=='T') { 
						if ($angle > 0) { $this->y += $cell['mih']-($cell['padding']['B'] + ($table['border_spacing_V']/2)); }
						else if ($angle < 0) { $this->y += ($cell['padding']['T'] + ($table['border_spacing_V']/2)); }
					}
					$this->Rotate($angle,$this->x,$this->y);
					$s_fs = $this->FontSizePt;
					$s_f = $this->Font;
					$s_st = $this->Style;
					$this->SetFont($cell['textbuffer'][0][4],$cell['textbuffer'][0][2],$cellPtSize,true,true);
					$this->Text($this->x,$this->y,$str);
					$this->Rotate(0);
					$this->SetFont($s_f,$s_st,$s_fs,true,true);
					$this->x = $opx;
				}
				else {

					if ($table['borders_separate']) {	// NB twice border width
						$xadj = $cell['border_details']['L']['w'] + $cell['padding']['L'] +($table['border_spacing_H']/2);
						$wadj = $cell['border_details']['L']['w'] + $cell['border_details']['R']['w'] + $cell['padding']['L'] +$cell['padding']['R'] + $table['border_spacing_H'];
						$yadj = $cell['border_details']['T']['w'] + $cell['padding']['T'] + ($table['border_spacing_H']/2);
					}
					else {
						$xadj = $cell['border_details']['L']['w']/2 + $cell['padding']['L'];
						$wadj = ($cell['border_details']['L']['w'] + $cell['border_details']['R']['w'])/2 + $cell['padding']['L'] + $cell['padding']['R'];
						$yadj = $cell['border_details']['T']['w']/2 + $cell['padding']['T'];
					}

					$this->divwidth=$w-$wadj;
					// mPDF 2.2 In case only content of cell is HR.
					if ($this->divwidth == 0) { $this->divwidth = 0.0001; }
					$this->x += $xadj;
					$this->y += $yadj;
					$this->printbuffer($cell['textbuffer'],'',true/*inside a table*/);
				}
				$this->y = $opy;
			}
			// mPDF 2.1 Free resources
			unset($cell );
			//Reset values
			$this->Reset();
		}//end of (if isset(cells)...)
	  }// end of columns

	  $newpagestarted = false;
	  $this->tabletheadjustfinished = false;

	  // mPDF 2.1
	  if ($this->ColActive && $i < $numrows-1 && $level==1) { $this->breakpoints[$this->CurrCol][] = $y + $h; }

	  // mPDF 3.0 - If columns, print out cell borders (in buffer) at end of each row)
	  if ($this->ColActive) {
	    if (count($this->cellBorderBuffer )) {
		usort( $this->cellBorderBuffer ,"_cmpdom"); 
		foreach($this->cellBorderBuffer AS $cbb) {
			$this->_tableRect($cbb['x'],$cbb['y'],$cbb['w'],$cbb['h'],$cbb['bord'],$cbb['details'], false, $table['borders_separate']);
		}
		$this->cellBorderBuffer = array();
	    }
	  }


	  if ($i == $numrows-1) { $this->y = $y + $h; } //last row jump (update this->y position)
	  if ($this->table_rotate && $level==1) {
		$this->tbrot_h += $h;
	  }

	}// end of rows


	if (count($this->cellBorderBuffer )) {
		usort( $this->cellBorderBuffer ,"_cmpdom"); 
		foreach($this->cellBorderBuffer AS $cbb) {
			$this->_tableRect($cbb['x'],$cbb['y'],$cbb['w'],$cbb['h'],$cbb['bord'],$cbb['details'], false, $table['borders_separate']);
		}
		$this->cellBorderBuffer = array();
	}


	// Advance down page by half width of bottom border
 	if ($table['borders_separate']) { $this->y += $table['padding']['B'] + $table['border_details']['B']['w'] + $table['border_spacing_V']/2; }
	else { $this->y += $table['max_cell_border_width']['B']/2; }

	if ($table['borders_separate'] && $level==1) { $this->tbrot_h += $table['margin']['B'] + $table['padding']['B'] + $table['border_details']['B']['w'] + $table['border_spacing_V']/2; }
	else if ($level==1) { $this->tbrot_h += $table['margin']['B'] + $table['max_cell_border_width']['B']/2; }


	// TABLE BOTTOM MARGIN
	if ($table['margin']['B']) {
	  if (!$this->table_rotate && $level==1) {
		$this->DivLn($table['margin']['B'],$this->blklvl,true); 	// collapsible
	  }
	  else {
		$this->y += ($table['margin']['B']);
	  }
	}

	// mPDF 2.1
	if ($this->ColActive && $level==1) { $this->breakpoints[$this->CurrCol][] = $this->y; }

}//END OF FUNCTION _tableWrite()


/////////////////////////END OF TABLE CODE//////////////////////////////////


function _putextgstates() {
	for ($i = 1; $i <= count($this->extgstates); $i++) {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_out('<</Type /ExtGState');
            foreach ($this->extgstates[$i]['parms'] as $k=>$v)
                $this->_out('/'.$k.' '.$v);
            $this->_out('>>');
            $this->_out('endobj');
	}
}




	var $encrypted=false;    //whether document is protected
	var $Uvalue;             //U entry in pdf document
	var $Ovalue;             //O entry in pdf document
	var $Pvalue;             //P entry in pdf document
	var $enc_obj_id;         //encryption object id
	var $last_rc4_key;       //last RC4 key encrypted (cached for optimisation)
	var $last_rc4_key_c;     //last RC4 computed key



	function SetProtection($permissions=array(),$user_pass='',$owner_pass=null)
	{
		$this->encrypted=false;
		if (!is_array($permissions)) { return 0; }


		if (count($permissions)<1) { return 0; }
		$this->last_rc4_key='';
		$this->padding="\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08".
						"\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
		$options = array('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32 );
		$protection = 192;
		foreach($permissions as $permission){
			if (!isset($options[$permission]))
				$this->Error('Incorrect permission: '.$permission);
			$protection += $options[$permission];
		}
		if ($owner_pass === null)
			$owner_pass = uniqid(rand());
		$this->encrypted = true;
		$this->_generateencryptionkey($user_pass, $owner_pass, $protection);
	}

/**
* Compute key depending on object number where the encrypted data is stored
*/
function _objectkey($n) {
		return substr($this->_md5_16($this->encryption_key.pack('VXxx',$n)),0,10);
}


// mPDF 3.0 - Tiling Patterns
function _putpatterns() {
	for ($i = 1; $i <= count($this->patterns); $i++) {
		$x = $this->patterns[$i]['x'];
		$y = $this->patterns[$i]['y']; 
		$w = $this->patterns[$i]['w'];
		$h = $this->patterns[$i]['h']; 
		$orig_w = $this->patterns[$i]['orig_w'];
		$orig_h = $this->patterns[$i]['orig_h']; 
		$image_id = $this->patterns[$i]['image_id'];
		if ($this->patterns[$i]['x_repeat']) { $x_repeat = true; } 
		else { $x_repeat = false; }
		if ($this->patterns[$i]['y_repeat']) { $y_repeat = true; }
		else { $y_repeat = false; }
		$x_pos = $this->patterns[$i]['x_pos'];
		if (stristr($x_pos ,'%') ) { 
			$x_pos += 0; 
			$x_pos /= 100; 
			$x_pos = ($w * $x_pos) - ($orig_w/$this->k * $x_pos);
		}
		$y_pos = $this->patterns[$i]['y_pos'];
		if (stristr($y_pos ,'%') ) { 
			$y_pos += 0; 
			$y_pos /= 100; 
			$y_pos = ($h * $y_pos) - ($orig_h/$this->k * $y_pos);
		}
		$adj_x = ($x_pos + $x) *$this->k;
		$adj_y = (($this->h - $y_pos - $y)*$this->k) - $orig_h ;
		$img_obj = false;
		foreach($this->images AS $img) {
			if ($img['i'] == $image_id) { $img_obj = $img['n']; break; }
		}
		if (!$img_obj ) { echo "Problem"; exit; }

            $this->_newobj();
            $this->_out('<</ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
            $this->_out('/XObject <</I'.$image_id.' '.$img_obj.' 0 R >>');
            $this->_out('>>');
            $this->_out('endobj');

		$this->_newobj();
		$this->patterns[$i]['n'] = $this->n;
		$this->_out('<< /Type /Pattern /PatternType 1 /PaintType 1 /TilingType 2');
		$this->_out('/Resources '. ($this->n-1) .' 0 R');
		$this->_out(sprintf('/BBox [0 0 %.2f %.2f]',$orig_w,$orig_h));
		if ($x_repeat) { $this->_out(sprintf('/XStep %.2f',$orig_w)); }
		else { $this->_out(sprintf('/XStep %d',999)); }
		if ($y_repeat) { $this->_out(sprintf('/YStep %.2f',$orig_h)); }
		else { $this->_out(sprintf('/YStep %d',999)); }

		$this->_out(sprintf('/Matrix [1 0 0 1 %.3f %.3f]',$adj_x,$adj_y));

		$s = sprintf("q %.3f 0 0 %.3f 0 0 cm /I%d Do Q",$orig_w,$orig_h,$image_id);

            if ($this->compress) {
			$this->_out('/Filter /FlateDecode');
			$s = gzcompress($s);
		}
		$this->_out('/Length '.strlen($s).'>>');
		$this->_putstream($s);
		$this->_out('endobj');
	}
}

// mPDF 3.0 added
function _putshaders() {
			foreach ($this->gradients as $id => $grad) {  
				if (($grad['type'] == 2) OR ($grad['type'] == 3)) {
					$this->_newobj();
					$this->_out('<<');
					$this->_out('/FunctionType 2');
					$this->_out('/Domain [0.0 1.0]');
					$this->_out('/C0 ['.$grad['col1'].']');
					$this->_out('/C1 ['.$grad['col2'].']');
					$this->_out('/N 1');
					$this->_out('>>');
					$this->_out('endobj');
					$f1 = $this->n;
				}
				$this->_newobj();
				$this->_out('<<');
				$this->_out('/ShadingType '.$grad['type']);
				$this->_out('/ColorSpace /DeviceRGB');
				if ($grad['type'] == 2) {
					$this->_out(sprintf('/Coords [%.3F %.3F %.3F %.3F]', $grad['coords'][0], $grad['coords'][1], $grad['coords'][2], $grad['coords'][3]));
					$this->_out('/Function '.$f1.' 0 R');
					$this->_out('/Extend ['.$grad['extend'][0].' '.$grad['extend'][1].'] ');
					$this->_out('>>');
				}
				else if ($grad['type'] == 3) {
					//x0, y0, r0, x1, y1, r1
					//at this this time radius of inner circle is 0
					$this->_out(sprintf('/Coords [%.3F %.3F 0 %.3F %.3F %.3F]', $grad['coords'][0], $grad['coords'][1], $grad['coords'][2], $grad['coords'][3], $grad['coords'][4]));
					$this->_out('/Function '.$f1.' 0 R');
					$this->_out('/Extend ['.$grad['extend'][0].' '.$grad['extend'][1].'] ');
					$this->_out('>>');
				}
				$this->_out('endobj');
				$this->gradients[$id]['id'] = $this->n;
			}
}


	
function _putresources() {
	$this->_putextgstates();
	$this->_putfonts();
	$this->_putimages();
	// mPDF 2.2 for WMF
	$this->_putformobjects();
	// mPDF 3.0 - Tiling Patterns
	$this->_putpatterns();
	$this->_putshaders();
	//Resource dictionary
	$this->offsets[2]=strlen($this->buffer);
	$this->_out('2 0 obj');
	$this->_out('<</ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->_out('/Font <<');
	foreach($this->fonts as $font)
		$this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
	$this->_out('>>');

	// mPDF 1.2
	if (count($this->extgstates)) {
		$this->_out('/ExtGState <<');
		foreach($this->extgstates as $k=>$extgstate)
			$this->_out('/GS'.$k.' '.$extgstate['n'].' 0 R');
		$this->_out('>>');
	}

	// mPDF 3.0 gradients
	if (isset($this->gradients) AND (count($this->gradients) > 0)) {
		$this->_out('/Shading <<');
		foreach ($this->gradients as $id => $grad) {
			$this->_out('/Sh'.$id.' '.$grad['id'].' 0 R');
		}
		$this->_out('>>');
	}

	// mPDF 2.2. for WMF
	if(count($this->images) or count($this->formobjects))	{
		$this->_out('/XObject <<');
		foreach($this->images as $image)
			$this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
            foreach($this->formobjects as $formobject)
                $this->_out('/FO'.$formobject['i'].' '.$formobject['n'].' 0 R');
		$this->_out('>>');
	}

	// mPDF 3.0 - Tiling Patterns
	if (count($this->patterns)) {
		$this->_out('/Pattern <<');
		foreach($this->patterns as $k=>$patterns)
			$this->_out('/P'.$k.' '.$patterns['n'].' 0 R');
		$this->_out('>>');
	}

	$this->_out('>>');
	$this->_out('endobj');	// end resource dictionary

	$this->_putbookmarks(); //EDITEI

	if ($this->encrypted) {
		$this->_newobj();
		$this->enc_obj_id = $this->n;
		$this->_out('<<');
		$this->_putencryption();
		$this->_out('>>');
		$this->_out('endobj');
	}
}

	function _putencryption()
	{
		$this->_out('/Filter /Standard');
		$this->_out('/V 1');
		$this->_out('/R 2');
		$this->_out('/O ('.$this->_escape($this->Ovalue).')');
		$this->_out('/U ('.$this->_escape($this->Uvalue).')');
		$this->_out('/P '.$this->Pvalue);
	}

	function _puttrailer()
	{
		$this->_out('/Size '.($this->n+1));
		$this->_out('/Root '.$this->n.' 0 R');
		$this->_out('/Info '.($this->n-1).' 0 R');
		if ($this->encrypted) {
			$this->_out('/Encrypt '.$this->enc_obj_id.' 0 R');
			$this->_out('/ID [()()]');
		}
	}

	/**
	* RC4 is the standard encryption algorithm used in PDF format
	*/
	function _RC4($key, $text)
	{
		if ($this->last_rc4_key != $key) {
			$k = str_repeat($key, 256/strlen($key)+1);
			$rc4 = range(0,255);
			$j = 0;
			for ($i=0; $i<256; $i++){
				$t = $rc4[$i];
				$j = ($j + $t + $this->ords[substr($k,$i,1)]) % 256;
				$rc4[$i] = $rc4[$j];
				$rc4[$j] = $t;
			}
			$this->last_rc4_key = $key;
			$this->last_rc4_key_c = $rc4;
		} else {
			$rc4 = $this->last_rc4_key_c;
		}

		$len = strlen($text);
		$a = 0;
		$b = 0;
		$out = '';
		for ($i=0; $i<$len; $i++){
			$a = ($a+1)%256;
			$t= $rc4[$a];
			$b = ($b+$t)%256;
			$rc4[$a] = $rc4[$b];
			$rc4[$b] = $t;
			$k = $rc4[($rc4[$a]+$rc4[$b])%256];
			$out.=$this->chrs[$this->ords[substr($text,$i,1)] ^ $k];
		}

		return $out;
	}

	/**
	* Get MD5 as binary string
	*/
	function _md5_16($string)
	{
		return pack('H*',md5($string));
	}

	/**
	* Compute O value
	*/
	function _Ovalue($user_pass, $owner_pass)
	{
		$tmp = $this->_md5_16($owner_pass);
		$owner_RC4_key = substr($tmp,0,5);
		return $this->_RC4($owner_RC4_key, $user_pass);
	}

	/**
	* Compute U value
	*/
	function _Uvalue()
	{
		return $this->_RC4($this->encryption_key, $this->padding);
	}

	/**
	* Compute encryption key
	*/
	function _generateencryptionkey($user_pass, $owner_pass, $protection)
	{
		// Pad passwords
		$user_pass = substr($user_pass.$this->padding,0,32);
		$owner_pass = substr($owner_pass.$this->padding,0,32);
		// Compute O value
		$this->Ovalue = $this->_Ovalue($user_pass,$owner_pass);
		// Compute encyption key
		$tmp = $this->_md5_16($user_pass.$this->Ovalue.$this->chrs[$protection]."\xFF\xFF\xFF");
		$this->encryption_key = substr($tmp,0,5);
		// Compute U value
		$this->Uvalue = $this->_Uvalue();
		// Compute P value
		$this->Pvalue = -(($protection^255)+1);
	}

//=========================================
// FROM class PDF_Bookmark

	var $BMoutlines=array();
	var $OutlineRoot;

function Bookmark($txt,$level=0,$y=0)
{
	//****************************//
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if($y==-1) {
		if (!$this->ColActive){ $y=$this->y; }
		else { $y = $this->y0; }	// If columns are on - mark top of columns
	}
	// else y is used as set, or =0 i.e. top of page
	// DIRECTIONALITY RTL
	// mPDF 3.0
//	$this->magic_reverse_dir($txt);
	// Edited mPDF 1.1 Keep Block together
	if ($this->keep_block_together) {
		$this->ktBMoutlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->page);
	}
	// mPDF 3.0
	else if ($this->table_rotate) {
		$this->tbrot_BMoutlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->page);
	}
	else if ($this->kwt) {
		$this->kwt_BMoutlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->page);
	}
	else if ($this->ColActive) {
		$this->col_BMoutlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->page);
	}
	else {
		$this->BMoutlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->page);
	}
}


function _putbookmarks()
{
	$nb=count($this->BMoutlines);
	if($nb==0)
		return;
	$lru=array();
	$level=0;
	foreach($this->BMoutlines as $i=>$o)
	{
		if($o['l']>0)
		{
			$parent=$lru[$o['l']-1];
			//Set parent and last pointers
			$this->BMoutlines[$i]['parent']=$parent;
			$this->BMoutlines[$parent]['last']=$i;
			if($o['l']>$level)
			{
				//Level increasing: set first pointer
				$this->BMoutlines[$parent]['first']=$i;
			}
		}
		else
			$this->BMoutlines[$i]['parent']=$nb;
		if($o['l']<=$level and $i>0)
		{
			//Set prev and next pointers
			$prev=$lru[$o['l']];
			$this->BMoutlines[$prev]['next']=$i;
			$this->BMoutlines[$i]['prev']=$prev;
		}
		$lru[$o['l']]=$i;
		$level=$o['l'];
	}
	//Outline items
	$n=$this->n+1;
	foreach($this->BMoutlines as $i=>$o)
	{
		$this->_newobj();
		$this->_out('<</Title '.$this->_UTF16BEtextstring($o['t']));
		$this->_out('/Parent '.($n+$o['parent']).' 0 R');
		if(isset($o['prev']))
			$this->_out('/Prev '.($n+$o['prev']).' 0 R');
		if(isset($o['next']))
			$this->_out('/Next '.($n+$o['next']).' 0 R');
		if(isset($o['first']))
			$this->_out('/First '.($n+$o['first']).' 0 R');
		if(isset($o['last']))
			$this->_out('/Last '.($n+$o['last']).' 0 R');


		// mPDF 3.0 Page orientation
		$h=$this->pageDim[$o['p']]['h'];

		$this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.3f null]',1+2*($o['p']),($h-$o['y'])*$this->k));

		$this->_out('/Count 0>>');
		$this->_out('endobj');
	}
	//Outline root
	$this->_newobj();
	$this->OutlineRoot=$this->n;
	$this->_out('<</Type /BMoutlines /First '.$n.' 0 R');
	$this->_out('/Last '.($n+$lru[0]).' 0 R>>');
	$this->_out('endobj');
}



//======================================================
// FROM class PDF_TOC

	var $_toc=array();
	var $TOCfont;
	var $TOCfontsize;
	var $TOCindent;
	var $TOCheader=false;
	var $TOCfooter=false;
	var $TOCpreHTML;
	var $TOCpostHTML;
	var $TOCbookmarkText;
	var $TOCusePaging;
	var $TOCuseLinking;
	var $TOC_margin_left;
	var $TOC_margin_right;
	var $TOC_margin_top;
	var $TOC_margin_bottom;
	var $TOC_margin_header;
	var $TOC_margin_footer;
	var $TOC_odd_header_name;
	var $TOC_even_header_name;
	var $TOC_odd_footer_name;
	var $TOC_even_footer_name;
	var $TOC_odd_header_value;
	var $TOC_even_header_value;
	var $TOC_odd_footer_value;
	var $TOC_even_footer_value;
	// mPDF 2.3
	var $m_TOC; 	// used for Multiple TOCs

// Edited mPDF 1.3 - DEPRACATED but included for backwards compatability
function startPageNums() {
}


// Initiate, and Mark a place for the Table of Contents to be inserted
function TOC($tocfont='', $tocfontsize=8, $tocindent=5, $resetpagenum='', $pagenumstyle='', $suppress='', $toc_orientation='', $TOCusePaging=true, $TOCuseLinking=false, $toc_id=0) {
		// mPDF 2.3
		if (strtoupper($toc_id)=='ALL') { $toc_id = '_mpdf_all'; }
		else if (!$toc_id) { $toc_id = 0; }
		else { $toc_id = strtolower($toc_id); }
		// To use odd and even pages
		// Cannot start table of contents on an even page
		if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
			if ($this->ColActive) {
				if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
			}
			$this->AddPage($this->CurOrientation,'',$resetpagenum, $pagenumstyle, $suppress);
		}
		else { 
			$this->PageNumSubstitutions[] = array('from'=>$this->page, 'reset'=> $resetpagenum, 'type'=>$pagenumstyle, 'suppress'=>$suppress);
		}
		if (!$tocfont) { $tocfont = $this->default_font; }
		if (!$tocfontsize) { $tocfontsize = $this->default_font_size; }
		// mPDF 2.3
		if ($toc_id) {
			$this->m_TOC[$toc_id]['TOCmark'] = $this->page; 
			$this->m_TOC[$toc_id]['TOCfont'] = $tocfont;
			$this->m_TOC[$toc_id]['TOCfontsize'] = $tocfontsize;
			$this->m_TOC[$toc_id]['TOCindent'] = $tocindent;
			$this->m_TOC[$toc_id]['TOCorientation'] = $toc_orientation;
			$this->m_TOC[$toc_id]['TOCuseLinking'] = $TOCuseLinking;
			$this->m_TOC[$toc_id]['TOCusePaging'] = $TOCusePaging;
		}
		else {
			$this->TOCmark = $this->page; 
			$this->TOCfont = $tocfont;
			$this->TOCfontsize = $tocfontsize;
			$this->TOCindent = $tocindent;
			// Added mPDF 2.0 - ToC paging/linking/orientation
			$this->TOCorientation = $toc_orientation;
			$this->TOCuseLinking = $TOCuseLinking;
			$this->TOCusePaging = $TOCusePaging;
		}
}

// mPDF 2.0 TOC and PAGEBREAK
// mPDF 2.2 tocfontsize default changed; indent, paging and links altered
function TOCpagebreak($tocfont='', $tocfontsize='', $tocindent='', $TOCusePaging=true, $TOCuseLinking='', $toc_orientation='', $toc_mgl='',$toc_mgr='',$toc_mgt='',$toc_mgb='',$toc_mgh='',$toc_mgf='',$toc_ohname='',$toc_ehname='',$toc_ofname='',$toc_efname='',$toc_ohvalue=0,$toc_ehvalue=0,$toc_ofvalue=0,$toc_efvalue=0, $toc_preHTML='', $toc_postHTML='', $toc_bookmarkText='', $resetpagenum='', $pagenumstyle='', $suppress='', $orientation='', $mgl='',$mgr='',$mgt='',$mgb='',$mgh='',$mgf='',$ohname='',$ehname='',$ofname='',$efname='',$ohvalue=0,$ehvalue=0,$ofvalue=0,$efvalue=0, $toc_id=0) {

		// mPDF 2.3
		if (strtoupper($toc_id)=='ALL') { $toc_id = '_mpdf_all'; }
		else if (!$toc_id) { $toc_id = 0; }
		else { $toc_id = strtolower($toc_id); }


		// mPDF 2.2 Use AddPage not AddPages
		// mPDF 2.3
		// mPDF 3.0
		//Start a new page
		if($this->state==0) $this->AddPage();
		if ($this->y == $this->tMargin && (!$this->useOddEven ||($this->useOddEven && $this->page % 2==1))) {
			// Don't add a page
			if ($this->page==1 && count($this->PageNumSubstitutions)==0) { 
				if (!$suppress) { $suppress = 'off'; }
				if (!$resetpagenum) { $resetpagenum= 1; }
				$this->PageNumSubstitutions[] = array('from'=>1, 'reset'=> $resetpagenum, 'type'=>$pagenumstyle, 'suppress'=> $suppress);
			}
		}
		else {
			$this->AddPage($orientation,'NEXT-ODD', $resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf,$ohname,$ehname,$ofname,$efname,$ohvalue,$ehvalue,$ofvalue,$efvalue); 
		}

		if (!$tocfont) { $tocfont = $this->default_font; }
		if (!$tocfontsize) { $tocfontsize = $this->default_font_size; }
		// mPDF 2.2
		if (!$tocindent && $tocindent !== 0) { $tocindent = 5; }
		// mPDF 3.0
		if ($TOCusePaging === false || strtolower($TOCusePaging) == "off" || $TOCusePaging === 0 || $TOCusePaging === "0" || $TOCusePaging === "") { $TOCusePaging = false; }
		else { $TOCusePaging = true; }
		if (!$TOCuseLinking) { $TOCuseLinking = false; }

		// mPDF 2.3
		if ($toc_id) {
			$this->m_TOC[$toc_id]['TOCmark'] = $this->page; 
			$this->m_TOC[$toc_id]['TOCfont'] = $tocfont;
			$this->m_TOC[$toc_id]['TOCfontsize'] = $tocfontsize;
			$this->m_TOC[$toc_id]['TOCindent'] = $tocindent;
			$this->m_TOC[$toc_id]['TOCorientation'] = $toc_orientation;
			$this->m_TOC[$toc_id]['TOCuseLinking'] = $TOCuseLinking;
			$this->m_TOC[$toc_id]['TOCusePaging'] = $TOCusePaging;

			if ($toc_preHTML) { $this->m_TOC[$toc_id]['TOCpreHTML'] = $toc_preHTML; }
			if ($toc_postHTML) { $this->m_TOC[$toc_id]['TOCpostHTML'] = $toc_postHTML; }
			if ($toc_bookmarkText) { $this->m_TOC[$toc_id]['TOCbookmarkText'] = $toc_bookmarkText; }

			$this->m_TOC[$toc_id]['TOC_margin_left'] = $toc_mgl;
			$this->m_TOC[$toc_id]['TOC_margin_right'] = $toc_mgr;
			$this->m_TOC[$toc_id]['TOC_margin_top'] = $toc_mgt;
			$this->m_TOC[$toc_id]['TOC_margin_bottom'] = $toc_mgb;
			$this->m_TOC[$toc_id]['TOC_margin_header'] = $toc_mgh;
			$this->m_TOC[$toc_id]['TOC_margin_footer'] = $toc_mgf;
			$this->m_TOC[$toc_id]['TOC_odd_header_name'] = $toc_ohname;
			$this->m_TOC[$toc_id]['TOC_even_header_name'] = $toc_ehname;
			$this->m_TOC[$toc_id]['TOC_odd_footer_name'] = $toc_ofname;
			$this->m_TOC[$toc_id]['TOC_even_footer_name'] = $toc_efname;
			$this->m_TOC[$toc_id]['TOC_odd_header_value'] = $toc_ohvalue;
			$this->m_TOC[$toc_id]['TOC_even_header_value'] = $toc_ehvalue;
			$this->m_TOC[$toc_id]['TOC_odd_footer_value'] = $toc_ofvalue;
			$this->m_TOC[$toc_id]['TOC_even_footer_value'] = $toc_efvalue;
		}
		else {
			$this->TOCmark = $this->page; 
			$this->TOCfont = $tocfont;
			$this->TOCfontsize = $tocfontsize;
			$this->TOCindent = $tocindent;
			$this->TOCorientation = $toc_orientation;
			// Added mPDF 2.0 - ToC paging/linking/orientation
			$this->TOCuseLinking = $TOCuseLinking;
			$this->TOCusePaging = $TOCusePaging;

			if ($toc_preHTML) { $this->TOCpreHTML = $toc_preHTML; }
			if ($toc_postHTML) { $this->TOCpostHTML = $toc_postHTML; }
			if ($toc_bookmarkText) { $this->TOCbookmarkText = $toc_bookmarkText; }

			$this->TOC_margin_left = $toc_mgl;
			$this->TOC_margin_right = $toc_mgr;
			$this->TOC_margin_top = $toc_mgt;
			$this->TOC_margin_bottom = $toc_mgb;
			$this->TOC_margin_header = $toc_mgh;
			$this->TOC_margin_footer = $toc_mgf;
			$this->TOC_odd_header_name = $toc_ohname;
			$this->TOC_even_header_name = $toc_ehname;
			$this->TOC_odd_footer_name = $toc_ofname;
			$this->TOC_even_footer_name = $toc_efname;
			$this->TOC_odd_header_value = $toc_ohvalue;
			$this->TOC_even_header_value = $toc_ehvalue;
			$this->TOC_odd_footer_value = $toc_ofvalue;
			$this->TOC_even_footer_value = $toc_efvalue;
		}
}

function TOC_Entry($txt, $level=0, $toc_id=0) {
		$txt = $this->purify_utf8_text($txt);
		if ($this->text_input_as_HTML) {
			$txt = $this->all_entities_to_utf8($txt);
		}
		if (!$this->is_MB) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }
		// Added mPDF 2.0 - ToC paging/linking
  		if ($this->ColActive) { $ily = $this->y0; } else { $ily = $this->y; }	// use top of columns
		$linkn = $this->AddLink(); 
		$this->SetLink($linkn,$ily,$this->page);
		// DIRECTIONALITY RTL
		// mPDF 3.0
   		if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->biDirectional)))  { 
	   		if ($this->rtlAsArabicFarsi || !preg_match("/[".$this->pregNonARABICchars ."]/u", $txt) ) {
				$txt = preg_replace("/([\x{0600}-\x{06FF}\x{0750}-\x{077F}]+)/ue", '$this->ArabJoin(stripslashes(\'\\1\'))', $txt);
	   		}
		}
		// mPDF 2.3
		if (strtoupper($toc_id)=='ALL') { $toc_id = '_mpdf_all'; }
		else if (!$toc_id) { $toc_id = 0; }
		else { $toc_id = strtolower($toc_id); }
		// Edited mPDF 1.1 Keep Block together
		if ($this->keep_block_together) {
			$this->_kttoc[]=array('t'=>$txt,'l'=>$level,'p'=>$this->page, 'link'=>$linkn, 'toc_id'=>$toc_id);
		}
		// mPDF 3.0
		else if ($this->table_rotate) {
			$this->tbrot_toc[]=array('t'=>$txt,'l'=>$level,'p'=>$this->page, 'link'=>$linkn, 'toc_id'=>$toc_id);
		}
		else if ($this->kwt) {
			$this->kwt_toc[]=array('t'=>$txt,'l'=>$level,'p'=>$this->page, 'link'=>$linkn, 'toc_id'=>$toc_id);
		}
		else if ($this->ColActive) {
			$this->col_toc[]=array('t'=>$txt,'l'=>$level,'p'=>$this->page, 'link'=>$linkn, 'toc_id'=>$toc_id);
		}
		else {
			$this->_toc[]=array('t'=>$txt,'l'=>$level,'p'=>$this->page, 'link'=>$linkn, 'toc_id'=>$toc_id);
		}
}

function insertTOC() {
	// mPDF 2.3
	$notocs = 0;
	if ($this->TOCmark) { $notocs = 1; }
	$notocs += count($this->m_TOC);

	if ($notocs==0) { return; }

	if (count($this->m_TOC)) { reset($this->m_TOC); }
	$added_toc_pages = 0;

	if ($this->ColActive) { $this->SetColumns(0); }
	if (($this->useOddEven) && (($this->page)%2==1)) {	// ODD
		$this->AddPage($this->CurOrientation);
		$extrapage = true;
	}
	else { $extrapage = false; }

	for ($toci = 0; $toci<$notocs; $toci++) {
		if ($toci==0 && $this->TOCmark) {
			$toc_id = 0;
			$toc_page = $this->TOCmark; 
			$tocfont = $this->TOCfont;
			$tocfontsize = $this->TOCfontsize;
			$tocindent = $this->TOCindent;
			$toc_orientation = $this->TOCorientation;
			$TOCuseLinking = $this->TOCuseLinking;
			$TOCusePaging = $this->TOCusePaging;
			$toc_preHTML = $this->TOCpreHTML;
			$toc_postHTML = $this->TOCpostHTML;
			$toc_bookmarkText = $this->TOCbookmarkText;
			$toc_mgl = $this->TOC_margin_left;
			$toc_mgr = $this->TOC_margin_right;
			$toc_mgt = $this->TOC_margin_top;
			$toc_mgb = $this->TOC_margin_bottom;
			$toc_mgh = $this->TOC_margin_header;
			$toc_mgf = $this->TOC_margin_footer;
			$toc_ohname = $this->TOC_odd_header_name;
			$toc_ehname = $this->TOC_even_header_name;
			$toc_ofname = $this->TOC_odd_footer_name;
			$toc_efname = $this->TOC_even_footer_name;
			$toc_ohvalue = $this->TOC_odd_header_value;
			$toc_ehvalue = $this->TOC_even_header_value;
			$toc_ofvalue = $this->TOC_odd_footer_value;
			$toc_efvalue = $this->TOC_even_footer_value;
		}
		else {
			$arr = current($this->m_TOC);

			$toc_id = key($this->m_TOC);
			$toc_page = $this->m_TOC[$toc_id]['TOCmark'];
			$tocfont = $this->m_TOC[$toc_id]['TOCfont'];
			$tocfontsize = $this->m_TOC[$toc_id]['TOCfontsize'];
			$tocindent = $this->m_TOC[$toc_id]['TOCindent'];
			$toc_orientation = $this->m_TOC[$toc_id]['TOCorientation'];
			$TOCuseLinking = $this->m_TOC[$toc_id]['TOCuseLinking'];
			$TOCusePaging = $this->m_TOC[$toc_id]['TOCusePaging'];
			$toc_preHTML = $this->m_TOC[$toc_id]['TOCpreHTML']; 
			$toc_postHTML = $this->m_TOC[$toc_id]['TOCpostHTML'];
			$toc_bookmarkText = $this->m_TOC[$toc_id]['TOCbookmarkText'];
			$toc_mgl = $this->m_TOC[$toc_id]['TOC_margin_left'];
			$toc_mgr = $this->m_TOC[$toc_id]['TOC_margin_right'];
			$toc_mgt = $this->m_TOC[$toc_id]['TOC_margin_top'];
			$toc_mgb = $this->m_TOC[$toc_id]['TOC_margin_bottom'];
			$toc_mgh = $this->m_TOC[$toc_id]['TOC_margin_header'];
			$toc_mgf = $this->m_TOC[$toc_id]['TOC_margin_footer'];
			$toc_ohname = $this->m_TOC[$toc_id]['TOC_odd_header_name'];
			$toc_ehname = $this->m_TOC[$toc_id]['TOC_even_header_name'];
			$toc_ofname = $this->m_TOC[$toc_id]['TOC_odd_footer_name'];
			$toc_efname = $this->m_TOC[$toc_id]['TOC_even_footer_name'];
			$toc_ohvalue = $this->m_TOC[$toc_id]['TOC_odd_header_value'];
			$toc_ehvalue = $this->m_TOC[$toc_id]['TOC_even_header_value'];
			$toc_ofvalue = $this->m_TOC[$toc_id]['TOC_odd_footer_value'];
			$toc_efvalue = $this->m_TOC[$toc_id]['TOC_even_footer_value'];

			next($this->m_TOC);
		}
		if ($this->TOCheader) { $this->setHeader($this->TOCheader); }
		else if ($this->TOCheader !== false) { $this->setHeader(); }

		// mPDF 3.0
		if (!$tocindent && $tocindent !== 0) { $tocindent = 5; }
		// Edited mPDF 1.3/1.4 - supress pagenumbers
		if (!$toc_orientation) { $toc_orientation= $this->DefOrientation; }
		// mPDF 3.0
		$this->AddPage($toc_orientation, '', '', '', "on", $toc_mgl, $toc_mgr, $toc_mgt, $toc_mgb, $toc_mgh, $toc_mgf, $toc_ohname, $toc_ehname, $toc_ofname, $toc_efname, $toc_ohvalue, $toc_ehvalue, $toc_ofvalue, $toc_efvalue);

		if ($this->TOCfooter) { $this->setFooter($this->TOCfooter); }
		else if ($this->TOCfooter !== false) { $this->setFooter(); }

		$tocstart=count($this->pages);
		if ($toc_preHTML) { $this->WriteHTML($toc_preHTML); }

		foreach($this->_toc as $t) {
		 if ($t['toc_id']==='_mpdf_all' || $t['toc_id']===$toc_id ) {
		   // mPDF 3.0
		   $tpgno = $this->docPageNum($t['p']);
		   $lineheightcorr = 2-$t['l'];
		   //Offset
		   $level=$t['l'];

		   if ($TOCuseLinking) { $tlink = $t['link']; }
		   else  { $tlink = ''; }

		   if ($this->directionality == 'rtl') {
			$weight='';
			if($level==0)
				$weight='B';
			$str=$t['t'];
			$this->SetFont($tocfont,$weight,$tocfontsize);
			$PageCellSize=$this->GetStringWidth($tpgno )+2;
			$strsize=$this->GetStringWidth($str);

			// mPDF 3.0 Cut to only one line
			$cw = count(explode(' ',$str));
			while (($strsize + $this->GetStringWidth(str_repeat('.',5))+4 + $PageCellSize) > $this->pgwidth && $cw>1) {
				$str = implode(' ',explode(' ',$str,-1));
				$strsize=$this->GetStringWidth($str);
				$cw = count(explode(' ',$str));
			}
			$sl = strlen($str);
			$rem = substr($t['t'], ($sl+1));

			// mPDF 3.0
			$this->magic_reverse_dir($str);

			// Added mPDF 2.0 - ToC paging/linking
			if ($TOCusePaging) {
				//Page number
				$this->SetFont($tocfont,'',$tocfontsize);
				$this->Cell($PageCellSize,$this->FontSize+$lineheightcorr,$tpgno ,0,0,'L',0,$tlink);

				//Filling dots
				$w=$this->w-$this->lMargin-$this->rMargin-$PageCellSize-($level*$tocindent)-($strsize+2);
				$nb=$w/$this->GetStringWidth('.');
				// Edited mPDF 2.0 Bug Fix
				if ($nb>0) { 
					$dots=str_repeat('.',$nb); 
					$this->Cell($w+2,$this->FontSize+$lineheightcorr,$dots,0,0,'L');
				}
				// Text
				$this->SetFont($tocfont,$weight,$tocfontsize);
				$this->Cell($strsize-($level*$tocindent),$this->FontSize+$lineheightcorr,$str,0,1,'R',0,$tlink);
			}
			else {
				// Text
				$this->SetFont($tocfont,$weight,$tocfontsize);
				$this->Cell($this->pgwidth -($level*$tocindent),$this->FontSize+$lineheightcorr,$str,0,1,'R',0,$tlink);
			}

			// mPDF 3.0
			if ($rem) {
				$this->x += 10;
				$this->MultiCell($this->pgwidth -($level*$tocindent)-15,$this->FontSize+$lineheightcorr,$rem,0,R,0,$tlink,'rtl'); 
			}
		   }
		   // LTR
		   else {
			if($level>0 && $tocindent) { $this->Cell($level*$tocindent,$this->FontSize+$lineheightcorr); }

			// Text
			$weight='';
			if($level==0)
				$weight='B';
			$str=$t['t'];
			$this->SetFont($tocfont,$weight,$tocfontsize);
			$PageCellSize=$this->GetStringWidth($tpgno )+2;
			$strsize=$this->GetStringWidth($str);

			// mPDF 3.0 Cut to only one line
			$cw = count(explode(' ',$str));
			while (($strsize + $this->GetStringWidth(str_repeat('.',5))+4+ $PageCellSize + ($level*$tocindent)) > $this->pgwidth && $cw>1) {
				$str = implode(' ',explode(' ',$str,-1));
				$strsize=$this->GetStringWidth($str);
				$cw = count(explode(' ',$str));
			}
			$sl = strlen($str);
			$rem = substr($t['t'], ($sl+1));

			// Added mPDF 2.0 - ToC paging/linking
			if ($TOCusePaging) {
				// Text
				$this->Cell($strsize+2,$this->FontSize+$lineheightcorr,$str,0,0,'',0,$tlink);

				//Filling dots
				$this->SetFont($tocfont,'',$tocfontsize);
				$w=$this->w-$this->lMargin-$this->rMargin-$PageCellSize-($level*$tocindent)-($strsize+2);
				// Edited mPDF 2.0 Bug Fix
				$nb=$w/$this->GetStringWidth('.');
				if ($nb>0) { $dots=str_repeat('.',$nb); }
				else { $this->y += $this->lineheight; $dots=str_repeat('.',5); }	// ..... 5 dots?
				$this->Cell($w,$this->FontSize+$lineheightcorr,$dots,0,0,'R');

				//Page number
				// Edited mPDF 2.0 change ToC paging
				$this->Cell($PageCellSize,$this->FontSize+$lineheightcorr,$tpgno ,0,1,'R',0,$tlink);
			}
			else {
				// Text only
				$this->Cell($strsize+2,$this->FontSize+$lineheightcorr,$str,0,1,'',0,$tlink);	// forces new line
			}
			// mPDF 3.0
			if ($rem) {
				$this->x +=  5 + $PageCellSize + ($level*$tocindent);
				$this->MultiCell($strsize+2,$this->FontSize+$lineheightcorr,$rem,0,L,0,$tlink,'ltr'); 
			}
		   }
		 } 
		}

		if ($toc_postHTML) { $this->WriteHTML($toc_postHTML); }
		$this->AddPage($toc_orientation,'E');

		$n_toc = $this->page - $tocstart + 1;

		if ($toci==0 && $this->TOCmark) {
			$this->TOC_start = $tocstart ;
			$this->TOC_end = $this->page;
			$this->TOC_npages = $n_toc;
		}
		else {
			$this->m_TOC[$toc_id]['start'] = $tocstart ;
			$this->m_TOC[$toc_id]['end'] = $this->page;
			$this->m_TOC[$toc_id]['npages'] = $n_toc;
		}
	}

	//Page footer
	$this->InFooter=true;
	$this->Footer();
	$this->InFooter=false;

	// 2nd time through to move pages etc.
	$added_toc_pages = 0;
	if (count($this->m_TOC)) { reset($this->m_TOC); }

	for ($toci = 0; $toci<$notocs; $toci++) {
		if ($toci==0 && $this->TOCmark) {
			$toc_id = 0;
			$toc_page = $this->TOCmark + $added_toc_pages; 
			$toc_orientation = $this->TOCorientation;
			$TOCuseLinking = $this->TOCuseLinking;
			$TOCusePaging = $this->TOCusePaging;
			$toc_bookmarkText = $this->TOCbookmarkText;

			$tocstart = $this->TOC_start ;
			$tocend = $n = $this->TOC_end;
			$n_toc = $this->TOC_npages;
		}
		else {
			$arr = current($this->m_TOC);

			$toc_id = key($this->m_TOC);
			$toc_page = $this->m_TOC[$toc_id]['TOCmark'] + $added_toc_pages;
			$toc_orientation = $this->m_TOC[$toc_id]['TOCorientation'];
			$TOCuseLinking = $this->m_TOC[$toc_id]['TOCuseLinking'];
			$TOCusePaging = $this->m_TOC[$toc_id]['TOCusePaging'];
			$toc_bookmarkText = $this->m_TOC[$toc_id]['TOCbookmarkText'];

			$tocstart = $this->m_TOC[$toc_id]['start'] ;
			$tocend = $n = $this->m_TOC[$toc_id]['end'] ;
			$n_toc = $this->m_TOC[$toc_id]['npages'] ;

			next($this->m_TOC);
		}

		// Now pages moved
		$added_toc_pages += $n_toc;

		$this->MovePages($toc_page, $tocstart, $tocend) ;

		// Insert new Bookmark for Bookmark
		if ($toc_bookmarkText) {
			$insert = -1;
			foreach($this->BMoutlines as $i=>$o) {
				if($o['p']<$toc_page) {	// i.e. before point of insertion
					$insert = $i;
				}
			}
			$txt = $this->purify_utf8_text($toc_bookmarkText);
			if ($this->text_input_as_HTML) {
				$txt = $this->all_entities_to_utf8($txt);
			}
			$newBookmark[0] = array('t'=>$txt,'l'=>0,'y'=>0,'p'=>$toc_page );	
			array_splice($this->BMoutlines,($insert+1),0,$newBookmark);
		}

	}

	// Delete empty page that was inserted earlier
	if ($extrapage) {
		unset($this->pages[count($this->pages)]);
		$this->page--;	// Reset page pointer
	}

}

//======================================================
function MovePages($target_page, $start_page, $end_page=-1) {
	// move a page/pages EARLIER in the document
		if ($end_page<1) { $end_page = $start_page; }
		$n_toc = $end_page - $start_page + 1;

		// Set/Update PageNumSubstitutions changes before moving anything
		if (count($this->PageNumSubstitutions)) {
			$tp_present = false;
			$sp_present = false;
			$ep_present = false;
			foreach($this->PageNumSubstitutions AS $k=>$v) {
			  if ($this->PageNumSubstitutions[$k]['from']==$target_page) {
				$tp_present = true;
				if ($this->PageNumSubstitutions[$k]['suppress']!='on' && $this->PageNumSubstitutions[$k]['suppress']!=1) { 
					$this->PageNumSubstitutions[$k]['suppress']='off';
				}
			  }
			  if ($this->PageNumSubstitutions[$k]['from']==$start_page) {
				$sp_present = true;
				if ($this->PageNumSubstitutions[$k]['suppress']!='on' && $this->PageNumSubstitutions[$k]['suppress']!=1) { 
					$this->PageNumSubstitutions[$k]['suppress']='off';
				}
			  }
			  if ($this->PageNumSubstitutions[$k]['from']==($end_page+1)) {
				$ep_present = true;
				if ($this->PageNumSubstitutions[$k]['suppress']!='on' && $this->PageNumSubstitutions[$k]['suppress']!=1) { 
					$this->PageNumSubstitutions[$k]['suppress']='off';
				}
			  }
			}

			if (!$tp_present) { 
				list($tp_type, $tp_suppress, $tp_reset) = $this->docPageSettings($target_page);
			}
			if (!$sp_present) { 
				list($sp_type, $sp_suppress, $sp_reset) = $this->docPageSettings($start_page);
			}
			if (!$ep_present) { 
				list($ep_type, $ep_suppress, $ep_reset) = $this->docPageSettings($start_page-1);
			}

		}

		$last = array();
		//store pages
		for($i = $start_page;$i <= $end_page ;$i++)
			$last[]=$this->pages[$i];
		//move pages
		for($i=$start_page - 1;$i>=$target_page-1;$i--) {
			$this->pages[$i+$n_toc]=$this->pages[$i];
		}
		//Put toc pages at insert point
		for($i = 0;$i < $n_toc;$i++) {
			$this->pages[$target_page + $i]=$last[$i];
		}

		// Update Bookmarks
		foreach($this->BMoutlines as $i=>$o) {
			if($o['p']>=$target_page) {
				$this->BMoutlines[$i]['p'] += $n_toc;
			}
		}

		// Update Page Links
		if (count($this->PageLinks)) {
		   $newarr = array();
		   foreach($this->PageLinks as $i=>$o) {
			// mPDF 3.0 - Change links to page numbers (@) used by Index
			foreach($this->PageLinks[$i] as $key => $pl) {
				if (strpos($pl[4],'@')===0) {
					$p=substr($pl[4],1);
					if($p>=$start_page && $p<=$end_page) {
						$this->PageLinks[$i][$key][4] = '@'.($p + ($target_page - $start_page));
					}
					else if($p>=$target_page && $p<$start_page) {
						$this->PageLinks[$i][$key][4] = '@'.($p+$n_toc);
					}
				}
			}
			// Edited mPDF 2.0 - move links in ToC itself
			if($i>=$start_page && $i<=$end_page) {
				$newarr[($i + ($target_page - $start_page))] = $this->PageLinks[$i];
			}
			else if($i>=$target_page && $i<$start_page) {
				$newarr[($i + $n_toc)] = $this->PageLinks[$i];
			}
			else {
				$newarr[$i] = $this->PageLinks[$i];
			}
		   }
		   $this->PageLinks = $newarr;
		}

		// OrientationChanges
		if (count($this->OrientationChanges)) {
			$newarr = array();
			foreach($this->OrientationChanges AS $p=>$v) {
				if($p>=$start_page && $p<=$end_page) { $newarr[($p + ($target_page - $start_page))] = $this->OrientationChanges[$p]; }
				else if($p>=$target_page && $p<$start_page) { $newarr[$p+$n_toc] = $this->OrientationChanges[$p]; }
				else { $newarr[$p] = $this->OrientationChanges[$p]; }
			}
			ksort($newarr);
			$this->OrientationChanges = $newarr;
		}

		// Page Dimensions
		if (count($this->pageDim)) {
			$newarr = array();
			foreach($this->pageDim AS $p=>$v) {
				if($p>=$start_page && $p<=$end_page) { $newarr[($p + ($target_page - $start_page))] = $this->pageDim[$p]; }
				else if($p>=$target_page && $p<$start_page) { $newarr[$p+$n_toc] = $this->pageDim[$p]; }
				else { $newarr[$p] = $this->pageDim[$p]; }
			}
			ksort($newarr);
			$this->pageDim = $newarr;
		}

		// Update Internal Links
		if (count($this->internallink)) {
		   foreach($this->internallink as $key=>$o) {
			// Edited mPDF 2.0 - move links in ToC itself
			if($o['PAGE']>=$start_page && $o['PAGE']<=$end_page) {
				$this->internallink[$key]['PAGE'] += ($target_page - $start_page);
			}
			else if($o['PAGE']>=$target_page && $o['PAGE']<$start_page) {
				$this->internallink[$key]['PAGE'] += $n_toc;
			}
		   }
		}

		// Update Links
		if (count($this->links)) {
		   foreach($this->links as $key=>$o) {
			// Edited mPDF 2.0 - move links in ToC itself
			if($o[0]>=$start_page && $o[0]<=$end_page) {
				$this->links[$key][0] += ($target_page - $start_page);
			}
			if($o[0]>=$target_page && $o[0]<$start_page) {
				$this->links[$key][0] += $n_toc;
			}
		   }
		}

		// Update Annotations
		if (count($this->PageAnnots)) {
		   $newarr = array();
		   foreach($this->PageAnnots as $p=>$anno) {
			if($p>=$start_page && $p<=$end_page) {
				$np = $p + ($target_page - $start_page);
				foreach($anno as $o) {
					$newarr[$np][] = $o;
				}
			}
			else if($p>=$target_page && $p<$start_page) {
				$np = $p + $n_toc;
				foreach($anno as $o) {
					$newarr[$np][] = $o;
				}
		      }
			else {
				$newarr[$p] = $this->PageAnnots[$p];
			}
		   }
		   $this->PageAnnots = $newarr;
		   unset($newarr);
		}

		// Update PageNumSubstitutions
		if (count($this->PageNumSubstitutions)) {
			$newarr = array();
			foreach($this->PageNumSubstitutions AS $k=>$v) {
				if($this->PageNumSubstitutions[$k]['from']>=$start_page && $this->PageNumSubstitutions[$k]['from']<=$end_page) { 
					$this->PageNumSubstitutions[$k]['from'] += ($target_page - $start_page); 
					$newarr[$this->PageNumSubstitutions[$k]['from']] = $this->PageNumSubstitutions[$k]; 
				}
				else if($this->PageNumSubstitutions[$k]['from']>=$target_page && $this->PageNumSubstitutions[$k]['from']<$start_page) {
					$this->PageNumSubstitutions[$k]['from'] += $n_toc;
					$newarr[$this->PageNumSubstitutions[$k]['from']] = $this->PageNumSubstitutions[$k]; 
				}
				else {
					$newarr[$this->PageNumSubstitutions[$k]['from']] = $this->PageNumSubstitutions[$k]; 
				}
			}

			if (!$sp_present) {
					$newarr[$target_page] = array('from'=>$target_page, 'suppress'=>$sp_suppress, 'reset'=>$sp_reset, 'type'=>$sp_type); 
			}
			if (!$tp_present) {
					$newarr[($target_page + $n_toc)] = array('from'=>($target_page+$n_toc), 'suppress'=>$tp_suppress, 'reset'=>$tp_reset, 'type'=>$tp_type); 
			}
			if (!$ep_present && $end_page>count($this->pages)) {
					$newarr[($end_page+1)] = array('from'=>($end_page+1), 'suppress'=>$ep_suppress, 'reset'=>$ep_reset, 'type'=>$ep_type); 
			}
			ksort($newarr);
			$this->PageNumSubstitutions = array();
			foreach($newarr as $v) {
				$this->PageNumSubstitutions[] = $v;
			}
		}
}




//======================================================
// FROM class PDF_Ref == INDEX
	var $ColActive=0;        //Flag indicating that columns are on (the index is being processed)
	var $ChangePage=0;       //Flag indicating that a page break has occurred
	var $Reference=array();  //Array containing the references
					// 
	var $CurrCol=0;              //Current column number
	var $NbCol;              //Total number of columns
	var $y0;                 //Top ordinate of columns
	var $ColL = array(0);	// Array of Left pos of columns - absolute - needs Margin correction for Odd-Even
	var $ColWidth;		// Column width
	var $ColGap=5;

// mPDF 2.2
function Reference($txt) {
	$this->IndexEntry($txt);
}

// mPDF 2.2
function IndexEntry($txt) {
	$txt = strip_tags($txt);
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if (!$this->is_MB) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }

	$Present=0;
	$size=sizeof($this->Reference);

	if ($this->directionality == 'rtl') {
		$txt = str_replace(':',' - ',$txt);
	}
	else {
		$txt = str_replace(':',', ',$txt);
	}


	//Search the reference (AND Ref/PageNo) in the array
	for ($i=0;$i<$size;$i++){
		if ($this->keep_block_together) {
			if ($this->ktReference[$i]['t']==$txt){
				$Present=1;
				if (!in_array($this->page,$this->ktReference[$i]['p'])) {
					$this->ktReference[$i]['op'] = $this->page;
				}
			}
		}
		// mPDF 3.0
		else if ($this->table_rotate) {
			if ($this->tbrot_Reference[$i]['t']==$txt){
				$Present=1;
				if (!in_array($this->page,$this->tbrot_Reference[$i]['p'])) {
					$this->tbrot_Reference[$i]['op'] = $this->page;
				}
			}
		}
		// mPDF 3.0
		else if ($this->kwt) {
			if ($this->kwt_Reference[$i]['t']==$txt){
				$Present=1;
				if (!in_array($this->page,$this->kwt_Reference[$i]['p'])) {
					$this->kwt_Reference[$i]['op'] = $this->page;
				}
			}
		}
		// mPDF 3.0
		else if ($this->ColActive) {
			if ($this->col_Reference[$i]['t']==$txt){
				$Present=1;
				if (!in_array($this->page,$this->col_Reference[$i]['p'])) {
					$this->col_Reference[$i]['op'] = $this->page;
				}
			}
		}
		else {
			if ($this->Reference[$i]['t']==$txt){
				$Present=1;
				if (!in_array($this->page,$this->Reference[$i]['p'])) {
					$this->Reference[$i]['p'][] = $this->page;
				}
			}
		}
	}
	//If not found, add it
	if ($Present==0) {
		// Edited mPDF 1.1 Keep Block together
		if ($this->keep_block_together) {
			$this->ktReference[]=array('t'=>$txt, 'op'=>$this->page);
		}
		// mPDF 3.0
		else if ($this->table_rotate) {
			$this->tbrot_Reference[]=array('t'=>$txt, 'op'=>$this->page);
		}
		else if ($this->kwt) {
			$this->kwt_Reference[]=array('t'=>$txt, 'op'=>$this->page);
		}
		else if ($this->ColActive) {
			$this->col_Reference[]=array('t'=>$txt, 'op'=>$this->page);
		}
		else {
			$this->Reference[]=array('t'=>$txt,'p'=>array($this->page));
		}
	}
}

// Added function to add a reference "Elephants. See Chickens"
// mPDF 2.2
function ReferenceSee($txta,$txtb) {
	$this->IndexEntrySee($txta,$txtb);
}

function IndexEntrySee($txta,$txtb) {
	$txta = strip_tags($txta);
	$txtb = strip_tags($txtb);
	$txta = $this->purify_utf8_text($txta);
	$txtb = $this->purify_utf8_text($txtb);
	if ($this->text_input_as_HTML) {
		$txta = $this->all_entities_to_utf8($txta);
		$txtb = $this->all_entities_to_utf8($txtb);
	}
	if (!$this->is_MB) { 
		$txta = mb_convert_encoding($txta,$this->mb_encoding,'UTF-8'); 
		$txtb = mb_convert_encoding($txtb,$this->mb_encoding,'UTF-8'); 
	}
	if ($this->directionality == 'rtl') {
		$txta = str_replace(':',' - ',$txta);
		$txtb = str_replace(':',' - ',$txtb);
	}
	else {
		$txta = str_replace(':',', ',$txta);
		$txtb = str_replace(':',', ',$txtb);
	}
	$this->Reference[]=array('t'=>$txta.' - see '.$txtb,'p'=>array());	// mPDF 3.0
}

// mPDF 2.2
function CreateReference($NbCol=1, $reffontsize='', $linespacing='', $offset=3, $usedivletters=1, $divlettfontsize='', $gap=5, $reffont='',$divlettfont='', $useLinking=false) {
	$this->CreateIndex($NbCol, $reffontsize, $linespacing, $offset, $usedivletters, $divlettfontsize, $gap, $reffont, $divlettfont, $useLinking);
}
// mPDF 2.2
function CreateIndex($NbCol=1, $reffontsize='', $linespacing='', $offset=3, $usedivletters=1, $divlettfontsize='', $gap=5, $reffont='',$divlettfont='', $useLinking=false) {
	if (!$reffontsize) { $reffontsize = $this->default_font_size; }
	if (!$divlettfontsize) { $divlettfontsize = ($this->default_font_size * 1.8); }
	if (!$reffont) { $reffont = $this->default_font; }
	if (!$divlettfont) { $divlettfont = $reffont; }
	if (!$linespacing) { $linespacing= $this->default_lineheight_correction; }	// mPDF 3.0
	if ($this->ColActive) { $this->SetColumns(0); }
	$size=sizeof($this->Reference);
	if ($size == 0) { return false; }


	// Edited mPDF 2.0
	if ($NbCol<2) { 
		$NbCol = 1; 
		$colWidth = $this->pgwidth;
	}
	else { 
		$this->SetColumns($NbCol,'',$gap); 
		$colWidth = $this->ColWidth;
	}
	if ($this->directionality == 'rtl') { $align = 'R'; }
	else { $align = 'L'; }
	$lett = '';
	function cmp ($a, $b) {
	    return strnatcmp(strtolower($a['t']), strtolower($b['t']));
	}
	//Alphabetic sort of the references
	usort($this->Reference, 'cmp');
	$size=sizeof($this->Reference);
	// mPDF 2.1
	$this->breakpoints[$this->CurrCol][] = $this->y; 

	// mPDF 3.0
	$this->OpenTag('DIV',array('STYLE'=>'line-height: '.$linespacing.'; font-family: '.$reffont.'; font-size: '.$reffontsize.'pt; '));

	// mPDF 3.0
	for ($i=0;$i<$size;$i++){
	   	if ($this->Reference[$i]['t']) { 
			if ($usedivletters) {
			   $lett = mb_substr($this->Reference[$i]['t'],0,1,$this->mb_encoding );
			   if ($lett != $last_lett) {
				if ($i>0) { 
					$this->OpenTag('DIV',array('STYLE'=>'line-height: '.$linespacing.'; font-family: '.$divlettfont.'; font-size: '.$divlettfontsize.'pt; font-weight: bold; page-break-after: avoid; margin-top: 1em; margin-collapse: collapse; '));
				}
				else { 
					$this->OpenTag('DIV',array('STYLE'=>'line-height: '.$linespacing.'; font-family: '.$divlettfont.'; font-size: '.$divlettfontsize.'pt; font-weight: bold; page-break-after: avoid; '));
				}
				$this->textbuffer[] = array($lett,'',$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
				$this->CloseTag('DIV');
			   }
			}

			$this->OpenTag('DIV',array('STYLE'=>'text-indent: -'.$offset.'mm; line-height: '.$linespacing.'; font-family: '.$reffont.'; font-size: '.$reffontsize.'pt; '));

			// mPDF 3.0 // Change Arabic + Persian. to Presentation Forms
   			if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->biDirectional)))  { 
			   	if ($this->rtlAsArabicFarsi || !preg_match("/[".$this->pregNonARABICchars ."]/u", $e) ) {
					$this->Reference[$i]['t'] = preg_replace("/([\x{0600}-\x{06FF}\x{0750}-\x{077F}]+)/ue", '$this->ArabJoin(stripslashes(\'\\1\'))', $this->Reference[$i]['t'] );
			   	}
			}

			$this->textbuffer[] = array($this->Reference[$i]['t'], '',$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
			$ppp = $this->Reference[$i]['p'];	// = array of page numbers to point to
			if (count($ppp)) { 
			 sort($ppp);
			 $newarr = array();
			 $range_start = $ppp[0];
			 $range_end = 0;

			 if ($this->is_MB) { $spacer = "\xc2\xa0 "; }
			 else { $spacer = chr(160).' '; }
			 $this->textbuffer[] = array($spacer, '',$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
			 if ($this->directionality == 'rtl') { $sep = '.'; $joiner = '-'; }
			 else { $sep = ', '; $joiner = '-'; }
			 for ($zi=1;$zi<count($ppp);$zi++) {
			  // RTL - Each number separately 
   			  if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->biDirectional)))  { 
				if ($zi<count($ppp)-1) {
					$txt =  $sep . $this->docPageNum($ppp[$zi]);
					if ($useLinking) { $href = '@'.$ppp[$zi]; } 
					else { $href = ''; }
					$this->textbuffer[] = array($txt, $href,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
				}
			  }

			  else if ($ppp[$zi] == ($ppp[($zi-1)]+1)) {
				$range_end = $ppp[$zi];
			  }
			  else {
				if ($range_end) {
					if ($range_end == $range_start+1) { 
						if ($useLinking) { $href = '@'.$range_start; } 
						else { $href = ''; }
						$txt = $this->docPageNum($range_start) . $sep;
						$this->textbuffer[] = array($txt, $href,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
						if ($useLinking) { $href = '@'.$ppp[$zi-1]; } 
						else { $href = ''; }
						$txt = $this->docPageNum($ppp[$zi-1]) . $sep;
						$this->textbuffer[] = array($txt, $href,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
					}
					else { 
						if ($useLinking) { $href = '@'.$range_start; } 
						else { $href = ''; }
					}
				}
				else {
					if ($useLinking) { $href = '@'.$ppp[$zi-1]; } 
					else { $href = ''; }
					$txt = $this->docPageNum($ppp[$zi-1]) . $sep;
					$this->textbuffer[] = array($txt, $href,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
				}
				$range_start = $ppp[$zi];
				$range_end = 0;
			  }
			 }

			 if ($range_end) {
				if ($range_end == $range_start+1) { 
					if ($useLinking) { $href = '@'.$range_start; } 
					else { $href = ''; }
					$txt = $this->docPageNum($range_start) . $sep;
					$this->textbuffer[] = array($txt, $href,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
					if ($useLinking) { $href = '@'.$range_end; } 
					else { $href = ''; }
					$txt = $this->docPageNum($range_end);
					$this->textbuffer[] = array($txt, $href,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
				}
				else {
					if ($useLinking) { $href = '@'.$range_start; } 
					else { $href = ''; }
					$txt = $this->docPageNum($range_start) . $joiner;
					$this->textbuffer[] = array($txt, $href,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
					if ($useLinking) { $href = '@'.$range_end; } 
					else { $href = ''; }
					$txt = $this->docPageNum($range_end);
					$this->textbuffer[] = array($txt, $href,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
				}
			 }
			 else {
				if ($useLinking) { $href = '@'.$ppp[(count($ppp)-1)]; } 
				else { $href = ''; }
				$txt = $this->docPageNum($ppp[(count($ppp)-1)]);
				$this->textbuffer[] = array($txt, $href,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
			 }
			}
		}
		$this->CloseTag('DIV');
		$this->breakpoints[$this->CurrCol][] = $this->y; 
		$last_lett = $lett;
	}
	$this->CloseTag('DIV');
	$this->breakpoints[$this->CurrCol][] = $this->y; 
	if ($this->ColActive) { $this->SetColumns(0);  }
}



//----------- COLUMNS ---------------------
	var $ColR = array(0);			// Array of Right pos of columns - absolute pos - needs Margin correction for Odd-Even
	var $ChangeColumn = 0;
	var $columnbuffer = array();
	var $ColDetails = array();		// Keeps track of some column details
	var $columnLinks = array();		// Cross references PageLinks
	var $colvAlign;				// Vertical alignment for columns

function SetColumns($NbCol,$vAlign='',$gap=5) {
// NbCol = number of columns
// CurrCol = Number of the current column starting at 0
// Called externally to set columns on/off and number
// Integer 2 upwards sets columns on to that number
// Anything less than 2 turns columns off
	if ($NbCol<2) {	// SET COLUMNS OFF
		if ($this->ColActive) { 
			$this->ColActive=0;
			if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
			$this->NbCol=1;
			$this->ResetMargins(); 
			$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
			$this->divwidth = 0;
			$this->Ln(); 
		}
		$this->ColActive=0;
		$this->columnbuffer = array();
		$this->ColDetails = array();
		$this->columnLinks = array();
		// mPDF 2.2 Annotations
		$this->columnAnnots = array();
		// mPDF 3.0
		$this->col_Reference = array();
		$this->col_BMoutlines = array();
		$this->col_toc = array();
		// mPDF 2.1
		$this->breakpoints = array();
	}
	else {	// SET COLUMNS ON
		// mPDF 2.5
		//if (($NbCol != $this->NbCol) && ($this->ColActive)) { 
		if ($this->ColActive) { 
			$this->ColActive=0;
			if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
			$this->ResetMargins(); 
		}
		$this->Ln();
		$this->NbCol=$NbCol;
		$this->ColGap = $gap;
		$this->divwidth = 0;
		$this->ColActive=1;
		$this->ColumnAdjust = true;	// enables column height adjustment for the page
		$this->columnbuffer = array();
		$this->ColDetails = array();
		$this->columnLinks = array();
		// mPDF 2.2 Annotations
		$this->columnAnnots = array();
		// mPDF 3.0
		$this->col_Reference = array();
		$this->col_BMoutlines = array();
		$this->col_toc = array();
		// mPDF 2.1
		$this->breakpoints = array();
		if ((strtoupper($vAlign) == 'J') || (strtoupper($vAlign) == 'JUSTIFY')) { $vAlign = 'J'; } 
		else { $vAlign = ''; }
		$this->colvAlign = $vAlign;
		//Save the ordinate
		$absL = $this->DeflMargin-($gap/2);
		$absR = $this->DefrMargin-($gap/2);
		$PageWidth = $this->w-$absL-$absR;	// virtual pagewidth for calculation only
		$ColWidth = (($PageWidth - ($gap * ($NbCol)))/$NbCol);
		$this->ColWidth = $ColWidth;
		if ($this->directionality == 'rtl') { 
			for ($i=0;$i<$this->NbCol;$i++) {
				$this->ColL[$i] = $absL + ($gap/2) + (($NbCol - ($i+1))*($PageWidth/$NbCol)) ;
				$this->ColR[$i] = $this->ColL[$i] + $ColWidth;	// NB This is not R margin -> R pos
			}
		} 
		else { 
			for ($i=0;$i<$this->NbCol;$i++) {
				$this->ColL[$i] = $absL + ($gap/2) + ($i* ($PageWidth/$NbCol)   );
				$this->ColR[$i] = $this->ColL[$i] + $ColWidth;	// NB This is not R margin -> R pos
			}
		}
		$this->pgwidth = $ColWidth;
		$this->SetCol(0);
		$this->y0=$this->GetY();
	}
	$this->x = $this->lMargin;
}

function SetCol($CurrCol) {
// Used internally to set column by number 0 is 1st column
	//Set position on a column
	$this->CurrCol=$CurrCol;
	$x = $this->ColL[$CurrCol];
	$xR = $this->ColR[$CurrCol];	// NB This is not R margin -> R pos
	if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
		$x += $this->MarginCorrection ;
		$xR += $this->MarginCorrection ;
	}
	$this->SetMargins($x,($this->w - $xR),$this->tMargin);
}

function AcceptPageBreak()
{
	if (count($this->cellBorderBuffer)) { $this->printcellbuffer(); }
	if ($this->ColActive==1) {
	    if($this->CurrCol<$this->NbCol-1) {
        	//Go to the next column
		$this->CurrCol++;
       	$this->SetCol($this->CurrCol);
		$this->y=$this->y0;
       	$this->ChangeColumn=1;	// Number (and direction) of columns changed +1, +2, -2 etc.
		//****************************//
		// DIRECTIONALITY RTL
		if ($this->directionality == 'rtl') { $this->ChangeColumn = -($this->ChangeColumn); }
		//****************************//
        	//Stay on the page
        	return false;
	   }
	   else {
    		//Go back to the first column - NEW PAGE
		if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
		$this->SetCol(0);
        	$this->ChangePage=1;
		$this->y0 = $this->tMargin;
        	$this->ChangeColumn= -($this->NbCol-1);
		//****************************//
		// DIRECTIONALITY RTL
		if ($this->directionality == 'rtl') { $this->ChangeColumn = -($this->ChangeColumn); }
		//****************************//
        	//Page break
       	return true;
	   }
	}
	else if ($this->table_rotate) {
		if (count($this->tablebuffer)) { $this->printtablebuffer(); }
		return true;
	}
	else {
        	$this->ChangeColumn=0;
		return true;
	}
	return true;
}

// mPDF 2.2 - Use this to call externally for a columnbreak
function AddColumn() {
	$this->NewColumn();
	$this->ColumnAdjust = false;	// disables all column height adjustment for the page.
}

// mPDF 2.2 NewColumn is reserved for calling internally, does not necessarily disable $this->ColumnAdjust 
function NewColumn() {
	if ($this->ColActive==1) {
	    if($this->CurrCol<$this->NbCol-1) {
        	//Go to the next column
		$this->CurrCol++;
        	$this->SetCol($this->CurrCol);
        	$this->y = $this->y0;
        	$this->ChangeColumn=1;
		// DIRECTIONALITY RTL
		if ($this->directionality == 'rtl') { $this->ChangeColumn = -($this->ChangeColumn); }
        	//Stay on the page
    		}
    		else {
    		//Go back to the first column
        	//Page break
		if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
		$this->AddPage($this->CurOrientation);
		$this->SetCol(0);
        	$this->ChangePage=1;
		$this->y0 = $this->tMargin;
        	$this->ChangeColumn= -($this->NbCol-1);
		// DIRECTIONALITY RTL
		if ($this->directionality == 'rtl') { $this->ChangeColumn = -($this->ChangeColumn); }
    		}
		$this->x = $this->lMargin;
	}
	else {
		$this->AddPage($this->CurOrientation);
	}
}

// mPDF 3.0 - Rewritten
function printcolumnbuffer() {
   $k = $this->k;
   // Columns ended (but page not ended) -> try to match all columns - unless disabled by using a custom column-break
   // mPDF 2.2 - variable name changed to lowercase first letter - keepColumns
   if (!$this->ColActive && $this->ColumnAdjust && !$this->keepColumns) {
	// Calculate adjustment to add to each column to calculate rel_y value
	$this->ColDetails[0]['add_y'] = 0;
	$last_col = 0;
	// Recursively add previous column's height
	for($i=1;$i<$this->NbCol;$i++) { 
		if ($this->ColDetails[$i]['bottom_margin']) { // If any entries in the column
			$this->ColDetails[$i]['add_y'] = ($this->ColDetails[$i-1]['bottom_margin'] - $this->y0) + $this->ColDetails[$i-1]['add_y'];
			$last_col = $i; 	// Last column actually printed
		}
	}

	// Calculate value for each position sensitive entry as though for one column
	foreach($this->columnbuffer AS $key=>$s) { 
		$t = $s['s'];
		if (preg_match('/BT \d+\.\d\d+ (\d+\.\d\d+) Td/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/\d+\.\d\d+ (\d+\.\d\d+) \d+\.\d\d+ [\-]{0,1}\d+\.\d\d+ re/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/\d+\.\d\d+ (\d+\.\d\d+) m/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/\d+\.\d\d+ (\d+\.\d\d+) l/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/q \d+\.\d\d+ 0 0 \d+\.\d\d+ \d+\.\d\d+ (\d+\.\d\d+) cm \/I\d+ Do Q/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/\d+\.\d\d+ (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ c/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
	}

	// mPDF 2.1
	$breaks = array();
	foreach($this->breakpoints AS $c => $bpa) { 
		foreach($bpa AS $rely) {
			$breaks[] = $rely + $this->ColDetails[$c]['add_y'] - $this->y0;
		}
	}

	$sum_h = $this->ColDetails[$last_col]['add_y'] + $this->ColDetails[$last_col]['bottom_margin'] - $this->y0;
	//$sum_h = max($this->ColDetails[$last_col]['add_y'] + $this->ColDetails[$last_col]['bottom_margin'] - $this->y0, end($breaks));
	$target_h = ($sum_h / $this->NbCol);

	$cbr = array();
	for($i=1;$i<$this->NbCol;$i++) { 
		$th = ($sum_h * $i / $this->NbCol);
		foreach($breaks AS $bk=>$val) {
			if ($val > $th) {
				if (($val-$th) < ($th-$breaks[$bk-1])) { $cbr[$i-1] = $val; }
				else  { $cbr[$i-1] = $breaks[$bk-1]; }
				break;
			}
		}
	}
	$cbr[($this->NbCol-1)] = $sum_h;

	// Now update the columns - divide into columns of approximately equal value
	$last_new_col = 0; 
	$yadj = 0;	// mm
	$xadj = 0;
	$last_col_bottom = 0;
	$lowest_bottom_y = 0;
	$block_bottom = 0;
	// mPDF 2.1
	$newcolumn = 0;
	foreach($this->columnbuffer AS $key=>$s) { 
	  if (isset($s['rel_y'])) {	// only process position sensitive data

		// mPDF 2.1
		if ($s['rel_y'] >= $cbr[$newcolumn]) {
			$newcolumn++;
		}
		else {
			$newcolumn = $last_new_col ;
		}


		$block_bottom = max($block_bottom,($s['rel_y']+$s['h']));

		if ($this->directionality == 'rtl') {
			$xadj = -(($newcolumn - $s['col']) * ($this->ColWidth + $this->ColGap));
		}
		else {
			$xadj = ($newcolumn - $s['col']) * ($this->ColWidth + $this->ColGap);
		}

		if ($last_new_col != $newcolumn) {	// Added new column
			$last_col_bottom = $this->columnbuffer[$key]['rel_y'];
			$block_bottom = 0;
		}
		$yadj = ($s['rel_y'] - $s['y']) - ($last_col_bottom)+$this->y0;
		// callback function in htmltoolkit
		$t = $s['s'];
		$t = preg_replace('/BT (\d+\.\d\d+) (\d+\.\d\d+) Td/e',"columnAdjustAdd('Td',$k,$xadj,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) ([\-]{0,1}\d+\.\d\d+) re/e',"columnAdjustAdd('re',$k,$xadj,$yadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) l/e',"columnAdjustAdd('l',$k,$xadj,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/q (\d+\.\d\d+) 0 0 (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) cm \/I/e',"columnAdjustAdd('img',$k,$xadj,$yadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) m/e',"columnAdjustAdd('draw',$k,$xadj,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) c/e',"columnAdjustAdd('bezier',$k,$xadj,$yadj,'\\1','\\2','\\3','\\4','\\5','\\6')",$t);

		$this->columnbuffer[$key]['s'] = $t;
		$this->columnbuffer[$key]['newcol'] = $newcolumn;
		$this->columnbuffer[$key]['newy'] = $s['y'] + $yadj;
		$last_new_col = $newcolumn;
		$clb = $s['y'] + $yadj + $s['h'] ;	// bottom_margin of current
		if ($clb > $this->ColDetails[$newcolumn]['max_bottom']) { $this->ColDetails[$newcolumn]['max_bottom'] = $clb; }
		if ($clb > $lowest_bottom_y) { $lowest_bottom_y = $clb; }
		// Adjust LINKS
		if (isset($this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])])) {
			$ref = $this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])];
			$this->PageLinks[$this->page][$ref][0] += ($xadj*$k);
			$this->PageLinks[$this->page][$ref][1] -= ($yadj*$k);
		}
		// mPDF 2.2 Annotations
		if (isset($this->columnAnnots[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])])) {
			$ref = $this->columnAnnots[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])];
			if ($this->PageAnnots[$this->page][$ref]['x'] < 0) {
				 $this->PageAnnots[$this->page][$ref]['x'] -= ($xadj);
			}
			else {
				 $this->PageAnnots[$this->page][$ref]['x'] += ($xadj);
			}
			$this->PageAnnots[$this->page][$ref]['y'] += ($yadj);	// unlike PageLinks, Page annots has y values from top in mm
		}
	  }
	}

	// mPDF 3.0
	// Adjust Bookmarks
	foreach($this->col_BMoutlines AS $v) {
		$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$this->y0,'p'=>$v['p']);
	}

	// mPDF 3.0
	// Adjust Reference (index)
	foreach($this->col_Reference AS $v) {
		$Present=0;
		//Search the reference (AND Ref/PageNo) in the array
		for ($i=0;$i<count($this->Reference);$i++){
			if ($this->Reference[$i]['t']==$v['t']){
				$Present=1;
				if (!in_array($v['op'],$this->Reference[$i]['p'])) {
					$this->Reference[$i]['p'][] = $v['op'];
				}
			}
		}
		if ($Present==0) {
			$this->Reference[]=array('t'=>$v['t'],'p'=>array($v['op']));
		}
	}

	 // mPDF 3.0
	 // Adjust ToC
	 foreach($this->col_toc AS $v) {
		$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$v['p'],'link'=>$v['link'],'toc_id'=>$v['toc_id']);
		$this->links[$v['link']][1] = $this->y0; 
	 }

	// Adjust column length to be equal
	if ($this->colvAlign == 'J') {
	 foreach($this->columnbuffer AS $key=>$s) { 
	   if (isset($s['rel_y'])) {	// only process position sensitive data
	    // Set ratio to expand y values or heights
	    if ($this->ColDetails[$s['newcol']]['max_bottom']) { 
		$ratio = ($lowest_bottom_y - ($this->y0)) / ($this->ColDetails[$s['newcol']]['max_bottom'] - ($this->y0));
	    }
	    else { $ratio = 1; }
	    if (($ratio > 1) && ($ratio <= $this->max_colH_correction)) {
		$yadj = ($s['newy'] - $this->y0) * ($ratio - 1);

		// Adjust LINKS
		if (isset($this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])])) {
			$ref = $this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])];
			$this->PageLinks[$this->page][$ref][1] -= ($yadj*$k);	// y value
			$this->PageLinks[$this->page][$ref][3] *= $ratio;	// height
		}
		// mPDF 2.2 Annotations
		// mPDF 2.2 Annotations
		if (isset($this->columnAnnots[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])])) {
			$ref = $this->columnAnnots[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])];
			$this->PageAnnots[$this->page][$ref]['y'] += ($yadj);
		}
	    }
	  }
	 }

	 $last_col = -1;
	 $trans_on = false;
	 foreach($this->columnbuffer AS $key=>$s) { 
		if (isset($s['rel_y'])) {	// only process position sensitive data
			// Set ratio to expand y values or heights
			if ($this->ColDetails[$s['newcol']]['max_bottom']) { 
				$ratio = ($lowest_bottom_y - ($this->y0)) / ($this->ColDetails[$s['newcol']]['max_bottom'] - ($this->y0));
			}
			else { $ratio = 1; }
			if (($ratio > 1) && ($ratio <= $this->max_colH_correction)) {
				//Start Transformation
				$this->pages[$this->page] .= $this->StartTransform(true)."\n";
				$this->pages[$this->page] .= $this->transformScale(100, $ratio*100, $x='', $this->y0, true)."\n";
				$trans_on = true;
			}
		}
		// Now output the adjusted values
		$this->pages[$this->page] .= $s['s']."\n"; 
		if (isset($s['rel_y']) && ($ratio > 1) && ($ratio <= $this->max_colH_correction)) {	// only process position sensitive data
			//Stop Transformation
			$this->pages[$this->page] .= $this->StopTransform(true)."\n";
		}
	 }
	}
	else {	// if NOT $this->colvAlign == 'J' 
		// Now output the adjusted values
		foreach($this->columnbuffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }
	}
	if ($lowest_bottom_y > 0) { $this->y = $lowest_bottom_y ; }
   }

   // Columns not ended but new page -> align columns (can leave the columns alone - just tidy up the height)
   else if ($this->colvAlign == 'J' && $this->ColumnAdjust && !$this->keepColumns)  {

	$k = $this->k;
	// calculate the lowest bottom margin
	$lowest_bottom_y = 0;
	foreach($this->columnbuffer AS $key=>$s) { 
	   // Only process output data
	   $t = $s['s'];
	   if ((preg_match('/BT \d+\.\d\d+ (\d+\.\d\d+) Td/',$t)) || (preg_match('/\d+\.\d\d+ (\d+\.\d\d+) \d+\.\d\d+ [\-]{0,1}\d+\.\d\d+ re/',$t)) ||
		(preg_match('/\d+\.\d\d+ (\d+\.\d\d+) l/',$t)) || 
		(preg_match('/q \d+\.\d\d+ 0 0 \d+\.\d\d+ \d+\.\d\d+ (\d+\.\d\d+) cm \/I\d+ Do Q/',$t)) || 
		(preg_match('/\d+\.\d\d+ (\d+\.\d\d+) m/',$t)) || 
		(preg_match('/\d+\.\d\d+ (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ c/',$t)) ) {

		$clb = $s['y'] + $s['h'];
		if ($clb > $this->ColDetails[$s['col']]['max_bottom']) { $this->ColDetails[$s['col']]['max_bottom'] = $clb; }
		if ($clb > $lowest_bottom_y) { $lowest_bottom_y = $clb; }
		$this->columnbuffer[$key]['rel_y'] = $s['y'];	// Marks position sensitive data to process later
	   }
	}
	// Adjust column length equal
	 foreach($this->columnbuffer AS $key=>$s) { 
	    // Set ratio to expand y values or heights
	    if ($this->ColDetails[$s['col']]['max_bottom']) { 
		$ratio = ($lowest_bottom_y - ($this->y0)) / ($this->ColDetails[$s['col']]['max_bottom'] - ($this->y0));
	    }
	    else { $ratio = 1; }
	    if (($ratio > 1) && ($ratio <= $this->max_colH_correction)) {
		$yadj = ($s['y'] - $this->y0) * ($ratio - 1);

		// Adjust LINKS
		if (isset($s['rel_y'])) {	// only process position sensitive data
		   // otherwise triggers for all entries in column buffer (.e.g. formatting) and makes below adjustments more than once
		   if (isset($this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])])) {
			$ref = $this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])];
			$this->PageLinks[$this->page][$ref][1] -= ($yadj*$k);	// y value
			$this->PageLinks[$this->page][$ref][3] *= $ratio;	// height
		   }
		   // mPDF 2.2 Annotations
		   if (isset($this->columnAnnots[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])])) {
			$ref = $this->columnAnnots[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])];
			$this->PageAnnots[$this->page][$ref]['y'] += ($yadj);
		   }
		}
	    }
	 }

	// mPDF 3.0
	// Adjust Bookmarks
	foreach($this->col_BMoutlines AS $v) {
		$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$this->y0,'p'=>$v['p']);
	}

	// mPDF 3.0
	// Adjust Reference (index)
	foreach($this->col_Reference AS $v) {
		$Present=0;
		//Search the reference (AND Ref/PageNo) in the array
		for ($i=0;$i<count($this->Reference);$i++){
			if ($this->Reference[$i]['t']==$v['t']){
				$Present=1;
				if (!in_array($v['op'],$this->Reference[$i]['p'])) {
					$this->Reference[$i]['p'][] = $v['op'];
				}
			}
		}
		if ($Present==0) {
			$this->Reference[]=array('t'=>$v['t'],'p'=>array($v['op']));
		}
	}

	 // mPDF 3.0
	 // Adjust ToC
	 foreach($this->col_toc AS $v) {
		$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$v['p'],'link'=>$v['link'],'toc_id'=>$v['toc_id']);
		$this->links[$v['link']][1] = $this->y0; 
	 }
	 foreach($this->columnbuffer AS $key=>$s) { 
		if (isset($s['rel_y'])) {	// only process position sensitive data
			// Set ratio to expand y values or heights
			if ($this->ColDetails[$s['col']]['max_bottom']) { 
				$ratio = ($lowest_bottom_y - ($this->y0)) / ($this->ColDetails[$s['col']]['max_bottom'] - ($this->y0));
			}
			else { $ratio = 1; }
			if (($ratio > 1) && ($ratio <= $this->max_colH_correction)) {
				//Start Transformation
				$this->pages[$this->page] .= $this->StartTransform(true)."\n";
				$this->pages[$this->page] .= $this->transformScale(100, $ratio*100, $x='', $this->y0, true)."\n";
			}
		}
		// Now output the adjusted values
		$this->pages[$this->page] .= $s['s']."\n"; 
		if (isset($s['rel_y']) && ($ratio > 1) && ($ratio <= $this->max_colH_correction)) {
			//Stop Transformation
			$this->pages[$this->page] .= $this->StopTransform(true)."\n";
		}
	 }

	// mPDF 2.2 Yes it was needed! Uncommented
	if ($lowest_bottom_y > 0) { $this->y = $lowest_bottom_y ; }	// Not needed? mPDF 2.2 
   }


   // Just reproduce the page as it was
   else {
	// If page has not ended but height adjustment was disabled by custom column-break - adjust y
	// mPDF 2.2 - variable name changed to lowercase first letter - keepColumns
	// mPDF 2.2
	$k = $this->k;
	$lowest_bottom_y = 0;
//	if ((!$this->ColumnAdjust && !$this->ColActive) || $this->keepColumns) {
	if (!$this->ColActive && (!$this->ColumnAdjust || $this->keepColumns)) {
		// calculate the lowest bottom margin
		foreach($this->columnbuffer AS $key=>$s) { 
		   // Only process output data
		   $t = $s['s'];
		   if ((preg_match('/BT \d+\.\d\d+ (\d+\.\d\d+) Td/',$t)) || (preg_match('/\d+\.\d\d+ (\d+\.\d\d+) \d+\.\d\d+ [\-]{0,1}\d+\.\d\d+ re/',$t)) ||
			(preg_match('/\d+\.\d\d+ (\d+\.\d\d+) l/',$t)) || 
			(preg_match('/q \d+\.\d\d+ 0 0 \d+\.\d\d+ \d+\.\d\d+ (\d+\.\d\d+) cm \/I\d+ Do Q/',$t)) || 
			(preg_match('/\d+\.\d\d+ (\d+\.\d\d+) m/',$t)) || 
			(preg_match('/\d+\.\d\d+ (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ c/',$t)) ) {

			$clb = $s['y'] + $s['h'];
			if ($clb > $this->ColDetails[$s['col']]['max_bottom']) { $this->ColDetails[$s['col']]['max_bottom'] = $clb; }
			if ($clb > $lowest_bottom_y) { $lowest_bottom_y = $clb; }
		   }
		}
	}
	foreach($this->columnbuffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }
	if ($lowest_bottom_y > 0) { $this->y = $lowest_bottom_y ; }	

	// mPDF 3.0
	// Output Reference (index)
	foreach($this->col_Reference AS $v) {
		$Present=0;
		for ($i=0;$i<count($this->Reference);$i++){
			if ($this->Reference[$i]['t']==$v['t']){
				$Present=1;
				if (!in_array($v['op'],$this->Reference[$i]['p'])) {
					$this->Reference[$i]['p'][] = $v['op'];
				}
			}
		}
		if ($Present==0) {
			$this->Reference[]=array('t'=>$v['t'],'p'=>array($v['op']));
		}
     }
      // Output Bookmarks
      foreach($this->col_BMoutlines AS $v) {
		$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$v['y'],'p'=>$v['p']);
      }
      // Output ToC
      foreach($this->col_toc AS $v) {
		$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$v['p'],'link'=>$v['link'],'toc_id'=>$v['toc_id']);
      }
   }
   $this->columnbuffer = array();
   $this->ColDetails = array();
   $this->columnLinks = array();
   // mPDF 2.2 Annotations
   $this->columnAnnots = array();
   // mPDF 3.0
   $this->col_Reference = array();
   $this->col_BMoutlines = array();
   $this->col_toc = array();
   // mPDF 2.1
   $this->breakpoints = array();
}


//==================================================================
function printcellbuffer() {

	if (count($this->cellBorderBuffer )) {
		usort( $this->cellBorderBuffer ,"_cmpdom"); 
		foreach($this->cellBorderBuffer AS $cbb) {
			$this->_tableRect($cbb['x'],$cbb['y'],$cbb['w'],$cbb['h'],$cbb['bord'],$cbb['details'], false, $cbb['borders_separate']);
		}
		$this->cellBorderBuffer = array();
	}
}
//==================================================================
function printtablebuffer() {

	if (!$this->table_rotate) { 
		foreach($this->tablebuffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }
		// Added mPDF 2.0
		foreach($this->tbrot_Links AS $p => $l) {
		   foreach($l AS $v) {
			$this->PageLinks[$p][] = $v;
		   }
		}
		$this->tbrot_Links = array();
		// mPDF 2.2 Annotations
		foreach($this->tbrot_Annots AS $p => $l) {
		   foreach($l AS $v) {
			$this->PageAnnots[$p][] = $v;
		   }
		}
		$this->tbrot_Annots = array();

		// mPDF 3.0
	      // Output Reference (index)
	      foreach($this->tbrot_Reference AS $v) {
			$Present=0;
			for ($i=0;$i<count($this->Reference);$i++){
				if ($this->Reference[$i]['t']==$v['t']){
					$Present=1;
					if (!in_array($v['op'],$this->Reference[$i]['p'])) {
						$this->Reference[$i]['p'][] = $v['op'];
					}
				}
			}
			if ($Present==0) {
				$this->Reference[]=array('t'=>$v['t'],'p'=>array($v['op']));
			}
	      }
		$this->tbrot_Reference = array();

	      // Output Bookmarks
	      foreach($this->tbrot_BMoutlines AS $v) {
			$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$v['y'],'p'=>$v['p']);
	      }
		$this->tbrot_BMoutlines = array();

	      // Output ToC
	      foreach($this->tbrot_toc AS $v) {
			$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$v['p'],'link'=>$v['link'],'toc_id'=>$v['toc_id']);
	      }
		$this->tbrot_toc = array();

		return; 
	}

	$lm = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'];
	$pw = $this->blk[$this->blklvl]['inner_width'];
	//Start Transformation
	$this->pages[$this->page] .= $this->StartTransform(true)."\n";

	if ($this->table_rotate > 1) {	// clockwise
	   if ($this->tbrot_align == 'L') {
		$xadj = $this->tbrot_h ;	// align L (as is)
	   }
	   else if ($this->tbrot_align == 'R') {
		$xadj = $lm-$this->tbrot_x0+($pw) ;	// align R
	   }
	   else {
		$xadj = $lm-$this->tbrot_x0+(($pw + $this->tbrot_h)/2) ;	// align C
	   }
	   $yadj = 0;
	}
	else {	// anti-clockwise
	   if ($this->tbrot_align == 'L') {
		$xadj = 0 ;	// align L (as is)
	   }
	   else if ($this->tbrot_align == 'R') {
		$xadj = $lm-$this->tbrot_x0+($pw - $this->tbrot_h) ;	// align R
	   }
	   else {
		$xadj = $lm-$this->tbrot_x0+(($pw - $this->tbrot_h)/2) ;	// align C
	   }
	   $yadj = $this->tbrot_w;
	}


	$this->pages[$this->page] .= $this->transformTranslate($xadj, $yadj , true)."\n";
	$this->pages[$this->page] .= $this->transformRotate($this->table_rotate, $this->tbrot_x0 , $this->tbrot_y0 , true)."\n";

	// Now output the adjusted values
	foreach($this->tablebuffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }


	// New mPDF 2.0 - Adjust hyperLinks
	foreach($this->tbrot_Links AS $p => $l) {
	    foreach($l AS $v) {
		$w = $v[2]/$this->k;
		$h = $v[3]/$this->k;
		$ax = ($v[0]/$this->k) - $this->tbrot_x0;
		$ay = (($this->hPt-$v[1])/$this->k) - $this->tbrot_y0;
		if ($this->table_rotate > 1) {	// clockwise
			$bx = $this->tbrot_x0+$xadj-$ay-$h;
			$by = $this->tbrot_y0+$yadj+$ax;
		}
		else {
			$bx = $this->tbrot_x0+$xadj+$ay;
			$by = $this->tbrot_y0+$yadj-$ax-$w;
		}
		$v[0] = $bx*$this->k;
		$v[1] = ($this->h-$by)*$this->k;
		$v[2] = $h*$this->k;	// swap width and height
		$v[3] = $w*$this->k;
		$this->PageLinks[$p][] = $v;
	    }
	}
	$this->tbrot_Links = array();
	// mPDF 2.2 Annotations
	foreach($this->tbrot_Annots AS $p => $l) {
	    foreach($l AS $v) {
		$w = $this->annotSize;
		$h = $this->annotSize;
		$ax = abs($v['x']) - $this->tbrot_x0;	// abs because -ve values are internally set and held for reference if annotMargin set
		$ay = $v['y'] - $this->tbrot_y0;
		if ($this->table_rotate > 1) {	// clockwise
			$bx = $this->tbrot_x0+$xadj-$ay-$h;
			$by = $this->tbrot_y0+$yadj+$ax;
		}
		else {
			$bx = $this->tbrot_x0+$xadj+$ay;
			$by = $this->tbrot_y0+$yadj-$ax-$w;
		}
		if ($v['x'] < 0) {
			$v['x'] = -$bx;
		}
		else {
			$v['x'] = $bx;
		}
		$v['y'] = ($by);
		$this->PageAnnots[$p][] = $v;
	    }
	}
	$this->tbrot_Annots = array();


	// mPDF 3.0
	// Adjust Bookmarks
	foreach($this->tbrot_BMoutlines AS $v) {
		$v['y'] = $this->tbrot_y0;
		$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$v['y'],'p'=>$this->page);
	}

	// mPDF 3.0
	// Adjust Reference (index)
	foreach($this->tbrot_Reference AS $v) {
		$Present=0;
		//Search the reference (AND Ref/PageNo) in the array
		for ($i=0;$i<count($this->Reference);$i++){
			if ($this->Reference[$i]['t']==$v['t']){
				$Present=1;
				if (!in_array($this->page,$this->Reference[$i]['p'])) {
					$this->Reference[$i]['p'][] = $this->page;
				}
			}
		}
		if ($Present==0) {
			$this->Reference[]=array('t'=>$v['t'],'p'=>array($this->page));
		}
	}

	// mPDF 3.0
	// Adjust ToC - uses document page number
	foreach($this->tbrot_toc AS $v) {
		$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$this->page,'link'=>$v['link'],'toc_id'=>$v['toc_id']);
		$this->links[$v['link']][1] = $this->tbrot_y0; 
	}


	// mPDF 3.0
	$this->tbrot_Reference = array();
	$this->tbrot_BMoutlines = array();
	$this->tbrot_toc = array();

	//Stop Transformation
	$this->pages[$this->page] .= $this->StopTransform(true)."\n";


	// Edited mPDF 2.0
	$this->y = $this->tbrot_y0 + $this->tbrot_w;
	$this->x = $this->lMargin;

	$this->tablebuffer = array();
}

//==================================================================
// mPDF 2.0 Keep-with-table This buffers contents of h1-6 to keep on page with table
function printkwtbuffer() {

	if (!$this->kwt_moved) { 
		foreach($this->kwt_buffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }
		// Added mPDF 2.0
		foreach($this->kwt_Links AS $p => $l) {
		   foreach($l AS $v) {
			$this->PageLinks[$p][] = $v;
		   }
		}
		$this->kwt_Links = array();
		// mPDF 2.2 Annotations
		foreach($this->kwt_Annots AS $p => $l) {
		   foreach($l AS $v) {
			$this->PageAnnots[$p][] = $v;
		   }
		}
		$this->kwt_Annots = array();

		// mPDF 3.0
	      // Output Reference (index)
	      foreach($this->kwt_Reference AS $v) {
			$Present=0;
			for ($i=0;$i<count($this->Reference);$i++){
				if ($this->Reference[$i]['t']==$v['t']){
					$Present=1;
					if (!in_array($v['op'],$this->Reference[$i]['p'])) {
						$this->Reference[$i]['p'][] = $v['op'];
					}
				}
			}
			if ($Present==0) {
				$this->Reference[]=array('t'=>$v['t'],'p'=>array($v['op']));
			}
	      }
		$this->kwt_Reference = array();

	      // Output Bookmarks
	      foreach($this->kwt_BMoutlines AS $v) {
			$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$v['y'],'p'=>$v['p']);
	      }
		$this->kwt_BMoutlines = array();

	      // Output ToC
	      foreach($this->kwt_toc AS $v) {
			$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$v['p'],'link'=>$v['link'],'toc_id'=>$v['toc_id']);
	      }
		$this->kwt_toc = array();

		return; 
	}

	//Start Transformation
	$this->pages[$this->page] .= $this->StartTransform(true)."\n";
	// mPDF 2.1
	//$xadj = $this->x - $this->kwt_x0 ;
	$xadj = $this->lMargin - $this->kwt_x0 ;
	//$yadj = $this->y - $this->kwt_y0 ;
	$yadj = $this->tMargin - $this->kwt_y0 ;

	$this->pages[$this->page] .= $this->transformTranslate($xadj, $yadj , true)."\n";

	// Now output the adjusted values
	foreach($this->kwt_buffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }

	// Adjust hyperLinks
	foreach($this->kwt_Links AS $p => $l) {
	    foreach($l AS $v) {
	//	$w = $v[2]/$this->k;
	//	$h = $v[3]/$this->k;
		$bx = $this->kwt_x0+$xadj;
		$by = $this->kwt_y0+$yadj;
		$v[0] = $bx*$this->k;
		$v[1] = ($this->h-$by)*$this->k;
	//	$v[2] = $w*$this->k;
	//	$v[3] = $h*$this->k;
		$this->PageLinks[$p][] = $v;
	    }
	}
	// mPDF 2.2 Annotations
	foreach($this->kwt_Annots AS $p => $l) {
	    foreach($l AS $v) {
		$w = $this->annotSize;
		$h = $this->annotSize;
		$bx = $this->kwt_x0+$xadj;
		$by = $this->kwt_y0+$yadj;
		if ($v['x'] < 0) {
			$v['x'] = -$bx;
		}
		else {
			$v['x'] = $bx;
		}
		$v['y'] = $by;
		$this->PageAnnots[$p][] = $v;
	    }
	}

	// mPDF 3.0
	// Adjust Bookmarks
	foreach($this->kwt_BMoutlines AS $v) {
		if ($v['y'] != 0) { $v['y'] += $yadj; }
		$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$v['y'],'p'=>$this->page);
	}

	// mPDF 3.0
	// Adjust Reference (index)
	foreach($this->kwt_Reference AS $v) {
		$Present=0;
		//Search the reference (AND Ref/PageNo) in the array
		for ($i=0;$i<count($this->Reference);$i++){
			if ($this->Reference[$i]['t']==$v['t']){
				$Present=1;
				if (!in_array($this->page,$this->Reference[$i]['p'])) {
					$this->Reference[$i]['p'][] = $this->page;
				}
			}
		}
		if ($Present==0) {
			$this->Reference[]=array('t'=>$v['t'],'p'=>array($this->page));
		}
	}

	// mPDF 3.0
	// Adjust ToC
	foreach($this->kwt_toc AS $v) {
		$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$this->page,'link'=>$v['link'],'toc_id'=>$v['toc_id']);
		$this->links[$v['link']][0] = $this->page;
		$this->links[$v['link']][1] += $yadj;
	}


	$this->kwt_Links = array();
	$this->kwt_Annots = array();
	// mPDF 3.0
	$this->kwt_Reference = array();
	$this->kwt_BMoutlines = array();
	$this->kwt_toc = array();
	//Stop Transformation
	$this->pages[$this->page] .= $this->StopTransform(true)."\n";

	$this->kwt_buffer = array();

	$this->y += $this->kwt_height;
}


//==================================================================
function printdivbuffer() {
	// Edited mPDF 1.1 keeping block together on one page
	$k = $this->k;
	$p1 = $this->blk[$this->blklvl]['startpage'];
	$p2 = $this->page;
	$bottom[$p1] = $this->ktBlock[$p1]['bottom_margin'];
	$bottom[$p2] = $this->y;	// $this->ktBlock[$p2]['bottom_margin'];
	$top[$p1] = $this->blk[$this->blklvl]['y00'];
	$top2 = $this->h;
	foreach($this->divbuffer AS $key=>$s) { 
		if ($s['page'] == $p2) {
			$top2 = MIN($s['y'], $top2);
		}
	}
	$top[$p2] = $top2;
	$height[$p1] = ($bottom[$p1] - $top[$p1]);
	$height[$p2] = ($bottom[$p2] - $top[$p2]);
	$xadj[$p1] = $this->MarginCorrection;
	$yadj[$p1] = -($top[$p1] - $top[$p2]);
	$xadj[$p2] = 0;
	$yadj[$p2] = $height[$p1];

	if ($this->ColActive || !$this->keep_block_together || $this->blk[$this->blklvl]['startpage'] == $this->page || ($this->page - $this->blk[$this->blklvl]['startpage']) > 1 || ($height[$p1]+$height[$p2]) > $this->h) { 
		foreach($this->divbuffer AS $s) { $this->pages[$s['page']] .= $s['s']."\n"; }
		foreach($this->ktLinks AS $p => $l) {
		   foreach($l AS $v) {
			$this->PageLinks[$p][] = $v;
		   }
		}
		// mPDF 2.2 Annotations
		foreach($this->ktAnnots AS $p => $l) {
		   foreach($l AS $v) {
			$this->PageAnnots[$p][] = $v;
		   }
		}
	      // Adjust Reference (index)
	      foreach($this->ktReference AS $v) {
			// mPDF 3.0
			$Present=0;
			//Search the reference (AND Ref/PageNo) in the array
			for ($i=0;$i<count($this->Reference);$i++){
				if ($this->Reference[$i]['t']==$v['t']){
					$Present=1;
					if (!in_array($p2,$this->Reference[$i]['p'])) {
						$this->Reference[$i]['p'][] = $p2;
					}
				}
			}
			//If not found, add it
			if ($Present==0) {
				$this->Reference[]=array('t'=>$v['t'],'p'=>array($p2));
			}
	      }

	      // Adjust Bookmarks
	      foreach($this->ktBMoutlines AS $v) {
			$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$v['y'],'p'=>$v['p']);
	      }

	      // Adjust ToC
		// mPDF 2.3
	      foreach($this->_kttoc AS $v) {
			$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$v['p'],'link'=>$v['link'],'toc_id'=>$v['toc_id']);
	      }

		$this->divbuffer = array();
		$this->ktLinks = array();
		// mPDF 2.2 Annotations
		$this->ktAnnots = array();
		$this->ktBlock = array();
		$this->ktReference = array();
		$this->ktBMoutlines = array();
		$this->_kttoc = array();
		$this->keep_block_together = 0;
		return; 
	}
	else {
	   foreach($this->divbuffer AS $key=>$s) { 
		// callback function in htmltoolkit
		$t = $s['s'];
		$p = $s['page'];
		$t = preg_replace('/BT (\d+\.\d\d+) (\d+\.\d\d+) Td/e',"blockAdjust('Td',$k,$xadj[$p],$yadj[$p],'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) ([\-]{0,1}\d+\.\d\d+) re/e',"blockAdjust('re',$k,$xadj[$p],$yadj[$p],'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) l/e',"blockAdjust('l',$k,$xadj[$p],$yadj[$p],'\\1','\\2')",$t);
		$t = preg_replace('/q (\d+\.\d\d+) 0 0 (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) cm \/I/e',"blockAdjust('img',$k,$xadj[$p],$yadj[$p],'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) m/e',"blockAdjust('draw',$k,$xadj[$p],$yadj[$p],'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) c/e',"blockAdjust('bezier',$k,$xadj[$p],$yadj[$p],'\\1','\\2','\\3','\\4','\\5','\\6')",$t);

		$this->pages[$this->page] .= $t."\n"; 
	   }
	   // Adjust hyperLinks
	   foreach($this->ktLinks AS $p => $l) {
	    foreach($l AS $v) {
		$v[0] += ($xadj[$p]*$k);
		$v[1] -= ($yadj[$p]*$k);
		$this->PageLinks[$p2][] = $v;
	    }
	   }
	   // mPDF 2.2 Annotations
	   foreach($this->ktAnnots AS $p => $l) {
	    foreach($l AS $v) {
		if ($v['x']>0) { $v['x'] += $xadj[$p]; }
		else if ($v['x']<0) { $v['x'] -= $xadj[$p]; }
		// mPDF 3.0 -= -> +=
		$v['y'] += $yadj[$p];
		$this->PageAnnots[$p2][] = $v;
	    }
	   }

	   // Adjust Bookmarks
	   foreach($this->ktBMoutlines AS $v) {
		if ($v['y'] != 0) { $v['y'] += ($yadj[$v['p']]); }
		$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$v['y'],'p'=>$p2);
	   }

	   // Adjust Reference (index)
	   foreach($this->ktReference AS $v) {
		// mPDF 3.0
		$Present=0;
		//Search the reference (AND Ref/PageNo) in the array
		for ($i=0;$i<count($this->Reference);$i++){
			if ($this->Reference[$i]['t']==$v['t']){
				$Present=1;
				if (!in_array($p2,$this->Reference[$i]['p'])) {
					$this->Reference[$i]['p'][] = $p2;
				}
			}
		}
		//If not found, add it
		if ($Present==0) {
			$this->Reference[]=array('t'=>$v['t'],'p'=>array($p2));
		}
	   }

	   // Adjust ToC
	   foreach($this->_kttoc AS $v) {
		$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$p2,'link'=>$v['link'],'toc_id'=>$v['toc_id']);
		// mPDF 3.0
		$this->links[$v['link']][0] = $p2;
		$this->links[$v['link']][1] += $yadj[$p];
	   }

	   $this->y = $top[$p2] + $height[$p1] + $height[$p2];
	   $this->x = $this->lMargin;

	   $this->divbuffer = array();
	   $this->ktLinks = array();
	   // mPDF 2.2 Annotations
	   $this->ktAnnots = array();
	   $this->ktBlock = array();
	   $this->ktReference = array();
	   $this->ktBMoutlines = array();
	   $this->_kttoc = array();
	   $this->keep_block_together = 0;
	}
}


//==================================================================
// Added ELLIPSES and CIRCLES
function Circle($x,$y,$r,$style='S') {
	$this->Ellipse($x,$y,$r,$r,$style);
}

function Ellipse($x,$y,$rx,$ry,$style='S') {
	if($style=='F') { $op='f'; }
	elseif($style=='FD' or $style=='DF') { $op='B'; }
	else { $op='S'; }
	$lx=4/3*(M_SQRT2-1)*$rx;
	$ly=4/3*(M_SQRT2-1)*$ry;
	$k=$this->k;
	$h=$this->h;
	$this->_out(sprintf('%.3f %.3f m %.3f %.3f %.3f %.3f %.3f %.3f c', ($x+$rx)*$k,($h-$y)*$k, ($x+$rx)*$k,($h-($y-$ly))*$k, ($x+$lx)*$k,($h-($y-$ry))*$k, $x*$k,($h-($y-$ry))*$k));
	$this->_out(sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c', ($x-$lx)*$k,($h-($y-$ry))*$k, 	($x-$rx)*$k,($h-($y-$ly))*$k, 	($x-$rx)*$k,($h-$y)*$k)); 
	$this->_out(sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c', ($x-$rx)*$k,($h-($y+$ly))*$k, ($x-$lx)*$k,($h-($y+$ry))*$k, $x*$k,($h-($y+$ry))*$k)); 
	$this->_out(sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c %s', ($x+$lx)*$k,($h-($y+$ry))*$k, ($x+$rx)*$k,($h-($y+$ly))*$k, ($x+$rx)*$k,($h-$y)*$k, $op));
}

// Added adaptation of shaded_box = AUTOSIZE-TEXT
// Label and number of invoice/estimate
function AutosizeText($text,$w,$font,$style,$szfont=72) {
	$text = $this->purify_utf8_text($text);
	if ($this->text_input_as_HTML) {
		$text = $this->all_entities_to_utf8($text);
	}
	if (!$this->is_MB) { $text = mb_convert_encoding($text,$this->mb_encoding,'UTF-8'); }
	$text = ' '.$text.' ';
	$width = ConvertSize($w);
	$loop   = 0;
	while ( $loop == 0 ) {
		$this->SetFont($font,$style,$szfont);
		$sz = $this->GetStringWidth( $text );
		if ( $sz > $w ) { $szfont --; }
		else { $loop ++; }
	}
 	$this->SetFont($font,$style,$szfont);
	$this->Cell($w, 0, $text, 0, 0, "C");
}





// ====================================================
// ====================================================
function reverse_letters($str) {
	return mb_strrev($str, $this->mb_encoding); 
}

function magic_reverse_dir(&$chunk, $join=true) {
   if (!$this->is_MB || $this->isCJK) { return 0; }
   // mPDF 2.2 - variable name changed to lowercase first letter
   if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->biDirectional)))  { 
	// mPDF 2.3
	// Change Arabic + Persian. to Presentation Forms
	if ($join) {
	   if ($this->rtlAsArabicFarsi || !preg_match("/[".$this->pregNonARABICchars ."]/u", $chunk) ) {
		$chunk = preg_replace("/([\x{0600}-\x{06FF}\x{0750}-\x{077F}]+)/ue", '$this->ArabJoin(stripslashes(\'\\1\'))', $chunk);
	   }
	}
	$contains_rtl = false;
	$all_rtl = true;
	if (preg_match("/[".$this->pregRTLchars."]/u",$chunk)) {	// Chunk contains RTL characters
		if (preg_match("/[^".$this->pregRTLchars."\x{A0}\"\'\(\). :\-]/u",$chunk)) {	// Chunk also contains LTR characters
			$all_rtl = false;
			$bits = preg_split('/[ ]/u',$chunk);
			foreach($bits AS $bitkey=>$bit) {
				if (preg_match("/[".$this->pregRTLchars."]/u",$bit)) {	// Chunk also contains LTR characters
					$bits[$bitkey] = $this->reverse_letters($bit); 
				}
				else { 
					$bits[$bitkey] = $bit; 
				}
			}
			$bits = array_reverse($bits,false);
			$chunk = implode(' ',$bits);
		}
		else {
			$chunk = $this->reverse_letters($chunk); 
		}
		$contains_rtl = true;
	}
	else { $all_rtl = false; }
	if ($all_rtl) { return 2; }
	else if ($contains_rtl) { return 1; }
	else { return 0; }
   }
   return 0;
}

//****************************//
//****************************//
//****************************//

var $subsearch = array();	// Array of search expressions to substitute characters
var $substitute = array();	// Array of substitution strings e.g. <ttz>112</ttz>
var $entsearch = array();	// Array of HTML entities (>ASCII 127) to substitute
var $entsubstitute = array();	// Array of substitution decimal unicode for the Hi entities


// mPDF 2.3 Capitalised
function SetSubstitutions($subsarr) {
	// mPDF 2.1
   if (!$this->useSubstitutions || count($subsarr) == 0) {
	$this->subsearch = array();
	$this->substitute = array();
   }
   else {
	foreach($subsarr AS $key => $val) {
		//$this->subsearch[] = '/'.preg_quote(code2utf($key),'/').'/u';
		//$this->substitute[] = $val;
		$this->substitute[code2utf($key)] = $val;
	}
    }
}


function SubstituteChars($html) {
	// only substitute characters between tags
	// mPDF 2.1
	if ($this->useSubstitutions && count($this->substitute)) {	// set in includes/pdf/config.php for VIEW, publish.php for PUBLISH
		// mPDF 2.1 Unnecessarily using /u modifier makes it slow
		$a=preg_split('/(<.*?>)/ms',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		$html = '';
		foreach($a as $i => $e) {
			if($i%2==0) {
			   //TEXT
			   //$e = preg_replace($this->subsearch, $this->substitute, $e);
			   $e = strtr($e, $this->substitute);
			}
			$html .= $e;
		}
	}
	return $html;
}


function setHiEntitySubstitutions($entarr) {
   if (count($entarr) == 0) {
	$this->entsearch = array();
	$this->entsubstitute = array();
   }
   else {
	foreach($entarr AS $key => $val) {
		$this->entsearch[] = '&'.$key.';';
		$this->entsubstitute[] = code2utf($val);
	}
    }
}

function SubstituteHiEntities($html) {
	// converts html_entities > ASCII 127 to unicode (defined in includes/pdf/config.php
	// Leaves in particular &lt; to distinguish from tag marker
	if (count($this->entsearch)) {
		$html = str_replace($this->entsearch,$this->entsubstitute,$html);
	}
	return $html;
}


// Edited v1.2 Pass by reference; option to continue if invalid UTF-8 chars
function is_utf8(&$string) {
	if ($string === mb_convert_encoding(mb_convert_encoding($string, "UTF-32", "UTF-8"), "UTF-8", "UTF-32")) {
		return true;
	} 
	else {
	  if ($this->ignore_invalid_utf8) {
		$string = mb_convert_encoding(mb_convert_encoding($string, "UTF-32", "UTF-8"), "UTF-8", "UTF-32") ;
		return true;
	  }
	  else {
		return false;
	  }
	}
} 


function purify_utf8($html,$lo=true) {
	// For HTML
	// Checks string is valid UTF-8 encoded
	// converts html_entities > ASCII 127 to UTF-8
	// Leaves in particular &lt; to distinguish from tag marker
	// Only exception - leaves low ASCII entities e.g. &lt; &amp; etc.
	if (!$this->is_utf8($html)) { $this->Error("HTML contains invalid UTF-8 character(s)"); }
	// mPDF 2.1 Unnecessary /u modifier slowed it down
	$html = preg_replace("/\r/", "", $html );

	// converts html_entities > ASCII 127 to UTF-8 
	// Leaves in particular &lt; to distinguish from tag marker
	$html = $this->SubstituteHiEntities($html);

	// converts all &#nnn; or &#xHHH; to UTF-8 multibyte
	// If $lo==true then includes ASCII < 128
	$html = strcode2utf($html,$lo);	

	// NON-BREAKING SPACE - convert to space as doesn't exist in CJK codepages (except japanese SJIS)
	if ($this->isCJK) {
		$html = preg_replace("/\xc2\xa0/"," ",$html);	// non-breaking space
	}

	return ($html);
}

function purify_utf8_text($txt) {
	// For TEXT
	// Make sure UTF-8 string of characters
	if (!$this->is_utf8($txt)) { $this->Error("Text contains invalid UTF-8 character(s)"); }

	// mPDF 2.1 Unnecessary /u modifier slowed it down
	$txt = preg_replace("/\r/", "", $txt );

	// NON-BREAKING SPACE - convert to space as doesn't exist in CJK codepages
	if ($this->isCJK) {
		$txt = preg_replace("/\xc2\xa0/"," ",$txt);	// non-breaking space
	}

	return ($txt);
}
function all_entities_to_utf8($txt) {
	// converts txt_entities > ASCII 127 to UTF-8 
	// Leaves in particular &lt; to distinguish from tag marker
	$txt = $this->SubstituteHiEntities($txt);

	// converts all &#nnn; or &#xHHH; to UTF-8 multibyte
	$txt = strcode2utf($txt);	


	$txt = lesser_entity_decode($txt);
	return ($txt);
}

// ====================================================
// Added mPDF 2.0
// Aadpted (with apologies) from TCPDF - only does EAN barcode
// EAN barcode
// Accepts 12 or 13 digits with or without - hyphens
function WriteBarcode($code, $showisbn=1, $x='', $y='', $size=1, $border=0, $paddingL=1, $paddingR=1, $paddingT=2, $paddingB=2, $height=1) {
			if (empty($code)) {
				return;
			}
			$isbn = $code;
			$code = preg_replace('/\-/','',$code);
			$arrcode = $this->getBarcodeArray($code);
			if ($arrcode === false) {
				$this->Error('Error in barcode string.');
			}
			if(strlen($code) == 12) {
				$code .= $arrcode['checkdigit'];
				if (stristr($isbn,'-')) { $isbn .= '-' . $arrcode['checkdigit']; }
				else { $isbn .= $arrcode['checkdigit']; }
			}
			$isbn = 'ISBN '.$isbn;
			if ($size>2 || $size < 0.8) {
				$this->Error('Barcode size must be between 0.8 and 2.0 (80% to 200%)');
			}

			if (empty($x)) {
				$x = $this->GetX();
			}
			if (empty($y)) {
				$y = $this->GetY();
			}
			// set foreground color
			$prevDrawColor = $this->DrawColor;
			$prevTextColor = $this->TextColor;
			$prevFillColor = $this->FillColor;
			$lw = $this->LineWidth;
			$this->SetLineWidth(0.01);
			$this->SetDrawColor(0);
			$this->SetTextColor(0);

			$xres = 0.33 * $size;
			$llm = 3.63 * $size;	// Left Light margin
			$rlm = 2.31 * $size;	// Right Light margin

			$tisbnm = 1.5 * $size;	// Top margin between isbn (if shown) & bars
			$isbn_fontsize = 2.1 * $size;

			$bcw = ($arrcode["maxw"] * $xres);	// Barcode width = Should always be 31.35mm * $size

			$fbw = $bcw + $llm + $rlm;	// Full barcode width incl. light margins
			$ow = $fbw + $paddingL + $paddingR;	// Full overall width incl. user-defined padding

			$fbwi = $fbw - 2;	// Full barcode width incl. light margins - 2mm - for isbn string

			// cf. http://www.gs1uk.org/downloads/bar_code/Bar coding getting it right.pdf
			$num_height = 3 * $size;			// Height of numerals
			$bch = (22.85 + 0.08 + 1.5) * $size * $height ;	// Barcode height of bars	 (3mm for numerals)
			$fbh = $bch + (1.5 * $size);			// Full barcode height incl. numerals $bch+1.5= 25.93mm (defined)
			if ($showisbn) { $paddingT += $isbn_fontsize + $tisbnm  ; }	// Add height for ISBN string + margin from top of bars
			$oh = $fbh + $paddingT + $paddingB;		// Full overall height incl. user-defined padding


			// print background color
			$this->SetFillColor(255);
			$xpos = $x;
			$ypos = $y;
			if ($border) { $fillb = 'DF'; } else { $fillb = 'F'; }
			$this->Rect($xpos, $ypos, $ow, $oh, $fillb);

			// print bars
			$xpos = $x + $paddingL + $llm ;
			$ypos = $y + $paddingT;
			$this->SetFillColor(0);
			if ($arrcode !== false) {
				foreach ($arrcode["bcode"] as $k => $v) {
					$bw = ($v["w"] * $xres);
					if ($v["t"]) {
						// draw a vertical bar
						$this->Rect($xpos, $ypos, $bw, $bch, 'F');
					}
					$xpos += $bw;
				}
			}


			// print text
			$prevFontFamily = $this->FontFamily;
			$prevFontStyle = $this->FontStyle;
			$prevFontSizePt = $this->FontSizePt;

			// ISBN string
			if ($showisbn) {
			   $this->SetFont('helvetica');
			   $this->SetFillColor(255);
			   $this->x = $x + $paddingL + 1;	// 1mm left margin (cf. $fbwi above)
			   // max width is $fbwi 
			   while ( $loop == 0 ) {
				$this->SetFontSize($isbn_fontsize*1.4*$this->k, false);	// don't write
				$sz = $this->GetStringWidth( $isbn );
				if ($sz > $fbwi)
					$isbn_fontsize -= 0.1;
				else
					$loop ++;
			   }
			   $this->SetFont('','',$isbn_fontsize*1.4*$this->k, true, true);	// * 1.4 because font height is only 7/10 of given mm

 			   // WORD SPACING
			   if ($fbwi > $sz) {
				$xtra =  $fbwi - $sz;
				$charspacing = $xtra / (strlen($isbn)-1);
				if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing*$this->k)); }
			   }

			   $this->y = $y + $paddingT - ($isbn_fontsize ) - $tisbnm ; 
			   $this->Cell($fbw , $isbn_fontsize, $isbn);
			   if ($charspacing) { $this->_out('BT 0 Tc ET'); }

			}

			if ($this->is_MB) {
				if (in_array('ocrb',$this->available_unifonts)) { $this->SetFont('ocrb'); }
				else { $this->SetFont('mono'); }
			}
			else {
				if (in_array('ocrb',$this->available_fonts)) { $this->SetFont('ocrb'); }
				else { $this->SetFont('mono'); }
			}

			if ($this->CurrentFont['desc']['Ascent']) { $fh = 1000/$this->CurrentFont['desc']['Ascent']; }
			else { $fh = 1.4; }

			$this->SetFontSize(3*$fh*$size*$this->k);	// 3mm numerals

			if ($this->is_MB) {
				$cw = $this->CurrentFont['cw'][32]*$this->FontSize/1000;	// character width
			}
			else {
				$cw = $this->CurrentFont['cw']['0']*$this->FontSize/1000;	// character width
			}
			$outerp = $xres * 4;
			$innerp = $xres * 2.5;
			$textw = ($bcw*0.5) - $outerp - $innerp;

 			// WORD SPACING
			$xtra =  $textw - ($cw*6);
			$charspacing = $xtra / 5;
			if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing*$this->k)); }

			$y_text = $y + $paddingT + $bch - ($num_height/2); 

			$this->x = $x + $paddingL;
			$this->y = $y_text; 
			$this->Cell($cw, $num_height, substr($code,0,1));

			$this->SetFillColor(255);
			$this->x = $x + $paddingL + $llm + $outerp;
			$this->y = $y_text; 
			$this->Cell($textw, $num_height, substr($code,1,6), 0, 0, '', 1);

			$this->SetFillColor(255);
			$this->x = $x + $paddingL + $llm + ($bcw*0.5) + $innerp;
			$this->y = $y_text; 
			$this->Cell($textw, $num_height, substr($code,7,6), 0, 0, '', 1);

			$this->x = $x + $paddingL + $llm + $bcw + $rlm - ($cw*0.6) ;	// squashed > by 60%
			$this->y = $y_text; 
			$this->_out('BT 60 Tz ET'); 	// squashed > by 60%
			$this->Cell(($cw*0.6), $num_height, '>');
			$this->_out('BT 100 Tz ET'); 


			// restore 
			if ($charspacing) { $this->_out('BT 0 Tc ET'); }
			$this->SetFont($prevFontFamily, $prevFontStyle, $prevFontSizePt);
			$this->DrawColor = $prevDrawColor;
			$this->TextColor = $prevTextColor;
			$this->FillColor = $prevFillColor;
			$this->SetLineWidth($lw);
			$this->SetY($y + $h);
}
		
function getBarcodeArray($code) {
		// add check digit
		if(strlen($code) == 12) {
			$sum=0;
			for($i=1;$i<=11;$i+=2) {
				$sum += (3 * substr($code,$i,1));
			}
			for($i=0; $i <= 10; $i+=2) {
				$sum += (substr($code,$i,1));
			}
			$r = $sum % 10;
			if($r > 0) {
				$r = (10 - $r);
			}
			$code .= $r;
			$checkdigit = $r;
		} else { // test checkdigit
			$sum = 0;
			for($i=1; $i <= 11; $i+=2) {
				$sum += (3 * substr($code,$i,1));
			}
			for($i=0; $i <= 10; $i+=2) {
				$sum += substr($code,$i,1);
			}
			if ((($sum + substr($code,12,1)) % 10) != 0) {
				return false;
			}
		}
		//Convert digits to bars
		$codes = array(
			'A'=>array(	'0'=>'0001101',	'1'=>'0011001',	'2'=>'0010011',	'3'=>'0111101',	'4'=>'0100011',
				'5'=>'0110001',		'6'=>'0101111',	'7'=>'0111011',	'8'=>'0110111',	'9'=>'0001011'),
			'B'=>array(	'0'=>'0100111',	'1'=>'0110011',	'2'=>'0011011',	'3'=>'0100001',	'4'=>'0011101',
				'5'=>'0111001',		'6'=>'0000101',	'7'=>'0010001',	'8'=>'0001001',	'9'=>'0010111'),
			'C'=>array(	'0'=>'1110010',	'1'=>'1100110',	'2'=>'1101100',	'3'=>'1000010',	'4'=>'1011100',
				'5'=>'1001110',		'6'=>'1010000',	'7'=>'1000100',	'8'=>'1001000',	'9'=>'1110100')
		);
		$parities = array(
			'0'=>array('A','A','A','A','A','A'),
			'1'=>array('A','A','B','A','B','B'),
			'2'=>array('A','A','B','B','A','B'),
			'3'=>array('A','A','B','B','B','A'),
			'4'=>array('A','B','A','A','B','B'),
			'5'=>array('A','B','B','A','A','B'),
			'6'=>array('A','B','B','B','A','A'),
			'7'=>array('A','B','A','B','A','B'),
			'8'=>array('A','B','A','B','B','A'),
			'9'=>array('A','B','B','A','B','A')
		);
		
		$bararray = array("code" => $code, "maxw" => 0, "maxh" => 1, "bcode" => array(), "checkdigit" => $checkdigit);
		$k = 0;
		$seq = '101';
		$p = $parities[substr($code,0,1)];
		for($i=1; $i < 7; $i++) {
			$seq .= $codes[$p[$i-1]][substr($code,$i,1)];
		}
		$seq .= '01010';
		for($i=7; $i < 13; $i++) {
			$seq .= $codes['C'][substr($code,$i,1)];
		}
		$seq .= '101';
		$len = strlen($seq);
		$w = 0;
		for($i=0; $i < $len; $i++) {
			$w += 1;
			if (($i == ($len - 1)) OR (($i < ($len - 1)) AND (substr($seq,$i,1) != substr($seq,($i+1),1)))) {
				if (substr($seq,$i,1) == '1') {
					$t = true; // bar
				} else {
					$t = false; // space
				}
				$bararray["bcode"][$k] = array("t" => $t, "w" => $w, "h" => 1, "p" => 0);
				$bararray["maxw"] += $w;
				$k++;
				$w = 0;
			}
		}
		return $bararray;
}

// ====================================================

		// START TRANSFORMATIONS SECTION -----------------------
		// authors: Moritz Wagner, Andreas Wurmser, Nicola Asuni
		// From TCPDF
		/**
		* Starts a 2D tranformation saving current graphic state.
		* This function must be called before scaling, mirroring, translation, rotation and skewing.
		* Use StartTransform() before, and StopTransform() after the transformations to restore the normal behavior.
		*/
		function StartTransform($returnstring=false) {
		  if ($returnstring) { return('q'); }
		  else { $this->_out('q'); }
		}
		
		function StopTransform($returnstring=false) {
		  if ($returnstring) { return('Q'); }
		  else { $this->_out('Q'); }
		}
		
		/**
		* Vertical and horizontal non-proportional Scaling.
		* @param float $s_x scaling factor for width as percent. 0 is not allowed.
		* @param float $s_y scaling factor for height as percent. 0 is not allowed.
		* @param int $x abscissa of the scaling center. Default is current x position
		* @param int $y ordinate of the scaling center. Default is current y position
		*/
		function transformScale($s_x, $s_y, $x='', $y='', $returnstring=false) {
			if ($x === '') {
				$x=$this->x;
			}
			if ($y === '') {
				$y=$this->y;
			}
			if (($s_x == 0) OR ($s_y == 0)) {
				$this->Error('Please do not use values equal to zero for scaling');
			}
			$y = ($this->h - $y) * $this->k;
			$x *= $this->k;
			//calculate elements of transformation matrix
			$s_x /= 100;
			$s_y /= 100;
			$tm[0] = $s_x;
			$tm[1] = 0;
			$tm[2] = 0;
			$tm[3] = $s_y;
			$tm[4] = $x * (1 - $s_x);
			$tm[5] = $y * (1 - $s_y);
			//scale the coordinate system
			if ($returnstring) { return($this->_transform($tm, true)); }
			else { $this->_transform($tm); }
		}
		
		
		/**
		* Translate graphic object horizontally and vertically.
		* @param int $t_x movement to the right
		* @param int $t_y movement to the bottom
		*/
		function transformTranslate($t_x, $t_y, $returnstring=false) {
			//calculate elements of transformation matrix
			$tm[0] = 1;
			$tm[1] = 0;
			$tm[2] = 0;
			$tm[3] = 1;
			$tm[4] = $t_x * $this->k;
			$tm[5] = -$t_y * $this->k;
			//translate the coordinate system
			if ($returnstring) { return($this->_transform($tm, true)); }
			else { $this->_transform($tm); }
		}
		
		/**
		* Rotate object.
		* @param float $angle angle in degrees for clockwise rotation
		* @param int $x abscissa of the rotation center. Default is current x position
		* @param int $y ordinate of the rotation center. Default is current y position
		*/
		function transformRotate($angle, $x='', $y='', $returnstring=false) {
			if ($x === '') {
				$x=$this->x;
			}
			if ($y === '') {
				$y=$this->y;
			}
			$angle = -$angle;
			$y = ($this->h - $y) * $this->k;
			$x *= $this->k;
			//calculate elements of transformation matrix
			$tm[0] = cos(deg2rad($angle));
			$tm[1] = sin(deg2rad($angle));
			$tm[2] = -$tm[1];
			$tm[3] = $tm[0];
			$tm[4] = $x + $tm[1] * $y - $tm[0] * $x;
			$tm[5] = $y - $tm[0] * $y - $tm[1] * $x;
			//rotate the coordinate system around ($x,$y)
			if ($returnstring) { return($this->_transform($tm, true)); }
			else { $this->_transform($tm); }
		}
		
		
		function _transform($tm, $returnstring=false) {
			if ($returnstring) { return(sprintf('%.3f %.3f %.3f %.3f %.3f %.3f cm', $tm[0], $tm[1], $tm[2], $tm[3], $tm[4], $tm[5])); }
			else { $this->_out(sprintf('%.3f %.3f %.3f %.3f %.3f %.3f cm', $tm[0], $tm[1], $tm[2], $tm[3], $tm[4], $tm[5])); }
		}
		
		// END TRANSFORMATIONS SECTION -------------------------


		
		


// ARABIC ===========================

// mPDF 2.3 
function InitArabic() {
	// Originals for Arabic
	//	$this->arabPrevLink = "\xd8\x8c\xd8\x9f\xd8\x9b\xd9\x80\xd8\xa6\xd8\xa8\xd8\xaa\xd8\xab\xd8\xac\xd8\xad\xd8\xae\xd8\xb3\xd8\xb4\xd8\xb5\xd8\xb6\xd8\xb7\xd8\xb8\xd8\xb9\xd8\xba\xd9\x81\xd9\x82\xd9\x83\xd9\x84\xd9\x85\xd9\x86\xd9\x87\xd9\x8a";
	//	$this->arabNextLink = "\xd9\x80\xd8\xa2\xd8\xa3\xd8\xa4\xd8\xa5\xd8\xa7\xd8\xa6\xd8\xa8\xd8\xa9\xd8\xaa\xd8\xab\xd8\xac\xd8\xad\xd8\xae\xd8\xaf\xd8\xb0\xd8\xb1\xd8\xb2\xd8\xb3\xd8\xb4\xd8\xb5\xd8\xb6\xd8\xb7\xd8\xb8\xd8\xb9\xd8\xba\xd9\x81\xd9\x82\xd9\x83\xd9\x84\xd9\x85\xd9\x86\xd9\x87\xd9\x88\xd9\x89\xd9\x8a";

	// With Persian chars added to both next and prev?
	// &#x06a9;&#x06cc;&#x0686;&#x06af;&#x0698;&#x067e;
	// \xda\xa9\xdb\x8c\xda\x86\xda\xaf\xda\x98\xd9\xbe
	$this->arabPrevLink = "\xd8\x8c\xd8\x9f\xd8\x9b\xd9\x80\xd8\xa6\xd8\xa8\xd8\xaa\xd8\xab\xd8\xac\xd8\xad\xd8\xae\xd8\xb3\xd8\xb4\xd8\xb5\xd8\xb6\xd8\xb7\xd8\xb8\xd8\xb9\xd8\xba\xd9\x81\xd9\x82\xd9\x83\xd9\x84\xd9\x85\xd9\x86\xd9\x87\xd9\x8a\xda\xa9\xdb\x8c\xda\x86\xda\xaf\xda\x98\xd9\xbe";
	$this->arabNextLink = "\xd9\x80\xd8\xa2\xd8\xa3\xd8\xa4\xd8\xa5\xd8\xa7\xd8\xa6\xd8\xa8\xd8\xa9\xd8\xaa\xd8\xab\xd8\xac\xd8\xad\xd8\xae\xd8\xaf\xd8\xb0\xd8\xb1\xd8\xb2\xd8\xb3\xd8\xb4\xd8\xb5\xd8\xb6\xd8\xb7\xd8\xb8\xd8\xb9\xd8\xba\xd9\x81\xd9\x82\xd9\x83\xd9\x84\xd9\x85\xd9\x86\xd9\x87\xd9\x88\xd9\x89\xd9\x8a\xda\xa9\xdb\x8c\xda\x86\xda\xaf\xda\x98\xd9\xbe";

	$this->arabVowels = "\xd9\x8b\xd9\x8c\xd9\x8d\xd9\x8e\xd9\x8f\xd9\x90\xd9\x91\xd9\x92";
	$this->arabGlyphs = "\xd9\x8b\xd9\x8c\xd9\x8d\xd9\x8e\xd9\x8f\xd9\x90\xd9\x91\xd9\x92";
	$this->arabHex = '064B064B064B064B064C064C064C064C064D064D064D064D064E064E064E064E064F064F064F064F065006500650065006510651065106510652065206520652';
	$this->arabGlyphs .= "\xd8\xa1\xd8\xa2\xd8\xa3\xd8\xa4\xd8\xa5\xd8\xa6\xd8\xa7\xd8\xa8";
	$this->arabHex .= 'FE80FE80FE80FE80FE81FE82FE81FE82FE83FE84FE83FE84FE85FE86FE85FE86FE87FE88FE87FE88FE89FE8AFE8BFE8CFE8DFE8EFE8DFE8EFE8FFE90FE91FE92';
	$this->arabGlyphs .= "\xd8\xa9\xd8\xaa\xd8\xab\xd8\xac\xd8\xad\xd8\xae\xd8\xaf\xd8\xb0";
	$this->arabHex .= 'FE93FE94FE93FE94FE95FE96FE97FE98FE99FE9AFE9BFE9CFE9DFE9EFE9FFEA0FEA1FEA2FEA3FEA4FEA5FEA6FEA7FEA8FEA9FEAAFEA9FEAAFEABFEACFEABFEAC';
	$this->arabGlyphs .= "\xd8\xb1\xd8\xb2\xd8\xb3\xd8\xb4\xd8\xb5\xd8\xb6\xd8\xb7\xd8\xb8";
	$this->arabHex .= 'FEADFEAEFEADFEAEFEAFFEB0FEAFFEB0FEB1FEB2FEB3FEB4FEB5FEB6FEB7FEB8FEB9FEBAFEBBFEBCFEBDFEBEFEBFFEC0FEC1FEC2FEC3FEC4FEC5FEC6FEC7FEC8';
	$this->arabGlyphs .= "\xd8\xb9\xd8\xba\xd9\x81\xd9\x82\xd9\x83\xd9\x84\xd9\x85\xd9\x86";
	$this->arabHex .= 'FEC9FECAFECBFECCFECDFECEFECFFED0FED1FED2FED3FED4FED5FED6FED7FED8FED9FEDAFEDBFEDCFEDDFEDEFEDFFEE0FEE1FEE2FEE3FEE4FEE5FEE6FEE7FEE8';
	$this->arabGlyphs .= "\xd9\x87\xd9\x88\xd9\x89\xd9\x8a\xd9\x80\xd8\x8c\xd8\x9f\xd8\x9b";
	$this->arabHex .= 'FEE9FEEAFEEBFEECFEEDFEEEFEEDFEEEFEEFFEF0FEEFFEF0FEF1FEF2FEF3FEF40640064006400640060C060C060C060C061F061F061F061F061B061B061B061B';
	$this->arabGlyphs .= "\xd9\x84\xd8\xa2\xd9\x84\xd8\xa3\xd9\x84\xd8\xa5\xd9\x84\xd8\xa7";
	$this->arabHex .= 'FEF5FEF6FEF5FEF6FEF7FEF8FEF7FEF8FEF9FEFAFEF9FEFAFEFBFEFCFEFBFEFC';

	// Added Arabic Presentation Forms - A - for Persian - based on preentation characters in DejaVusans font
	// FB52 - FB81, FB8A - FB95, FB9E & FB9F, FBFC-FBFF are in Dejavusans
	$this->persianGlyphs = "\xd9\xbb\xd9\xbe\xda\x80\xd9\xba";
	$this->persianHex = 'FB52FB53FB54FB55FB56FB57FB58FB59FB5AFB5BFB5CFB5DFB5EFB5FFB60FB61';
	$this->persianGlyphs .= "\xd9\xbf\xd9\xb9\xda\xa4\xda\xa6";
	$this->persianHex .= 'FB62FB63FB64FB65FB66FB67FB68FB69FB6AFB6BFB6CFB6DFB6EFB6FFB70FB71';
	$this->persianGlyphs .= "\xda\x84\xda\x83\xda\x86\xda\x87";
	$this->persianHex .= 'FB72FB73FB74FB75FB76FB77FB78FB79FB7AFB7BFB7CFB7DFB7EFB7FFB80FB81';
	$this->persianGlyphs .= "\xda\x98\xda\x91\xda\xa9\xda\xaf";
	$this->persianHex .= 'FB8AFB8BFB8AFB8BFB8CFB8DFB8CFB8DFB8EFB8FFB90FB91FB92FB93FB94FB95';
	$this->persianGlyphs .= "\xda\xba\xdb\x8c";
	$this->persianHex .= 'FB9EFB9FFB9EFB9FFBFCFBFDFBFEFBFF';
}
		
// mPDF 2.3 Changes arabic text to presentation forms
  // ----------------------------------------------------------------------
  // Derived from ArPHP
  // by Khaled Al-Shamaa
  // http://www.ar-php.org
  // ----------------------------------------------------------------------
function ArabJoin($str) {
	if (!$this->arabGlyphs) { $this->InitArabic(); }
	$crntChar = null;
	$prevChar = null;
	$nextChar = null;
	$output = array();
	$chars = preg_split('//u', $str);
	$max = count($chars);
	for ($i = $max - 1; $i >= 0; $i--) {
		$crntChar = $chars[$i];
		if ($i > 0){ $prevChar = $chars[$i - 1]; }
		else{ $prevChar = NULL; }
		if ($prevChar && mb_strpos($this->arabVowels, $prevChar, 0, 'utf-8') !== false) {
			$prevChar = $chars[$i - 2];
			if ($prevChar && mb_strpos($this->arabVowels, $prevChar, 0, 'utf-8') !== false) {
				$prevChar = $chars[$i - 3];
			}
		}
		if ($crntChar && mb_strpos($this->arabVowels, $crntChar, 0, 'utf-8') !== false) {
			// If next_char = nextLink && prev_char = prevLink:
			if ($chars[$i + 1] && (mb_strpos($this->arabNextLink, $chars[$i + 1], 0, 'utf-8') !== false)  && (mb_strpos($this->arabPrevLink, $prevChar, 0, 'utf-8') !== false)) {
				$output[] = '&#x' . $this->get_arab_glyphs($crntChar, 1) . ';';	// <final> form
			} 
			else {
				$output[] = '&#x' . $this->get_arab_glyphs($crntChar, 0) . ';';  // <isolated> form
			}
			continue;
		}
		if ($chars[$i + 1] && in_array($chars[$i + 1], array("\xd8\xa2","\xd8\xa3","\xd8\xa5","\xd8\xa7")) && $crntChar == "\xd9\x84"){
			continue;
		}
		if (ord($crntChar) < 128) {
			$output[] = $crntChar;
			$nextChar = $crntChar;
			continue;
		}
		$form = 0;
		if (in_array($crntChar, array("\xd8\xa2", "\xd8\xa3", "\xd8\xa5", "\xd8\xa7")) && $prevChar == "\xd9\x84") {
			if ($chars[$i - 2] && mb_strpos($this->arabPrevLink, $chars[$i - 2], 0, 'utf-8') !== false) {
				$form++;	// <final> form
			}
			$output[] = '&#x' . $this->get_arab_glyphs($prevChar . $crntChar, $form) . ';';
			$nextChar = $prevChar;
			continue;
		}
		if ($prevChar && mb_strpos($this->arabPrevLink, $prevChar, 0, 'utf-8') !== false) {
			$form++;
		}
		if ($nextChar && mb_strpos($this->arabNextLink, $nextChar, 0, 'utf-8') !== false) {
			$form += 2;
		}
		$output[] = '&#x' . $this->get_arab_glyphs($crntChar, $form) . ';';
		$nextChar = $crntChar;
	}
	$ra = array_reverse($output);
	$s = implode($ra);	
	$s = strcode2utf($s);
	return $s;
}

function get_arab_glyphs($char, $type) {
	$pos = mb_strpos($this->arabGlyphs, $char, 0, 'utf-8');
	if ($pos === false) { 	// If character not covered here
	  $pos = mb_strpos($this->persianGlyphs, $char, 0, 'utf-8'); 	// Try Persian additions
	  if ($pos === false) { 	// return original character
		$x = mb_encode_numericentity ($char, array (0x0, 0xffff, 0, 0xffff), 'UTF-8');
		preg_match('/&#(\d+);/', $x, $m);
		return dechex($m[1]);
	  }
	  else {	// if covered by Added Persian chars
		$pos = $pos*16 + $type*4;
		return mb_substr($this->persianHex, $pos, 4, 'utf-8');
	  }
	}
	if ($pos > 48){
		$pos = ($pos-48)/2 + 48;
	}
	$pos = $pos*16 + $type*4;
	return mb_substr($this->arabHex, $pos, 4, 'utf-8');
}

//===========================


// mPDF 2.3
function replaceCJK($str) {
	if (preg_match("/[".$this->pregUHCchars."]/u", $str)) { 
		return '##lthtmltag##span lang="ko"##gthtmltag##' . $str .'##lthtmltag##/span##gthtmltag##';
	}
	else if (preg_match("/[".$this->pregSJISchars."]/u", $str)) { 
		return '##lthtmltag##span lang="ja"##gthtmltag##' . $str .'##lthtmltag##/span##gthtmltag##';
	}
	// mPDF 3.0  if in Unicode Plane 2, probably HKCS (incl in BIG5) if not Japanese
	else if (preg_match("/[\x{20000}-\x{2AFFF}]/u", $str)) { 
		return '##lthtmltag##span lang="zh-HK"##gthtmltag##' . $str .'##lthtmltag##/span##gthtmltag##';
	}
	else{ 
		return '##lthtmltag##span lang="zh-CN"##gthtmltag##' . $str .'##lthtmltag##/span##gthtmltag##';
	}
	return $str;
}

// mPDF 2.3
function replaceArabic($str) {
	if (!$this->rtlAsArabicFarsi && preg_match("/[".$this->pregNonARABICchars ."]/u", $str) ) {
		// PASHTO, SINDHI, URDU
		return '##lthtmltag##span lang="ur"##gthtmltag##'.$str.'##lthtmltag##/span##gthtmltag##';	
	}
	else {
		// ARABIC, PERSIAN
		return '##lthtmltag##span lang="ar"##gthtmltag##'.$str.'##lthtmltag##/span##gthtmltag##';	
	}
	return $str;
}



// mPDF 2.3
function AutoFont($html) {
	if ( !$this->is_MB ) { return $html; }
	$this->useLang = true;
	if ($this->autoFontGroupSize == 1) { $extra = $this->pregASCIIchars1; }
	else if ($this->autoFontGroupSize == 3) { $extra = $this->pregASCIIchars3; }
	else {  $extra = $this->pregASCIIchars2; }
	$n = '';
	$a=preg_split('/<(.*?)>/ms',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	foreach($a as $i => $e) {
	   if($i%2==0) {
		// mPDF 3.0	
		$e = strcode2utf($e);	
		$e = lesser_entity_decode($e);
		// CJK
		if ($this->autoFontGroups & AUTOFONT_CJK) {
			$e = preg_replace("/([".$this->pregCJKchars.$extra."]*[".$this->pregCJKchars."][".$this->pregCJKchars.$extra."]*)/ue", '$this->replaceCJK(stripslashes(\'\\1\'))', $e);
		}

		if ($this->autoFontGroups & AUTOFONT_THAIVIET) {
			// THAI
			$e = preg_replace("/([\x{0E00}-\x{0E7F}".$extra."]*[\x{0E00}-\x{0E7F}][\x{0E00}-\x{0E7F}".$extra."]*)/u", '##lthtmltag##span lang="th"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);
			// Vietnamese
			$e = preg_replace("/([".$this->pregVIETchars .$this->pregVIETPluschars ."]*[".$this->pregVIETchars ."][".$this->pregVIETchars .$this->pregVIETPluschars ."]*)/u", '##lthtmltag##span lang="vi"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	
		}

		if ($this->autoFontGroups & AUTOFONT_RTL) {
			// HEBREW
			$e = preg_replace("/([".$this->pregHEBchars .$extra."]*[".$this->pregHEBchars ."][".$this->pregHEBchars .$extra."]*)/u", '##lthtmltag##span lang="he"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e); 
			// All Arabic
			$e = preg_replace("/([".$this->pregARABICchars .$extra."]*[".$this->pregARABICchars ."][".$this->pregARABICchars .$extra."]*)/ue", '$this->replaceArabic(stripslashes(\'\\1\'))', $e);
		}

		// INDIC
		if ($this->autoFontGroups & AUTOFONT_INDIC) {
			// Bengali
			$e = preg_replace("/([\x{0980}-\x{09FF}".$extra."]*[\x{0980}-\x{09FF}][\x{0980}-\x{09FF}".$extra."]*)/u", '##lthtmltag##span lang="bn"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	
			// Devanagari (= script for Hindi, Nepali + Sindhi)
			$e = preg_replace("/([\x{0900}-\x{097F}".$extra."]*[\x{0900}-\x{097F}][\x{0900}-\x{097F}".$extra."]*)/u", '##lthtmltag##span lang="hi"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	
			// Gujarati
			$e = preg_replace("/([\x{0A80}-\x{0AFF}".$extra."]*[\x{0A80}-\x{0AFF}][\x{0A80}-\x{0AFF}".$extra."]*)/u", '##lthtmltag##span lang="gu"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	
			// Malayalam
			$e = preg_replace("/([\x{0D00}-\x{0D7F}".$extra."]*[\x{0D00}-\x{0D7F}][\x{0D00}-\x{0D7F}".$extra."]*)/u", '##lthtmltag##span lang="ml"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	

			// Indic fonts with limited coverage of ASCII
			// Kannada
			$e = preg_replace("/([\x{0C80}-\x{0CFF}".$this->pregASCIILchars."]*[\x{0C80}-\x{0CFF}][\x{0C80}-\x{0CFF}".$this->pregASCIILchars."]*)/u", '##lthtmltag##span lang="kn"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	
			// Oriya
			$e = preg_replace("/([\x{0B00}-\x{0B7F}".$this->pregASCIILchars."]*[\x{0B00}-\x{0B7F}][\x{0B00}-\x{0B7F}".$this->pregASCIILchars."]*)/u", '##lthtmltag##span lang="or"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	
			// Punjabi ?= Gurmuhki 
			$e = preg_replace("/([\x{0A00}-\x{0A7F}".$this->pregASCIILchars."]*[\x{0A00}-\x{0A7F}][\x{0A00}-\x{0A7F}".$this->pregASCIILchars."]*)/u", '##lthtmltag##span lang="pa"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	
			// Tamil
			$e = preg_replace("/([\x{0B80}-\x{0BFF}".$this->pregASCIILchars."]*[\x{0B80}-\x{0BFF}][\x{0B80}-\x{0BFF}".$this->pregASCIILchars."]*)/u", '##lthtmltag##span lang="ta"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	
			// Telugu
			$e = preg_replace("/([\x{0C00}-\x{0C7F}".$this->pregASCIILchars."]*[\x{0C00}-\x{0C7F}][\x{0C00}-\x{0C7F}".$this->pregASCIILchars."]*)/u", '##lthtmltag##span lang="te"##gthtmltag##\\1##lthtmltag##/span##gthtmltag##', $e);	
		}

		// mPDF 3.0	
		$e = preg_replace('/[&]/u','&amp;',$e);
		$e = preg_replace('/[<]/u','&lt;',$e);
		$e = preg_replace('/[>]/u','&gt;',$e);
		$e = preg_replace('/##lthtmltag##/','<',$e);
		$e = preg_replace('/##gthtmltag##/','>',$e);

		$a[$i] = $e;
	   }
	   else {
		$a[$i] = '<'.$e.'>'; 
	   }
	}
	$n = implode('',$a);
	return $n;
}

    
//===========================


}//end of Class



?>
