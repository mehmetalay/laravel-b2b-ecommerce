<?php

namespace App\Application\Survey\Repositories;

use App\Application\Survey\Support\SurveyCacheKeys;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SurveyRepository
{
    public function all(): Collection
    {
        return Cache::rememberForever(SurveyCacheKeys::ALL, function () {
            return Survey::query()
                ->with([
                    'questions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                    'questions.options' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                ])
                ->latest()
                ->get();
        });
    }

    public function findByIdWithQuestions(int $surveyId): ?Survey
    {
        return Cache::rememberForever(SurveyCacheKeys::detail($surveyId), function () use ($surveyId) {
            return Survey::query()
                ->with([
                    'questions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                    'questions.options' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                ])
                ->find($surveyId);
        });
    }

    public function getActiveSurvey(): ?Survey
    {
        return Cache::rememberForever(SurveyCacheKeys::ACTIVE, function () {
            return $this->activeQuery()
                ->with([
                    'questions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                    'questions.options' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                ])
                ->latest()
                ->first();
        });
    }

    public function create(array $payload): Survey
    {
        $survey = Survey::query()->create($payload);
        $this->clearCache($survey);

        return $survey;
    }

    public function update(Survey $survey, array $payload): Survey
    {
        $survey->update($payload);
        $this->clearCache($survey);

        return $survey;
    }

    public function delete(Survey $survey): bool
    {
        $isDeleted = (bool) $survey->delete();
        $this->clearCache($survey);

        return $isDeleted;
    }

    public function clearCache(?Survey $survey = null): void
    {
        $keys = [SurveyCacheKeys::ALL, SurveyCacheKeys::ACTIVE];

        if ($survey) {
            $keys[] = SurveyCacheKeys::detail((int) $survey->id);
        }

        forget_cache_keys($keys);
    }

    private function activeQuery(): Builder
    {
        return Survey::query()
            ->where('is_active', 1)
            ->where(function (Builder $query) {
                $query->where('use_dates', 0)
                    ->orWhere(function (Builder $dateQuery) {
                        $dateQuery->where('use_dates', 1)
                            ->whereDate('start_at', '<=', now())
                            ->whereDate('end_at', '>=', now());
                    });
            });
    }
}

