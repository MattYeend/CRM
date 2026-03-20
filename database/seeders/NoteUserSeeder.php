<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\NoteUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $notes = Note::all();

        foreach ($notes as $note) {
            $assignedUsers = $users->random(rand(1, 5));

            foreach ($assignedUsers as $user) {
                NoteUser::create([
                    'note_id' => $note->id,
                    'user_id' => $user->id,
                    'is_test' => true,
                    'meta' => ['important' => (bool) rand(0, 1)],
                ]);
            }
        }
    }
}
