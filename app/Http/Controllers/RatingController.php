<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\RateInterface;
use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\Team;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    private $calculationFactors = [];
    private $game;
    private $stats = [];
    private $teams = [];

    public function __construct()
    {
        $request = Request::capture();
        $this->setGame($request);
        $this->setCalculationFactors();
        $this->setStats($request);
    }

    public function getCalculationFactors(): array
    {
        return $this->calculationFactors;
    }

    public function getGame()
    {
        return $this->game;
    }

    public function getPlayerPosition(Player $player)
    {
        return $player->position;
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    public function setGame(Request $request)
    {
        $this->game = Game::where('name', strtolower($request->game));
    }

    public function getTeams(): array
    {
        return $this->teams;
    }

    public function setTeams($team)
    {
        $this->teams[] = $team;
    }

    public function setCalculationFactors(): void
    {
        $calculationFactors = $this->game->firstOrFail('calculation_factors');
        $this->calculationFactors = json_decode($calculationFactors['calculation_factors'], true);
    }

    public function setStats(Request $request)
    {
        $request = $request->toArray();
        $requestGame = $request['game'];
        unset($request['game']);
        foreach ($request as $item) {
            $itemArr = explode(';', $item);
            $game = Game::where('name', $requestGame)->first();
            if ($game === null)
                return response('game does not exist! create it first.');

            $team = Team::where('name', $itemArr[3])->first();
            $player = Player::where('nick_name', $itemArr[1])->first();

            if ($player === null)
                $player = Player::create([
                    'name' => $itemArr[0],
                    'nick_name' => $itemArr[1],
                    'number' => $itemArr[2],
                    'position' => $itemArr[4],
                ]);

            if ($team === null)
                $team = Team::create([
                    'name' => $itemArr[3],
                ]);

            $this->setTeams($team->name);

            if (!PlayerTeam::where('player_id', $player->id)->where('team_id', $team->id)->where('game_id', $game->id)->exists())
                PlayerTeam::create(['player_id' => $player->id, 'team_id' => $team->id, 'game_id' => $game->id]);

            $this->stats[$itemArr[1]] = array_values(array_slice($itemArr, 5));
        }
    }

    public function setRating(RateInterface $rate)
    {
        $rate->calculateRating();
    }

    public function create(Request $request)
    {
        $class = __NAMESPACE__ . '\\' . ucfirst(strtolower($request->game)) . "Controller";
        if (!class_exists($class))
            return response("game does not exist!");

        $this->setRating(new $class);

        $request = $request->toArray();
        $game = $request['game'];
        unset($request['game']);

        foreach ($request as $item) {
            $itemArr = explode(';', $item);
            $nickName[] = $itemArr[1];
        }
        $result = PlayerController::show($game, $nickName);

        $this->setWinningPoints($result);

        $resultWithWinningData = PlayerController::show($game, $nickName);

        return response($resultWithWinningData ?? $result, 200);
    }

    public function setWinningPoints($result)
    {
        $game = Game::where('name', $result[0]['game'])->first();

        [$firstTeam, $secondTeam] = array_values(array_unique(array_map(function ($item) {
            return $item['team'];
        }, $result->toArray())));

        $firstTeamPoints = $secondTeamPoints = [];
        $switch = false;

        foreach ($result as $key => $value) {
            if (!$switch) {
                $firstTeamPoints[$value['team']][] = $value['rating'];
            } else {
                $secondTeamPoints[$value['team']][] = $value['rating'];
            }

            if (isset($result[$key + 1]) && ($value['team'] !== $result[$key + 1]['team']))
                $switch = true;
        }

        $firstTeamPoints = array_sum($firstTeamPoints[$firstTeam]);
        $secondTeamPoints = array_sum($secondTeamPoints[$secondTeam]);

        $winnerTeam = $firstTeamPoints > $secondTeamPoints ? $firstTeam : $secondTeam;

        $winnerTeamId = Team::where('name', $winnerTeam)->first()->id;
        foreach ($result->where('team', $winnerTeam) as $prev) {
            $newPoints = $prev['rating'] + 10;
            PlayerTeam::where('game_id', $game->id)
                ->where('team_id', $winnerTeamId)
                ->where('player_id', Player::where('nick_name', $prev['nick_name'])->first()->id)
                ->update(['rating' => $newPoints]);
        }
    }

}
