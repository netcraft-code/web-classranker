<x-admin::layouts>
    <x-slot:title>Edit Video</x-slot>

    <x-admin::form
        :action="route('admin.study_materials.videos.update', $video->id)"
        method="PUT"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">Edit Video</p>
            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.study_materials.videos.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">Back</a>
                <button type="submit" class="primary-button">Update</button>
            </div>
        </div>

        <v-edit-videos
            :initial-assignments="{{ json_encode($formattedAssignments) }}"
            :initial-video-items="{{ json_encode($formattedVideoItems) }}"
            :boards="{{ json_encode($boards) }}"
            :video-id="{{ $video->id }}"
            :routes="{{ json_encode([
                'assignments_add'    => route('admin.study_materials.videos.assignments.add',    $video->id),
                'assignments_remove' => route('admin.study_materials.videos.assignments.remove', [$video->id, ':assignmentId']),
                'items_add'          => route('admin.study_materials.videos.items.add',          $video->id),
                'items_update'       => route('admin.study_materials.videos.items.update',       [$video->id, ':itemId']),
                'items_remove'       => route('admin.study_materials.videos.items.remove',       [$video->id, ':itemId']),
            ]) }}"
        ></v-edit-videos>
    </x-admin::form>

    @pushOnce('scripts')
    <script type="text/x-template" id="v-edit-videos-template">
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

                    <button type="button" @click="ajaxAddAssignment" :disabled="assignmentLoading" class="secondary-button">
                        <span v-if="assignmentLoading">Adding...</span>
                        <span v-else>+ Add Assignment</span>
                    </button>

                    <div v-if="assignments.length > 0" class="mt-4 space-y-2">
                        <div v-for="(a, index) in assignments" :key="a.id"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded border">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                @{{ index+1 }}. @{{ a.boardName }} → @{{ a.gradeName }} → @{{ a.subjectName }} → @{{ a.bookName }} → @{{ a.chapterName }}
                            </span>
                            <button v-if="assignments.length > 1" type="button"
                                @click="ajaxRemoveAssignment(a.id, index)"
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
                            v-model="title" :value="old('title', $video->title)" />
                        <x-admin::form.control-group.error control-name="title" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Slug</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="slug"
                            v-model="slug" :value="old('slug', $video->slug)" readonly />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">Short Title (Mobile)</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="short_title" rules="required"
                            v-model="shortTitle" :value="old('short_title', $video->short_title)" />
                        <x-admin::form.control-group.error control-name="short_title" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Top Description</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="top_description"
                            class="top_description" name="top_description"
                            :value="old('top_description', $video->top_description)" :tinymce="true" />
                    </x-admin::form.control-group>
                </div>

                <!-- ── VIDEO ITEMS ── -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Videos</p>

                    <!-- Existing items -->
                    <div v-for="(item, index) in videoItems" :key="item.id"
                        class="mb-4 p-4 border rounded bg-gray-50 dark:bg-gray-800">

                        <div class="flex items-center justify-between mb-3">
                            <p class="font-semibold text-gray-700 dark:text-white">
                                Video @{{ index + 1 }}
                                <span v-if="item.editing" class="ml-2 text-xs text-yellow-600 font-normal">(Editing)</span>
                            </p>
                            <div class="flex items-center gap-2">
                                <button v-if="!item.editing" type="button"
                                    @click="startEditItem(item)"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-semibold">Edit</button>
                                <button v-if="item.editing" type="button"
                                    @click="cancelEditItem(item)"
                                    class="text-gray-500 hover:text-gray-700 text-sm font-semibold">Cancel</button>
                                <button v-if="item.editing" type="button"
                                    @click="ajaxUpdateVideoItem(item)"
                                    :disabled="item.saving"
                                    class="text-green-600 hover:text-green-800 text-sm font-semibold">
                                    @{{ item.saving ? 'Saving...' : 'Save' }}
                                </button>
                                <button type="button"
                                    @click="ajaxRemoveVideoItem(item.id, index)"
                                    :disabled="item.deleting"
                                    class="text-red-600 hover:text-red-800 text-sm font-semibold">
                                    @{{ item.deleting ? 'Removing...' : 'Remove' }}
                                </button>
                            </div>
                        </div>

                        <!-- Read-only view -->
                        <template v-if="!item.editing">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">@{{ item.title }}</p>

                            <div v-if="item.video_url" class="mb-2">
                                <video :src="item.video_url" controls
                                    class="w-[360px] max-w-full max-h-[400px] rounded border bg-black" preload="metadata"></video>
                            </div>
                            <p v-else class="text-xs text-gray-400 mb-2">No video uploaded</p>

                            <div v-if="item.thumbnail_full_url" class="mb-2">
                                <img :src="item.thumbnail_full_url" class="h-20 rounded border object-cover">
                            </div>

                            <div class="flex gap-4 text-xs text-gray-500">
                                <span v-if="item.duration">⏱ @{{ item.duration }}</span>
                                <span>Position: @{{ item.position }}</span>
                                <span :class="item.status ? 'text-green-600' : 'text-red-500'">
                                    @{{ item.status ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </template>

                        <!-- Edit form -->
                        <template v-if="item.editing">
                            <!-- Title -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" v-model="item.title" class="w-full border rounded p-2 text-sm">
                            </div>

                            <!-- Video -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Video File</label>

                                <div v-if="item.video_url && !item.video_temp_path && !item.delete_video" class="mb-2">
                                    <video :src="item.video_url" controls class="w-[360px] max-w-full max-h-[400px] rounded border bg-black" preload="metadata"></video>
                                    <div class="flex items-center gap-3 mt-1">
                                        <p class="text-xs text-gray-400 flex-1">Current video</p>
                                        <button type="button" @click="item.delete_video = true"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium">🗑 Delete</button>
                                    </div>
                                </div>

                                <div v-if="item.delete_video && !item.video_temp_path"
                                    class="mb-2 p-2 bg-red-50 border border-red-200 rounded flex items-center justify-between">
                                    <p class="text-xs text-red-600">⚠ Video will be deleted on save</p>
                                    <button type="button" @click="item.delete_video = false"
                                        class="text-xs text-blue-600 hover:underline">Undo</button>
                                </div>

                                <input type="file" accept="video/mp4,video/webm,video/ogg,video/avi,video/mov"
                                    @change="onVideoSelect($event, item)"
                                    class="w-full border rounded p-2 text-sm bg-white cursor-pointer">

                                <div v-if="item.video_temp_path" class="mt-2">
                                    <video :src="item.video_preview" controls class="w-[360px] max-w-full max-h-[400px] rounded border bg-black" preload="metadata"></video>
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="text-xs text-green-600 flex-1">✓ New video — will replace on save</p>
                                        <button type="button" @click="item.video_temp_path = null; item.video_preview = null"
                                            class="text-xs text-red-500 hover:underline">Cancel</button>
                                    </div>
                                </div>

                                <div v-if="item.uploading_video" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Uploading video...
                                </div>
                            </div>

                            <!-- Thumbnail -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Thumbnail</label>

                                <div v-if="item.thumbnail_full_url && !item.thumbnail_temp_path && !item.delete_thumbnail" class="mb-2">
                                    <img :src="item.thumbnail_full_url" class="h-24 rounded border object-cover">
                                    <div class="flex items-center gap-3 mt-1">
                                        <p class="text-xs text-gray-400 flex-1">Current thumbnail</p>
                                        <button type="button" @click="item.delete_thumbnail = true"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium">🗑 Delete</button>
                                    </div>
                                </div>

                                <div v-if="item.delete_thumbnail && !item.thumbnail_temp_path"
                                    class="mb-2 p-2 bg-red-50 border border-red-200 rounded flex items-center justify-between">
                                    <p class="text-xs text-red-600">⚠ Thumbnail will be deleted on save</p>
                                    <button type="button" @click="item.delete_thumbnail = false"
                                        class="text-xs text-blue-600 hover:underline">Undo</button>
                                </div>

                                <input type="file" accept="image/jpeg,image/png,image/webp,image/jpg"
                                    @change="onThumbnailSelect($event, item)"
                                    class="w-full border rounded p-2 text-sm bg-white cursor-pointer">

                                <div v-if="item.thumbnail_temp_path" class="mt-2">
                                    <img :src="item.thumbnail_preview" class="h-24 rounded border object-cover">
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="text-xs text-green-600 flex-1">✓ New thumbnail — will replace on save</p>
                                        <button type="button" @click="item.thumbnail_temp_path = null; item.thumbnail_preview = null"
                                            class="text-xs text-red-500 hover:underline">Cancel</button>
                                    </div>
                                </div>

                                <div v-if="item.uploading_thumbnail" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Uploading thumbnail...
                                </div>
                            </div>

                            <!-- Duration + Position -->
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Duration</label>
                                    <input type="text" v-model="item.duration" class="w-full border rounded p-2 text-sm" placeholder="e.g. 10:30">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Position</label>
                                    <input type="number" v-model="item.position" class="w-full border rounded p-2 text-sm">
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="flex items-center gap-2">
                                <input type="checkbox" v-model="item.status" class="h-4 w-4 rounded border-gray-300">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Active</label>
                            </div>
                        </template>
                    </div>

                    <!-- New video form -->
                    <div v-if="showNewVideo" class="mb-4 p-4 border-2 border-dashed border-blue-300 rounded bg-blue-50 dark:bg-gray-800">
                        <p class="font-semibold text-blue-700 dark:text-white mb-3">New Video</p>

                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" v-model="newVideo.title" class="w-full border rounded p-2 text-sm" placeholder="Video title">
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Video File <span class="text-red-500">*</span>
                            </label>
                            <input type="file" accept="video/mp4,video/webm,video/ogg,video/avi,video/mov"
                                @change="onNewVideoSelect($event)"
                                class="w-full border rounded p-2 text-sm bg-white cursor-pointer">
                            <div v-if="newVideo.video_preview" class="mt-2">
                                <video :src="newVideo.video_preview" controls class="w-[360px] max-w-full max-h-[200px] rounded border bg-black" preload="metadata"></video>
                                <p class="text-xs text-green-600 mt-1">✓ @{{ newVideo.video_name }}</p>
                            </div>
                            <div v-if="newVideo.uploading_video" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                Uploading...
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Thumbnail</label>
                            <input type="file" accept="image/jpeg,image/png,image/webp,image/jpg"
                                @change="onNewThumbnailSelect($event)"
                                class="w-full border rounded p-2 text-sm bg-white cursor-pointer">
                            <div v-if="newVideo.thumbnail_preview" class="mt-2">
                                <img :src="newVideo.thumbnail_preview" class="h-24 rounded border object-cover">
                            </div>
                            <div v-if="newVideo.uploading_thumbnail" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                Uploading...
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Duration</label>
                                <input type="text" v-model="newVideo.duration" class="w-full border rounded p-2 text-sm" placeholder="e.g. 10:30">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Position</label>
                                <input type="number" v-model="newVideo.position" class="w-full border rounded p-2 text-sm">
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mb-4">
                            <input type="checkbox" v-model="newVideo.status" class="h-4 w-4 rounded border-gray-300">
                            <label class="text-sm text-gray-700 dark:text-gray-300">Active</label>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" @click="ajaxAddVideoItem" :disabled="videoSaving" class="primary-button text-sm">
                                @{{ videoSaving ? 'Saving...' : 'Save Video' }}
                            </button>
                            <button type="button" @click="showNewVideo = false; resetNewVideo()" class="transparent-button text-sm">Cancel</button>
                        </div>
                    </div>

                    <button v-if="!showNewVideo" type="button" @click="showNewVideo = true" class="secondary-button">
                        + Add Video
                    </button>
                </div>

                <!-- ── BOTTOM DESCRIPTION ── -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Bottom Description</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="bottom_description"
                            class="bottom_description" name="bottom_description"
                            :value="old('bottom_description', $video->bottom_description)" :tinymce="true" />
                    </x-admin::form.control-group>
                </div>

                <!-- ── SEO ── -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">SEO Meta Tags</p>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Title</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="meta_title"
                            :value="old('meta_title', $video->meta_title)" />
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Description</x-admin::form.control-group.label>
                        <textarea name="meta_description" rows="3" class="w-full border rounded p-2 text-sm">{{ old('meta_description', $video->meta_description) }}</textarea>
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Keywords</x-admin::form.control-group.label>
                        <textarea name="meta_keywords" rows="2" class="w-full border rounded p-2 text-sm">{{ old('meta_keywords', $video->meta_keywords) }}</textarea>
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
                                :checked="(boolean) old('status', $video->status)" />
                        </x-admin::form.control-group>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Is Premium</x-admin::form.control-group.label>
                            <input type="checkbox" name="is_premium" value="1"
                                class="h-4 w-4 rounded border-gray-300"
                                {{ old('is_premium', $video->is_premium) ? 'checked' : '' }}>
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-edit-videos', {
            template: '#v-edit-videos-template',

            props: {
                initialAssignments: { type: Array, default: () => [] },
                initialVideoItems:  { type: Array, default: () => [] },
                boards:             { type: Array, default: () => [] },
                videoId:            { type: Number, required: true },
                routes:             { type: Object, required: true },
            },

            data() {
                return {
                    title:      '{{ old('title', $video->title) }}',
                    slug:       '{{ old('slug', $video->slug) }}',
                    shortTitle: '{{ old('short_title', $video->short_title) }}',

                    selectedBoardId:   '', selectedGradeId:   '',
                    selectedSubjectId: '', selectedBookId:    '',
                    selectedChapterId: '',
                    filteredGrades:    [], filteredSubjects:  [],
                    filteredBooks:     [], filteredChapters:  [],

                    assignmentLoading: false,
                    assignments: this.initialAssignments,

                    videoItems: this.initialVideoItems.map(item => ({
                        ...item,
                        editing:             false,
                        saving:              false,
                        deleting:            false,
                        delete_video:        false,
                        delete_thumbnail:    false,
                        video_preview:       null,
                        video_temp_path:     null,
                        uploading_video:     false,
                        thumbnail_preview:   null,
                        thumbnail_temp_path: null,
                        uploading_thumbnail: false,
                    })),

                    showNewVideo:  false,
                    newVideo:      this.defaultNewVideo(),
                    videoSaving:   false,
                };
            },

            watch: {
                title(val) { this.slug = this.slugify(val); },
            },

            methods: {
                defaultNewVideo() {
                    return {
                        title: '', duration: '', position: 0, status: true,
                        video_temp_path: null, video_preview: null, video_name: null, uploading_video: false,
                        thumbnail_temp_path: null, thumbnail_preview: null, uploading_thumbnail: false,
                    };
                },

                resetNewVideo() {
                    this.newVideo = this.defaultNewVideo();
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
                    this.$axios.delete(this.routes.assignments_remove.replace(':assignmentId', assignmentId))
                        .then(res => {
                            this.assignments.splice(index, 1);
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to remove' }));
                },

                // ── Video Items ──────────────────────────────────────
                startEditItem(item) {
                    // Snapshot save karo — cancel pe restore karne ke liye
                    item._snapshot = {
                        title: item.title, duration: item.duration,
                        position: item.position, status: item.status,
                    };
                    item.editing = true;
                },

                cancelEditItem(item) {
                    if (item._snapshot) {
                        item.title    = item._snapshot.title;
                        item.duration = item._snapshot.duration;
                        item.position = item._snapshot.position;
                        item.status   = item._snapshot.status;
                    }
                    item.editing          = false;
                    item.delete_video     = false;
                    item.delete_thumbnail = false;
                    item.video_temp_path  = null;
                    item.video_preview    = null;
                    item.thumbnail_temp_path = null;
                    item.thumbnail_preview   = null;
                },

                ajaxAddVideoItem() {
                    if (!this.newVideo.title) { alert('Title required'); return; }
                    if (!this.newVideo.video_temp_path) { alert('Video file required'); return; }

                    this.videoSaving = true;
                    this.$axios.post(this.routes.items_add, {
                        title:                this.newVideo.title,
                        video_temp_path:      this.newVideo.video_temp_path,
                        thumbnail_temp_path:  this.newVideo.thumbnail_temp_path,
                        duration:             this.newVideo.duration,
                        position:             this.newVideo.position || this.videoItems.length,
                        status:               this.newVideo.status ? 1 : 0,
                    })
                    .then(res => {
                        this.videoItems.push({
                            ...res.data.item,
                            editing: false, saving: false, deleting: false,
                            delete_video: false, delete_thumbnail: false,
                            video_preview: null, video_temp_path: null, uploading_video: false,
                            thumbnail_preview: null, thumbnail_temp_path: null, uploading_thumbnail: false,
                        });
                        this.showNewVideo = false;
                        this.resetNewVideo();
                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    })
                    .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to add video' }))
                    .finally(() => this.videoSaving = false);
                },

                ajaxUpdateVideoItem(item) {
                    if (!item.title) { alert('Title required'); return; }

                    item.saving = true;
                    this.$axios.post(this.routes.items_update.replace(':itemId', item.id), {
                        title:                item.title,
                        video_temp_path:      item.video_temp_path,
                        delete_video:         item.delete_video ? 1 : 0,
                        thumbnail_temp_path:  item.thumbnail_temp_path,
                        delete_thumbnail:     item.delete_thumbnail ? 1 : 0,
                        duration:             item.duration,
                        position:             item.position,
                        status:               item.status ? 1 : 0,
                        _method:              'PUT',
                    })
                    .then(res => {
                        // Update local item with server response
                        const updated = res.data.item;
                        item.video_url          = updated.video_url;
                        item.video_path         = updated.video_path;
                        item.thumbnail_full_url = updated.thumbnail_full_url;
                        item.thumbnail_path     = updated.thumbnail_path;
                        item.editing            = false;
                        item.delete_video       = false;
                        item.delete_thumbnail   = false;
                        item.video_temp_path    = null;
                        item.video_preview      = null;
                        item.thumbnail_temp_path = null;
                        item.thumbnail_preview  = null;
                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    })
                    .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to update video' }))
                    .finally(() => item.saving = false);
                },

                ajaxRemoveVideoItem(itemId, index) {
                    if (!confirm('Remove this video?')) return;
                    this.videoItems[index].deleting = true;
                    this.$axios.delete(this.routes.items_remove.replace(':itemId', itemId))
                        .then(res => {
                            this.videoItems.splice(index, 1);
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => {
                            this.videoItems[index].deleting = false;
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to remove video' });
                        });
                },

                // ── File Uploads ─────────────────────────────────────
                getToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content
                        || document.querySelector('input[name="_token"]')?.value;
                },

                uploadFile(file, type, onSuccess, onError, onStart) {
                    onStart?.();
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('type', type);
                    formData.append('_token', this.getToken());

                    fetch("{{ route('admin.study_materials.temp_upload.store') }}", { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) onSuccess(data);
                            else onError();
                        })
                        .catch(onError);
                },

                // Existing item video select
                onVideoSelect(event, item) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.uploadFile(file, 'video',
                        data => {
                            item.video_temp_path = data.path;
                            item.video_preview   = data.url;
                            item.video_name      = data.filename;
                            item.uploading_video = false;
                        },
                        () => { alert('Upload failed'); item.uploading_video = false; },
                        () => { item.uploading_video = true; }
                    );
                },

                onThumbnailSelect(event, item) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.uploadFile(file, 'thumbnail',
                        data => {
                            item.thumbnail_temp_path = data.path;
                            item.thumbnail_preview   = data.url;
                            item.uploading_thumbnail = false;
                        },
                        () => { alert('Upload failed'); item.uploading_thumbnail = false; },
                        () => { item.uploading_thumbnail = true; }
                    );
                },

                // New item video select
                onNewVideoSelect(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.uploadFile(file, 'video',
                        data => {
                            this.newVideo.video_temp_path = data.path;
                            this.newVideo.video_preview   = data.url;
                            this.newVideo.video_name      = data.filename;
                            this.newVideo.uploading_video = false;
                        },
                        () => { alert('Upload failed'); this.newVideo.uploading_video = false; },
                        () => { this.newVideo.uploading_video = true; }
                    );
                },

                onNewThumbnailSelect(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.uploadFile(file, 'thumbnail',
                        data => {
                            this.newVideo.thumbnail_temp_path = data.path;
                            this.newVideo.thumbnail_preview   = data.url;
                            this.newVideo.uploading_thumbnail = false;
                        },
                        () => { alert('Upload failed'); this.newVideo.uploading_thumbnail = false; },
                        () => { this.newVideo.uploading_thumbnail = true; }
                    );
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