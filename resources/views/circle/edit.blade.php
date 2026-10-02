<x-layouts.app :title="__('サークル')">
  <div class="p-6">
    <h2 class="font-semibold text-xl mb-2">{{ __('サークル') }}</h2>
    <p class="text-sm text-gray-500 mb-4">サークルに入れるフォロワーを選択してください。「サークルのみ」で投稿したTweetは、ここで選択したフォロワーだけが閲覧できます。</p>

    @if (session('status'))
    <p class="mb-4 text-green-600">{{ session('status') }}</p>
    @endif

    @if ($followers->count())
    <form method="POST" action="{{ route('circle.update') }}">
      @csrf
      @method('PUT')
      <p class="text-sm mb-2">サークルのメンバー: {{ $memberIds->count() }}人 / フォロワー: {{ $followers->count() }}人</p>
      <div class="mb-4">
        @foreach ($followers as $follower)
        <label class="flex items-center gap-2 mb-2 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-pointer">
          <input type="checkbox" name="member_ids[]" value="{{ $follower->id }}" @checked($memberIds->contains($follower->id))>
          <span>{{ $follower->name }}</span>
        </label>
        @endforeach
      </div>
      <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">保存</button>
    </form>
    @else
    <p>まだフォロワーがいません。フォローされると、サークルに入れるフォロワーを選択できるようになります。</p>
    @endif
  </div>
</x-layouts.app>
