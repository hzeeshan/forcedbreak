<?php

namespace Database\Seeders;

use App\Models\Challenge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChallengesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $challenges = [
            // Physical
            ['emoji' => '💪', 'title' => 'Do 10 push-ups',           'subtitle' => 'Get that blood pumping!',          'category' => 'physical'],
            ['emoji' => '🏋️', 'title' => 'Do 10 squats',             'subtitle' => 'Activate those legs!',             'category' => 'physical'],
            ['emoji' => '🤸', 'title' => 'Touch your toes 5 times',   'subtitle' => 'Loosen up your back.',             'category' => 'physical'],
            ['emoji' => '🧗', 'title' => 'Do 10 jumping jacks',       'subtitle' => 'Wake up your whole body!',         'category' => 'physical'],
            ['emoji' => '🦵', 'title' => 'Do 15 calf raises',         'subtitle' => 'Perfect for desk days.',           'category' => 'physical'],
            // Hydration
            ['emoji' => '💧', 'title' => 'Drink a glass of water',    'subtitle' => 'Hydration is everything.',         'category' => 'hydration'],
            ['emoji' => '🍵', 'title' => 'Make yourself a hot drink', 'subtitle' => 'Take a mindful moment.',           'category' => 'hydration'],
            ['emoji' => '🥤', 'title' => 'Refill your water bottle',  'subtitle' => 'Stay ahead of dehydration.',       'category' => 'hydration'],
            // Mental
            ['emoji' => '🧘', 'title' => 'Take 5 deep breaths',       'subtitle' => 'Slow down and reset.',             'category' => 'mental'],
            ['emoji' => '👀', 'title' => 'Look 20 feet away for 20s', 'subtitle' => 'Give your eyes a rest.',           'category' => 'mental'],
            ['emoji' => '📓', 'title' => 'Write one thing you\'re grateful for', 'subtitle' => 'A mindful minute.',     'category' => 'mental'],
            ['emoji' => '🌬️', 'title' => 'Box breathe for 1 minute', 'subtitle' => 'In 4s, hold 4s, out 4s, hold 4s.', 'category' => 'mental'],
            // Movement
            ['emoji' => '🚶', 'title' => 'Walk around for 2 minutes', 'subtitle' => 'Step away from the screen.',      'category' => 'movement'],
            ['emoji' => '🙆', 'title' => 'Do 10 shoulder rolls',      'subtitle' => 'Release the tension.',            'category' => 'movement'],
            ['emoji' => '🔄', 'title' => 'Roll your wrists & ankles', 'subtitle' => 'Loosen up those joints.',         'category' => 'movement'],
            ['emoji' => '🦴', 'title' => 'Stretch your neck gently',  'subtitle' => 'Tilt side to side, 5 each.',      'category' => 'movement'],
        ];

        foreach ($challenges as $data) {
            Challenge::firstOrCreate(
                ['title' => $data['title'], 'is_custom' => false],
                array_merge($data, ['is_active' => true, 'is_custom' => false])
            );
        }
    }
}
