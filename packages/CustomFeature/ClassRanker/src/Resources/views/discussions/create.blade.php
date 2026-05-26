<x-admin::layouts>
    <x-slot:title>@lang('class_ranker::app.discussions.create.title')</x-slot>

    <x-admin::form :action="route('admin.discussions.store')">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.discussions.create.title')
            </p>
            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.discussions.index') }}"
                   class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    @lang('admin::app.account.edit.back-btn')
                </a>
                <button type="submit" class="primary-button">
                    @lang('class_ranker::app.discussions.create.save-btn')
                </button>
            </div>
        </div>

        <v-create-discussion
            hints-url="{{ route('admin.discussions.hints') }}"
            :hashtags="{{ json_encode($hashtags->map(fn($h) => ['id' => $h->id, 'name' => $h->name, 'slug' => $h->slug])) }}"
            :boards='@json($boards)'
        ></v-create-discussion>
    </x-admin::form>

    @pushOnce('scripts')
        <!-- ====== TEMPLATE ====== -->
        <script type="text/x-template" id="v-create-discussion-template">
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">

                <!-- LEFT -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <!-- Basic Info -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.discussions.create.information')
                        </p>

                        <!-- Title with hint dropdown -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.discussions.create.title-label')
                            </x-admin::form.control-group.label>

                            <div style="position:relative;">
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="title"
                                    rules="required"
                                    v-model="title"
                                    @input="onTitleInput"
                                    @blur="hideHintsDelayed"
                                    :value="old('title')"
                                    :label="trans('class_ranker::app.discussions.create.title-label')"
                                    :placeholder="trans('class_ranker::app.discussions.create.title-placeholder')"
                                    autocomplete="off"
                                />

                                <!-- Hints dropdown -->
                                <div
                                    v-if="hints.length > 0 && showHints"
                                    style="position:absolute;top:100%;left:0;right:0;z-index:50;background:#fff;border:1px solid #e2e8f0;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.08);max-height:240px;overflow-y:auto;"
                                >
                                    <div
                                        v-for="hint in hints"
                                        :key="hint.id"
                                        @mousedown.prevent="openHint(hint)"
                                        style="padding:10px 14px;cursor:pointer;font-size:13px;color:#374151;border-bottom:1px solid #f1f5f9;"
                                        onmouseover="this.style.background='#f8fafc'"
                                        onmouseout="this.style.background=''"
                                    >
                                        🔗 @{{ hint.title }}
                                    </div>
                                </div>
                            </div>

                            <x-admin::form.control-group.error control-name="title" />
                        </x-admin::form.control-group>

                        <!-- Description -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.discussions.create.description')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="textarea"
                                id="description"
                                name="description"
                                rules="required"
                                :value="old('description')"
                                :label="trans('class_ranker::app.discussions.create.description')"
                                :placeholder="trans('class_ranker::app.discussions.create.description-placeholder')"
                                :tinymce="true"
                            />
                        </x-admin::form.control-group>
                    </div>

                    <!-- Taxonomy -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.discussions.create.taxonomy')
                        </p>

                        <div class="grid grid-cols-2 gap-4">

                            <!-- BOARD -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Board
                                </x-admin::form.control-group.label>

                                <select
                                    v-model="selectedBoardId"
                                    name="board_id"
                                    @change="onBoardChange"
                                    class="w-full rounded border px-3 py-2 text-sm dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="">Select Board</option>

                                    <option
                                        v-for="board in boards"
                                        :key="board.id"
                                        :value="board.id"
                                    >
                                        @{{ board.name }}
                                    </option>
                                </select>
                            </x-admin::form.control-group>

                            <!-- GRADE -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Grade
                                </x-admin::form.control-group.label>

                                <select
                                    v-model="selectedGradeId"
                                    name="grade_id"
                                    @change="onGradeChange"
                                    :disabled="!filteredGrades.length"
                                    class="w-full rounded border px-3 py-2 text-sm dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="">Select Grade</option>

                                    <option
                                        v-for="grade in filteredGrades"
                                        :key="grade.id"
                                        :value="grade.id"
                                    >
                                        @{{ grade.name }}
                                    </option>
                                </select>
                            </x-admin::form.control-group>

                            <!-- SUBJECT -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Subject
                                </x-admin::form.control-group.label>

                                <select
                                    v-model="selectedSubjectId"
                                    name="subject_id"
                                    @change="onSubjectChange"
                                    :disabled="!filteredSubjects.length"
                                    class="w-full rounded border px-3 py-2 text-sm dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="">Select Subject</option>

                                    <option
                                        v-for="subject in filteredSubjects"
                                        :key="subject.id"
                                        :value="subject.id"
                                    >
                                        @{{ subject.name }}
                                    </option>
                                </select>
                            </x-admin::form.control-group>

                            <!-- BOOK -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Book
                                </x-admin::form.control-group.label>

                                <select
                                    v-model="selectedBookId"
                                    name="book_id"
                                    @change="onBookChange"
                                    :disabled="!filteredBooks.length"
                                    class="w-full rounded border px-3 py-2 text-sm dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="">Select Book</option>

                                    <option
                                        v-for="book in filteredBooks"
                                        :key="book.id"
                                        :value="book.id"
                                    >
                                        @{{ book.title }}
                                    </option>
                                </select>
                            </x-admin::form.control-group>

                            <!-- CHAPTER -->
                            <x-admin::form.control-group class="col-span-2">
                                <x-admin::form.control-group.label class="required">
                                    Chapter
                                </x-admin::form.control-group.label>

                                <select
                                    v-model="selectedChapterId"
                                    name="chapter_id"
                                    :disabled="!filteredChapters.length"
                                    class="w-full rounded border px-3 py-2 text-sm dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="">Select Chapter</option>

                                    <option
                                        v-for="chapter in filteredChapters"
                                        :key="chapter.id"
                                        :value="chapter.id"
                                    >
                                        @{{ chapter.title }}
                                    </option>
                                </select>
                            </x-admin::form.control-group>

                        </div>
                    </div>

                    <!-- Hashtags -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.discussions.create.hashtags')
                        </p>

                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                            <label
                                v-for="tag in hashtags"
                                :key="tag.id"
                                style="display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;border:1.5px solid #e2e8f0;cursor:pointer;font-size:13px;transition:all .15s;"
                                :style="selectedHashtags.includes(tag.id) ? 'border-color:#6366f1;background:#eef2ff;color:#6366f1;font-weight:600;' : 'color:#64748b;'"
                            >
                                <input
                                    type="checkbox"
                                    :name="`hashtag_ids[]`"
                                    :value="tag.id"
                                    v-model="selectedHashtags"
                                    style="display:none;"
                                />
                                #@{{ tag.name }}
                            </label>
                        </div>

                        <p v-if="hashtags.length === 0" class="text-sm text-gray-400">
                            No hashtags found. <a href="{{ route('admin.hashtags.create') }}" class="text-indigo-500 underline">Create one</a>.
                        </p>
                    </div>
                </div>

                <!-- RIGHT: Settings -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('class_ranker::app.discussions.create.settings')
                            </p>
                        </x-slot>
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('class_ranker::app.discussions.create.status')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    value="1"
                                    :checked="true"
                                    :label="trans('class_ranker::app.discussions.create.status')"
                                />
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::accordion>

                    <!-- Live Preview -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">Preview</p>
                        </x-slot>
                        <x-slot:content>
                            <div style="border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                                <p style="font-size:15px;font-weight:700;color:#1e293b;margin-bottom:6px;">
                                    @{{ title || 'Discussion Title' }}
                                </p>
                                <p style="font-size:12px;color:#94a3b8;margin-bottom:10px;">
                                    @{{ description || 'No description.' }}
                                </p>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                    <span
                                        v-for="tagId in selectedHashtags"
                                        :key="tagId"
                                        style="font-size:11px;background:#eef2ff;color:#6366f1;padding:3px 10px;border-radius:20px;"
                                    >
                                        #@{{ hashtagName(tagId) }}
                                    </span>
                                </div>
                            </div>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <!-- ====== SCRIPT ====== -->
        <script type="module">
            app.component('v-create-discussion', {
                template: '#v-create-discussion-template',

                props: {
                    hintsUrl:  { type: String, required: true },
                    hashtags:  { type: Array,  default: () => [] },
                    boards:    { type: Array, default: () => [] },
                },

                mounted() {
                    if (this.selectedBoardId) {
                        this.onBoardChange();
                    }

                    if (this.selectedGradeId) {
                        this.onGradeChange();
                    }

                    if (this.selectedSubjectId) {
                        this.onSubjectChange();
                    }

                    if (this.selectedBookId) {
                        this.onBookChange();
                    }
                },

                data() {
                    return {
                        title: @json(old('title', '')),

                        description: @json(old('description', '')),

                        selectedHashtags: @json(old('hashtag_ids', [])),

                        hints: [],
                        showHints: false,
                        hintTimer: null,

                        selectedBoardId: @json(old('board_id', '')),
                        selectedGradeId: @json(old('grade_id', '')),
                        selectedSubjectId: @json(old('subject_id', '')),
                        selectedBookId: @json(old('book_id', '')),
                        selectedChapterId: @json(old('chapter_id', '')),

                        filteredGrades: [],
                        filteredSubjects: [],
                        filteredBooks: [],
                        filteredChapters: [],
                    };
                },

                methods: {
                    onTitleInput() {
                        clearTimeout(this.hintTimer);
                        if (this.title.length < 2) { this.hints = []; return; }

                        this.hintTimer = setTimeout(async () => {
                            const res  = await fetch(`${this.hintsUrl}?q=${encodeURIComponent(this.title)}`);
                            this.hints = await res.json();
                            this.showHints = this.hints.length > 0;
                        }, 300);
                    },

                    hideHintsDelayed() {
                        setTimeout(() => { this.showHints = false; }, 200);
                    },

                    openHint(hint) {
                        window.open('{{ route('admin.discussions.edit', '') }}/' + hint.id, '_blank');
                    },

                    hashtagName(id) {
                        return this.hashtags.find(h => h.id === id)?.name ?? '';
                    },

                    onBoardChange() {
                        const board = this.boards.find(
                            b => b.id == this.selectedBoardId
                        );

                        this.filteredGrades = board?.grades ?? [];

                        this.selectedGradeId = '';
                        this.selectedSubjectId = '';
                        this.selectedBookId = '';
                        this.selectedChapterId = '';

                        this.filteredSubjects = [];
                        this.filteredBooks = [];
                        this.filteredChapters = [];
                    },

                    onGradeChange() {
                        const grade = this.filteredGrades.find(
                            g => g.id == this.selectedGradeId
                        );

                        this.filteredSubjects = grade?.subjects ?? [];

                        this.selectedSubjectId = '';
                        this.selectedBookId = '';
                        this.selectedChapterId = '';

                        this.filteredBooks = [];
                        this.filteredChapters = [];
                    },

                    onSubjectChange() {
                        const subject = this.filteredSubjects.find(
                            s => s.id == this.selectedSubjectId
                        );

                        this.filteredBooks = subject?.books ?? [];

                        this.selectedBookId = '';
                        this.selectedChapterId = '';

                        this.filteredChapters = [];
                    },

                    onBookChange() {
                        const book = this.filteredBooks.find(
                            b => b.id == this.selectedBookId
                        );

                        this.filteredChapters = book?.chapters ?? [];

                        this.selectedChapterId = '';
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>