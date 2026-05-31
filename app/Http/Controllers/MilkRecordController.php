<?php

namespace App\Http\Controllers;

use App\Http\Requests\MilkBulkRecordRequest;
use App\Http\Requests\MilkRecordRequest;
use App\Models\MilkRecord;
use App\Models\MilkSession;
use App\Services\MilkSessionService;
use Illuminate\Http\RedirectResponse;

class MilkRecordController extends Controller
{
    public function __construct(
        private readonly MilkSessionService $sessionService,
    ) {}

    public function store(MilkRecordRequest $request, MilkSession $milkSession): RedirectResponse
    {
        try {
            $this->sessionService->addRecord($milkSession, $request->recordAttributes());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['record' => $e->getMessage()]);
        }

        return redirect()
            ->route('milk.sessions.edit', $milkSession)
            ->with('success', 'Animal yield recorded.');
    }

    public function bulkStore(MilkBulkRecordRequest $request, MilkSession $milkSession): RedirectResponse
    {
        try {
            $result = $this->sessionService->addRecordsBulk(
                $milkSession,
                $request->input('yields', []),
                $request->input('bulk_lines'),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['bulk' => $e->getMessage()]);
        }

        $message = "{$result['added']} yield(s) saved.";

        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} skipped.";
        }

        $redirect = redirect()->route('milk.sessions.edit', $milkSession)->with('success', $message);

        if ($result['errors'] !== []) {
            $redirect->with('bulk_warnings', $result['errors']);
        }

        return $redirect;
    }

    public function update(MilkRecordRequest $request, MilkRecord $milkRecord): RedirectResponse
    {
        try {
            $this->sessionService->updateRecord($milkRecord, $request->recordAttributes());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['record' => $e->getMessage()]);
        }

        return redirect()
            ->route('milk.sessions.edit', $milkRecord->session)
            ->with('success', 'Record updated.');
    }

    public function destroy(MilkRecord $milkRecord): RedirectResponse
    {
        $session = $milkRecord->session;

        try {
            $this->sessionService->removeRecord($milkRecord);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['record' => $e->getMessage()]);
        }

        return redirect()
            ->route('milk.sessions.edit', $session)
            ->with('success', 'Record removed.');
    }
}
