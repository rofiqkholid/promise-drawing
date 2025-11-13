<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PROMISE Notification</title>
  </head>
  <body style="margin:0;padding:0;background:#f6f7f9;font-family:Arial,Helvetica,sans-serif;color:#111;">
    <!-- Preheader (hidden) -->
    <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
      You have received the files, please log-in to PROMISE and download the files.
    </div>

    <!-- Full width wrapper -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f6f7f9;padding:24px 12px;">
      <tr>
        <td align="center">
          <!-- Centered container -->
          <table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
            <tr>
              <td style="padding:24px 28px; font-size:14px; line-height:1.6;">

                <div style="margin-bottom:8px;">To : {{ $user->name }}</div>
                <div style="font-weight:bold;margin:6px 0 16px;">----PROMISE----</div>

                <p style="margin:0 0 14px;">
                  You have received the files, please log-in to PROMISE and download the files.
                </p>

                <!-- Bulletproof button -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:16px 0 20px;">
                  <tr>
                    <td align="center" bgcolor="#4A90E2" style="border-radius:6px;">
                      <a href="{{ $approval['download_url'] }}"
                         style="display:inline-block;padding:12px 22px;text-decoration:none;font-weight:bold;color:#ffffff;border-radius:6px;border:1px solid #357acb;">
                        Click here
                      </a>
                    </td>
                  </tr>
                </table>

                <div style="margin:8px 0;">
                  <strong>Date</strong> : Approve ({{ $approval['decision_date'] }})
                </div>

                <div style="margin:8px 0;">
                  <strong>Subject</strong> :
                  [Data Sending] {{ $approval['customer'] }}-{{ $approval['model'] }}-{{ $approval['doc_type'] }}-{{ $approval['category'] }}
                </div>

                <div style="margin:8px 0;">
                  <strong>Comment</strong> : {{ $approval['comment'] ?? '-' }}
                </div>

                <div style="margin:18px 0 8px;"><strong>-----------Transmit Files-----------</strong></div>
                @php($files = $approval['filenames'] ?? [])
                @if(count($files))
                  <ul style="margin:6px 0 0 18px;padding:0;">
                    @foreach($files as $fn)
                      <li style="margin:2px 0;padding:0;">{{ $fn }}</li>
                    @endforeach
                  </ul>
                @else
                  <div>-</div>
                @endif

                <div style="margin:18px 0 4px;"><strong>-----Attention-----</strong></div>
                <div>Do not reply to this e-mail.</div>

                <div style="margin:18px 0 0;"><strong>----------End of Mail---------- </strong></div>

              </td>
            </tr>
          </table>

          <!-- Footer spacing -->
          <div style="height:16px;line-height:16px;">&nbsp;</div>
        </td>
      </tr>
    </table>
  </body>
</html>
