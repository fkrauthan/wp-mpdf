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
	mpdf_admin_allowedprintedpages();
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
		update_option('mpdf_allow_all', isset($_POST['allow_all']));
		
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
	echo '<tr><td>Allow to Print all Pages: </td><td><input type="checkbox" name="allow_all" ';
	if(get_option('mpdf_allow_all')==true) echo 'checked="checked"';
	echo '/></td></tr>';
	echo '</table>';
	echo '<input type="submit" value="Save" name="save_options" /> <input type="reset" />';
	echo '</form>';
}

function mpdf_admin_allowedprintedpages() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'wp_mpdf_allowed';
	
	echo '<h2>Allow Printed Pages</h2>';
	
	if(isset($_GET['delallowedprintedpage'])) {
		$wpdb->query('DELETE FROM '.$table_name.' WHERE id='.$_GET['delallowedprintedpage'].' LIMIT 1');
		
		echo '<p style="color: green;">Delete allowed Page with id "'.$_GET['delallowedprintedpage'].'"</p>';
	}
	
	if(isset($_POST['addallowedpage'])) {
		$page_type = $_POST['allowedpage_type'];
		if($page_type=='page'||$page_type=='post') {
			$page_name = $_POST['allowedpage_title'];
			
			$page = null;
			if($page_type=='page') {
				$page = get_page_by_title($page_name);
			}
			else {
				$sql = 'SELECT id FROM '.$wpdb->posts.' WHERE post_title="'.$page_name.'" AND post_type="post" LIMIT 1';
				$post_id = $wpdb->get_var($sql);
				if($post_id!=null)
					$page = get_post($post_id);
			}
			
			if($page!=null) {
				$sql = 'SELECT id FROM '.$table_name.' WHERE post_id='.$page->ID.' AND post_type="'.$page_type.'" LIMIT 1';
				$db_id = $wpdb->get_var($sql);
				
				if($db_id == null ) {
					$sql = 'INSERT INTO '.$table_name.' (post_type, post_id, enabled) VALUES (%s, %d, %d)';
					$wpdb->query($wpdb->prepare($sql, $page_type, $page->ID, true));
				}
				
				echo '<p style="color: green;">Post has been added to the .</p>';
			}
			else {
				echo '<p style="color: red;">Post not found.</p>';
			}
		}
		else {
			echo '<p style="color: red;">Selected type is not defined.</p>';
		}
	}
	else if(isset($_GET['addallowedpage'])) {
		echo '<form action="?page='.$_GET['page'].'" method="post">';
		echo '<select name="allowedpage_type"><option value="post">Post</option><option value="page">Page</option></select> Title: <input type="text" name="allowedpage_title" value="" /><br />';
		echo '<input type="submit" value="Add Entry" name="addallowedpage" />';
		echo '</form>';
		echo '<br />';
	}
	
	echo '<a href="?page='.$_GET['page'].'&amp;addallowedpage=1">New Entry</a>';
	echo '<table border="1">';
	$sql = 'SELECT id,post_type,post_id,enabled FROM '.$table_name;
	$data = $wpdb->get_results($sql, OBJECT);
	for($i=0;$i<count($data);$i++) {
		echo '<tr>';
		echo '<td>'.$data[$i]->post_type.'</td>';
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		if($data[$i]->post_type=='post') {
			$post = get_post($data[$i]->post_id);
			echo '<td>'.$post->post_title.'</td>';
		}
		else {
			$page = get_page($data[$i]->post_id);
			echo '<td>'.$page->post_title.'</td>';
		}
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		echo '<td><a href="?page='.$_GET['page'].'&amp;delallowedprintedpage='.$data[$i]->id.'">Delete</a></td>';
		echo '</tr>';
	}
	echo '</table>';
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