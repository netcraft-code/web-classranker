<x-admin::layouts>
    <x-slot:title>Boards</x-slot>

    <v-boards>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Boards
            </p>

            <div class="flex items-center gap-x-2.5">
                <button type="button" class="primary-button">
                    Create Board
                </button>
            </div>
        </div>

        <x-admin::shimmer.datagrid />
    </v-boards>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-boards-template">

            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    Boards
                </p>

                <div class="flex items-center gap-x-2.5">
                    <button
                        type="button"
                        class="primary-button"
                        @click="isEditable=false; resetForm(); $refs.boardModal.toggle()"
                    >
                        Create Board
                    </button>
                </div>
            </div>

            <!-- DataGrid -->
            <x-admin::datagrid
                :src="route('admin.study_materials.subjects.boards.index')"
                ref="datagrid"
            >
                <template #body="{ isLoading, available, applied, selectAll, sort, performAction }">
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body />
                    </template>

                    <template v-else>
                        <div
                            v-for="record in available.records"
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                            :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                        >
                            <!-- ID -->
                            <p>@{{ record.id }}</p>

                            <!-- Name -->
                            <p>@{{ record.name }}</p>

                            <!-- Code -->
                            <p>@{{ record.code }}</p>

                            <!-- Title -->
                            <p>@{{ record.title }}</p>

                            <!-- Avatar -->
                            <span v-if="record.avatar" v-html="record.avatar">
                            </span>

                            <span v-else
                                class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-bold">
                                @{{ record.name?.charAt(0)?.toUpperCase() }}
                            </span>

                            <!-- Status -->
                            <p :class="[record.status ? 'label-active': 'label-info']">
                                @{{ record.status ? "@lang('admin::app.catalog.products.index.datagrid.active')" : "@lang('admin::app.catalog.products.index.datagrid.disable')" }}
                            </p>

                            <!-- Actions -->
                            <div class="flex justify-end">
                                <span
                                    class="cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                    :class="record.actions.find(a => a.index === 'edit')?.icon"
                                    @click="editModal(record.actions.find(a => a.index === 'edit')?.url)"
                                ></span>

                                <span
                                    class="cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                    :class="record.actions.find(a => a.index === 'delete')?.icon"
                                    @click="performAction(record.actions.find(a => a.index === 'delete'))"
                                ></span>
                            </div>
                        </div>
                    </template>
                </template>
            </x-admin::datagrid>

            <!-- Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, updateOrCreate)" ref="boardForm" enctype="multipart/form-data">
                    <x-admin::modal ref="boardModal">

                        <!-- Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @{{ isEditable ? 'Edit Board' : 'Create Board' }}
                            </p>
                        </x-slot>

                        <!-- Content -->
                        <x-slot:content>
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                                v-model="form.id"
                            />

                            <!-- Avatar Preview + Upload -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>Avatar</x-admin::form.control-group.label>

                                <!-- Current avatar preview -->
                                <div v-if="form.avatar_url || avatarPreview" class="mb-2">
                                    <img :src="avatarPreview || form.avatar_url"
                                        class="w-20 h-20 rounded-full object-cover border" />
                                </div>

                                <input
                                    type="file"
                                    name="avatar[]"
                                    accept="image/*"
                                    @change="onAvatarChange"
                                    class="w-full border rounded p-2 text-sm bg-white cursor-pointer"
                                />

                                <!-- Remove avatar checkbox — only on edit -->
                                <div v-if="isEditable && form.avatar_url && !avatarPreview" class="mt-1 flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        id="remove_avatar"
                                        name="remove_avatar"
                                        value="1"
                                        v-model="form.remove_avatar"
                                        class="h-4 w-4 rounded border-gray-300"
                                    >
                                    <label for="remove_avatar" class="text-xs text-red-500">Remove current avatar</label>
                                </div>
                            </x-admin::form.control-group>

                            <!-- Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">Name</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    rules="required"
                                    v-model="form.name"
                                    placeholder="e.g. CBSE" />
                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <!-- Code — auto generated -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>Code</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="code"
                                    v-model="form.code"
                                    placeholder="Auto-generated" />
                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>

                            <!-- Title -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>Title</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="title"
                                    v-model="form.title"
                                    placeholder="e.g. Central Board of Secondary Education" />
                                <x-admin::form.control-group.error control-name="title" />
                            </x-admin::form.control-group>

                            <!-- Status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">Status</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    value="1"
                                    ::checked="form.status" />
                            </x-admin::form.control-group>
                        </x-slot>

                        <!-- Footer -->
                        <x-slot:footer>
                            <x-admin::button
                                button-type="submit"
                                class="primary-button"
                                :title="'Save Board'"
                                ::loading="isLoading"
                                ::disabled="isLoading"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-boards', {
                template: '#v-boards-template',

                data() {
                    return {
                        isEditable:    false,
                        isLoading:     false,
                        avatarPreview: null,
                        form: {
                            id:           null,
                            name:         '',
                            code:         '',
                            title:        '',
                            status:       true,
                            avatar_url:   null,
                            remove_avatar: false,
                        },
                    };
                },

                computed: {
                    gridsCount() {
                        let count = this.$refs.datagrid.available.columns.length;

                        if (this.$refs.datagrid.available.actions.length)    ++count;

                        return count;
                    },
                },

                watch: {
                    'form.name'(val) {
                        // Sirf create mode mein auto-fill karo
                        if (! this.isEditable) {
                            this.form.code = this.slugify(val);
                        }
                    },
                },

                methods: {
                    resetForm() {
                        this.form = {
                            id: null,
                            name: '',
                            code: '',
                            title: '',
                            status: true,
                            avatar_url: null,
                            remove_avatar: false,
                        };

                        this.avatarPreview = null;
                    },

                    onAvatarChange(event) {
                        const file = event.target.files?.[0];

                        if (!file) return;

                        this.avatarPreview = URL.createObjectURL(file);

                        this.form.remove_avatar = false;
                    },

                    updateOrCreate(params, { resetForm, setErrors }) {
                        this.isLoading = true;

                        const formData = new FormData(this.$refs.boardForm);

                        if (this.form.id) {
                            formData.append('_method', 'PUT');
                        }

                        const url = this.form.id
                            ? `{{ route('admin.study_materials.subjects.boards.update', '') }}/${this.form.id}`
                            : `{{ route('admin.study_materials.subjects.boards.store') }}`;

                        this.$axios.post(url, formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        })
                        .then(response => {
                            this.isLoading = false;

                            this.$refs.boardModal.close();

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data.message
                            });

                            this.$refs.datagrid.get();

                            resetForm();

                            this.resetForm();
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response?.status === 422) {
                                setErrors(error.response.data.errors);
                            } else {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || 'Something went wrong'
                                });
                            }
                        });
                    },

                    editModal(url) {
                        if (!url) return;

                        this.isEditable    = true;

                        this.avatarPreview = null;

                        this.$axios.get(url)
                            .then(response => {
                                const data   = response.data;

                                this.form = {
                                    id:            data.id,
                                    name:          data.name,
                                    code:          data.code,
                                    title:         data.title        || '',
                                    status:        !!data.status,
                                    avatar_url:    data.avatar_url   || null,
                                    remove_avatar: false,
                                };

                                this.$refs.boardModal.toggle();
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || 'Failed to load board'
                                });
                            });
                    },

                    slugify(text) {
                        return text.toString().toLowerCase().trim()
                            .replace(/\s+/g, '-')
                            .replace(/[^\w\-]+/g, '')
                            .replace(/\-\-+/g, '-');
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>