<x-admin::layouts>
    <!--Page title -->
    <x-slot:title>
        @lang('class_ranker::app.study_materials.subjects.boards.edit.title')
    </x-slot>

    <!-- Edit Page Form -->
    <x-admin::form
        :action="route('admin.study_materials.subjects.boards.update', $board->id)"
        method="PUT"
        enctype="multipart/form-data"
    >

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.subjects.boards.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.study_materials.subjects.boards.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button type="submit" class="primary-button">
                    @lang('class_ranker::app.study_materials.subjects.boards.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- Body Content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Section -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <!-- Information -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('class_ranker::app.study_materials.subjects.boards.edit.information')
                    </p>

                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('class_ranker::app.study_materials.subjects.boards.edit.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="name"
                            rules="required"
                            :value="old('name', $board->name)"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Code -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('class_ranker::app.study_materials.subjects.boards.edit.code')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="code"
                            rules="required"
                            :value="old('code', $board->code)"
                        />

                        <x-admin::form.control-group.error control-name="code" />
                    </x-admin::form.control-group>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.study_materials.subjects.boards.edit.settings')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <!-- Status -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.subjects.boards.edit.status')
                            </x-admin::form.control-group.label>

                            @php $selectedValue = old('status') ?: $board->status @endphp

                            <!-- Visible in menu Hidden field -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                class="cursor-pointer"
                                name="status"
                                :checked="(boolean) $selectedValue"
                            />

                            <x-admin::form.control-group.control
                                type="switch"
                                class="cursor-pointer"
                                name="status"
                                value="1"
                                :label="trans('class_ranker::app.study_materials.subjects.boards.edit.status')"
                                :checked="(boolean) $selectedValue"
                            />
                        </x-admin::form.control-group>

                        <!-- Avatar -->
                        <div class="flex w-2/5 flex-col gap-2">
                            <p class="font-medium text-gray-800 dark:text-white">
                                @lang('class_ranker::app.study_materials.subjects.boards.edit.avatar')
                            </p>

                            <x-admin::media.images
                                name="avatar"
                                :files="$board->avatar ? [$board->avatar] : []"
                            />
                        </div>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
