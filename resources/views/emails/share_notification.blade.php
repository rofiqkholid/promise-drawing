@component('mail::message')
# To, {{ $shareToNames }}
Mr, **{{ $username }}**
<br>
You have received a new file in the **Promise** application.
Please log in to the application to view and download the file.

@component('mail::button', ['url' => route('receipts.detail', $packageId)])
Open the Promise Application
@endcomponent

@if($expiredAt)
<p style="font-size: 12px; color: #ef4444; text-align: center; font-weight: bold;">
    Note: Download access will expire on {{ \Carbon\Carbon::parse($expiredAt)->format('Y/m/d') }}
</p>
@endif

@php
    $p = $packageDetails ?? [];
    $model      = $p['model'] ?? '-';
    $partNo     = $p['part_no'] ?? '-';
    $docType    = $p['doc_type'] ?? '-';
    $category   = $p['category'] ?? '-';
    $partGroup  = $p['code_part_group'] ?? '-';
    $revisionNo = $p['revision_no'] ?? '-';
    $ecnNo      = $p['ecn_no'] ?? '-';
    
    $subjectSegments = array_filter([$model, $partNo, $docType, $category, $partGroup, $ecnNo], fn($v) => $v !== null && $v !== '-' && $v !== '');
    $fullSubject = empty($subjectSegments) ? $emailSubject : implode(' - ', $subjectSegments);
@endphp

---

Date : {{ \Carbon\Carbon::now()->format('Y/m/d H:i') }} WIB

**Subject : [Data Sending] {{ $fullSubject }}**

---

### Package Information

- **Model**    : {{ $model }}
- **Part No**  : {{ $partNo }}
- **Doc Type** : {{ $docType }}
- **Category** : {{ $category }}
- **Revision** : Rev-{{ $revisionNo }}

---

### List of Files Sent

@if($files && count($files) > 0)
<ul style="padding-left: 20px; margin: 0;">
@foreach($files as $file)
<li style="margin-bottom: 5px; font-size: 14px;">{{ $file }}</li>
@endforeach
</ul>
@else
<p style="font-size: 14px; color: #6b7280; font-style: italic;">
    *(No files were found for this package..)*
</p>
@endif

<br>

@component('mail::table')
| NO | DISTRIBUTED TO | EXPIRED AT |
|:--- |:--- |:--- |
| 1 | {{ $shareToNames }} | {{ $expiredAt ? \Carbon\Carbon::parse($expiredAt)->format('Y/m/d') : '-' }} |
@endcomponent

---

@component('mail::panel')
**Attention:** This is an automated email. Please do not reply to this email. If you have any questions, please contact the SAI listed in the operations manual.
@endcomponent

@endcomponent