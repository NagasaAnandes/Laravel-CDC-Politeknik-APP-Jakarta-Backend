<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\TracerStudy\Services\TracerStudyService;

class TracerSurveyController extends Controller
{
    protected TracerStudyService $service;

    public function __construct(TracerStudyService $service)
    {
        $this->service = $service;
    }

    /**
     * Get active tracer survey
     */
    public function survey()
    {
        $survey = $this->service->getActiveSurvey();

        if (!$survey) {
            return response()->json([
                'message' => 'No active tracer survey'
            ], 404);
        }

        return response()->json([
            'message' => 'Active tracer survey',
            'data' => $survey
        ]);
    }

    /**
     * Submit tracer study
     */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'survey_id' => ['required', 'exists:tracer_surveys,id'],
            'answers' => ['required', 'array'],

            'answers.*.question_id' => [
                'required',
                'exists:tracer_questions,id'
            ],

            'answers.*.value' => ['nullable'],
            'answers.*.values' => ['nullable', 'array'],
        ]);

        $response = $this->service->submitSurvey(
            $request->user(),
            $data['survey_id'],
            $data['answers']
        );

        return response()->json([
            'message' => 'Tracer study submitted successfully',
            'data' => $response
        ]);
    }
}
