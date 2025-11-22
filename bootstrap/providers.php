<?php

return [
    App\Providers\AppServiceProvider::class,

    /*
     * Module Service Providers
     */
    Modules\Users\Providers\UsersServiceProvider::class,
    Modules\Posts\Providers\PostsServiceProvider::class,
    Modules\Comments\Providers\CommentsServiceProvider::class,
];
