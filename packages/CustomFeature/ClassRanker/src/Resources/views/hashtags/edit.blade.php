<x-admin::layouts>
    <x-slot:title>@lang('class_ranker::app.hashtags.edit.title')</x-slot>

    <x-admin::form
        :action="route('admin.hashtags.update', $hashtag->id)"
        method="PUT"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.hashtags.edit.title')
            </p>
            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.hashtags.index') }}"
                   class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    @lang('admin::app.account.edit.back-btn')
                </a>
                <button type="submit" class="primary-button">
                    @lang('class_ranker::app.hashtags.edit.save-btn')
                </button>
            </div>
        </div>

        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-2">
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('class_ranker::app.hashtags.create.name')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="name"
                            rules="required"
                            :value="old('name', $hashtag->name)"
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
                            :value="old('slug', $hashtag->slug)"
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
                                :checked="old('status', $hashtag->status)"
                                :label="trans('class_ranker::app.hashtags.create.status')"
                            />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>