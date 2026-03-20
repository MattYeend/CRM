<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Note::factory(50)->make()->each(function ($note) {
            if ($note->notable_id) {
                $note->save();
            }
        });
    }
}
