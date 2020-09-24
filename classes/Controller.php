<?php
use Illuminate\Support;  // https://laravel.com/docs/5.8/collections - provides the collect methods & collections class
use LSS\Array2Xml;

require_once __DIR__ . '/Exporter.php';
require_once __DIR__ . '/Reporter.php';


class Controller {

    public function __construct($args) {
        $this->args = $args;
    }

    public function export($type, $format) {
        $data = [];
        $exporter = new Exporter();
        switch ($type) {
            case 'playerstats':
                $searchArgs = ['player', 'playerId', 'team', 'position', 'country'];
                $search = $this->args->filter(function($value, $key) use ($searchArgs) {
                    return in_array($key, $searchArgs);
                });
                $data = $exporter->getPlayerStats($search);
                break;
            case 'players':
                $searchArgs = ['player', 'playerId', 'team', 'position', 'country'];
                $search = $this->args->filter(function($value, $key) use ($searchArgs) {
                    return in_array($key, $searchArgs);
                });
                $data = $exporter->getPlayers($search);
                break;
        }
        if (!$data) {
            exit("Error: No data found!");
        }
        return format($data, $format);
    }

    public function report($type) 
    {
        $data = [];
        $reporter = new Reporter();
         switch ($type) {
            case 'all':
                $data = $reporter->getTeamCode();
                break;
            case '3ptsplayer':
                $data = $reporter->getBest3ptShooter();
                break;
            case '3ptsteam':
                $data = $reporter->getBest3ptShooterTeam();
                break;
        }

        return $data;
    }


}