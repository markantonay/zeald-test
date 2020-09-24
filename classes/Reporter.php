<?php
use Illuminate\Support;


class Reporter 
{
    public function __construct() {
	}

	function getTeamCode()
	{
		$sql = "SELECT * FROM team";
		$data = query($sql) ?: [];
		return $data;
	}

	function getBest3ptShooter()
	{
		$sql = "SELECT roster.name as 'Player Name', team.name as 'Full Team Name', player_totals.age as 'Age', roster.number as 'Player Number', roster.pos as 'Position', FORMAT((3pt/3pt_attempted) * 100,2) as '3pointers made %', 3pt as 'Number of 3-pointers made' FROM  player_totals JOIN roster ON roster.id = player_totals.player_id JOIN team ON team.code = roster.team_code WHERE age > 30 AND 3pt > (3pt_attempted * .35)";
		$data = query($sql) ?: [];
		return $data;
	}

	function getBest3ptShooterTeam()
	{
		$sql = "SELECT team.name, FORMAT((SUM(3pt) / SUM(3pt_attempted)) * 100,2) as 'accuracy',	(SUM(3pt)) as 'Total 3points made by the team',	IF(3pt > 0, COUNT(player_id), 0) as '# of contributing players',		IF(3pt_attempted > 0, COUNT(player_id), 0) as '# of contributing players', (SUM(3pt_attempted) - SUM(3pt)) as 'total # of 3-point attempts'	FROM team LEFT JOIN roster ON roster.team_code = team.code LEFT JOIN player_totals ON player_totals.player_id = roster.id WHERE 3pt > (3pt_attempted * .35) GROUP BY team.code ORDER BY accuracy DESC";
		$data = query($sql) ?: [];
		return $data;
	}
}

?>