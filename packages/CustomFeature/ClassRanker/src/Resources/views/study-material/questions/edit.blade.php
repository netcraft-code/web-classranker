<x-admin::layouts>
    <x-slot:title>
        @lang('class_ranker::app.study_materials.questions.edit.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.study_materials.questions.update', $question->id)"
        method="PUT"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.study_materials.questions.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.study_materials.questions.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <button type="submit" class="primary-button">
                    @lang('class_ranker::app.study_materials.questions.edit.update-btn')
                </button>
            </div>
        </div>

        <v-edit-questions
            :initial-assignments="{{ json_encode($formattedAssignments) }}"
            :initial-question-items="{{ json_encode($formattedQuestionItems) }}"
            :initial-faqs="{{ json_encode($formattedFaqs) }}"
            :boards="{{ json_encode($boards) }}"
            :question-id="{{ $question->id }}"
            :routes="{{ json_encode([
                'assignments_add'    => route('admin.study_materials.questions.assignments.add',    $question->id),
                'assignments_remove' => route('admin.study_materials.questions.assignments.remove', [$question->id, ':assignmentId']),
                'items_add'          => route('admin.study_materials.questions.items.add',          $question->id),
                'items_update'       => route('admin.study_materials.questions.items.update',       [$question->id, ':itemId']),
                'items_remove'       => route('admin.study_materials.questions.items.remove',       [$question->id, ':itemId']),
                'faqs_add'           => route('admin.study_materials.questions.faqs.add',           $question->id),
                'faqs_update'        => route('admin.study_materials.questions.faqs.update',        [$question->id, ':faqId']),
                'faqs_remove'        => route('admin.study_materials.questions.faqs.remove',        [$question->id, ':faqId']),
            ]) }}"
        ></v-edit-questions>
    </x-admin::form>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-edit-questions-template">
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <!-- ── ASSIGNMENTS ── -->
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

                        <button type="button" @click="ajaxAddAssignment"
                            :disabled="assignmentLoading"
                            class="secondary-button">
                            <span v-if="assignmentLoading">Adding...</span>
                            <span v-else>+ Add Assignment</span>
                        </button>

                        <div v-if="assignments.length > 0" class="mt-4 space-y-2">
                            <div v-for="(a, index) in assignments" :key="a.id"
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded border">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    @{{ index+1 }}. @{{ a.boardName }} → @{{ a.gradeName }} → @{{ a.subjectName }} → @{{ a.bookName }} → @{{ a.chapterName }}
                                </span>
                                <!-- Remove btn sirf tab show karo jab assignments > 1 -->
                                <button v-if="assignments.length > 1"
                                    type="button" @click="ajaxRemoveAssignment(a.id, index)"
                                    class="text-red-600 hover:text-red-800 text-sm font-semibold ml-3 flex-shrink-0">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ── BASIC INFO ── -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Basic Information</p>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">Title (Web View)</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="text" name="title" rules="required"
                                v-model="title" :value="old('title', $question->title)" />
                            <x-admin::form.control-group.error control-name="title" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Slug</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="text" name="slug"
                                v-model="slug" :value="old('slug', $question->slug)" readonly />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">Short Title (Mobile)</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="text" name="short_title" rules="required"
                                v-model="shortTitle" :value="old('short_title', $question->short_title)" />
                            <x-admin::form.control-group.error control-name="short_title" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Top Description</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="textarea" id="top_description"
                                class="top_description" name="top_description"
                                :value="old('top_description', $question->top_description)" :tinymce="true" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- ── QUESTION ITEMS ── -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Questions</p>

                        <!-- Existing items — read only, edit toggle -->
                        <div v-for="(item, index) in questionItems" :key="item.id"
                            class="mb-4 p-4 border rounded bg-gray-50 dark:bg-gray-800">

                            <div class="flex items-center justify-between mb-3">
                                <p class="font-semibold text-gray-700 dark:text-white">
                                    Question @{{ index + 1 }}
                                    <span v-if="item.editing" class="ml-2 text-xs text-yellow-600 font-normal">(Editing)</span>
                                </p>
                                <div class="flex items-center gap-2">
                                    <!-- Edit / Cancel -->
                                    <button v-if="!item.editing" type="button"
                                        @click="startEditItem(item)"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                        Edit
                                    </button>
                                    <button v-if="item.editing" type="button"
                                        @click="item.editing = false; destroyTinyMCE('question_text_' + item.id); destroyTinyMCE('answer_text_' + item.id)"
                                        class="text-gray-500 hover:text-gray-700 text-sm font-semibold">
                                        Cancel
                                    </button>
                                    <!-- Save -->
                                    <button v-if="item.editing" type="button"
                                        @click="ajaxUpdateQuestionItem(item, index)"
                                        :disabled="item.saving"
                                        class="text-green-600 hover:text-green-800 text-sm font-semibold">
                                        @{{ item.saving ? 'Saving...' : 'Save' }}
                                    </button>
                                    <!-- Delete -->
                                    <button type="button"
                                        @click="ajaxRemoveQuestionItem(item.id, index)"
                                        :disabled="item.deleting"
                                        class="text-red-600 hover:text-red-800 text-sm font-semibold">
                                        @{{ item.deleting ? 'Removing...' : 'Remove' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Read-only view -->
                            <template v-if="!item.editing">
                                <p class="text-xs text-gray-500 mb-1">Q No: @{{ item.question_number }}</p>
                                <div class="text-sm text-gray-700 dark:text-gray-300 mb-2 prose max-w-none"
                                    v-html="item.question"></div>
                                <hr class="my-2 dark:border-gray-700">
                                <div class="text-sm text-gray-600 dark:text-gray-400 prose max-w-none"
                                    v-html="item.answer"></div>
                                <p v-if="item.page_number" class="text-xs text-gray-400 mt-1">Page: @{{ item.page_number }}</p>
                            </template>

                            <!-- Edit form -->
                            <template v-if="item.editing">
                                <div class="mb-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Question Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" v-model="item.question_number"
                                        class="w-full border rounded p-2 text-sm">
                                </div>
                                <div class="mb-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Question <span class="text-red-500">*</span>
                                    </label>
                                    <textarea :id="'question_text_' + item.id"
                                        class="w-full border rounded p-2 text-sm">@{{ item.question }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Answer <span class="text-red-500">*</span>
                                    </label>
                                    <textarea :id="'answer_text_' + item.id"
                                        class="w-full border rounded p-2 text-sm">@{{ item.answer }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Page Number</label>
                                    <input type="text" v-model="item.page_number"
                                        class="w-full border rounded p-2 text-sm" placeholder="e.g. 45">
                                </div>
                            </template>
                        </div>

                        <!-- New item form -->
                        <div v-if="showNewQuestion" class="mb-4 p-4 border-2 border-dashed border-blue-300 rounded bg-blue-50 dark:bg-gray-800">
                            <p class="font-semibold text-blue-700 dark:text-white mb-3">New Question</p>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Question Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" v-model="newQuestion.question_number"
                                    class="w-full border rounded p-2 text-sm" placeholder="1">
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Question <span class="text-red-500">*</span>
                                </label>
                                <textarea id="new_question_text"
                                    class="w-full border rounded p-2 text-sm"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Answer <span class="text-red-500">*</span>
                                </label>
                                <textarea id="new_answer_text"
                                    class="w-full border rounded p-2 text-sm"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Page Number</label>
                                <input type="text" v-model="newQuestion.page_number"
                                    class="w-full border rounded p-2 text-sm" placeholder="e.g. 45">
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="ajaxAddQuestionItem"
                                    :disabled="questionSaving"
                                    class="primary-button text-sm">
                                    @{{ questionSaving ? 'Saving...' : 'Save Question' }}
                                </button>
                                <button type="button" @click="showNewQuestion = false; resetNewQuestion()"
                                    class="transparent-button text-sm">
                                    Cancel
                                </button>
                            </div>
                        </div>

                        <button v-if="!showNewQuestion" type="button"
                            @click="showNewQuestion = true"
                            class="secondary-button">
                            + Add Question
                        </button>
                    </div>

                    <!-- ── BOTTOM DESCRIPTION ── -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Bottom Description</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="textarea" id="bottom_description"
                                class="bottom_description" name="bottom_description"
                                :value="old('bottom_description', $question->bottom_description)" :tinymce="true" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- ── FAQ ── -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">FAQ Schema</p>

                        <!-- Existing FAQs -->
                        <div v-for="(faq, index) in faqs" :key="faq.id"
                            class="mb-4 p-4 border rounded bg-gray-50 dark:bg-gray-800">

                            <div class="flex items-center justify-between mb-3">
                                <p class="font-semibold text-gray-700 dark:text-white">
                                    FAQ @{{ index + 1 }}
                                    <span v-if="faq.editing" class="ml-2 text-xs text-yellow-600 font-normal">(Editing)</span>
                                </p>
                                <div class="flex items-center gap-2">
                                    <button v-if="!faq.editing" type="button"
                                        @click="startEditFaq(faq)"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                        Edit
                                    </button>
                                    <button v-if="faq.editing" type="button"
                                        @click="faq.editing = false; destroyTinyMCE('faq_answer_' + faq.id)"
                                        class="text-gray-500 hover:text-gray-700 text-sm font-semibold">
                                        Cancel
                                    </button>
                                    <button v-if="faq.editing" type="button"
                                        @click="ajaxUpdateFaq(faq, index)"
                                        :disabled="faq.saving"
                                        class="text-green-600 hover:text-green-800 text-sm font-semibold">
                                        @{{ faq.saving ? 'Saving...' : 'Save' }}
                                    </button>
                                    <button type="button"
                                        @click="ajaxRemoveFaq(faq.id, index)"
                                        :disabled="faq.deleting"
                                        class="text-red-600 hover:text-red-800 text-sm font-semibold">
                                        @{{ faq.deleting ? 'Removing...' : 'Remove' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Read-only -->
                            <template v-if="!faq.editing">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">@{{ faq.question }}</p>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 prose max-w-none"
                                    v-html="faq.answer"></div>
                            </template>

                            <!-- Edit form -->
                            <template v-if="faq.editing">
                                <div class="mb-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Question <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" v-model="faq.question"
                                        class="w-full border rounded p-2 text-sm">
                                </div>
                                <div class="mb-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Answer <span class="text-red-500">*</span>
                                    </label>
                                    <textarea :id="'faq_answer_' + faq.id"
                                        class="w-full border rounded p-2 text-sm">@{{ faq.answer }}</textarea>
                                </div>
                            </template>
                        </div>

                        <!-- New FAQ form -->
                        <div v-if="showNewFaq" class="mb-4 p-4 border-2 border-dashed border-blue-300 rounded bg-blue-50 dark:bg-gray-800">
                            <p class="font-semibold text-blue-700 dark:text-white mb-3">New FAQ</p>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Question <span class="text-red-500">*</span>
                                </label>
                                <input type="text" v-model="newFaq.question"
                                    class="w-full border rounded p-2 text-sm" placeholder="FAQ question">
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Answer <span class="text-red-500">*</span>
                                </label>
                                <textarea id="new_faq_answer"
                                    class="w-full border rounded p-2 text-sm"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="ajaxAddFaq"
                                    :disabled="faqSaving"
                                    class="primary-button text-sm">
                                    @{{ faqSaving ? 'Saving...' : 'Save FAQ' }}
                                </button>
                                <button type="button" @click="showNewFaq = false; resetNewFaq()"
                                    class="transparent-button text-sm">
                                    Cancel
                                </button>
                            </div>
                        </div>

                        <button v-if="!showNewFaq" type="button"
                            @click="showNewFaq = true"
                            class="secondary-button">
                            + Add FAQ
                        </button>
                    </div>

                    <!-- ── RELATED LINKS ── -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Related Links</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="textarea" id="related_links"
                                class="related_links" name="related_links"
                                :value="old('related_links', $question->related_links)" :tinymce="true" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- ── SEO ── -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">SEO Meta Tags</p>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Meta Title</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="text" name="meta_title"
                                :value="old('meta_title', $question->meta_title)" />
                        </x-admin::form.control-group>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Meta Description</x-admin::form.control-group.label>
                            <textarea name="meta_description" rows="3" class="w-full border rounded p-2 text-sm">{{ old('meta_description', $question->meta_description) }}</textarea>
                        </x-admin::form.control-group>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Meta Keywords</x-admin::form.control-group.label>
                            <textarea name="meta_keywords" rows="2" class="w-full border rounded p-2 text-sm">{{ old('meta_keywords', $question->meta_keywords) }}</textarea>
                        </x-admin::form.control-group>
                    </div>

                </div>

                <!-- ── RIGHT SIDEBAR ── -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">Settings</p>
                        </x-slot>
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">Status</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="switch" name="status" value="1"
                                    :checked="(boolean) old('status', $question->status)" />
                            </x-admin::form.control-group>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>Is Premium</x-admin::form.control-group.label>
                                <input type="checkbox" name="is_premium" value="1"
                                    class="h-4 w-4 rounded border-gray-300"
                                    {{ old('is_premium', $question->is_premium) ? 'checked' : '' }}>
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-edit-questions', {
                template: '#v-edit-questions-template',

                props: {
                    initialAssignments:   { type: Array, default: () => [] },
                    initialQuestionItems: { type: Array, default: () => [] },
                    initialFaqs:          { type: Array, default: () => [] },
                    boards:               { type: Array, default: () => [] },
                    questionId:           { type: Number, required: true },
                    routes:               { type: Object, required: true },
                },

                data() {
                    return {
                        title:      '{{ old('title', $question->title) }}',
                        slug:       '{{ old('slug', $question->slug) }}',
                        shortTitle: '{{ old('short_title', $question->short_title) }}',

                        // Cascade
                        selectedBoardId:   '', selectedGradeId:   '',
                        selectedSubjectId: '', selectedBookId:    '',
                        selectedChapterId: '',
                        filteredGrades:    [], filteredSubjects:  [],
                        filteredBooks:     [], filteredChapters:  [],

                        assignmentLoading: false,

                        // Pre-loaded — each has id for AJAX
                        assignments: this.initialAssignments,

                        questionItems: this.initialQuestionItems.map(item => ({
                            ...item,
                            editing:  false,
                            saving:   false,
                            deleting: false,
                        })),

                        faqs: this.initialFaqs.map(faq => ({
                            ...faq,
                            editing:  false,
                            saving:   false,
                            deleting: false,
                        })),

                        // New item forms
                        showNewQuestion: false,
                        newQuestion: { question_number: '', question: '', answer: '', page_number: '' },
                        questionSaving: false,

                        showNewFaq: false,
                        newFaq: { question: '', answer: '' },
                        faqSaving: false,
                    };
                },
                
                watch: {
                    title(val) { this.slug = this.slugify(val); },

                    questionItems: {
                        deep: true,
                        handler(items, oldItems) {
                            items.forEach((item, index) => {
                                const wasEditing = oldItems?.[index]?.editing;
                                // Sirf tab init karo jab editing false → true hua ho
                                if (item.editing && !wasEditing) {
                                    this.$nextTick(() => {
                                        this.initItemTinyMCE(`question_text_${item.id}`, item.question);
                                        this.initItemTinyMCE(`answer_text_${item.id}`, item.answer);
                                    });
                                }
                            });
                        }
                    },

                    faqs: {
                        deep: true,
                        handler(faqs, oldFaqs) {
                            faqs.forEach((faq, index) => {
                                const wasEditing = oldFaqs?.[index]?.editing;
                                // Sirf tab init karo jab editing false → true hua ho
                                if (faq.editing && !wasEditing) {
                                    this.$nextTick(() => {
                                        this.initItemTinyMCE(`faq_answer_${faq.id}`, faq.answer);
                                    });
                                }
                            });
                        }
                    },

                    showNewQuestion(val) {
                        if (val) {
                            this.$nextTick(() => {
                                this.initItemTinyMCE('new_question_text', '');
                                this.initItemTinyMCE('new_answer_text', '');
                            });
                        } else {
                            this.destroyTinyMCE('new_question_text');
                            this.destroyTinyMCE('new_answer_text');
                        }
                    },

                    showNewFaq(val) {
                        if (val) {
                            this.$nextTick(() => {
                                this.initItemTinyMCE('new_faq_answer', '');
                            });
                        } else {
                            this.destroyTinyMCE('new_faq_answer');
                        }
                    },
                },

                methods: {
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

                    // ── Assignments ──────────────────────────────────────
                    ajaxAddAssignment() {
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

                        this.assignmentLoading = true;
                        this.$axios.post(this.routes.assignments_add, {
                            board_id:   this.selectedBoardId,
                            grade_id:   this.selectedGradeId,
                            subject_id: this.selectedSubjectId,
                            book_id:    this.selectedBookId,
                            chapter_id: this.selectedChapterId,
                        })
                        .then(res => {
                            this.assignments.push(res.data.assignment);
                            this.selectedBoardId   = ''; this.selectedGradeId   = '';
                            this.selectedSubjectId = ''; this.selectedBookId    = '';
                            this.selectedChapterId = '';
                            this.filteredGrades    = []; this.filteredSubjects  = [];
                            this.filteredBooks     = []; this.filteredChapters  = [];
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to add assignment' }))
                        .finally(() => this.assignmentLoading = false);
                    },

                    ajaxRemoveAssignment(assignmentId, index) {
                        if (!confirm('Remove this assignment?')) return;
                        console.log(assignmentId, index);
                        this.$axios.delete(this.routes.assignments_remove.replace(':assignmentId', assignmentId))
                            .then(res => {
                                this.assignments.splice(index, 1);
                                this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                            })
                            .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to remove assignment' }));
                    },

                    // ── Question Items ───────────────────────────────────
                    ajaxAddQuestionItem() {
                        this.newQuestion.question = this.getTinyMCEContent('new_question_text');
                        this.newQuestion.answer   = this.getTinyMCEContent('new_answer_text');

                        if (!this.newQuestion.question_number || !this.newQuestion.question || !this.newQuestion.answer) {
                            alert('Question number, question and answer are required'); return;
                        }
                        this.questionSaving = true;
                        this.$axios.post(this.routes.items_add, {
                            question_number: this.newQuestion.question_number,
                            question:        this.newQuestion.question,
                            answer:          this.newQuestion.answer,
                            page_number:     this.newQuestion.page_number,
                        })
                        .then(res => {
                            this.questionItems.push({ ...res.data.item, editing: false, saving: false, deleting: false });
                            this.showNewQuestion = false;
                            this.resetNewQuestion();
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to add question' }))
                        .finally(() => this.questionSaving = false);
                    },

                    ajaxUpdateQuestionItem(item) {
                        item.question = this.getTinyMCEContent(`question_text_${item.id}`);
                        item.answer   = this.getTinyMCEContent(`answer_text_${item.id}`);

                        if (!item.question_number || !item.question || !item.answer) {
                            alert('Question number, question and answer are required'); return;
                        }
                        item.saving = true;
                        this.$axios.put(this.routes.items_update.replace(':itemId', item.id), {
                            question_number: item.question_number,
                            question:        item.question,
                            answer:          item.answer,
                            page_number:     item.page_number,
                        })
                        .then(res => {
                            item.editing = false;
                            this.destroyTinyMCE(`question_text_${item.id}`);
                            this.destroyTinyMCE(`answer_text_${item.id}`);
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to update question' }))
                        .finally(() => item.saving = false);
                    },

                    ajaxRemoveQuestionItem(itemId, index) {
                        if (!confirm('Remove this question?')) return;
                        this.questionItems[index].deleting = true;
                        this.$axios.delete(this.routes.items_remove.replace(':itemId', itemId))
                            .then(res => {
                                this.questionItems.splice(index, 1);
                                this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                            })
                            .catch(() => {
                                this.questionItems[index].deleting = false;
                                this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to remove question' });
                            });
                    },

                    resetNewQuestion() {
                        this.newQuestion = { question_number: '', question: '', answer: '', page_number: '' };
                    },

                    // ── FAQs ─────────────────────────────────────────────
                    ajaxAddFaq() {
                        this.newFaq.answer = this.getTinyMCEContent('new_faq_answer');

                        if (!this.newFaq.question || !this.newFaq.answer) {
                            alert('Question and answer are required'); return;
                        }
                        this.faqSaving = true;
                        this.$axios.post(this.routes.faqs_add, {
                            question: this.newFaq.question,
                            answer:   this.newFaq.answer,
                        })
                        .then(res => {
                            this.faqs.push({ ...res.data.faq, editing: false, saving: false, deleting: false });
                            this.showNewFaq = false;
                            this.resetNewFaq();
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to add FAQ' }))
                        .finally(() => this.faqSaving = false);
                    },

                    ajaxUpdateFaq(faq) {
                        faq.answer = this.getTinyMCEContent(`faq_answer_${faq.id}`);

                        if (!faq.question || !faq.answer) {
                            alert('Question and answer are required'); return;
                        }
                        faq.saving = true;
                        this.$axios.put(this.routes.faqs_update.replace(':faqId', faq.id), {
                            question: faq.question,
                            answer:   faq.answer,
                        })
                        .then(res => {
                            faq.editing = false;
                            this.destroyTinyMCE(`faq_answer_${faq.id}`);
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to update FAQ' }))
                        .finally(() => faq.saving = false);
                    },

                    ajaxRemoveFaq(faqId, index) {
                        if (!confirm('Remove this FAQ?')) return;
                        this.faqs[index].deleting = true;
                        this.$axios.delete(this.routes.faqs_remove.replace(':faqId', faqId))
                            .then(res => {
                                this.faqs.splice(index, 1);
                                this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                            })
                            .catch(() => {
                                this.faqs[index].deleting = false;
                                this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to remove FAQ' });
                            });
                    },

                    resetNewFaq() {
                        this.newFaq = { question: '', answer: '' };
                    },

                    slugify(text) {
                        return text.toString().toLowerCase().trim()
                            .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '').replace(/\-\-+/g, '-');
                    },

                    // Shared config — ek jagah define karo
                    getTinyMCEConfig() {
                        return {
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

                    initItemTinyMCE(id, content = '') {
                        if (typeof tinymce === 'undefined') return;

                        const existing = tinymce.get(id);
                        if (existing) existing.remove();

                        tinymce.init({
                            selector: `#${id}`,
                            height: 300,
                            ...this.getTinyMCEConfig(),   // ← pura config yahan
                            setup(editor) {
                                editor.on('init', () => editor.setContent(content || ''));
                            },
                        });
                    },

                    destroyTinyMCE(id) {
                        const editor = tinymce.get(id);
                        if (editor) editor.remove();
                    },

                    getTinyMCEContent(id) {
                        const editor = tinymce.get(id);
                        return editor ? editor.getContent() : document.getElementById(id)?.value || '';
                    },

                    // Edit btn click method — item ke liye
                    startEditItem(item) {
                        item.editing = true;
                        this.$nextTick(() => {
                            this.initItemTinyMCE(`question_text_${item.id}`, item.question);
                            this.initItemTinyMCE(`answer_text_${item.id}`, item.answer);
                        });
                    },

                    startEditFaq(faq) {
                        faq.editing = true;
                        this.$nextTick(() => {
                            this.initItemTinyMCE(`faq_answer_${faq.id}`, faq.answer);
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>