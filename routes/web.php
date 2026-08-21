<?php

use App\Livewire\Child\Dashboard\Index as Dashboard;
use App\Livewire\Child\Explore\Index as Explore;
use App\Livewire\Child\Missions\Index as Missions;
use App\Livewire\Child\Missions\Journey\Show as MissionJourneyShow;
use App\Livewire\Child\Worlds\Show as SubjectsWorld;
use App\Livewire\Child\Learning\Course as LearningCourse;
use App\Livewire\Child\Learning\Lesson as LearningLesson;
use App\Livewire\Child\Learning\Activity as LearningActivity;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
  return view("welcome");
});

Route::get("/login", function () {
  return view("login");
})->name("login");

Route::get("/register", function () {
  return view("register");
});

/*
|--------------------------------------------------------------------------
| Authenticated Child Routes
|--------------------------------------------------------------------------
*/

Route::middleware(["child"])->group(function () {
  /*
    |--------------------------------------------------------------------------
    | Child Universe
    |--------------------------------------------------------------------------
    */

  Route::get("/child", Dashboard::class)->name("child");

  Route::get("/child/my-universe", Dashboard::class)->name("child.dashboard");

  /*
    |--------------------------------------------------------------------------
    | Child Explore
    |--------------------------------------------------------------------------
    */

  Route::get("/child/explore", Explore::class)->name("child.explore");

  /*
    |--------------------------------------------------------------------------
    | Child Missions
    |--------------------------------------------------------------------------
    */

  Route::get("/child/missions", Missions::class)->name("child.missions");

  Route::get("/child/missions/{mission}", MissionJourneyShow::class)->name(
    "child.mission"
  );

  /*
    |--------------------------------------------------------------------------
    | Child Worlds
    |--------------------------------------------------------------------------
    */

  Route::get("/child/world/{subject:slug}", SubjectsWorld::class)->name(
    "child.world"
  );

  /*
    |--------------------------------------------------------------------------
    | Child Learning
    |--------------------------------------------------------------------------
    */

  Route::get("/learn/course/{course}", LearningCourse::class)->name(
    "learn.course"
  );

  Route::get("/learn/lesson/{lesson}", LearningLesson::class)->name(
    "learn.lesson"
  );

  Route::get("/learn/activity/{activity}", LearningActivity::class)->name(
    "learn.activity"
  );
});

/*
|--------------------------------------------------------------------------
| Authenticated Non-Child Routes
|--------------------------------------------------------------------------
*/

Route::middleware(["auth"])->group(function () {
  Route::get("/nova", function () {
    return "Nova Coming Soon";
  })->name("nova");
});

Route::view("/design-system", "lqds.explorer")->name("lqds.explorer");
