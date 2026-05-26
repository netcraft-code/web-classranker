<?php

namespace CustomFeature\ClassRanker\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $templates = [
            [
                'event'    => 'bagisto.admin.layout.head.before',
                'template' => 'class_ranker::components.layouts.header.style',
            ],
        ];

        foreach ($templates as $template) {
            Event::listen(current($template), fn ($e) => $e->addTemplate(end($template)));
        }
    }
}
