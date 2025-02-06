<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Approval</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .page-break {
            page-break-after: always;
        }

        .signature {
            width: 100%;
            height: 20%;
        }

        table,
        th,
        td {
            width: 100%;
            text-align: center;
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <h2>Laporan Approval</h2>

    @foreach ($approvals as $approval)
        <table border="1">
            <tr>
                <td>Approval ID: {{ $approval->id }}</td>
                <td>Nama Pemohon: {{ $approval->user->name }}</td>
            </tr>
        </table>
        <p>Status: {{ $approval->status }}</p>
        <p>Data: {{ $approval->data }}</p>
        <p>Description: {{ $approval->description }}</p>

        <div>
            <p>Tanggal Persetujuan: {{ $approval->created_at->format('d-m-Y') }}</p>
            <h4>Daftar Approver:</h4>
            <!-- Tampilkan Tanda Tangan Approver -->
            <table class="signature">
                <tr>
                    @foreach ($approval->logs as $log)
                        @if (in_array($log->action, ['approved', 'rejected']))
                            <td>
                                @if ($log->user->signature_image)
                                    <img class="signature"
                                        src="{{ realpath(public_path('storage/' . $log->user->signature_image)) }}"
                                        alt="Signature" style="width: 100px; height: 100px; ">
                                @else
                                    <p>-</p>
                                @endif

                            </td>
                        @endif
                    @endforeach
                </tr>
                <tr>
                    @foreach ($approval->logs as $log)
                        @if (in_array($log->action, ['approved', 'rejected']))
                            <td> {{ ucfirst($log->user->name) }} - {{ $log->user->getRoleNames()->first() }}
                                {{-- {{ $log->created_at->format('H:i, d F Y') }}</td> --}}
                        @endif
                    @endforeach
                </tr>
            </table>
        </div>
        <div class="page-break"></div>
    @endforeach

</body>

</html>
