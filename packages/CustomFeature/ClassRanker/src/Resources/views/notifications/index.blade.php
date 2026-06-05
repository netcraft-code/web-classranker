<x-admin::layouts>
    <x-slot:title>
        Notification
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Notification
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.notifications.create') }}"
                class="primary-button"
            >
                Add Notification
            </a>
        </div>
    </div>

    <!-- DataGrid -->
    <x-admin::datagrid :src="route('admin.notifications.index')" />
</x-admin::layouts>