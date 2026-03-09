<x-mail::message>
# ¡Gracias por contactarnos!

Hola **{{ $contactMessage->name }}**,

Recibimos tu mensaje y te responderemos lo antes posible. A continuación, una copia de lo que nos enviaste:

---

**Asunto:** {{ $contactMessage->subject }}

{{ $contactMessage->body }}

---

<x-mail::button :url="config('app.url')">
Visitar Archivo de Chile
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
