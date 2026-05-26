<x-admin::layouts>
    <x-slot:title>@lang('class_ranker::app.hashtags.create.title')</x-slot>

    <x-admin::form :action="route('admin.hashtags.store')">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.hashtags.create.title')
            </p>
            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.hashtags.index') }}"
                   class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    @lang('admin::app.account.edit.back-btn')
                </a>
                <button type="submit" class="primary-button">
                    @lang('class_ranker::app.hashtags.create.save-btn')
                </button>
            </div>
        </div>

        <v-create-hashtag></v-create-hashtag>
    </x-admin::form>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-create-hashtag-template">
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <div class="flex flex-1 flex-col gap-2">
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.hashtags.create.information')
                        </p>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.hashtags.create.name')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="name"
                                rules="required"
                                v-model="name"
                                :value="old('name')"
                                :label="trans('class_ranker::app.hashtags.create.name')"
                                :placeholder="trans('class_ranker::app.hashtags.create.name')"
                            />
                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.hashtags.create.slug')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="slug"
                                rules="required"
                                v-model="slug"
                                :value="old('slug')"
                                :label="trans('class_ranker::app.hashtags.create.slug')"
                                placeholder="e.g. mathematics"
                            />
                            <x-admin::form.control-group.error control-name="slug" />
                        </x-admin::form.control-group>
                    </div>
                </div>

                <div class="flex w-[360px] flex-col gap-2 max-sm:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('class_ranker::app.hashtags.create.settings')
                            </p>
                        </x-slot>
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('class_ranker::app.hashtags.create.status')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    value="1"
                                    :checked="true"
                                    :label="trans('class_ranker::app.hashtags.create.status')"
                                />
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-create-hashtag', {
                template: '#v-create-hashtag-template',
                data() {
                    return { name: '', slug: '' };
                },
                watch: {
                    name(val) {
                        if (!this.slug || this.slugIsAuto) {
                            this.slug = this.slugify(val);
                            this.slugIsAuto = true;
                        }
                    },
                },
                data() {
                    return { name: '', slug: '', slugIsAuto: true };
                },
                methods: {
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