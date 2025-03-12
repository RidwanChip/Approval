<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Approval Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }

        .report-title {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .info-label {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 30%;
        }

        .approval-details {
            margin: 20px 0;
            line-height: 1.6;
        }

        .signature-section {
            margin-top: 30px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .signature-table td {
            padding: 10px;
            /* border: 1px solid #ddd; */
            text-align: center;
            vertical-align: middle;
            width: 16.66%;
        }

        .signature-image {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }

        .page-break {
            page-break-after: always;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2 class="report-title">Approval Report</h2>

    @foreach ($approvals as $approval)
        <table class="info-table">
            <tr>
                <td class="info-label">Document Number</td>
                <td>{{ $approval->id }}</td>
                <td class="info-label">Requestor</td>
                <td>{{ $approval->user->name }}</td>
            </tr>
        </table>

        <div class="approval-details">
            <table class="info-table">
                <tr>
                    <td class="info-label">Document Type</td>
                    <td>{{ $approval->flow->name }}</td>
                </tr>
                <tr>
                    <td class="info-label">Status</td>
                    <td>{{ ucfirst($approval->status) }}</td>
                </tr>
                <tr>
                    <td class="info-label">Request for</td>
                    <td>{{ $approval->data }}</td>
                </tr>
                <tr>
                    <td class="info-label">Description</td>
                    <td>{{ $approval->description }}</td>
                </tr>
                <tr>
                    <td class="info-label">Submission Date</td>
                    <td>{{ $approval->created_at->format('d F Y') }}</td>
                </tr>
            </table>
        </div>

        <div class="signature-section">
            <h4>Approval Signatures</h4>
            @php
                $signatures = $approval->logs
                    ->filter(function ($log) {
                        return in_array($log->action, ['approved', 'rejected']);
                    })
                    ->values();
                $chunkedSignatures = $signatures->chunk(5);
            @endphp

            @foreach ($chunkedSignatures as $signatureRow)
                <table class="signature-table" style="margin-bottom: 20px;">
                    <tr>
                        @foreach ($signatureRow as $log)
                            <td style="width: 16.66%;">
                                @if ($log->user->signature_image)
                                    <img class="signature-image"
                                        src="{{ realpath(public_path('storage/' . $log->user->signature_image)) }}"
                                        alt="Signature">
                                @else
                                    <p></p>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($signatureRow as $log)
                            <td>
                                {{ ucfirst($log->user->name) }}<br>
                                <small
                                    style="color: #3f3f3f; font-size: 12px">{{ ucfirst($log->user->employee->position->name ?? '') }}</small><br>
                                <small
                                    style="color: #666; font-size: 10px">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                        @endforeach
                    </tr>
                </table>
            @endforeach
        </div>
        <div class="page-break"></div>
    @endforeach
</body>

</html>
