<x-admin::layouts>
    <x-slot:title>
        Create Quiz
    </x-slot>

    <x-admin::form
        :action="route('admin.study_materials.quizzes.store')"
        enctype="multipart/form-data"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Create New Quiz
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.study_materials.quizzes.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    Back
                </a>

                <button type="submit" class="primary-button">
                    Save Quiz
                </button>
            </div>
        </div>

        <v-create-quiz></v-create-quiz>
    </x-admin::form>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-create-quiz-template">
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Column -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <!-- SECTION 1: Basic Info -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            Basic Information
                        </p>

                        <!-- Title -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                Quiz Title
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="title"
                                rules="required"
                                v-model="quiz.title"
                                placeholder="Enter quiz title"
                            />
                        </x-admin::form.control-group>

                        <!-- Slug (Auto-generated) -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Slug (Auto-generated)
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="slug"
                                v-model="quiz.slug"
                                readonly
                                placeholder="Auto-generated from title"
                            />
                        </x-admin::form.control-group>

                        <!-- Description -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Description
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="description"
                                class="description"
                                name="description"
                                v-model="quiz.description"
                                label="Top Description"
                                :tinymce="true"
                            />
                        </x-admin::form.control-group>
                    </div>

                    <!-- SECTION 2: Questions -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            Questions
                        </p>

                        <!-- Question Items -->
                        <div
                            v-for="(question, qIndex) in questions"
                            :key="'question-' + qIndex"
                            class="mb-6 p-4 border-2 rounded bg-gray-50 dark:bg-gray-800"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-semibold text-gray-700 dark:text-white">
                                    Question @{{ qIndex + 1 }}
                                </p>
                                <button
                                    type="button"
                                    @click="removeQuestion(qIndex)"
                                    class="text-red-600 hover:text-red-800 text-sm font-semibold"
                                >
                                    Remove Question
                                </button>
                            </div>

                            <!-- Question Text (TinyMCE) -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Question Text
                                </x-admin::form.control-group.label>

                                <textarea
                                    class="question_text"
                                    v-bind:name="'questions[' + qIndex + '][text]'"
                                    rules="required"
                                    label="Question Text"
                                    :tinymce="false"
                                ></textarea>

                                <x-admin::form.control-group.error control-name="question_text" />
                            </x-admin::form.control-group>

                            <!-- Options -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Options
                                </label>

                                <div
                                    v-for="(option, oIndex) in question.options"
                                    :key="'option-' + oIndex"
                                    class="mb-3 p-3 border rounded bg-white dark:bg-gray-900"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            Option @{{ oIndex + 1 }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <!-- TinyMCE Toggle -->
                                            <label class="flex items-center gap-1 text-xs">
                                                <input
                                                    type="checkbox"
                                                    v-model="option.useTinymce"
                                                    @change="toggleTinymce(qIndex, oIndex)"
                                                    class="h-3 w-3"
                                                >
                                                <span class="text-gray-600">Use Rich Editor</span>
                                            </label>

                                            <!-- 🔥 FIXED: Is Correct Checkbox -->
                                            <label class="flex items-center gap-1">
                                                <input
                                                    type="checkbox"
                                                    :value="oIndex"
                                                    v-model="question.correctOptions"
                                                    class="h-4 w-4"
                                                >
                                                <span class="text-xs text-green-600 font-semibold">Correct</span>
                                            </label>

                                            <!-- Remove Option -->
                                            <button
                                                type="button"
                                                @click="removeOption(qIndex, oIndex)"
                                                class="text-red-500 hover:text-red-700 text-sm font-semibold"
                                                v-if="question.options.length > 2"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Option Text (Conditional TinyMCE) -->
                                    <div v-if="!option.useTinymce">
                                        <textarea
                                            v-model="option.text"
                                            :name="'questions[' + qIndex + '][options][' + oIndex + '][text]'"
                                            class="w-full border rounded p-2 text-sm"
                                            rows="2"
                                            placeholder="Option text"
                                            required
                                        ></textarea>
                                    </div>

                                    <div v-else>
                                        <x-admin::form.control-group>
                                            <textarea
                                                v-bind:class="'option_text_' + qIndex + '_' + oIndex"
                                                v-bind:name="'questions[' + qIndex + '][options][' + oIndex + '][text]'"
                                                label="Option Text"
                                                :tinymce="false"
                                            ></textarea>
                                        </x-admin::form.control-group>
                                    </div>

                                    <!-- Hidden input for useTinymce flag -->
                                    <input
                                        type="hidden"
                                        :name="'questions[' + qIndex + '][options][' + oIndex + '][use_tinymce]'"
                                        :value="option.useTinymce ? 1 : 0"
                                    >
                                </div>

                                <!-- Add Option Button -->
                                <button
                                    type="button"
                                    @click="addOption(qIndex)"
                                    class="text-sm text-blue-600 hover:text-blue-800 mt-1"
                                >
                                    + Add Option
                                </button>
                            </div>

                            <!-- 🔥 FIXED: Hidden inputs for correct options (NO duplicates) -->
                            <template v-for="(correctIdx, arrayIndex) in question.correctOptions" :key="'correct-hidden-' + qIndex + '-' + arrayIndex">
                                <input
                                    type="hidden"
                                    :name="'questions[' + qIndex + '][correct_options][]'"
                                    :value="correctIdx"
                                >
                            </template>

                            <!-- Question Order -->
                            <input
                                type="hidden"
                                :name="'questions[' + qIndex + '][order]'"
                                :value="qIndex"
                            >

                            <!-- Hidden inputs for correct options array -->
                            <input
                                v-for="(correctIndex, idx) in question.correctOptions"
                                :key="'correct-' + qIndex + '-' + idx"
                                type="hidden"
                                :name="'questions[' + qIndex + '][correct_options][]'"
                                :value="correctIndex"
                            >

                            <!-- Question Order -->
                            <input
                                type="hidden"
                                :name="'questions[' + qIndex + '][order]'"
                                :value="qIndex"
                            >
                        </div>

                        <!-- Add Question Button -->
                        <button
                            type="button"
                            @click="addQuestion"
                            class="secondary-button"
                        >
                            + Add Question
                        </button>
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
                                    :checked="true"
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
                                >
                            </x-admin::form.control-group>

                            <!-- SECTION: Chapter Selection -->
                            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900 mt-4">
                                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                                    Select Chapters
                                </p>

                                <!-- Cascading Dropdowns -->
                                <div class="space-y-2">
                                    <!-- Board -->
                                    <select
                                        v-model="selectedBoardId"
                                        @change="onBoardChange"
                                        class="w-full border rounded p-2 text-sm"
                                    >
                                        <option value="">Select Board</option>
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
                                        <option value="">Select Grade</option>
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
                                        <option value="">Select Subject</option>
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
                                        <option value="">Select Book</option>
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
                                        <option value="">Select Chapter</option>
                                        <option v-for="chapter in filteredChapters" :key="chapter.id" :value="chapter.id">
                                            @{{ chapter.title }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Add Chapter Button -->
                                <button
                                    type="button"
                                    @click="addChapter"
                                    class="secondary-button mt-3 w-full"
                                >
                                    + Add Chapter
                                </button>

                                <!-- Selected Chapters List -->
                                <div v-if="selectedChapters.length > 0" class="mt-4">
                                    <p class="font-semibold mb-2 text-xs text-gray-700 dark:text-gray-300">
                                        Selected: @{{ selectedChapters.length }}
                                    </p>
                                    <div class="space-y-2 max-h-64 overflow-y-auto">
                                        <div
                                            v-for="(chapter, index) in selectedChapters"
                                            :key="index"
                                            class="flex items-start justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded border text-xs"
                                        >
                                            <span class="text-gray-700 dark:text-gray-300 flex-1">
                                                @{{ chapter.boardName }} → @{{ chapter.gradeName }} → @{{ chapter.subjectName }} → @{{ chapter.bookName }} → @{{ chapter.chapterName }}
                                            </span>
                                            <button
                                                type="button"
                                                @click="removeChapter(index)"
                                                class="text-red-600 hover:text-red-800 ml-2"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Hidden inputs for chapters -->
                                    <template v-for="(chapter, index) in selectedChapters" :key="'chapter-inputs-' + index">
                                        <input type="hidden" :name="'chapters[' + index + '][board_id]'" :value="chapter.boardId">
                                        <input type="hidden" :name="'chapters[' + index + '][grade_id]'" :value="chapter.gradeId">
                                        <input type="hidden" :name="'chapters[' + index + '][subject_id]'" :value="chapter.subjectId">
                                        <input type="hidden" :name="'chapters[' + index + '][book_id]'" :value="chapter.bookId">
                                        <input type="hidden" :name="'chapters[' + index + '][chapter_id]'" :value="chapter.chapterId">
                                    </template>
                                </div>
                            </div>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-create-quiz', {
                template: '#v-create-quiz-template',

                data() {
                    return {
                        boards: @json($boards),

                        quiz: {
                            title: '',
                            slug: '',
                            description: '',
                        },

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

                        // Selected chapters
                        selectedChapters: [],

                        // Questions
                        questions: [],
                    };
                },

                watch: {
                    'quiz.title'(newTitle) {
                        this.quiz.slug = this.slugify(newTitle);
                    },

                    questions: {
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
                        this.resetCascade('grade');
                    },

                    // Grade change
                    onGradeChange() {
                        const grade = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                        this.filteredSubjects = grade ? grade.subjects : [];
                        this.resetCascade('subject');
                    },

                    // Subject change
                    onSubjectChange() {
                        const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                        this.filteredBooks = subject ? subject.books : [];
                        this.resetCascade('book');
                    },

                    // Book change
                    onBookChange() {
                        const book = this.filteredBooks.find(b => b.id == this.selectedBookId);
                        this.filteredChapters = book?.chapters ?? [];
                        this.selectedChapterId = '';
                    },

                    // Reset cascade
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

                    // Add chapter
                    addChapter() {
                        if (!this.selectedBoardId || !this.selectedGradeId || 
                            !this.selectedSubjectId || !this.selectedBookId || 
                            !this.selectedChapterId) {
                            alert('Please select all fields');
                            return;
                        }

                        // Check duplicate
                        const isDuplicate = this.selectedChapters.some(c => 
                            c.chapterId == this.selectedChapterId
                        );

                        if (isDuplicate) {
                            alert('Chapter already added!');
                            return;
                        }

                        // Get names
                        const board = this.boards.find(b => b.id == this.selectedBoardId);
                        const grade = this.filteredGrades.find(g => g.id == this.selectedGradeId);
                        const subject = this.filteredSubjects.find(s => s.id == this.selectedSubjectId);
                        const book = this.filteredBooks.find(b => b.id == this.selectedBookId);
                        const chapter = this.filteredChapters.find(c => c.id == this.selectedChapterId);

                        // Add
                        this.selectedChapters.push({
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

                        // Reset
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

                    // Remove chapter
                    removeChapter(index) {
                        this.selectedChapters.splice(index, 1);
                    },

                    // Add question
                    addQuestion() {
                        this.questions.push({
                            text: '',
                            options: [
                                { text: '', useTinymce: false },
                                { text: '', useTinymce: false }
                            ],
                            correctOptions: []
                        });

                        this.$nextTick(() => {
                            this.initializeTinyMCE();
                        });
                    },

                    // Remove question
                    removeQuestion(qIndex) {
                        this.questions.splice(qIndex, 1);

                        this.$nextTick(() => {
                            this.initializeTinyMCE();
                        });
                    },

                    // Add option
                    addOption(qIndex) {
                        this.questions[qIndex].options.push({ 
                            text: '', 
                            useTinymce: false 
                        });
                    },

                    // Remove option
                    removeOption(qIndex, oIndex) {
                        if (this.questions[qIndex].options.length > 2) {
                            this.questions[qIndex].options.splice(oIndex, 1);
                            
                            // Remove from correctOptions if it was selected
                            const correctIndex = this.questions[qIndex].correctOptions.indexOf(oIndex);
                            if (correctIndex > -1) {
                                this.questions[qIndex].correctOptions.splice(correctIndex, 1);
                            }

                            // Adjust correctOptions indices
                            this.questions[qIndex].correctOptions = this.questions[qIndex].correctOptions.map(idx => {
                                return idx > oIndex ? idx - 1 : idx;
                            });

                            this.$nextTick(() => {
                                this.initializeTinyMCE();
                            });
                        }
                    },

                    // Toggle TinyMCE for option
                    toggleTinymce(qIndex, oIndex) {
                        const option = this.questions[qIndex].options[oIndex];
    
                        // ✅ SAVE CONTENT BEFORE TOGGLE
                        if (option.useTinymce) {
                            // Switching FROM TinyMCE TO plain text
                            const editorId = 'option_text_' + qIndex + '_' + oIndex;
                            const editor = tinymce.get(editorId);
                            
                            if (editor) {
                                // Save content from TinyMCE
                                option.text = editor.getContent();
                            }
                        }
                        
                        this.$nextTick(() => {
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
                        if (typeof tinymce !== 'undefined') {
                            // ✅ SAVE CONTENT BEFORE REMOVING
                            tinymce.get().forEach(editor => {
                                const editorId = editor.id;
                                const content = editor.getContent();
                                
                                // Find and update the corresponding option
                                this.questions.forEach((question, qIndex) => {
                                    question.options.forEach((option, oIndex) => {
                                        const expectedId = 'option_text_' + qIndex + '_' + oIndex;
                                        if (editorId.includes(expectedId)) {
                                            option.text = content;
                                        }
                                    });
                                });
                            });
                            
                            tinymce.remove();
                        }

                        this.$nextTick(() => {
                            if (typeof tinymce === 'undefined') {
                                console.warn('TinyMCE not loaded yet');
                                return;
                            }

                            const commonConfig = {
                                menubar: true,
                                relative_urls: false,
                                remove_script_host: false,
                                document_base_url: '/',

                                plugins: `
                                    advlist autolink lists link image charmap preview anchor
                                    searchreplace visualblocks code fullscreen
                                    insertdatetime media table help wordcount save
                                    directionality
                                `,

                                toolbar: `
                                    undo redo | formatselect |
                                    bold italic underline strikethrough |
                                    forecolor backcolor |
                                    alignleft aligncenter alignright alignjustify |
                                    bullist numlist outdent indent |
                                    link image media table |
                                    fullscreen preview code removeformat
                                `,

                                image_advtab: true,
                                file_picker_types: 'image',
                                
                                file_picker_callback: (cb) => {
                                    const input = document.createElement('input');
                                    input.type = 'file';
                                    input.accept = 'image/*';

                                    input.onchange = () => {
                                        const file = input.files[0];
                                        const reader = new FileReader();

                                        reader.onload = () => {
                                            cb(reader.result, { title: file.name });
                                        };

                                        reader.readAsDataURL(file);
                                    };

                                    input.click();
                                },

                                images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
                                    const xhr = new XMLHttpRequest();
                                    xhr.open('POST', '/admin/tinymce/upload');

                                    xhr.upload.onprogress = e => {
                                        progress((e.loaded / e.total) * 100);
                                    };

                                    xhr.onload = () => {
                                        if (xhr.status < 200 || xhr.status >= 300) {
                                            reject('Upload failed');
                                            return;
                                        }

                                        const json = JSON.parse(xhr.responseText);
                                        if (!json.location) {
                                            reject('Invalid response');
                                            return;
                                        }

                                        resolve(json.location);
                                    };

                                    const formData = new FormData();
                                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                                    xhr.send(formData);
                                }),

                                directionality: 'ltr',
                                
                                // ✅ PRESERVE CONTENT ON INIT
                                setup: (editor) => {
                                    editor.on('init', () => {
                                        console.log('Editor initialized:', editor.id);
                                    });
                                }
                            };

                            // DESCRIPTION
                            tinymce.init({
                                selector: 'textarea.description',
                                height: 300,
                                ...commonConfig
                            });

                            // QUESTION TEXT
                            tinymce.init({
                                selector: 'textarea.question_text',
                                height: 300,
                                ...commonConfig
                            });

                            // OPTION TEXT (with TinyMCE enabled)
                            this.questions.forEach((question, qIndex) => {
                                question.options.forEach((option, oIndex) => {
                                    if (option.useTinymce) {
                                        const selector = 'textarea.option_text_' + qIndex + '_' + oIndex;
                                        
                                        tinymce.init({
                                            selector: selector,
                                            height: 200,
                                            ...commonConfig,
                                            // ✅ SET INITIAL CONTENT
                                            init_instance_callback: (editor) => {
                                                if (option.text) {
                                                    editor.setContent(option.text);
                                                }
                                            }
                                        });
                                    }
                                });
                            });
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>