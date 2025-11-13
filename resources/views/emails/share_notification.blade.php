@component('mail::message')
# To, {{ $shareToNames }}
Mr, **{{ $username }}**
<br>
You have received a new file in the **Promise** application.
Please log in to the application to view and download the file.

@component('mail::button', ['url' => route('receipts.detail', $packageId)])
Open the Promise Application
@endcomponent

---

Date : {{ \Carbon\Carbon::now()->format('Y/m/d H:i') }} WIB

**Subject : [Data Sending] {{ $emailSubject }}**

### List of Files Sent

@if($files && $files->count() > 0)

<ul style="font-size: 14px; line-height: 2.5;">
@foreach($files as $file)
<li>{{ $file }}</li>
@endforeach
</ul>
@else

<p style="font-size: 14px;">
    *(No files were found for this package..)*
</p>
@endif

<br>

@component('mail::table')
| NO | PACKAGE | DISTRIBUTED TO |
|:--- |:--- |:--- |
| 1 | {{ $emailSubject }} | {{ $shareToNames }} |
@endcomponent

---

@component('mail::panel')
**Attention:** This is an automated email. Please do not reply to this email. If you have any questions, please contact the SAI listed in the operations manual.
@endcomponent

@endcomponent