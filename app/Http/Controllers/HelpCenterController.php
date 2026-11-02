<?php

namespace App\Http\Controllers;

use App\Services\WorkspaceAccessService;
use App\Support\HelpCenter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Help Center for farm workspace users. Technical documentation is not offered here. */
class HelpCenterController extends Controller
{
    public function __construct(private readonly WorkspaceAccessService $access) {}

    public function index(Request $request): View
    {
        return view('help.index', [
            'activeNav' => 'help',
            'documents' => HelpCenter::documentsFor($this->level($request)),
        ]);
    }

    public function download(Request $request, string $document): BinaryFileResponse
    {
        $doc = HelpCenter::documentsFor($this->level($request))[$document] ?? null;

        abort_unless($doc && $doc['available'], 404);

        return response()->download(HelpCenter::path($doc['file']), $doc['file'], ['Content-Type' => 'application/pdf']);
    }

    private function level(Request $request): string
    {
        $user = $request->user();

        abort_unless($this->access->isActive($user), 403);

        return $this->access->isWorkspaceAdmin($user) ? 'workspace_admin' : 'member';
    }
}
