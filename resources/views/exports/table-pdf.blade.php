<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $header }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 24px;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 0.3px;
        }

        .header p {
            margin: 6px 0 0;
            font-size: 10px;
            color: #6b7280;
        }

        .notice {
            margin-bottom: 12px;
            padding: 8px 10px;
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #92400e;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            vertical-align: top;
            word-break: break-word;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        tr:nth-child(even) td {
            background: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $header }}</h1>
        <p>Generated: {{ $generatedAt->format('F j, Y g:i A') }}</p>
    </div>

    @if (! empty($exportNotice))
        <div class="notice">{{ $exportNotice }}</div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($headings), 1) }}">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
