<x-admin::layouts>
    <x-slot:title>
        DB
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            DB
        </p>
    </div>

    <h2>Database Tables</h2>

    @foreach($tables as $table)
        <div style="margin-bottom:10px;">
            <strong>{{ $table }}</strong>

            <a href="{{ route('admin.system.tables.export', $table) }}">
                Export
            </a>
        </div>
    @endforeach
</x-admin::layouts>