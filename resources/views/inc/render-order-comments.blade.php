@if($order->comments()->count()>0)
	@foreach($order->comments as $comment)
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
			<p class="lead mb-3">{{ $comment->content }}</p>
			<p class="mb-0">
				<small>Megrendelés állapota ekkor: </small>
				<small style="color: {{ $comment->status_color }};">{{ $comment->status_text }}</small>
			</p>
		</div>
	@endforeach
@else
	<p>Nincsen a megrendeléshez kapcsolódó megjegyzés.</p>
@endif