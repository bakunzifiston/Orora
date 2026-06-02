<?php

namespace Database\Seeders\Tenant;

use App\Models\AbattoirDispatch;
use App\Models\BreedingRecord;
use App\Models\Certificate;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\FeedInventoryMovement;
use App\Models\HealthRecord;
use App\Models\MilkRecord;
use App\Models\MilkSession;
use App\Models\Movement;
use App\Models\SaleTransaction;
use App\Models\Treatment;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\VetVisit;
use App\Services\BreedingService;
use App\Services\CustomerService;
use App\Services\MilkSessionService;
use App\Services\MilkStorageService;
use App\Services\SaleTransactionService;
use Database\Seeders\Tenant\Concerns\SeedsHistoricalDemo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ComprehensiveDemoSeeder extends Seeder
{
    use SeedsHistoricalDemo;

    public function __construct(
        private readonly CustomerService $customers,
        private readonly SaleTransactionService $sales,
        private readonly BreedingService $breeding,
        private readonly MilkStorageService $milkStorage,
        private readonly MilkSessionService $milkSessions,
    ) {}

    protected function customerService(): CustomerService
    {
        return $this->customers;
    }

    protected function saleService(): SaleTransactionService
    {
        return $this->sales;
    }

    protected function breedingService(): BreedingService
    {
        return $this->breeding;
    }

    protected function milkStorageService(): MilkStorageService
    {
        return $this->milkStorage;
    }

    protected function milkSessionService(): MilkSessionService
    {
        return $this->milkSessions;
    }

    public function run(): void
    {
        if (Farm::query()->exists() && ! config('demo.seed_force')) {
            $this->command?->warn('Tenant already has data. Use: php artisan orora:seed-demo --fresh');

            return;
        }

        $this->initHistoricalDemo();
        $this->seedDemoUser();

        $this->command?->info('Seeding historical demo data ('
            .config('demo.date_start').' → '.config('demo.date_end').', '
            .$this->minRecords.'+ records per module)…');

        $this->seedHistoricalFoundation();
        $this->seedHistoricalFeedingVolume();
        $this->seedHistoricalHealthVolume();
        $this->seedHistoricalMilkVolume();
        $this->seedHistoricalBreedingVolume();
        $this->seedHistoricalSalesVolume();
        $this->seedHistoricalAbattoirVolume();
        $this->seedHistoricalExpenseVolume();
        $this->seedHistoricalComplianceVolume();
        $this->syncHistoricalCustomerBalances();

        $this->command?->newLine();
        $this->command?->info('Demo seed complete.');
        $this->command?->table(
            ['Entity', 'Count'],
            [
                ['Farms', $this->ctx->farms->count()],
                ['Animals', $this->ctx->animals->count()],
                ['Customers', $this->ctx->customers->count()],
                ['Employees', $this->ctx->employees->count()],
                ['Feed movements', FeedInventoryMovement::count()],
                ['Vaccinations', Vaccination::count()],
                ['Treatments', Treatment::count()],
                ['Vet visits', VetVisit::count()],
                ['Health records', HealthRecord::count()],
                ['Milk sessions', MilkSession::count()],
                ['Milk records', MilkRecord::count()],
                ['Breeding records', BreedingRecord::count()],
                ['Sale transactions', SaleTransaction::count()],
                ['Abattoir dispatches', AbattoirDispatch::count()],
                ['Expenses', Expense::count()],
                ['Certificates', Certificate::count()],
                ['Movements', Movement::count()],
            ],
        );
        $this->command?->info('Login: '.config('demo.user.email').' / '.config('demo.user.password'));
    }

    private function seedDemoUser(): void
    {
        $credentials = config('demo.user');

        $this->ctx->user = User::query()->updateOrCreate(
            ['email' => $credentials['email']],
            [
                'name' => $credentials['name'],
                'password' => Hash::make($credentials['password']),
                'email_verified_at' => now(),
            ],
        );

        Auth::login($this->ctx->user);
    }
}
