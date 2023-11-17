@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Biobubi Futár</h1>
            </div>
        </div>
        <div class="card card-body">
            @if (count($regions) == 0)
                {{-- TODO --}}
                <p>Nincsenek régiók</p>
            @else
                @php
                    /** @var \App\Region $region */
                @endphp
                <form action="#!" method="POST">
                    <div class="row d-none d-md-flex">
                        <div class="col-md-5">
                            <small class="font-weight-semiboldbold">Régió</small>
                        </div>
                        <div class="col-md-4">
                            <small class="font-weight-semiboldbold">Viszonteladó</small>
                        </div>
                        <div class="col-md-2 text-md-center">
                            <small class="font-weight-semiboldbold">BioBubi futár szállítás</small>
                        </div>
                    </div>
                    @foreach ($regions as $region)
                        <div class="row mt-2">
                            <div class="col-md-5">
                                <p class="mb-0 font-weight-bold text-truncate">{{ $region->name }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-0 text-truncate">{{ $region->user->name }}</p>
                            </div>
                            <div class="col-md-2 text-md-center">
                                <div class="d-flex justify-content-center sw-container">
                                    <p
                                        class="sw-no mr-2 mb-0 {{ !$region->biobubi_delivery ? 'text-dark font-weight-bold' : 'text-black-50' }}">
                                        Nem
                                    </p>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input sw-delivery" name="sw-delivery"
                                            {{ $region->biobubi_delivery ? 'checked' : '' }}
                                            data-region-id="{{ $region->id }}" id="sw-region-{{ $region->id }}">
                                        <label
                                            class="custom-control-label sw-yes {{ $region->biobubi_delivery ? 'text-dark font-weight-bold' : 'text-black-50' }}"
                                            for="sw-region-{{ $region->id }}">Igen</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </form>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            function handleChange(regionId, checked, element) {
                $.ajax({
                    url: "{{ action('RegionController@updateBiobubiDelivery') }}",
                    method: 'POST',
                    dataType: 'json',
                    encode: true,
                    data: {
                        'region_id': regionId,
                        'checked': checked,
                    }
                }).done(response => {
                    console.log(response);
                    if (response.success) {
                        updateElement(element, checked);
                    } else {
                        element.checked = !checked;
                    }
                });
            }

            function updateElement(element, checked) {
                if (!element) {
                    return;
                }

                const $container = $(element).closest('.sw-container');
                if (checked) {
                    $container.find('.sw-no').removeClass('text-dark font-weight-bold');
                    $container.find('.sw-no').addClass('text-black-50');

                    $container.find('.sw-yes').removeClass('text-black-50');
                    $container.find('.sw-yes').addClass('text-dark font-weight-bold');
                } else {
                    $container.find('.sw-no').removeClass('text-black-50');
                    $container.find('.sw-no').addClass('text-dark font-weight-bold');

                    $container.find('.sw-yes').removeClass('text-dark font-weight-bold');
                    $container.find('.sw-yes').addClass('text-black-50');
                }
            }

            function init() {
                $('.sw-delivery').on('change', e => {
                    var regionId = $(e.currentTarget).data('region-id');
                    var checked = $(e.currentTarget).is(':checked');

                    handleChange(regionId, checked, e.currentTarget);
                });
            }

            init();
        });
    </script>
@endsection
