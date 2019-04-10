# wp-mpdf #

**Contributors:** fkrauthan  
**Donate link:** [fkrauthan.de](http://fkrauthan.de)  
**Wordpress plugin page:** [wordpress.org/extend/plugins/wp-mpdf/](http://wordpress.org/extend/plugins/wp-mpdf/)  
**Tags:** print, printer, wp-mpdf, pdf, mpdf  
**Requires at least:** 2.9  
**Tested up to:** 5.1.1
**Stable tag:** 3.4

Print Wordpress posts as PDF. Optional with Geshi highlighting.

## Description ##

Print Wordpress posts as PDF. Optional with Geshi highlighting. It also has support for password protected posts and only logged in users can print post as pdf support.  


## Changelog ##

### 3.4 ###
* Made codebase PHP 7.3 compatible (thanks to nopticon)
* Fixed issues with newer wordpress versions (thanks to nopticon)

### 3.3 ###
* Fixed some small bugs
* Updated mpdf to version 6
* Updated geshi to latest version
* Made plugin compatible with PHP 7

### 3.2.2 ###
* Fixed admin menu call

### 3.2.1 ###
* Fixed options menu with recent Wordpress versions (thanks to NickGreen)
* Removed a call to a deprecated method

### 3.2 ###
* Added option to replace the pdf button images
* Fixed a bug where 0 for pdf margin was interpreted as no value set

### 3.1.3 ###
* Added removed font folder (again...)

### 3.1.2 ###
* Added missing files
* Fixed a php warning (thanks to Menelao)
* Fixed a temp folder issue (thanks to shortster)

### 3.1.1 ###
* Fixed cache dir guessing

### 3.1 ###
* Added nofollow option to the pdf download buttons (default: not enabled)
* If cache folder within the plugin folder is not writable check for cache folder within the mpdf template folder

### 3.0.1 ###
* Added missing font folder
* Updated mpdf lib to version 3.7.1

### 3.0 ###
* Fixed some issues with wordpress 3.7
* Changed template loading (templates loaded from the custom template folder. If it can't find a template there it tries to load it from the plugin template folder.)
* Updated mpdf lib to version 5.7
* Moved all tmp directories to one folder (plugin_direcotry/tmp. Don't forget to give write permission for this folder)
* Removed not needed files and directories for the mpdf lib

### 2.12.0 ###
* Removed some deprecated calls (thanks to Permarad)
* Fixed some php warnings (thanks to Jason Judge)
* Fixed the readme
* Added the possibility to translate the ui (at the moment frontend only)

### 2.11.0 ###
* Added some hooks and actions to the plugin (thanks to Danny)

### 2.10.0 ###
* Updating mpdf lib to version 5.6.1

### 2.9.4 ###
* Fixed category link in the templates

### 2.9.3 ###
* Fixed pdf link id to class (thanks to ZilverDragOn)

### 2.9.2 ###
* Fixed project homepage link again

### 2.9.1 ###
* Fixed project homepage link

### 2.9 ###
* Disable printing of password protected posts without entering passwort first

### 2.8 ###
* Add french language templates (thanks to Patrick)

### 2.7 ###
* Add template option to enable HTML in Header and Footer

### 2.6 ###
* Updateing mpdf lib to version 5.3
* Add a small fix for image paths. Thanks to Mário Kašuba.

### 2.5 ###
* Disable wrong anotations in PDFs (thanks to misenko)
* Fix image printing for multi instance wordpress setups (thanks to stephen7cohen)

### 2.4.5 ###
* Fix version number

### 2.4.4 ###
* Add a debug option (You must create a folder called debug in the wp-mpdf root folder)

### 2.4.3 ###
* Set the tmp path for mpdf
* Add codepage selection to the admin menu

### 2.4.2 ###
* Update the german template translation (Thank you for your help Andreas)
* Updateing mpdf lib to version 4.6
* Fix formating for the default english template

### 2.4.1 ###
* Fix a small image bug

### 2.4 ###
* Updating mpdf lib
* Add option to select a user for cron generating of pdfs for cache

### 2.3.8 ###
* Now the content filter would be added to the pdf file. So all content plugins should work at pdf print now.
* Remove some broaken wp-mpdf filters

### 2.3.7 ###
* There goes somthing worng. This version should work correct again.

### 2.3.6 ###
* Fix a copy and paste bug

### 2.3.5 ###
* Fix relativ links

### 2.3.4 ###
* Fix a encoding problem (Thanks to swwwolf)
* Add geshi line number print as a option

### 2.3.3 ###
* Now the bug is completly fixed :)

### 2.3.2 ###
* The bug is at the moment not fixed.

### 2.3.1 ###
* Spaces and Tabs are now correct at pre tags. (Thanks to mark for his help)

### 2.3 ###
* Now to add new Posts to the black-/white-list, you only must select it from a dropdown

### 2.2 ###
* Added the option to add a text if you have checked "need login" and a user isn't logged in
* Added an option to set "need login" per post
* Added an option to set an own pdf name
* Added an optional download statistic
* Now removing cache file and database entry when a post/page is deleted

### 2.1.1 ###
* Fixed a new image print bug

### 2.1 ###
* Added the option to allow pdf print only to users who are logged in
* Added a script that you can call by cron to create templates in the background (if you have caching enabled)

### 2.0.2 ###
* Added a missing file

### 2.0.1 ###
* Fixed two small include bugs
* Fixed a php4 issue

### 2.0 ###
* Some small bugfixes
* PDF Template support (now you can also use a pdf file as a template): If you need this please write me a mail so I can tell you how it works.

### 1.9.2 ###
* Added two vars to the templates for using PDF Templates

### 1.9 ###
* Added option to make a black list instead of a white list
* Added button to clear all selected posts on the black/white list

### 1.8 ###
* Fixed the pdf button function e.g. when using this function on the archive page

### 1.7 ###
* Fixed a small pre html tag problem

### 1.6 ###
* Fixed an image printing bug
* Cleaned some code parts which aren't needed
* Changed PDF author and creator string
* Moved the theme dir from the plugin dir to `wp-content/wp-mpdf-themes`
* Now you can access the plugin config under settings and not any longer under plugins

### 1.5 ###
* Fixed an encoding problem again

### 1.4 ###
* Added function parameter for pdfbutton to link to a new page for PDF Print
* Added function to display only for some Posts and Pages the PDF print

### 1.3 ###
* Fixed a `<pre>` problem with newlines

### 1.2 ###
* Fixed an encoding problem
* Converted `<pre>` to `<div class="pre">` to prevent a pdf print bug

### 1.1 ###
* Created an admin panel

### 1.0 ###
* Released the Plugin


## Installation ##

1. Upload the whole plugin folder to your `/wp-content/plugins/` folder.
1. Set write permission (777) to the plugin dir folder => `/wp-content/plugins/wp-mpdf/cache`
1. Go to the plugins page and activate the plugin.
1. Add to your template "`<?php if(function_exists('mpdf_pdfbutton')) mpdf_pdfbutton(); ?>`" as a small button or "`<?php if(function_exists('mpdf_pdfbutton')) mpdf_pdfbutton(false, 'my link', 'my login text'); ?>`" as a textlink. The second text specifies the text which should displayed if you have checked "needs login" and a user isn't loggend in. (if you wish to open the pdf print in a new tab you may pass "true" for the first parameter)
1. You can adjust some options: in your admin interface, click on plugins and then on wp-mpdf. For allowing or disabling pdf export you can use the checkbox when creating/editing a post or a page.
1. Place your templates into `/wp-content/wp-mpdf-themes`

The mpdf_pdfbutton function signature: `function mpdf_pdfbutton($opennewtab=false, $buttontext = '', $logintext = 'Login!', $print_button = true, $nofollow = false, $options = array())`

The options array supports 'pdf_lock_image' => '/my/image/path/relative/to/wordpress/route' and 'pdf_image' => '/my/image/path/relative/to/wordpress/route' to overwrite which icon should be used.


## License ##

This file is part of wp-mpdf.

**wp-mpdf is free software:** you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.


wp-mpdf is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with wp-mpdf. If not, see [www.gnu.org/licenses/](http://www.gnu.org/licenses/).


## Publish to wordpress ##

### Setup svn ###

You need to install the subversion client on your local system

### Commit changes to git ###

Make sure that all your changes are committed to the repository. The deployment script will stop in case there are uncommitted changes to prevent any mistakes.


### Publish new version ###

You just need to execute the `release.sh` script. It will take care of some basic validation and the full publish process.

	./release.sh
