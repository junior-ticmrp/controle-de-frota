<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFilterSingleRowTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_filters_render_in_a_single_horizontal_row_with_operational_font_size(): void
    {
        $actor = User::create(['name' => 'Supervisor', 'username' => 'report-row-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'supervisor', 'is_active' => true]);

        $this->actingAs($actor, 'ldap')->get('/relatorios')
            ->assertOk()
            ->assertSee('class="report-filter-row"', false)
            ->assertSee('display:grid', false)
            ->assertSee('grid-template-columns:minmax(130px,1.05fr)', false)
            ->assertSee('Setor/Gabinete Responsável')
            ->assertSee('font-size:13px!important', false)
            ->assertSee('height:38px!important', false)
            ->assertDontSee('overflow-x:auto', false);
    }
}
