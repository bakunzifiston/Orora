<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MilkSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\MilkRecordRequest;
use App\Models\Animal;
use App\Models\Livestock;
use App\Models\MilkRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MilkRecordController extends Controller
{
    use MilkSectionViews;
    use ProvidesModuleNavigation;

    public function index(): View
    {
        $milkRecords = MilkRecord::query()
            ->with(['farm', 'animal', 'livestock'])
            ->orderByDesc('recorded_on')
            ->orderByDesc('id')
            ->paginate(15);

        return view('modules.milk.records.index', $this->milkSectionData('records', compact('milkRecords')));
    }

    public function create(): View
    {
        return view('modules.milk.records.create', $this->milkSectionData('records', $this->formOptions()));
    }

    public function store(MilkRecordRequest $request): RedirectResponse
    {
        MilkRecord::create($request->milkRecordAttributes());

        return redirect()->route('milk.records')->with('success', 'Milk record saved successfully.');
    }

    public function edit(MilkRecord $milkRecord): View
    {
        return view('modules.milk.records.edit', $this->milkSectionData('records', array_merge(
            $this->formOptions(),
            ['milkRecord' => $milkRecord],
        )));
    }

    public function update(MilkRecordRequest $request, MilkRecord $milkRecord): RedirectResponse
    {
        $milkRecord->update($request->milkRecordAttributes());

        return redirect()->route('milk.records')->with('success', 'Milk record updated successfully.');
    }

    public function destroy(MilkRecord $milkRecord): RedirectResponse
    {
        $milkRecord->delete();

        return redirect()->route('milk.records')->with('success', 'Milk record removed successfully.');
    }

    private function formOptions(): array
    {
        return [
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
            'livestockGroups' => Livestock::query()->orderBy('name')->get(),
        ];
    }
}
