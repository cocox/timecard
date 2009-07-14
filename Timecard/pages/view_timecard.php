<?php
# Copyright (C) 2008	John Reese
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_manage_menu();

if( 0 == $t_current_project ){ # All Projects selected

	$t_projects = project_get_all_rows();
	$t_all_projects = array();

	foreach( $t_projects as $t_project ){
		$t_all_projects[] = $t_project['id'];
	}

} else { # Use Current Project plus any Subprojects

	$t_current_project = helper_get_current_project();
	$t_all_projects = project_hierarchy_get_all_subprojects( $t_current_project );
	array_unshift( $t_all_projects, $t_current_project );

}

$t_bug_table = db_get_table( 'mantis_bug_table' );

echo '<table class="width100" cellspacing="1">';

echo '<tr class="row-category">
		<td>' . lang_get( 'id' ) . '</td>
		<td>' . lang_get( 'summary' ) . '</td>
		<td>' . lang_get( 'assigned_to' ) . '</td>
		<td>' . plugin_lang_get( 'timecard' ) . '</td>
		<td>' . plugin_lang_get( 'hours_remaining' ) . '</td>
	</tr>';

$i = 1; #row class selector
$t_time_sum = 0;

foreach( $t_all_projects as $t_all_project ){

	$t_query = "SELECT * FROM $t_bug_table
			WHERE project_id=" . db_param();
	$t_result = db_query_bound( $t_query, array( $t_all_project ) );

	while ( $t_row = db_fetch_array( $t_result ) ) {

		$t_timecard = TimecardBug::load( $t_row['id'] );
		$t_timecard->summary = substr( $t_row['summary'], 0, 60 );
		$t_timecard->assigned = user_get_name( $t_row['handler_id'] );

		if( $t_timecard->estimate < 0 ){
			$t_timecard->estimate = plugin_lang_get( 'estimate_zero' );
			$row_class = 'negative';
		} else {
			$t_time_sum += $t_timecard->estimate;
			$row_class = "row-$i";
		}

		echo "<tr class='$row_class'>
				<td>" , print_bug_link( $t_timecard->bug_id ) , '</td>' .
				'<td>' . $t_timecard->summary . '</td>
				<td>' . $t_timecard->assigned . '</td>
				<td class="center">' . $t_timecard->timecard . '</td>
				<td class="center">' . $t_timecard->estimate . '</td>
			</tr>';

		$i = ($i == 1) ? 2 : 1; #toggle row class selector
	}
}
echo '<tr class="spacer"></tr>';
echo '<tr class="bold"><td colspan="4" class="right">' . plugin_lang_get( 'total_remaining' ) . '</td><td class="center">' . $t_time_sum . '</td></tr>';
echo '</table>';

?>