<?php

namespace Tests\Feature;

use App\Models\Category;
use Tests\TestCase;
use App\Models\Idea;
use App\Models\Status;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowIdeasTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * @test
     * @group idea
     */
    public function list_of_ideas_shows_on_main_page()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $statusOpen = Status::factory()->create(['name' => "Open"]);
        $statusConsidering = Status::factory()->create(['name' => "Considering"]);

        $ideaOne = Idea::factory()->create([
            'title' => 'My First Idea',
            "category_id" => $categoryOne->id,
            "status_id" => $statusOpen->id,
            'description' => 'Description of my first idea',
        ]);

        $ideaTwo = Idea::factory()->create([
            'title' => 'My Second Idea',
            "category_id" => $categoryTwo->id,
            "status_id" => $statusConsidering->id,
            'description' => 'Description of my second idea',
        ]);

        $response = $this->get(route('idea.index'));

        $response->assertSuccessful();
        $response->assertSee($ideaOne->title);
        $response->assertSee($ideaOne->description);
        $response->assertSee($categoryOne->name);
        $response->assertSee('<div class="open text-xxs font-bold uppercase leading-none rounded-full text-center w-full h-7 py-2 px-4">
                                Open
                            </div>', false);
        $response->assertSee($ideaTwo->title);
        $response->assertSee($ideaTwo->description);
        $response->assertSee($categoryTwo->name);
        $response->assertSee('<div class="considering text-xxs font-bold uppercase leading-none rounded-full text-center w-full h-7 py-2 px-4">
                                Considering
                            </div>', false);

    }

    /** 
     * @test
     * @group idea
     */
    public function single_idea_shows_corretly_on_the_show_page()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);

        $statusOpen = Status::factory()->create(['name' => "Open"]);

        $idea = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'title' => 'My First Idea',
            "status_id" => $statusOpen->id,
            'description' => 'Description of my first idea',
        ]);

        $response = $this->get(route('idea.show', $idea));

        $response->assertSuccessful();
        $response->assertSee($idea->title);
        $response->assertSee($idea->description);
        $response->assertSee('<div class="open text-xxs font-bold uppercase leading-none rounded-full text-center w-full h-7 py-2 px-4">Open</div>', false);
    }

    /** 
     * @test
     * @group idea
     */
    public function ideas_pagination_works()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);

        $statusOpen = Status::factory()->create(['name' => "Open"]);

        Idea::factory(Idea::PAGINATION_COUNT + 1)->create([
            'category_id' => $categoryOne->id,
            "status_id" => $statusOpen->id,
        ]);

        $ideaOne = Idea::find(1);
        $ideaOne->title = 'My First Idea';
        $ideaOne->save();

        $ideaEleven = Idea::find(11);
        $ideaEleven->title = 'My Eleventh Idea';
        $ideaEleven->save();

        $response = $this->get('/');

        $response->assertSee($ideaOne->title);
        $response->assertDontSee($ideaEleven->title);

        $response = $this->get('/?page=2');

        $response->assertSee($ideaEleven->title);
        $response->assertDontSee($ideaOne->title);
    }

    /** 
     * @test
     * @group idea
     */
    public function same_idea_title_different_slugs()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);

        $statusOpen = Status::factory()->create(['name' => "Open"]);

        $ideaOne = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            "status_id" => $statusOpen->id,
            'title' => 'My First Idea',
            'description' => 'Description of my first idea',
        ]);

        $ideaTwo = Idea::factory()->create([
            'category_id' => $categoryOne->id,
            "status_id" => $statusOpen->id,
            'title' => 'My First Idea',
            'description' => 'Another description of my first idea',
        ]);

        $response = $this->get(route('idea.show', $ideaOne));

        $response->assertSuccessful();
        $this->assertTrue(request()->path() === 'ideas/my-first-idea');

        $response = $this->get(route('idea.show', $ideaTwo));

        $response->assertSuccessful();
        $this->assertTrue(request()->path() === 'ideas/my-first-idea-2');
    }
}
