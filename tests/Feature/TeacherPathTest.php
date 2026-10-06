<?php

namespace Tests\Feature;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use App\Models\TeachingGroup;
use App\Models\User;
use App\Services\RoadmapGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherPathTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function group(User $teacher, ?string $technology = 'html'): TeachingGroup
    {
        $group = new TeachingGroup(['name' => 'Terminale D', 'technology' => $technology]);
        $group->user_id = $teacher->id;
        $group->join_code = TeachingGroup::generateUniqueCode();
        $group->save();

        return $group;
    }

    public function test_registration_can_create_a_teacher_but_default_is_student(): void
    {
        $payload = ['name' => 'Prof', 'email' => 'prof@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'];

        $this->post('/register', $payload + ['account_type' => 'teacher'])->assertRedirect(route('teacher.dashboard'));
        $this->assertSame('teacher', User::where('email', 'prof@example.com')->first()->role);

        auth()->logout();
        $this->post('/register', ['email' => 'eleve@example.com', 'name' => 'Eleve'] + $payload)->assertRedirect(route('onboarding.level'));
        $this->assertSame('student', User::where('email', 'eleve@example.com')->first()->fresh()->role);
    }

    public function test_role_cannot_be_mass_assigned_from_profile(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->patch('/profile', ['name' => $student->name, 'email' => $student->email, 'role' => 'teacher']);

        $this->assertSame('student', $student->fresh()->role);
    }

    public function test_student_cannot_access_teacher_area_or_create_groups(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/teacher')->assertForbidden();
        $this->actingAs($student)->post('/teacher/groups', ['name' => 'X'])->assertForbidden();
    }

    public function test_student_can_activate_teacher_mode(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/teacher/activate')->assertRedirect(route('teacher.dashboard'));
        $this->assertTrue($user->fresh()->isTeacher());
    }

    public function test_teacher_creates_group_with_unique_code_and_validates_technology(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->post('/teacher/groups', ['name' => 'Classe A', 'technology' => 'laravel'])->assertRedirect();
        $this->actingAs($teacher)->post('/teacher/groups', ['name' => 'Classe B', 'technology' => 'cobol'])->assertSessionHasErrors('technology');

        $group = TeachingGroup::first();
        $this->assertSame($teacher->id, $group->user_id);
        $this->assertMatchesRegularExpression('/^[A-HJKMNP-Z2-9]{8}$/', $group->join_code);
        $this->assertSame(1, TeachingGroup::count());
    }

    public function test_student_joins_with_code_leaves_and_cannot_join_closed_group(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->create();
        $group = $this->group($teacher);

        $this->actingAs($student)->post('/groups/join', ['code' => strtolower(substr($group->join_code, 0, 4)).' - '.substr($group->join_code, 4)])->assertRedirect(route('groups.index'));
        $this->assertTrue($group->students()->whereKey($student->id)->exists());

        // idempotent
        $this->actingAs($student)->post('/groups/join', ['code' => $group->join_code]);
        $this->assertSame(1, $group->students()->count());

        $this->actingAs($student)->delete("/groups/{$group->id}/leave")->assertRedirect();
        $this->assertSame(0, $group->students()->count());

        $group->update(['name' => 'Terminale D']);
        $group->archived_at = now();
        $group->save();
        $this->actingAs($student)->post('/groups/join', ['code' => $group->join_code])->assertSessionHasErrors('code');
        $this->actingAs($student)->post('/groups/join', ['code' => 'ZZZZZZZZ'])->assertSessionHasErrors('code');
    }

    public function test_teacher_cannot_join_as_student(): void
    {
        $teacher = User::factory()->teacher()->create();
        $other = User::factory()->teacher()->create();
        $group = $this->group($other);

        $this->actingAs($teacher)->post('/groups/join', ['code' => $group->join_code])->assertSessionHasErrors('code');
    }

    public function test_only_owner_can_view_manage_or_delete_group(): void
    {
        $owner = User::factory()->teacher()->create();
        $intruder = User::factory()->teacher()->create();
        $group = $this->group($owner);

        $this->actingAs($intruder)->get("/teacher/groups/{$group->id}")->assertForbidden();
        $this->actingAs($intruder)->patch("/teacher/groups/{$group->id}", ['name' => 'Hack'])->assertForbidden();
        $this->actingAs($intruder)->post("/teacher/groups/{$group->id}/code")->assertForbidden();
        $this->actingAs($intruder)->delete("/teacher/groups/{$group->id}")->assertForbidden();
        $this->assertSame('Terminale D', $group->fresh()->name);

        $this->actingAs($owner)->get("/teacher/groups/{$group->id}")->assertOk();
    }

    public function test_regenerating_code_invalidates_old_code(): void
    {
        $teacher = User::factory()->teacher()->create();
        $group = $this->group($teacher);
        $old = $group->join_code;

        $this->actingAs($teacher)->post("/teacher/groups/{$group->id}/code")->assertRedirect();

        $this->assertNotSame($old, $group->fresh()->join_code);
        $student = User::factory()->create();
        $this->actingAs($student)->post('/groups/join', ['code' => $old])->assertSessionHasErrors('code');
    }

    public function test_stats_are_computed_and_hide_emails(): void
    {
        $teacher = User::factory()->teacher()->create();
        $group = $this->group($teacher, 'html');
        $generator = app(RoadmapGenerator::class);

        $alice = User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);
        $group->students()->attach([$alice->id, $bob->id]);

        $roadmapA = $generator->create($alice, ['title' => 'HTML', 'technology' => 'html', 'status' => 'active'], 'html');
        $generator->create($bob, ['title' => 'HTML', 'technology' => 'html', 'status' => 'active'], 'html');

        $roadmapA->steps()->where('position', 1)->update(['status' => RoadmapStep::COMPLETED]);
        $roadmapA->steps()->where('position', 2)->update(['status' => RoadmapStep::IN_PROGRESS]);
        // Bob : inactif depuis 20 jours
        RoadmapStep::whereHas('roadmap', fn ($q) => $q->where('user_id', $bob->id))->update(['updated_at' => now()->subDays(20)]);

        $response = $this->actingAs($teacher)->get("/teacher/groups/{$group->id}")->assertOk();
        $stats = $response->viewData('page')['props']['stats'];

        $this->assertSame(2, $stats['members_count']);
        $this->assertSame(1, $stats['active_last_7_days']);
        $this->assertSame(1, $stats['stalled_count']);
        $alicePayload = collect($stats['members'])->firstWhere('name', 'Alice');
        $this->assertSame((int) round(1 / 7 * 100), $alicePayload['progress']);
        $this->assertSame('Sémantique', $alicePayload['current_step']);
        $this->assertArrayNotHasKey('email', $alicePayload);
        $this->assertNotNull($stats['blocking_step']);
    }

    public function test_removing_student_and_deleting_group(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->create();
        $group = $this->group($teacher);
        $group->students()->attach($student->id);

        $this->actingAs($teacher)->delete("/teacher/groups/{$group->id}/students/{$student->id}")->assertRedirect();
        $this->assertSame(0, $group->students()->count());

        $this->actingAs($teacher)->delete("/teacher/groups/{$group->id}")->assertRedirect(route('teacher.dashboard'));
        $this->assertSame(0, TeachingGroup::count());
        $this->assertNotNull(User::find($student->id));
    }

    public function test_sync_command_updates_existing_roadmaps_without_losing_progress(): void
    {
        $user = User::factory()->create();
        $roadmap = app(RoadmapGenerator::class)->create($user, ['title' => 'HTML', 'technology' => 'html', 'status' => 'active'], 'html');
        $roadmap->steps()->where('position', 1)->update(['status' => RoadmapStep::COMPLETED, 'content' => 'ancien']);

        $this->artisan('devroad:sync-courses')->assertSuccessful();

        $first = $roadmap->steps()->where('position', 1)->first();
        $this->assertSame('completed', $first->status);
        $this->assertGreaterThan(400, str_word_count($first->content));
    }
}
