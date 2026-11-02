<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Support\HelpCenter;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Help Center for platform administrators: all manuals, including technical documentation. */
class HelpCenterController extends Controller
{
    public function index(): View
    {
        return view('central.help.index', [
            'activeNav' => 'help',
            'documents' => HelpCenter::documentsFor('platform'),
        ]);
    }

    public function download(string $document): BinaryFileResponse
    {
        $doc = HelpCenter::documentsFor('platform')[$document] ?? null;

        abort_unless($doc && $doc['available'], 404);

        return response()->download(HelpCenter::path($doc['file']), $doc['file'], ['Content-Type' => 'application/pdf']);
    }
}
