<!-- Modal -->
<div class="modal fade" id="worksheetModal" tabindex="-1" role="dialog" aria-labelledby="worksheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="worksheetModalLabel">Munkalap</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="worksheets-container" class="list-group">
                    @foreach(Auth::user()->worksheet as $wse)
                        <x-order :order="$wse->localOrder" type="worksheet" :worksheet="$wse"></x-order>
                    @endforeach
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                <a href="{{ route('worksheet.shipping-mail') }}" class="btn btn-sm btn-primary">Szállítólevél letöltése</a>
                <button id="btn-update-ws-orders" type="button" class="btn btn-sm btn-success">Sorrend mentése</button>
            </div>
        </div>
    </div>
</div>
