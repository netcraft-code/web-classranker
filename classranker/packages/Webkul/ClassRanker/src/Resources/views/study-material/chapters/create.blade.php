<x-admin::layouts>
    <!--Page title -->
    <x-slot:title>
        @lang('class_ranker::app.study_materials.chapters.create.title')
    </x-slot>

    <!--Create Page Form -->
    <x-admin::form
        :action="route('admin.study_materials.chapters.store')"
        enctype="multipart/form-data"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.chapters.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.study_materials.chapters.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <!--Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('class_ranker::app.study_materials.chapters.create.save-btn')
                </button>
            </div>
        </div>

        <!-- VUE COMPONENT -->
        <v-create-chapters></v-create-chapters>
    </x-admin::form>

    @pushOnce('scripts')
        <!-- ================= TEMPLATE ================= -->
        <script type="text/x-template" id="v-create-chapters-template">
            <!-- body content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left sub-component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <!-- Information -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.study_materials.chapters.create.information')
                        </p>

                        <!-- Chapter Title -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.chapters.create.title')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="title"
                                name="title"
                                rules="required"
                                v-model="title"
                                :value="old('title')"
                                :label="trans('class_ranker::app.study_materials.chapters.create.title')"
                                :placeholder="trans('class_ranker::app.study_materials.chapters.create.title')"
                            />

                            <x-admin::form.control-group.error control-name="title" />
                        </x-admin::form.control-group>

                        <!-- Chapter Code -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.chapters.create.code')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="code"
                                name="code"
                                rules="required"
                                v-model="code"
                                :value="old('code')"
                                :label="trans('class_ranker::app.study_materials.chapters.create.code')"
                                :placeholder="trans('class_ranker::app.study_materials.chapters.create.code')"
                            />

                            <x-admin::form.control-group.error control-name="code" />
                        </x-admin::form.control-group>

                        <!-- Status -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.chapters.create.status')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                name="status"
                                value="1"
                                :label="trans('class_ranker::app.study_materials.chapters.create.status')"
                                :checked="(boolean) old('status')"
                            />

                            <x-admin::form.control-group.error control-name="status" />
                        </x-admin::form.control-group>

                        <!-- Premium Option -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.study_materials.chapters.create.is-premium')
                            </x-admin::form.control-group.label>

                            <input
                                type="hidden"
                                name="is_premium"
                                :value="chapterIsPremium ? 1 : 0"
                            >

                            <input
                                type="checkbox"
                                v-model="chapterIsPremium"
                                :disabled="isPremiumLocked"
                                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >

                            <p
                                v-if="isPremiumLocked"
                                class="text-xs text-gray-500 mt-1"
                            >
                                This is locked because selected book is premium.
                            </p>

                            <x-admin::form.control-group.error control-name="is_premium" />
                        </x-admin::form.control-group>

                        <!-- Avatar -->
                        <div class="flex w-2/5 flex-col gap-2">
                            <p class="font-medium text-gray-800 dark:text-white">
                                @lang('class_ranker::app.study_materials.chapters.create.avatar')
                            </p>

                            <p class="text-xs text-gray-500">
                                @lang('class_ranker::app.study_materials.chapters.create.avatar-size')
                            </p>

                            <x-admin::media.images name="avatar" />
                        </div>
                    </div>
                </div>

                <!-- Right sub-component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    <!-- Settings -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('class_ranker::app.study_materials.chapters.create.settings')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- STEP 1: BOARD DROPDOWN -->
                            <div class="box-shadow rounded bg-white p-4 mb-4">
                                <p class="font-semibold mb-3">Select Board <span class="text-red-500">*</span></p>

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

                                            <!-- Premium Status -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Premium:</span>
                                                
                                                <span 
                                                    v-if="selectedBoard.is_premium"
                                                    style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%); color: #ffffff; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; box-shadow: 0 2px 4px rgba(251, 146, 60, 0.3);"
                                                >
                                                    ⭐ Premium
                                                </span>
                                                
                                                <span 
                                                    v-else
                                                    style="display: inline-block; background: #e2e8f0; color: #475569; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;"
                                                >
                                                    Standard
                                                </span>
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

                            <!-- STEP 2: GRADE DROPDOWN -->
                            <div 
                                v-if="selectedBoard && filteredGrades.length > 0"
                                class="box-shadow rounded bg-white p-4 mb-4"
                            >
                                <p class="font-semibold mb-3">Select Grade <span class="text-red-500">*</span></p>

                                <select
                                    name="grade_id"
                                    v-model="selectedGradeId"
                                    @change="onGradeChange"
                                    class="w-full border rounded p-3"
                                    required
                                >
                                    <option value="" disabled>-- Select Grade --</option>

                                    <option
                                        v-for="grade in filteredGrades"
                                        :key="grade.id"
                                        :value="grade.id"
                                    >
                                        @{{ grade.name }} (@{{ grade.code }})
                                    </option>
                                </select>
                            </div>

                            <!-- GRADE DETAILS -->
                            <div
                                v-if="selectedGrade"
                                style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);"
                            >
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                                    <!-- Left: Details -->
                                    <div style="flex: 1;">
                                        <p style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #cbd5e1;">
                                            🎓 Grade Details
                                        </p>

                                        <div style="display: flex; flex-direction: column; gap: 10px;">
                                            <!-- Name -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Name:</span>
                                                <span style="font-size: 14px; font-weight: 500; color: #0f172a;">@{{ selectedGrade.name }}</span>
                                            </div>

                                            <!-- Code -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Code:</span>
                                                <span style="display: inline-block; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; font-family: 'Courier New', monospace; color: #475569; border: 1px solid #e2e8f0;">@{{ selectedGrade.code }}</span>
                                            </div>

                                            <!-- Premium Status -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Premium:</span>
                                                
                                                <span 
                                                    v-if="selectedGrade.is_premium"
                                                    style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%); color: #ffffff; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; box-shadow: 0 2px 4px rgba(251, 146, 60, 0.3);"
                                                >
                                                    ⭐ Premium
                                                </span>
                                                
                                                <span 
                                                    v-else
                                                    style="display: inline-block; background: #e2e8f0; color: #475569; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;"
                                                >
                                                    Standard
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Avatar -->
                                    <div 
                                        v-if="selectedGrade.avatar"
                                        style="flex-shrink: 0;"
                                    >
                                        <img
                                            :src="avatarUrl(selectedGrade.avatar)"
                                            :alt="selectedGrade.name"
                                            style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover; border: 3px solid #ffffff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: SUBJECT DROPDOWN -->
                            <div 
                                v-if="selectedGrade && filteredSubjects.length > 0"
                                class="box-shadow rounded bg-white p-4 mb-4"
                            >
                                <p class="font-semibold mb-3">Select Subject <span class="text-red-500">*</span></p>

                                <select
                                    name="subject_id"
                                    v-model="selectedSubjectId"
                                    @change="onSubjectChange"
                                    class="w-full border rounded p-3"
                                    required
                                >
                                    <option value="" disabled>-- Select Subject --</option>

                                    <option
                                        v-for="subject in filteredSubjects"
                                        :key="subject.id"
                                        :value="subject.id"
                                    >
                                        @{{ subject.name }} (@{{ subject.code }})
                                    </option>
                                </select>
                            </div>

                            <!-- SUBJECT DETAILS -->
                            <div
                                v-if="selectedSubject"
                                style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #fcd34d; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);"
                            >
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                                    <!-- Left: Details -->
                                    <div style="flex: 1;">
                                        <p style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #cbd5e1;">
                                            📚 Subject Details
                                        </p>

                                        <div style="display: flex; flex-direction: column; gap: 10px;">
                                            <!-- Name -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Name:</span>
                                                <span style="font-size: 14px; font-weight: 500; color: #0f172a;">@{{ selectedSubject.name }}</span>
                                            </div>

                                            <!-- Code -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Code:</span>
                                                <span style="display: inline-block; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; font-family: 'Courier New', monospace; color: #475569; border: 1px solid #e2e8f0;">@{{ selectedSubject.code }}</span>
                                            </div>

                                            <!-- Premium Status -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Premium:</span>
                                                
                                                <span 
                                                    v-if="selectedSubject.is_premium"
                                                    style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%); color: #ffffff; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; box-shadow: 0 2px 4px rgba(251, 146, 60, 0.3);"
                                                >
                                                    ⭐ Premium
                                                </span>
                                                
                                                <span 
                                                    v-else
                                                    style="display: inline-block; background: #e2e8f0; color: #475569; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;"
                                                >
                                                    Standard
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Avatar -->
                                    <div 
                                        v-if="selectedSubject.avatar"
                                        style="flex-shrink: 0;"
                                    >
                                        <img
                                            :src="avatarUrl(selectedSubject.avatar)"
                                            :alt="selectedSubject.name"
                                            style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover; border: 3px solid #ffffff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 4: BOOK DROPDOWN -->
                            <div 
                                v-if="selectedSubject && filteredBooks.length > 0"
                                class="box-shadow rounded bg-white p-4 mb-4"
                            >
                                <p class="font-semibold mb-3">Select Book <span class="text-red-500">*</span></p>

                                <select
                                    name="book_id"
                                    v-model="selectedBookId"
                                    @change="onBookChange"
                                    class="w-full border rounded p-3"
                                    required
                                >
                                    <option value="" disabled>-- Select Book --</option>

                                    <option
                                        v-for="book in filteredBooks"
                                        :key="book.id"
                                        :value="book.id"
                                    >
                                        @{{ book.title }} (@{{ book.code }})
                                    </option>
                                </select>
                            </div>

                            <!-- BOOK DETAILS -->
                            <div
                                v-if="selectedBook"
                                style="background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%); border: 1px solid #f9a8d4; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);"
                            >
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                                    <!-- Left: Details -->
                                    <div style="flex: 1;">
                                        <p style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #cbd5e1;">
                                            📖 Book Details
                                        </p>

                                        <div style="display: flex; flex-direction: column; gap: 10px;">
                                            <!-- Title -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Title:</span>
                                                <span style="font-size: 14px; font-weight: 500; color: #0f172a;">@{{ selectedBook.title }}</span>
                                            </div>

                                            <!-- Code -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Code:</span>
                                                <span style="display: inline-block; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; font-family: 'Courier New', monospace; color: #475569; border: 1px solid #e2e8f0;">@{{ selectedBook.code }}</span>
                                            </div>

                                            <!-- Writer -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Writer:</span>
                                                <span style="font-size: 14px; font-weight: 500; color: #0f172a;">@{{ selectedBook.writer }}</span>
                                            </div>

                                            <!-- Premium Status -->
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 70px;">Premium:</span>
                                                
                                                <span 
                                                    v-if="selectedBook.is_premium"
                                                    style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%); color: #ffffff; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; box-shadow: 0 2px 4px rgba(251, 146, 60, 0.3);"
                                                >
                                                    ⭐ Premium
                                                </span>
                                                
                                                <span 
                                                    v-else
                                                    style="display: inline-block; background: #e2e8f0; color: #475569; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;"
                                                >
                                                    Standard
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Avatar -->
                                    <div 
                                        v-if="selectedBook.avatar"
                                        style="flex-shrink: 0;"
                                    >
                                        <img
                                            :src="avatarUrl(selectedBook.avatar)"
                                            :alt="selectedBook.title"
                                            style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover; border: 3px solid #ffffff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);"
                                        >
                                    </div>
                                </div>
                            </div>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <!-- ================= SCRIPT ================= -->
        <script type="module">
            app.component('v-create-chapters', {
                template: '#v-create-chapters-template',

                data() {
                    return {
                        // All boards with nested relationships
                        boards: @json($boards),

                        // Board selection
                        selectedBoardId: '',
                        selectedBoard: null,

                        // Grade selection
                        selectedGradeId: '',
                        selectedGrade: null,
                        filteredGrades: [],

                        // Subject selection
                        selectedSubjectId: '',
                        selectedSubject: null,
                        filteredSubjects: [],

                        // Book selection
                        selectedBookId: '',
                        selectedBook: null,
                        filteredBooks: [],

                        // Chapter fields
                        title: '',
                        code: '',
                        codeIsAutoGenerated: true,

                        // Premium control
                        chapterIsPremium: false,
                        isPremiumLocked: false,
                    };
                },

                watch: {
                    // Auto-generate code from title
                    title(newTitle) {
                        if (!this.code || this.codeIsAutoGenerated) {
                            this.code = this.slugify(newTitle);
                            this.codeIsAutoGenerated = true;
                        }
                    }
                },

                methods: {
                    // Board selection handler
                    onBoardChange() {
                        this.selectedBoard = this.boards.find(
                            board => board.id == this.selectedBoardId
                        );

                        if (!this.selectedBoard) {
                            this.resetSelections();
                            return;
                        }

                        // Filter grades by selected board
                        this.filteredGrades = this.selectedBoard.grades || [];
                        
                        // Reset other selections
                        this.selectedGradeId = '';
                        this.selectedGrade = null;
                        this.selectedSubjectId = '';
                        this.selectedSubject = null;
                        this.filteredSubjects = [];
                        this.selectedBookId = '';
                        this.selectedBook = null;
                        this.filteredBooks = [];
                        this.resetPremiumState();
                    },

                    // Grade selection handler
                    onGradeChange() {
                        this.selectedGrade = this.filteredGrades.find(
                            grade => grade.id == this.selectedGradeId
                        );

                        if (!this.selectedGrade) {
                            this.selectedSubjectId = '';
                            this.selectedSubject = null;
                            this.filteredSubjects = [];
                            this.selectedBookId = '';
                            this.selectedBook = null;
                            this.filteredBooks = [];
                            this.resetPremiumState();
                            return;
                        }

                        // Filter subjects by selected grade
                        this.filteredSubjects = this.selectedGrade.subjects || [];
                        
                        // Reset other selections
                        this.selectedSubjectId = '';
                        this.selectedSubject = null;
                        this.selectedBookId = '';
                        this.selectedBook = null;
                        this.filteredBooks = [];
                        this.resetPremiumState();
                    },

                    // Subject selection handler
                    onSubjectChange() {
                        this.selectedSubject = this.filteredSubjects.find(
                            subject => subject.id == this.selectedSubjectId
                        );

                        if (!this.selectedSubject) {
                            this.selectedBookId = '';
                            this.selectedBook = null;
                            this.filteredBooks = [];
                            this.resetPremiumState();
                            return;
                        }

                        // Filter books by selected subject
                        this.filteredBooks = this.selectedSubject.books || [];
                        
                        // Reset book selection
                        this.selectedBookId = '';
                        this.selectedBook = null;
                        this.resetPremiumState();
                    },

                    // Book selection handler
                    onBookChange() {
                        this.selectedBook = this.filteredBooks.find(
                            book => book.id == this.selectedBookId
                        );

                        if (!this.selectedBook) {
                            this.resetPremiumState();
                            return;
                        }

                        // Set premium based on book
                        if (this.selectedBook.is_premium) {
                            this.chapterIsPremium = true;
                            this.isPremiumLocked = true;
                        } else {
                            this.chapterIsPremium = false;
                            this.isPremiumLocked = false;
                        }
                    },

                    // Reset all selections
                    resetSelections() {
                        this.filteredGrades = [];
                        this.selectedGradeId = '';
                        this.selectedGrade = null;
                        this.selectedSubjectId = '';
                        this.selectedSubject = null;
                        this.filteredSubjects = [];
                        this.selectedBookId = '';
                        this.selectedBook = null;
                        this.filteredBooks = [];
                        this.resetPremiumState();
                    },

                    // Reset premium state
                    resetPremiumState() {
                        this.chapterIsPremium = false;
                        this.isPremiumLocked = false;
                    },

                    // Avatar URL generator
                    avatarUrl(avatarPath) {
                        return `{{ url('/storage/') }}/${avatarPath}`;
                    },

                    // Code input handler
                    onCodeInput() {
                        this.codeIsAutoGenerated = false;
                    },

                    // Slugify function
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