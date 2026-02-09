<x-admin::layouts>
    <!--Page title -->
    <x-slot:title>
        @lang('class_ranker::app.study_materials.questions.create.title')
    </x-slot>

    <!--Create Page Form -->
    <x-admin::form
        :action="route('admin.study_materials.questions.store')"
        enctype="multipart/form-data"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.questions.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.study_materials.questions.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <!--Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('class_ranker::app.study_materials.questions.create.save-btn')
                </button>
            </div>
        </div>

        <!-- VUE COMPONENT -->
        <v-create-questions></v-create-questions>
    </x-admin::form>

    @pushOnce('scripts')
        <!-- ================= TEMPLATE ================= -->
        <script type="text/x-template" id="v-create-questions-template">
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Column -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <!-- ========== SECTION 1: ASSIGNMENTS ========== -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            Assign to Chapters
                        </p>

                        <!-- Cascading Dropdowns -->
                        <div class="grid grid-cols-5 gap-3 mb-4">
                            <!-- Board -->
                            <select
                                v-model="selectedBoardId"
                                @change="onBoardChange"
                                class="w-full border rounded p-2 text-sm"
                            >
                                <option value="">Board</option>
                                <option v-for="board in boards" :key="board.id" :value="board.id">
                                    @{{ board.name }}
                                </option>
                            </select>

                            <!-- Grade -->
                            <select
                                v-model="selectedGradeId"
                                @change="onGradeChange"
                                :disabled="!filteredGrades.length"
                                class="w-full border rounded p-2 text-sm"
                            >
                                <option value="">Grade</option>
                                <option v-for="grade in filteredGrades" :key="grade.id" :value="grade.id">
                                    @{{ grade.name }}
                                </option>
                            </select>

                            <!-- Subject -->
                            <select
                                v-model="selectedSubjectId"
                                @change="onSubjectChange"
                                :disabled="!filteredSubjects.length"
                                class="w-full border rounded p-2 text-sm"
                            >
                                <option value="">Subject</option>
                                <option v-for="subject in filteredSubjects" :key="subject.id" :value="subject.id">
                                    @{{ subject.name }}
                                </option>
                            </select>

                            <!-- Book -->
                            <select
                                v-model="selectedBookId"
                                @change="onBookChange"
                                :disabled="!filteredBooks.length"
                                class="w-full border rounded p-2 text-sm"
                            >
                                <option value="">Book</option>
                                <option v-for="book in filteredBooks" :key="book.id" :value="book.id">
                                    @{{ book.title }}
                                </option>
                            </select>

                            <!-- Chapter -->
                            <select
                                v-model="selectedChapterId"
                                :disabled="!filteredChapters.length"
                                class="w-full border rounded p-2 text-sm"
                            >
                                <option value="">Chapter</option>
                                <option v-for="chapter in filteredChapters" :key="chapter.id" :value="chapter.id">
                                    @{{ chapter.title }}
                                </option>
                            </select>
                        </div>

                        <!-- Add Selection Button -->
                        <button
                            type="button"
                            @click="addAssignment"
                            class="secondary-button"
                        >
                            + Add Selection
                        </button>

                        <!-- Selected Assignments List -->
                        <div v-if="assignments.length > 0" class="mt-4">
                            <p class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Selected Combinations:</p>
                            <div class="space-y-2">
                                <div
                                    v-for="(assignment, index) in assignments"
                                    :key="index"
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded border"
                                >
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        @{{ index + 1 }}. @{{ assignment.boardName }} → @{{ assignment.gradeName }} → @{{ assignment.subjectName }} → @{{ assignment.bookName }} → @{{ assignment.chapterName }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="removeAssignment(index)"
                                        class="text-red-600 hover:text-red-800 text-sm font-semibold"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden inputs for assignments -->
                        <input
                            v-for="(assignment, index) in assignments"
                            :key="'assignment-' + index"
                            type="hidden"
                            :name="'assignments[' + index + '][board_id]'"
                            :value="assignment.boardId"
                        >
                        <input
                            v-for="(assignment, index) in assignments"
                            :key="'assignment-grade-' + index"
                            type="hidden"
                            :name="'assignments[' + index + '][grade_id]'"
                            :value="assignment.gradeId"
                        >
                        <input
                            v-for="(assignment, index) in assignments"
                            :key="'assignment-subject-' + index"
                            type="hidden"
                            :name="'assignments[' + index + '][subject_id]'"
                            :value="assignment.subjectId"
                        >
                        <input
                            v-for="(assignment, index) in assignments"
                            :key="'assignment-book-' + index"
                            type="hidden"
                            :name="'assignments[' + index + '][book_id]'"
                            :value="assignment.bookId"
                        >
                        <input
                            v-for="(assignment, index) in assignments"
                            :key="'assignment-chapter-' + index"
                            type="hidden"
                            :name="'assignments[' + index + '][chapter_id]'"
                            :value="assignment.chapterId"
                        >
                    </div>

                    <!-- ========== SECTION 2: BASIC INFO ========== -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            Basic Information
                        </p>

                        <!-- Title (Web) -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                Title (Web View)
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="title"
                                rules="required"
                                v-model="title"
                                :value="old('title')"
                                placeholder="Enter title for web view"
                            />

                            <x-admin::form.control-group.error control-name="title" />
                        </x-admin::form.control-group>

                        <!-- Slug (Auto-generated, read-only) -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Slug (Auto-generated)
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="slug"
                                v-model="slug"
                                :value="old('slug')"
                                readonly
                                placeholder="Auto-generated from title"
                            />
                        </x-admin::form.control-group>

                        <!-- Short Title (Mobile) -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                Short Title (Mobile View)
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="short_title"
                                rules="required"
                                v-model="shortTitle"
                                :value="old('short_title')"
                                placeholder="Enter short title for mobile view"
                            />

                            <x-admin::form.control-group.error control-name="short_title" />
                        </x-admin::form.control-group>

                        <!-- Top Description -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Top Description')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="top_description"
                                class="top_description"
                                name="top_description"
                                :value="old('top_description')"
                                label="Top Description"
                                :tinymce="true"
                            />

                            <x-admin::form.control-group.error control-name="top_description" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- ========== SECTION 3: QUESTION ITEMS ========== -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            Questions
                        </p>

                        <!-- Question Items -->
                        <div
                            v-for="(item, index) in questionItems"
                            :key="'question-' + index"
                            class="mb-4 p-4 border rounded bg-gray-50 dark:bg-gray-800"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-semibold text-gray-700 dark:text-white">Question @{{ index + 1 }}</p>
                                <button
                                    type="button"
                                    @click="removeQuestionItem(index)"
                                    class="text-red-600 hover:text-red-800 text-sm font-semibold"
                                >
                                    Remove
                                </button>
                            </div>

                            <!-- Question Number -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Question Number <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="item.questionNumber"
                                    :name="'question_items[' + index + '][question_number]'"
                                    class="w-full border rounded p-2"
                                    placeholder="1"
                                    required
                                >
                            </div>

                            <!-- Question -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Question
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    class="question_text"
                                    v-bind:name="'question_items[' + index + '][question]'"
                                    rules="required"
                                    label="Question Text"
                                    :tinymce="false"
                                />

                                <x-admin::form.control-group.error control-name="question_text" />
                            </x-admin::form.control-group>

                            <!-- Answer -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Answer
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    class="answer_text"
                                    v-bind:name="'question_items[' + index + '][answer]'"
                                    rules="required"
                                    label="Answer"
                                    :tinymce="false"
                                />

                                <x-admin::form.control-group.error control-name="question_items" />
                            </x-admin::form.control-group>

                            <!-- Page Number -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Page Number
                                </label>
                                <input
                                    type="text"
                                    v-model="item.pageNumber"
                                    :name="'question_items[' + index + '][page_number]'"
                                    class="w-full border rounded p-2"
                                    placeholder="e.g., 45"
                                >
                            </div>

                            <!-- Order (hidden) -->
                            <input
                                type="hidden"
                                :name="'question_items[' + index + '][order]'"
                                :value="index"
                            >
                        </div>

                        <!-- Add Question Button -->
                        <button
                            type="button"
                            @click="addQuestionItem"
                            class="secondary-button"
                        >
                            + Add Question
                        </button>
                    </div>

                    <!-- ========== SECTION 4: BOTTOM DESCRIPTION ========== -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                Bottom Description
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="bottom_description"
                                class="bottom_description tinymce-editor"
                                name="bottom_description"
                                :value="old('bottom_description')"
                                label="Bottom Description"
                                :tinymce="true"
                            />

                            <x-admin::form.control-group.error control-name="bottom_description" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- ========== SECTION 5: FAQ SCHEMA ========== -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            FAQ Schema
                        </p>

                        <!-- FAQ Items -->
                        <div
                            v-for="(faq, index) in faqs"
                            :key="'faq-' + index"
                            class="mb-4 p-4 border rounded bg-gray-50 dark:bg-gray-800"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-semibold text-gray-700 dark:text-white">FAQ @{{ index + 1 }}</p>
                                <button
                                    type="button"
                                    @click="removeFaq(index)"
                                    class="text-red-600 hover:text-red-800 text-sm font-semibold"
                                >
                                    Remove
                                </button>
                            </div>

                            <!-- FAQ Question -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Question <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="faq.question"
                                    :name="'faqs[' + index + '][question]'"
                                    class="w-full border rounded p-2"
                                    placeholder="FAQ question"
                                    required
                                >
                            </div>

                            <!-- FAQ Answer -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Answer
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    class="faq_answer"
                                    v-bind:name="'faqs[' + index + '][answer]'"
                                    rules="required"
                                    label="FAQ Answer"
                                    :tinymce="false"
                                />

                                <x-admin::form.control-group.error control-name="faqs" />
                            </x-admin::form.control-group>

                            <!-- Order (hidden) -->
                            <input
                                type="hidden"
                                :name="'faqs[' + index + '][order]'"
                                :value="index"
                            >
                        </div>

                        <!-- Add FAQ Button -->
                        <button
                            type="button"
                            @click="addFaq"
                            class="secondary-button"
                        >
                            + Add FAQ
                        </button>
                    </div>

                    <!-- ========== SECTION 6: RELATED LINKS & SEO ========== -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Related Links
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="related_links"
                                class="related_links tinymce-editor"
                                name="related_links"
                                :value="old('related_links')"
                                label="Related Links"
                                :tinymce="true"
                            />

                            <x-admin::form.control-group.error control-name="related_links" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- SEO Section -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            SEO Meta Tags
                        </p>

                        <!-- Meta Title -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Meta Title
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="meta_title"
                                :value="old('meta_title')"
                                placeholder="SEO meta title"
                            />
                        </x-admin::form.control-group>

                        <!-- Meta Description -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Meta Description
                            </x-admin::form.control-group.label>

                            <textarea
                                name="meta_description"
                                rows="3"
                                class="w-full border rounded p-2"
                                placeholder="SEO meta description"
                            >{{ old('meta_description') }}</textarea>
                        </x-admin::form.control-group>

                        <!-- Meta Keywords -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Meta Keywords
                            </x-admin::form.control-group.label>

                            <textarea
                                name="meta_keywords"
                                rows="2"
                                class="w-full border rounded p-2"
                                placeholder="keyword1, keyword2, keyword3"
                            >{{ old('meta_keywords') }}</textarea>
                        </x-admin::form.control-group>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                Settings
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- Status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Status
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    value="1"
                                    :checked="(boolean) old('status', true)"
                                />
                            </x-admin::form.control-group>

                            <!-- Is Premium -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Is Premium
                                </x-admin::form.control-group.label>

                                <input
                                    type="checkbox"
                                    name="is_premium"
                                    value="1"
                                    class="h-4 w-4 rounded border-gray-300"
                                    {{ old('is_premium') ? 'checked' : '' }}
                                >
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <!-- ================= SCRIPT ================= -->
        <script type="module">
            app.component('v-create-questions', {
                template: '#v-create-questions-template',

                data() {
                    return {
                        boards: @json($boards),

                        // Cascading selections
                        selectedBoardId: '',
                        selectedGradeId: '',
                        selectedSubjectId: '',
                        selectedBookId: '',
                        selectedChapterId: '',

                        filteredGrades: [],
                        filteredSubjects: [],
                        filteredBooks: [],
                        filteredChapters: [],

                        // Assignments
                        assignments: [],

                        // Basic fields
                        title: '',
                        slug: '',
                        shortTitle: '',

                        // Question items
                        questionItems: [],

                        // FAQs
                        faqs: [],
                    };
                },

                watch: {
                    title(newTitle) {
                        this.slug = this.slugify(newTitle);
                    },

                    questionItems: {
                        deep: true
                    },

                    faqs: {
                        deep: true
                    }
                },

                mounted() {
                    // Initialize TinyMCE on page load
                    this.$nextTick(() => {
                        this.initializeTinyMCE();
                    });
                },

                methods: {
                    // Board change
                    onBoardChange() {
                        const board = this.boards.find(b => b.id == this.selectedBoardId);
                        this.filteredGrades = board ? board.grades : [];
                        
                        // Reset
                        this.selectedGradeId = '';
                        this.filteredSubjects = [];
                        this.selectedSubjectId = '';
                        this.filteredBooks = [];
                        this.selectedBookId = '';
                        this.filteredChapters = [];
                        this.selectedChapterId = '';
                    },

                    // Grade change
                    onGradeChange() {
                        const grade = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                        this.filteredSubjects = grade ? grade.subjects : [];
                        
                        // Reset
                        this.selectedSubjectId = '';
                        this.filteredBooks = [];
                        this.selectedBookId = '';
                        this.filteredChapters = [];
                        this.selectedChapterId = '';
                    },

                    // Subject change
                    onSubjectChange() {
                        const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                        this.filteredBooks = subject ? subject.books : [];
                        
                        // Reset
                        this.selectedBookId = '';
                        this.filteredChapters = [];
                        this.selectedChapterId = '';
                    },

                    // Book change
                    onBookChange() {
                        const book = this.filteredBooks.find(b => b.id == this.selectedBookId);
                        console.log(book);
                        // this.filteredChapters = book ? book?.chapters : [];
                        this.filteredChapters = book?.chapters ?? [];
                        
                        // Reset
                        this.selectedChapterId = '';
                    },

                    // Add assignment
                    addAssignment() {
                        if (!this.selectedBoardId || !this.selectedGradeId || 
                            !this.selectedSubjectId || !this.selectedBookId || 
                            !this.selectedChapterId) {
                            alert('Please select all fields (Board → Grade → Subject → Book → Chapter)');
                            return;
                        }

                        // Check for duplicate
                        const isDuplicate = this.assignments.some(a => 
                            a.boardId == this.selectedBoardId &&
                            a.gradeId == this.selectedGradeId &&
                            a.subjectId == this.selectedSubjectId &&
                            a.bookId == this.selectedBookId &&
                            a.chapterId == this.selectedChapterId
                        );

                        if (isDuplicate) {
                            alert('This combination is already added!');
                            return;
                        }

                        // Get names
                        const board = this.boards.find(b => b.id == this.selectedBoardId);
                        const grade = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                        const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                        const book = this.filteredBooks.find(b => b.id == this.selectedBookId);
                        const chapter = this.filteredChapters.find(c => c.id == this.selectedChapterId);

                        // Add assignment
                        this.assignments.push({
                            boardId: this.selectedBoardId,
                            gradeId: this.selectedGradeId,
                            subjectId: this.selectedSubjectId,
                            bookId: this.selectedBookId,
                            chapterId: this.selectedChapterId,
                            boardName: board.name,
                            gradeName: grade.name,
                            subjectName: subject.name,
                            bookName: book.title,
                            chapterName: chapter.title,
                        });

                        // Reset selections
                        this.selectedBoardId = '';
                        this.selectedGradeId = '';
                        this.selectedSubjectId = '';
                        this.selectedBookId = '';
                        this.selectedChapterId = '';
                        this.filteredGrades = [];
                        this.filteredSubjects = [];
                        this.filteredBooks = [];
                        this.filteredChapters = [];
                    },

                    // Remove assignment
                    removeAssignment(index) {
                        this.assignments.splice(index, 1);
                    },

                    // Add question item
                    addQuestionItem() {
                        const newIndex = this.questionItems.length;
                        this.questionItems.push({
                            questionNumber: newIndex + 1,
                            questionTitle: '',
                            question: '',
                            answer: '',
                            pageNumber: '',
                        });
                        
                        this.$nextTick(() => {
                            // Re-initialize all TinyMCE editors
                            this.initializeTinyMCE();
                        });
                    },
                    
                    // Remove question item
                    removeQuestionItem(index) {
                        this.questionItems.splice(index, 1);

                        this.$nextTick(() => {
                            // Re-initialize all TinyMCE editors
                            this.initializeTinyMCE();
                        });
                    },

                    // Add FAQ
                    addFaq() {
                        const newIndex = this.faqs.length;
                        this.faqs.push({
                            question: '',
                            answer: '',
                        });
                        
                        this.$nextTick(() => {
                            // Re-initialize all TinyMCE editors
                            this.initializeTinyMCE();
                        });
                    },

                    // Remove FAQ
                    removeFaq(index) {
                        this.faqs.splice(index, 1);

                        this.$nextTick(() => {
                            // Re-initialize all TinyMCE editors
                            this.initializeTinyMCE();
                        });
                    },

                    // Slugify
                    slugify(text) {
                        return text
                            .toString()
                            .toLowerCase()
                            .trim()
                            .replace(/\s+/g, '-')
                            .replace(/[^\w\-]+/g, '')
                            .replace(/\-\-+/g, '-');
                    },

                    // Initialize TinyMCE for all textareas
                    initializeTinyMCE() {
                        // Remove all existing TinyMCE instances first
                        if (typeof tinymce !== 'undefined') {
                            tinymce.remove();
                        }

                        // Reinitialize for all textareas with class 'tinymce-editor'
                        this.$nextTick(() => {
                            if (typeof tinymce !== 'undefined') {
                                // Initialize questions
                                tinymce.init({
                                    selector: 'textarea.question_text',
                                    height: 300,
                                    menubar: false,
                                    plugins: 'lists link image code',
                                    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
                                });

                                // Initialize answers
                                tinymce.init({
                                    selector: 'textarea.answer_text',
                                    height: 300,
                                    menubar: false,
                                    plugins: 'lists link image code',
                                    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
                                });

                                // Initialize FAQ answers
                                tinymce.init({
                                    selector: 'textarea.faq_answer',
                                    height: 200,
                                    menubar: false,
                                    plugins: 'lists link image code',
                                    toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link image | code'
                                });

                                // Initialize FAQ answers
                                tinymce.init({
                                    selector: 'textarea.top_description',
                                    height: 200,
                                    menubar: false,
                                    plugins: 'lists link image code',
                                    toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link image | code'
                                });

                                // Initialize FAQ answers
                                tinymce.init({
                                    selector: 'textarea.bottom_description',
                                    height: 200,
                                    menubar: false,
                                    plugins: 'lists link image code',
                                    toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link image | code'
                                });

                                // Initialize FAQ answers
                                tinymce.init({
                                    selector: 'textarea.related_links',
                                    height: 200,
                                    menubar: false,
                                    plugins: 'lists link image code',
                                    toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link image | code'
                                });
                            }
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>