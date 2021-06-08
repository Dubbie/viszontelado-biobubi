@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Kézikönyv</h1>
            </div>
            @if(Auth::user()->admin)
                <div class="col text-right">
                    <a href="#!" data-toggle="modal" data-target="#documentUploadModal" class="btn btn-teal shadow-sm">Dokumentum
                        feltöltése</a>
                </div>
            @endif
        </div>
        <div class="card card-body">
            @if(count($documents) > 0)
                @php /** @var \App\Document $document */ @endphp
                @foreach($documents as $document)
                    <div class="row">
                        <div class="col-md">
                            <a href="{{ url('/pdfjs/web/viewer.html?file=' . urlencode(url(str_replace('public', 'storage', $document->path)))) }}"
                               target="_blank"
                               class="btn text-left d-block btn-link font-weight-bold">
                                <b>{{ $document->name }}</b>
                            </a>
                        </div>
                        @if(Auth::user()->admin)
                            <div class="col-md-auto">
                                <a href="{{ action('DocumentController@deleteDocument', $document) }}"
                                   class="btn btn-sm btn-danger">Törlés</a>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <p class="mb-0">Jelenleg egy dokumentum sincs feltöltve.</p>
            @endif
        </div>
    </div>

    @include('modal.document-upload')
@endsection