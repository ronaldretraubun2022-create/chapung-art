<?php

namespace App\Services;

use App\Models\Artwork;
use App\Models\ArtworkReview;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArtworkReviewService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function submit(Artwork $artwork, User $user, array $payload, Request $request): ArtworkReview
    {
        $orderItem = $this->reviewableOrderItem($artwork, $user);

        if (! $orderItem) {
            throw ValidationException::withMessages([
                'review' => __('chapung.reviews.errors.purchase_required'),
            ]);
        }

        if ($this->hasReviewed($artwork, $user)) {
            throw ValidationException::withMessages([
                'review' => __('chapung.reviews.errors.already_reviewed'),
            ]);
        }

        return DB::transaction(function () use ($artwork, $user, $payload, $request, $orderItem): ArtworkReview {
            return ArtworkReview::create([
                'artwork_id' => $artwork->id,
                'user_id' => $user->id,
                'order_item_id' => $orderItem->id,
                'reviewer_name' => $user->name,
                'reviewer_email' => $user->email,
                'rating' => (int) $payload['rating'],
                'title' => $payload['title'] ?? null,
                'body' => (string) $payload['body'],
                'status' => ArtworkReview::STATUS_PENDING,
                'is_verified_purchase' => true,
                'ip_hash' => hash('sha256', (string) $request->ip()),
                'user_agent' => str($request->userAgent() ?? '')->limit(255)->toString(),
            ]);
        });
    }

    public function hasReviewed(Artwork $artwork, User $user): bool
    {
        return ArtworkReview::query()
            ->where('artwork_id', $artwork->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function canReview(Artwork $artwork, ?User $user): bool
    {
        return $user !== null
            && ! $this->hasReviewed($artwork, $user)
            && $this->reviewableOrderItem($artwork, $user) !== null;
    }

    public function reviewableOrderItem(Artwork $artwork, User $user): ?OrderItem
    {
        return OrderItem::query()
            ->where('product_type', 'artwork')
            ->where('product_id', $artwork->id)
            ->whereHas('order', function ($query) use ($user): void {
                $query->where('customer_email', $user->email)
                    ->where(function ($query): void {
                        $query->where('payment_status', 'paid')
                            ->orWhereIn('status', ['shipped', 'completed']);
                    });
            })
            ->whereDoesntHave('review')
            ->latest('id')
            ->first();
    }
}
