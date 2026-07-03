# LearnQuest Architecture

**AI Project Documentation**

**Document:** 01_ARCHITECTURE.md

**Version:** 1.0

**Status:** Active

---

# Table of Contents

1. Purpose
2. Architectural Principles
3. System Overview
4. Domain Model
5. Learning Hierarchy
6. Progress Architecture
7. Service Layer
8. Gamification Architecture
9. Future Readiness Architecture
10. Administration Architecture
11. Configuration
12. Future Expansion
13. Related Documents
14. Version History

---

# 1. Purpose

This document defines the architecture of LearnQuest.

It serves as the primary technical reference for developers and AI assistants.

Any architectural changes should be reflected here before implementation where practical.

---

# 2. Architectural Principles

LearnQuest follows several core engineering principles.

## Service-Oriented Architecture

Business logic belongs in dedicated service classes.

Models primarily represent data and relationships.

Controllers, Livewire components, and Filament Resources should remain thin.

---

## Domain-Driven Naming

Business terminology should reflect the learning domain.

Current hierarchy:

Subject

↓

Course

↓

CourseModule

↓

Lesson

↓

LessonActivity

These names should remain consistent throughout the codebase.

---

## Modular Design

Each subsystem should be independently maintainable.

Examples:

* Progress
* Gamification
* Future Readiness
* Analytics
* Parent Portal
* Teacher Portal

---

## Configuration First

Magic numbers should not exist.

Application settings belong inside:

config/learnquest.php

---

## Single Responsibility

Each service should solve one business problem.

Examples:

ActivityProgressService

LessonProgressService

CourseProgressService

SkillProgressService

XPService

BadgeService

FutureReadinessService

---

# 3. System Overview

The platform is organised into several layers.

Presentation Layer

↓

Filament Resources

↓

Widgets

↓

Pages

↓

Service Layer

↓

Models

↓

Database

Business logic flows downward through services rather than directly between user interfaces and models.

---

# 4. Domain Model

Core learning entities:

Child

Subject

Course

CourseModule

Lesson

LessonActivity

Skill

Badge

Achievement

Parent

Teacher (planned)

School (planned)

Progress entities:

ActivityProgress

LessonProgress

CourseProgress

ChildSkillProgress

ChildStreak

Analytics entities:

Future Readiness

Leaderboards

Recommendations (planned)

---

# 5. Learning Hierarchy

The learning structure is intentionally hierarchical.

Subject

↓

Course

↓

CourseModule

↓

Lesson

↓

LessonActivity

Each level contains the level beneath it.

Relationships:

Subject

hasMany Courses

Course

belongsTo Subject

hasMany CourseModules

CourseModule

belongsTo Course

hasMany Lessons

Lesson

belongsTo CourseModule

hasMany LessonActivities

LessonActivity

belongsTo Lesson

belongsToMany Skills

through

lesson_activity_skill

---

# 6. Progress Architecture

Learning progress always begins with an activity.

LessonActivity

↓

ActivityProgress

↓

LessonProgress

↓

CourseProgress

↓

Future Readiness

The source of truth is ActivityProgress.

Higher progress records are derived from lower ones.

This prevents duplicated business logic.

---

## Planned Progress Engine

The ProgressEngineService will orchestrate progress updates.

Child completes activity

↓

ActivityProgressService

↓

LessonProgressService

↓

CourseProgressService

↓

SkillProgressService

↓

XPService

↓

LevelService

↓

AchievementService

↓

BadgeService

↓

DailyStreakService

↓

FutureReadinessService

The engine coordinates services.

Individual services remain independent.

---

# 7. Service Layer

Services are grouped by responsibility.

Progress

ActivityProgressService

LessonProgressService

CourseProgressService

SkillProgressService

ProgressEngineService (planned)

Gamification

XPService

LevelService

BadgeService

AchievementService

DailyStreakService

Analytics

FutureReadinessService

LeaderboardService (planned)

Recommendations (planned)

AI

AITutorService (planned)

RecommendationEngine (planned)

AdaptiveLearningService (planned)

---

# 8. Gamification Architecture

Gamification motivates consistent learning.

Components include:

XP

Levels

Badges

Achievements

Daily Streaks

Leaderboards

Future systems may include:

Seasonal Events

Challenges

Quests

Team Competitions

---

# 9. Future Readiness Architecture

Future Readiness measures competency growth.

Skill Progress

↓

Skill Levels

↓

Future Readiness Score

↓

Analytics

↓

Recommendations

Every completed activity contributes toward one or more future-ready skills.

Skills are linked to LessonActivities through the lesson_activity_skill pivot table.

Future Readiness is calculated from ChildSkillProgress.

---

# 10. Administration Architecture

Administration uses Filament v5.6.3.

Current components include:

Resources

Widgets

Relation Managers

Dashboard Widgets

Leaderboards

Charts

Statistics

Future additions:

Parent Dashboard

Teacher Dashboard

School Dashboard

AI Dashboard

---

# 11. Configuration

Project configuration should be centralised.

Examples:

config/learnquest.php

Contains:

XP per level

Maximum skill XP

Passing score

Daily streak bonuses

Future Readiness settings

Future AI settings

---

# 12. Future Expansion

The architecture is intentionally designed for long-term growth.

Planned modules include:

AI Tutor

Adaptive Learning

Parent Portal

Teacher Portal

School Portal

Recommendation Engine

Mobile Applications

Offline Learning

Content Marketplace

Multi-school Support

Internationalisation

Every new subsystem should integrate through services rather than tightly coupling with existing components.

---

# 13. Related Documents

00_PROJECT.md

02_DATABASE.md

03_FILAMENT.md

04_CODING_STANDARDS.md

05_SERVICES.md

06_GAMIFICATION.md

07_FUTURE_READINESS.md

08_ROADMAP.md

09_DEVELOPMENT_LOG.md

10_PROMPT.md

11_DECISIONS.md

---

# 14. Version History

## Version 1.0

Initial architecture document.

Documents the current LearnQuest architecture, service-oriented design, learning hierarchy, progress flow, administration structure, and future expansion strategy.

This document should evolve as the platform architecture grows while preserving the core architectural principles defined in 00_PROJECT.md.
