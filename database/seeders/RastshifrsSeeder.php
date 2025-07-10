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
            ['rastshifr' => 'А1'],
            ['rastshifr' => 'А2'],
            ['rastshifr' => 'Б3'],
            ['rastshifr' => 'Б4'],
            ['rastshifr' => 'Ф5'],
        ];
        foreach($rastshifrs as $rastshifr){
            Rastshifr::create($rastshifr);
        }
    }
}
