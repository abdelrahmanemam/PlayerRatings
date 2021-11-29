<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;

class PlayerController extends Controller
{
    static public function show($game, $players)
    {
        $gameId = Game::where('name', $game)->first()->id;

        return
            Player::whereIn('nick_name', $players)
                ->whereHas('teams.games', function ($q) use ($game) {
                    $q->where('name', strtolower($game));
                })
                ->with(['teams', 'teams.games'])
                ->get()
                ->map(function ($player) use ($gameId, $game) {
                    return [
                        "nick_name" => $player->nick_name,
                        "name" => $player->name,
                        "position" => $player->position,
                        "number" => $player->number,
                        "rating" => $player->teams->first()->pivot->where('game_id', $gameId)->where('player_id', $player->id)->first()->rating,
                        "team" => $player->teams->first()->name,
                        "game" => $game
                    ];
                });
    }
}
