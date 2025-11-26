@component('mail::message')
# To, {{ $user->name }}

Mr, **{{ $user->name }}**  
You have received an **approved document revision** in the **PROMISE** application.  
Please log in to the application to view and download the approved file(s).

@component('mail::button', ['url' => $approval['download_url'] ?? '#' ])
Open the PROMISE Application
@endcomponent

---

@php
    $statusText = trim(($approval['project_status'] ?? '') . ' Approved');
@endphp

**Status :** 
<span style="
    display:inline-block;
    padding:4px 10px;
    border-radius:9999px;
    background-color:#dbeafe;   /* biru muda, tidak terlalu terang */
    border:1px solid #bfdbfe;   /* sedikit outline */
    color:#1d4ed8;              /* biru teks */
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

@if(!empty($approval['filenames']) && is_array($approval['filenames']) && count($approval['filenames']) > 0)
<ul style="font-size: 14px; line-height: 2;">
@foreach($approval['filenames'] as $fn)
<li>{{ $fn }}</li>
@endforeach
</ul>
@else
<p style="font-size: 14px;">
    *(No files were found for this revision.)*
</p>
@endif

---

If the button above does not work, please copy and paste the link below into your browser:

[{{ $approval['download_url'] ?? '-' }}]({{ $approval['download_url'] ?? '#' }})

---

@component('mail::panel')
**Attention:** This is an automated email. Please do not reply to this email.  
If you have any questions, please contact the SAI listed in the operations manual.
@endcomponent

@endcomponent
