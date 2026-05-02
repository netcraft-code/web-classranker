<x-admin::layouts>
    <x-slot:title>
        DB
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            DB
        </p>
    </div>

    <h2>Storage Files</h2>

    @foreach($files as $file)
        <div style="margin-bottom:10px;">
            {{ $file }}

            <a href="{{ route('admin.system.files.download', $file) }}">
                Download
            </a>
        </div>
    @endforeach
</x-admin::layouts>