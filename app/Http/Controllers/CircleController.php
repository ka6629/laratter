<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CircleController extends Controller
{
    /**
     * サークルに入れるフォロワーを選択する画面
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        $followers = $user->followers()->orderBy('name')->get();
        $memberIds = $user->circleMembers()->pluck('users.id');

        return view('circle.edit', compact('followers', 'memberIds'));
    }

    /**
     * 選択したフォロワーでサークルを更新
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'member_ids' => 'array',
            'member_ids.*' => 'integer',
        ]);

        // フォロワー以外のIDが送られてきても無視する
        $memberIds = $user->followers()
            ->whereIn('users.id', $request->input('member_ids', []))
            ->pluck('users.id');

        $user->circleMembers()->sync($memberIds);

        return redirect()->route('circle.edit')->with('status', 'サークルを更新しました');
    }
}
