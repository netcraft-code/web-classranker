<x-admin::layouts>
    <!--Page title -->
    <x-slot:title>
        @lang('class_ranker::app.study_materials.subjects.boards.create.title')
    </x-slot>

    <!--Create Page Form -->
    <x-admin::form
        :action="route('admin.study_materials.subjects.boards.store')"
        enctype="multipart/form-data"
    >

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.subjects.boards.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.study_materials.subjects.boards.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <!--Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('class_ranker::app.study_materials.subjects.boards.create.save-btn')
                </button>
            </div>
        </div>

        <!-- body content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left sub-component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                <!-- Information -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('class_ranker::app.study_materials.subjects.boards.create.information')
                    </p>

                    <!-- Name -->
                    <x-admin::form.control-group >
                        <x-admin::form.control-group.label class="required">
                            @lang('class_ranker::app.study_materials.subjects.boards.create.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="name"
                            name="name"
                            rules="required"
                            :value="old('name')"
                            :label="trans('class_ranker::app.study_materials.subjects.boards.create.name')"
                            :placeholder="trans('class_ranker::app.study_materials.subjects.boards.create.name')"
                            v-slugify-target:code="setValues"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Code -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('class_ranker::app.study_materials.subjects.boards.create.code')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="code"
                            name="code"
                            rules="required"
                            :value="old('code')"
                            :label="trans('class_ranker::app.study_materials.subjects.boards.create.code')"
                            :placeholder="trans('class_ranker::app.study_materials.subjects.boards.create.code')"
                            v-slugify
                        />

                        <x-admin::form.control-group.error control-name="code" />
                    </x-admin::form.control-group>
                </div>
            </div>

            <!-- Right sub-component -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <!-- Settings -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.study_materials.subjects.boards.create.settings')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <!-- status -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.subjects.boards.create.status')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                class="cursor-pointer"
                                name="status"
                                value="1"
                                :label="trans('class_ranker::app.study_materials.subjects.boards.create.status')"
                            />

                            <x-admin::form.control-group.error control-name="status" />
                        </x-admin::form.control-group>

                        <!-- Add Logo -->
                        <div class="flex w-2/5 flex-col gap-2">
                            <p class="font-medium text-gray-800 dark:text-white">
                                @lang('class_ranker::app.study_materials.subjects.boards.create.avatar')
                            </p>

                            <p class="text-xs text-gray-500">
                                @lang('class_ranker::app.study_materials.subjects.boards.create.avatar-size')
                            </p>

                            <x-admin::media.images name="avatar" />
                        </div>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
