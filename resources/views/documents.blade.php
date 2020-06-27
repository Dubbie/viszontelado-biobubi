@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Dokumentumok</h1>
            </div>
            @if(Auth::user()->admin)
                <div class="col text-right">
                    <a href="#!" data-toggle="modal" data-target="#documentUploadModal" class="btn btn-teal shadow-sm">Dokumentum feltöltése</a>
                </div>
            @endif
        </div>
        <div class="card card-body">
            @if(count($documents) > 0)
               <div class="row">
                   <div class="col-md">
                       @php /** @var \App\Document $document */ @endphp
                       @foreach($documents as $document)
                           <a href="{{ action('DocumentController@getDocument', $document) }}" class="btn-link font-weight-bold">
                               <b>{{ $document->name }}</b>
                           </a>
                       @endforeach
                   </div>
                   @if(Auth::user()->admin)
                       <a href="{{ action('DocumentController@deleteDocument', $document) }}" class="btn btn-sm btn-danger">Törlés</a>
                   @endif
               </div>
            @else
                <p class="mb-0">Jelenleg egy dokument sincs feltöltve.</p>
            @endif
        </div>
    </div>

    @include('modal.document-upload')
@endsection