@extends('layouts.app')

@section('content')
    <div class="container">
        <p class="mb-0">
            <a href="{{ action('PostController@index') }}" class="btn-muted font-weight-bold text-decoration-none">
                <span class="icon icon-sm">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span>Vissza a bejegyzésekhez</span>
            </a>
        </p>
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Bejegyzés hozzáadása</h1>
            </div>
        </div>

        <div class="card card-body">
            <div class="row">
                <div class="col-lg-3">
                    <h5 class="font-weight-bold mb-2">Bejegyzés adatai</h5>
                    <p class="text-muted">Kérlek töltsd ki a megfelelő adatokkal.</p>
                </div>
                <div class="col-lg-9">
                    <form action="{{ action('PostController@store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="post-title">Bejegyzés címe</label>
                            <input type="text" name="post-title" id="post-title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="post-thumbnail">Bejegyzés képe</label>
                            <input type="file" name="post-thumbnail" id="post-thumbnail" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="post-content">Bejegyzés tartalma</label>
                            <textarea name="post-content" id="post-content" class="form-control" cols="30"
                                      rows="10"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Bejegyzés létrehozása</button>
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
            ClassicEditor.create(document.querySelector('#post-content'), {
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'fontSize',
                        '|',
                        'alignment',
                        'link',
                        'bulletedList',
                        'numberedList',
                        '|',
                        'indent',
                        'outdent',
                        '|',
                        'imageUpload',
                        'blockQuote',
                        'insertTable',
                        'mediaEmbed',
                        '|',
                        'undo',
                        'redo'
                    ]
                },
                language: 'hu',
                image: {
                    toolbar: [
                        'imageTextAlternative',
                        'imageStyle:full',
                        'imageStyle:side'
                    ]
                },
                table: {
                    contentToolbar: [
                        'tableColumn',
                        'tableRow',
                        'mergeTableCells'
                    ]
                },
                licenseKey: '',
                simpleUpload: {
                    // The URL that the images are uploaded to.
                    uploadUrl: '{{ action('PostController@handleUpload') }}',

                    // Enable the XMLHttpRequest.withCredentials property.
                    withCredentials: true,

                    // Headers sent along with the XMLHttpRequest to the upload server.
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                },
                mediaEmbed: {
                    previewsInData: true,
                }
            }).then(editor => {
                window.editor = editor;
            }).catch(error => {
                console.error('Oops, something gone wrong!');
                console.error('Please, report the following error in the https://github.com/ckeditor/ckeditor5 with the build id and the error stack trace:');
                console.warn('Build id: wp7ziqg98mz-dtucbwj6nu4w');
                console.error(error);
            });
        });
    </script>
@endsection