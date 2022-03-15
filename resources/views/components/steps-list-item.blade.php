@props(['active' => false, 'completed' => false, 'href' => '#!'])

@php
    /** @var boolean $active */
    /** @var boolean $completed */
    /** @var string $href */
    $classList = 'steps-list-item';

    if ($active) {
        $classList .= ' is-active';
    }

    if ($completed) {
        $classList .= ' is-completed';
    }
@endphp

<li {{ $attributes->merge(['class' => $classList]) }}>
    @if($completed)
        <a href="{{ $href }}">{{ $slot }}</a>
    @else
        {{ $slot }}
    @endif
</li>
