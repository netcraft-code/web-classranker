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
            :initial-imports="{{ json_encode($quizImports ?? []) }}"
            :import-routes="{{ json_encode([
                'index'     => route('admin.study_materials.quizzes.imports.index',     $quiz->id),
                'upload'    => route('admin.study_materials.quizzes.imports.upload',    $quiz->id),
                'status'    => route('admin.study_materials.quizzes.imports.status',    [$quiz->id, ':importId']),
                'update'    => route('admin.study_materials.quizzes.imports.update',    [$quiz->id, ':importId']),
                'destroy'   => route('admin.study_materials.quizzes.imports.destroy',   [$quiz->id, ':importId']),
                'questions' => route('admin.study_materials.quizzes.imports.questions', $quiz->id),
            ]) }}"
        ></v-edit-quiz>
    </x-admin::form>

    @pushOnce('scripts')

    {{-- ═══════════════════════════════════════════════════════════
         TEMPLATE: v-edit-quiz
    ═══════════════════════════════════════════════════════════ --}}
    <script type="text/x-template" id="v-edit-quiz-template">
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                <!-- BASIC INFO -->
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

                <!-- QUESTIONS -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        Questions
                        <span class="ml-2 text-sm font-normal text-gray-400">(@{{ questions.length }})</span>
                    </p>

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
                                    :class="opt.is_correct ? 'bg-green-50 border border-green-200' : 'bg-white border'"
                                >
                                    <span :class="opt.is_correct ? 'text-green-600 font-bold' : 'text-gray-400'"
                                        class="flex-shrink-0">@{{ oIdx + 1 }}.
                                    </span>
                                    
                                    <span v-html="opt.text" class="flex-1 prose max-w-none text-sm"></span>
                                    <span v-if="opt.is_correct" class="text-xs text-green-600 font-semibold flex-shrink-0">&#10003; Correct</span>
                                </div>
                            </div>

                            <div v-if="question.solution" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded">
                                <p class="text-xs font-semibold text-blue-700 mb-1">Solution:</p>
                                <div class="text-sm text-blue-800 prose max-w-none" v-html="question.solution"></div>
                            </div>
                        </template>

                        <!-- Edit form -->
                        <template v-if="question.editing">
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Question Text <span class="text-red-500">*</span>
                                </label>
                                <textarea :id="'question_text_' + question.id"
                                    class="w-full border rounded p-2 text-sm">@{{ question.text }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Options</label>

                                <div v-for="(opt, oIdx) in question.options" :key="oIdx"
                                    class="mb-3 p-3 border rounded bg-white dark:bg-gray-900">

                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            Option @{{ oIdx + 1 }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <label class="flex items-center gap-1 text-xs">
                                                <input type="checkbox" v-model="opt.useTinymce"
                                                    @change="onOptionTinymceToggle(question, oIdx)"
                                                    class="h-3 w-3">
                                                <span class="text-gray-600">Rich Editor</span>
                                            </label>
                                            <label class="flex items-center gap-1">
                                                <input type="checkbox" :value="oIdx"
                                                    v-model="question.correctOptions" class="h-4 w-4">
                                                <span class="text-xs text-green-600 font-semibold">Correct</span>
                                            </label>
                                            <button v-if="question.options.length > 2" type="button"
                                                @click="removeEditOption(question, oIdx)"
                                                class="text-red-500 text-xs font-semibold">Remove</button>
                                        </div>
                                    </div>

                                    <div v-if="!opt.useTinymce">
                                        <textarea v-model="opt.text" rows="2"
                                            class="w-full border rounded p-2 text-sm" placeholder="Option text"></textarea>
                                    </div>
                                    <div v-else>
                                        <textarea :id="'opt_' + question.id + '_' + oIdx"
                                            class="w-full border rounded p-2 text-sm">@{{ opt.text }}</textarea>
                                    </div>
                                </div>

                                <button type="button"
                                    @click="question.options.push({ text: '', useTinymce: false, is_correct: false })"
                                    class="text-sm text-blue-600 hover:text-blue-800">+ Add Option</button>
                            </div>

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

                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Question Text <span class="text-red-500">*</span>
                            </label>
                            <textarea id="new_question_text" class="w-full border rounded p-2 text-sm"></textarea>
                        </div>

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
                                                @change="onNewOptionTinymceToggle(oIdx)" class="h-3 w-3">
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
                                    <textarea :id="'new_opt_' + oIdx" class="w-full border rounded p-2 text-sm"></textarea>
                                </div>
                            </div>

                            <button type="button"
                                @click="newQuestion.options.push({ text: '', useTinymce: false })"
                                class="text-sm text-blue-600 hover:text-blue-800">+ Add Option</button>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Solution / Explanation
                            </label>
                            <textarea id="new_solution_text" class="w-full border rounded p-2 text-sm"></textarea>
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

            <!-- RIGHT SIDEBAR -->
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
                                @{{ c.boardName }} &#8594; @{{ c.gradeName }} &#8594; @{{ c.subjectName }} &#8594; @{{ c.bookName }} &#8594; @{{ c.chapterName }}
                            </span>
                            <button v-if="selectedChapters.length > 1" type="button"
                                @click="ajaxRemoveChapter(c.id, index)"
                                class="text-red-600 hover:text-red-800 ml-2 flex-shrink-0">&#215;</button>
                        </div>
                    </div>
                </div>

                <!-- XLS Import -->
                <v-xls-import
                    :initial-imports="initialImports"
                    :import-routes="importRoutes"
                ></v-xls-import>

            </div>
        </div>
    </script>

    {{-- ═══════════════════════════════════════════════════════════
         TEMPLATE: v-xls-import
    ═══════════════════════════════════════════════════════════ --}}
    <script type="text/x-template" id="v-xls-import-template">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">

            <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">Import Questions (XLS)</p>
            <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                Columns: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">question, option_a, option_b, option_c, option_d, correct_option, solution</code>
            </p>

            <!-- Drop zone -->
            <label
                class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed rounded cursor-pointer transition-colors"
                :class="dragging
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-gray-300 bg-gray-50 dark:bg-gray-800 hover:border-blue-400'"
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="onDrop"
            >
                <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Drop .xlsx / .xls here or <span class="text-blue-600 font-semibold">browse</span>
                </span>
                <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" multiple class="hidden"
                    @change="onFileSelect">
            </label>

            <!-- Upload queue -->
            <div v-if="queue.length" class="mt-3 space-y-2">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Ready to upload</p>
                <div v-for="(item, idx) in queue" :key="idx"
                    class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 border rounded text-xs">
                    <span class="truncate max-w-[200px] text-gray-700 dark:text-gray-300">@{{ item.file.name }}</span>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" @click="uploadFile(item)" :disabled="item.uploading"
                            class="text-blue-600 hover:text-blue-800 font-semibold">
                            @{{ item.uploading ? 'Uploading...' : 'Upload' }}
                        </button>
                        <button type="button" @click="queue.splice(idx, 1)"
                            class="text-red-500 hover:text-red-700">&#215;</button>
                    </div>
                </div>
                <button type="button" @click="uploadAll" :disabled="allUploading"
                    class="primary-button w-full text-sm mt-1">
                    @{{ allUploading ? 'Uploading...' : 'Upload All' }}
                </button>
            </div>

            <!-- Existing imports list -->
            <div v-if="imports.length" class="mt-4 space-y-3">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Uploaded Files</p>

                <div v-for="(imp, idx) in imports" :key="imp.id"
                    class="p-3 border rounded bg-gray-50 dark:bg-gray-800">

                    <!-- Filename + status badge -->
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate max-w-[180px]">
                            @{{ imp.original_filename }}
                        </span>
                        <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full font-semibold"
                            :class="{
                                'bg-yellow-100 text-yellow-700': imp.status === 'pending',
                                'bg-blue-100  text-blue-700':   imp.status === 'processing',
                                'bg-green-100 text-green-700':  imp.status === 'completed',
                                'bg-red-100   text-red-700':    imp.status === 'failed',
                            }">
                            @{{ imp.status }}
                        </span>
                    </div>

                    <!-- Progress bar -->
                    <div v-if="imp.status === 'processing' || imp.status === 'completed'" class="mb-1">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full transition-all duration-300"
                                :class="imp.status === 'completed' ? 'bg-green-500' : 'bg-blue-500'"
                                :style="{ width: imp.progress_percent + '%' }"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            @{{ imp.progress_percent }}% &#8212; @{{ imp.imported_rows }} / @{{ imp.total_rows }} questions imported
                        </p>
                    </div>

                    <!-- Error message -->
                    <p v-if="imp.status === 'failed' && imp.error_message"
                        class="text-xs text-red-600 mb-1">@{{ imp.error_message }}</p>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 mt-2">
                        <label class="cursor-pointer text-xs text-blue-600 hover:text-blue-800 font-semibold">
                            Replace
                            <input type="file" accept=".xlsx,.xls,.csv" class="hidden"
                                @change="replaceFile(imp, $event)">
                        </label>
                        <button type="button"
                            @click="deleteImport(imp, idx)"
                            :disabled="imp.deleting"
                            class="text-xs text-red-600 hover:text-red-800 font-semibold">
                            @{{ imp.deleting ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <p v-if="!imports.length && !queue.length"
                class="mt-3 text-center text-xs text-gray-400 dark:text-gray-500">
                No files uploaded yet.
            </p>
        </div>
    </script>

    <script type="module">

        // ════════════════════════════════════════════════════════════════
        // v-edit-quiz
        // ════════════════════════════════════════════════════════════════
        app.component('v-edit-quiz', {
            template: '#v-edit-quiz-template',

            props: {
                initialChapters:  { type: Array,  default: () => [] },
                initialQuestions: { type: Array,  default: () => [] },
                boards:           { type: Array,  default: () => [] },
                quizId:           { type: Number, required: true },
                routes:           { type: Object, required: true },
                initialImports:   { type: Array,  default: () => [] },
                importRoutes:     { type: Object, required: true },
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

            mounted() {
                // Jab XLS import complete ho — questions list bina page refresh ke update ho
                this.$emitter.on('quiz-questions-refreshed', (freshQuestions) => {
                    this.questions = freshQuestions.map(q => ({
                        ...q,
                        editing:        false,
                        saving:         false,
                        deleting:       false,
                        correctOptions: q.correctOptions || [],
                    }));
                    this.$emitter.emit('add-flash', {
                        type:    'success',
                        message: 'Questions list updated from imported file.',
                    });
                });
            },

            watch: {
                'quiz.title'(val) { this.quiz.slug = this.slugify(val); },
            },

            methods: {
                defaultNewQuestion() {
                    return {
                        text: '', solution: '',
                        options: [
                            { text: '', useTinymce: false },
                            { text: '', useTinymce: false },
                        ],
                        correctOptions: [],
                    };
                },

                // Cascade
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

                // Chapters AJAX
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

                // Questions AJAX
                startEditQuestion(question) {
                    question._snapshot = JSON.parse(JSON.stringify({
                        text:           question.text,
                        solution:       question.solution,
                        options:        question.options,
                        correctOptions: question.correctOptions,
                    }));
                    question.editing = true;
                    this.$nextTick(() => this.initQuestionEditors(question));
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
                    const qtEditor = tinymce.get('question_text_' + question.id);
                    if (qtEditor) question.text = qtEditor.getContent();
                    const solEditor = tinymce.get('solution_' + question.id);
                    if (solEditor) question.solution = solEditor.getContent();
                    question.options.forEach((opt, oIdx) => {
                        if (opt.useTinymce) {
                            const e = tinymce.get('opt_' + question.id + '_' + oIdx);
                            if (e) opt.text = e.getContent();
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
                            const e = tinymce.get('new_opt_' + oIdx);
                            if (e) opt.text = e.getContent();
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
                        options:         this.newQuestion.options.map(o => ({
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
                        options:         question.options.map(o => ({
                            text:        o.text,
                            use_tinymce: o.useTinymce ? 1 : 0,
                        })),
                        correct_options: question.correctOptions,
                    })
                    .then(res => {
                        const updated           = res.data.question;
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
                    const e = tinymce.get('opt_' + question.id + '_' + oIdx);
                    if (e) e.remove();
                    question.options.splice(oIdx, 1);
                    const ci = question.correctOptions.indexOf(oIdx);
                    if (ci > -1) question.correctOptions.splice(ci, 1);
                    question.correctOptions = question.correctOptions.map(i => i > oIdx ? i - 1 : i);
                },

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
                    this.showNewQuestion = false;
                    this.newQuestion     = this.defaultNewQuestion();
                },

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
                        const editor = tinymce.get('opt_' + question.id + '_' + oIdx);
                        if (editor) { opt.text = editor.getContent(); editor.remove(); }
                    }
                },

                onNewOptionTinymceToggle(oIdx) {
                    const opt = this.newQuestion.options[oIdx];
                    if (opt.useTinymce) {
                        this.$nextTick(() => this.initTinyMCE('new_opt_' + oIdx, opt.text || ''));
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

        // ════════════════════════════════════════════════════════════════
        // v-xls-import
        // ════════════════════════════════════════════════════════════════
        app.component('v-xls-import', {
            template: '#v-xls-import-template',

            props: {
                initialImports: { type: Array,  default: () => [] },
                importRoutes:   { type: Object, required: true },
            },

            data() {
                return {
                    imports:  this.initialImports.map(i => ({ ...i, deleting: false })),
                    queue:    [],
                    dragging: false,
                };
            },

            computed: {
                allUploading() {
                    return this.queue.length > 0 && this.queue.every(i => i.uploading);
                },
            },

            methods: {
                onFileSelect(e) {
                    Array.from(e.target.files).forEach(f => this.addToQueue(f));
                    e.target.value = '';
                },

                onDrop(e) {
                    this.dragging = false;
                    Array.from(e.dataTransfer.files).forEach(f => this.addToQueue(f));
                },

                addToQueue(file) {
                    const allowed = ['xlsx', 'xls', 'csv'];
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!allowed.includes(ext)) {
                        this.$emitter.emit('add-flash', { type: 'error', message: `${file.name}: unsupported format.` });
                        return;
                    }
                    this.queue.push({ file, uploading: false });
                },

                async uploadFile(item) {
                    item.uploading = true;
                    const fd = new FormData();
                    fd.append('file', item.file);

                    try {
                        const res = await this.$axios.post(this.importRoutes.upload, fd, {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        });

                        const imp = { ...res.data.import, deleting: false };
                        this.imports.unshift(imp);

                        // Remove from queue
                        const idx = this.queue.indexOf(item);
                        if (idx > -1) this.queue.splice(idx, 1);

                        if (imp.status === 'processing' || imp.status === 'pending') {
                            this.pollStatus(imp);
                        } else if (imp.status === 'completed') {
                            // Small file — already done, refresh questions immediately
                            this.refreshQuestions();
                        }

                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    } catch (err) {
                        const msg = err.response?.data?.message ?? 'Upload failed';
                        this.$emitter.emit('add-flash', { type: 'error', message: msg });
                        item.uploading = false;
                    }
                },

                uploadAll() {
                    this.queue.filter(i => !i.uploading).forEach(i => this.uploadFile(i));
                },

                pollStatus(imp) {
                    const statusUrl = this.importRoutes.status.replace(':importId', imp.id);

                    const timer = setInterval(async () => {
                        try {
                            const res   = await this.$axios.get(statusUrl);
                            const fresh = res.data.import;

                            imp.status           = fresh.status;
                            imp.total_rows       = fresh.total_rows;
                            imp.imported_rows    = fresh.imported_rows;
                            imp.progress_percent = fresh.progress_percent;
                            imp.error_message    = fresh.error_message;

                            if (fresh.status === 'completed') {
                                clearInterval(timer);
                                // Fetch fresh questions + emit to v-edit-quiz — no page reload!
                                this.refreshQuestions();
                            } else if (fresh.status === 'failed') {
                                clearInterval(timer);
                            }
                        } catch {
                            clearInterval(timer);
                        }
                    }, 1500);
                },

                async refreshQuestions() {
                    try {
                        const res = await this.$axios.get(this.importRoutes.questions);
                        // v-edit-quiz ka mounted() listener yeh event sunke questions update kar dega
                        this.$emitter.emit('quiz-questions-refreshed', res.data.questions);
                    } catch {
                        // Silently ignore — questions are saved in DB
                    }
                },

                async replaceFile(imp, event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    event.target.value = '';

                    const fd = new FormData();
                    fd.append('file', file);

                    try {
                        const url = this.importRoutes.update.replace(':importId', imp.id);
                        const res = await this.$axios.post(url, fd, {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        });

                        const fresh           = res.data.import;
                        imp.original_filename = fresh.original_filename;
                        imp.status            = fresh.status;
                        imp.total_rows        = fresh.total_rows;
                        imp.imported_rows     = fresh.imported_rows;
                        imp.progress_percent  = fresh.progress_percent;
                        imp.error_message     = fresh.error_message;

                        if (fresh.status === 'processing' || fresh.status === 'pending') {
                            this.pollStatus(imp);
                        } else if (fresh.status === 'completed') {
                            this.refreshQuestions();
                        }

                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    } catch (err) {
                        const msg = err.response?.data?.message ?? 'Replace failed';
                        this.$emitter.emit('add-flash', { type: 'error', message: msg });
                    }
                },

                async deleteImport(imp, idx) {
                    if (!confirm('Delete this import record?\n\nNote: Already imported questions will remain in the quiz.')) return;
                    imp.deleting = true;
                    try {
                        const url = this.importRoutes.destroy.replace(':importId', imp.id);
                        const res = await this.$axios.delete(url);
                        this.imports.splice(idx, 1);
                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    } catch {
                        imp.deleting = false;
                        this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to delete import' });
                    }
                },
            },
        });

    </script>
    @endPushOnce
</x-admin::layouts>