<x-admin::layouts>
    <x-slot:title>
        @lang('class_ranker::app.plans.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('class_ranker::app.plans.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.plans.create') }}"
                class="primary-button"
            >
                @lang('class_ranker::app.plans.index.create-btn')
            </a>
        </div>
    </div>

    <!-- DataGrid -->
    <x-admin::datagrid :src="route('admin.plans.index')" />
</x-admin::layouts>