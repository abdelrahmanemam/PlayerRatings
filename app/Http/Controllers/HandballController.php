<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\RateInterface;
use App\Models\PlayerTeam;

class HandballController extends RatingController implements RateInterface
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
                    $rating = $factor['Initial rating points'] + ($stats[$player->nick_name][0] * $factor['Goal made']) + ($stats[$player->nick_name][1] * $factor['Goal recieved']);
            }

            $player->playerTeam()->where('game_id',$game->id)->update(['rating' => $rating]);
        }
    }

}
