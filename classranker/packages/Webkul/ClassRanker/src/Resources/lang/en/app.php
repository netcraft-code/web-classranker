<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'study-materials' => 'Study Materials',
                'subjects'        => 'Subjects',
                'grades'          => 'Grades',
                'boards'          => 'Boards',
                'books'           => 'Books',
                'chapters'        => 'Chapters',
            ],
        ],
    ],

    'study_materials' => [
        'subjects' => [
            'boards' => [
                'create-success' => 'Board created successfully.',
                'update-success' => 'Board updated successfully.',
                'delete-success' => 'Board deleted successfully.',
                'no-resource'    => 'No resource found.',

                'index' => [
                    'title' => 'Boards',
                    'create-btn' => 'Create Board',
                    'already-taken' => 'The :name already exists.',

                    'datagrid' => [
                        'id'         => 'ID',
                        'name'       => 'Name',
                        'code'       => 'Code',
                        'avatar'     => 'Avatar',
                        'status'     => 'Status',
                        'edit'       => 'Edit',
                        'delete'     => 'Delete',
                    ],
                ],

                'create' => [
                    'title' => 'Create Board',
                    'save-btn' => 'Save Board',
                    'information' => 'Information',
                    'name' => 'Name',
                    'code' => 'Code',
                    'settings' => 'Settings',
                    'status' => 'Status',
                    'avatar' => 'Avatar',
                    'avatar-size' => 'Recommended size: 110px x 110px',
                ],

                'edit' => [
                    'title' => 'Edit Board',
                    'save-btn' => 'Save Board',
                    'information' => 'Information',
                    'name' => 'Name',
                    'code' => 'Code',
                    'settings' => 'Settings',
                    'status' => 'Status',
                    'avatar' => 'Avatar',
                    'avatar-size' => 'Recommended size: 110px x 110px',
                ],
            ],

            'grades' => [
                'create-success' => 'Grade created successfully.',
                'update-success' => 'Grade updated successfully.',
                'delete-success' => 'Grade deleted successfully.',
                'no-resource'    => 'No resource found.',

                'index' => [
                    'title'         => 'Grades',
                    'create-btn'    => 'Create Grade',
                    'already-taken' => 'The :name already exists.',

                    'datagrid' => [
                        'id'         => 'ID',
                        'name'       => 'Name',
                        'code'       => 'Code',
                        'avatar'     => 'Avatar',
                        'status'     => 'Status',
                        'edit'       => 'Edit',
                        'delete'     => 'Delete',
                    ],
                ],

                'create' => [
                    'title'       => 'Create Board',
                    'save-btn'    => 'Save Board',
                    'information' => 'Information',
                    'name'        => 'Name',
                    'code'        => 'Code',
                    'settings'    => 'Settings',
                    'status'      => 'Status',
                    'avatar'      => 'Avatar',
                    'avatar-size' => 'Recommended size: 110px x 110px',
                ],

                'edit' => [
                    'title'       => 'Edit Board',
                    'update-btn'  => 'Update Board',
                    'information' => 'Information',
                    'name'        => 'Name',
                    'code'        => 'Code',
                    'settings'    => 'Settings',
                    'status'      => 'Status',
                    'avatar'      => 'Avatar',
                    'avatar-size' => 'Recommended size: 110px x 110px',
                ],
            ],

            'subjects' => [
                'create-success' => 'Subject created successfully.',
                'update-success' => 'Subject updated successfully.',
                'delete-success' => 'Subject deleted successfully.',
                'no-resource'    => 'No resource found.',

                'index' => [
                    'title'         => 'Subjects',
                    'create-btn'    => 'Create Subject',
                    'already-taken' => 'The :name already exists.',

                    'datagrid' => [
                        'id'         => 'ID',
                        'name'       => 'Name',
                        'code'       => 'Code',
                        'avatar'     => 'Avatar',
                        'status'     => 'Status',
                        'edit'       => 'Edit',
                        'delete'     => 'Delete',
                    ],
                ],

                'create' => [
                    'title'       => 'Create Subject',
                    'save-btn'    => 'Save Subject',
                    'information' => 'Information',
                    'name'        => 'Name',
                    'code'        => 'Code',
                    'settings'    => 'Settings',
                    'status'      => 'Status',
                    'avatar'      => 'Avatar',
                    'avatar-size' => 'Recommended size: 110px x 110px',
                ],

                'edit' => [
                    'title'       => 'Edit Subject',
                    'update-btn'  => 'Update Subject',
                    'information' => 'Information',
                    'name'        => 'Name',
                    'code'        => 'Code',
                    'settings'    => 'Settings',
                    'status'      => 'Status',
                    'avatar'      => 'Avatar',
                    'avatar-size' => 'Recommended size: 110px x 110px',
                ],
            ],
        ],

        'books' => [
            'create-success' => 'Book created successfully.',
            'update-success' => 'Book updated successfully.',
            'delete-success' => 'Book deleted successfully.',
            'no-resource'    => 'No resource found.',

            'index' => [
                'title'         => 'Books',
                'create-btn'    => 'Create Book',
                'already-taken' => 'The :title already exists.',

                'datagrid' => [
                    'id'         => 'ID',
                    'title'      => 'Title',
                    'code'       => 'Code',
                    'avatar'     => 'Avatar',
                    'status'     => 'Status',
                    'edit'       => 'Edit',
                    'delete'     => 'Delete',
                ],
            ],

            'create' => [
                'title'       => 'Create Book',
                'save-btn'    => 'Save Book',
                'information' => 'Information',
                'name'        => 'Name',
                'code'        => 'Code',
                'settings'    => 'Settings',
                'status'      => 'Status',
                'avatar'      => 'Avatar',
                'avatar-size' => 'Recommended size: 110px x 110px',
                'writer'       => 'Writer',
                'publisher'    => 'Publisher',
                'total-pages' => 'Total Pages',
                'edition'     => 'Edition',
                'publication-year' => 'Publication Year',
            ],

            'edit' => [
                'title'       => 'Edit Book',
                'update-btn'  => 'Update Book',
                'information' => 'Information',
                'name'        => 'Name',
                'code'        => 'Code',
                'settings'    => 'Settings',
                'status'      => 'Status',
                'avatar'      => 'Avatar',
                'avatar-size' => 'Recommended size: 110px x 110px',
                'writer'      => 'Writer',
                'publisher'   => 'Publisher',
                'total-pages' => 'Total Pages',
                'edition'     => 'Edition',
                'publication-year' => 'Publication Year',
            ],
        ],

        'chapters' => [
            'create-success' => 'Chapter created successfully.',
            'update-success' => 'Chapter updated successfully.',
            'delete-success' => 'Chapter deleted successfully.',
            'no-resource'    => 'No resource found.',

            'index' => [
                'title'         => 'Chapters',
                'create-btn'    => 'Create Chapter',
                'already-taken' => 'The :title already exists.',

                'datagrid' => [
                    'id'         => 'ID',
                    'title'      => 'Title',
                    'code'       => 'Code',
                    'avatar'     => 'Avatar',
                    'status'     => 'Status',
                    'edit'       => 'Edit',
                    'delete'     => 'Delete',
                ],
            ],

            'create' => [
                'title'       => 'Create Book',
                'save-btn'    => 'Save Book',
                'information' => 'Information',
                'name'        => 'Name',
                'code'        => 'Code',
                'settings'    => 'Settings',
                'status'      => 'Status',
                'avatar'      => 'Avatar',
                'avatar-size' => 'Recommended size: 110px x 110px',
            ],

            'edit' => [
                'title'       => 'Edit Book',
                'update-btn'  => 'Update Book',
                'information' => 'Information',
                'name'        => 'Name',
                'code'        => 'Code',
                'settings'    => 'Settings',
                'status'      => 'Status',
                'avatar'      => 'Avatar',
                'avatar-size' => 'Recommended size: 110px x 110px',
            ],
        ],
    ],
];
