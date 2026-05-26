<x-admin::layouts>
    <x-slot:title>Discussion Detail</x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Discussion Detail
        </p>
        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.discussions.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                @lang('admin::app.account.edit.back-btn')
            </a>
            <div class="flex gap-2">
                <a href="{{ route('admin.discussions.edit', $discussion->id) }}" class="primary-button">
                    Edit Discussion
                </a>
            </div>
        </div>
    </div>

    <v-discussion-show
        :discussion="{{ json_encode($discussion) }}"
        likes-url="{{ route('admin.discussions.ajax-likes', $discussion->id) }}"
        comments-url="{{ route('admin.discussions.ajax-comments', $discussion->id) }}"
        update-comment-url="{{ route('admin.discussions.update-comment', [$discussion->id, '__ID__']) }}"
        delete-comment-url="{{ route('admin.discussions.delete-comment', [$discussion->id, '__ID__']) }}"
        block-url="{{ route('admin.discussions.block-customer') }}"
        unblock-url="{{ route('admin.discussions.unblock-customer', '__ID__') }}"
        mark-correct-url="{{ route('admin.discussions.mark-correct', $discussion->id) }}"
    ></v-discussion-show>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-discussion-show-template">
            <div class="flex mt-3.5 gap-4 max-xl:flex-col">

                <!-- LEFT: Discussion info + Stats -->
                <div class="flex flex-col gap-4 flex-1">

                    <!-- Discussion Card -->
                    <div class="box-shadow rounded bg-white p-5 dark:bg-gray-900">
                        <!-- Hashtags -->
                        <div v-if="discussion.hashtags?.length" class="flex flex-wrap gap-2 mb-3">
                            <span
                                v-for="tag in discussion.hashtags"
                                :key="tag.id"
                                class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-50 text-indigo-600"
                            >
                                #@{{ tag.name }}
                            </span>
                        </div>

                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">@{{ discussion.title }}</h2>

                        <p v-if="discussion.description" class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                            @{{ discussion.description }}
                        </p>

                        <!-- Taxonomy -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span v-if="discussion.board"   class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">@{{ discussion.board }}</span>
                            <span v-if="discussion.class"   class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">Class @{{ discussion.class }}</span>
                            <span v-if="discussion.subject" class="text-xs px-2.5 py-1 rounded-full bg-blue-50 text-blue-600">@{{ discussion.subject }}</span>
                            <span v-if="discussion.book"    class="text-xs px-2.5 py-1 rounded-full bg-green-50 text-green-600">@{{ discussion.book }}</span>
                            <span v-if="discussion.chapter" class="text-xs px-2.5 py-1 rounded-full bg-orange-50 text-orange-600">@{{ discussion.chapter }}</span>
                        </div>

                        <!-- Creator -->
                        <div class="flex items-center gap-3 pb-4 border-b dark:border-gray-700">
                            <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                                @{{ creatorInitial }}
                            </div>
                            <div>
                                <p class="font-semibold text-sm text-gray-800 dark:text-white">@{{ creatorName }}</p>
                                <p class="text-xs text-gray-400">@{{ formatDate(discussion.created_at) }}</p>
                            </div>
                            <span
                                :class="discussion.status ? 'badge-success' : 'badge-danger'"
                                class="badge badge-md ml-auto"
                            >
                                @{{ discussion.status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <!-- Stats row -->
                        <div class="flex items-center gap-6 pt-4">
                            <!-- Likes — clickable -->
                            <button
                                @click="openLikes"
                                class="flex flex-col items-center text-gray-600 dark:text-gray-400 hover:text-red-500 transition-colors"
                            >
                                <span class="text-2xl font-bold">@{{ discussion.likes_count }}</span>
                                <span class="text-xs font-medium">❤️ Likes</span>
                            </button>

                            <div class="flex flex-col items-center text-gray-600 dark:text-gray-400">
                                <span class="text-2xl font-bold">@{{ discussion.comments_count }}</span>
                                <span class="text-xs font-medium">💬 Comments</span>
                            </div>

                            <!-- <div class="flex flex-col items-center text-gray-600 dark:text-gray-400">
                                <span class="text-2xl font-bold">@{{ discussion.views_count }}</span>
                                <span class="text-xs font-medium">👁 Views</span>
                            </div> -->
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="box-shadow rounded bg-white dark:bg-gray-900">
                        <!-- Header + Search -->
                        <div class="flex items-center justify-between gap-3 p-4 border-b dark:border-gray-700">
                            <p class="font-semibold text-gray-800 dark:text-white">Comments</p>

                            <div class="flex items-center gap-2">
                                <input
                                    v-model="commentSearch"
                                    @input="onSearchInput"
                                    type="text"
                                    placeholder="Search comments…"
                                    class="border rounded-lg px-3 py-1.5 text-sm w-64 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-400"
                                />

                                <button
                                    @click="saveCorrectAnswers"
                                    :disabled="savingCorrectAnswers"
                                    class="primary-button"
                                >
                                    @{{ savingCorrectAnswers ? 'Saving...' : 'Save Correct' }}
                                </button>
                            </div>
                        </div>

                        <!-- Comments List -->
                        <div class="divide-y dark:divide-gray-700">
                            <div v-if="commentsLoading && comments.length === 0" class="p-8 text-center text-gray-400">
                                Loading…
                            </div>

                            <div v-else-if="comments.length === 0" class="p-8 text-center text-gray-400">
                                No comments found.
                            </div>

                            <div
                                v-for="comment in comments"
                                :key="comment.id"
                                class="p-4 transition-colors"
                                :class="comment.deleted_at ? 'bg-red-50 dark:bg-red-900/20' : ''"
                            >
                                <div class="flex items-start gap-3">
                                    <!-- Avatar -->
                                    <div class="w-9 h-9 rounded-full bg-gray-400 flex items-center justify-center text-white text-sm font-bold flex-shrink-0 overflow-hidden">
                                        <img v-if="comment.customer?.image" :src="comment.customer.image" class="w-full h-full object-cover" />
                                        <span v-else>@{{ (comment.customer?.first_name || 'D')[0].toUpperCase() }}</span>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <!-- Customer info row -->
                                        <div class="flex items-center flex-wrap gap-2 mb-1">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                @{{ customerName(comment.customer) }}
                                            </p>
                                            <!-- Premium badge -->
                                            <span
                                                v-if="comment.customer?.is_premium_user"
                                                class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-bold"
                                            >
                                                Premium
                                            </span>

                                            <!-- Correct Answer badge -->
                                            <span
                                                v-if="comment.is_correct"
                                                class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold"
                                            >
                                                Correct Answer
                                            </span>

                                            <!-- Blocked badge -->
                                            <span
                                                v-if="comment.customer?.is_blocked"
                                                class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold"
                                            >
                                                🚫 Blocked
                                            </span>
                                            <!-- Deleted badge -->
                                            <span
                                                v-if="comment.deleted_at"
                                                class="text-xs bg-gray-200 text-gray-500 px-2 py-0.5 rounded-full"
                                            >
                                                Deleted by @{{ comment.deleted_by_type }}
                                            </span>
                                            <!-- Edited badge -->
                                            <span
                                                v-if="comment.edited_at && !comment.deleted_at"
                                                class="text-xs text-gray-400"
                                            >
                                                (edited)
                                            </span>
                                            <span class="text-xs text-gray-400 ml-auto">@{{ formatDate(comment.created_at) }}</span>
                                        </div>

                                        <!-- Body / Edit input -->
                                        <div v-if="editingId === comment.id">
                                            <textarea
                                                v-model="editText"
                                                rows="3"
                                                class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-400"
                                            />
                                            <div class="flex gap-2 mt-2">
                                                <button @click="saveEdit(comment.id)" class="primary-button !px-3 !py-1.5 text-sm">Save</button>
                                                <button @click="cancelEdit" class="transparent-button !px-3 !py-1.5 text-sm hover:bg-gray-100">Cancel</button>
                                            </div>
                                        </div>
                                        <p v-else class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                            @{{ comment.body }}
                                        </p>

                                        <!-- Like count -->
                                        <p class="text-xs text-gray-400 mt-1">❤️ @{{ comment.likes_count }} likes</p>
                                    </div>

                                    
                                    <!-- Action buttons -->
                                    <div v-if="!comment.deleted_at" class="flex items-center gap-1.5 flex-shrink-0">
                                        <!-- Correct Answer Checkbox -->
                                        <div class="flex items-center pt-1">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    :value="comment.id"
                                                    v-model="selectedCorrectComments"
                                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                                >
    
                                                <span class="text-xs font-semibold text-green-600">
                                                    Correct
                                                </span>
                                            </label>
                                        </div>

                                        <!-- Edit -->
                                        <button
                                            @click="startEdit(comment)"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-blue-50 text-blue-500"
                                            title="Edit comment"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                        </button>

                                        <!-- Delete -->
                                        <button
                                            @click="deleteComment(comment)"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-50 text-red-500"
                                            title="Delete comment"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                            </svg>
                                        </button>

                                        <!-- Block/Unblock -->
                                        <button
                                            v-if="comment.customer"
                                            @click="comment.customer.is_blocked ? unblockCustomer(comment) : blockCustomer(comment)"
                                            :class="comment.customer.is_blocked
                                                ? 'hover:bg-green-50 text-green-500'
                                                : 'hover:bg-orange-50 text-orange-500'"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg"
                                            :title="comment.customer.is_blocked ? 'Unblock user' : 'Block user from discussions'"
                                        >
                                            <span class="text-sm">@{{ comment.customer.is_blocked ? '✅' : '🚫' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="commentsMeta" class="flex items-center justify-between px-4 py-3 border-t dark:border-gray-700">
                            <p class="text-xs text-gray-500">
                                Showing @{{ commentsMeta.from }}–@{{ commentsMeta.to }} of @{{ commentsMeta.total }}
                            </p>
                            <div class="flex gap-1">
                                <button
                                    v-for="page in visiblePages"
                                    :key="page"
                                    @click="loadComments(page)"
                                    :class="page === commentsPage
                                        ? 'bg-indigo-500 text-white'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'"
                                    class="w-8 h-8 rounded-lg text-xs font-semibold flex items-center justify-center transition-colors"
                                >
                                    @{{ page }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== Likes Modal ====== -->
            <transition name="fade">
                <div v-if="showLikesModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" @click.self="showLikesModal = false">
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] flex flex-col">
                        <!-- Header -->
                        <div class="flex items-center justify-between p-5 border-b dark:border-gray-700">
                            <p class="font-bold text-gray-800 dark:text-white text-lg">❤️ Likes (@{{ likesTotal }})</p>
                            <button @click="showLikesModal = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
                        </div>

                        <!-- List -->
                        <div class="flex-1 overflow-y-auto divide-y dark:divide-gray-700">
                            <div v-if="likesLoading" class="p-8 text-center text-gray-400">Loading…</div>
                            <div
                                v-for="like in likesList"
                                :key="like.id"
                                class="flex items-center gap-3 px-5 py-3"
                            >
                                <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold flex-shrink-0 overflow-hidden">
                                    <img v-if="like.customer?.image" :src="like.customer.image" class="w-full h-full object-cover" />
                                    <span v-else>@{{ (like.customer?.first_name || 'U')[0].toUpperCase() }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-gray-800 dark:text-white truncate">
                                        @{{ customerName(like.customer) }}
                                    </p>
                                    <p class="text-xs text-gray-400 truncate">@{{ like.customer?.email }}</p>
                                </div>
                                <span v-if="like.customer?.is_premium_user" class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-bold flex-shrink-0">✨ Premium</span>
                                <span v-if="like.customer?.is_blocked" class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold flex-shrink-0">🚫 Blocked</span>
                            </div>
                        </div>

                        <!-- Likes pagination -->
                        <div v-if="likesMeta && likesMeta.last_page > 1" class="flex items-center justify-center gap-2 p-4 border-t dark:border-gray-700">
                            <button
                                @click="loadLikes(likesPage - 1)"
                                :disabled="likesPage === 1"
                                class="px-3 py-1.5 rounded-lg bg-gray-100 text-xs font-semibold disabled:opacity-40 hover:bg-gray-200 dark:bg-gray-800"
                            >← Prev</button>
                            <span class="text-xs text-gray-500">@{{ likesPage }} / @{{ likesMeta.last_page }}</span>
                            <button
                                @click="loadLikes(likesPage + 1)"
                                :disabled="likesPage === likesMeta.last_page"
                                class="px-3 py-1.5 rounded-lg bg-gray-100 text-xs font-semibold disabled:opacity-40 hover:bg-gray-200 dark:bg-gray-800"
                            >Next →</button>
                        </div>
                    </div>
                </div>
            </transition>
        </script>

        <style>
            .fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
            .fade-enter-from, .fade-leave-to { opacity: 0; }
        </style>

        <script type="module">
            app.component('v-discussion-show', {
                template: '#v-discussion-show-template',

                props: {
                    discussion:       { type: Object, required: true },
                    likesUrl:         String,
                    commentsUrl:      String,
                    updateCommentUrl: String,
                    deleteCommentUrl: String,
                    blockUrl:         String,
                    unblockUrl:       String,
                    markCorrectUrl:   String,
                },

                data() {
                    return {
                        // Likes
                        showLikesModal: false,
                        likesList:      [],
                        likesLoading:   false,
                        likesPage:      1,
                        likesMeta:      null,
                        likesTotal:     this.discussion.likes_count ?? 0,

                        // Comments
                        comments:       [],
                        commentsLoading: false,
                        commentsPage:   1,
                        commentsMeta:   null,
                        commentSearch:  '',
                        searchTimer:    null,

                        // Edit
                        editingId: null,
                        editText:  '',

                        selectedCorrectComments: [],
                        savingCorrectAnswers: false,
                    };
                },

                computed: {
                    creatorName() {
                        const ct = this.discussion.creator_type || '';
                        if (ct.includes('Admin')) return 'Study Rankers (Admin)';
                        const c = this.discussion.creator;
                        return c ? `${c.first_name || ''} ${c.last_name || ''}`.trim() || 'Student' : 'Unknown';
                    },
                    creatorInitial() { return this.creatorName[0]?.toUpperCase() || 'S'; },
                    visiblePages() {
                        if (!this.commentsMeta) return [];
                        const last  = this.commentsMeta.last_page;
                        const cur   = this.commentsPage;
                        const pages = [];
                        for (let i = Math.max(1, cur - 2); i <= Math.min(last, cur + 2); i++) {
                            pages.push(i);
                        }
                        return pages;
                    },
                },

                mounted() {
                    this.loadComments(1);
                },

                methods: {
                    formatDate(d) {
                        if (!d) return '';
                        return new Date(d).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
                    },

                    customerName(c) {
                        if (!c) return 'Unknown';
                        return `${c.first_name || ''} ${c.last_name || ''}`.trim() || 'Student';
                    },

                    // ── Likes ────────────────────────────────────────────────

                    async openLikes() {
                        this.showLikesModal = true;
                        if (this.likesList.length === 0) await this.loadLikes(1);
                    },

                    async loadLikes(page = 1) {
                        this.likesLoading = true;
                        try {
                            const res       = await axios.get(this.likesUrl, { params: { page } });
                            this.likesList  = res.data.data;
                            this.likesMeta  = res.data.meta;
                            this.likesTotal = res.data.meta?.total ?? this.likesTotal;
                            this.likesPage  = page;
                        } finally {
                            this.likesLoading = false;
                        }
                    },

                    // ── Comments ─────────────────────────────────────────────

                    async loadComments(page = 1) {
                        this.commentsLoading = true;
                        try {
                            const res         = await axios.get(this.commentsUrl, {
                                params: { page, search: this.commentSearch }
                            });
                            this.comments     = res.data.data;
                            this.commentsMeta = res.data.meta;
                            this.commentsPage = page;

                            this.selectedCorrectComments = this.comments
                                .filter(comment => comment.is_correct)
                                .map(comment => comment.id);
                        } finally {
                            this.commentsLoading = false;
                        }
                    },

                    async saveCorrectAnswers() {
                        this.savingCorrectAnswers = true;

                        try {
                            await axios.post(this.markCorrectUrl, {
                                comment_ids: this.selectedCorrectComments
                            });

                            this.comments = this.comments.map(comment => ({
                                ...comment,
                                is_correct: this.selectedCorrectComments.includes(comment.id)
                            }));

                            alert('Correct answers updated successfully.');
                        } catch (err) {
                            alert(err.response?.data?.message || 'Failed to save.');
                        } finally {
                            this.savingCorrectAnswers = false;
                        }
                    },

                    onSearchInput() {
                        clearTimeout(this.searchTimer);
                        this.searchTimer = setTimeout(() => this.loadComments(1), 400);
                    },

                    // ── Edit ─────────────────────────────────────────────────

                    startEdit(comment) {
                        this.editingId = comment.id;
                        this.editText  = comment.body;
                    },

                    cancelEdit() { this.editingId = null; this.editText = ''; },

                    async saveEdit(commentId) {
                        if (!this.editText.trim()) return;
                        try {
                            const url = this.updateCommentUrl.replace('__ID__', commentId);
                            const res = await axios.patch(url, { body: this.editText });
                            const idx = this.comments.findIndex(c => c.id === commentId);
                            if (idx !== -1) this.comments[idx] = { ...this.comments[idx], body: this.editText, edited_at: new Date().toISOString() };
                            this.cancelEdit();
                        } catch (err) {
                            alert(err.response?.data?.message || 'Failed to update.');
                        }
                    },

                    // ── Delete ────────────────────────────────────────────────

                    async deleteComment(comment) {
                        if (!confirm('Delete this comment? This action uses soft delete.')) return;
                        try {
                            const url = this.deleteCommentUrl.replace('__ID__', comment.id);
                            await axios.delete(url);
                            const idx = this.comments.findIndex(c => c.id === comment.id);
                            if (idx !== -1) {
                                this.comments[idx].deleted_at      = new Date().toISOString();
                                this.comments[idx].deleted_by_type = 'admin';
                            }
                        } catch (err) {
                            alert(err.response?.data?.message || 'Failed to delete.');
                        }
                    },

                    // ── Block / Unblock ───────────────────────────────────────

                    async blockCustomer(comment) {
                        const reason = prompt(`Reason for blocking ${this.customerName(comment.customer)}? (optional)`);
                        if (reason === null) return; // cancelled
                        try {
                            await axios.post(this.blockUrl, {
                                customer_id: comment.customer_id,
                                reason: reason || ''
                            });
                            comment.customer.is_blocked = true;
                            alert('Customer blocked from all discussions.');
                        } catch (err) {
                            alert(err.response?.data?.message || 'Failed to block.');
                        }
                    },

                    async unblockCustomer(comment) {
                        if (!confirm(`Unblock ${this.customerName(comment.customer)}?`)) return;
                        try {
                            const url = this.unblockUrl.replace('__ID__', comment.customer_id);
                            await axios.delete(url);
                            comment.customer.is_blocked = false;
                        } catch (err) {
                            alert(err.response?.data?.message || 'Failed to unblock.');
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>