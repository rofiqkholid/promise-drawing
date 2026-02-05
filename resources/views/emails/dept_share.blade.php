@component('mail::message')
# To, {{ $user->name }}

Mr/Ms, **{{ $user->name }}**  
You have received a **shared document package** in the **PROMISE** application.

Please log in to the application to review the document information if needed.

@component('mail::button', ['url' => $share['app_url'] ?? '#' ])
Open the PROMISE Application
@endcomponent

---

**Status : SHARED TO DEPARTMENT**  
Date   : {{ $share['shared_at'] ?? '-' }}

**Subject : [Data Sharing] {{ $share['customer'] ?? '-' }}-{{ $share['model'] ?? '-' }}-{{ $share['doc_type'] ?? '-' }}-{{ $share['category'] ?? '-' }}**

---

### Package Information

- **Customer** : {{ $share['customer'] ?? '-' }}
- **Model**    : {{ $share['model'] ?? '-' }}
- **Part No**  : {{ $share['part_no'] ?? '-' }}
- **Doc Type** : {{ $share['doc_type'] ?? '-' }}
- **Category** : {{ $share['category'] ?? '-' }}
- **Revision** : Rev-{{ $share['revision_no'] ?? '-' }}

---

### Message / Note from Sender

@isset($share['note'])
> {{ $share['note'] }}
@else
> (No additional message.)
@endisset

Shared by: **{{ $share['shared_by'] ?? 'System' }}**

@component('mail::panel')
**Attention:** This is an automated email. Please do not reply to this email.  
@endcomponent

@endcomponent
