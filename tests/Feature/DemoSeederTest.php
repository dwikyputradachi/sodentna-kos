<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\RentalApplication;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WaitingListEntry;
use App\Support\InvoiceState;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedDemo(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $this->seed(DemoSeeder::class);
    }

    public function test_it_refuses_to_run_outside_local(): void
    {
        $this->seed(DemoSeeder::class); // testing environment
        $this->assertDatabaseCount('tenants', 0);
    }

    public function test_demo_data_covers_every_billing_state_and_room_status(): void
    {
        $this->seedDemo();

        $this->assertSame(9, Tenant::count());                 // 7 active + 1 alumnus + 1 reserved
        $this->assertSame(1, Tenant::where('status', 'former')->count());
        $this->assertSame(7, Room::where('status', 'occupied')->count());
        $this->assertSame(1, Room::where('status', 'reserved')->count());
        $this->assertSame(1, Room::where('status', 'maintenance')->count());
        $this->assertSame(2, RentalApplication::where('status', 'pending')->count());
        $this->assertSame(2, WaitingListEntry::count());

        $labels = Invoice::where('status', '!=', 'void')->get()->map(fn ($i) => InvoiceState::of($i)['label'])->unique()->all();
        foreach (['H-2', 'Jatuh Tempo', 'Terlambat', 'Lunas', 'Belum Jatuh Tempo'] as $want) {
            $this->assertContains($want, $labels);
        }
        $this->assertSame(Invoice::where('status', 'paid')->count(), \App\Models\Payment::count());
        $this->assertSame(\App\Models\Payment::count(), \App\Models\Receipt::count());
    }

    public function test_running_it_twice_does_not_duplicate_anything(): void
    {
        $this->seedDemo();
        $this->seed(DemoSeeder::class);

        $this->assertSame(9, Tenant::count());
        $this->assertSame(2, WaitingListEntry::count());
    }

    public function test_owner_and_tenant_demo_logins_can_open_the_main_pages(): void
    {
        $this->seedDemo();
        $owner = User::where('phone', '6281300000001')->firstOrFail();

        $this->actingAs($owner);
        foreach (['owner.dashboard', 'owner.rooms', 'owner.tenants', 'owner.billing', 'owner.applications', 'owner.waitlist', 'owner.whatsapp', 'owner.finance', 'owner.reports', 'owner.documents'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('owner.tenants', ['tab' => 'alumni']))->assertOk()->assertSee('Ahmad Fauzi');
        $this->get(route('owner.finance'))->assertSee('Listrik & air')->assertSee('Saldo akhir kas');
        $this->get(route('owner.tenants', ['tab' => 'dipesan']))->assertOk()->assertSee('Sari Ramadhani');
        $this->get(route('owner.billing'))->assertSee('Alma Rikhafinata')->assertSee('Terlambat');

        auth()->logout();
        $alma = User::where('phone', '6281234567001')->firstOrFail();
        $this->actingAs($alma)->get('/penghuni')->assertOk()->assertSee('101');
        $this->get('/penghuni/tagihan')->assertOk()->assertSee('INV-');
        $this->get('/penghuni/riwayat-pembayaran')->assertOk();
    }
}
