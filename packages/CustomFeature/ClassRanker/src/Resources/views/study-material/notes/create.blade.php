<x-admin::layouts>
    <x-slot:title>Create Note</x-slot>

    <x-admin::form :action="route('admin.study_materials.notes.store')">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">Create Note</p>
            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.study_materials.notes.index') }}"
                   class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">Back</a>
                <button type="submit" class="primary-button">Save</button>
            </div>
        </div>

        <v-create-notes :boards="{{ json_encode($boards) }}"></v-create-notes>
    </x-admin::form>

    @pushOnce('scripts')
    <script type="text/x-template" id="v-create-notes-template">
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                <!-- ASSIGNMENTS -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Assign to Chapters</p>
                    <div class="grid grid-cols-5 gap-3 mb-4">
                        <select v-model="selectedBoardId" @change="onBoardChange" class="w-full border rounded p-2 text-sm">
                            <option value="">Board</option>
                            <option v-for="board in boards" :key="board.id" :value="board.id">@{{ board.name }}</option>
                        </select>
                        <select v-model="selectedGradeId" @change="onGradeChange" :disabled="!filteredGrades.length" class="w-full border rounded p-2 text-sm">
                            <option value="">Grade</option>
                            <option v-for="grade in filteredGrades" :key="grade.id" :value="grade.id">@{{ grade.name }}</option>
                        </select>
                        <select v-model="selectedSubjectId" @change="onSubjectChange" :disabled="!filteredSubjects.length" class="w-full border rounded p-2 text-sm">
                            <option value="">Subject</option>
                            <option v-for="subject in filteredSubjects" :key="subject.id" :value="subject.id">@{{ subject.name }}</option>
                        </select>
                        <select v-model="selectedBookId" @change="onBookChange" :disabled="!filteredBooks.length" class="w-full border rounded p-2 text-sm">
                            <option value="">Book</option>
                            <option v-for="book in filteredBooks" :key="book.id" :value="book.id">@{{ book.title }}</option>
                        </select>
                        <select v-model="selectedChapterId" :disabled="!filteredChapters.length" class="w-full border rounded p-2 text-sm">
                            <option value="">Chapter</option>
                            <option v-for="chapter in filteredChapters" :key="chapter.id" :value="chapter.id">@{{ chapter.title }}</option>
                        </select>
                    </div>
                    <button type="button" @click="addAssignment" class="secondary-button">+ Add Selection</button>

                    <div v-if="assignments.length > 0" class="mt-4 space-y-2">
                        <div v-for="(a, index) in assignments" :key="index"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded border">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                @{{ index+1 }}. @{{ a.boardName }} → @{{ a.gradeName }} → @{{ a.subjectName }} → @{{ a.bookName }} → @{{ a.chapterName }}
                            </span>
                            <button type="button" @click="removeAssignment(index)"
                                class="text-red-600 text-sm font-semibold">Remove</button>
                        </div>
                    </div>

                    <template v-for="(a, index) in assignments" :key="'a-'+index">
                        <input type="hidden" :name="'assignments['+index+'][board_id]'"   :value="a.boardId">
                        <input type="hidden" :name="'assignments['+index+'][grade_id]'"   :value="a.gradeId">
                        <input type="hidden" :name="'assignments['+index+'][subject_id]'" :value="a.subjectId">
                        <input type="hidden" :name="'assignments['+index+'][book_id]'"    :value="a.bookId">
                        <input type="hidden" :name="'assignments['+index+'][chapter_id]'" :value="a.chapterId">
                    </template>
                </div>

                <!-- BASIC INFO -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Basic Information</p>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">Title</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="title" rules="required" v-model="title" placeholder="Enter title" />
                        <x-admin::form.control-group.error control-name="title" />
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Slug</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="slug" v-model="slug" readonly />
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">Short Title</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="short_title" rules="required" v-model="shortTitle" placeholder="Short title" />
                        <x-admin::form.control-group.error control-name="short_title" />
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Top Description</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="top_description" class="top_description" name="top_description" :tinymce="true" />
                    </x-admin::form.control-group>
                </div>

                <!-- NOTE CONTENT -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Note Content</p>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Note Content</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="content" class="content" name="content" :tinymce="true" />
                    </x-admin::form.control-group>
                </div>

                <!-- BOTTOM DESCRIPTION -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Bottom Description</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="bottom_description" class="bottom_description" name="bottom_description" :tinymce="true" />
                    </x-admin::form.control-group>
                </div>

                <!-- SEO -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">SEO Meta Tags</p>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Title</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="meta_title" placeholder="SEO meta title" />
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Description</x-admin::form.control-group.label>
                        <textarea name="meta_description" rows="3" class="w-full border rounded p-2 text-sm"></textarea>
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Keywords</x-admin::form.control-group.label>
                        <textarea name="meta_keywords" rows="2" class="w-full border rounded p-2 text-sm"></textarea>
                    </x-admin::form.control-group>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">Settings</p>
                    </x-slot>
                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">Status</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="switch" name="status" value="1" :checked="true" />
                        </x-admin::form.control-group>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Is Premium</x-admin::form.control-group.label>
                            <input type="checkbox" name="is_premium" value="1" class="h-4 w-4 rounded border-gray-300">
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-create-notes', {
            template: '#v-create-notes-template',

            props: {
                boards: { type: Array, default: () => [] },
            },

            data() {
                return {
                    title: '', slug: '', shortTitle: '',
                    selectedBoardId: '', selectedGradeId: '', selectedSubjectId: '',
                    selectedBookId: '', selectedChapterId: '',
                    filteredGrades: [], filteredSubjects: [], filteredBooks: [], filteredChapters: [],
                    assignments: [],
                };
            },

            watch: {
                title(val) { this.slug = this.slugify(val); },
            },

            methods: {
                onBoardChange() {
                    const board = this.boards.find(b => b.id == this.selectedBoardId);
                    this.filteredGrades = board?.grades ?? [];
                    this.selectedGradeId = ''; this.filteredSubjects = [];
                    this.selectedSubjectId = ''; this.filteredBooks = [];
                    this.selectedBookId = ''; this.filteredChapters = [];
                    this.selectedChapterId = '';
                },

                onGradeChange() {
                    const grade = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                    this.filteredSubjects = grade?.subjects ?? [];
                    this.selectedSubjectId = ''; this.filteredBooks = [];
                    this.selectedBookId = ''; this.filteredChapters = [];
                    this.selectedChapterId = '';
                },

                onSubjectChange() {
                    const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                    this.filteredBooks = subject?.books ?? [];
                    this.selectedBookId = ''; this.filteredChapters = [];
                    this.selectedChapterId = '';
                },

                onBookChange() {
                    const book = this.filteredBooks.find(b => b.id == this.selectedBookId);
                    this.filteredChapters = book?.chapters ?? [];
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
                    this.selectedBoardId = ''; this.selectedGradeId = '';
                    this.selectedSubjectId = ''; this.selectedBookId = '';
                    this.selectedChapterId = '';
                    this.filteredGrades = []; this.filteredSubjects = [];
                    this.filteredBooks = []; this.filteredChapters = [];
                },

                removeAssignment(index) { this.assignments.splice(index, 1); },

                slugify(text) {
                    return text.toString().toLowerCase().trim()
                        .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '').replace(/\-\-+/g, '-');
                },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>