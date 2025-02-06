<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function generatePDF(Request $request)
    {
        $approvals = ApprovalRequest::with(['user', 'logs'])
            ->whereIn('id', $request->ids)
            ->get();

        $pdf = Pdf::loadView('pdf.approval_report', compact('approvals'));

        return $pdf->download('laporan-approval.pdf');
    }
}
