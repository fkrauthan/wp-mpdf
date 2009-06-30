<?php
/*
 * This file is part of wp-mpdf.
 * wp-mpdf is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free  
 * Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * wp-mpdf is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or  
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with wp-mpdf. If not, see <http://www.gnu.org/licenses/>.
 */
 
function mpdf_admin_display() {
	echo '<h1>Configure wp-mpdf</h1>';
	echo '<table style="width: 100%;" border="0">';
	echo '<tr><td width="300" style="vertical-align: top;">';
	mpdf_admin_options();
	echo '</td><td style="vertical-align: top;">';
	mpdf_admin_cache();
	echo '</td></tr></table>';
}

function mpdf_admin_options() {
	echo '<h2>Options</h2>';
	
	if(isset($_POST['save_options'])) {
		update_option('mpdf_theme', $_POST['theme']);
		update_option('mpdf_caching', isset($_POST['caching']));
		update_option('mpdf_geshi', isset($_POST['geshi']));
		
		echo '<p style="color: green;">Options Saved</p>';
	}
	
	echo '<form action="?page='.$_GET['page'].'" method="post">';
	echo '<table border="0">';
	echo '<tr><td>Thema: </td><td>';
	echo '<select name="theme">';
	//Search for Themes
	if($dir = opendir(dirname(__FILE__).'/themes')) {
		while($file = readdir($dir)) {
			if(!is_dir($path.$file) && $file != "." && $file != "..")  {
				if(strtolower(substr($file, count($file)-4))=='php') {
					echo '<option value="'.substr($file, 0, count($file)-5).'" ';
					if(get_option('mpdf_theme')==substr($file, 0, count($file)-5)) {
						echo 'selected="selected"';
					}
					echo '>'.str_replace('_', ' ', substr($file, 0, count($file)-5)).'</option>';
				}
			}
		}
	}
	echo '</select>';
	echo '</td></tr>';
	echo '<tr><td>Caching: </td><td><input type="checkbox" name="caching" ';
	if(get_option('mpdf_caching')==true) echo 'checked="checked"';
	echo '/></td></tr>';
	echo '<tr><td>Geshi Parsing: </td><td><input type="checkbox" name="geshi" ';
	if(get_option('mpdf_geshi')==true) echo 'checked="checked"';
	echo '/></td></tr>';
	echo '</table>';
	echo '<input type="submit" value="Save" name="save_options" /> <input type="reset" />';
	echo '</form>';
}

function mpdf_admin_cache() {
	echo '<h2>Cache</h2>';
	
	if(isset($_GET['delfile'])) {
		if(file_exists(dirname(__FILE__).'/cache/'.$_GET['delfile']))
			unlink(dirname(__FILE__).'/cache/'.$_GET['delfile']);
		if(file_exists(dirname(__FILE__).'/cache/'.$_GET['delfile'].'.cache'))
			unlink(dirname(__FILE__).'/cache/'.$_GET['delfile'].'.cache');
			
		echo '<p style="color: green;">Cache file "'.$_GET['delfile'].'" is deleted</p>';
	}
	if(isset($_GET['clearcache'])) {
		if($dir = opendir(dirname(__FILE__).'/cache')) {
			while($file = readdir($dir)) {
				if(!is_dir($path.$file) && $file != "." && $file != "..")  {
					unlink(dirname(__FILE__).'/cache/'.$file);
				}
			}
		}
		
		echo '<p style="color: green;">Cache is cleared</p>';
	}
	
	
	echo '<p><a href="?page='.$_GET['page'].'&amp;clearcache=1">Clear Cache</a></p>';
	
	echo '<table border="1">';
	if($dir = opendir(dirname(__FILE__).'/cache')) {
		while($file = readdir($dir)) {
			if(!is_dir($path.$file) && $file != "." && $file != "..")  {
				if(strtolower(substr($file, strlen($file)-5))=='cache') {
					$pdffilename = substr($file, 0, strlen($file)-6);
					echo '<tr>';
					echo '<td style="padding: 5px;">'.file_get_contents(dirname(__FILE__).'/cache/'.$file).'</td>';
					echo '<td style="padding: 5px;"><a href="../wp-content/plugins/wp-mpdf/cache/'.$pdffilename.'">'.$pdffilename.'</a></td>';
					echo '<td style="padding: 5px;"><a href="?page='.$_GET['page'].'&amp;delfile='.$pdffilename.'">Delete</a></td>';
					echo '</tr>';
				}
			}
		}
	}
	echo '</table>';
}

?>