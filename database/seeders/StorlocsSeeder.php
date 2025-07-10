<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class StorlocsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storlocs = [
            [
                'storloc' => 'Хр1',
                'storlocdescr' => 'Хранилище1',
            ],
            [
                'storloc' => 'Хр2',
                'storlocdescr' => 'Хранилище2',
            ],
            [
                'storloc' => 'ЧЗ',
                'storlocdescr' => 'Читальный_зал',
            ],
        ];
        DB::table('storlocs')->insert($storlocs);
    }
}
