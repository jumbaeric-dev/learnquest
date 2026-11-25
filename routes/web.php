<?php

use App\Http\Controllers\Auth\ChildLoginController;
use App\Http\Controllers\Auth\ParentAuthController;
use App\Http\Controllers\Auth\ParentEmailVerificationController;
use App\Http\Controllers\Auth\ParentPasswordResetController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LocaleController;
use App\Livewire\Child\Dashboard\Index as Dashboard;
use App\Livewire\Child\Explore\Index as Explore;
use App\Livewire\Child\Learning\Activity as LearningActivity;
use App\Livewire\Child\Learning\Course as LearningCourse;
use App\Livewire\Child\Learning\Lesson as LearningLesson;
use App\Livewire\Child\Missions\Index as Missions;
use App\Livewire\Child\Missions\Journey\Show as MissionJourneyShow;
use App\Livewire\Child\Profile\Edit as ChildProfileEdit;
use App\Livewire\Child\Profile\Show as ChildProfile;
use App\Livewire\Child\Settings\Index as ChildSettings;
use App\Livewire\Child\Worlds\Show as SubjectsWorld;
use App\Livewire\ParentPortal\Account\Settings as ParentAccountSettings;
use App\Livewire\ParentPortal\Billing\Index as ParentBilling;
use App\Livewire\ParentPortal\Children\Index as ParentChildren;
use App\Livewire\ParentPortal\Children\Show as ParentChildShow;
use App\Livewire\ParentPortal\Dashboard\Index as ParentDashboard;
use App\Services\Child\Context\CurrentChildService;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::post('/contact', [ContactController::class, 'send'])
    ->middleware('throttle:5,1')
    ->name('contact.send');
    
Route::post('/locale', [LocaleController::class, 'update'])
  ->middleware('throttle:30,1')
  ->name('locale.update');

// Guest Login Routes
Route::get("/login", [ChildLoginController::class, "showLoginForm"])->name(
  "login"
);

Route::post("/login", [ChildLoginController::class, "login"])->name(
  "child.login"
);

Route::post("/logout", [ChildLoginController::class, "logout"])
  ->middleware("auth")
  ->name("logout");
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
  Route::get("/child/profile", ChildProfile::class)
    ->name("child.profile");

  Route::get("/child/profile/edit", ChildProfileEdit::class)
    ->name("child.profile.edit");

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

  // child settings
  Route::get('/child/settings', ChildSettings::class)
    ->name('child.settings');
});

/*
|--------------------------------------------------------------------------
| Parent Portal
|--------------------------------------------------------------------------
*/

Route::get("/parent/login", [ParentAuthController::class, "showLoginForm"])
  ->name("parent.login");

Route::post("/parent/login", [ParentAuthController::class, "login"])
  ->name("parent.login.submit");

Route::get("/parent/register", [ParentAuthController::class, "showRegisterForm"])
  ->name("parent.register");

Route::post("/parent/register", [ParentAuthController::class, "register"])
  ->middleware("throttle:6,1")
  ->name("parent.register.submit");

Route::get("/parent/forgot-password", [ParentPasswordResetController::class, "showForgotForm"])
  ->name("parent.password.request");

Route::post("/parent/forgot-password", [ParentPasswordResetController::class, "sendLink"])
  ->middleware("throttle:5,1")
  ->name("parent.password.email");

Route::get(
  "/parent/reset-password/{token}",
  [ParentPasswordResetController::class, "showResetForm"]
)->name("parent.password.reset");

Route::post("/parent/reset-password", [ParentPasswordResetController::class, "reset"])
  ->middleware("throttle:10,1")
  ->name("parent.password.update");

Route::get("/parent/verify-email/{id}/{hash}", [ParentEmailVerificationController::class, "verify"])
  ->middleware(["signed", "throttle:6,1"])
  ->whereNumber("id")
  ->name("parent.verification.verify");

Route::middleware(["parent", "auth.session"])
  ->prefix("parent")
  ->name("parent.")
  ->group(function () {
        Route::post("/email/verification-notification", [ParentEmailVerificationController::class, "send"])
      ->middleware("throttle:3,1")
      ->name("verification.send");
    Route::get("/", ParentDashboard::class)->name("dashboard");
    Route::get("/children/{child}", ParentChildShow::class)->whereNumber("child")->name("children.show");
    Route::get("/children", ParentChildren::class)->name("children.index");
    Route::post("/return", function (CurrentChildService $currentChild) {
      $currentChild->leaveParentPreview();
      return redirect()->route("parent.dashboard");
    })->name("return");
    Route::get("/account", ParentAccountSettings::class)->name("account");
    Route::get("/billing", ParentBilling::class)->name("billing");
  });


/*
|--------------------------------------------------------------------------
| Authenticated Non-Child Routes
|--------------------------------------------------------------------------
*/

Route::middleware(["auth"])->group(function () {
  Route::get("/nova", function () {
    return redirect()->route("child.dashboard");
  })->name("nova");
});

Route::view("/design-system", "lqds.explorer")->name("lqds.explorer");
