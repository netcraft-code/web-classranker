<?php

return [
    /**
     * Dashboard.
     */
    [
        'key'   => 'dashboard',
        'name'  => 'admin::app.components.layouts.sidebar.dashboard',
        'route' => 'admin.class_ranker.dashboard.index',
        'sort'  => 1,
        'icon'  => 'icon-dashboard',
    ],

    /**
     * Study Materials.
     */
    [
        'key'   => 'study_materials',
        'name'  => 'class_ranker::app.components.layouts.sidebar.study-materials',
        'route' => 'admin.study_materials.questions.index',
        'sort'  => 2,
        'icon'  => 'icon-product',
    ], [
        'key'   => 'study_materials.questions',
        'name'  => 'class_ranker::app.components.layouts.sidebar.questions',
        'route' => 'admin.study_materials.questions.index',
        'sort'  => 1,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.questions.questions',
        'name'  => 'class_ranker::app.components.layouts.sidebar.questions',
        'route' => 'admin.study_materials.questions.index',
        'sort'  => 1,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.questions.videos',
        'name'  => 'class_ranker::app.components.layouts.sidebar.videos',
        'route' => 'admin.study_materials.videos.index',
        'sort'  => 2,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.questions.pdfs',
        'name'  => 'class_ranker::app.components.layouts.sidebar.pdfs',
        'route' => 'admin.study_materials.pdfs.index',
        'sort'  => 3,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.questions.quizzes',
        'name'  => 'class_ranker::app.components.layouts.sidebar.quizzes',
        'route' => 'admin.study_materials.quizzes.index',
        'sort'  => 4,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.questions.notes',
        'name'  => 'class_ranker::app.components.layouts.sidebar.notes',
        'route' => 'admin.study_materials.notes.index',
        'sort'  => 5,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.chapters',
        'name'  => 'class_ranker::app.components.layouts.sidebar.chapters',
        'route' => 'admin.study_materials.chapters.index',
        'sort'  => 3,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.books',
        'name'  => 'class_ranker::app.components.layouts.sidebar.books',
        'route' => 'admin.study_materials.books.index',
        'sort'  => 4,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.subjects',
        'name'  => 'class_ranker::app.components.layouts.sidebar.subjects',
        'route' => 'admin.study_materials.subjects.subjects.index',
        'sort'  => 5,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.subjects.subjects',
        'name'  => 'class_ranker::app.components.layouts.sidebar.subjects',
        'route' => 'admin.study_materials.subjects.subjects.index',
        'sort'  => 1,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.subjects.grades',
        'name'  => 'class_ranker::app.components.layouts.sidebar.grades',
        'route' => 'admin.study_materials.subjects.grades.index',
        'sort'  => 2,
        'icon'  => '',
    ], [
        'key'   => 'study_materials.subjects.boards',
        'name'  => 'class_ranker::app.components.layouts.sidebar.boards',
        'route' => 'admin.study_materials.subjects.boards.index',
        'sort'  => 3,
        'icon'  => '',
    ],

    /**
     * Plans.
     */
    [
        'key'   => 'plans',
        'name'  => 'class_ranker::app.components.layouts.sidebar.plans',
        'route' => 'admin.plans.index',
        'sort'  => 3,
        'icon'  => 'icon-sales',
    ], [
        'key'   => 'plans.plans',
        'name'  => 'class_ranker::app.components.layouts.sidebar.plans',
        'route' => 'admin.plans.index',
        'sort'  => 1,
        'icon'  => '',
    ], [
        'key'   => 'plans.customer_plans',
        'name'  => 'Customer Plans',
        'route' => 'admin.plans.customer_plans.index',
        'sort'  => 2,
        'icon'  => '',
    ],

    /**
     * Discussions.
     */
    [
        'key'   => 'discussions',
        'name'  => 'class_ranker::app.components.layouts.sidebar.discussions',
        'route' => 'admin.discussions.index',
        'sort'  => 3,
        'icon' => 'icon-promotion',
        'icon-class' => 'promotion-icon',
    ],  [
        'key'   => 'discussions.discussions',
        'name'  => 'class_ranker::app.components.layouts.sidebar.discussions',
        'route' => 'admin.discussions.index',
        'sort'  => 1,
        'icon'  => '',
    ], [
        'key'   => 'discussions.hashtags',
        'name'  => 'class_ranker::app.components.layouts.sidebar.hashtags',
        'route' => 'admin.hashtags.index',
        'sort'  => 2,
        'icon'  => '',
    ],
    
    /**
     * Customers.
     */
    [
        'key'   => 'customers',
        'name'  => 'admin::app.components.layouts.sidebar.customers',
        'route' => 'admin.customers.customers.index',
        'sort'  => 4,
        'icon'  => 'icon-customer-2',
    ], [
        'key'   => 'customers.customers',
        'name'  => 'admin::app.components.layouts.sidebar.customers',
        'route' => 'admin.customers.customers.index',
        'sort'  => 1,
        'icon'  => '',
    ],

    /**
     * CMS.
     */
    [
        'key'   => 'cms',
        'name'  => 'admin::app.components.layouts.sidebar.cms',
        'route' => 'admin.cms.index',
        'sort'  => 5,
        'icon'  => 'icon-cms',
    ],
];