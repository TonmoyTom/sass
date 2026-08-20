<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
        }
        body {
            position: relative;
        }
        /* absolute-positioned border layers instead of nested height:100%
           boxes — dompdf's support for percentage heights combined with
           box-sizing:border-box is unreliable and was leaving a large
           blank gap below the content. */
        .border-outer {
            position: absolute;
            top: 18px;
            left: 18px;
            right: 18px;
            bottom: 18px;
            border: 10px solid #1e3a8a;
        }
        .border-inner {
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            bottom: 8px;
            border: 2px solid #c9a227;
        }
        .content {
            position: absolute;
            top: 40px;
            left: 60px;
            right: 60px;
            text-align: center;
        }
        .brand-logo {
            height: 42px;
        }
        .brand-name {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 6px;
        }
        .title {
            font-size: 32px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 2px;
            margin: 26px 0 4px 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 11px;
            color: #6b7280;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .student-name {
            font-size: 28px;
            font-weight: bold;
            color: #111827;
            margin: 22px 0 6px 0;
            padding: 0 30px 8px 30px;
            border-bottom: 1px solid #c9a227;
            display: inline-block;
        }
        .completion-text {
            font-size: 12px;
            color: #374151;
            margin-top: 24px;
        }
        .course-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin-top: 6px;
        }
        .meta-table {
            width: 100%;
            margin-top: 44px;
            border-collapse: collapse;
        }
        .meta-table td {
            width: 33.33%;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        .meta-table .meta-value {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }
        .cert-footer {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 10px;
            color: #9ca3af;
            margin-top: 20px;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="border-outer"></div>
    <div class="border-inner"></div>

    <div class="content">
        @if($companyLogo)
            <img src="{{ $companyLogo }}" class="brand-logo" alt="{{ $companyName }}"><br>
        @endif
        <div class="brand-name">{{ $companyName }}</div>

        <div class="title">Certificate of Completion</div>
        <div class="subtitle">This certificate is proudly presented to</div>

        <div class="student-name">{{ $studentName }}</div>

        <div class="completion-text">for successfully completing the course</div>
        <div class="course-title">{{ $courseTitle }}</div>

        <table class="meta-table">
            <tr>
                <td>
                    <span class="meta-value">{{ $issuedDate }}</span>
                    Date Issued
                </td>
                <td>
                    <span class="meta-value">{{ $companyName }}</span>
                    Issuing Organization
                </td>
                <td>
                    <span class="meta-value">{{ $certificateNumber }}</span>
                    Certificate No.
                </td>
            </tr>
        </table>

        <div class="cert-footer">
            Verify this certificate at {{ $verifyUrl }}
        </div>
    </div>
</body>
</html>