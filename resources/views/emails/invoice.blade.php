<x-mail::message>
# Invoice

Please find your invoice details below.

<x-mail::button :url="''">
View invoice
</x-mail::button>

Thank you,<br>
{{ config('app.name') }}
</x-mail::message>
