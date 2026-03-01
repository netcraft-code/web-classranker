<x-admin::layouts>
    <x-slot:title>
        @lang('class_ranker::app.study_materials.questions.index.title')
    </x-slot>

    <v-questions>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.questions.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <button type="button" class="primary-button">
                    @lang('class_ranker::app.study_materials.questions.index.create-btn')
                </button>
            </div>
        </div>

        <x-admin::shimmer.datagrid />
    </v-questions>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-questions-template">

            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('class_ranker::app.study_materials.questions.index.title')
                </p>

                <div class="flex items-center gap-x-2.5">
                    <button
                        type="button"
                        class="primary-button"
                        @click="resetForm(); $refs.questionModal.toggle()"
                    >
                        @lang('class_ranker::app.study_materials.questions.index.create-btn')
                    </button>
                </div>
            </div>

            <!-- DataGrid -->
            <x-admin::datagrid :src="route('admin.study_materials.questions.index')" :isMultiRow="true">
                <template #header="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
                    </template>

                    <template v-else>
                        <!-- Grid Header Columns -->
                        <div
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 dark:border-gray-800 dark:text-gray-300"
                            style="grid-template-columns: 1fr 1fr 2fr 1fr 1fr"
                        >
                            <div
                                class="flex select-none items-center gap-2.5"
                                v-for="(columnGroup, index) in [['id', 'created_at', 'status', 'is_premium'], ['slug', 'short_title', 'title'], ['board_name', 'grade_name', 'subject_name', 'book_title', 'chapter_title'], ['item_count', 'faq_count']]"
                            >
                                <p class="text-gray-600 dark:text-gray-300 text-sm sm:text-base">
                                    <span class="[&>*]:after:content-['_/_']">
                                        <template v-for="column in columnGroup">
                                            <span
                                                class="after:content-['/'] last:after:content-['']"
                                                :class="{
                                                    'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                                    'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                                }"
                                                @click="
                                                    available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)) : {}
                                                "
                                            >
                                                @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                            </span>
                                        </template>
                                    </span>

                                    <i
                                        class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5"
                                        :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                        v-if="columnGroup.includes(applied.sort.column)"
                                    >
                                    </i>
                                </p>
                            </div>
                        </div>
                    </template>
                </template>

                <template #body="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
                    </template>

                    <template v-else>
                        <!-- Order Rows -->
                        <div
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                            style="grid-template-columns: 1fr 1fr 2fr 1fr 1fr"
                            v-for="record in available.records"
                        >
                            <div class="flex flex-col min-w-0 gap-1.5">
                                <p class="text-sm sm:text-base font-semibold text-gray-800 dark:text-white">
                                    @{{ "@lang('admin::app.sales.orders.index.datagrid.id')".replace(':id', record.id) }}
                                </p>

                                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                    @{{ record.created_at }}
                                </p>
                                
                                <p :class="[record.status ? 'label-active': 'label-info']">
                                    @{{ record.status ? "@lang('admin::app.catalog.products.index.datagrid.active')" : "@lang('admin::app.catalog.products.index.datagrid.disable')" }}
                                </p>

                                <p>
                                    <span :class="record.is_premium ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500'"
                                        class="px-2 py-0.5 rounded-full text-xs font-medium">
                                        @{{ record.is_premium ? 'Premium' : 'Free' }}
                                    </span>
                                </p>
                            </div>

                            <div class="flex flex-col min-w-0 gap-3">
                                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                    @{{ record.slug }}
                                </p>

                                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                    @{{ record.short_title }}
                                </p>

                                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                    @{{ record.title }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-1.5 min-w-0">
                                <p>@{{ record.board_name }} → @{{ record.grade_name }} → @{{ record.subject_name }} →
                                <span class="text-gray-500 text-sm">@{{ record.book_title }} → @{{ record.chapter_title }}</span></p>
                            </div>

                            <div class="flex flex-col min-w-0 gap-1.5">
                                <p class="text-center">
                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium"
                                    >
                                        @{{ record.item_count }} Q
                                    </span>
                                </p>
                                
                                <p class="text-center">
                                    <span class="bg-red-100 text-red-500 px-2 py-0.5 rounded-full text-xs font-medium">
                                        @{{ record.faq_count }} FAQ
                                    </span>
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-1.5  min-w-0">
                                <p
                                    class="flex items-center gap-1.5"
                                    v-if="available.actions.length"
                                >
                                    <span
                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        :class="action.icon"
                                        v-text="! action.icon ? action.title : ''"
                                        v-for="action in record.actions"
                                        @click="performAction(action)"
                                    >
                                    </span>
                                </p>
                            </div>
                        </div>
                    </template>
                </template>
            </x-admin::datagrid>

            <!-- Create Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, createQuestion)" ref="questionForm">
                    <x-admin::modal ref="questionModal" width="max-w-4xl">

                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">Create Question</p>
                        </x-slot>

                        <x-slot:content>

                            <!-- ASSIGNMENTS -->
                            <div class="mb-5">
                                <p class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">Assign to Chapters</p>

                                <div class="grid grid-cols-5 gap-2 mb-3">
                                    <select v-model="selectedBoardId" @change="onBoardChange"
                                        class="w-full border rounded p-2 text-sm">
                                        <option value="">Board</option>
                                        <option v-for="board in boards" :key="board.id" :value="board.id">@{{ board.name }}</option>
                                    </select>
                                    <select v-model="selectedGradeId" @change="onGradeChange"
                                        :disabled="!filteredGrades.length" class="w-full border rounded p-2 text-sm">
                                        <option value="">Grade</option>
                                        <option v-for="grade in filteredGrades" :key="grade.id" :value="grade.id">@{{ grade.name }}</option>
                                    </select>
                                    <select v-model="selectedSubjectId" @change="onSubjectChange"
                                        :disabled="!filteredSubjects.length" class="w-full border rounded p-2 text-sm">
                                        <option value="">Subject</option>
                                        <option v-for="subject in filteredSubjects" :key="subject.id" :value="subject.id">@{{ subject.name }}</option>
                                    </select>
                                    <select v-model="selectedBookId" @change="onBookChange"
                                        :disabled="!filteredBooks.length" class="w-full border rounded p-2 text-sm">
                                        <option value="">Book</option>
                                        <option v-for="book in filteredBooks" :key="book.id" :value="book.id">@{{ book.title }}</option>
                                    </select>
                                    <select v-model="selectedChapterId"
                                        :disabled="!filteredChapters.length" class="w-full border rounded p-2 text-sm">
                                        <option value="">Chapter</option>
                                        <option v-for="chapter in filteredChapters" :key="chapter.id" :value="chapter.id">@{{ chapter.title }}</option>
                                    </select>
                                </div>

                                <button type="button" @click="addAssignment"
                                    class="secondary-button text-sm">+ Add Selection</button>

                                <div v-if="assignments.length > 0" class="mt-3 space-y-2">
                                    <div v-for="(a, index) in assignments" :key="index"
                                        class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded border text-sm">
                                        <span class="text-gray-700 dark:text-gray-300">
                                            @{{ index+1 }}. @{{ a.boardName }} → @{{ a.gradeName }} → @{{ a.subjectName }} → @{{ a.bookName }} → @{{ a.chapterName }}
                                        </span>
                                        <button type="button" @click="removeAssignment(index)"
                                            class="text-red-600 text-xs font-semibold ml-2">Remove</button>
                                    </div>
                                </div>

                                <!-- Hidden inputs -->
                                <template v-for="(a, index) in assignments" :key="'a-'+index">
                                    <input type="hidden" :name="'assignments['+index+'][board_id]'"   :value="a.boardId">
                                    <input type="hidden" :name="'assignments['+index+'][grade_id]'"   :value="a.gradeId">
                                    <input type="hidden" :name="'assignments['+index+'][subject_id]'" :value="a.subjectId">
                                    <input type="hidden" :name="'assignments['+index+'][book_id]'"    :value="a.bookId">
                                    <input type="hidden" :name="'assignments['+index+'][chapter_id]'" :value="a.chapterId">
                                </template>
                            </div>

                            <hr class="mb-5 dark:border-gray-700">

                            <!-- BASIC INFO -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">Title (Web View)</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text" name="title" rules="required"
                                    v-model="form.title" placeholder="Enter title" />
                                <x-admin::form.control-group.error control-name="title" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>Slug</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text" name="slug"
                                    v-model="form.slug" readonly />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">Short Title (Mobile)</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text" name="short_title" rules="required"
                                    v-model="form.shortTitle" placeholder="Enter short title" />
                                <x-admin::form.control-group.error control-name="short_title" />
                            </x-admin::form.control-group>

                        </x-slot>

                        <x-slot:footer>
                            <x-admin::button
                                button-type="submit"
                                class="primary-button"
                                title="Save & Continue to Edit"
                                ::loading="isLoading"
                                ::disabled="isLoading"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>

        </script>

        <script type="module">
            app.component('v-questions', {
                template: '#v-questions-template',

                data() {
                    return {
                        isLoading: false,
                        boards: @json($boards),

                        // Cascade
                        selectedBoardId:   '', selectedGradeId:   '',
                        selectedSubjectId: '', selectedBookId:    '',
                        selectedChapterId: '',
                        filteredGrades:    [], filteredSubjects:  [],
                        filteredBooks:     [], filteredChapters:  [],
                        assignments: [],

                        form: {
                            title:     '',
                            slug:      '',
                            shortTitle: '',
                        },
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
                    'form.title'(val) {
                        this.form.slug = this.slugify(val);
                    },
                },

                methods: {
                    resetForm() {
                        this.form = { title: '', slug: '', shortTitle: ''};
                        this.assignments    = [];
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

                    addAssignment() {
                        if (!this.selectedBoardId || !this.selectedGradeId || !this.selectedSubjectId ||
                            !this.selectedBookId || !this.selectedChapterId) {
                            alert('Please select all fields'); return;
                        }
                        const isDuplicate = this.assignments.some(a =>
                            a.boardId == this.selectedBoardId && a.gradeId == this.selectedGradeId &&
                            a.subjectId == this.selectedSubjectId && a.bookId == this.selectedBookId &&
                            a.chapterId == this.selectedChapterId
                        );
                        if (isDuplicate) { alert('Already added!'); return; }

                        const board   = this.boards.find(b => b.id == this.selectedBoardId);
                        const grade   = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                        const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                        const book    = this.filteredBooks.find(b => b.id == this.selectedBookId);
                        const chapter = this.filteredChapters.find(c => c.id == this.selectedChapterId);

                        this.assignments.push({
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

                    removeAssignment(index) { this.assignments.splice(index, 1); },

                    createQuestion(params, { setErrors }) {
                        if (!this.assignments.length) {
                            alert('Please add at least one assignment'); return;
                        }

                        this.isLoading = true;
                        const formData = new FormData(this.$refs.questionForm);

                        this.$axios.post("{{ route('admin.study_materials.questions.store') }}", formData)
                            .then(response => {
                                this.isLoading = false;
                                this.$refs.questionModal.close();
                                // Edit page pe redirect
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

                    navigateToEdit(url) {
                        if (url) window.location.href = url;
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