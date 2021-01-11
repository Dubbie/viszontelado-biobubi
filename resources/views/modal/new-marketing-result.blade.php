<!-- Modal -->
<div class="modal fade" id="newMarketingResult" tabindex="-1" role="dialog" aria-labelledby="newMarketingResultLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ action('MarketingResultController@store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newMarketingResultLabel">Hírdetési eredmény rögzítése</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="hq-income-reseller">
                        <div class="form-group">
                            <label for="mr-reseller-id">Viszonteladó:</label>
                            <select name="mr-reseller-id" id="mr-reseller-id" class="custom-select">
                                @php /** @var \App\User $reseller */ @endphp
                                @foreach($resellers as $reseller)
                                    <option value="{{ $reseller->id }}" data-balance="{{ $reseller->balance }}">{{ $reseller->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col-md">
                            <p class="h5 mb-0">Viszonteladó egyenlege:</p>
                        </div>
                        <div class="col-md-auto text-md-right">
                            <p id="reseller-balance" class="h3 text-primary font-weight-bold mb-0">0 Ft</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input class="custom-control-input" type="checkbox" id="mr-topup" name="mr-topup">
                            <label class="custom-control-label" for="mr-topup">Egyenleg feltöltése a viszonteladóhoz.</label>
                        </div>
                    </div>
                    <div id="topup-container" class="form-group" style="display: none;">
                        <label for="mr-topup-amount">Feltöltendő egyenleg</label>
                        <div class="input-group">
                            <input type="text" id="mr-topup-amount" name="mr-topup-amount" class="form-control text-right">
                            <div class="input-group-append">
                                <span class="input-group-text">Ft</span>
                            </div>
                        </div>

                        <small class="form-text text-muted">Ezt az összeget a rendszer jóváírja automatikusan <b>központi bevétel</b> és <b>viszonteladói kiadásként</b>.</small>
                    </div>

                    <hr>

                    <div class="form-row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="mr-spent">Elköltött összeg</label>
                                <div class="input-group">
                                    <input type="text" name="mr-spent" id="mr-spent" class="form-control text-right" placeholder="0" min="1" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Ft</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Ezt az összeget a rendszer automatikusan levonja az egyenlegből, de nem hoz létre se központi bevételt, se kiadást.</small>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="mr-reached">Elért emberek száma</label>
                                <div class="input-group">
                                    <input type="text" name="mr-reached" id="mr-reached" class="form-control text-right" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Fő</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mb-1">Lezárandó hónap</p>
                    <div class="form-row">
                        <div class="col-3">
                            <div class="form-group">
                                <label for="mr-date-year" class="sr-only">Lezárandó hónap (év)</label>
                                <input type="text" id="mr-date-year" name="mr-date-year" class="form-control" value="{{ \Carbon\Carbon::now()->subMonth()->format('Y') }}" required>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="form-group">
                                <label for="mr-date-month" class="sr-only">Lezárandó hónap (hónap)</label>
                                <select name="mr-date-month" id="mr-date-month" class="custom-select">
                                    @for($m = 1; $m<=12; ++$m)
                                        <option value="{{ $m }}" @if(\Carbon\Carbon::now()->subMonth()->month == $m) selected @endif>{{ \Illuminate\Support\Str::title(\Carbon\Carbon::now()->setMonth($m)->translatedFormat('F')) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mr-comment">Megjegyzés</label>
                        <textarea name="mr-comment" id="mr-comment" rows="2" class="form-control" placeholder="Ide tudsz hozzáfűzni dolgokat..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Hírdetési eredmény rögzítése</button>
                </div>
            </form>
        </div>
    </div>
</div>
