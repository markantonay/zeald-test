<?php

/**
 * Use this file to output reports required for the SQL Query Design test.
 * An example is provided below. You can use the `asTable` method to pass your query result to,
 * to output it as a styled HTML table.
 */

$database = 'nba2019';
require_once('vendor/autoload.php');
require_once('include/utils.php');

/*
 * Example Query
 * -------------
 * Retrieve all team codes & names
 */
echo '<h1>Example Query</h1>';
$teamSql = "SELECT * FROM team";
$teamResult = query($teamSql);
// dd($teamResult);
echo asTable($teamResult);

/*
 * Report 1
 * --------
 * Produce a query that reports on the best 3pt shooters in the database that are older than 30 years old. Only 
 * retrieve data for players who have shot 3-pointers at greater accuracy than 35%.
 * 
 * Retrieve
 *  - Player name
 *  - Full team name
 *  - Age
 *  - Player number
 *  - Position
 *  - 3-pointers made %
 *  - Number of 3-pointers made 
 *
 * Rank the data by the players with the best % accuracy first.
 */
echo '<h1>Report 1 - Best 3pt Shooters</h1>';
// write your query here
$result = "SELECT roster.name as 'Player Name', team.name as 'Full Team Name', player_totals.age as 'Age', roster.number as 'Player Number', roster.pos as 'Position', FORMAT((3pt/3pt_attempted) * 100,2) as '3pointers made %', 3pt as 'Number of 3-pointers made' 
			FROM  player_totals	
		JOIN roster ON roster.id = player_totals.player_id		
		JOIN team ON team.code = roster.team_code
		WHERE age > 30 AND 3pt > (3pt_attempted * .35)";
$report1 = query($result);		
echo asTable($report1);




/*
 * Report 2
 * --------
 * Produce a query that reports on the best 3pt shooting teams. Retrieve all teams in the database and list:
 *  - Team name
 *  - 3-pointer accuracy (as 2 decimal place percentage - e.g. 33.53%) for the team as a whole,
 *  - Total 3-pointers made by the team
 *  - # of contributing players - players that scored at least 1 x 3-pointer
 *  - of attempting player - players that attempted at least 1 x 3-point shot
 *  - total # of 3-point attempts made by players who failed to make a single 3-point shot.
 * 
 * You should be able to retrieve all data in a single query, without subqueries.
 * Put the most accurate 3pt teams first.
 */
echo '<h1>Report 2 - Best 3pt Shooting Teams</h1>';
// write your query here
$result = "SELECT 
			team.name,
			FORMAT((SUM(3pt) / SUM(3pt_attempted)) * 100,2) as 'accuracy',
			(SUM(3pt)) as 'Total 3points made by the team',
			IF(3pt > 0, COUNT(player_id), 0) as '# of contributing players',
			IF(3pt_attempted > 0, COUNT(player_id), 0) as '# of contributing players',
			(SUM(3pt_attempted) - SUM(3pt)) as 'total # of 3-point attempts'
			FROM team 
			LEFT JOIN roster ON roster.team_code = team.code		
			LEFT JOIN player_totals ON player_totals.player_id = roster.id
			WHERE 3pt > (3pt_attempted * .35) GROUP BY team.code
			ORDER BY accuracy DESC";
$report2 = query($result);		
echo asTable($report2);

?>

