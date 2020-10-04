<!-- Modal -->
<div class="modal fade" id="massUpdateResellerModal" tabindex="-1" role="dialog"
     aria-labelledby="massUpdateResellerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ action('OrderController@massUpdateReseller') }}" method="POST">
                <input type="hidden" id="mur-order-ids" name="mur-order-ids" class="mass-order-id-input" value="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="massUpdateResellerModalLabel">Visztoneladó frissítése</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="mur-reseller-id">
                        <span>Viszonteladó</span>
                        <span class="icon icon-sm text-muted has-tooltip" data-toggle="tooltip"
                              data-placement="right"
                              title="Ezeket az állapotokat a Shoprenter szolgáltatja.">
                                <i class="fas fa-info-circle"></i>
                            </span>
                    </label>
                    <select name="mur-reseller-id" id="mur-reseller-id" class="custom-select">
                        <option value="" selected disabled hidden>Kérjük válasszon...</option>
                        @php /** @var \App\User $reseller */ @endphp
                        <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                        @foreach($resellers as $reseller)
                            <option value="{{ $reseller->id }}">{{ $reseller->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Viszonteladó frissítése</button>
                </div>
            </form>
        </div>
    </div>
</div>