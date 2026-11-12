@props(['href', 'type' => 'primary'])

<a href={{ $href }} {{ $attributes->merge(["class" => "btn btn-{$type}"]) }}>
    {{ $slot }}
</a>