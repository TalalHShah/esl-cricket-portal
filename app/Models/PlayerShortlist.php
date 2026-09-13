<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerShortlist extends Model
{
    protected $fillable = ['team_id', 'player_id'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
