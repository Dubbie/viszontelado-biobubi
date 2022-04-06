{{-- Ügyfelet megjelenítő modal belső része, ezt kapod meg ha fetchelsz a "ugyfelek/{customerID}/megjegyzesek/html" URL-re. --}}
@if($customer->comments()->count()>0)
    @foreach($customer->comments as $comment)
        <div class="order-comment border rounded-lg p-3">
            <div class="row">
                <div class="col">
                    <p class="text-small mb-0">
                        <b>{{ $comment->user->name }}</b>
                        <span class="text-muted"> - </span>
                        <span>{{ $comment->created_at->format('Y.m.d H:i:s') }}</span>
                    </p>
                </div>
            </div>
            <p class="lead mb-0">{{ $comment->content }}</p>
        </div>
    @endforeach
@else
    <p>Nincsen az ügyfélhez kapcsolódó megjegyzés.</p>
@endif