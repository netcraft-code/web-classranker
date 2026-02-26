<x-admin::layouts>
    <x-slot:title>Edit PDF</x-slot>

    <x-admin::form :action="route('admin.study_materials.pdfs.update', $pdf->id)">
        @method('PUT')

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
        ></v-edit-pdfs>

    </x-admin::form>

    @pushOnce('scripts')
    <script type="text/x-template" id="v-edit-pdfs-template">
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
                        <x-admin::form.control-group.control type="text" name="title" rules="required"
                            v-model="title" :value="old('title', $pdf->title)" placeholder="Enter title" />
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
                            v-model="shortTitle" :value="old('short_title', $pdf->short_title)" placeholder="Short title" />
                        <x-admin::form.control-group.error control-name="short_title" />
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Top Description</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="top_description"
                            class="top_description" name="top_description" :tinymce="true"
                            :value="old('top_description', $pdf->top_description)" />
                    </x-admin::form.control-group>
                </div>

                <!-- PDF ITEMS -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">PDF Files</p>

                    <div v-for="(item, index) in pdfItems" :key="'pdf-'+index"
                        class="mb-4 p-4 border rounded bg-gray-50 dark:bg-gray-800">

                        <div class="flex items-center justify-between mb-3">
                            <p class="font-semibold text-gray-700 dark:text-white">PDF @{{ index + 1 }}</p>
                            <button type="button" @click="removePdfItem(index)"
                                class="text-red-600 text-sm font-semibold">Remove</button>
                        </div>

                        <!-- Hidden tracking fields -->
                        <input type="hidden" :name="'pdf_items['+index+'][item_id]'" :value="item.item_id || ''">
                        <input type="hidden" :name="'pdf_items['+index+'][is_new]'"  :value="item.is_new ? '1' : '0'">

                        <!-- Title -->
                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" v-model="item.title"
                                :name="'pdf_items['+index+'][title]'"
                                class="w-full border rounded p-2 text-sm" required>
                        </div>

                        <!-- ── PDF FILE SECTION ── -->
                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                PDF File <span v-if="item.is_new" class="text-red-500">*</span>
                            </label>

                            <!-- Current PDF preview -->
                            <div v-if="item.pdf_url && !item.pdf_temp_path && !item.delete_pdf"
                                class="mb-2 flex items-center gap-3 p-3 bg-white border rounded">
                                <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM12 18H8v-1h4v1zm4-3H8v-1h8v1zm0-3H8v-1h8v1z"/>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-700 truncate">@{{ item.pdf_name || 'Current PDF' }}</p>
                                    <p class="text-xs text-gray-400">Upload new PDF to replace</p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <a :href="item.pdf_url" target="_blank"
                                        class="text-xs text-blue-600 hover:underline">Preview</a>
                                    <button type="button" @click="item.delete_pdf = true"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium">🗑 Delete</button>
                                </div>
                            </div>

                            <!-- Delete marked — undo option -->
                            <div v-if="item.delete_pdf && !item.pdf_temp_path"
                                class="mb-2 p-2 bg-red-50 border border-red-200 rounded flex items-center justify-between">
                                <p class="text-xs text-red-600">⚠ PDF will be deleted on save</p>
                                <button type="button" @click="item.delete_pdf = false"
                                    class="text-xs text-blue-600 hover:underline">Undo</button>
                            </div>

                            <!-- Upload input -->
                            <input type="file"
                                accept="application/pdf"
                                @change="onPdfSelect($event, index)"
                                class="w-full border rounded p-2 text-sm bg-white cursor-pointer">

                            <!-- Uploading spinner -->
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
                                    <p class="text-sm font-medium text-gray-700 truncate">@{{ item.new_pdf_name }}</p>
                                    <p class="text-xs text-green-600">✓ New PDF — will replace on save</p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <a :href="item.new_pdf_url" target="_blank"
                                        class="text-xs text-blue-600 hover:underline">Preview</a>
                                    <button type="button" @click="cancelPdfUpload(index)"
                                        class="text-xs text-red-500 hover:underline">Cancel</button>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden PDF fields -->
                        <input type="hidden" :name="'pdf_items['+index+'][pdf_path]'"      :value="item.pdf_path || ''">
                        <input type="hidden" :name="'pdf_items['+index+'][pdf_temp_path]'" :value="item.pdf_temp_path || ''">
                        <input type="hidden" :name="'pdf_items['+index+'][delete_pdf]'"    :value="item.delete_pdf ? '1' : '0'">

                        <!-- Position + Status -->
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Position</label>
                                <input type="number" v-model="item.position"
                                    :name="'pdf_items['+index+'][position]'"
                                    class="w-full border rounded p-2 text-sm">
                            </div>
                            <div class="flex items-end pb-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" v-model="item.status"
                                        :name="'pdf_items['+index+'][status]'"
                                        value="1" class="h-4 w-4 rounded border-gray-300">
                                    <label class="text-sm text-gray-700 dark:text-gray-300">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="addPdfItem" class="secondary-button">+ Add PDF</button>
                </div>

                <!-- BOTTOM DESCRIPTION -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Bottom Description</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="textarea" id="bottom_description"
                            class="bottom_description" name="bottom_description" :tinymce="true"
                            :value="old('bottom_description', $pdf->bottom_description)" />
                    </x-admin::form.control-group>
                </div>

                <!-- SEO -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">SEO Meta Tags</p>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Meta Title</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="meta_title"
                            :value="old('meta_title', $pdf->meta_title)" placeholder="SEO meta title" />
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

            <!-- Right Sidebar -->
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
            },

            data() {
                return {
                    title:      '{{ old('title', $pdf->title) }}',
                    slug:       '{{ old('slug', $pdf->slug) }}',
                    shortTitle: '{{ old('short_title', $pdf->short_title) }}',

                    selectedBoardId: '', selectedGradeId: '', selectedSubjectId: '',
                    selectedBookId: '', selectedChapterId: '',
                    filteredGrades: [], filteredSubjects: [], filteredBooks: [], filteredChapters: [],

                    assignments: this.initialAssignments,

                    pdfItems: this.initialPdfItems.map(item => ({
                        ...item,
                        delete_pdf:    false,
                        pdf_temp_path: null,
                        new_pdf_url:   null,
                        new_pdf_name:  null,
                        uploading:     false,
                    })),
                };
            },

            watch: {
                title(val) { this.slug = this.slugify(val); },
            },

            mounted() {
                this.$nextTick(() => this.initializeTinyMCE());
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

                addPdfItem() {
                    this.pdfItems.push({
                        item_id: null, is_new: true,
                        title: '', pdf_path: null, pdf_url: null, pdf_name: null,
                        delete_pdf: false, pdf_temp_path: null,
                        new_pdf_url: null, new_pdf_name: null,
                        position: this.pdfItems.length, status: true, uploading: false,
                    });
                },
                removePdfItem(index) { this.pdfItems.splice(index, 1); },

                cancelPdfUpload(index) {
                    this.pdfItems[index].pdf_temp_path = null;
                    this.pdfItems[index].new_pdf_url   = null;
                    this.pdfItems[index].new_pdf_name  = null;
                },

                getToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content
                        || document.querySelector('input[name="_token"]')?.value;
                },

                onPdfSelect(event, index) {
                    const file = event.target.files?.[0];
                    if (!file) return;

                    if (file.size > 50 * 1024 * 1024) {
                        alert('PDF too large. Max 50MB.'); event.target.value = ''; return;
                    }

                    this.pdfItems[index].uploading  = true;
                    this.pdfItems[index].delete_pdf = false; // cancel delete if new upload

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('type', 'pdf');
                    formData.append('_token', this.getToken());

                    fetch("{{ route('admin.study_materials.temp_upload.store') }}", {
                            method: 'POST', body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                this.pdfItems[index].pdf_temp_path = data.path;
                                this.pdfItems[index].new_pdf_url   = data.url;
                                this.pdfItems[index].new_pdf_name  = data.filename;
                            }
                            this.pdfItems[index].uploading = false;
                        })
                        .catch(() => {
                            alert('PDF upload failed');
                            this.pdfItems[index].uploading = false;
                        });
                },

                slugify(text) {
                    return text.toString().toLowerCase().trim()
                        .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '').replace(/\-\-+/g, '-');
                },

                initializeTinyMCE() {
                    if (typeof tinymce !== 'undefined') tinymce.remove();
                    this.$nextTick(() => {
                        if (typeof tinymce === 'undefined') return;
                        const config = {
                            menubar: false,
                            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code fullscreen',
                        };
                        tinymce.init({ selector: 'textarea.top_description',    height: 200, ...config });
                        tinymce.init({ selector: 'textarea.bottom_description', height: 200, ...config });
                    });
                },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>