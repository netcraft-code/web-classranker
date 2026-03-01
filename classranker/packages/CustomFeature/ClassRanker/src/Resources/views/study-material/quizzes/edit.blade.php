<x-admin::layouts>
    <x-slot:title>Edit Quiz - {{ $quiz->title }}</x-slot>

    <x-admin::form
        :action="route('admin.study_materials.quizzes.update', $quiz->id)"
        method="PUT"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">Edit Quiz: {{ $quiz->title }}</p>
            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.study_materials.quizzes.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">Back</a>
                <button type="submit" class="primary-button">Update Quiz</button>
            </div>
        </div>

        <v-edit-quiz
            :initial-chapters="{{ json_encode($formattedChapters) }}"
            :initial-questions="{{ json_encode($formattedQuestions) }}"
            :boards="{{ json_encode($boards) }}"
            :quiz-id="{{ $quiz->id }}"
            :routes="{{ json_encode([
                'chapters_add'    => route('admin.study_materials.quizzes.chapters.add',    $quiz->id),
                'chapters_remove' => route('admin.study_materials.quizzes.chapters.remove', [$quiz->id, ':chapterId']),
                'questions_add'   => route('admin.study_materials.quizzes.questions.add',   $quiz->id),
                'questions_update'=> route('admin.study_materials.quizzes.questions.update',[$quiz->id, ':questionId']),
                'questions_remove'=> route('admin.study_materials.quizzes.questions.remove',[$quiz->id, ':questionId']),
            ]) }}"
        ></v-edit-quiz>
    </x-admin::form>

    @pushOnce('scripts')
    <script type="text/x-template" id="v-edit-quiz-template">
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                <!-- ── BASIC INFO ── -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Basic Information</p>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">Quiz Title</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="title" rules="required"
                            v-model="quiz.title" :value="old('title', $quiz->title)" />
                        <x-admin::form.control-group.error control-name="title" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Slug</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="slug"
                            v-model="quiz.slug" :value="old('slug', $quiz->slug)" readonly />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Description</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="description"
                            class="description" name="description"
                            :value="old('description', $quiz->description)" :tinymce="true" />
                    </x-admin::form.control-group>
                </div>

                <!-- ── QUESTIONS ── -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Questions</p>

                    <!-- Existing questions -->
                    <div v-for="(question, qIndex) in questions" :key="question.id"
                        class="mb-6 p-4 border-2 rounded bg-gray-50 dark:bg-gray-800">

                        <div class="flex items-center justify-between mb-3">
                            <p class="font-semibold text-gray-700 dark:text-white">
                                Question @{{ qIndex + 1 }}
                                <span v-if="question.editing" class="ml-2 text-xs text-yellow-600 font-normal">(Editing)</span>
                            </p>
                            <div class="flex items-center gap-2">
                                <button v-if="!question.editing" type="button"
                                    @click="startEditQuestion(question)"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-semibold">Edit</button>
                                <button v-if="question.editing" type="button"
                                    @click="cancelEditQuestion(question)"
                                    class="text-gray-500 hover:text-gray-700 text-sm font-semibold">Cancel</button>
                                <button v-if="question.editing" type="button"
                                    @click="ajaxUpdateQuestion(question)"
                                    :disabled="question.saving"
                                    class="text-green-600 hover:text-green-800 text-sm font-semibold">
                                    @{{ question.saving ? 'Saving...' : 'Save' }}
                                </button>
                                <button type="button"
                                    @click="ajaxRemoveQuestion(question.id, qIndex)"
                                    :disabled="question.deleting"
                                    class="text-red-600 hover:text-red-800 text-sm font-semibold">
                                    @{{ question.deleting ? 'Removing...' : 'Remove' }}
                                </button>
                            </div>
                        </div>

                        <!-- Read-only view -->
                        <template v-if="!question.editing">
                            <div class="text-sm text-gray-700 dark:text-gray-300 mb-3 prose max-w-none"
                                v-html="question.text"></div>

                            <div class="space-y-1 mb-3">
                                <div v-for="(opt, oIdx) in question.options" :key="oIdx"
                                    class="flex items-start gap-2 text-sm p-2 rounded"
                                    :class="opt.is_correct ? 'bg-green-50 border border-green-200' : 'bg-white border'">
                                    <span :class="opt.is_correct ? 'text-green-600 font-bold' : 'text-gray-400'"
                                        class="flex-shrink-0">@{{ oIdx + 1 }}.</span>
                                    <span v-html="opt.text" class="flex-1 prose max-w-none text-sm"></span>
                                    <span v-if="opt.is_correct" class="text-xs text-green-600 font-semibold flex-shrink-0">✓ Correct</span>
                                </div>
                            </div>

                            <div v-if="question.solution" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded">
                                <p class="text-xs font-semibold text-blue-700 mb-1">Solution:</p>
                                <div class="text-sm text-blue-800 prose max-w-none" v-html="question.solution"></div>
                            </div>
                        </template>

                        <!-- Edit form -->
                        <template v-if="question.editing">
                            <!-- Question Text -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Question Text <span class="text-red-500">*</span>
                                </label>
                                <textarea :id="'question_text_' + question.id"
                                    class="w-full border rounded p-2 text-sm">@{{ question.text }}</textarea>
                            </div>

                            <!-- Options -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Options</label>

                                <div v-for="(opt, oIdx) in question.options" :key="oIdx"
                                    class="mb-3 p-3 border rounded bg-white dark:bg-gray-900">

                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            Option @{{ oIdx + 1 }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <!-- TinyMCE Toggle -->
                                            <label class="flex items-center gap-1 text-xs">
                                                <input type="checkbox" v-model="opt.useTinymce"
                                                    @change="onOptionTinymceToggle(question, oIdx)"
                                                    class="h-3 w-3">
                                                <span class="text-gray-600">Rich Editor</span>
                                            </label>

                                            <!-- Is Correct -->
                                            <label class="flex items-center gap-1">
                                                <input type="checkbox" :value="oIdx"
                                                    v-model="question.correctOptions" class="h-4 w-4">
                                                <span class="text-xs text-green-600 font-semibold">Correct</span>
                                            </label>

                                            <!-- Remove option -->
                                            <button v-if="question.options.length > 2" type="button"
                                                @click="removeEditOption(question, oIdx)"
                                                class="text-red-500 text-xs font-semibold">Remove</button>
                                        </div>
                                    </div>

                                    <!-- Plain text -->
                                    <div v-if="!opt.useTinymce">
                                        <textarea v-model="opt.text" rows="2"
                                            class="w-full border rounded p-2 text-sm" placeholder="Option text"></textarea>
                                    </div>

                                    <!-- TinyMCE -->
                                    <div v-else>
                                        <textarea :id="'opt_' + question.id + '_' + oIdx"
                                            class="w-full border rounded p-2 text-sm">@{{ opt.text }}</textarea>
                                    </div>
                                </div>

                                <button type="button" @click="question.options.push({ text: '', useTinymce: false, is_correct: false })"
                                    class="text-sm text-blue-600 hover:text-blue-800">+ Add Option</button>
                            </div>

                            <!-- Solution (TinyMCE) -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Solution / Explanation
                                </label>
                                <textarea :id="'solution_' + question.id"
                                    class="w-full border rounded p-2 text-sm">@{{ question.solution }}</textarea>
                            </div>
                        </template>
                    </div>

                    <!-- New question form -->
                    <div v-if="showNewQuestion"
                        class="mb-6 p-4 border-2 border-dashed border-blue-300 rounded bg-blue-50 dark:bg-gray-800">
                        <p class="font-semibold text-blue-700 dark:text-white mb-3">New Question</p>

                        <!-- Question Text -->
                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Question Text <span class="text-red-500">*</span>
                            </label>
                            <textarea id="new_question_text"
                                class="w-full border rounded p-2 text-sm"></textarea>
                        </div>

                        <!-- Options -->
                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Options</label>

                            <div v-for="(opt, oIdx) in newQuestion.options" :key="'new-opt-'+oIdx"
                                class="mb-3 p-3 border rounded bg-white dark:bg-gray-900">

                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        Option @{{ oIdx + 1 }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-1 text-xs">
                                            <input type="checkbox" v-model="opt.useTinymce"
                                                @change="onNewOptionTinymceToggle(oIdx)"
                                                class="h-3 w-3">
                                            <span class="text-gray-600">Rich Editor</span>
                                        </label>
                                        <label class="flex items-center gap-1">
                                            <input type="checkbox" :value="oIdx"
                                                v-model="newQuestion.correctOptions" class="h-4 w-4">
                                            <span class="text-xs text-green-600 font-semibold">Correct</span>
                                        </label>
                                        <button v-if="newQuestion.options.length > 2" type="button"
                                            @click="newQuestion.options.splice(oIdx, 1)"
                                            class="text-red-500 text-xs font-semibold">Remove</button>
                                    </div>
                                </div>

                                <div v-if="!opt.useTinymce">
                                    <textarea v-model="opt.text" rows="2"
                                        class="w-full border rounded p-2 text-sm" placeholder="Option text"></textarea>
                                </div>
                                <div v-else>
                                    <textarea :id="'new_opt_' + oIdx"
                                        class="w-full border rounded p-2 text-sm"></textarea>
                                </div>
                            </div>

                            <button type="button"
                                @click="newQuestion.options.push({ text: '', useTinymce: false })"
                                class="text-sm text-blue-600 hover:text-blue-800">+ Add Option</button>
                        </div>

                        <!-- Solution -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Solution / Explanation
                            </label>
                            <textarea id="new_solution_text"
                                class="w-full border rounded p-2 text-sm"></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" @click="ajaxAddQuestion" :disabled="questionSaving" class="primary-button text-sm">
                                @{{ questionSaving ? 'Saving...' : 'Save Question' }}
                            </button>
                            <button type="button" @click="cancelNewQuestion" class="transparent-button text-sm">Cancel</button>
                        </div>
                    </div>

                    <button v-if="!showNewQuestion" type="button" @click="openNewQuestion" class="secondary-button">
                        + Add Question
                    </button>
                </div>

            </div>

            <!-- ── RIGHT SIDEBAR ── -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">

                <!-- Settings -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">Settings</p>
                    </x-slot>
                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">Status</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="switch" name="status" value="1"
                                ::checked="quiz.status" />
                        </x-admin::form.control-group>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Is Premium</x-admin::form.control-group.label>
                            <input type="checkbox" name="is_premium" value="1"
                                v-model="quiz.is_premium" class="h-4 w-4 rounded border-gray-300">
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                <!-- Chapters -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Chapters</p>

                    <div class="space-y-2 mb-3">
                        <select v-model="selectedBoardId" @change="onBoardChange" class="w-full border rounded p-2 text-sm">
                            <option value="">Select Board</option>
                            <option v-for="board in boards" :key="board.id" :value="board.id">@{{ board.name }}</option>
                        </select>
                        <select v-model="selectedGradeId" @change="onGradeChange" :disabled="!filteredGrades.length" class="w-full border rounded p-2 text-sm">
                            <option value="">Select Grade</option>
                            <option v-for="grade in filteredGrades" :key="grade.id" :value="grade.id">@{{ grade.name }}</option>
                        </select>
                        <select v-model="selectedSubjectId" @change="onSubjectChange" :disabled="!filteredSubjects.length" class="w-full border rounded p-2 text-sm">
                            <option value="">Select Subject</option>
                            <option v-for="subject in filteredSubjects" :key="subject.id" :value="subject.id">@{{ subject.name }}</option>
                        </select>
                        <select v-model="selectedBookId" @change="onBookChange" :disabled="!filteredBooks.length" class="w-full border rounded p-2 text-sm">
                            <option value="">Select Book</option>
                            <option v-for="book in filteredBooks" :key="book.id" :value="book.id">@{{ book.title }}</option>
                        </select>
                        <select v-model="selectedChapterId" :disabled="!filteredChapters.length" class="w-full border rounded p-2 text-sm">
                            <option value="">Select Chapter</option>
                            <option v-for="chapter in filteredChapters" :key="chapter.id" :value="chapter.id">@{{ chapter.title }}</option>
                        </select>
                    </div>

                    <button type="button" @click="ajaxAddChapter" :disabled="chapterLoading"
                        class="secondary-button w-full">
                        <span v-if="chapterLoading">Adding...</span>
                        <span v-else>+ Add Chapter</span>
                    </button>

                    <div v-if="selectedChapters.length > 0" class="mt-3 space-y-2 max-h-64 overflow-y-auto">
                        <div v-for="(c, index) in selectedChapters" :key="c.id"
                            class="flex items-start justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded border text-xs">
                            <span class="text-gray-700 dark:text-gray-300 flex-1">
                                @{{ c.boardName }} → @{{ c.gradeName }} → @{{ c.subjectName }} → @{{ c.bookName }} → @{{ c.chapterName }}
                            </span>
                            <button v-if="selectedChapters.length > 1" type="button"
                                @click="ajaxRemoveChapter(c.id, index)"
                                class="text-red-600 hover:text-red-800 ml-2 flex-shrink-0">×</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-edit-quiz', {
            template: '#v-edit-quiz-template',

            props: {
                initialChapters:   { type: Array,  default: () => [] },
                initialQuestions:  { type: Array,  default: () => [] },
                boards:            { type: Array,  default: () => [] },
                quizId:            { type: Number, required: true },
                routes:            { type: Object, required: true },
            },

            data() {
                return {
                    quiz: {
                        title:      '{{ old('title', $quiz->title) }}',
                        slug:       '{{ old('slug', $quiz->slug) }}',
                        status:     {{ old('status', $quiz->status) ? 'true' : 'false' }},
                        is_premium: {{ old('is_premium', $quiz->is_premium) ? 'true' : 'false' }},
                    },

                    selectedBoardId:   '', selectedGradeId:   '',
                    selectedSubjectId: '', selectedBookId:    '',
                    selectedChapterId: '',
                    filteredGrades:    [], filteredSubjects:  [],
                    filteredBooks:     [], filteredChapters:  [],

                    chapterLoading:   false,
                    selectedChapters: this.initialChapters,

                    questions: this.initialQuestions.map(q => ({
                        ...q,
                        editing:        false,
                        saving:         false,
                        deleting:       false,
                        correctOptions: q.correctOptions || [],
                    })),

                    showNewQuestion: false,
                    newQuestion:     this.defaultNewQuestion(),
                    questionSaving:  false,
                };
            },

            watch: {
                'quiz.title'(val) { this.quiz.slug = this.slugify(val); },
            },

            methods: {
                defaultNewQuestion() {
                    return {
                        text:     '',
                        solution: '',
                        options:  [
                            { text: '', useTinymce: false },
                            { text: '', useTinymce: false },
                        ],
                        correctOptions: [],
                    };
                },

                // ── Cascade ──────────────────────────────────────────
                onBoardChange() {
                    const board = this.boards.find(b => b.id == this.selectedBoardId);
                    this.filteredGrades    = board?.grades ?? [];
                    this.selectedGradeId   = ''; this.filteredSubjects  = [];
                    this.selectedSubjectId = ''; this.filteredBooks     = [];
                    this.selectedBookId    = ''; this.filteredChapters  = [];
                    this.selectedChapterId = '';
                },
                onGradeChange() {
                    const grade = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                    this.filteredSubjects  = grade?.subjects ?? [];
                    this.selectedSubjectId = ''; this.filteredBooks    = [];
                    this.selectedBookId    = ''; this.filteredChapters = [];
                    this.selectedChapterId = '';
                },
                onSubjectChange() {
                    const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                    this.filteredBooks     = subject?.books ?? [];
                    this.selectedBookId    = ''; this.filteredChapters = [];
                    this.selectedChapterId = '';
                },
                onBookChange() {
                    const book = this.filteredBooks.find(b => b.id == this.selectedBookId);
                    this.filteredChapters  = book?.chapters ?? [];
                    this.selectedChapterId = '';
                },

                // ── Chapters AJAX ────────────────────────────────────
                ajaxAddChapter() {
                    if (!this.selectedBoardId || !this.selectedGradeId || !this.selectedSubjectId ||
                        !this.selectedBookId || !this.selectedChapterId) {
                        alert('Please select all fields'); return;
                    }
                    const isDuplicate = this.selectedChapters.some(c => c.chapterId == this.selectedChapterId);
                    if (isDuplicate) { alert('Already added!'); return; }

                    this.chapterLoading = true;
                    this.$axios.post(this.routes.chapters_add, {
                        board_id:   this.selectedBoardId,
                        grade_id:   this.selectedGradeId,
                        subject_id: this.selectedSubjectId,
                        book_id:    this.selectedBookId,
                        chapter_id: this.selectedChapterId,
                    })
                    .then(res => {
                        this.selectedChapters.push(res.data.chapter);
                        this.selectedBoardId   = ''; this.selectedGradeId   = '';
                        this.selectedSubjectId = ''; this.selectedBookId    = '';
                        this.selectedChapterId = '';
                        this.filteredGrades    = []; this.filteredSubjects  = [];
                        this.filteredBooks     = []; this.filteredChapters  = [];
                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    })
                    .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to add chapter' }))
                    .finally(() => this.chapterLoading = false);
                },

                ajaxRemoveChapter(chapterId, index) {
                    if (!confirm('Remove this chapter?')) return;
                    this.$axios.delete(this.routes.chapters_remove.replace(':chapterId', chapterId))
                        .then(res => {
                            this.selectedChapters.splice(index, 1);
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to remove chapter' }));
                },

                // ── Questions AJAX ───────────────────────────────────
                startEditQuestion(question) {
                    question._snapshot = JSON.parse(JSON.stringify({
                        text:           question.text,
                        solution:       question.solution,
                        options:        question.options,
                        correctOptions: question.correctOptions,
                    }));
                    question.editing = true;
                    this.$nextTick(() => {
                        this.initQuestionEditors(question);
                    });
                },

                cancelEditQuestion(question) {
                    if (question._snapshot) {
                        question.text           = question._snapshot.text;
                        question.solution       = question._snapshot.solution;
                        question.options        = question._snapshot.options;
                        question.correctOptions = question._snapshot.correctOptions;
                    }
                    question.editing = false;
                    this.destroyQuestionEditors(question);
                },

                collectQuestionData(question) {
                    // TinyMCE se content fetch karo
                    const qtEditor = tinymce.get('question_text_' + question.id);
                    if (qtEditor) question.text = qtEditor.getContent();

                    const solEditor = tinymce.get('solution_' + question.id);
                    if (solEditor) question.solution = solEditor.getContent();

                    question.options.forEach((opt, oIdx) => {
                        if (opt.useTinymce) {
                            const optEditor = tinymce.get('opt_' + question.id + '_' + oIdx);
                            if (optEditor) opt.text = optEditor.getContent();
                        }
                    });
                },

                collectNewQuestionData() {
                    const qtEditor = tinymce.get('new_question_text');
                    if (qtEditor) this.newQuestion.text = qtEditor.getContent();

                    const solEditor = tinymce.get('new_solution_text');
                    if (solEditor) this.newQuestion.solution = solEditor.getContent();

                    this.newQuestion.options.forEach((opt, oIdx) => {
                        if (opt.useTinymce) {
                            const optEditor = tinymce.get('new_opt_' + oIdx);
                            if (optEditor) opt.text = optEditor.getContent();
                        }
                    });
                },

                ajaxAddQuestion() {
                    this.collectNewQuestionData();

                    if (!this.newQuestion.text) { alert('Question text required'); return; }
                    if (!this.newQuestion.options.some(o => o.text)) { alert('Options required'); return; }

                    this.questionSaving = true;
                    this.$axios.post(this.routes.questions_add, {
                        text:            this.newQuestion.text,
                        solution:        this.newQuestion.solution,
                        options:         this.newQuestion.options.map((o, i) => ({
                            text:        o.text,
                            use_tinymce: o.useTinymce ? 1 : 0,
                        })),
                        correct_options: this.newQuestion.correctOptions,
                    })
                    .then(res => {
                        this.questions.push({
                            ...res.data.question,
                            editing: false, saving: false, deleting: false,
                        });
                        this.cancelNewQuestion();
                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    })
                    .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to add question' }))
                    .finally(() => this.questionSaving = false);
                },

                ajaxUpdateQuestion(question) {
                    this.collectQuestionData(question);

                    if (!question.text) { alert('Question text required'); return; }

                    question.saving = true;
                    this.$axios.put(this.routes.questions_update.replace(':questionId', question.id), {
                        text:            question.text,
                        solution:        question.solution,
                        options:         question.options.map((o, i) => ({
                            text:        o.text,
                            use_tinymce: o.useTinymce ? 1 : 0,
                        })),
                        correct_options: question.correctOptions,
                    })
                    .then(res => {
                        // Update local data from server response
                        const updated = res.data.question;
                        question.text           = updated.text;
                        question.solution       = updated.solution;
                        question.options        = updated.options;
                        question.correctOptions = updated.correctOptions;
                        question.editing        = false;
                        this.destroyQuestionEditors(question);
                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    })
                    .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to update question' }))
                    .finally(() => question.saving = false);
                },

                ajaxRemoveQuestion(questionId, index) {
                    if (!confirm('Remove this question?')) return;
                    this.questions[index].deleting = true;
                    this.$axios.delete(this.routes.questions_remove.replace(':questionId', questionId))
                        .then(res => {
                            this.questions.splice(index, 1);
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => {
                            this.questions[index].deleting = false;
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to remove question' });
                        });
                },

                removeEditOption(question, oIdx) {
                    if (question.options.length <= 2) return;
                    // TinyMCE destroy karo
                    const optEditor = tinymce.get('opt_' + question.id + '_' + oIdx);
                    if (optEditor) optEditor.remove();

                    question.options.splice(oIdx, 1);
                    // correctOptions adjust karo
                    const ci = question.correctOptions.indexOf(oIdx);
                    if (ci > -1) question.correctOptions.splice(ci, 1);
                    question.correctOptions = question.correctOptions.map(i => i > oIdx ? i - 1 : i);
                },

                // ── New question form ────────────────────────────────
                openNewQuestion() {
                    this.showNewQuestion = true;
                    this.$nextTick(() => {
                        this.initTinyMCE('new_question_text', '');
                        this.initTinyMCE('new_solution_text', '');
                    });
                },

                cancelNewQuestion() {
                    this.destroyTinyMCE('new_question_text');
                    this.destroyTinyMCE('new_solution_text');
                    this.newQuestion.options.forEach((opt, oIdx) => {
                        if (opt.useTinymce) this.destroyTinyMCE('new_opt_' + oIdx);
                    });
                    this.showNewQuestion  = false;
                    this.newQuestion      = this.defaultNewQuestion();
                },

                // ── TinyMCE helpers ──────────────────────────────────
                getTinyMCEConfig() {
                    return {
                        menubar: true,
                        relative_urls: false,
                        remove_script_host: false,
                        document_base_url: '/',
                        plugins: `advlist autolink lists link image charmap preview anchor
                            searchreplace visualblocks code fullscreen
                            insertdatetime media table help wordcount save directionality`,
                        toolbar: `undo redo | formatselect |
                            bold italic underline strikethrough |
                            forecolor backcolor |
                            alignleft aligncenter alignright alignjustify |
                            bullist numlist outdent indent |
                            link image media table |
                            fullscreen preview code removeformat`,
                        image_advtab: true,
                        file_picker_types: 'image',
                        file_picker_callback: (cb) => {
                            const input = document.createElement('input');
                            input.type = 'file'; input.accept = 'image/*';
                            input.onchange = () => {
                                const file = input.files[0];
                                const reader = new FileReader();
                                reader.onload = () => cb(reader.result, { title: file.name });
                                reader.readAsDataURL(file);
                            };
                            input.click();
                        },
                        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', '/admin/tinymce/upload');
                            xhr.upload.onprogress = e => progress((e.loaded / e.total) * 100);
                            xhr.onload = () => {
                                if (xhr.status < 200 || xhr.status >= 300) { reject('Upload failed'); return; }
                                const json = JSON.parse(xhr.responseText);
                                if (!json.location) { reject('Invalid response'); return; }
                                resolve(json.location);
                            };
                            const formData = new FormData();
                            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                            formData.append('file', blobInfo.blob(), blobInfo.filename());
                            xhr.send(formData);
                        }),
                        directionality: 'ltr',
                    };
                },

                initTinyMCE(id, content = '') {
                    if (typeof tinymce === 'undefined') return;
                    const existing = tinymce.get(id);
                    if (existing) existing.remove();
                    tinymce.init({
                        selector: `#${id}`,
                        height: 250,
                        ...this.getTinyMCEConfig(),
                        setup(editor) {
                            editor.on('init', () => editor.setContent(content || ''));
                        },
                    });
                },

                destroyTinyMCE(id) {
                    const editor = tinymce.get(id);
                    if (editor) editor.remove();
                },

                initQuestionEditors(question) {
                    this.initTinyMCE('question_text_' + question.id, question.text || '');
                    this.initTinyMCE('solution_' + question.id, question.solution || '');
                    question.options.forEach((opt, oIdx) => {
                        if (opt.useTinymce) {
                            this.initTinyMCE('opt_' + question.id + '_' + oIdx, opt.text || '');
                        }
                    });
                },

                destroyQuestionEditors(question) {
                    this.destroyTinyMCE('question_text_' + question.id);
                    this.destroyTinyMCE('solution_' + question.id);
                    question.options.forEach((opt, oIdx) => {
                        this.destroyTinyMCE('opt_' + question.id + '_' + oIdx);
                    });
                },

                onOptionTinymceToggle(question, oIdx) {
                    const opt = question.options[oIdx];
                    if (opt.useTinymce) {
                        this.$nextTick(() => {
                            this.initTinyMCE('opt_' + question.id + '_' + oIdx, opt.text || '');
                        });
                    } else {
                        // Content save karke destroy
                        const editor = tinymce.get('opt_' + question.id + '_' + oIdx);
                        if (editor) { opt.text = editor.getContent(); editor.remove(); }
                    }
                },

                onNewOptionTinymceToggle(oIdx) {
                    const opt = this.newQuestion.options[oIdx];
                    if (opt.useTinymce) {
                        this.$nextTick(() => {
                            this.initTinyMCE('new_opt_' + oIdx, opt.text || '');
                        });
                    } else {
                        const editor = tinymce.get('new_opt_' + oIdx);
                        if (editor) { opt.text = editor.getContent(); editor.remove(); }
                    }
                },

                slugify(text) {
                    return text.toString().toLowerCase().trim()
                        .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '').replace(/\-\-+/g, '-');
                },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>