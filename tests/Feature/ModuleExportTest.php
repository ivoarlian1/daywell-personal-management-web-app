<?php

namespace Tests\Feature;

use App\Models\DiaryEntry;
use App\Models\Schedule;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ModuleExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_diary_export_only_passes_the_signed_in_users_records_to_the_pdf(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        DiaryEntry::query()->create([
            'user_id' => $user->id,
            'entry_date' => '2026-10-13',
            'title' => 'My private reflection',
            'content' => 'A moment I want to remember.',
        ]);
        DiaryEntry::query()->create([
            'user_id' => $otherUser->id,
            'entry_date' => '2026-10-13',
            'title' => 'Different account only',
            'content' => 'Another account private content.',
        ]);

        $exportRows = [];
        $pdf = Mockery::mock(DomPdfDocument::class);
        $pdf->shouldReceive('setPaper')->once()->with('a4')->andReturnSelf();
        $pdf->shouldReceive('download')->once()->andReturn(response('%PDF-test', 200, ['Content-Type' => 'application/pdf']));
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('exports.module', Mockery::on(function (array $viewData) use (&$exportRows): bool {
                $exportRows = $viewData['rows'];

                return true;
            }))
            ->andReturn($pdf);

        $response = $this->actingAs($user)->get(route('exports.download', 'diary'));

        $response->assertOk();
        $this->assertCount(1, $exportRows);
        $this->assertSame('My private reflection', $exportRows[0]['title']);
        $this->assertStringNotContainsString('Another account private content.', serialize($exportRows));
    }

    public function test_diary_export_returns_a_valid_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('exports.download', 'diary'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_diary_export_handles_unicode_long_text_and_multiple_pages(): void
    {
        $user = User::factory()->create();
        $longText = 'Café — 東京 — naïve. '.str_repeat('A detailed reflection for a long document. ', 100);

        for ($day = 1; $day <= 15; $day++) {
            DiaryEntry::query()->create([
                'user_id' => $user->id,
                'entry_date' => sprintf('2026-10-%02d', $day),
                'title' => 'Reflection '.$day,
                'content' => $longText,
            ]);
        }

        $response = $this->actingAs($user)->get(route('exports.download', 'diary'));
        $pdf = $response->getContent();
        preg_match_all('~/Type\s*/Page\b~', $pdf, $pages);

        $response->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(1, count($pages[0]));
    }

    public function test_schedule_pdf_date_filter_uses_the_users_local_date_and_only_owned_schedules(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Jakarta']);
        $otherUser = User::factory()->create(['timezone' => 'Asia/Jakarta']);
        Schedule::query()->create([
            'user_id' => $user->id,
            'title' => 'Starts on local filter date',
            'description' => null,
            'start_at' => '2026-10-13 17:00:00',
            'end_at' => '2026-10-13 18:00:00',
            'timezone' => 'Asia/Jakarta',
        ]);
        Schedule::query()->create([
            'user_id' => $user->id,
            'title' => 'Starts after local filter date',
            'description' => null,
            'start_at' => '2026-10-14 17:00:00',
            'end_at' => '2026-10-14 18:00:00',
            'timezone' => 'Asia/Jakarta',
        ]);
        Schedule::query()->create([
            'user_id' => $otherUser->id,
            'title' => 'Another users schedule',
            'description' => null,
            'start_at' => '2026-10-13 17:00:00',
            'end_at' => '2026-10-13 18:00:00',
            'timezone' => 'Asia/Jakarta',
        ]);

        $exportRows = [];
        $pdf = Mockery::mock(DomPdfDocument::class);
        $pdf->shouldReceive('setPaper')->once()->with('a4')->andReturnSelf();
        $pdf->shouldReceive('download')->once()->andReturn(response('%PDF-test', 200, ['Content-Type' => 'application/pdf']));
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('exports.module', Mockery::on(function (array $viewData) use (&$exportRows): bool {
                $exportRows = $viewData['rows'];

                return true;
            }))
            ->andReturn($pdf);

        $this->actingAs($user)
            ->get(route('exports.download', ['module' => 'schedules', 'from' => '2026-10-14', 'to' => '2026-10-14']))
            ->assertOk();

        $this->assertCount(1, $exportRows);
        $this->assertSame('Starts on local filter date', $exportRows[0]['title']);
    }

    public function test_guest_cannot_export_private_records(): void
    {
        $response = $this->get(route('exports.download', 'diary'));

        $response->assertRedirect(route('login'));
    }
}
