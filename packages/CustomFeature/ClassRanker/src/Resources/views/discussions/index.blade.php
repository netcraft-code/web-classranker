<x-admin::layouts>
    <x-slot:title>@lang('class_ranker::app.discussions.index.title')</x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('class_ranker::app.discussions.index.title')
        </p>
        <a href="{{ route('admin.discussions.create') }}" class="primary-button">
            @lang('class_ranker::app.discussions.index.create-btn')
        </a>
    </div>

    <x-admin::datagrid :src="route('admin.discussions.index')" />
</x-admin::layouts>