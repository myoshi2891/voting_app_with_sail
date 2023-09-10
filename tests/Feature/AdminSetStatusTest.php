<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Idea;
use App\Models\User;
use App\Models\Status;
use Livewire\Livewire;
use App\Models\Category;
use App\Http\Livewire\SetStatus;
use App\Jobs\NotifyAllVoters;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminSetStatusTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * @test
     * @group admin
     */
    public function show_page_contains_set_status_livewire_component_when_user_is_admin()
    {
        $user = User::factory()->create([
            'email'  => 'test@example.com',
        ]);

        $idea = Idea::factory()->create();

        $this->actingAs($user)
            ->get(route('idea.show', $idea))
            ->assertSeeLivewire('set-status');
    }

    /** 
     * @test
     * @group admin
     */
    public function show_page_does_not_contain_set_status_livewire_component_when_user_is_not_admin()
    {
        $userNotAdmin = User::factory()->create();

        $idea = Idea::factory()->create();

        $this->actingAs($userNotAdmin)
            ->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('set-status');
    }

    /** 
     * @test
     * @group admin
     */
    public function initial_status_is_set_correctly()
    {
        $user = User::factory()->admin()->create();

        $statusConsidering = Status::factory()->create(['id' => 2, 'name' => 'Considering']);

        $idea = Idea::factory()->create([
            'status_id' => $statusConsidering->id,
        ]);

        Livewire::actingAs($user)
            ->test(SetStatus::class, [
                'idea' => $idea,
            ])
            ->assertSet('status', $statusConsidering->id);
    }

    /** 
     * @test
     * @group admin
     */
    public function can_set_status_correctly()
    {
        $user = User::factory()->admin()->create();

        $statusConsidering = Status::factory()->create(['id' => 2, 'name' => 'Considering']);
        $statusInProgress = Status::factory()->create(['id' => 3, 'name' => 'InProgress']);

        $idea = Idea::factory()->create(['status_id' => $statusConsidering->id,
        ]);

        Livewire::actingAs($user)
            ->test(SetStatus::class, [
                'idea' => $idea,
            ])
            ->set('status', $statusInProgress->id)
            ->call('setStatus')
            ->assertEmitted('statusWasUpdated');

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'status_id' => $statusInProgress->id,
        ]);
    }

    /** 
     * @test
     * @group admin
     */
    public function can_set_status_correctly_while_notifying_all_voters()
    {
        $user = User::factory()->admin()->create();

        $statusConsidering = Status::factory()->create(['id' => 2, 'name' => 'Considering']);
        $statusInProgress = Status::factory()->create(['id' => 3, 'name' => 'InProgress']);

        $idea = Idea::factory()->create(['status_id' => $statusConsidering->id,
        ]);

        Queue::fake();

        Queue::assertNothingPushed();

        Livewire::actingAs($user)
            ->test(SetStatus::class, [
                'idea' => $idea,
            ])
            ->set('status', $statusInProgress->id)
            ->set('notifyAllVoters', true)
            ->call('setStatus')
            ->assertEmitted('statusWasUpdated');

        Queue::assertPushed(NotifyAllVoters::class);
    }
}