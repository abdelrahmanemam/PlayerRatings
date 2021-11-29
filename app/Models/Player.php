<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'nick_name', 'position', 'number'];

    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'player_team')->withPivot('rating', 'game_id');
    }

    public function playerTeam(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(PlayerTeam::class, 'player_id');
    }
}
