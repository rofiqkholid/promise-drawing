@component('mail::message')
# To, {{ $user->name }}

Mr/Mrs, **{{ $user->name }}**  
You have received an **approved document revision** in the **PROMISE** application.  
Please log in to the application to view and download the approved file(s).

@component('mail::button', ['url' => $approval['download_url'] ?? '#' ])
Open the PROMISE Application
@endcomponent

---

@php
    $projectStatus = trim($approval['project_status'] ?? '');

    // Teks yang ditampilkan di badge
    $statusText = $projectStatus
        ? $projectStatus . ' Approved'
        : 'Approved';

    // Mapping warna per status
    $statusStyles = [
        'Regular' => [                // Hijau lembut
            'bg'    => '#dcfce7',     // bg hijau muda
            'border'=> '#bbf7d0',     // border hijau muda
            'text'  => '#15803d',     // teks hijau tua
        ],
        'Project' => [                // Biru (seperti sekarang)
            'bg'    => '#dbeafe',
            'border'=> '#bfdbfe',
            'text'  => '#1d4ed8',
        ],
        'Feasibility Study' => [      // Abu-abu lembut
            'bg'    => '#e5e7eb',
            'border'=> '#d1d5db',
            'text'  => '#374151',
        ],
    ];

    // Default kalau status tidak dikenali → pakai gaya Project (biru)
    $style = $statusStyles[$projectStatus] ?? $statusStyles['Project'];
@endphp

**Status :** 
<span style="
    display:inline-block;
    padding:4px 10px;
    border-radius:9999px;
    background-color:{{ $style['bg'] }};
    border:1px solid {{ $style['border'] }};
    color:{{ $style['text'] }};
    font-size:12px;
    font-weight:600;
    line-height:1.4;
">
    {{ $statusText }}
</span>
   
Date Approve    :  ({{ $approval['decision_date'] ?? $approval['approved_at'] ?? '-' }})  

**Subject : [Data Sending] 
{{ $approval['customer'] ?? '-' }} - 
{{ $approval['model'] ?? '-' }} - 
{{ $approval['part_no'] ?? '-' }} - 
{{ $approval['doc_type'] ?? '-' }} - 
{{ $approval['category'] ?? '-' }} - 
{{ $approval['part_group'] ?? '-' }} - 
{{ $approval['ecn_no'] ?? '-' }}**


---

### List of Transmit Files

@if(!empty($approval['files']) && count($approval['files']) > 0)
<ul style="padding-left: 20px; margin: 0;">
@foreach($approval['files'] as $file)
<li style="margin-bottom: 5px;">
{{ is_array($file) ? $file['name'] : $file->name }} 
<span style="color: #6b7280; font-size: 12px;">
({{ is_array($file) ? $file['size'] : $file->size }})
</span>
</li>
@endforeach
</ul>
@else
<p style="font-size: 14px; color: #6b7280; font-style: italic;">
(No files were found for this revision.)
</p>
@endif

@component('mail::panel')
**Attention:** This is an automated email. Please do not reply to this email.
@endcomponent

@endcomponent
