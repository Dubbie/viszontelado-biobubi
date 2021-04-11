@extends('layouts.app')

@php /** @var \App\Order $localOrder */ @endphp
@section('content')
	<div class="container">
		<p class="mb-0">
			<a href="{{ action('UserController@index') }}" class="btn-muted font-weight-bold text-decoration-none">
				<span class="icon icon-sm">
					<i class="fas fa-arrow-left"></i>
				</span>
				<span>Vissza a felhasználókhoz</span>
			</a>
		</p>
		<div class="row">
			<div class="col">
				<h1 class="font-weight-bold mb-4">Felhasználó részletei</h1>
			</div>
		</div>

		<div class="row flex-md-row-reverse">
			<div class="col-md-2">
				<a href="{{ action('UserController@edit', ['userId' => $user->id]) }}"
					 class="btn btn-sm btn-block btn-outline-secondary">Szerkesztés</a>
				<a href="{{ action('OrderController@index', ['filter-reseller' => $user->id]) }}"
					 class="btn btn-sm btn-block btn-link">Megrendelések</a>
			</div>
			<div class="col-md">
				<ul class="nav nav-tabs">
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#user-details">Adatok</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#user-finance">Pénzügy</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#user-stock">Készlet</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#user-monthly-reports">Havi riportok</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#user-yearly-report">Éves riport</a>
					</li>
				</ul>
				<div class="card card-body tab-content">
					<div class="tab-pane fade" id="user-details">
						@include('inc.user-details-content')
					</div>
					<div id="user-finance" class="tab-pane fade" data-user-id="{{ $user->id }}">
						@include('inc.reseller-finance')
					</div>
					<div id="user-stock" class="tab-pane fade">
						@include('inc.reseller-stock')
					</div>
					<div id="user-monthly-reports" class="tab-pane fade">
						@include('inc.reseller-monthly-reports')
					</div>
					<div id="user-yearly-report" class="tab-pane fade">
						@include('inc.reseller-yearly-report')
					</div>
				</div>
			</div>
		</div>
	</div>

	@include('modal.stock.add-to-reseller')
@endsection

