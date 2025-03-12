<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function generatePDF(Request $request)
    {
        $approvals = ApprovalRequest::with(['user', 'logs'])
            ->whereIn('id', $request->ids)
            ->get();

        // Ambil tanggal saat ini
        $tanggal = now()->format('d-m-Y');

        // Ambil nama pengguna yang mengunduh laporan
        $userName = Auth::user()->name ?? 'Admin'; // Gunakan 'Admin' jika user tidak tersedia

        // Bersihkan karakter yang tidak diperbolehkan dalam nama file
        $userName = preg_replace('/[^A-Za-z0-9_\-]/', '', $userName);

        // Format nama file dengan nama user
        $fileName = "Approval-report_{$userName}_{$tanggal}.pdf";

        // Generate PDF
        $pdf = Pdf::loadView('pdf.approval_report', compact('approvals'));

        return $pdf->download($fileName);
    }
}
