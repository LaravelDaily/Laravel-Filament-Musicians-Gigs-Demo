<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gig Worksheet - {{ $gig->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #111;
            max-width: 8.5in;
            margin: 0 auto;
            padding: 0.5in;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .header .date {
            font-size: 14pt;
            color: #333;
        }

        .section {
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f0f0f0;
            padding: 0.25rem 0.5rem;
            margin-bottom: 0.5rem;
            border: 1px solid #ccc;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem 1rem;
        }

        .info-row {
            display: flex;
            gap: 0.5rem;
        }

        .info-label {
            font-weight: bold;
            min-width: 100px;
        }

        .info-value {
            flex: 1;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }

        th, td {
            border: 1px solid #333;
            padding: 0.35rem 0.5rem;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10pt;
        }

        td {
            font-size: 11pt;
        }

        .print-button {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 14px;
        }

        .print-button:hover {
            background-color: #1d4ed8;
        }

        .notes-box {
            border: 1px solid #ccc;
            padding: 0.5rem;
            min-height: 60px;
            background-color: #fafafa;
            white-space: pre-line;
        }

        .footer {
            margin-top: 1.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #ccc;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Print Worksheet</button>

    <div class="header">
        <h1>{{ $gig->name }}</h1>
        <div class="date">{{ $gig->date->format('l, F j, Y') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Times</div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Call Time:</span>
                <span class="info-value">{{ $gig->call_time->format('g:i A') }}</span>
            </div>
            @if($gig->performance_time)
                <div class="info-row">
                    <span class="info-label">Performance:</span>
                    <span class="info-value">{{ $gig->performance_time->format('g:i A') }}</span>
                </div>
            @endif
            @if($gig->end_time)
                <div class="info-row">
                    <span class="info-label">End Time:</span>
                    <span class="info-value">{{ $gig->end_time->format('g:i A') }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Venue</div>
        <div class="info-grid">
            <div class="info-row full-width">
                <span class="info-label">Venue:</span>
                <span class="info-value">{{ $gig->venue_name }}</span>
            </div>
            <div class="info-row full-width">
                <span class="info-label">Address:</span>
                <span class="info-value">{{ $gig->venue_address }}</span>
            </div>
        </div>
    </div>

    @if($gig->client_contact_name || $gig->client_contact_phone || $gig->client_contact_email)
        <div class="section">
            <div class="section-title">Client Contact</div>
            <div class="info-grid">
                @if($gig->client_contact_name)
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ $gig->client_contact_name }}</span>
                    </div>
                @endif
                @if($gig->client_contact_phone)
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value">{{ $gig->client_contact_phone }}</span>
                    </div>
                @endif
                @if($gig->client_contact_email)
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $gig->client_contact_email }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($gig->dress_code)
        <div class="section">
            <div class="section-title">Dress Code</div>
            <div class="notes-box">{{ $gig->dress_code }}</div>
        </div>
    @endif

    @if($gig->notes)
        <div class="section">
            <div class="section-title">Notes / Instructions</div>
            <div class="notes-box">{{ $gig->notes }}</div>
        </div>
    @endif

    <div class="section">
        <div class="section-title">Musicians ({{ $gig->assignments->count() }})</div>
        @if($gig->assignments->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%">Name</th>
                        <th style="width: 25%">Instrument</th>
                        <th style="width: 25%">Phone</th>
                        <th style="width: 15%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gig->assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->user->name }}</td>
                            <td>{{ $assignment->instrument?->name ?? '-' }}</td>
                            <td>{{ $assignment->user->phone ?? '-' }}</td>
                            <td>{{ $assignment->status->getLabel() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 0.5rem; color: #666;">No musicians assigned to this gig.</p>
        @endif
    </div>

    <div class="footer">
        Printed on {{ now()->format('F j, Y \a\t g:i A') }}
    </div>
</body>
</html>
