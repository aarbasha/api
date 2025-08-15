<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'owner' => [
            "dashborde" => "c,r,u,d",
            'users' => 'c,r,u,d',
            'categories' => 'c,r,u,d',
            'roles' => 'c,r,u,d',
            'parmissions' => 'c,r,u,d',
            'comments' => 'c,r,u,d',
            'rating' => 'c,r,u,d',
            'post' => 'c,r,u,d',
            'favorites' => 'c,r,u,d',

        ],
        'admin' => [
            'users' => 'c,r,u,d',
            'profile' => 'r,u',
        ],
        'user' => [
            'profile' => 'r,u',
            'comments' => 'c,r,u,d',
            'rating' => 'c,r,u,d',
            'favorites' => 'c,r,u,d',
        ],

    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],
];
