<!DOCTYPE html>
<html lang="en">
  <body style="margin:0;padding:0;background:#f6f7f9;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111;">
    <!-- Wrapper -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="padding:24px 12px;">
      <tr>
        <td align="center">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:700px;background:#ffffff;border:1px solid #e6e8eb;border-radius:8px;overflow:hidden;">
            <!-- Header -->
            <tr>
              <td style="background:#111827;color:#ffffff;padding:16px 20px;font-size:16px;font-weight:bold;">
                PROMISE — Revision Approved
              </td>
            </tr>

            <!-- Body -->
            <tr>
              <td style="padding:20px;">
                <div style="margin-bottom:10px;">To: <strong>{{ $user->name }}</strong></div>

                <p style="margin:0 0 12px;">
                  Your document revision has been <strong>Approved</strong>. Please sign in to PROMISE to download the files.
                </p>

                <!-- CTA Button -->
                <div style="margin:16px 0 20px;">
                  <a href="{{ $approval['download_url'] }}"
                     style="display:inline-block;text-decoration:none;background:#2563eb;border:1px solid #1d4ed8;border-radius:6px;padding:10px 22px;color:#fff;font-weight:bold;">
                    Click here
                  </a>
                </div>

                <!-- Quick Details -->
                <table role="presentation" cellpadding="4" cellspacing="0" border="0" style="width:100%;margin:10px 0 6px 0;">
                  <tr>
                    <td style="width:140px;"><strong>Date</strong></td>
                    <td>: Approve ({{ $approval['decision_date'] ?? $approval['approved_at'] ?? '-' }})</td>
                  </tr>
                  <tr>
                    <td><strong>Subject</strong></td>
                    <td>: [Data Sending] {{ $approval['customer'] ?? '-' }}-{{ $approval['model'] ?? '-' }}-{{ $approval['doc_type'] ?? '-' }}-{{ $approval['category'] ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Customer</strong></td>
                    <td>: {{ $approval['customer'] ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Model</strong></td>
                    <td>: {{ $approval['model'] ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Part No</strong></td>
                    <td>: {{ $approval['part_no'] ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Doc Type</strong></td>
                    <td>: {{ $approval['doc_type'] ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Category</strong></td>
                    <td>: {{ $approval['category'] ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Revision</strong></td>
                    <td>: Rev-{{ $approval['revision_no'] ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Approved By</strong></td>
                    <td>: {{ $approval['approved_by'] ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Approved At</strong></td>
                    <td>: {{ $approval['approved_at'] ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td><strong>Comment</strong></td>
                    <td>: {{ $approval['comment'] ?? '-' }}</td>
                  </tr>
                </table>

                <!-- Files -->
                <div style="margin:18px 0 8px;"><strong>Transmit Files</strong></div>
                <div style="padding:10px 12px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;">
                  @forelse(($approval['filenames'] ?? []) as $fn)
                    <div style="margin:2px 0;">• {{ $fn }}</div>
                  @empty
                    <div>-</div>
                  @endforelse
                </div>

                <!-- Fallback link -->
                <p style="margin:14px 0 0;color:#374151;">
                  If the button does not work, copy and paste this link into your browser:<br>
                  <a href="{{ $approval['download_url'] }}" style="color:#2563eb;word-break:break-all;">
                    {{ $approval['download_url'] }}
                  </a>
                </p>

                <!-- Footer note -->
                <div style="margin:18px 0 0;color:#6b7280;">
                  <strong>Note:</strong> Do not reply to this e-mail.
                </div>
              </td>
            </tr>

            <!-- Footer -->
            <tr>
              <td style="background:#f3f4f6;color:#6b7280;padding:12px 20px;font-size:12px;">
                © {{ date('Y') }} PROMISE. All rights reserved.
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
