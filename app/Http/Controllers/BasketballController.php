<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\RateInterface;
use App\Models\PlayerTeam;

class BasketballController extends RatingController implements RateInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function calculateRating()
    {
        $stats = $this->getStats();
        $factors = $this->getCalculationFactors();
        $teams = array_unique($this->getTeams());
        $game = $this->getGame()->first();

        $players = PlayerTeam::whereHas('team', function ($q) use ($teams) {
            $q->whereIn('name', $teams);
        })
            ->whereHas('game', function ($q) use ($game) {
                $q->where('name', $game->name);
            })
            ->with('player')
            ->get()
            ->map(function ($item) {
                return $item->player;
            });

        foreach ($players as $player) {
            $position = $this->getPlayerPosition($player);
            $rating = 0;

            foreach ($factors as $factor) {
                if (($position === $factor['position'] || str_contains($factor['position'], lcfirst($position))))
                    $rating = ($stats[$player->nick_name][0] * $factor['scored point']) + ($stats[$player->nick_name][1] * $factor['rebound']) + ($stats[$player->nick_name][2] * $factor['assist']);
            }
            $player->playerTeam()->where('game_id', $game->id)->update(['rating' => $rating]);
        }
    }

}