@section('scripts')
	<script>
		$(() => {
			const rsAddRows = document.getElementById('rs-add-rows');
			const rsAddForm = document.getElementById('rs-add-form');
			const rsAddModal = document.getElementById('addStockToReseller');
			const csNewRows = document.getElementById('cs-new-rows');
			const csNewForm = document.getElementById('cs-new-form');
			const csList = document.getElementById('cs-list');
			const csNewModal = document.getElementById('newCentralStock');
			let activeTab = '{{ $activeTab }}';

			console.log(activeTab);

			function bindAllElements() {
				// Betöltjük a részleteit a viszonteladónak
				$('.btn-toggle-rs-add-modal').on('click', e => {
					$(rsAddForm).find('input[name="rs-add-reseller-id"]')[0].value = e.currentTarget.dataset.resellerId;
					updateRsAddDynamicElements();
				});

				// Riport választó
				$('#date').on('change', e => {
					$(e.currentTarget).closest('form').submit();
				});

				$(document).on('click', '.btn-remove-cs-row', e => {
					const btn = e.currentTarget;
					const row = $(btn).closest('.cs-row')[0];
					$(row).animate({
						opacity: 0,
						marginLeft: '100px',
					}, 350, () => {
						// Töröljük
						csNewRows.removeChild(row);
					});
				});

				$(document).on('click', '.btn-remove-rs-row', e => {
					const btn = e.currentTarget;
					const row = $(btn).closest('.rs-row')[0];
					$(row).animate({
						opacity: 0,
						marginLeft: '100px',
					}, 350, () => {
						// Töröljük
						csNewRows.removeChild(row);
					});
				});

				$(document).on('change', 'select[name="cs-new-product[]"]', e => {
					updateNewPrices();
				});
				$(document).on('keyup', 'input[name="cs-new-product-qty[]"]', e => {
					if (e.currentTarget.value.length > 0) {
						updateNewPrices();
					}
				});

				$(document).on('change', 'select[name="rs-add-stock[]"], input[name="rs-add-stock-qty[]"]', e => {
					updateRsAddDynamicElements();
				});
				$(document).on('keyup', 'input[name="rs-add-stock-qty[]"]', e => {
					if (e.currentTarget.value.length > 0) {
						updateRsAddDynamicElements();
					}
				});

				$('#btn-new-cs').on('click', e => {
					const btn = e.currentTarget;
					// A gombot inaktívvá tesszük, hogy ne tudja spammolni
					btn.classList.add('disabled');
					btn.disabled = true;

					// Betöltünk egy új sort
					$.ajax('{{ action('CentralStockController@getCentralStockRow') }}').done(html => {
						$(csNewRows).append(html);
						$(csNewRows.lastChild).slideDown(350);
					}).always(() => {
						btn.classList.remove('disabled');
						btn.disabled = false;
						updateNewPrices();
					});
				});

				$('#btn-add-rs').on('click', e => {
					const btn = e.currentTarget;
					// A gombot inaktívvá tesszük, hogy ne tudja spammolni
					btn.classList.add('disabled');
					btn.disabled = true;

					// Betöltünk egy új sort
					$.ajax('{{ action('CentralStockController@getResellerStockRow') }}').done(html => {
						$(rsAddRows).append(html);
						$(rsAddRows.lastChild).slideDown(350);
					}).always(() => {
						btn.classList.remove('disabled');
						btn.disabled = false;
						updateRsAddDynamicElements();
					});
				});

				// Központi készlet hozzáadása
				$(csNewForm).on('submit', e => {
					e.preventDefault();

					const btn = $(e.currentTarget).find('button[type="submit"]')[0];
					btn.classList.add('disabled');
					btn.disabled = true;

					// Beküldjük az ajaxot
					$.ajax('{{ action('CentralStockController@store') }}', {
						method: 'POST',
						data: $(csNewForm).serializeArray()
					}).done(response => {
						console.log(response);
						csNewRows.innerHTML = response.csNewHTML;
						csList.innerHTML = response.csListHTML;
						$(csNewModal).modal('toggle');
					}).always(() => {
						btn.classList.remove('disabled');
						btn.disabled = false;
						updateNewPrices();
					});
				});

				// Viszonteladó készletének frissítése
				$(rsAddForm).on('submit', e => {
					const btn = $(e.currentTarget).find('button[type="submit"]')[0];
					btn.classList.add('disabled');
					btn.disabled = true;
				});
			}

			function updateNewPrices() {
				// Frissítjük a központi készletet
				for (const el of $('#newCentralStock').find('.cs-row')) {
					const grossPrice = $(el).find('select[name="cs-new-product[]"] option:selected')[0].dataset.grossPrice;
					const qty = $(el).find('input[name="cs-new-product-qty[]"]')[0].value;
					$(el).find('.cs-gross-price')[0].innerText = grossPrice.toLocaleString() + ' Ft';
					$(el).find('.cs-total-price')[0].innerText = (grossPrice * qty).toLocaleString() + ' Ft';
				}
			}

			function updateRsAddDynamicElements() {
				// Frissítjük a központi készletet
				for (const el of $(rsAddForm).find('.rs-row')) {
					const grossPrice = $(el).find('select[name="rs-add-stock[]"] option:selected')[0].dataset.grossPrice;
					const qty = $(el).find('input[name="rs-add-stock-qty[]"]')[0].value;
					$(el).find('.rs-gross-price')[0].innerText = grossPrice.toLocaleString() + ' Ft';
					$(el).find('.rs-total-price')[0].innerText = (grossPrice * qty).toLocaleString() + ' Ft';
				}
			}

			function updateActiveTab() {
				const tgt = document.getElementById(activeTab);
				$('.nav-link[data-toggle="tab"][href="#' + activeTab + '"')[0].classList.add('active');
				tgt.classList.add('active', 'show');
			}

			function init() {
				bindAllElements();
				updateNewPrices();
				updateActiveTab();
			}

			init();
		});
	</script>
@endsection
