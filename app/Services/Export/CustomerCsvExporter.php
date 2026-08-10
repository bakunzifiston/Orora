<?php

namespace App\Services\Export;

use App\Models\Customer;
use App\Services\Export\Concerns\StreamsCsv;
use App\Services\ImportExport\CustomerCsvSchema;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerCsvExporter
{
    use StreamsCsv;

    public function export(Request $request): StreamedResponse
    {
        $headers = CustomerCsvSchema::headers();
        $filename = 'customers-'.now()->format('Y-m-d-His').'.csv';

        $query = Customer::query()
            ->with(['profile', 'contacts'])
            ->when($request->filled('type'), fn ($q) => $q->where('customer_type', $request->input('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';
                $query->where('display_name', 'like', $term)
                    ->orWhere('customer_code', 'like', $term);
            }))
            ->orderBy('display_name');

        return $this->streamCsv($filename, $headers, function ($handle) use ($query): void {
            $query->chunk(200, function ($customers) use ($handle): void {
                foreach ($customers as $customer) {
                    $profile = $customer->profile;
                    $contact = $customer->contacts
                        ->firstWhere('is_primary', true)
                        ?? $customer->contacts->first();

                    fputcsv($handle, [
                        $customer->customer_code,
                        $customer->customer_type,
                        $customer->display_name,
                        $customer->status,
                        $customer->trust_level,
                        $customer->preferred_payment_method,
                        $customer->currency,
                        $customer->notes,
                        $profile?->first_name,
                        $profile?->last_name,
                        $profile?->national_id,
                        optional($profile?->date_of_birth)?->format('Y-m-d'),
                        $profile?->gender,
                        $profile?->organization_name,
                        $profile?->registration_number,
                        $profile?->tax_id,
                        $profile?->license_number,
                        optional($profile?->license_expiry_date)?->format('Y-m-d'),
                        $profile?->website,
                        $profile?->industry,
                        $profile?->number_of_employees,
                        optional($profile?->established_date)?->format('Y-m-d'),
                        $contact?->contact_name,
                        $contact?->role,
                        $contact?->phone,
                        $contact?->email,
                    ]);
                }
            });
        });
    }

    public function template(): StreamedResponse
    {
        return $this->streamCsv('customers-import-template.csv', CustomerCsvSchema::headers(), function ($handle): void {
            fputcsv($handle, CustomerCsvSchema::exampleRow());
        });
    }
}
