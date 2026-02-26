<x-admin::layouts>
    <x-slot:title>
        Bulk Upload Quizzes
    </x-slot>

    @push('meta')
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endPush

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap mb-4">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Bulk Upload Quizzes
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.study_materials.quizzes.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                Back
            </a>
        </div>
    </div>

    <v-bulk-upload></v-bulk-upload>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-bulk-upload-template">
            <div class="mt-3.5">
                <!-- Upload Section -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900 mb-4" v-if="!parsedData">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        Upload Quiz File
                    </p>

                    <!-- File Upload -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Select File (PDF or CSV)
                        </label>
                        <input
                            type="file"
                            @change="handleFileUpload"
                            accept=".pdf,.csv"
                            class="w-full border rounded p-2"
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            Supported formats: PDF, CSV
                        </p>
                    </div>

                    <!-- Chapter Selection -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            Assign to Chapters
                        </p>

                        <div class="space-y-2">
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

                        <button type="button" @click="addChapter" class="secondary-button mt-3 w-full">+ Add Chapter</button>

                        <div v-if="selectedChapters.length > 0" class="mt-4">
                            <p class="font-semibold mb-2 text-xs">Selected: @{{ selectedChapters.length }}</p>
                            <div class="space-y-2">
                                <div v-for="(chapter, index) in selectedChapters" :key="index" class="flex items-center justify-between p-2 bg-gray-50 rounded border text-xs">
                                    <span>@{{ chapter.chapterName }}</span>
                                    <button type="button" @click="removeChapter(index)" class="text-red-600">×</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Button -->
                    <button
                        type="button"
                        @click="uploadFile"
                        :disabled="!selectedFile || !selectedChapters.length || uploading"
                        class="primary-button mt-4"
                    >
                        <span v-if="uploading">⏳ Parsing...</span>
                        <span v-else>📤 Upload & Parse</span>
                    </button>
                </div>

                <!-- Preview Section -->
                <div v-if="parsedData" class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            Preview Parsed Questions (@{{ parsedData.questions.length }} found)
                        </p>
                        <button type="button" @click="resetUpload" class="secondary-button">
                            ← Upload New File
                        </button>
                    </div>

                    <!-- Parsed Questions -->
                    <div v-for="(question, qIndex) in parsedData.questions" :key="qIndex" class="mb-4 p-4 border rounded">
                        <p class="font-semibold mb-2">Question @{{ qIndex + 1 }}</p>
                        <p class="text-sm mb-2" v-html="question.text"></p>

                        <div class="ml-4">
                            <div v-for="(option, oIndex) in question.options" :key="oIndex" class="text-sm mb-1">
                                <span :class="{'text-green-600 font-semibold': question.correctOptions.includes(oIndex)}">
                                    (@{{ String.fromCharCode(97 + oIndex) }}) @{{ option.text }}
                                    <span v-if="question.correctOptions.includes(oIndex)">✓</span>
                                </span>
                            </div>
                        </div>

                        <p v-if="question.explanation" class="text-xs text-gray-600 mt-2">
                            <strong>Explanation:</strong> @{{ question.explanation }}
                        </p>
                    </div>

                    <!-- Save Button -->
                    <button
                        type="button"
                        @click="saveQuizzes"
                        :disabled="saving"
                        class="primary-button mt-4"
                    >
                        <span v-if="saving">⏳ Saving...</span>
                        <span v-else>💾 Save @{{ parsedData.questions.length }} Questions as Quiz</span>
                    </button>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-bulk-upload', {
                template: '#v-bulk-upload-template',

                data() {
                    return {
                        boards: @json($boards),
                        selectedFile: null,
                        uploading: false,
                        saving: false,
                        parsedData: null,

                        selectedBoardId: '',
                        selectedGradeId: '',
                        selectedSubjectId: '',
                        selectedBookId: '',
                        selectedChapterId: '',

                        filteredGrades: [],
                        filteredSubjects: [],
                        filteredBooks: [],
                        filteredChapters: [],
                        selectedChapters: [],
                    };
                },

                methods: {
                    handleFileUpload(event) {
                        this.selectedFile = event.target.files[0];
                    },

                    async uploadFile() {
                        if (!this.selectedFile || !this.selectedChapters.length) {
                            alert('Please select a file and chapters');
                            return;
                        }

                        this.uploading = true;

                        const formData = new FormData();
                        formData.append('file', this.selectedFile);
                        formData.append('chapters', JSON.stringify(this.selectedChapters));

                        try {
                            const response = await fetch('{{ route("admin.study_materials.quizzes.parse-file") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.parsedData = data.data;
                            } else {
                                alert('Error parsing file: ' + data.message);
                            }
                        } catch (error) {
                            alert('Upload failed: ' + error.message);
                        } finally {
                            this.uploading = false;
                        }
                    },

                    async saveQuizzes() {
                        this.saving = true;

                        try {
                            const response = await fetch('{{ route("admin.study_materials.quizzes.bulk-store") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    chapters: this.selectedChapters,
                                    questions: this.parsedData.questions
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                window.location.href = '{{ route("admin.study_materials.quizzes.index") }}';
                            } else {
                                alert('Error saving: ' + data.message);
                            }
                        } catch (error) {
                            alert('Save failed: ' + error.message);
                        } finally {
                            this.saving = false;
                        }
                    },

                    resetUpload() {
                        this.parsedData = null;
                        this.selectedFile = null;
                    },

                    // Chapter selection methods (same as create page)
                    onBoardChange() {
                        const board = this.boards.find(b => b.id == this.selectedBoardId);
                        this.filteredGrades = board ? board.grades : [];
                        this.resetCascade('grade');
                    },

                    onGradeChange() {
                        const grade = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                        this.filteredSubjects = grade ? grade.subjects : [];
                        this.resetCascade('subject');
                    },

                    onSubjectChange() {
                        const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                        this.filteredBooks = subject ? subject.books : [];
                        this.resetCascade('book');
                    },

                    onBookChange() {
                        const book = this.filteredBooks.find(b => b.id == this.selectedBookId);
                        this.filteredChapters = book?.chapters ?? [];
                        this.selectedChapterId = '';
                    },

                    resetCascade(level) {
                        if (level === 'grade') {
                            this.selectedGradeId = '';
                            this.filteredSubjects = [];
                        }
                        if (level === 'grade' || level === 'subject') {
                            this.selectedSubjectId = '';
                            this.filteredBooks = [];
                        }
                        if (level === 'grade' || level === 'subject' || level === 'book') {
                            this.selectedBookId = '';
                            this.filteredChapters = [];
                        }
                        this.selectedChapterId = '';
                    },

                    addChapter() {
                        if (!this.selectedChapterId) {
                            alert('Please select chapter');
                            return;
                        }

                        const board = this.boards.find(b => b.id == this.selectedBoardId);
                        const grade = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                        const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                        const book = this.filteredBooks.find(b => b.id == this.selectedBookId);
                        const chapter = this.filteredChapters.find(c => c.id == this.selectedChapterId);

                        this.selectedChapters.push({
                            boardId: this.selectedBoardId,
                            gradeId: this.selectedGradeId,
                            subjectId: this.selectedSubjectId,
                            bookId: this.selectedBookId,
                            chapterId: this.selectedChapterId,
                            chapterName: chapter.title,
                        });

                        this.selectedChapterId = '';
                    },

                    removeChapter(index) {
                        this.selectedChapters.splice(index, 1);
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>