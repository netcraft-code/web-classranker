<x-admin::layouts>
    <x-slot:title>@lang('class_ranker::app.discussions.edit.title')</x-slot>

    <x-admin::form
        :action="route('admin.discussions.update', $discussion->id)"
        method="PUT"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.discussions.edit.title')
            </p>
            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.discussions.index') }}"
                   class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    @lang('admin::app.account.edit.back-btn')
                </a>
                <button type="submit" class="primary-button">
                    @lang('class_ranker::app.discussions.edit.save-btn')
                </button>
            </div>
        </div>

        <v-edit-discussion
            hints-url="{{ route('admin.discussions.hints') }}"
            :hashtags="{{ json_encode($hashtags->map(fn($h) => ['id' => $h->id, 'name' => $h->name])) }}"
            :selected-ids="{{ json_encode($discussion->hashtags->pluck('id')) }}"
            initial-title="{{ old('title', $discussion->title) }}"
            initial-description="{{ old('description', $discussion->description) }}"
            initial-board="{{ old('board', $discussion->board) }}"
            initial-class="{{ old('class', $discussion->class) }}"
            initial-subject="{{ old('subject', $discussion->subject) }}"
            initial-book="{{ old('book', $discussion->book) }}"
            initial-chapter="{{ old('chapter', $discussion->chapter) }}"
            :initial-status="{{ old('status', $discussion->status) ? 'true' : 'false' }}"
        ></v-edit-discussion>
    </x-admin::form>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-edit-discussion-template">
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.discussions.create.information')
                        </p>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">Title</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text" name="title" rules="required" v-model="title"
                            />
                            <x-admin::form.control-group.error control-name="title" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Description</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                id="description" type="textarea" name="description" v-model="description" rows="4" :tinymce="true" 
                            />
                        </x-admin::form.control-group>
                    </div>

                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.discussions.create.taxonomy')
                        </p>

                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Board
                                </label>

                                <div class="rounded border bg-gray-100 px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    {{ $discussion->board?->name }}
                                </div>

                                <input type="hidden" name="board_id" value="{{ $discussion->board_id }}">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Grade
                                </label>

                                <div class="rounded border bg-gray-100 px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    {{ $discussion->grade?->name }}
                                </div>

                                <input type="hidden" name="grade_id" value="{{ $discussion->grade_id }}">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Subject
                                </label>

                                <div class="rounded border bg-gray-100 px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    {{ $discussion->subject?->name }}
                                </div>

                                <input type="hidden" name="subject_id" value="{{ $discussion->subject_id }}">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Book
                                </label>

                                <div class="rounded border bg-gray-100 px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    {{ $discussion->book?->title }}
                                </div>

                                <input type="hidden" name="book_id" value="{{ $discussion->book_id }}">
                            </div>

                            <div class="col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Chapter
                                </label>

                                <div class="rounded border bg-gray-100 px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    {{ $discussion->chapter?->title }}
                                </div>

                                <input type="hidden" name="chapter_id" value="{{ $discussion->chapter_id }}">
                            </div>

                        </div>
                    </div>

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
                                <input type="checkbox" :name="`hashtag_ids[]`" :value="tag.id" v-model="selectedHashtags" style="display:none;" />
                                #@{{ tag.name }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">Settings</p>
                        </x-slot>
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>Status</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="switch" name="status" value="1"
                                    ::checked="initialStatus"
                                    label="Status"
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

        <script type="module">
            app.component('v-edit-discussion', {
                template: '#v-edit-discussion-template',
                props: {
                    hintsUrl:           String,
                    hashtags:           { type: Array, default: () => [] },
                    selectedIds:        { type: Array, default: () => [] },
                    initialTitle:       String,
                    initialDescription: String,
                    initialBoard:       String,
                    initialClass:       String,
                    initialSubject:     String,
                    initialBook:        String,
                    initialChapter:     String,
                    initialStatus:      Boolean,
                },

                data() {
                    return {
                        title:           this.initialTitle       ?? '',
                        description:     this.initialDescription ?? '',
                        board:           this.initialBoard       ?? '',
                        classVal:        this.initialClass       ?? '',
                        subject:         this.initialSubject     ?? '',
                        book:            this.initialBook        ?? '',
                        chapter:         this.initialChapter     ?? '',
                        selectedHashtags: [...(this.selectedIds ?? [])],
                    };
                },

                methods: {
                    hashtagName(id) {
                        return this.hashtags.find(h => h.id === id)?.name ?? '';
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>