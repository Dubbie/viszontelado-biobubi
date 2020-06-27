<!-- Modal -->
<div class="modal fade" id="documentUploadModal" tabindex="-1" role="dialog" aria-labelledby="documentUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ action('DocumentController@store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="documentUploadModalLabel">Dokumentumok feltöltése</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Dokumentumok</p>
                    <div class="custom-file">
                        <input type="file" name="documents[]" class="custom-file-input" id="document-uploader" multiple>
                        <label class="custom-file-label" for="document-uploader" data-browse="Böngészés">Válaszd ki a fájlokat...</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Feltöltés</button>
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