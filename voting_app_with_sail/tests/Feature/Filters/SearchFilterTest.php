<?php

namespace Tests\Feature\Filters;

use Tests\TestCase;
use App\Models\Idea;
use App\Models\User;
use App\Models\Vote;
use App\Models\Status;
use Livewire\Livewire;
use App\Models\Category;
use App\Http\Livewire\IdeasIndex;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * @test
     * @group searchFilter
     */
    public function seaching_works_when_more_than_3_characters()
    {
        $ideaOne = Idea::factory()->create([
            'title' => 'My First Idea',
        ]);

        $ideaTwo = Idea::factory()->create([
            'title' => 'My Second Idea',
        ]);

        $ideaThree = Idea::factory()->create([
            'title' => 'My Third Idea',
        ]);

        Livewire::test(IdeasIndex::class)
            ->set('search', 'Second')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 1
                    && $ideas->first()->title === 'My Second Idea';
            });
    }

    /** 
     * @test
     * @group searchFilter
     */
    public function does_not_perform_search_if_less_than_3_characters()
    {
        $ideaOne = Idea::factory()->create([
            'title' => 'My First Idea',
        ]);

        $ideaTwo = Idea::factory()->create([
            'title' => 'My Second Idea',
        ]);

        $ideaThree = Idea::factory()->create([
            'title' => 'My Third Idea',
        ]);

        Livewire::test(IdeasIndex::class)
            ->set('search', 'ab')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3;
            });
    }

    /** 
     * @test
     * @group searchFilter
     */
    public function search_works_correctly_with_category_filters()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $statusOpen = Status::factory()->create(['name' => 'Open']);

        $ideaOne = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'title' => 'My First Idea',
        ]);

        $ideaTwo = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'title' => 'My Second Idea',
        ]);

        $ideaThree = Idea::factory()->create([
            'category_id' => $categoryTwo->id,
            'title' => 'My Third Idea',
        ]);

        Livewire::test(IdeasIndex::class)
            ->set('category', 'Category 1')
            ->set('search', 'Idea')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2;
            });
    }
}