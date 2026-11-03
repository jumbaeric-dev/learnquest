<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    /**
     * Starter skill catalogue aligned with the 12 LearnQuest worlds.
     *
     * Keys are skill names; values are concise descriptions.
     */
    private const SKILLS = [
        // 1. Self-Discovery
        'Self-Awareness' => 'Recognise personal thoughts, feelings, preferences, and behaviours.',
        'Strengths Recognition' => 'Identify personal strengths and understand how to develop them.',
        'Values and Identity' => 'Explore personal values, interests, identity, and individual differences.',
        'Confidence and Self-Esteem' => 'Develop a realistic, respectful sense of personal worth and capability.',
        'Emotional Awareness' => 'Recognise and name emotions in oneself and understand their signals.',

        // 2. Growth and Habits
        'Self-Regulation' => 'Practise managing reactions and choosing helpful responses.',
        'Goal Setting' => 'Set meaningful, achievable goals and identify steps towards them.',
        'Habit Building' => 'Build and maintain helpful routines through consistent practice.',
        'Focus and Attention' => 'Practise directing attention and returning to a task after distractions.',
        'Time Management' => 'Plan activities and use time thoughtfully according to age and ability.',
        'Resilience and Adaptability' => 'Respond to setbacks, learn from experience, and adapt to change.',

        // 3. People and Relationships
        'Empathy' => 'Consider other people’s feelings, perspectives, and experiences.',
        'Active Listening' => 'Listen carefully, ask clarifying questions, and respond thoughtfully.',
        'Clear Communication' => 'Express thoughts, needs, and ideas clearly and respectfully.',
        'Teamwork and Collaboration' => 'Share responsibilities and work constructively towards common goals.',
        'Healthy Boundaries' => 'Recognise personal limits, respect others’ limits, and seek help when needed.',
        'Conflict Resolution' => 'Address disagreements through respectful discussion and safe problem-solving.',

        // 4. Critical Thinking
        'Curiosity and Questioning' => 'Ask meaningful questions and investigate how things work.',
        'Critical Thinking' => 'Examine claims, assumptions, and possible explanations before reaching conclusions.',
        'Problem Solving' => 'Define a problem, explore options, and test possible solutions.',
        'Decision Making' => 'Compare options and consequences before making a considered choice.',
        'Evidence and Information Literacy' => 'Distinguish evidence from opinion and assess information carefully.',
        'Creative Thinking' => 'Generate varied ideas and explore new approaches to challenges.',

        // 5. Digital Life and Safety
        'Digital Literacy' => 'Understand common digital tools and use them thoughtfully.',
        'Online Privacy and Security' => 'Recognise private information and practise age-appropriate online safety.',
        'Responsible Digital Citizenship' => 'Interact respectfully and responsibly in digital spaces.',
        'Media and Misinformation Awareness' => 'Question online claims and practise checking information with reliable sources.',

        // 6. Money and Financial Wisdom
        'Money Awareness' => 'Understand the purpose of money and common ways people use it.',
        'Needs and Wants' => 'Distinguish essential needs from wants in different situations.',
        'Saving and Budgeting' => 'Plan how to set aside and allocate limited resources.',
        'Spending Decisions' => 'Compare choices and consider value, priorities, and consequences.',
        'Financial Responsibility' => 'Practise responsible everyday money decisions and recognise when to seek trusted guidance.',

        // 7. Entrepreneurship and Innovation
        'Opportunity Recognition' => 'Notice needs, challenges, and opportunities to help others.',
        'Value Creation' => 'Develop ideas, services, or solutions that provide useful benefits.',
        'Design Thinking' => 'Understand needs, imagine solutions, create simple prototypes, and improve them.',
        'Planning and Experimentation' => 'Plan manageable steps, test ideas, and learn from results.',
        'Entrepreneurial Responsibility' => 'Consider fairness, safety, honesty, and community impact when developing ideas.',

        // 8. AI and Future Intelligence
        'AI Literacy' => 'Understand basic concepts about AI systems and their possible uses.',
        'Data Awareness' => 'Recognise how data can be collected, represented, and used to produce results.',
        'Responsible AI Use' => 'Use AI tools thoughtfully, protect private information, and follow appropriate guidance.',
        'AI Limitations and Bias Awareness' => 'Recognise that AI can make mistakes and produce incomplete or biased results.',

        // 9. Coding, Robotics and Engineering
        'Computational Thinking' => 'Break problems into manageable parts and identify patterns and steps.',
        'Coding Fundamentals' => 'Learn foundational programming concepts through age-appropriate examples.',
        'Algorithms and Sequencing' => 'Create and follow ordered instructions to achieve a goal.',
        'Debugging and Testing' => 'Find errors, test solutions, and improve instructions or designs.',
        'Engineering Design' => 'Design, build, test, and refine solutions to practical challenges.',
        'Robotics Fundamentals' => 'Explore how robots use instructions, sensors, and components to perform tasks.',

        // 10. Science, Nature and the Universe
        'Scientific Inquiry' => 'Ask testable questions and investigate them systematically.',
        'Observation and Measurement' => 'Observe carefully and use appropriate ways to compare or measure.',
        'Scientific Reasoning' => 'Use observations and evidence to develop and evaluate explanations.',
        'Environmental Awareness' => 'Understand relationships between people, nature, resources, and the environment.',
        'Systems Thinking' => 'Explore how parts interact within a larger system.',

        // 11. Healthy and Practical Life
        'Personal Hygiene' => 'Practise age-appropriate everyday hygiene routines.',
        'Healthy Everyday Choices' => 'Explore everyday habits that support wellbeing without shame or unrealistic expectations.',
        'Personal Safety Awareness' => 'Recognise unsafe situations, understand personal boundaries, and seek trusted help.',
        'Practical Life Skills' => 'Build age-appropriate everyday skills that support independence and shared responsibility.',
        'Help-Seeking and Safety Communication' => 'Explain concerns clearly and approach trusted adults or appropriate services for help.',

        // 12. Leadership, Citizenship and Global Futures
        'Leadership and Responsibility' => 'Take responsibility, support others, and contribute constructively to shared goals.',
        'Civic Awareness' => 'Explore community roles, shared responsibilities, and how decisions affect people.',
        'Inclusion and Respect' => 'Treat people fairly and respect differences in experiences and perspectives.',
        'Community Participation' => 'Identify practical, age-appropriate ways to contribute to a community.',
        'Global Awareness' => 'Explore how people, places, cultures, and global challenges are connected.',
    ];

    public function run(): void
    {
        $createdOrUpdated = 0;

        foreach (self::SKILLS as $name => $description) {
            $slug = Str::slug($name);

            Skill::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'is_active' => true,
                ]
            );

            $createdOrUpdated++;
        }

        $this->command?->info(
            "LearnQuest skills seeded or updated: {$createdOrUpdated}."
        );
    }
}
