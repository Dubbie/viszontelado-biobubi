@extends('layouts.app')

@php /** @var \App\User $user */ @endphp
@section('content')
    @php /** @var \App\User $user */ @endphp
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <p class="mb-0">
                    <a href="{{ action('UserController@index') }}"
                       class="btn-muted font-weight-bold text-decoration-none">
                        <span class="icon icon-sm">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Vissza a felhasználókhoz</span>
                    </a>
                </p>
                <div class="row">
                    <div class="col">
                        <h1 class="font-weight-bold mb-4">Felhasználó szerkesztése</h1>
                    </div>
                </div>
                <div class="card card-body">
                    <form id="user-form" action="{{ action('UserController@update', ['userId' => $user->id]) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row mb-4">
                            <div class="col-lg-4">
                                <h5 class="font-weight-bold mb-1">Alapvető adatok</h5>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="u-name">Név</label>
                                    <input type="text" id="u-name" name="u-name" class="form-control"
                                           value="{{ $user->name }}" required>
                                    <small class="form-text text-muted">Ez a viszonteladó neve</small>
                                </div>
                                <div class="form-group">
                                    <label for="u-email">E-mail cím</label>
                                    <input type="email" id="u-email" name="u-email" class="form-control"
                                           value="{{ $user->email }}" required>
                                    <small class="form-text text-muted">Ez a viszonteladó e-mail címe amivel be tud
                                        lépni
                                    </small>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="u-email-notifications" name="u-email-notifications"
                                               @if($user->emailNotificationsEnabled()) checked @endif>
                                        <label class="custom-control-label" for="u-email-notifications">E-mail értesítések küldése új megrendelésekről</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="u-zip">Hozzárendelt régiók</label>

                                    @if(count($user->regions) > 0)
                                        @foreach($user->regions->sortBy('name') as $region)
                                            <p class="font-weight-bold mb-2">{{ $region->name }}</p>

                                            <div class="row">
                                                @foreach($region->zips()->orderBy('zip')->get() as $rZip)
                                                    <div class="col-md-4 col-lg-3">
                                                        <p class="font-weight-semibold mb-0">{{ $rZip->zip }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="alert alert-info">
                                            <p class="lead mb-0">A felhasználóhoz nem tartozik egy régió sem, ezért nem
                                                fog kapni
                                                megrendeléseket automatikusan.</p>
                                        </div>
                                    @endif

                                    <div class="alert alert-warning mt-3">Az elérhető régiókat mostantól <a
                                            href="{{ action('RegionController@index') }}">itt</a> tudod
                                        szerkeszteni.
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="u-aam" name="u-aam"
                                               @if($user->vat_id == 992) checked @endif>
                                        <label class="custom-control-label" for="u-aam">A felhasználó által kiállított
                                            számlák Alanyi Adómentesek.</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-4">
                                <h5 class="font-weight-bold mb-1">Számlázási adatok</h5>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="u-billing-name">Név</label>
                                    <input type="text" id="u-billing-name" name="u-billing-name"
                                           value="{{ $user->details ? $user->details->billing_name : '' }}"
                                           class="form-control">
                                </div>
                                <div class="form-row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="u-billing-zip">Ir. Szám.</label>
                                            <input type="text" id="u-billing-zip" name="u-billing-zip"
                                                   value="{{ ($user->details && $user->details->billingAddress) ? $user->details->billingAddress->zip : '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="form-group">
                                            <label for="u-billing-city">Város</label>
                                            <input type="text" id="u-billing-city" name="u-billing-city"
                                                   value="{{ ($user->details && $user->details->billingAddress) ? $user->details->billingAddress->city : '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="u-billing-address1">Cím</label>
                                    <input type="text" id="u-billing-address1" name="u-billing-address1"
                                           value="{{ ($user->details && $user->details->billingAddress) ? $user->details->billingAddress->address1 : '' }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="u-billing-address2">Cím 2 <span class="text-muted">(Emelet, ajtó
                                            stb.)</span></label>
                                    <input type="text" id="u-billing-address2" name="u-billing-address2"
                                           value="{{ ($user->details && $user->details->billingAddress) ? $user->details->billingAddress->address2 : '' }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="u-billing-tax-number">Adószám</label>
                                    <input type="text" id="u-billing-tax-number" name="u-billing-tax-number"
                                           value="{{ $user->details ? $user->details->billing_tax_number : '' }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="u-billing-account-number">Számlaszám</label>
                                    <input type="text" id="u-billing-account-number" name="u-billing-account-number"
                                           value="{{ $user->details ? $user->details->billing_account_number : '' }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-4">
                                <h5 class="font-weight-bold mb-1">Szállítási adatok</h5>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="u-shipping-name">Név</label>
                                    <input type="text" id="u-shipping-name" name="u-shipping-name"
                                           value="{{ $user->details ? $user->details->shipping_name : '' }}"
                                           class="form-control">
                                </div>
                                <div class="form-row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="u-shipping-zip">Ir. Szám.</label>
                                            <input type="text" id="u-shipping-zip" name="u-shipping-zip"
                                                   value="{{ ($user->details && $user->details->shippingAddress) ? $user->details->shippingAddress->zip : '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="form-group">
                                            <label for="u-shipping-city">Város</label>
                                            <input type="text" id="u-shipping-city" name="u-shipping-city"
                                                   value="{{ ($user->details && $user->details->shippingAddress) ? $user->details->shippingAddress->city : '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="u-shipping-address1">Cím</label>
                                    <input type="text" id="u-shipping-address1" name="u-shipping-address1"
                                           value="{{ ($user->details && $user->details->shippingAddress) ? $user->details->shippingAddress->address1 : '' }}"
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="u-shipping-address2">Cím 2 <span class="text-muted">(Emelet, ajtó
                                            stb.)</span></label>
                                    <input type="text" id="u-shipping-address2" name="u-shipping-address2"
                                           value="{{ ($user->details && $user->details->shippingAddress) ? $user->details->shippingAddress->address2 : '' }}"
                                           class="form-control">
                                </div>
                                <div class="form-row">
                                    <div class="col-lg">
                                        <div class="form-group">
                                            <label for="u-shipping-email">E-mail cím</label>
                                            <input type="email" id="u-shipping-email" name="u-shipping-email"
                                                   value="{{ $user->details ? $user->details->shipping_email : '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg">
                                        <div class="form-group">
                                            <label for="u-shipping-phone">Telefonszám</label>
                                            <input type="tel" id="u-shipping-phone" name="u-shipping-phone"
                                                   value="{{ $user->details ? $user->details->shipping_phone : '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <h5 class="font-weight-bold mb-1">Integráció</h5>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="u-integration-type">Számlázó rendszer</label>
                                    <select name="u-integration-type" id="u-integration-type" class="form-control">
                                        <option value="">Nem használ</option>
                                        <option value="BILLINGO" @if($user->usesBillingo()) selected @endif>Billingo</option>
                                        <option value="THARANIS" @if($user->usesTharanis()) selected @endif>Tharanis ERP</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="billingo-row" class="row" style="display: none">
                            <div class="col-lg-4">
                                <h5 class="font-weight-bold mb-1">Billingo integráció</h5>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="u-billingo-api-key">Billingo API Kulcs <small
                                            class="font-weight-bold text-muted">Billingo API v3</small></label>
                                    <input type="text" id="u-billingo-api-key" name="u-billingo-api-key"
                                           value="{{ $user->billingo_api_key }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="u-block-uid">Számlatömb azonosító</label>
                                    <input type="text" id="u-block-uid" name="u-block-uid" class="form-control"
                                           value="{{ $user->block_uid }}">
                                    <small class="form-text text-muted"><a
                                            href="https://app.billingo.hu/beallitasok/szamlazo/szamlatomb">Ezen</a>
                                        az oldalon található, Tömb API ID-t kell ide beírni. <b class="d-block">FONTOS: Nem a Legacy API ID-t.</b>
                                    </small>
                                </div>
                                <div class="form-group">
                                    <button type="button" id="btn-billingo-api-test" class="btn btn-sm btn-outline-secondary mr-2">
                                        <span class="text">Billingo integráció teszelése</span>
                                        <span class="loading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </span>
                                    <span class="ml-2">Csatlakozás folyamatban...</span>
                                </span>
                                    </button>
                                </div>
                                <div id="billingo-test-results" class="mb-5" style="display: none;">

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <h5 class="font-weight-bold mb-1">Marketing egyenleg</h5>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="u-marketing-balance">Marketing egyenleg</label>
                                    <div class="input-group">
                                        <input type="text" id="u-marketing-balance" name="u-marketing-balance" aria-describedby="currency-text" value="{{ $user->marketingBalance() }}" class="text-right form-control">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="currency-text">Ft</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-8 offset-lg-4">
                                <button type="submit" class="btn btn-sm btn-success btn-block">
                                    <span class="text">Felhasználó frissítése</span>
                                    <span class="loading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </span>
                                    <span class="ml-2">Frissítés...</span>
                                </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            const selectIntegrationType = document.getElementById('u-integration-type');
            const rowBillingo = document.getElementById('billingo-row');

            $('#u-billing-account-number').mask('00000000-00000000-00000000');

            const updateIntegrationRows = () => {
                const selected =selectIntegrationType.options[selectIntegrationType.selectedIndex].value;

                if (selected === 'BILLINGO') {
                    $(rowBillingo).show();
                } else if (selected === 'THARANIS') {
                    $(rowBillingo).hide();
                } else {
                    $(rowBillingo).hide();
                }
            }

            const bindElements = () => {
                $(selectIntegrationType).on('change', () => {
                    updateIntegrationRows();
                });
            }

            const init = () =>{
                bindElements();
                updateIntegrationRows();
            }

            init();
        });
    </script>
@endsection
