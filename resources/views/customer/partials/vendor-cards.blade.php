{{-- A bare run of vendor cards. Rendered standalone for the category page's
     infinite-scroll endpoint, so it must stay free of any wrapper markup. --}}
@foreach($vendors as $vendor)
    @include('customer.partials.vendor-card', [
        'vendor'    => $vendor,
        'allThemes' => $allThemes,
        'eager'     => ($eagerFirst ?? false) && $loop->first,
    ])
@endforeach
