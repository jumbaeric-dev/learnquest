LearnQuest MVP progression-access patch (based on LearnQuest_16.zip)

Apply this ZIP at the root of your current LearnQuest project. The archive
contains only the nine changed source/view/test files listed below; it is not
a full project archive and contains no .env, API keys, vendor folder, or DB.

Files:
- app/Services/Child/Learning/LearningContentAccessService.php
- app/Livewire/Child/Learning/Lesson.php
- app/Livewire/Child/Learning/Activity.php
- app/Livewire/Child/Learning/Course.php
- resources/views/livewire/child/learning/course.blade.php
- resources/views/livewire/child/learning/lesson.blade.php
- tests/Feature/ChildLearningLessonTest.php
- tests/Feature/ChildLearningActivityTest.php
- tests/Feature/ChildLearningCourseTest.php

Changes:
- Unfinished lessons are restricted to the child's current course and next lesson.
- Unfinished activities are restricted to the current course, lesson, and next activity.
- Completed lessons and activities remain available for review even after another
  course becomes the active journey.
- Livewire rechecks activity access before recording progress.
- Course and lesson views show locked future steps instead of links that would 404.
- Tests cover cross-course guessing, skipped lessons/activities, completed reviews,
  and UI link gating.

After overlaying, run these commands from the project root:
  php artisan config:clear
  php artisan test --filter=ChildLearningLessonTest
  php artisan test --filter=ChildLearningActivityTest
  php artisan test --filter=ChildLearningCourseTest
  php artisan test --filter=ChildLearningRouteTest
  php artisan test

The provided source archive does not contain Composer dependencies, so PHPUnit
could not be run in the patch-preparation environment. PHP syntax checks passed
for all changed PHP files; please run the tests in the local Laragon project.
