<x-admin::layouts>
    <x-slot:title>
        Customer Plans
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Customer Plans
        </p>
    </div>

    <!-- DataGrid -->
    <x-admin::datagrid :src="route('admin.plans.customer_plans.index')" />
</x-admin::layouts>