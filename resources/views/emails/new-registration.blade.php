<x-mail::message>
# New Registration

A new user has registered on HARelay.

**Name:** {{ $user->name }}

**Email:** {{ $user->email }}

**Registered at:** {{ $user->created_at->format('F j, Y \a\t H:i') }} UTC

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
