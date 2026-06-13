<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('archive.qr_print_title', ['number' => $label['document_number']]) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1a1a1a;
            --muted: #555;
            --border: #ccc;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            color: var(--ink);
            background: #f0f2f5;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: #fff;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .toolbar button,
        .toolbar a {
            border: 1px solid var(--border);
            background: #fff;
            padding: .45rem .9rem;
            border-radius: .5rem;
            cursor: pointer;
            text-decoration: none;
            color: var(--ink);
            font-family: inherit;
            font-size: .9rem;
        }

        .toolbar button.primary,
        .toolbar a.primary {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .preview-wrap {
            display: flex;
            justify-content: center;
            padding: 2rem 1rem 3rem;
        }

        .label-sheet {
            background: #fff;
            border: 1px dashed var(--border);
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }

        /* ملصق صغير — 80×50 مم تقريباً */
        .label-sheet.size-label {
            width: 80mm;
            min-height: 50mm;
            padding: 3mm;
        }

        /* ورقة A4 — ملصق واحد في المنتصف */
        .label-sheet.size-a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-sheet.size-a4 .label-inner {
            width: 100%;
            max-width: 160mm;
            border: 2px solid var(--ink);
            padding: 12mm;
        }

        .label-inner {
            display: flex;
            gap: 4mm;
            align-items: stretch;
        }

        .label-meta {
            flex: 1;
            min-width: 0;
        }

        .org-name {
            font-size: 9pt;
            font-weight: 700;
            margin-bottom: 2mm;
            border-bottom: 1px solid var(--ink);
            padding-bottom: 1.5mm;
        }

        .doc-title {
            font-size: 8.5pt;
            font-weight: 600;
            line-height: 1.35;
            margin-bottom: 2mm;
            word-break: break-word;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: .5mm 2mm;
            font-size: 7pt;
            line-height: 1.35;
        }

        .meta-grid dt {
            margin: 0;
            color: var(--muted);
            white-space: nowrap;
        }

        .meta-grid dd {
            margin: 0;
            font-weight: 500;
            word-break: break-word;
        }

        .doc-number {
            margin-top: 2mm;
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: .5px;
        }

        .scan-hint {
            margin-top: 2mm;
            font-size: 6.5pt;
            color: var(--muted);
        }

        .label-qr {
            flex-shrink: 0;
            text-align: center;
        }

        .label-qr img {
            width: 28mm;
            height: 28mm;
            display: block;
            border: 1px solid var(--border);
        }

        .size-a4 .label-qr img {
            width: 45mm;
            height: 45mm;
        }

        .label-qr small {
            display: block;
            font-size: 6pt;
            color: var(--muted);
            margin-top: 1mm;
        }

        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .preview-wrap { padding: 0; }
            .label-sheet {
                border: none;
                box-shadow: none;
                margin: 0 auto;
            }
        }

        @page {
            margin: 0;
        }

        @media print {
            .size-label {
                page: label-page;
            }
            .size-a4 {
                page: a4-page;
            }
        }

        @page label-page {
            size: 80mm 50mm;
            margin: 0;
        }

        @page a4-page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="primary" onclick="window.print()">{{ __('archive.print_label') }}</button>
        <a href="{{ route('document.qr.print', ['file' => $document->id, 'size' => 'label']) }}" class="{{ $size === 'label' ? 'primary' : '' }}">{{ __('archive.qr_size_label') }}</a>
        <a href="{{ route('document.qr.print', ['file' => $document->id, 'size' => 'a4']) }}" class="{{ $size === 'a4' ? 'primary' : '' }}">{{ __('archive.qr_size_a4') }}</a>
        <a href="{{ route('document.qr', $document) }}?download=1">{{ __('archive.download_qr') }}</a>
        <a href="{{ route('document.show', $document->id) }}">{{ __('archive.back_to_document') }}</a>
    </div>

    <div class="preview-wrap">
        <div class="label-sheet size-{{ $size }}">
            <div class="label-inner">
                <div class="label-meta">
                    <div class="org-name">{{ $label['organization'] }}</div>
                    <div class="doc-title">{{ $label['title'] }}</div>
                    <dl class="meta-grid">
                        <dt>{{ __('archive.document_number') }}</dt>
                        <dd>{{ $label['document_number'] }}</dd>
                        <dt>{{ __('archive.department') }}</dt>
                        <dd>{{ $label['department'] }}</dd>
                        <dt>{{ __('archive.category') }}</dt>
                        <dd>{{ $label['category'] }}</dd>
                        <dt>{{ __('archive.document_type') }}</dt>
                        <dd>{{ $label['document_type'] }}</dd>
                        <dt>{{ __('archive.archive_date') }}</dt>
                        <dd>{{ $label['archive_date'] }}</dd>
                        <dt>{{ __('archive.status') }}</dt>
                        <dd>{{ $label['status'] }}</dd>
                    </dl>
                    <div class="doc-number">{{ $label['document_number'] }}</div>
                    <div class="scan-hint">{{ __('archive.qr_scan_hint') }}</div>
                </div>
                <div class="label-qr">
                    <img src="{{ route('document.qr', $document) }}" alt="{{ __('archive.qr_code') }}">
                    <small>{{ $label['code'] }}</small>
                </div>
            </div>
        </div>
    </div>

    @if(request()->boolean('print'))
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>
