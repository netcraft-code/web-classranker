<x-admin::layouts>
    <x-slot:title>
        @lang('class_ranker::app.plans.edit.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.plans.update', $plan->id)"
        enctype="multipart/form-data"
    >
        @method('PUT')

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('class_ranker::app.plans.edit.title') — {{ $plan->name }}
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.plans.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <button type="submit" class="primary-button">
                    @lang('class_ranker::app.plans.edit.save-btn')
                </button>
            </div>
        </div>

        <v-edit-plan
            :initial-plan='@json($plan)'
        ></v-edit-plan>
    </x-admin::form>

    @pushOnce('scripts')
        <!-- ================= TEMPLATE ================= -->
        <script type="text/x-template" id="v-edit-plan-template">
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">

                <!-- LEFT: Main Info -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                    <!-- Basic Information -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.plans.create.information')
                        </p>

                        <!-- Name -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.plans.create.name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="name"
                                rules="required"
                                v-model="name"
                                :label="trans('class_ranker::app.plans.create.name')"
                                :placeholder="trans('class_ranker::app.plans.create.name')"
                            />
                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <!-- Code -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('class_ranker::app.plans.create.code')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="code"
                                rules="required"
                                v-model="code"
                                :label="trans('class_ranker::app.plans.create.code')"
                                :placeholder="trans('class_ranker::app.plans.create.code')"
                            />
                            <x-admin::form.control-group.error control-name="code" />
                        </x-admin::form.control-group>

                        <!-- Description -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('class_ranker::app.plans.create.description')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                name="description"
                                v-model="description"
                                :label="trans('class_ranker::app.plans.create.description')"
                                :placeholder="trans('class_ranker::app.plans.create.description')"
                                rows="3"
                            />
                            <x-admin::form.control-group.error control-name="description" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- Pricing -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.plans.create.pricing')
                        </p>

                        <div class="grid grid-cols-2 gap-4">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('class_ranker::app.plans.create.price') (₹)
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="number"
                                    name="price"
                                    rules="required|min_value:0"
                                    v-model="price"
                                    step="0.01"
                                    placeholder="0.00"
                                />
                                <x-admin::form.control-group.error control-name="price" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('class_ranker::app.plans.create.discount-price') (₹)
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="number"
                                    name="discount_price"
                                    v-model="discountPrice"
                                    step="0.01"
                                    placeholder="0.00"
                                />
                                <x-admin::form.control-group.error control-name="discount_price" />
                            </x-admin::form.control-group>
                        </div>

                        <div
                            v-if="discountPercent > 0"
                            style="background: #dcfce7; border: 1px solid #86efac; border-radius: 8px; padding: 10px 14px; margin-top: 8px; display: flex; align-items: center; gap: 8px;"
                        >
                            <span style="font-size: 20px;">🏷️</span>
                            <span style="font-size: 14px; font-weight: 600; color: #16a34a;">
                                @{{ discountPercent }}% off — Customer pays ₹@{{ discountPrice }}
                            </span>
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('class_ranker::app.plans.create.duration')
                        </p>

                        <div class="grid grid-cols-2 gap-4">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('class_ranker::app.plans.create.duration-value')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="number"
                                    name="duration_value"
                                    rules="required|min_value:1"
                                    v-model="durationValue"
                                    placeholder="1"
                                />
                                <x-admin::form.control-group.error control-name="duration_value" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('class_ranker::app.plans.create.duration-type')
                                </x-admin::form.control-group.label>

                                <select
                                    name="duration_type"
                                    v-model="durationType"
                                    class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="days">Days</option>
                                    <option value="months">Months</option>
                                    <option value="years">Years</option>
                                </select>
                                <x-admin::form.control-group.error control-name="duration_type" />
                            </x-admin::form.control-group>
                        </div>

                        <p v-if="durationValue && durationType" class="mt-2 text-sm text-gray-500">
                            Plan valid for: <strong>@{{ durationLabel }}</strong>
                        </p>
                    </div>

                    <!-- Features -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <div class="mb-4 flex items-center justify-between">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('class_ranker::app.plans.create.features')
                            </p>
                            <button
                                type="button"
                                @click="addFeature"
                                class="primary-button !px-3 !py-1.5 text-sm"
                            >
                                + Add Feature
                            </button>
                        </div>

                        <div
                            v-if="features.length === 0"
                            class="rounded border border-dashed border-gray-300 p-6 text-center text-gray-400"
                        >
                            No features added yet.
                        </div>

                        <div
                            v-for="(feature, index) in features"
                            :key="index"
                            class="mb-2 flex items-center gap-2"
                        >
                            <input
                                type="text"
                                :name="`features[${index}]`"
                                v-model="features[index]"
                                placeholder="e.g. Unlimited access to all books"
                                class="flex-1 rounded border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            <button
                                type="button"
                                @click="removeFeature(index)"
                                class="flex h-8 w-8 items-center justify-center rounded bg-red-50 text-red-500 hover:bg-red-100"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Settings -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('class_ranker::app.plans.create.settings')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- Status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('class_ranker::app.plans.create.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    value="1"
                                    :label="trans('class_ranker::app.plans.create.status')"
                                    ::checked="initialPlan.status"
                                />
                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                            <!-- Is Popular -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('class_ranker::app.plans.create.is-popular')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="is_popular"
                                    value="1"
                                    :label="trans('class_ranker::app.plans.create.is-popular')"
                                    ::checked="initialPlan.is_popular"
                                />
                                <x-admin::form.control-group.error control-name="is_popular" />
                            </x-admin::form.control-group>

                            <!-- Sort Order -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('class_ranker::app.plans.create.sort-order')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="number"
                                    name="sort_order"
                                    v-model="sortOrder"
                                    placeholder="0"
                                />
                                <x-admin::form.control-group.error control-name="sort_order" />
                            </x-admin::form.control-group>

                            <!-- Avatar -->
                            <div class="flex w-2/5 flex-col gap-2 mb-5">
                                <p class="font-medium text-gray-800 dark:text-white">
                                    @lang('class_ranker::app.plans.create.avatar')
                                </p>
                                <p class="text-xs text-gray-500">
                                    @lang('class_ranker::app.plans.create.avatar-size')
                                </p>
                                <x-admin::media.images
                                    name="avatar"
                                    :uploaded-images="$plan->avatar ? [['id' => 'existing', 'url' => asset('storage/'.$plan->avatar)]] : []"
                                />
                            </div>
                        </x-slot>
                    </x-admin::accordion>

                    <!-- Live Preview -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                Preview
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; position: relative; overflow: hidden;">
                                <div
                                    v-if="isPopular"
                                    style="position: absolute; top: 12px; right: 12px; background: #f59e0b; color: white; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px;"
                                >
                                    ⭐ POPULAR
                                </div>

                                <p style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                                    @{{ name || 'Plan Name' }}
                                </p>

                                <p style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;">
                                    @{{ durationLabel }}
                                </p>

                                <div style="margin-bottom: 12px;">
                                    <span
                                        v-if="discountPrice"
                                        style="font-size: 12px; color: #94a3b8; text-decoration: line-through; margin-right: 6px;"
                                    >₹@{{ price }}</span>
                                    <span style="font-size: 28px; font-weight: 800; color: #0f172a;">
                                        ₹@{{ discountPrice || price || '0' }}
                                    </span>
                                </div>

                                <div v-if="features.length > 0" style="text-align: left; margin-top: 12px;">
                                    <div
                                        v-for="(f, i) in features.slice(0, 4)"
                                        :key="i"
                                        style="font-size: 12px; color: #475569; padding: 3px 0; display: flex; align-items: center; gap: 6px;"
                                    >
                                        <span style="color: #22c55e;">✓</span> @{{ f }}
                                    </div>
                                    <p v-if="features.length > 4" style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                                        +@{{ features.length - 4 }} more features
                                    </p>
                                </div>
                            </div>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <!-- ================= SCRIPT ================= -->
        <script type="module">
            app.component('v-edit-plan', {
                template: '#v-edit-plan-template',

                props: {
                    initialPlan: {
                        type: Object,
                        required: true,
                    },
                },

                data() {
                    return {
                        name:          this.initialPlan.name,
                        code:          this.initialPlan.code,
                        description:   this.initialPlan.description || '',
                        price:         this.initialPlan.price,
                        discountPrice: this.initialPlan.discount_price || '',
                        durationValue: this.initialPlan.duration_value,
                        durationType:  this.initialPlan.duration_type,
                        features:      this.initialPlan.features || [],
                        sortOrder:     this.initialPlan.sort_order,
                        isPopular:     this.initialPlan.is_popular,
                    };
                },

                computed: {
                    discountPercent() {
                        if (!this.discountPrice || !this.price || parseFloat(this.discountPrice) >= parseFloat(this.price)) return 0;
                        return Math.round(((this.price - this.discountPrice) / this.price) * 100);
                    },

                    durationLabel() {
                        if (!this.durationValue || !this.durationType) return '';
                        const val  = parseInt(this.durationValue);
                        const type = this.durationType.charAt(0).toUpperCase() + this.durationType.slice(1, -1);
                        return val + ' ' + type + (val > 1 ? 's' : '');
                    },
                },

                methods: {
                    addFeature() {
                        this.features.push('');
                    },

                    removeFeature(index) {
                        this.features.splice(index, 1);
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>