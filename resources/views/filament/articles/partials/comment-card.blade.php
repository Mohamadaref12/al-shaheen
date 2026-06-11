@php
    $statusClass = match ($comment->status) {
        'approved' => 'as-cmt--approved',
        'rejected' => 'as-cmt--rejected',
        default    => 'as-cmt--pending',
    };

    $statusLabel = match ($comment->status) {
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        default    => 'Pending',
    };
@endphp

<article
    wire:key="comment-{{ $comment->id }}"
    @class(['as-cmt', $statusClass, 'as-cmt--reply' => $isReply ?? false])
>
    <div class="as-cmt__accent"></div>

    <div class="as-cmt__main">
        <div class="as-cmt__head">
            <div class="as-cmt__user">
                <div class="as-cmt__avatar">
                    {{ strtoupper(substr($comment->user?->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <p class="as-cmt__name">{{ $comment->user?->name ?? 'Unknown' }}</p>
                    <p class="as-cmt__date">{{ $comment->created_at?->format('M j, Y · g:i A') }}</p>
                </div>
            </div>

            <span @class(['as-cmt__status', 'as-cmt__status--' . $comment->status])>
                {{ $statusLabel }}
            </span>
        </div>

        <p class="as-cmt__body">{{ $comment->body }}</p>

        <div class="as-cmt__actions">
            @if ($comment->status !== 'approved')
                <button
                    type="button"
                    class="as-cmt__btn as-cmt__btn--approve"
                    wire:click="approveComment({{ $comment->id }})"
                    wire:loading.attr="disabled"
                    wire:target="approveComment({{ $comment->id }})"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                    <span wire:loading.remove wire:target="approveComment({{ $comment->id }})">Approve</span>
                    <span wire:loading wire:target="approveComment({{ $comment->id }})">...</span>
                </button>
            @endif

            @if ($comment->status !== 'rejected')
                <button
                    type="button"
                    class="as-cmt__btn as-cmt__btn--reject"
                    wire:click="rejectComment({{ $comment->id }})"
                    wire:loading.attr="disabled"
                    wire:target="rejectComment({{ $comment->id }})"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                    <span wire:loading.remove wire:target="rejectComment({{ $comment->id }})">Reject</span>
                    <span wire:loading wire:target="rejectComment({{ $comment->id }})">...</span>
                </button>
            @endif
        </div>
    </div>
</article>

@if ($comment->replies->isNotEmpty())
    <div class="as-cmt__replies">
        @foreach ($comment->replies as $reply)
            @include('filament.articles.partials.comment-card', ['comment' => $reply, 'isReply' => true])
        @endforeach
    </div>
@endif
