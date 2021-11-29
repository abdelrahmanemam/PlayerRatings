<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerTeam extends Model
{
    use HasFactory;

    protected $table = 'player_team';

    protected $fillable = ['game_id', 'team_id', 'player_id'];

    public function player(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(Player::class, 'id', 'player_id');
    }

    public function team(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(Team::class, 'id', 'team_id');
    }

    public function game(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(Game::class, 'id', 'game_id');
    }
}
