<x-admin::layouts>
    <x-slot:title>
        Create Notification
    </x-slot>

    <x-admin::form
        :action="route('admin.notifications.store')"
        enctype="multipart/form-data"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Create Notification
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.notifications.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    Back
                </a>

                <button type="submit" class="primary-button">
                    Save Notification
                </button>
            </div>
        </div>

        <v-create-notification></v-create-notification>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-create-notification-template"
        >
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">

                <!-- LEFT -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <!-- Notification Information -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            Notification Information
                        </p>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                Title
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="title"
                                rules="required"
                                v-model="title"
                                :value="old('title')"
                                label="Title"
                                placeholder="Enter notification title"
                            />

                            <x-admin::form.control-group.error control-name="title" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Message
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                name="text"
                                rows="5"
                                v-model="text"
                                :value="old('text')"
                                label="Message"
                                placeholder="Notification message..."
                            />

                            <x-admin::form.control-group.error control-name="text" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- Redirect Configuration -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            Redirect Configuration
                        </p>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                Redirect Type
                            </x-admin::form.control-group.label>

                            <select
                                name="redirect_type"
                                v-model="redirectType"
                                class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="internal">
                                    Internal
                                </option>

                                <option value="external">
                                    External
                                </option>
                            </select>

                            <x-admin::form.control-group.error control-name="redirect_type" />
                        </x-admin::form.control-group>

                        <!-- Internal -->
                        <div v-if="redirectType === 'internal'">

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Path
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="path"
                                    v-model="path"
                                    :value="old('path')"
                                    label="Path"
                                    placeholder="/test-detail"
                                />

                                <x-admin::form.control-group.error control-name="path" />
                            </x-admin::form.control-group>

                        </div>

                        <!-- External -->
                        <div v-if="redirectType === 'external'">

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    URL
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="link"
                                    v-model="link"
                                    :value="old('link')"
                                    label="URL"
                                    placeholder="https://example.com"
                                />

                                <x-admin::form.control-group.error control-name="link" />
                            </x-admin::form.control-group>

                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">

                    <!-- Image -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                Notification Image
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <div class="flex flex-col gap-2">
                                <p class="text-xs text-gray-500">
                                    Upload image for push notification.
                                </p>

                                <x-admin::media.images name="image" />
                            </div>
                        </x-slot>
                    </x-admin::accordion>

                    <!-- Preview -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                Live Preview
                            </p>
                        </x-slot>

                        <x-slot:content>

                            <div
                                style="
                                    border:1px solid #e5e7eb;
                                    border-radius:14px;
                                    overflow:hidden;
                                    background:#fff;
                                "
                            >

                                <!-- Top -->
                                <div
                                    style="
                                        padding:14px;
                                        border-bottom:1px solid #f1f5f9;
                                        display:flex;
                                        align-items:center;
                                        gap:12px;
                                    "
                                >
                                    <div
                                        style="
                                            width:46px;
                                            height:46px;
                                            border-radius:10px;
                                            background:#e2e8f0;
                                            flex-shrink:0;
                                        "
                                    ></div>

                                    <div style="flex:1;">
                                        <p
                                            style="
                                                font-size:13px;
                                                font-weight:700;
                                                color:#0f172a;
                                                margin:0 0 4px;
                                            "
                                        >
                                            Ranker Point
                                        </p>

                                        <p
                                            style="
                                                font-size:11px;
                                                color:#94a3b8;
                                                margin:0;
                                            "
                                        >
                                            just now
                                        </p>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div style="padding:14px;">

                                    <p
                                        style="
                                            font-size:15px;
                                            font-weight:700;
                                            color:#111827;
                                            margin-bottom:8px;
                                            word-break:break-word;
                                        "
                                    >
                                        @{{ title || 'Notification Title' }}
                                    </p>

                                    <p
                                        style="
                                            font-size:13px;
                                            line-height:1.6;
                                            color:#6b7280;
                                            margin-bottom:12px;
                                            white-space:pre-line;
                                        "
                                    >
                                        @{{ text || 'Your notification message preview will appear here.' }}
                                    </p>

                                    <div
                                        style="
                                            display:inline-flex;
                                            align-items:center;
                                            gap:6px;
                                            padding:4px 10px;
                                            border-radius:999px;
                                            background:#eff6ff;
                                            color:#2563eb;
                                            font-size:11px;
                                            font-weight:600;
                                        "
                                    >
                                        Redirect:
                                        @{{ redirectType }}
                                    </div>

                                </div>

                                <!-- Footer -->
                                <div
                                    style="
                                        border-top:1px solid #f1f5f9;
                                        padding:12px 14px;
                                        background:#fafafa;
                                    "
                                >

                                    <div
                                        v-if="redirectType === 'internal'"
                                        style="
                                            font-size:12px;
                                            color:#475569;
                                            word-break:break-all;
                                        "
                                    >
                                        Path:
                                        <strong>
                                            @{{ path || '/' }}
                                        </strong>
                                    </div>

                                    <div
                                        v-if="redirectType === 'external'"
                                        style="
                                            font-size:12px;
                                            color:#475569;
                                            word-break:break-all;
                                        "
                                    >
                                        URL:
                                        <strong>
                                            @{{ link || 'https://example.com' }}
                                        </strong>
                                    </div>

                                </div>

                            </div>
                        </x-slot:content>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-create-notification', {
                template: '#v-create-notification-template',

                data() {
                    return {
                        title: @json(old('title', '')),
                        text: @json(old('text', '')),

                        redirectType: @json(old('redirect_type', 'internal')),

                        link: @json(old('link', '')),

                        path: @json(old('path', '')),
                        
                        params: @json(old('params', '{}')),
                    };
                },

                computed: {
                    isValidJson() {
                        if (! this.params) {
                            return true;
                        }

                        try {
                            JSON.parse(this.params);

                            return true;
                        } catch (e) {
                            return false;
                        }
                    },
                },

                watch: {
                    redirectType(value) {
                        if (value === 'internal') {
                            this.link = '';
                        }

                        if (value === 'external') {
                            this.path   = '';
                            this.params = '{}';
                        }
                    },
                },

                methods: {
                    formatJson() {
                        if (! this.params) {
                            this.params = '{}';

                            return;
                        }

                        try {
                            this.params = JSON.stringify(
                                JSON.parse(this.params),
                                null,
                                4
                            );
                        } catch (e) {
                            console.warn('Invalid JSON');
                        }
                    },

                    minifyJson() {
                        if (! this.params) {
                            return;
                        }

                        try {
                            this.params = JSON.stringify(
                                JSON.parse(this.params)
                            );
                        } catch (e) {
                            console.warn('Invalid JSON');
                        }
                    },

                    exampleTestDetail() {
                        this.path = '/test-detail';

                        this.params = JSON.stringify({
                            test_id: 1,
                        }, null, 4);
                    },

                    exampleBookDetail() {
                        this.path = '/book-detail';

                        this.params = JSON.stringify({
                            book_id: 1,
                        }, null, 4);
                    },

                    exampleSubjectDetail() {
                        this.path = '/subject-detail';

                        this.params = JSON.stringify({
                            subject_id: 1,
                        }, null, 4);
                    },
                },

                mounted() {
                    if (
                        this.redirectType === 'internal'
                        && this.params
                    ) {
                        this.formatJson();
                    }
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>