<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rastshifr;

class RastshifrsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rastshifrs = [
            ['rastshifr' => 'A1'],
            ['rastshifr' => 'A2'],
            ['rastshifr' => 'A3'],
            ['rastshifr' => 'A4'],
            ['rastshifr' => 'A5'],
            ['rastshifr' => 'A6'],
            ['rastshifr' => 'A7'],
        ];
        foreach($rastshifrs as $rastshifr){
            Rastshifr::create($rastshifr);
        }
    }
}
