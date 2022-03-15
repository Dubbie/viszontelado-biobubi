<!-- Modal -->
<div class="modal fade" id="massOrderStatusModal" tabindex="-1" role="dialog"
     aria-labelledby="massOrderStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ action('OrderController@massUpdateStatus') }}" method="POST">
                <input type="hidden" id="mos-order-ids" name="mos-order-ids" class="mass-order-id-input" value="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="massOrderStatusModalLabel">Megrendelések állapotának módosítása</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="order-status-href">
                        <span>Megrendelési állapot</span>
                        <span class="icon icon-sm text-muted has-tooltip" data-toggle="tooltip"
                              data-placement="right"
                              title="Ezeket az állapotokat a Shoprenter szolgáltatja.">
                            <i class="fas fa-info-circle"></i>
                        </span>
                    </label>
                    <select name="order-status-href" id="order-status-href" class="custom-select">
                        <option value="" selected disabled hidden>Kérjük válasszon...</option>
                        @foreach($statuses as $os)
                            <option value="{{ $os->status_id }}">
                                {{ $os->name }}
                            </option>
                        @endforeach
                    </select>

                    <div id="payment-method-group" style="display: none">
                        <div class="form-group mt-2 mb-0">
                            <label for="mos-payment-method">Kifizetés módja</label>
                            <select id="mos-payment-method" name="mos-payment-method" class="custom-select" required>
                                <option value="">Kérlek válassz fizetési módot...</option>
                                <option value="Készpénz">Készpénz</option>
                                <option value="Bankkártya">Bankkártya (Terminál)</option>
                                <option value="Átutalás">Átutalás</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Állapotok frissítése</button>
                </div>
            </form>
        </div>
    </div>
</div>
