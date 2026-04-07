@component('mail::message')
# To, {{ $supplierName }}

Your request for a **re-share** of the following package in the **PROMISE** application has been **{{ $status }}**.

@if($status === 'approved')
@component('mail::button', ['url' => $appUrl])
Open the PROMISE Application
@endcomponent
@endif

---

@php
    $p = is_array($packageName) ? $packageName : [];
    $model      = $p['model'] ?? '-';
    $partNo     = $p['part_no'] ?? '-';
    $docType    = $p['doc_type'] ?? '-';
    $category   = $p['category'] ?? '-';
    $partGroup  = $p['code_part_group'] ?? '-';
    $revisionNo = $p['revision_no'] ?? '-';
    $ecnNo      = $p['ecn_no'] ?? '-';
    
    $subjectSegments = array_filter([$model, $partNo, $docType, $category, $partGroup, $ecnNo], fn($v) => $v !== null && $v !== '-' && $v !== '');
    $fullSubject = empty($subjectSegments) ? (is_string($packageName) ? $packageName : '-') : implode(' - ', $subjectSegments);

    $isApproved = $status === 'approved';
    $statusColor = $isApproved ? '#dcfce7' : '#fee2e2';
    $statusBorder = $isApproved ? '#bbf7d0' : '#fecaca';
    $statusText = $isApproved ? '#15803d' : '#b91c1c';
    $statusLabel = $isApproved ? 'Re-share Approved' : 'Re-share Rejected';
@endphp

**Status :** 
<span style="
    display:inline-block;
    padding:4px 10px;
    border-radius:9999px;
    background-color:{{ $statusColor }};
    border:1px solid {{ $statusBorder }};
    color:{{ $statusText }};
    font-size:12px;
    font-weight:600;
    line-height:1.4;
">
    {{ $statusLabel }}
</span>

Date : {{ \Carbon\Carbon::now()->format('Y/m/d H:i') }} WIB

**Subject : [{{ $statusLabel }}] {{ $fullSubject }}**

---

### Process Details

@if($isApproved)
- **New Expiry Date** : {{ \Carbon\Carbon::parse($expiredAt)->format('Y/m/d') }}
<p style="font-size: 14px; color: #15803d; margin-top: 10px;">
    You can now access and download the files again until the new expiry date.
</p>
@else
- **Reason for Rejection** :  
<div style="background-color: #f9fafb; padding: 10px; border-left: 4px solid #ef4444; margin-top: 5px; color: #b91c1c;">
    {{ $rejectReason }}
</div>
<p style="font-size: 14px; margin-top: 10px;">
    If you have any questions, please contact the purchasing department.
</p>
@endif

---

### Package Information

- **Model**    : {{ $model }}
- **Part No**  : {{ $partNo }}
- **Doc Type** : {{ $docType }}
- **Category** : {{ $category }}
- **Revision** : Rev-{{ $revisionNo }}

@component('mail::panel')
**Attention:** This is an automated email. Please do not reply to this email.
@endcomponent

@endcomponent
