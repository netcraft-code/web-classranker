<?php

return [
    /**
     * Dashboard.
     */
    [
        'key'        => 'dashboard',
        'name'       => 'admin::app.components.layouts.sidebar.dashboard',
        'route'      => 'admin.class_ranker.dashboard.index',
        'sort'       => 1,
        'icon'       => 'icon-dashboard',
    ],

    /**
     * Study Materials.
     */
    [
        'key'        => 'study_materials',
        'name'       => 'class_ranker::app.components.layouts.sidebar.study-materials',
        'route'      => 'admin.study_materials.questions.index',
        'sort'       => 2,
        'icon'       => 'icon-product',
    ], [
        'key'        => 'study_materials.questions',
        'name'       => 'class_ranker::app.components.layouts.sidebar.questions',
        'route'      => 'admin.study_materials.questions.index',
        'sort'       => 1,
        'icon'       => '',
    ], [
        'key'        => 'study_materials.chapters',
        'name'       => 'class_ranker::app.components.layouts.sidebar.chapters',
        'route'      => 'admin.study_materials.chapters.index',
        'sort'       => 2,
        'icon'       => '',
    ], [
        'key'        => 'study_materials.books',
        'name'       => 'class_ranker::app.components.layouts.sidebar.books',
        'route'      => 'admin.study_materials.books.index',
        'sort'       => 3,
        'icon'       => '',
    ], [
        'key'        => 'study_materials.subjects',
        'name'       => 'class_ranker::app.components.layouts.sidebar.subjects',
        'route'      => 'admin.study_materials.subjects.subjects.index',
        'sort'       => 4,
        'icon'       => '',
    ], [
        'key'        => 'study_materials.subjects.subjects',
        'name'       => 'class_ranker::app.components.layouts.sidebar.subjects',
        'route'      => 'admin.study_materials.subjects.subjects.index',
        'sort'       => 1,
        'icon'       => '',
    ], [
        'key'        => 'study_materials.subjects.grades',
        'name'       => 'class_ranker::app.components.layouts.sidebar.grades',
        'route'      => 'admin.study_materials.subjects.grades.index',
        'sort'       => 2,
        'icon'       => '',
    ], [
        'key'        => 'study_materials.subjects.boards',
        'name'       => 'class_ranker::app.components.layouts.sidebar.boards',
        'route'      => 'admin.study_materials.subjects.boards.index',
        'sort'       => 3,
        'icon'       => '',
    ], 
    
    /**
     * Customers.
     */
    [
        'key'        => 'customers',
        'name'       => 'admin::app.components.layouts.sidebar.customers',
        'route'      => 'admin.customers.customers.index',
        'sort'       => 3,
        'icon'       => 'icon-customer-2',
    ], [
        'key'        => 'customers.customers',
        'name'       => 'admin::app.components.layouts.sidebar.customers',
        'route'      => 'admin.customers.customers.index',
        'sort'       => 1,
        'icon'       => '',
    ],

    /**
     * CMS.
     */
    [
        'key'        => 'cms',
        'name'       => 'admin::app.components.layouts.sidebar.cms',
        'route'      => 'admin.cms.index',
        'sort'       => 5,
        'icon'       => 'icon-cms',
    ],
];