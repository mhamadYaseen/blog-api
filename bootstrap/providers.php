<?php

return [
    App\Providers\AppServiceProvider::class,

    /*
     * Module Service Providers
     */
    Modules\Users\App\Providers\UsersServiceProvider::class,
    Modules\Posts\App\Providers\PostsServiceProvider::class,
    Modules\Comments\App\Providers\CommentsServiceProvider::class,
];
