<x-admin::layouts>
    <!--Page title -->
    <x-slot:title>
        @lang('class_ranker::app.study_materials.subjects.grades.edit.title')
    </x-slot>

    <!--Edit Page Form -->
    <x-admin::form
        :action="route('admin.study_materials.subjects.grades.update', $grade->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.subjects.grades.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.study_materials.subjects.grades.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <!--Update Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('class_ranker::app.study_materials.subjects.grades.edit.update-btn')
                </button>
            </div>
        </div>

        <!-- VUE COMPONENT -->
        <v-edit-grades></v-edit-grades>
    </x-admin::form>

    @pushOnce('scripts')
        <!-- ================= TEMPLATE ================= -->
        <script type="text/x-template" id="v-edit-grades-template">
            <!-- body content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left sub-component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <!-- Information -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.study_materials.subjects.grades.edit.information')
                        </p>

                        <!-- BOARD DETAILS (READ-ONLY) -->
                        <div
                            v-if="board"
                            style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);"
                        >
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                                <!-- Left: Details -->
                                <div style="flex: 1;">
                                    <p style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #cbd5e1;">
                                        📋 Board Details
                                    </p>

                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        <!-- Name -->
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Name:</span>
                                            <span style="font-size: 14px; font-weight: 500; color: #0f172a;">@{{ board.name }}</span>
                                        </div>

                                        <!-- Code -->
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Code:</span>
                                            <span style="display: inline-block; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; font-family: 'Courier New', monospace; color: #475569; border: 1px solid #e2e8f0;">@{{ board.code }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Avatar -->
                                <div 
                                    v-if="board.avatar"
                                    style="flex-shrink: 0;"
                                >
                                    <img
                                        :src="avatarUrl(board.avatar)"
                                        :alt="board.name"
                                        style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover; border: 3px solid #ffffff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Name -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.subjects.grades.edit.name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="name"
                                name="name"
                                rules="required"
                                v-model="name"
                                :value="old('name', $grade->name)"
                                :label="trans('class_ranker::app.study_materials.subjects.grades.edit.name')"
                                :placeholder="trans('class_ranker::app.study_materials.subjects.grades.edit.name')"
                            />

                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <!-- Code -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.subjects.grades.edit.code')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="code"
                                name="code"
                                rules="required"
                                v-model="code"
                                :value="old('code', $grade->code)"
                                :label="trans('class_ranker::app.study_materials.subjects.grades.edit.code')"
                                :placeholder="trans('class_ranker::app.study_materials.subjects.grades.edit.code')"
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
                                @lang('class_ranker::app.study_materials.subjects.grades.edit.settings')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('class_ranker::app.study_materials.subjects.grades.edit.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    value="1"
                                    :label="trans('class_ranker::app.study_materials.subjects.grades.edit.status')"
                                    :checked="(boolean) old('status', $grade->status)"
                                />

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                            <!-- Avatar -->
                            <div class="flex w-2/5 flex-col gap-2">
                                <p class="font-medium text-gray-800 dark:text-white">
                                    @lang('class_ranker::app.study_materials.subjects.grades.edit.avatar')
                                </p>

                                <p class="text-xs text-gray-500">
                                    @lang('class_ranker::app.study_materials.subjects.grades.edit.avatar-size')
                                </p>

                                <x-admin::media.images 
                                    name="avatar"
                                    :uploaded-images="$grade->avatar ? [['id' => 'avatar', 'url' => $grade->avatar_url]] : []"
                                />
                            </div>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <!-- ================= SCRIPT ================= -->
        <script type="module">
            app.component('v-edit-grades', {
                template: '#v-edit-grades-template',

                data() {
                    return {
                        // Board data from relationship
                        board: @json($grade->board),

                        // Grade data
                        name: '{{ old('name', $grade->name) }}',
                        code: '{{ old('code', $grade->code) }}',
                    };
                },

                methods: {
                    avatarUrl(avatarPath) {
                        return `{{ url('/storage/') }}/${avatarPath}`;
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>