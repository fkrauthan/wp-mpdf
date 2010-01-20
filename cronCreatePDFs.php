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


//Call this script from a cron job to create/update the pdf cache
require_once(dirname(__FILE__).'/../../../wp-config.php');
require_once(dirname(__FILE__).'/wp-mpdf.php');


//Disable the Timeout
set_time_limit(0);


//Check if Caching is enabled or not
if(get_option('mpdf_caching')!=true) {
	echo "No caching enabled\n";
	exit(-1);
}


//Cache the posts
$_GET['output'] = 'pdf';
echo "Start cache creating\n";

$posts = get_posts('numberposts=-1&order=ASC&orderby=title');
foreach($posts as $post) {
	echo "Create cache for post (".$post->ID.")\n";
	

	query_posts('p='.$post->ID);
	mpdf_exec('false');
}

echo "Caching finished\n";
?>
