@component('mail::message')
# To, Purchasing Team

A supplier has requested a **re-share** for an expired document package in the **PROMISE** application.
Please review this request.

@component('mail::button', ['url' => $requestUrl])
Process Re-share Request
@endcomponent

---

@php
    $p = is_array($packageName) ? $packageName : [];
    $customer   = $p['customer'] ?? '-';
    $model      = $p['model'] ?? '-';
    $partNo     = $p['part_no'] ?? '-';
    $docType    = $p['doc_type'] ?? '-';
    $category   = $p['category'] ?? '-';
    $partGroup  = $p['code_part_group'] ?? '-';
    $revisionNo = $p['revision_no'] ?? '-';
    $ecnNo      = $p['ecn_no'] ?? '-';
    
    $subjectSegments = array_filter([$customer, $model, $partNo, $docType, $category, $partGroup, $ecnNo], fn($v) => $v !== null && $v !== '-' && $v !== '');
    $fullSubject = empty($subjectSegments) ? (is_string($packageName) ? $packageName : '-') : implode(' - ', $subjectSegments);
@endphp

Date : {{ \Carbon\Carbon::now()->format('Y/m/d H:i') }} WIB

**Subject : [Action Required] {{ $fullSubject }}**

---

### Request Details

- **Requested By** : {{ $supplierName }}
- **Reason** :  
<div style="background-color: #f9fafb; padding: 10px; border-left: 4px solid #d1d5db; margin-top: 5px; color: #374151;">
    {{ $reason }}
</div>

---

### Package Information

- **Customer** : {{ $customer }}
- **Model**    : {{ $model }}
- **Part No**  : {{ $partNo }}
- **Doc Type** : {{ $docType }}
- **Category** : {{ $category }}
- **Revision** : Rev-{{ $revisionNo }}

@component('mail::panel')
**Attention:** This is an automated email. Please do not reply to this email.
@endcomponent

@endcomponent
