@component('mail::message')
# Halo {{ $username }},

There is a new document that has been shared with you.

@component('mail::button', ['url' => url('/package/'.$packageId)])
View Document
@endcomponent

Thank you..<br>
From,<br>
{{ config('app.mail_from') }}
@endcomponent
