<?php

namespace Database\Seeders;

use App\Models\ContentSubmission;
use App\Models\User;
use App\Models\Writer;
use Illuminate\Database\Seeder;

class ContentSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $writerIds = Writer::pluck('id')->toArray();
        $editors   = User::whereHas('editor')->orWhereHas('admin')->pluck('id')->toArray();

        if (empty($writerIds)) return;

        $statuses = ['draft', 'submitted', 'under_review', 'approved', 'rejected'];

        for ($i = 0; $i < 15; $i++) {
            $status   = fake()->randomElement($statuses);
            $reviewed = in_array($status, ['approved', 'rejected', 'under_review']);

            ContentSubmission::create([
                'writer_id'      => fake()->randomElement($writerIds),
                'reviewer_id'    => $reviewed ? fake()->randomElement($editors) : null,
                'title'          => fake('ar_SA')->sentence(rand(5, 10)),
                'subtitle'       => fake('ar_SA')->sentence(5),
                'content'        => implode("\n\n", fake('ar_SA')->paragraphs(rand(4, 8))),
                'type'           => fake()->randomElement(['article', 'report']),
                'status'         => $status,
                'reviewer_notes' => $reviewed ? fake('ar_SA')->sentence() : null,
            ]);
        }
    }
}
