<?php

namespace Database\Seeders\Support;

use App\Models\Animal;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ExpenseVendor;
use App\Models\Farm;
use App\Models\FeedSupplier;
use App\Models\FeedType;
use App\Models\Livestock;
use App\Models\MilkStorage;
use App\Models\User;
use Illuminate\Support\Collection;

class DemoSeedContext
{
    public User $user;

    /** @var Collection<int, Farm> */
    public Collection $farms;

    /** @var array<string, Farm> */
    public array $farmsByKey = [];

    /** @var Collection<int, Livestock> */
    public Collection $herds;

    /** @var Collection<int, Animal> */
    public Collection $animals;

    /** @var Collection<int, Customer> */
    public Collection $customers;

    /** @var Collection<int, Employee> */
    public Collection $employees;

    /** @var Collection<int, ExpenseVendor> */
    public Collection $vendors;

    /** @var Collection<int, FeedSupplier> */
    public Collection $feedSuppliers;

    /** @var Collection<int, FeedType> */
    public Collection $feedTypes;

    /** @var Collection<int, MilkStorage> */
    public Collection $milkTanks;

    public function __construct()
    {
        $this->farms = collect();
        $this->herds = collect();
        $this->animals = collect();
        $this->customers = collect();
        $this->employees = collect();
        $this->vendors = collect();
        $this->feedSuppliers = collect();
        $this->feedTypes = collect();
        $this->milkTanks = collect();
    }

    public function farm(string $key): Farm
    {
        return $this->farmsByKey[$key];
    }

    /** @return list<Animal> */
    public function lactatingCows(Farm $farm): array
    {
        return $this->animals
            ->filter(fn (Animal $a) => (int) $a->farm_id === (int) $farm->id
                && strcasecmp((string) $a->production_status, 'Lactating') === 0)
            ->values()
            ->all();
    }
}
