<x-admin::layouts>
    <x-slot:title>
        @lang('class_ranker::app.study_materials.quizzes.index.title')
    </x-slot>

    <v-quizzes>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.quizzes.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <button type="button" class="primary-button">
                    @lang('class_ranker::app.study_materials.quizzes.index.create-btn')
                </button>
            </div>
        </div>
        <x-admin::shimmer.datagrid />
    </v-quizzes>

    @pushOnce('scripts')
    <script type="text/x-template" id="v-quizzes-template">

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.quizzes.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <button type="button" class="primary-button" @click="resetForm(); $refs.quizModal.toggle()">
                    @lang('class_ranker::app.study_materials.quizzes.index.create-btn')
                </button>
            </div>
        </div>

        <x-admin::datagrid :src="route('admin.study_materials.quizzes.index')" />

        <!-- Create Modal -->
        <x-admin::form v-slot="{ meta, errors, handleSubmit }" as="div" ref="modalForm">
            <form @submit="handleSubmit($event, createQuiz)" ref="quizForm">
                <x-admin::modal ref="quizModal" width="max-w-4xl">
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">Create Quiz</p>
                    </x-slot>

                    <x-slot:content>
                        <!-- CHAPTERS -->
                        <div class="mb-5">
                            <p class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">Select Chapters</p>
                            <div class="grid grid-cols-5 gap-2 mb-3">
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
                            <button type="button" @click="addChapter" class="secondary-button text-sm">+ Add Chapter</button>

                            <div v-if="selectedChapters.length > 0" class="mt-3 space-y-2 max-h-40 overflow-y-auto">
                                <div v-for="(c, index) in selectedChapters" :key="index"
                                    class="flex items-start justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded border text-xs">
                                    <span class="text-gray-700 dark:text-gray-300 flex-1">
                                        @{{ c.boardName }} → @{{ c.gradeName }} → @{{ c.subjectName }} → @{{ c.bookName }} → @{{ c.chapterName }}
                                    </span>
                                    <button type="button" @click="selectedChapters.splice(index, 1)" class="text-red-600 ml-2">×</button>
                                </div>
                            </div>

                            <template v-for="(c, index) in selectedChapters" :key="'c-'+index">
                                <input type="hidden" :name="'chapters['+index+'][board_id]'"   :value="c.boardId">
                                <input type="hidden" :name="'chapters['+index+'][grade_id]'"   :value="c.gradeId">
                                <input type="hidden" :name="'chapters['+index+'][subject_id]'" :value="c.subjectId">
                                <input type="hidden" :name="'chapters['+index+'][book_id]'"    :value="c.bookId">
                                <input type="hidden" :name="'chapters['+index+'][chapter_id]'" :value="c.chapterId">
                            </template>
                        </div>

                        <hr class="mb-5 dark:border-gray-700">

                        <!-- BASIC INFO -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">Quiz Title</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="text" name="title" rules="required"
                                v-model="form.title" placeholder="Enter quiz title" />
                            <x-admin::form.control-group.error control-name="title" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Slug</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="text" name="slug" v-model="form.slug" readonly />
                        </x-admin::form.control-group>
                    </x-slot>

                    <x-slot:footer>
                        <x-admin::button button-type="submit" class="primary-button"
                            title="Save & Continue to Edit"
                            ::loading="isLoading" ::disabled="isLoading" />
                    </x-slot>
                </x-admin::modal>
            </form>
        </x-admin::form>
    </script>

    <script type="module">
        app.component('v-quizzes', {
            template: '#v-quizzes-template',

            data() {
                return {
                    isLoading: false,
                    boards: @json($boards),

                    selectedBoardId:   '', selectedGradeId:   '',
                    selectedSubjectId: '', selectedBookId:    '',
                    selectedChapterId: '',
                    filteredGrades:    [], filteredSubjects:  [],
                    filteredBooks:     [], filteredChapters:  [],
                    selectedChapters:  [],

                    form: { title: '', slug: '' },
                };
            },

            computed: {
                gridsCount() {
                    let count = this.$refs.datagrid.available.columns.length;
                    if (this.$refs.datagrid.available.actions.length)     ++count;
                    if (this.$refs.datagrid.available.massActions.length) ++count;
                    return count;
                },
            },

            watch: {
                'form.title'(val) { this.form.slug = this.slugify(val); },
            },

            methods: {
                resetForm() {
                    this.form = { title: '', slug: '' };
                    this.selectedChapters  = [];
                    this.selectedBoardId   = ''; this.selectedGradeId   = '';
                    this.selectedSubjectId = ''; this.selectedBookId    = '';
                    this.selectedChapterId = '';
                    this.filteredGrades    = []; this.filteredSubjects  = [];
                    this.filteredBooks     = []; this.filteredChapters  = [];
                },

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

                addChapter() {
                    if (!this.selectedBoardId || !this.selectedGradeId || !this.selectedSubjectId ||
                        !this.selectedBookId || !this.selectedChapterId) {
                        alert('Please select all fields'); return;
                    }
                    const isDuplicate = this.selectedChapters.some(c => c.chapterId == this.selectedChapterId);
                    if (isDuplicate) { alert('Already added!'); return; }

                    const board   = this.boards.find(b => b.id == this.selectedBoardId);
                    const grade   = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                    const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                    const book    = this.filteredBooks.find(b => b.id == this.selectedBookId);
                    const chapter = this.filteredChapters.find(c => c.id == this.selectedChapterId);

                    this.selectedChapters.push({
                        boardId: this.selectedBoardId, gradeId: this.selectedGradeId,
                        subjectId: this.selectedSubjectId, bookId: this.selectedBookId,
                        chapterId: this.selectedChapterId,
                        boardName: board.name, gradeName: grade.name, subjectName: subject.name,
                        bookName: book.title, chapterName: chapter.title,
                    });
                    this.selectedBoardId   = ''; this.selectedGradeId   = '';
                    this.selectedSubjectId = ''; this.selectedBookId    = '';
                    this.selectedChapterId = '';
                    this.filteredGrades    = []; this.filteredSubjects  = [];
                    this.filteredBooks     = []; this.filteredChapters  = [];
                },

                createQuiz(params, { setErrors }) {
                    if (!this.selectedChapters.length) {
                        alert('Please add at least one chapter'); return;
                    }
                    this.isLoading = true;
                    const formData = new FormData(this.$refs.quizForm);

                    this.$axios.post("{{ route('admin.study_materials.quizzes.store') }}", formData)
                        .then(response => {
                            this.isLoading = false;
                            this.$refs.quizModal.close();
                            window.location.href = response.data.redirect_url;
                        })
                        .catch(error => {
                            this.isLoading = false;
                            if (error.response?.status === 422) {
                                setErrors(error.response.data.errors);
                            } else {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || 'Something went wrong'
                                });
                            }
                        });
                },

                navigateToEdit(url) { if (url) window.location.href = url; },

                slugify(text) {
                    return text.toString().toLowerCase().trim()
                        .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '').replace(/\-\-+/g, '-');
                },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>