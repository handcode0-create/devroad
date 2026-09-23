<?php

namespace App\Http\Controllers;

use App\Models\UserLearningProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $profile = $request->user()->learningProfile;

        $categories = collect(config('devroad_onboarding.categories'))
            ->map(fn (array $category) => [
                'label' => $category['label'],
                'questions' => collect($category['questions'])->map(fn (array $question) => [
                    'id' => $question['id'],
                    'question' => $question['question'],
                    'options' => collect($question['options'])->map(fn (array $option) => [
                        'id' => $option['id'],
                        'label' => $option['label'],
                    ])->values()->all(),
                ])->values()->all(),
            ])->all();

        return Inertia::render('Auth/Onboarding', [
            'academicLevels' => config('devroad_onboarding.academic_levels'),
            'technologies' => config('devroad_onboarding.technologies'),
            'goals' => config('devroad_onboarding.goals'),
            'categories' => $categories,
            'profile' => $profile ? [
                'academic_level' => $profile->academic_level,
                'experience_years' => $profile->experience_years,
                'technologies' => $profile->technologies ?? [],
                'goals' => $profile->goals ?? [],
                'answers' => $profile->assessment_answers ?? [],
            ] : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $categories = config('devroad_onboarding.categories', []);
        $questionIds = collect($categories)
            ->flatMap(fn (array $category) => collect($category['questions'])->pluck('id'))
            ->values();

        $request->validate([
            'academic_level' => ['required', Rule::in(array_keys(config('devroad_onboarding.academic_levels', [])))],
            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
            'technologies' => ['required', 'array', 'min:1', 'max:10'],
            'technologies.*' => ['string', Rule::in(array_keys(config('devroad_onboarding.technologies', [])))],
            'goals' => ['required', 'array', 'min:1', 'max:5'],
            'goals.*' => ['string', Rule::in(array_keys(config('devroad_onboarding.goals', [])))],
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'string', 'max:10'],
        ]);

        $answers = $request->input('answers', []);
        abort_unless($questionIds->diff(array_keys($answers))->isEmpty(), 422, 'L’évaluation doit être entièrement complétée.');

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
                'percentage' => $categoryMaximum > 0 ? (int) round($categoryScore / $categoryMaximum * 100) : 0,
            ];

            $total += $categoryScore;
            $maximum += $categoryMaximum;
        }

        $percentage = $maximum > 0 ? $total / $maximum * 100 : 0;
        $level = match (true) {
            $percentage >= 70 => 'professional',
            $percentage >= 40 => 'intermediate',
            default => 'beginner',
        };

        UserLearningProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'academic_level' => $request->string('academic_level')->toString(),
                'level' => $level,
                'level_source' => 'assessment',
                'experience_years' => (int) $request->input('experience_years'),
                'technologies' => array_values($request->input('technologies')),
                'goals' => array_values($request->input('goals')),
                'assessment_scores' => [
                    'total' => $total,
                    'max' => $maximum,
                    'percentage' => (int) round($percentage),
                    'categories' => $scores,
                ],
                'assessment_answers' => $answers,
                'completed_at' => now(),
            ]
        );

        return redirect()->route('dashboard')->with('onboarding_completed', true);
    }
}
