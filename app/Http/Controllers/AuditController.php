<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TransferLog;
use Inertia\Inertia;
use Inertia\Response;

class AuditController extends Controller
{
    /**
     * Show the transfer history (the black box) paginated.
     */
    public function index(): Response
    {
        $logs = TransferLog::query()
            ->with(['locationFrom', 'locationTo', 'resource', 'user'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Inventory/History', [
            'logs' => $logs,
        ]);
    }
}
