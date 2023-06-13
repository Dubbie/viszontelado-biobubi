@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
<img src="/storage/shoplogo.png" alt="BioBubi Shop Logo">
        @endcomponent
    @endslot

    {{-- Body --}}
# Holnapi kiszállítás

Kedves Bubizó!

{!! $message !!}

**További szép napot neked!**

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} BioBubi | Minden jog fenntartva.
        @endcomponent
    @endslot
@endcomponent
