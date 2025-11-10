@component('mail::message')
# Halo {{ $username }},

There is a new document that has been shared with you.

@component('mail::button', ['url' => route('receipts.detail', $packageId)])
View Document
@endcomponent

Thank you..<br>
From,<br>
{{ config('app.name') }}
@endcomponent
