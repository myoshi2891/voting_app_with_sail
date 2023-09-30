<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\DeleteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    /** @test */
    public function the_component_can_render()
    {
        $component = Livewire::test(DeleteUser::class);

        $component->assertStatus(200);
    }
}
