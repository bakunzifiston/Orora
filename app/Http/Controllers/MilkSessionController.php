<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MilkSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\MilkSessionCompleteRequest;
use App\Http\Requests\MilkSessionRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use App\Models\MilkSession;
use App\Models\MilkStorage;
use App\Services\MilkSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MilkSessionController extends Controller
{
    use MilkSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(
        private readonly MilkSessionService $sessionService,
    ) {}

    public function index(): View
    {
        $sessions = MilkSession::query()
            ->with(['farm', 'livestock'])
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('modules.milk.sessions.index', $this->milkSectionData('sessions', compact('sessions')));
    }

    public function create(): View
    {
        return view('modules.milk.sessions.create', $this->milkSectionData('sessions', $this->formOptions()));
    }

    public function store(MilkSessionRequest $request): RedirectResponse
    {
        try {
            $session = $this->sessionService->create($request->sessionAttributes());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['session' => $e->getMessage()]);
        }

        return redirect()
            ->route('milk.sessions.edit', $session)
            ->with('success', 'Milking session opened. Add animal yields below.');
    }

    public function edit(MilkSession $milkSession): View
    {
        $milkSession->load(['farm', 'livestock', 'records.animal', 'destinationStorage']);

        return view('modules.milk.sessions.edit', $this->milkSectionData('sessions', array_merge(
            $this->formOptions($milkSession),
            ['milkSession' => $milkSession],
        )));
    }

    public function update(MilkSessionRequest $request, MilkSession $milkSession): RedirectResponse
    {
        try {
            $this->sessionService->update($milkSession, $request->sessionAttributes());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['session' => $e->getMessage()]);
        }

        return redirect()
            ->route('milk.sessions.edit', $milkSession)
            ->with('success', 'Session updated.');
    }

    public function complete(MilkSessionCompleteRequest $request, MilkSession $milkSession): RedirectResponse
    {
        try {
            $this->sessionService->complete(
                $milkSession,
                $request->input('destination_storage_id') ? (int) $request->input('destination_storage_id') : null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['complete' => $e->getMessage()]);
        }

        return redirect()
            ->route('milk.sessions')
            ->with('success', 'Session completed and milk stored.');
    }

    public function cancel(MilkSession $milkSession): RedirectResponse
    {
        try {
            $this->sessionService->cancel($milkSession);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['session' => $e->getMessage()]);
        }

        return redirect()
            ->route('milk.sessions')
            ->with('success', 'Session cancelled.');
    }

    public function destroy(MilkSession $milkSession): RedirectResponse
    {
        if (! $milkSession->isOpen()) {
            return back()->withErrors(['session' => 'Only open sessions can be deleted.']);
        }

        $milkSession->records()->delete();
        $milkSession->delete();

        return redirect()->route('milk.sessions')->with('success', 'Session removed.');
    }

    private function formOptions(?MilkSession $session = null): array
    {
        $farmId = $session?->farm_id ?? old('farm_id');
        $livestockByFarm = Livestock::query()
            ->select(['id', 'farm_id', 'name'])
            ->orderBy('name')
            ->get()
            ->groupBy('farm_id')
            ->map(fn ($groups) => $groups->map(fn (Livestock $group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])->values())
            ->toArray();

        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'livestockGroups' => Livestock::query()
                ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                ->orderBy('name')
                ->get(),
            'livestockByFarm' => $livestockByFarm,
            'storageTanks' => MilkStorage::query()
                ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                ->orderBy('container_name')
                ->get(),
            'sessionAnimals' => $session
                ? Animal::query()
                    ->where('farm_id', $session->farm_id)
                    ->where('livestock_id', $session->livestock_id)
                    ->milkingEligible()
                    ->whereNotIn('id', $session->records()->pluck('animal_id'))
                    ->orderBy('tag_number')
                    ->get()
                : collect(),
            'herdLactatingCount' => $session
                ? Animal::query()
                    ->where('farm_id', $session->farm_id)
                    ->where('livestock_id', $session->livestock_id)
                    ->milkingEligible()
                    ->count()
                : 0,
            'eligibleAnimalsByLivestock' => $session
                ? Animal::query()
                    ->where('farm_id', $session->farm_id)
                    ->milkingEligible()
                    ->whereNotIn('id', $session->records()->pluck('animal_id'))
                    ->orderBy('tag_number')
                    ->get(['id', 'livestock_id', 'tag_number', 'name'])
                    ->groupBy('livestock_id')
                    ->map(fn ($animals) => $animals->map(fn (Animal $animal) => [
                        'id' => $animal->id,
                        'tag_number' => $animal->tag_number,
                        'name' => $animal->name,
                    ])->values())
                    ->toArray()
                : [],
        ];
    }
}
