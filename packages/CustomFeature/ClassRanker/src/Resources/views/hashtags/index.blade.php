<x-admin::layouts>
    <x-slot:title>
        @lang('class_ranker::app.hashtags.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('class_ranker::app.hashtags.index.title')
        </p>
        <a href="{{ route('admin.hashtags.create') }}" class="primary-button">
            @lang('class_ranker::app.hashtags.index.create-btn')
        </a>
    </div>

    <x-admin::datagrid :src="route('admin.hashtags.index')" />
</x-admin::layouts>