<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RwandaLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RwandaLocationController extends Controller
{
    public function __construct(private readonly RwandaLocationService $locations) {}

    public function provinces(): JsonResponse
    {
        return response()->json($this->locations->provinces());
    }

    public function districts(Request $request): JsonResponse
    {
        $request->validate(['province_code' => ['required', 'integer']]);

        return response()->json(
            $this->locations->districts((int) $request->query('province_code'))
        );
    }

    public function sectors(Request $request): JsonResponse
    {
        $request->validate(['district_code' => ['required', 'integer']]);

        return response()->json(
            $this->locations->sectors((int) $request->query('district_code'))
        );
    }

    public function cells(Request $request): JsonResponse
    {
        $request->validate(['sector_code' => ['required', 'string']]);

        return response()->json(
            $this->locations->cells($request->query('sector_code'))
        );
    }

    public function villages(Request $request): JsonResponse
    {
        $request->validate(['cell_code' => ['required', 'integer']]);

        return response()->json(
            $this->locations->villages((int) $request->query('cell_code'))
        );
    }
}
