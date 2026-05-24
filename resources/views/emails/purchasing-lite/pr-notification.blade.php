<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
</head>

<body style="margin:0; padding:0; background:#f8fafc; font-family:Arial, sans-serif; color:#0f172a;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; padding:24px;">
        <tr>
            <td align="center">
                <table width="640" cellpadding="0" cellspacing="0" style="background:#ffffff; border:1px solid #cbd5e1;">
                    <tr>
                        <td style="padding:24px; border-bottom:1px solid #cbd5e1;">
                            <h1 style="margin:0; font-size:22px; line-height:1.3; color:#0f172a;">
                                {{ $title }}
                            </h1>

                            <p style="margin:8px 0 0; font-size:14px; color:#475569;">
                                Purchasing Lite Notification
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 16px; font-size:16px; line-height:1.6; color:#334155;">
                                {{ $messageText }}
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin:20px 0;">
                                <tr>
                                    <td style="border:1px solid #cbd5e1; background:#f1f5f9; padding:10px; font-size:13px; font-weight:bold; width:180px;">
                                        PR Number
                                    </td>
                                    <td style="border:1px solid #cbd5e1; padding:10px; font-size:13px;">
                                        {{ $purchaseRequest->pr_number ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="border:1px solid #cbd5e1; background:#f1f5f9; padding:10px; font-size:13px; font-weight:bold;">
                                        Title
                                    </td>
                                    <td style="border:1px solid #cbd5e1; padding:10px; font-size:13px;">
                                        {{ $purchaseRequest->title ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="border:1px solid #cbd5e1; background:#f1f5f9; padding:10px; font-size:13px; font-weight:bold;">
                                        Requester
                                    </td>
                                    <td style="border:1px solid #cbd5e1; padding:10px; font-size:13px;">
                                        {{ $purchaseRequest->requester_name ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="border:1px solid #cbd5e1; background:#f1f5f9; padding:10px; font-size:13px; font-weight:bold;">
                                        Department
                                    </td>
                                    <td style="border:1px solid #cbd5e1; padding:10px; font-size:13px;">
                                        {{ $purchaseRequest->department_name ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="border:1px solid #cbd5e1; background:#f1f5f9; padding:10px; font-size:13px; font-weight:bold;">
                                        Status
                                    </td>
                                    <td style="border:1px solid #cbd5e1; padding:10px; font-size:13px;">
                                        {{ ucwords(str_replace('_', ' ', (string) ($purchaseRequest->status ?? '-'))) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="border:1px solid #cbd5e1; background:#f1f5f9; padding:10px; font-size:13px; font-weight:bold;">
                                        Current Step
                                    </td>
                                    <td style="border:1px solid #cbd5e1; padding:10px; font-size:13px;">
                                        {{ ucwords(str_replace('_', ' ', (string) ($purchaseRequest->current_step ?? '-'))) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="border:1px solid #cbd5e1; background:#f1f5f9; padding:10px; font-size:13px; font-weight:bold;">
                                        Date Needed
                                    </td>
                                    <td style="border:1px solid #cbd5e1; padding:10px; font-size:13px;">
                                        {{ $purchaseRequest->date_needed ? \Carbon\Carbon::parse($purchaseRequest->date_needed)->format('d M Y') : '-' }}
                                    </td>
                                </tr>
                            </table>

                            @if (! empty($remarks))
                            <div style="margin:20px 0; border:1px solid #f59e0b; background:#fffbeb; padding:14px;">
                                <p style="margin:0 0 6px; font-size:13px; font-weight:bold; color:#92400e;">
                                    Remarks
                                </p>

                                <p style="margin:0; font-size:14px; line-height:1.6; color:#78350f; white-space:pre-line;">
                                    {{ $remarks }}
                                </p>
                            </div>
                            @endif

                            <table cellpadding="0" cellspacing="0" style="margin-top:24px;">
                                <tr>
                                    <td style="background:#0f172a;">
                                        <a href="{{ $buttonUrl }}" style="display:inline-block; padding:13px 22px; color:#ffffff; text-decoration:none; font-size:14px; font-weight:bold;">
                                            {{ $buttonLabel }}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; font-size:12px; line-height:1.5; color:#64748b;">
                                If the button does not work, copy and open this link:
                                <br>
                                <span style="word-break:break-all;">{{ $buttonUrl }}</span>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px; border-top:1px solid #cbd5e1; background:#f8fafc;">
                            <p style="margin:0; font-size:12px; color:#64748b;">
                                This email was sent automatically from Purchasing Lite.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>