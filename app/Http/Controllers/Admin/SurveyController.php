<?php

namespace App\Http\Controllers\Admin;

use App\Application\Survey\Actions\BuildSurveyEditPayloadAction;
use App\Application\Survey\Actions\BuildSurveyResultsPayloadAction;
use App\Application\Survey\Actions\CreateAdminSurveyAction;
use App\Application\Survey\Actions\DeleteAdminSurveyAction;
use App\Application\Survey\Actions\ListAdminSurveysAction;
use App\Application\Survey\Actions\UpdateAdminSurveyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyRequest;
use App\Models\Survey;

class SurveyController extends Controller
{
    public function __construct(
        private ListAdminSurveysAction $listAdminSurveysAction,
        private CreateAdminSurveyAction $createAdminSurveyAction,
        private UpdateAdminSurveyAction $updateAdminSurveyAction,
        private DeleteAdminSurveyAction $deleteAdminSurveyAction,
        private BuildSurveyEditPayloadAction $buildSurveyEditPayloadAction,
        private BuildSurveyResultsPayloadAction $buildSurveyResultsPayloadAction
    ) {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $items = ($this->listAdminSurveysAction)();

        return view('backend.pages.settings.surveys.index', compact('items'));
    }

    public function create()
    {
        return view('backend.pages.settings.surveys.create');
    }

    public function store(SurveyRequest $request)
    {
        $survey = ($this->createAdminSurveyAction)($request, auth('admin')->id());

        return response()->json([
            'status' => 'success',
            'message' => 'Anket başarıyla oluşturuldu.',
            'redirect' => route('admin.surveys.edit', $survey->id),
        ]);
    }

    public function show($id)
    {
    }

    public function edit(Survey $survey)
    {
        $surveyJson = ($this->buildSurveyEditPayloadAction)($survey);

        return view('backend.pages.settings.surveys.edit', compact('survey', 'surveyJson'));
    }

    public function update(SurveyRequest $request, Survey $survey)
    {
        ($this->updateAdminSurveyAction)($request, $survey);

        return response()->json([
            'status' => 'success',
            'message' => 'Anket başarıyla güncellendi.',
        ]);
    }

    public function destroy(Survey $survey)
    {
        ($this->deleteAdminSurveyAction)($survey);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function results(Survey $survey)
    {
        $payload = ($this->buildSurveyResultsPayloadAction)($survey);

        return view('backend.pages.settings.surveys.results', $payload);
    }
}
