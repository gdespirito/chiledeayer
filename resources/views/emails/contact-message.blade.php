<x-mail::message>
# Nuevo mensaje de contacto

**De:** {{ $contactMessage->name }} ({{ $contactMessage->email }})
@if($contactMessage->phone)
**Teléfono:** {{ $contactMessage->phone }}
@endif

**Asunto:** {{ $contactMessage->subject }}

---

{{ $contactMessage->body }}

<x-mail::button :url="config('app.url')">
Ir al sitio
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
