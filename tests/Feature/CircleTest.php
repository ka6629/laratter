<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CircleTest extends TestCase
{
    use RefreshDatabase;

    // サークル選択画面の表示
    public function test_displays_followers_at_circle_page(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create();
        $follower->follows()->attach($user);

        $response = $this->actingAs($user)->get(route('circle.edit'));

        $response->assertStatus(200);
        $response->assertSee($follower->name);
    }

    // フォロワーをサークルに入れられる（フォロワー以外は無視される）
    public function test_can_select_followers_for_circle(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create();
        $follower->follows()->attach($user);
        $stranger = User::factory()->create();

        $this->actingAs($user)->put(route('circle.update'), [
            'member_ids' => [$follower->id, $stranger->id],
        ]);

        $this->assertDatabaseHas('circle_members', ['user_id' => $user->id, 'member_id' => $follower->id]);
        $this->assertDatabaseMissing('circle_members', ['user_id' => $user->id, 'member_id' => $stranger->id]);
    }

    // 全員のチェックを外すとサークルが空になる
    public function test_can_remove_all_members_from_circle(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create();
        $follower->follows()->attach($user);
        $user->circleMembers()->attach($follower);

        $this->actingAs($user)->put(route('circle.update'));

        $this->assertDatabaseMissing('circle_members', ['user_id' => $user->id]);
    }

    // サークルのみのTweetを作成できる
    public function test_can_create_circle_only_tweet(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tweets', ['tweet' => 'circle tweet', 'circle_only' => '1']);

        $this->assertDatabaseHas('tweets', ['tweet' => 'circle tweet', 'circle_only' => true]);
    }

    // サークルのメンバーだけがサークルのみのTweetを閲覧できる
    public function test_circle_only_tweet_is_visible_only_to_circle_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $other = User::factory()->create();
        $member->follows()->attach($owner);
        $other->follows()->attach($owner);
        $owner->circleMembers()->attach($member);

        $tweet = Tweet::factory()->create(['user_id' => $owner->id, 'tweet' => 'secret circle tweet', 'circle_only' => true]);

        // 本人
        $this->actingAs($owner)->get('/tweets')->assertSee($tweet->tweet);
        $this->actingAs($owner)->get(route('tweets.show', $tweet))->assertStatus(200);

        // サークルのメンバー
        $this->actingAs($member)->get('/tweets')->assertSee($tweet->tweet);
        $this->actingAs($member)->get(route('tweets.show', $tweet))->assertStatus(200);
        $this->actingAs($member)->get(route('profile.show', $owner))->assertSee($tweet->tweet);

        // サークル外のフォロワー
        $this->actingAs($other)->get('/tweets')->assertDontSee($tweet->tweet);
        $this->actingAs($other)->get(route('tweets.show', $tweet))->assertStatus(404);
        $this->actingAs($other)->get(route('profile.show', $owner))->assertDontSee($tweet->tweet);
        $this->actingAs($other)->get(route('profile.show', $other))->assertDontSee($tweet->tweet);
        $this->actingAs($other)->get(route('tweets.search', ['keyword' => 'secret']))->assertDontSee($tweet->tweet);
        $this->actingAs($other)->post(route('tweets.like', $tweet))->assertStatus(404);
        $this->actingAs($other)->post(route('tweets.comments.store', $tweet), ['comment' => 'hi'])->assertStatus(404);
    }

    // フォローを解除するとサークルからも外れる
    public function test_unfollow_removes_from_circle(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $member->follows()->attach($owner);
        $owner->circleMembers()->attach($member);

        $this->actingAs($member)->delete(route('follow.destroy', $owner));

        $this->assertDatabaseMissing('circle_members', ['user_id' => $owner->id, 'member_id' => $member->id]);
    }
}
