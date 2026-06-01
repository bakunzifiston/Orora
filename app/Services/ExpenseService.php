<?php

namespace App\Services;

use App\Models\BirthRecord;
use App\Models\BreedingRecord;
use App\Models\Expense;
use App\Models\PregnancyCheck;
use App\Models\ExpenseCategory;
use App\Models\ExpenseVendor;
use App\Models\FeedInventoryMovement;
use App\Models\Treatment;
use App\Models\Vaccination;
use App\Models\VetVisit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseService
{
    public function syncFromRequest(Request $request, Model $source, string $categoryCode, array $context): ?Expense
    {
        if (! $request->boolean('log_expense') || ! $request->filled('expense_amount')) {
            $source->expense()?->delete();

            return null;
        }

        $category = ExpenseCategory::query()->where('code', $categoryCode)->firstOrFail();

        $data = [
            'farm_id' => $context['farm_id'] ?? null,
            'animal_id' => $context['animal_id'] ?? null,
            'livestock_id' => $context['livestock_id'] ?? null,
            'expense_category_id' => $category->id,
            'expense_vendor_id' => $this->resolveVendorId($request),
            'expense_date' => $context['expense_date'],
            'amount' => $request->input('expense_amount'),
            'currency' => $request->input('expense_currency', 'RWF'),
            'payment_method' => $request->input('expense_payment_method'),
            'paid_by' => $request->input('expense_paid_by'),
            'title' => $context['title'] ?? null,
            'notes' => $request->input('expense_notes'),
            'status' => 'paid',
            'source_type' => $source::class,
            'source_id' => $source->getKey(),
        ];

        $expense = $source->expense()->first();

        if ($expense) {
            $expense->update($data);
        } else {
            $expense = Expense::create($data);
        }

        if ($request->hasFile('expense_attachment')) {
            $this->storeAttachment($request, $expense);
        }

        return $expense->fresh();
    }

    public function createForMovement(FeedInventoryMovement $movement, Request $request, array $context): ?Expense
    {
        if ($movement->movement_type !== 'purchase' || ! $request->boolean('log_expense') || ! $request->filled('expense_amount')) {
            $movement->expense()?->delete();

            return null;
        }

        return $this->syncFromRequest($request, $movement, 'feed.purchase', $context);
    }

    public function deleteForSource(Model $source): void
    {
        $expense = $source->expense()->first();

        if (! $expense) {
            return;
        }

        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }

        $expense->delete();
    }

    private function resolveVendorId(Request $request): ?int
    {
        if ($request->filled('expense_vendor_id')) {
            return (int) $request->input('expense_vendor_id');
        }

        $name = $request->input('expense_vendor_name');

        if (! $name) {
            return null;
        }

        $vendor = ExpenseVendor::query()->firstOrCreate(
            ['name' => $name],
            ['is_active' => true],
        );

        return $vendor->id;
    }

    private function storeAttachment(Request $request, Expense $expense): void
    {
        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }

        $path = $request->file('expense_attachment')->store('expenses/'.$expense->id, 'public');
        $expense->update(['attachment_path' => $path]);
    }

    public static function vaccinationContext(Vaccination $vaccination): array
    {
        return [
            'farm_id' => $vaccination->farm_id,
            'animal_id' => $vaccination->animal_id,
            'expense_date' => $vaccination->vaccination_date,
            'title' => 'Vaccination: '.$vaccination->vaccine_name,
            'default_vendor_name' => $vaccination->veterinary_clinic ?: $vaccination->veterinarian_name,
        ];
    }

    public static function treatmentContext(Treatment $treatment): array
    {
        return [
            'farm_id' => $treatment->farm_id,
            'animal_id' => $treatment->animal_id,
            'expense_date' => $treatment->start_date,
            'title' => 'Treatment: '.$treatment->disease_name.' — '.$treatment->medicine_name,
            'default_vendor_name' => $treatment->veterinarian_name,
        ];
    }

    public static function vetVisitContext(VetVisit $visit): array
    {
        return [
            'farm_id' => $visit->farm_id,
            'animal_id' => $visit->animal_id,
            'expense_date' => $visit->start_date,
            'title' => 'Vet visit: '.$visit->disease_name,
            'default_vendor_name' => $visit->veterinarian_name,
        ];
    }

    public static function breedingRecordContext(BreedingRecord $record): array
    {
        $record->loadMissing(['femaleAnimal']);

        $label = $record->breeding_code ?: 'Breeding #'.$record->id;
        $type = config('modules.breeding_type_labels.'.$record->breeding_type, $record->breeding_type);

        return [
            'farm_id' => $record->farm_id,
            'animal_id' => $record->female_animal_id,
            'expense_date' => $record->breeding_date,
            'title' => "Breeding ({$type}): {$label}",
            'default_vendor_name' => $record->technician_name ?: $record->semen_source,
        ];
    }

    public static function pregnancyCheckContext(PregnancyCheck $check): array
    {
        $check->loadMissing(['breedingRecord', 'animal']);

        $label = $check->check_code ?: 'Check #'.$check->id;

        return [
            'farm_id' => $check->breedingRecord->farm_id,
            'animal_id' => $check->animal_id,
            'expense_date' => $check->check_date,
            'title' => "Pregnancy check ({$check->resultLabel()}): {$label}",
            'default_vendor_name' => $check->clinic_name ?: $check->checked_by,
        ];
    }

    public static function birthRecordContext(BirthRecord $birth): array
    {
        $birth->loadMissing(['breedingRecord', 'motherAnimal']);

        $label = $birth->birth_code ?: 'Birth #'.$birth->id;

        return [
            'farm_id' => $birth->breedingRecord->farm_id,
            'animal_id' => $birth->mother_animal_id,
            'expense_date' => $birth->birth_date,
            'title' => "Birth: {$label}",
            'default_vendor_name' => $birth->veterinarian_name ?: $birth->assisted_by,
        ];
    }
}
