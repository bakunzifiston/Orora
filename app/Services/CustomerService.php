<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCredit;
use App\Models\CustomerLog;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function generateCustomerCode(): string
    {
        $seq = Customer::withTrashed()->count() + 1;

        return sprintf('CUS-%04d', $seq);
    }

    public function create(array $customerData, array $profileData = [], array $contactData = null): Customer
    {
        return DB::transaction(function () use ($customerData, $profileData, $contactData) {
            $customer = Customer::create([
                ...$customerData,
                'customer_code' => $customerData['customer_code'] ?? $this->generateCustomerCode(),
                'created_by' => auth()->id(),
            ]);

            $customer->profile()->create($profileData);

            CustomerCredit::create([
                'customer_id' => $customer->id,
                'credit_limit' => 0,
                'outstanding_balance' => 0,
            ]);

            if ($contactData && ! empty($contactData['contact_name'])) {
                $customer->contacts()->create([
                    ...$contactData,
                    'is_primary' => true,
                ]);
            }

            $this->log($customer, 'created', null, $customer->only(['display_name', 'customer_type', 'status']));

            return $customer->fresh(['profile', 'credit', 'contacts']);
        });
    }

    public function update(Customer $customer, array $customerData, array $profileData = []): Customer
    {
        return DB::transaction(function () use ($customer, $customerData, $profileData) {
            $old = $customer->only(['display_name', 'status', 'trust_level', 'customer_type']);

            $customer->update($customerData);

            if ($customer->profile) {
                $customer->profile->update($profileData);
            } else {
                $customer->profile()->create($profileData);
            }

            $new = $customer->fresh()->only(['display_name', 'status', 'trust_level', 'customer_type']);

            if ($old['status'] !== $new['status'] && $new['status'] === 'blacklisted') {
                $this->log($customer, 'blacklisted', $old, $new, 'Customer blacklisted.');
            } elseif ($old !== $new) {
                $this->log($customer, 'updated', $old, $new);
            }

            return $customer->fresh(['profile', 'credit', 'contacts', 'addresses']);
        });
    }

    public function syncOutstandingBalance(Customer $customer): void
    {
        $balance = $customer->saleTransactions()
            ->whereNotIn('sale_status', ['cancelled', 'draft'])
            ->withSum('payments as paid_total', 'amount_paid')
            ->get()
            ->sum(fn ($transaction) => max(0, (float) $transaction->total_amount - (float) ($transaction->paid_total ?? 0)));

        $credit = $customer->credit ?? $customer->credit()->create([
            'credit_limit' => 0,
            'outstanding_balance' => 0,
        ]);

        $oldBalance = (float) $credit->outstanding_balance;

        if ($oldBalance !== $balance) {
            $credit->update(['outstanding_balance' => $balance]);
            $this->log($customer, 'credit_changed', ['outstanding_balance' => $oldBalance], ['outstanding_balance' => $balance]);
        }
    }

    public function syncOutstandingBalanceById(?int $customerId): void
    {
        if (! $customerId) {
            return;
        }

        $customer = Customer::query()->find($customerId);

        if ($customer) {
            $this->syncOutstandingBalance($customer);
        }
    }

    public function updateCredit(Customer $customer, array $data): CustomerCredit
    {
        $credit = $customer->credit ?? $customer->credit()->create([
            'credit_limit' => 0,
            'outstanding_balance' => 0,
        ]);

        $old = $credit->only(['credit_limit', 'payment_terms']);

        $credit->update($data);

        $this->log($customer, 'credit_changed', $old, $credit->fresh()->only(['credit_limit', 'payment_terms']));

        return $credit->fresh();
    }

    public function log(
        Customer $customer,
        string $actionType,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null,
    ): CustomerLog {
        return CustomerLog::create([
            'customer_id' => $customer->id,
            'action_type' => $actionType,
            'action_by' => auth()->id(),
            'action_at' => now(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => $notes,
        ]);
    }

    public function profileFieldsForType(string $type): array
    {
        return match ($type) {
            'individual' => ['first_name', 'last_name', 'national_id', 'date_of_birth', 'gender'],
            default => ['organization_name', 'registration_number', 'tax_id', 'license_number', 'license_expiry_date', 'website', 'industry', 'number_of_employees', 'established_date'],
        };
    }
}
