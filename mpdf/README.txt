Upgrading
============

To upgrade from mPDF 5.6 to 5.7, simply upload all the files to their corresponding folders, overwriting files as required.



If you wish to keep your config.php file, you will need to make the following edits (additions) to your config.php file:

$this->defaultCSS = array(
	...
	'BODY' => array(
		'HYPHENS' => 'manual',
	),
);


$this->allowedCSStags .= '|TEXTCIRCLE|DOTTAB';



config.php
----------
Removed:
	$this->hyphenateTables
	$this->hyphenate
	$this->orphansAllowed
New: 
	$this->decimal_align = array();
	$this->h2toc = array();
	$this->h2bookmarks = array();
	$this->CJKforceend = false;
