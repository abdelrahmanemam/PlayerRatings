<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Game::create(
            [
                'name' => 'basketball',
                'calculation_factors' => '[
{
"position": "guard",
"scored point": 2,
"rebound": 3,
"assist": 1
},
{
"position": "forward",
"scored point": 2,
"rebound": 2,
"assist": 2
},
{
"position": "center",
"scored point": 2,
"rebound": 1,
"assist": 3
}
]'
            ]
        );

        \App\Models\Game::create(
            [
                'name' => 'handball',
                'calculation_factors' => '[
{
"position": "goalkeeper",
"Initial rating points": 50,
"Goal made": 5,
"Goal recieved": -2
},
{
"position": "field player",
"Initial rating points": 20,
"Goal made": 1,
"Goal recieved": -1
}
]'
            ]
        );
    }
}
