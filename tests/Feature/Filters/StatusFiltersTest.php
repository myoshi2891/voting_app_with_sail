<?php

namespace Tests\Feature\Filters;

use App\Http\Livewire\IdeasIndex;
use Tests\TestCase;
use App\Models\Idea;
use App\Models\User;
use App\Models\Status;
use Livewire\Livewire;
use App\Models\Category;
use App\Http\Livewire\StatusFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatusFiltersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group status
     */
    public function index_page_contains_status_filters_livewire_component()
    {
        Idea::factory()->create();

        $this->get(route('idea.index'))
            ->assertSeeLivewire('status-filters');
    }

    /**
     * @test
     * @group status
     */
    public function show_page_contains_status_filters_livewire_component()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertSeeLivewire('status-filters');
    }

    /**
     * @test
     * @group status
     */
    public function shows_correct_status_count()
    {
        $statusImplemented = Status::factory()->create(['id' => 4, 'name' => 'implemented']);

        Idea::factory()->create([
            'status_id' => $statusImplemented->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusImplemented->id,
        ]);

        Livewire::test(StatusFilters::class)
            ->assertSee('All Ideas (2)')
            ->assertSee('Implemented (2)');
    }

    /**
     * @test
     * @group status
     */
    public function filtering_works_when_query_string_in_place()
    {
        $statusConsidering = Status::factory()->create(['name' => 'Considering']);
        $statusInProgress = Status::factory()->create(['name' => 'In Progress']);

        Idea::factory()->create([
            'status_id' => $statusConsidering->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusConsidering->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusInProgress->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusInProgress->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusInProgress->id,
        ]);

        Livewire::withQueryParams(['status' => 'In Progress'])
            ->test(IdeasIndex::class)
            ->assertViewHas('ideas', function ($ideas) {
                // dd($ideas);
                return
                    $ideas->count() === 3
                    &&
                    $ideas->first()->status->name === 'In Progress';
            });
    }

    /**
     * @test
     * @group status
     */
    public function show_page_does_not_show_selected_status()
    {
        $statusImplemented = Status::factory()->create(['id' => 4, 'name' => 'implemented']);

        $idea = Idea::factory()->create([
            'status_id' => $statusImplemented->id,
        ]);

        $response = $this->get(route('idea.show', $idea));

        $response->assertDontSee("border-blue text-gray-900");
    }

    /**
     * @test
     * @group status
     */
    public function index_page_shows_selected_status()
    {
        $statusImplemented = Status::factory()->create(['id' => 4, 'name' => 'implemented']);

        Idea::factory()->create([
            'status_id' => $statusImplemented->id,
        ]);

        $response = $this->get(route('idea.index'));

        $response->assertSee("border-blue text-gray-900");
    }
}