<?php
namespace Tests\Feature;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
class PaginationRenderingTest extends TestCase
{
 public function test_compact_pagination_uses_fixed_size_text_controls_without_svg(): void { $paginator=new LengthAwarePaginator(range(1,20),40,20,2,['path'=>'/teste']); $html=view('components.compact-pagination',['paginator'=>$paginator])->render(); $this->assertStringContainsString('Anterior',$html); $this->assertStringContainsString('Próxima',$html); $this->assertStringContainsString('height:34px',$html); $this->assertStringNotContainsString('<svg',$html); }
 public function test_default_pagination_template_has_no_svg_arrow_markup(): void { $template=file_get_contents(resource_path('views/vendor/pagination/tailwind.blade.php')); $this->assertStringContainsString('Anterior',$template); $this->assertStringContainsString('Próxima',$template); $this->assertStringNotContainsString('<svg',$template); }
}
