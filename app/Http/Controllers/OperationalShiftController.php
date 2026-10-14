<?php

namespace App\Http\Controllers;

use App\Services\OperationalShiftService;
use Exception;
use Illuminate\Http\Request;

class OperationalShiftController extends Controller
{
    /**
     * Open operational shift counter manually (Staff / Admin).
     */
    public function open(Request $request, OperationalShiftService $shiftService)
    {
        try {
            $date = $request->input('date');
            $result = $shiftService->openShift(auth()->user(), $date);
            return back()->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Close operational shift counter manually (Staff / Admin).
     * Automatically checks out all checked-in guides!
     */
    public function close(Request $request, OperationalShiftService $shiftService)
    {
        try {
            $reason = $request->input('reason', 'Tutup operasional loket');
            $date = $request->input('date');
            $result = $shiftService->closeShift(auth()->user(), $reason, $date);
            return back()->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check current operational shift status (JSON for widgets/live polling).
     */
    public function status(OperationalShiftService $shiftService)
    {
        return response()->json($shiftService->getStatus());
    }
}
