<?php

namespace App\Http\Controllers;

use App\Models\UserLearningProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function level(Request $request): Response
    {
        return Inertia::render('Auth/LevelSelection', [
            'level' => $request->user()->learningProfile?->level,
        ]);
    }

    public function storeLevel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'level' => ['required', Rule::in(array_keys(config('devroad_onboarding.levels', [])))],
        ]);

        $profile = UserLearningProfile::firstOrNew([
            'user_id' => $request->user()->id,
        ]);

        $profile->level = $validated['level'];
        $profile->level_source = 'self_assessed';
        $profile->save();

        return redirect()->route('onboarding.create');
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $profile = $request->user()->learningProfile;

        if (! $profile?->level) {
            return redirect()->route('onboarding.level');
        }

        $categories = $this->assessmentCategories($profile->level);

        return Inertia::render('Auth/Onboarding', [
            'academicLevels' => config('devroad_onboarding.academic_levels'),
            'technologies' => config('devroad_onboarding.technologies'),
            'goals' => config('devroad_onboarding.goals'),
            'categories' => $categories,
            'profile' => [
                'level' => $profile->level,
                'level_source' => $profile->level_source,
                'academic_level' => $profile->academic_level,
                'experience_years' => $profile->experience_years,
                'technologies' => $profile->technologies ?? [],
                'goals' => $profile->goals ?? [],
                'answers' => $this->answersForCategories(
                    $profile->assessment_answers ?? [],
                    $categories
                ),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->learningProfile;

        $validated = $request->validate([
            'level' => ['required', Rule::in(array_keys(config('devroad_onboarding.levels', [])))],
            'academic_level' => ['required', Rule::in(array_keys(config('devroad_onboarding.academic_levels', [])))],
            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
            'technologies' => ['required', 'array', 'min:1', 'max:10'],
            'technologies.*' => ['string', Rule::in(array_keys(config('devroad_onboarding.technologies', [])))],
            'goals' => ['required', 'array', 'min:1', 'max:5'],
            'goals.*' => ['string', Rule::in(array_keys(config('devroad_onboarding.goals', [])))],
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'string', 'max:10'],
        ]);

        $level = $validated['level'];
        $categories = $this->assessmentCategories($level);

        $questions = collect($categories)
            ->flatMap(fn (array $category) => $category['questions'])
            ->values();

        $questionIds = $questions->pluck('id');
        $answers = $validated['answers'];

        abort_unless(
            $questionIds->count() === count($answers)
            && $questionIds->diff(array_keys($answers))->isEmpty(),
            422,
            'L’évaluation ne correspond pas au niveau sélectionné.'
        );

        $scores = [];
        $total = 0;
        $maximum = 0;

        foreach ($categories as $categoryKey => $category) {
            $categoryScore = 0;
            $categoryMaximum = count($category['questions']) * 2;

            foreach ($category['questions'] as $question) {
                $selectedId = $answers[$question['id']] ?? null;
                $option = collect($question['options'])->firstWhere('id', $selectedId);

                abort_unless($option, 422, 'Une réponse d’évaluation est invalide.');

                $categoryScore += (int) $option['score'];
            }

            $scores[$categoryKey] = [
                'label' => $category['label'],
                'score' => $categoryScore,
                'max' => $categoryMaximum,
                'percentage' => $categoryMaximum > 0
                    ? (int) round($categoryScore / $categoryMaximum * 100)
                    : 0,
            ];

            $total += $categoryScore;
            $maximum += $categoryMaximum;
        }

        $percentage = $maximum > 0 ? $total / $maximum * 100 : 0;
        $recommendedLevel = $this->recommendedLevel($level, $percentage);

        $profile ??= new UserLearningProfile([
            'user_id' => $request->user()->id,
        ]);

        $profile->fill([
            'level' => $level,
            'level_source' => $profile->level_source ?: 'self_assessed',
            'academic_level' => $validated['academic_level'],
            'experience_years' => (int) $validated['experience_years'],
            'technologies' => array_values($validated['technologies']),
            'goals' => array_values($validated['goals']),
            'assessment_scores' => [
                'total' => $total,
                'max' => $maximum,
                'percentage' => (int) round($percentage),
                'categories' => $scores,
                'assessed_level' => $level,
                'recommended_level' => $recommendedLevel,
            ],
            'assessment_answers' => $answers,
            'completed_at' => now(),
        ]);

        $profile->save();

        return redirect()->route('dashboard')->with('onboarding_completed', true);
    }

    private function assessmentCategories(string $level): array
    {
        return config("devroad_assessment.levels.{$level}.categories", []);
    }

    private function answersForCategories(array $answers, array $categories): array
    {
        $ids = collect($categories)
            ->flatMap(fn (array $category) => collect($category['questions'])->pluck('id'))
            ->all();

        return array_intersect_key($answers, array_flip($ids));
    }

    private function recommendedLevel(string $selectedLevel, float $percentage): string
    {
        return match ($selectedLevel) {
            'beginner' => match (true) {
                $percentage >= 90 => 'professional',
                $percentage >= 70 => 'intermediate',
                default => 'beginner',
            },
            'intermediate' => match (true) {
                $percentage >= 85 => 'professional',
                $percentage < 40 => 'beginner',
                default => 'intermediate',
            },
            'professional' => $percentage < 40 ? 'intermediate' : 'professional',
            default => $selectedLevel,
        };
    }
}
