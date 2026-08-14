<?php

use App\Livewire\Child\Dashboard\Index as Dashboard;
use App\Livewire\Child\Explore\Index as Explore;
use App\Livewire\Child\Missions\Index as Missions;
use App\Livewire\Child\Worlds\Show as SubjectsWorld;
use App\Livewire\Child\Missions\Journey\Show as MissionJourneyShow;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonActivity;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/child/my-universe', Dashboard::class)
        ->name('child.dashboard');

    Route::get('/child/explore', Explore::class)
        ->name('child.explore');

    Route::get('/child/missions', Missions::class)
        ->name('child.missions');

    Route::get('/child/missions/{mission}', MissionJourneyShow::class)
        ->name('child.mission');

    Route::get('/world/{subject:slug}', SubjectsWorld::class)
        ->name('child.world');

    /*
        |--------------------------------------------------------------------------
        | Course Adventure
        |--------------------------------------------------------------------------
        */

    Route::get(
        '/learn/course/{course}',
        function (Course $course) {
            return view(
                'child.learning.course',
                compact('course')
            );
        }
    )->name('learn.course');

    /*
        |--------------------------------------------------------------------------
        | Lesson Introduction
        |--------------------------------------------------------------------------
        */

    Route::get(
        '/learn/lesson/{lesson}',
        function (Lesson $lesson) {

            return view(
                'child.learning.lesson',
                compact('lesson')
            );
        }
    )->name('learn.lesson');


    /*
        |--------------------------------------------------------------------------
        | Activity Player
        |--------------------------------------------------------------------------
        */

    Route::get(
        '/learn/activity/{activity}',
        function (LessonActivity $activity) {
            return view(
                'child.learning.activity',
                compact('activity')
            );
        }
    )->name('learn.activity');

    /*
        |--------------------------------------------------------------------------
        | Nova
        |--------------------------------------------------------------------------
        */
    Route::get(
        '/nova',
        function () {
            return 'Nova Coming Soon';
        }
    )->name('nova');
});

Route::view(
    '/design-system',
    'lqds.explorer'
)->name('lqds.explorer');
