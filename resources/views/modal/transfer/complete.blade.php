<!-- Modal -->
<div class="modal fade" id="completeTransfer" tabindex="-1" role="dialog" aria-labelledby="completeTransferLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ action('MoneyTransferController@complete') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($transfer)
                    <input type="hidden" name="mt-transfer-id" value="{{ $transfer->id }}">
                @endif
                <div class="modal-header">
                    <h5 class="modal-title" id="completeTransferLabel">Átutalás teljesítése</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <p class="mb-0">Csatolmány</p>
                        <div class="custom-file">
                            <input type="file" name="mt-attachment" class="custom-file-input" id="mt-attachment"
                                   multiple>
                            <label class="custom-file-label" for="mt-attachment" data-browse="Böngészés">Válaszd ki a
                                fájlt...</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Átutalás teljesítése</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(() => {
            bsCustomFileInput.init();
        });
    </script>
@endsection