<!-- Modal -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1" role="dialog" aria-labelledby="paymentMethodModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <form action="{{ action('OrderController@completeOrder') }}"
              class="form-complete-order d-inline-block ml-2" method="POST">
            @csrf
            {{-- Rejtett mező a megrendelésnek --}}
            <input id="payment-method-order-id" type="hidden" name="order-id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentMethodModalLabel">Kifizetés típusa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label for="payment-method">Kifizetés módja</label>
                        <select id="payment-method" name="payment-method" class="custom-select" required>
                            <option value="">Kérlek válassz fizetési módot...</option>
                            <option value="Készpénz">Készpénz</option>
                            <option value="Bankkártya">Bankkártya (Terminál)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-success font-weight-semibold h-100">
                        <span>Teljesítés</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
