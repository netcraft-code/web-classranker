<x-admin::layouts>
    <x-slot:title>
        @lang('class_ranker::app.study_materials.subjects.grades.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('class_ranker::app.study_materials.subjects.grades.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.study_materials.subjects.grades.create') }}"
                class="primary-button"
            >
                @lang('class_ranker::app.study_materials.subjects.grades.index.create-btn')
            </a>
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.study_materials.subjects.grades.index')" />

</x-admin::layouts>
