<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    /** @use HasFactory<\Database\Factories\TweetFactory> */
    use HasFactory;
    protected $fillable = ['tweet', 'circle_only'];

    protected function casts(): array
    {
        return [
            'circle_only' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function liked()
  {
    return $this->belongsToMany(User::class)->withTimestamps();
  }
  
    public function comments()
  {
    return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
  }

  public function scopeTimeline(Builder $query, User $user): Builder
  {
    return $query
      ->where('user_id', $user->id) // 自分の Tweet
      ->orWhereIn('user_id', $user->follows->pluck('id')); // フォローしているユーザの Tweet
  }

  // $user が閲覧できる Tweet だけに絞り込む
  public function scopeVisibleTo(Builder $query, User $user): Builder
  {
    return $query->where(function (Builder $query) use ($user) {
      $query
        ->where('circle_only', false) // 全体公開の Tweet
        ->orWhere('user_id', $user->id) // 自分の Tweet
        ->orWhereIn('user_id', $user->circleOwners()->pluck('users.id')); // 自分をサークルに入れているユーザのサークル Tweet
    });
  }

  public function isVisibleTo(User $user): bool
  {
    return ! $this->circle_only
      || $this->user_id === $user->id
      || $this->user->circleMembers()->whereKey($user->id)->exists();
  }
  
}
