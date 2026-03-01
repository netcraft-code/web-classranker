<x-admin::layouts>
    <x-slot:title>Edit PDF</x-slot>

    <x-admin::form
        :action="route('admin.study_materials.pdfs.update', $pdf->id)"
        method="PUT"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">Edit PDF</p>
            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.study_materials.pdfs.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">Back</a>
                <button type="submit" class="primary-button">Update</button>
            </div>
        </div>

        <v-edit-pdfs
            :initial-assignments="{{ json_encode($formattedAssignments) }}"
            :initial-pdf-items="{{ json_encode($formattedPdfItems) }}"
            :boards="{{ json_encode($boards) }}"
            :pdf-id="{{ $pdf->id }}"
            :routes="{{ json_encode([
                'assignments_add'    => route('admin.study_materials.pdfs.assignments.add',    $pdf->id),
                'assignments_remove' => route('admin.study_materials.pdfs.assignments.remove', [$pdf->id, ':assignmentId']),
                'items_add'          => route('admin.study_materials.pdfs.items.add',          $pdf->id),
                'items_update'       => route('admin.study_materials.pdfs.items.update',       [$pdf->id, ':itemId']),
                'items_remove'       => route('admin.study_materials.pdfs.items.remove',       [$pdf->id, ':itemId']),
            ]) }}"
        ></v-edit-pdfs>
    </x-admin::form>

    @pushOnce('scripts')
    <script type="text/x-template" id="v-edit-pdfs-template">
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
                        <x-admin::form.control-group.label class="required">Title</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="title" rules="required"
                            v-model="title" :value="old('title', $pdf->title)" />
                        <x-admin::form.control-group.error control-name="title" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Slug</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="slug"
                            v-model="slug" :value="old('slug', $pdf->slug)" readonly />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">Short Title</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="short_title" rules="required"
                            v-model="shortTitle" :value="old('short_title', $pdf->short_title)" />
                        <x-admin::form.control-group.error control-name="short_title" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Top Description</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="top_description"
                            class="top_description" name="top_description"
                            :value="old('top_description', $pdf->top_description)" :tinymce="true" />
                    </x-admin::form.control-group>
                </div>

                <!-- ── PDF ITEMS ── -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">PDF Files</p>

                    <!-- Existing items -->
                    <div v-for="(item, index) in pdfItems" :key="item.id"
                        class="mb-4 p-4 border rounded bg-gray-50 dark:bg-gray-800">

                        <div class="flex items-center justify-between mb-3">
                            <p class="font-semibold text-gray-700 dark:text-white">
                                PDF @{{ index + 1 }}
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
                                    @click="ajaxUpdatePdfItem(item)"
                                    :disabled="item.saving"
                                    class="text-green-600 hover:text-green-800 text-sm font-semibold">
                                    @{{ item.saving ? 'Saving...' : 'Save' }}
                                </button>
                                <button type="button"
                                    @click="ajaxRemovePdfItem(item.id, index)"
                                    :disabled="item.deleting"
                                    class="text-red-600 hover:text-red-800 text-sm font-semibold">
                                    @{{ item.deleting ? 'Removing...' : 'Remove' }}
                                </button>
                            </div>
                        </div>

                        <!-- Read-only view -->
                        <template v-if="!item.editing">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">@{{ item.title }}</p>
                            <div v-if="item.pdf_url" class="flex items-center gap-3 p-3 bg-white border rounded mb-2">
                                <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM12 18H8v-1h4v1zm4-3H8v-1h8v1zm0-3H8v-1h8v1z"/>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 truncate">@{{ item.pdf_name || 'PDF File' }}</p>
                                </div>
                                <a :href="item.pdf_url" target="_blank" class="text-xs text-blue-600 hover:underline flex-shrink-0">Preview</a>
                            </div>
                            <p v-else class="text-xs text-gray-400 mb-2">No PDF uploaded</p>
                            <div class="flex gap-4 text-xs text-gray-500">
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

                            <!-- PDF File -->
                            <div class="mb-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">PDF File</label>

                                <!-- Current PDF -->
                                <div v-if="item.pdf_url && !item.pdf_temp_path && !item.delete_pdf"
                                    class="mb-2 flex items-center gap-3 p-3 bg-white border rounded">
                                    <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM12 18H8v-1h4v1zm4-3H8v-1h8v1zm0-3H8v-1h8v1z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-700 truncate">@{{ item.pdf_name || 'Current PDF' }}</p>
                                        <p class="text-xs text-gray-400">Upload new PDF to replace</p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <a :href="item.pdf_url" target="_blank" class="text-xs text-blue-600 hover:underline">Preview</a>
                                        <button type="button" @click="item.delete_pdf = true"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium">🗑 Delete</button>
                                    </div>
                                </div>

                                <!-- Delete marked -->
                                <div v-if="item.delete_pdf && !item.pdf_temp_path"
                                    class="mb-2 p-2 bg-red-50 border border-red-200 rounded flex items-center justify-between">
                                    <p class="text-xs text-red-600">⚠ PDF will be deleted on save</p>
                                    <button type="button" @click="item.delete_pdf = false"
                                        class="text-xs text-blue-600 hover:underline">Undo</button>
                                </div>

                                <!-- Upload input -->
                                <input type="file" accept="application/pdf"
                                    @change="onPdfSelect($event, item)"
                                    class="w-full border rounded p-2 text-sm bg-white cursor-pointer">

                                <!-- Uploading -->
                                <div v-if="item.uploading" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Uploading PDF...
                                </div>

                                <!-- New PDF preview -->
                                <div v-if="item.pdf_temp_path && !item.uploading"
                                    class="mt-2 flex items-center gap-3 p-3 bg-white border border-green-200 rounded">
                                    <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM12 18H8v-1h4v1zm4-3H8v-1h8v1zm0-3H8v-1h8v1z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-700 truncate">@{{ item.new_pdf_name }}</p>
                                        <p class="text-xs text-green-600">✓ New PDF — will replace on save</p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <a :href="item.new_pdf_url" target="_blank" class="text-xs text-blue-600 hover:underline">Preview</a>
                                        <button type="button" @click="item.pdf_temp_path = null; item.new_pdf_url = null; item.new_pdf_name = null"
                                            class="text-xs text-red-500 hover:underline">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Position + Status -->
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Position</label>
                                    <input type="number" v-model="item.position" class="w-full border rounded p-2 text-sm">
                                </div>
                                <div class="flex items-end pb-2">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" v-model="item.status" class="h-4 w-4 rounded border-gray-300">
                                        <label class="text-sm text-gray-700 dark:text-gray-300">Active</label>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- New PDF form -->
                    <div v-if="showNewPdf" class="mb-4 p-4 border-2 border-dashed border-blue-300 rounded bg-blue-50 dark:bg-gray-800">
                        <p class="font-semibold text-blue-700 dark:text-white mb-3">New PDF</p>

                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" v-model="newPdf.title" class="w-full border rounded p-2 text-sm" placeholder="PDF title">
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                PDF File <span class="text-red-500">*</span>
                            </label>
                            <input type="file" accept="application/pdf"
                                @change="onNewPdfSelect($event)"
                                class="w-full border rounded p-2 text-sm bg-white cursor-pointer">

                            <div v-if="newPdf.uploading" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                Uploading PDF...
                            </div>

                            <div v-if="newPdf.pdf_temp_path && !newPdf.uploading"
                                class="mt-2 flex items-center gap-3 p-3 bg-white border border-green-200 rounded">
                                <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM12 18H8v-1h4v1zm4-3H8v-1h8v1zm0-3H8v-1h8v1z"/>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 truncate">@{{ newPdf.pdf_name }}</p>
                                    <p class="text-xs text-green-600">✓ Ready to save</p>
                                </div>
                                <a :href="newPdf.pdf_url" target="_blank" class="text-xs text-blue-600 hover:underline flex-shrink-0">Preview</a>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Position</label>
                                <input type="number" v-model="newPdf.position" class="w-full border rounded p-2 text-sm">
                            </div>
                            <div class="flex items-end pb-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" v-model="newPdf.status" class="h-4 w-4 rounded border-gray-300">
                                    <label class="text-sm text-gray-700 dark:text-gray-300">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" @click="ajaxAddPdfItem" :disabled="pdfSaving" class="primary-button text-sm">
                                @{{ pdfSaving ? 'Saving...' : 'Save PDF' }}
                            </button>
                            <button type="button" @click="showNewPdf = false; resetNewPdf()" class="transparent-button text-sm">Cancel</button>
                        </div>
                    </div>

                    <button v-if="!showNewPdf" type="button" @click="showNewPdf = true" class="secondary-button">
                        + Add PDF
                    </button>
                </div>

                <!-- ── BOTTOM DESCRIPTION ── -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Bottom Description</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="bottom_description"
                            class="bottom_description" name="bottom_description"
                            :value="old('bottom_description', $pdf->bottom_description)" :tinymce="true" />
                    </x-admin::form.control-group>
                </div>

                <!-- ── SEO ── -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">SEO Meta Tags</p>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Title</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="meta_title"
                            :value="old('meta_title', $pdf->meta_title)" />
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Description</x-admin::form.control-group.label>
                        <textarea name="meta_description" rows="3" class="w-full border rounded p-2 text-sm">{{ old('meta_description', $pdf->meta_description) }}</textarea>
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Keywords</x-admin::form.control-group.label>
                        <textarea name="meta_keywords" rows="2" class="w-full border rounded p-2 text-sm">{{ old('meta_keywords', $pdf->meta_keywords) }}</textarea>
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
                                :checked="(boolean) old('status', $pdf->status)" />
                        </x-admin::form.control-group>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>Is Premium</x-admin::form.control-group.label>
                            <input type="checkbox" name="is_premium" value="1"
                                class="h-4 w-4 rounded border-gray-300"
                                {{ old('is_premium', $pdf->is_premium) ? 'checked' : '' }}>
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-edit-pdfs', {
            template: '#v-edit-pdfs-template',

            props: {
                initialAssignments: { type: Array, default: () => [] },
                initialPdfItems:    { type: Array, default: () => [] },
                boards:             { type: Array, default: () => [] },
                pdfId:              { type: Number, required: true },
                routes:             { type: Object, required: true },
            },

            data() {
                return {
                    title:      '{{ old('title', $pdf->title) }}',
                    slug:       '{{ old('slug', $pdf->slug) }}',
                    shortTitle: '{{ old('short_title', $pdf->short_title) }}',

                    selectedBoardId:   '', selectedGradeId:   '',
                    selectedSubjectId: '', selectedBookId:    '',
                    selectedChapterId: '',
                    filteredGrades:    [], filteredSubjects:  [],
                    filteredBooks:     [], filteredChapters:  [],

                    assignmentLoading: false,
                    assignments: this.initialAssignments,

                    pdfItems: this.initialPdfItems.map(item => ({
                        ...item,
                        editing:       false,
                        saving:        false,
                        deleting:      false,
                        delete_pdf:    false,
                        pdf_temp_path: null,
                        new_pdf_url:   null,
                        new_pdf_name:  null,
                        uploading:     false,
                    })),

                    showNewPdf: false,
                    newPdf:     this.defaultNewPdf(),
                    pdfSaving:  false,
                };
            },

            watch: {
                title(val) { this.slug = this.slugify(val); },
            },

            methods: {
                defaultNewPdf() {
                    return {
                        title: '', position: 0, status: true,
                        pdf_temp_path: null, pdf_url: null, pdf_name: null, uploading: false,
                    };
                },

                resetNewPdf() { this.newPdf = this.defaultNewPdf(); },

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

                // ── PDF Items ────────────────────────────────────────
                startEditItem(item) {
                    item._snapshot = { title: item.title, position: item.position, status: item.status };
                    item.editing = true;
                },

                cancelEditItem(item) {
                    if (item._snapshot) {
                        item.title    = item._snapshot.title;
                        item.position = item._snapshot.position;
                        item.status   = item._snapshot.status;
                    }
                    item.editing      = false;
                    item.delete_pdf   = false;
                    item.pdf_temp_path = null;
                    item.new_pdf_url  = null;
                    item.new_pdf_name = null;
                },

                ajaxAddPdfItem() {
                    if (!this.newPdf.title)        { alert('Title required'); return; }
                    if (!this.newPdf.pdf_temp_path) { alert('PDF file required'); return; }

                    this.pdfSaving = true;
                    this.$axios.post(this.routes.items_add, {
                        title:         this.newPdf.title,
                        pdf_temp_path: this.newPdf.pdf_temp_path,
                        position:      this.newPdf.position || this.pdfItems.length,
                        status:        this.newPdf.status ? 1 : 0,
                    })
                    .then(res => {
                        this.pdfItems.push({
                            ...res.data.item,
                            editing: false, saving: false, deleting: false,
                            delete_pdf: false, pdf_temp_path: null,
                            new_pdf_url: null, new_pdf_name: null, uploading: false,
                        });
                        this.showNewPdf = false;
                        this.resetNewPdf();
                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    })
                    .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to add PDF' }))
                    .finally(() => this.pdfSaving = false);
                },

                ajaxUpdatePdfItem(item) {
                    if (!item.title) { alert('Title required'); return; }

                    item.saving = true;
                    this.$axios.post(this.routes.items_update.replace(':itemId', item.id), {
                        title:         item.title,
                        pdf_temp_path: item.pdf_temp_path,
                        delete_pdf:    item.delete_pdf ? 1 : 0,
                        position:      item.position,
                        status:        item.status ? 1 : 0,
                        _method:       'PUT',
                    })
                    .then(res => {
                        const updated       = res.data.item;
                        item.pdf_url        = updated.pdf_url;
                        item.pdf_path       = updated.pdf_path;
                        item.pdf_name       = updated.pdf_name;
                        item.editing        = false;
                        item.delete_pdf     = false;
                        item.pdf_temp_path  = null;
                        item.new_pdf_url    = null;
                        item.new_pdf_name   = null;
                        this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                    })
                    .catch(() => this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to update PDF' }))
                    .finally(() => item.saving = false);
                },

                ajaxRemovePdfItem(itemId, index) {
                    if (!confirm('Remove this PDF?')) return;
                    this.pdfItems[index].deleting = true;
                    this.$axios.delete(this.routes.items_remove.replace(':itemId', itemId))
                        .then(res => {
                            this.pdfItems.splice(index, 1);
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(() => {
                            this.pdfItems[index].deleting = false;
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to remove PDF' });
                        });
                },

                // ── File Uploads ─────────────────────────────────────
                getToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content
                        || document.querySelector('input[name="_token"]')?.value;
                },

                uploadPdf(file, onSuccess, onError, onStart) {
                    if (file.size > 50 * 1024 * 1024) {
                        alert('PDF too large. Max 50MB.'); return;
                    }
                    onStart?.();
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('type', 'pdf');
                    formData.append('_token', this.getToken());

                    fetch("{{ route('admin.study_materials.temp_upload.store') }}", {
                        method: 'POST', body: formData
                    })
                    .then(r => r.json())
                    .then(data => { if (data.success) onSuccess(data); else onError(); })
                    .catch(onError);
                },

                // Existing item PDF select
                onPdfSelect(event, item) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.uploadPdf(file,
                        data => {
                            item.pdf_temp_path = data.path;
                            item.new_pdf_url   = data.url;
                            item.new_pdf_name  = data.filename;
                            item.uploading     = false;
                            item.delete_pdf    = false;
                        },
                        () => { alert('Upload failed'); item.uploading = false; },
                        () => { item.uploading = true; }
                    );
                },

                // New PDF select
                onNewPdfSelect(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.uploadPdf(file,
                        data => {
                            this.newPdf.pdf_temp_path = data.path;
                            this.newPdf.pdf_url       = data.url;
                            this.newPdf.pdf_name      = data.filename;
                            this.newPdf.uploading     = false;
                        },
                        () => { alert('Upload failed'); this.newPdf.uploading = false; },
                        () => { this.newPdf.uploading = true; }
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