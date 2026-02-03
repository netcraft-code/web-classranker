<x-admin::layouts>
    <!--Page title -->
    <x-slot:title>
        @lang('class_ranker::app.study_materials.subjects.grades.create.title')
    </x-slot>

    <!--Create Page Form -->
    <x-admin::form
        :action="route('admin.study_materials.subjects.grades.store')"
        enctype="multipart/form-data"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.subjects.grades.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.study_materials.subjects.grades.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <!--Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('class_ranker::app.study_materials.subjects.grades.create.save-btn')
                </button>
            </div>
        </div>

        <!-- VUE COMPONENT -->
        <v-create-grades></v-create-grades>
    </x-admin::form>

    @pushOnce('scripts')
        <!-- ================= TEMPLATE ================= -->
        <script type="text/x-template" id="v-create-grades-template">
            <!-- body content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left sub-component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <!-- Information -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.study_materials.subjects.grades.create.information')
                        </p>

                        <!-- BOARD DROPDOWN -->
                        <div class="box-shadow rounded bg-white p-4 mb-4">
                            <p class="font-semibold mb-3">Select Board</p>

                            <select
                                name="board_id"
                                v-model="selectedBoardId"
                                @change="onBoardChange"
                                class="w-full border rounded p-3"
                                required
                            >
                                <option value="" disabled>-- Select Board --</option>

                                <option
                                    v-for="board in boards"
                                    :key="board.id"
                                    :value="board.id"
                                >
                                    @{{ board.name }} (@{{ board.code }})
                                </option>
                            </select>
                        </div>

                        <!-- BOARD DETAILS -->
                        <div
                            v-if="selectedBoard"
                            style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);"
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
                                            <span style="font-size: 14px; font-weight: 500; color: #0f172a;">@{{ selectedBoard.name }}</span>
                                        </div>

                                        <!-- Code -->
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Code:</span>
                                            <span style="display: inline-block; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; font-family: 'Courier New', monospace; color: #475569; border: 1px solid #e2e8f0;">@{{ selectedBoard.code }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Avatar -->
                                <div 
                                    v-if="selectedBoard.avatar"
                                    style="flex-shrink: 0;"
                                >
                                    <img
                                        :src="avatarUrl(selectedBoard.avatar)"
                                        :alt="selectedBoard.name"
                                        style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover; border: 3px solid #ffffff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Name -->
                        <x-admin::form.control-group >
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.subjects.grades.create.name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="name"
                                name="name"
                                rules="required"
                                v-model="name"
                                :value="old('name')"
                                :label="trans('class_ranker::app.study_materials.subjects.grades.create.name')"
                                :placeholder="trans('class_ranker::app.study_materials.subjects.grades.create.name')"
                                v-slugify-target:code="setValues"
                            />

                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <!-- Code -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.subjects.grades.create.code')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="code"
                                name="code"
                                rules="required"
                                v-model="code"
                                :value="old('code')"
                                :label="trans('class_ranker::app.study_materials.subjects.grades.create.code')"
                                :placeholder="trans('class_ranker::app.study_materials.subjects.grades.create.code')"
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
                                @lang('class_ranker::app.study_materials.subjects.grades.create.settings')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('class_ranker::app.study_materials.subjects.grades.create.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    value="1"
                                    :label="trans('class_ranker::app.study_materials.subjects.grades.create.status')"
                                    :checked="(boolean) old('status')"
                                />

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                            <!-- Add Logo -->
                            <div class="flex w-2/5 flex-col gap-2">
                                <p class="font-medium text-gray-800 dark:text-white">
                                    @lang('class_ranker::app.study_materials.subjects.grades.create.avatar')
                                </p>

                                <p class="text-xs text-gray-500">
                                    @lang('class_ranker::app.study_materials.subjects.grades.create.avatar-size')
                                </p>

                                <x-admin::media.images name="avatar" />
                            </div>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <!-- ================= SCRIPT ================= -->
        <script type="module">
            app.component('v-create-grades', {
                template: '#v-create-grades-template',

                data() {
                    return {
                        boards: @json($boards),

                        selectedBoardId: '',
                        selectedBoard: null,

                        name: '',
                        code: '',
                        codeIsAutoGenerated: true,
                    };
                },

                watch: {
                    // 👇 Auto-generate code from name
                    name(newName) {
                        if (!this.code || this.codeIsAutoGenerated) {
                            this.code = this.slugify(newName);
                            this.codeIsAutoGenerated = true;
                        }
                    }
                },

                methods: {
                    onBoardChange() {
                        this.selectedBoard = this.boards.find(
                            board => board.id == this.selectedBoardId
                        );
                    },

                    avatarUrl(avatarPath) {
                        return `{{ url('/storage/') }}/${avatarPath}`;
                    },
                    
                    onCodeInput() {
                        this.codeIsAutoGenerated = false;
                    },

                    slugify(text) {
                        return text
                            .toString()
                            .toLowerCase()
                            .trim()
                            .replace(/\s+/g, '-')
                            .replace(/[^\w\-]+/g, '')
                            .replace(/\-\-+/g, '-');
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

