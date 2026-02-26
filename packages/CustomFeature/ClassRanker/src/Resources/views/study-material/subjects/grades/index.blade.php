<x-admin::layouts>
    <x-slot:title>
        @lang('class_ranker::app.study_materials.subjects.grades.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('class_ranker::app.study_materials.subjects.grades.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.study_materials.subjects.grades.create') }}"
                class="primary-button"
            >
                @lang('class_ranker::app.study_materials.subjects.grades.index.create-btn')
            </a>
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.study_materials.subjects.grades.index')" :isMultiRow="true">
        <template #header="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>

            <template v-else>
                <!-- Grid Header Columns -->
                <div class="row grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 items-center border-b px-2 sm:px-4 py-2.5 dark:border-gray-800">
                    <div
                        class="flex select-none items-center gap-2.5"
                        v-for="(columnGroup, index) in [['id', 'created_at', 'status'], ['avatar', 'code', 'name', 'title'], ['board_code', 'board_name', 'board_title']]"
                    >
                        <p class="text-gray-600 dark:text-gray-300 text-sm sm:text-base">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                        }"
                                        @click="
                                            available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)) : {}
                                        "
                                    >
                                        @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                    </span>
                                </template>
                            </span>

                            <i
                                class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5"
                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                v-if="columnGroup.includes(applied.sort.column)"
                            >
                            </i>
                        </p>
                    </div>
                </div>
            </template>
        </template>

        <template #body="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>

            <template v-else>
                <!-- Order Rows -->
                <div
                    class="row grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-y-4 border-b px-2 sm:px-4 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <div class="flex flex-col gap-1.5">
                        <p class="text-sm sm:text-base font-semibold text-gray-800 dark:text-white">
                            @{{ "@lang('admin::app.sales.orders.index.datagrid.id')".replace(':id', record.id) }}
                        </p>

                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                            @{{ record.created_at }}
                        </p>
                        
                        <p :class="[record.status ? 'label-active': 'label-info']">
                            @{{ record.status ? "@lang('admin::app.catalog.products.index.datagrid.active')" : "@lang('admin::app.catalog.products.index.datagrid.disable')" }}
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <div>
                            <span v-if="record.avatar" v-html="record.avatar"></span>

                            <span v-else
                                class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-bold">
                                @{{ record.name?.charAt(0)?.toUpperCase() }}
                            </span>
                        </div>

                        <!-- Right Side Details -->
                        <div class="flex flex-col gap-1">
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                @{{ record.code }}
                            </p>

                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                @{{ record.name }}
                            </p>

                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                @{{ record.title }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                            @{{ record.board_code }}
                        </p>
                        
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                            @{{ record.board_name }}
                        </p>

                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                            @{{ record.board_title }}
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-x-5">
                        <p
                            class="flex items-center gap-1.5"
                            v-if="available.actions.length"
                        >
                            <span
                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                :class="action.icon"
                                v-text="! action.icon ? action.title : ''"
                                v-for="action in record.actions"
                                @click="performAction(action)"
                            >
                            </span>
                        </p>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

</x-admin::layouts>
