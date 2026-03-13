<?php

namespace App\Domain\TracerStudy\Services;

use App\Models\TracerSurvey;
use App\Models\TracerResponse;
use App\Models\TracerAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class TracerStudyService
{
    public function getActiveSurvey()
    {
        return TracerSurvey::with('questions')
            ->where('is_active', true)
            ->first();
    }

    public function submitSurvey(User $user, int $surveyId, array $answers)
    {
        DB::beginTransaction();

        try {

            $exists = TracerResponse::where([
                'survey_id' => $surveyId,
                'user_id' => $user->id
            ])->exists();

            if ($exists) {
                throw new Exception('Tracer study already submitted');
            }

            $response = TracerResponse::create([
                'survey_id' => $surveyId,
                'user_id' => $user->id,
                'submitted_at' => now()
            ]);

            foreach ($answers as $answer) {

                TracerAnswer::create([
                    'response_id' => $response->id,
                    'question_id' => $answer['question_id'],
                    'answer_value' => $answer['value'] ?? null,
                    'answer_json' => $answer['values'] ?? null,
                ]);
            }

            DB::commit();

            return $response;
        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
