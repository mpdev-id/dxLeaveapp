<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Application Form - {{ $leaveRequest->user->name }}</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        body {
            font-family: Calibri, sans-serif;
            font-size: 12pt;
            color: #000;
            margin: 0;
            padding: 20pt;
        }

        .header {
            margin-bottom: 10pt;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #000080; /* Navy Blue */
            text-transform: uppercase;
        }

        .dept-name {
            font-size: 13pt;
            font-weight: bold;
            color: #000080;
            margin-top: 2pt;
        }

        .divider {
            border-bottom: 2px solid #000;
            margin-top: 5pt;
            margin-bottom: 15pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
        }

        th, td {
            border: 0.8px solid #000;
            padding: 5px 8pt;
            vertical-align: middle;
            line-height: 1.3;
        }

        .title-row {
            background-color: #EFEFEF;
            text-align: center;
            font-weight: normal;
            font-size: 16pt;
            padding: 10pt;
        }

        .label-col {
            line-height: 1.4;
            background-color: #EFEFEF;
            width: 17.5%;
            font-size: 12pt;
            font-weight: normal;
        }

        .checkbox-item {
            display: block;
            margin-bottom: 3px;
        }

        .checkbox-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 5px;
            text-align: center;
            line-height: 10px;
            font-size: 12pt;
        }

        .note-text {
            font-size: 11pt;
            /* margin-top: 5px; */
            font-style: italic;
            font-weight: thin !important;
        }

        .declaration {
            margin-bottom: 1px;
            font-size: 11pt;
        }

        .approval-table th {
            background-color: #EFEFEF;
            text-align: center;
            font-weight: normal;
            height: 25px;
        }

        .approval-table td {
            height: 125px; /* Space for signature */
            text-align: center;
            vertical-align: bottom;
        }

        .approval-date {
            /* border-top: 1px solid #ccc; */
            /* margin-top: 5px; */
            padding-top: 2px;
            font-size: 9pt;
        }
        
        .footer-note {
            font-size: 11pt;
            margin-top: -5px;
        }

        /* Helper to map leave types */
        @php
            $leaveTypeName = strtolower($leaveRequest->leaveType->name);
            $isAnnual = str_contains($leaveTypeName, 'annual') || str_contains($leaveTypeName, 'tahunan');
            $isSick = str_contains($leaveTypeName, 'sick') || str_contains($leaveTypeName, 'sakit');
            $isMaternity = str_contains($leaveTypeName, 'maternity') || str_contains($leaveTypeName, 'melahirkan');
            $isSpecial = str_contains($leaveTypeName, 'special') || str_contains($leaveTypeName, 'khusus');
            $isUnpaid = str_contains($leaveTypeName, 'unpaid') || str_contains($leaveTypeName, 'potong gaji');
            $isMonthly = str_contains($leaveTypeName, 'monthly') || str_contains($leaveTypeName, 'bulanan');
            
            // Fallback if none match
            if (!$isAnnual && !$isSick && !$isMaternity && !$isSpecial && !$isUnpaid && !$isMonthly) {
                $isSpecial = true; // Default to special/other
            }
        @endphp
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="company-name">PT POSCO DX INDONESIA</div>
        <div class="dept-name">HR TEAM</div>
        <div class="divider"></div>
    </div>

    <table>
        <tr>
            <td colspan="4" class="title-row">
                LEAVE APPLICATION FORM<br>
                (휴가 신청서)
            </td>
        </tr>
        <tr>
            <td class="label-col">Doc No.</td>
            <td colspan="3">DX-HR-ADM-014-001</td>
        </tr>
        <tr>
            <td class="label-col" rowspan="1">Type of Leave</td>
            <td colspan="3">
                <div class="checkbox-item">
                    <span class="checkbox-box">
                        @if($isAnnual)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </span> Annual Leave (연차)
                </div>
                <div class="checkbox-item">
                    <span class="checkbox-box">
                        @if($isSick)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </span> Sick Leave (병가)
                </div>
                <div class="checkbox-item">
                    <span class="checkbox-box">
                        @if($isMaternity)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </span> Maternity Leave (출산 휴가)
                </div>
                <div class="checkbox-item">
                    <span class="checkbox-box">
                        @if($isSpecial)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </span> Special Leave (청원 휴가)
                </div>
                <div class="checkbox-item">
                    <span class="checkbox-box">
                        @if($isUnpaid)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </span> Unpaid Leave (무급 휴가)
                </div>
                <div class="checkbox-item">
                    <span class="checkbox-box"> 
                        @if($isMonthly)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </span> Monthly Leave (월간 휴가)
                </div>
                <div class="note-text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ※Applicable for employee under 12 months only (12 개월 미만자 적용)</div>
            </td>
        </tr>
        <tr>
            <td class="label-col">Name</td>
            <td>{{ $leaveRequest->user->name }}</td>
            <td class="label-col">Department</td>
            <td>{{ $leaveRequest->user->department->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">ID No.</td>
            <td>{{ $leaveRequest->user->employee_code }}</td>
            <td class="label-col">Team</td>
            <td>-</td> <!-- Team not in user model explicitly, usually part of dept or separate -->
        </tr>
        <tr>
            <td class="label-col">Join Date</td>
            <td>{{ $leaveRequest->user->hire_date ? \Carbon\Carbon::parse($leaveRequest->user->hire_date)->format('Y/m/d') : '-' }}</td>
            <td class="label-col">Plant</td>
            <td>-</td> <!-- Plant info not available -->
        </tr>
        <tr>
            <td class="label-col">Leave Date<br>(휴가일)</td>
            <td>{{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('Y/m/d') }}</td>
            <td class="label-col">Total Leave<br>(휴가 기간)</td>
            <td>{{ $leaveRequest->duration_days + 0 }} Day{{ $leaveRequest->duration_days > 1 ? 's' : '' }}</td>
        </tr>
        <tr>
            <td class="label-col">Reason<br>(사유)</td>
            <td colspan="3">{{ $leaveRequest->reason }}</td>
        </tr>
        <tr>
            <td class="label-col">Leave Address<br>(행선지 주소)</td>
            <td colspan="3">{{ $leaveRequest->leave_address ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Phone No.<br>(연락처)</td>
            <td colspan="3">{{ $leaveRequest->user->phone_number ?? '-' }}</td>
        </tr>
    </table>

    <div class="declaration" style="margin-left: 10pt;">
        I hereby declare that the leave information above is true and earnestly ask for your approval.
    </div>

    @php
        // Helper to find approval by role
        $findApproval = function($roleName) use ($leaveRequest) {
            foreach ($leaveRequest->approvals as $approval) {
                // Check if approver has the role
                if ($approval->approver && $approval->approver->hasRole($roleName)) {
                    return $approval;
                }
            }
            return null;
        };

        // Mapping roles to columns
        $slApproval = $findApproval('SL');
        $spvApproval = $findApproval('SPV');
        $asmenApproval = $findApproval('ASMEN');
        $tlApproval = $findApproval('TL');
        $managerApproval = $findApproval('Manager');
    @endphp

    <table class="approval-table">
        <tr>
            <th style="width: 16%">Requester</th>
            <th style="width: 16%">Section<br>Leader</th>
            <th style="width: 16%">Supervisor</th>
            <th style="width: 16%">Assistant<br>Mgr.</th>
            <th style="width: 16%">Team Mgr.</th>
            <th style="width: 16%">Dept. Mgr.</th>
        </tr>
        <tr>
            <!-- Requester -->
            <!-- Requester -->
            <td>
            @if($leaveRequest->signature_url)
            <img src="{{ $leaveRequest->signature_url }}" alt="Signature" style="max-height: 80px; max-width: 100%; display: block; margin: 5px auto;">
            @else
            <div style="height: 80px;"></div>
            @endif
            <div><span style="font-size: 9pt;"> {{ $leaveRequest->user->name }}</span></div>
                <div class="approval-date">Date: {{ $leaveRequest->created_at->format('d/m/Y') }}</div>
            </td>

            <!-- Section Leader -->
            <td>
                @if($slApproval)
                @if($slApproval->signature_url)
                <img src="{{ $slApproval->signature_url }}" alt="Signature" style="max-height: 80px; max-width: 100%; display: block; margin: 5px auto;">
                @else
                <div style="font-weight: bold; margin-bottom: 10px;">{{ $slApproval->action }}</div>
                @endif
                <div><span style="font-size: 9pt;"> {{ $slApproval->approver->name }}</span></div>
                    <div class="approval-date">Date: {{ \Carbon\Carbon::parse($slApproval->acted_at)->format('d/m/Y') }}</div>
                @endif
            </td>

            <!-- Supervisor -->
            <td>
            @if($spvApproval)
            @if($spvApproval->signature_url)
            <img src="{{ $spvApproval->signature_url }}" alt="Signature" style="max-height: 80px; max-width: 100%; display: block; margin: 5px auto;">
                    @else
                        <div style="font-weight: bold; margin-bottom: 10px;">{{ $spvApproval->action }}</div>
                    @endif
            <div><span style="font-size: 9pt;"> {{ $spvApproval->approver->name }}</span></div>
                    <div class="approval-date">Date: {{ \Carbon\Carbon::parse($spvApproval->acted_at)->format('d/m/Y') }}</div>
                @endif
            </td>

            <!-- Assistant Mgr -->
            <td>
                @if($asmenApproval)
                @if($asmenApproval->signature_url)
                <img src="{{ $asmenApproval->signature_url }}" alt="Signature" style="max-height: 80px; max-width: 100%; display: block; margin: 5px auto;">
                @else
                <div style="font-weight: bold; margin-bottom: 10px;">{{ $asmenApproval->action }}</div>
                @endif
                    <div><span style="font-size: 9pt;">{{ $asmenApproval->approver->name }}</span></div>
                    <div class="approval-date">Date: {{ \Carbon\Carbon::parse($asmenApproval->acted_at)->format('d/m/Y') }}</div>
                @endif
            </td>

            <!-- Team Mgr -->
            <td>
                @if($tlApproval)
                @if($tlApproval->signature_url)
                <img src="{{ $tlApproval->signature_url }}" alt="Signature" style="max-height: 80px; max-width: 100%; display: block; margin: 5px auto;">
                @else
                <div style="font-weight: bold; margin-bottom: 10px;">{{ $tlApproval->action }}</div>
                @endif
                
                <div><span style="font-size: 9pt;">{{ $tlApproval->approver->name }}</span></div>
                <div class="approval-date">Date: {{ \Carbon\Carbon::parse($tlApproval->acted_at)->format('d/m/Y') }}</div>
                @endif
            </td>

            <!-- Dept Mgr -->
            <td>
                @if($managerApproval)
                @if($managerApproval->signature_url)
                <img src="{{ $managerApproval->signature_url }}" alt="Signature" style="max-height: 80px; max-width: 100%; display: block; margin: 5px auto;">
                @else
                        <div style="font-weight: bold; margin-bottom: 10px;">{{ $managerApproval->action }}</div>
                    @endif
                <div><span style="font-size: 9pt;">{{ $managerApproval->approver->name }}</span></div>
                <div class="approval-date">Date: {{ \Carbon\Carbon::parse($managerApproval->acted_at)->format('d/m/Y') }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer-note" style="margin-top: -20px !important;">
       &nbsp;&nbsp;&nbsp; ※Please keep the column blank if the position is empty(직책 보임자가 없는 경우 비워 두십시오).
    </div>

</body>
</html>
