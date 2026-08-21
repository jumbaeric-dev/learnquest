<div>
    <header>
        <h1>{{ $lesson->title }}</h1>

        @if ($lesson->description)
            <p>{{ $lesson->description }}</p>
        @endif

        <div>
            <strong>Progress:</strong>
            {{ $this->progressPercentage }}%
        </div>

        <div>
            {{ $this->completedActivities }}
            /
            {{ $this->totalActivities }}
            activities completed
        </div>

        @if ($this->isCompleted)
            <p>Lesson completed 🎉</p>
        @endif
    </header>

    <section>
        <h2>Activities</h2>

        @forelse ($lesson->activities as $activity)
            <article>
                <h3>{{ $activity->title }}</h3>

                @if ($activity->activity_type)
                    <p>{{ $activity->activity_type }}</p>
                @endif

                <a href="{{ route('learn.activity', $activity) }}">
                    Start activity
                </a>
            </article>
        @empty
            <p>No activities are available for this lesson yet.</p>
        @endforelse
    </section>
</div>