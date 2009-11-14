=== wp-mpdf ===
Contributors: fkrauthan
Donate link: http://www.fkrauthan.de
Tags: print, printer, wp-mpdf, pdf, mpdf
Requires at least: 2.8
Tested up to: 2.8
Stable tag: 1.9.1

Print Wordpress Posts as PDF. Optional with Geshi highlighting.

== Description ==

Print Wordpress Posts as PDF. Optional with Geshi highlighting. It is written for a online games programming magazine

!!!Important!!!
1.) The themes folder has been moved from "wp-content/plugins/wp-mpdf/themes" to "wp-content/wp-mpdf-themes". The Movement of the templates on the folder themes would be made automaticli but if you have trouble then made this movment manualy.
2.) The plugin config is now avaible at the Settings Submenu not longer on the Plugins Submenu
!!!Important!!!



== Changelog ==  

= 1.9 =
* Add Option to make a black list instead of a white list
* Add Button to clear all selecetet Posts at the black/white list
= 1.8 =
* Fix the pdf button function if you use this function on the archive page for example
= 1.7 =
* Fix a small pre html tag problem
= 1.6 =
* Fix a image print bug
* Clean some not needed code parts
* Changing PDF author and creater string
* Move the theme dir from the plugin dir to wp-content/wp-mpdf-themes
* Now you can access the Plugin config under Settings and not any longer under Plugins
= 1.5 =
* Fix a encoding problem again
= 1.4 =
* Add function parameter for pdfbutton to link to a new page for PDF Print
* Add function to display only for some Posts and Pages the PDF print
= 1.3 =
* Fix a pre problem with newlines
= 1.2 =
* Fix a encoding problem
* convert &lt;pre&gt; to &lt;div class="pre"&gt; to prevent a pdf print bug
= 1.1 =
* Create a admin panel
= 1.0 =  
* Release the Plugin  

== Installation ==

1. Upload the whole plugin folder to your /wp-content/plugins/ folder.
2. Give the folders in the Plugin dir write permission (777) => wp-mpdf/cache AND wp-mpdf/graph_cache
3. Go to the Plugins page and activate the plugin.
4. Add to your template "&lt;?php if(function&#95;exists('mpdf&#95;pdfbutton')) mpdf&#95;pdfbutton(); ?&gt;" for a small button or "&lt;?php if(function&#95;exists('mpdf&#95;pdfbutton')) mpdf&#95;pdfbutton('my link'); ?&gt;" for a textlink.
 (if you wish to open the pdf print in a new tab you can add as first parameter true)
5. You can adjust some options if you go to your admin interface, klick on plugins and then on wp-mpdf. For pdf allow you can use the checkbox when you create or edit a Post or Site.

== License ==

This file is part of wp-mpdf.

wp-mpdf is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

wp-mpdf is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with wp-mpdf. If not, see <http://www.gnu.org/licenses/>.

