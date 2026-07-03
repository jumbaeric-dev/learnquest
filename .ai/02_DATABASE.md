# LearnQuest Database Design

**AI Project Documentation**

**Document:** 02_DATABASE.md

**Version:** 1.0

**Status:** Active

---

# Table of Contents

1. Purpose
2. Database Philosophy
3. Database Engine
4. Naming Conventions
5. Core Learning Tables
6. Progress Tables
7. Gamification Tables
8. Analytics Tables
9. Pivot Tables
10. Relationship Diagram
11. Source of Truth
12. Planned Tables
13. Indexing Strategy
14. Migration Rules
15. Related Documents
16. Version History

---

# 1. Purpose

This document defines the database architecture for LearnQuest.

It is the authoritative reference for:

* Database schema
* Relationships
* Naming
* Migration design
* Future expansion

Every migration should align with this document.

---

# 2. Database Philosophy

The database is designed around the learner journey.

A child progresses through educational content while earning XP, improving skills, unlocking achievements, and increasing Future Readiness.

The schema favours:

* Normalisation
* Clear relationships
* Data integrity
* Scalability
* Maintainability

---

# 3. Database Engine

Current database:

PostgreSQL

Primary keys:

id

Foreign keys:

Laravel foreignId()

Cascade deletes should be used where appropriate to maintain referential integrity.

---

# 4. Naming Conventions

Tables use plural snake_case.

Examples

children

subjects

courses

learning_modules

lessons

activities

skills

badges

course_progress

lesson_progress

activity_progress

child_skill_progress

child_streaks

lesson_activity_skill

Columns use snake_case.

Pivot tables use singular model names in alphabetical order where practical, unless a project-specific name improves clarity.

---

# 4.1 Database vs Domain Naming

LearnQuest intentionally distinguishes between database naming and PHP domain naming.

## Philosophy

The database represents the persistence layer.

PHP models represent the business domain.

These two layers have different goals.

### Database Layer

The database prioritises:

* Stability
* Backwards compatibility
* Predictable migrations
* Minimal schema changes

Once a table is in production, it should rarely be renamed unless there is a compelling architectural reason.

### Domain Layer

PHP models prioritise:

* Readability
* Business terminology
* Developer experience
* Educational clarity

Model names should describe the learning domain rather than the historical database structure.

---

## Current Mapping

| Database Table       | PHP Model          |
| -------------------- | ------------------ |
| children             | Child              |
| subjects             | Subject            |
| courses              | Course             |
| learning_modules     | CourseModule       |
| lessons              | Lesson             |
| activities           | LessonActivity     |
| skills               | Skill              |
| badges               | Badge              |
| activity_progress    | ActivityProgress   |
| lesson_progress      | LessonProgress     |
| course_progress      | CourseProgress     |
| child_skill_progress | ChildSkillProgress |
| child_streaks        | ChildStreak        |

---

## Why This Exists

The database schema should remain stable over time.

The PHP layer should remain expressive.

For example:

Database

activities

↓

PHP

LessonActivity

This immediately communicates that the activity belongs to a lesson rather than representing a generic system activity.

Similarly,

learning_modules

↓

CourseModule

communicates the educational relationship more clearly than the historical table name.

---

## Future Rule

Future refactoring should favour improving the PHP domain model before considering database renames.

Database renames should only occur when they provide significant long-term value and can be safely migrated.

---

## AI Guidance

When generating code:

* Always use the PHP model names in application code.
* Use the actual database table names only where required (migrations, explicit table references, raw queries, etc.).
* Avoid introducing new model names that conflict with the established learning hierarchy.

This distinction improves readability while preserving database stability.


# 5. Core Learning Tables

Children

Stores learner profiles.

Subjects

Top-level curriculum categories.

Courses

Belong to Subjects.

Course Modules

Database table:

learning_modules

PHP Model:

CourseModule

Lessons

Belong to Course Modules.

Lesson Activities

Database table:

activities

PHP Model:

LessonActivity

Skills

Future-ready competencies.

Relationships

Subject

↓

Course

↓

CourseModule

↓

Lesson

↓

LessonActivity

---

# 6. Progress Tables

Activity Progress

Stores completion for individual activities.

Lesson Progress

Derived from Activity Progress.

Course Progress

Derived from Lesson Progress.

Child Skill Progress

Stores accumulated XP and mastery for each skill.

Child Streaks

Tracks consecutive learning days.

Progress always flows upward.

Activity

↓

Lesson

↓

Course

↓

Skills

↓

Future Readiness

---

# 7. Gamification Tables

Current

Badges

child_badge

Achievements (planned)

XP (stored on children)

Levels (stored on children)

Daily Streaks

Leaderboards (calculated)

Future

Challenges

Quests

Season Rewards

---

# 8. Analytics Tables

Current analytics are calculated from existing data.

No duplicated analytical data should be stored unless performance requires it.

Examples

Future Readiness

Leaderboards

Strongest Skills

Weakest Skills

Recommended Skills (planned)

---

# 9. Pivot Tables

Current

lesson_activity_skill

Purpose

Links Lesson Activities to one or more Skills.

Contains

activity_id

skill_id

weight

Future pivots may include

child_badge

course_teacher

school_child

---

# 10. Relationship Diagram

Child

↓

ActivityProgress

↓

LessonProgress

↓

CourseProgress

↓

Future Readiness

Learning Structure

Subject

↓

Course

↓

CourseModule

↓

Lesson

↓

LessonActivity

↓

lesson_activity_skill

↓

Skill

↓

ChildSkillProgress

---

# 11. Source of Truth

The following tables are authoritative.

Activity Progress

Source of learning completion.

Child Skill Progress

Source of skill development.

Children

Source of XP and Levels.

Higher-level tables should be derived from lower-level data whenever possible.

Avoid duplicated business logic.

---

# 12. Planned Tables

teacher_profiles

parent_profiles

schools

classes

enrolments

notifications

recommendations

ai_conversations

learning_sessions

parent_reports

teacher_reports

audit_logs

These should integrate without breaking the existing schema.

---

# 13. Indexing Strategy

Use indexes for:

Foreign keys

Frequently filtered columns

Frequently sorted columns

Future Readiness ranking

Leaderboards

Progress queries

Unique constraints should prevent duplicate progress records.

---

# 14. Migration Rules

Every migration should:

Use foreign keys.

Use cascade deletes where appropriate.

Avoid nullable foreign keys unless necessary.

Prefer updateOrCreate() in services rather than duplicate rows.

Preserve backwards compatibility where practical.

Document architectural changes in:

11_DECISIONS.md

---

# 15. Related Documents

00_PROJECT.md

01_ARCHITECTURE.md

03_FILAMENT.md

04_CODING_STANDARDS.md

05_SERVICES.md

07_FUTURE_READINESS.md

---

# 16. Version History

## Version 1.0

Initial database specification.

Documents the current schema, relationships, naming conventions, source-of-truth philosophy, and future expansion strategy.
