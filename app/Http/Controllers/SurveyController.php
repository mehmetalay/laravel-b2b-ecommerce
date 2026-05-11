<?php

namespace App\Http\Controllers;

use App\Application\Survey\Actions\{ShowSurveyAction, SubmitSurveyAction};
use App\Application\Survey\Exceptions\{SurveyAccessDeniedException, SurveyAlreadyAnsweredException, SurveyNotActiveException};
use App\Application\Survey\Services\SurveyWorkflowService;
use App\Http\Requests\SurveyAnswerRequest;
use App\Models\Survey;
use Illuminate\Validation\ValidationException;
use Throwable;

class SurveyController extends Controller
{
    public function __construct(
        private ShowSurveyAction $showSurveyAction,
        private SubmitSurveyAction $submitSurveyAction,
        private SurveyWorkflowService $surveyWorkflowService
    ) {}

    public function show(Survey $survey)
    {
        try {
            $survey = ($this->showSurveyAction)($survey, auth('web')->user());

            return view('surveys.show', compact('survey'));
        } catch (SurveyAccessDeniedException $e) {
            abort(404);
        } catch (SurveyNotActiveException|SurveyAlreadyAnsweredException $e) {
            return redirect()->route('index')->with('warning', $e->getMessage());
        } catch (Throwable $e) {
            $this->surveyWorkflowService->reportException('show', $e);

            return redirect()->route('index')->with('warning', 'Anket şu anda görüntülenemiyor.');
        }
    }

    public function submit(SurveyAnswerRequest $request, Survey $survey)
    {
        $dealerId = (int) auth('web')->user()->current_account_id;

        try {
            ($this->submitSurveyAction)($request, $survey, $dealerId, auth('web')->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Teşekkürler! Ankete katılımınız kaydedildi.',
            ]);
        } catch (SurveyAlreadyAnsweredException $e) {
            return response()->json([
                'status' => 'warning',
                'message' => $e->getMessage(),
            ]);
        } catch (SurveyNotActiveException $e) {
            return response()->json([
                'status' => 'warning',
                'message' => $e->getMessage(),
            ], 400);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (SurveyAccessDeniedException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 403);
        } catch (Throwable $e) {
            $this->surveyWorkflowService->reportException('submit', $e);

            return response()->json([
                'status' => 'error',
                'message' => 'Anket cevabı kaydedilemedi. Lütfen tekrar deneyiniz.',
            ], 400);
        }
    }
}
