<?php

namespace App\Application\Survey\Services;

use App\Application\Survey\Exceptions\SurveyAccessDeniedException;
use App\Application\Survey\Exceptions\SurveyAlreadyAnsweredException;
use App\Application\Survey\Exceptions\SurveyNotActiveException;
use App\Application\Survey\Repositories\SurveyAnswerRepository;
use App\Models\Survey;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;

class SurveyAccessService
{
    public function __construct(
        private SurveyAnswerRepository $surveyAnswerRepository
    ) {}

    public function ensureDealerUser(?Authenticatable $user): void
    {
        if (!$user || (string) ($user->role ?? '') !== 'dealer') {
            throw new SurveyAccessDeniedException('Anket sadece bayiler için görüntülenebilir.');
        }
    }

    public function ensureSurveyIsActive(Survey $survey): void
    {
        if (!(bool) $survey->is_active) {
            throw new SurveyNotActiveException('Bu anket şu anda aktif değil.');
        }

        if (!(bool) $survey->use_dates) {
            return;
        }

        $now = now();
        $startAt = $survey->start_at ? Carbon::parse($survey->start_at) : null;
        $endAt = $survey->end_at ? Carbon::parse($survey->end_at) : null;

        if (!$startAt || !$endAt || $now->lt($startAt) || $now->gt($endAt)) {
            throw new SurveyNotActiveException('Bu anket tarih aralığı dışında.');
        }
    }

    public function ensureNotParticipated(int $surveyId, int $dealerId): void
    {
        if ($this->surveyAnswerRepository->hasDealerParticipated($surveyId, $dealerId)) {
            throw new SurveyAlreadyAnsweredException('Bu ankete zaten katıldınız.');
        }
    }
}
