<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Skill;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class LearnQuestCurriculumSeeder extends Seeder
{
    /**
     * Fresh, self-contained curriculum seed data.
     *
     * This seeder does not call the legacy course, module, lesson,
     * or activity seeders.
     *
     * All generated curriculum remains unpublished until reviewed.
     */
    private const AGE_BANDS = [
        '3-5' => [
            'label' => 'Little Explorers',
            'approach' => 'Use simple words, familiar examples, short activities, and adult-supported practice.',
            'minutes' => 5,
            'xp' => 5,
        ],
        '6-9' => [
            'label' => 'Curious Explorers',
            'approach' => 'Use practical examples, guided choices, and short independent tasks.',
            'minutes' => 8,
            'xp' => 10,
        ],
        '10-14' => [
            'label' => 'Independent Explorers',
            'approach' => 'Use realistic scenarios, reasoning, reflection, and responsible decision-making.',
            'minutes' => 12,
            'xp' => 15,
        ],
    ];

    private const AGE_SPECIFIC_COURSE_DESCRIPTIONS = [
        '3-5' => 'Notice what you enjoy, name simple feelings, and build confidence through small supported steps.',
        '6-9' => 'Explore your interests and strengths, practise naming feelings, and build confidence through practice.',
        '10-14' => 'Reflect on identity, strengths, emotions, and the strategies that help confidence develop.',
    ];

    private const AGE_SPECIFIC_WORLD_COURSE_DESCRIPTIONS = [
        'growth-habits' => [
            '3-5' => 'Practise tiny steps, repeat helpful routines, and learn to try again with support.',
            '6-9' => 'Set simple goals, practise routines, focus on one task, and learn from mistakes.',
            '10-14' => 'Plan realistic goals, evaluate routines, protect attention, and adapt strategies after setbacks.',
        ],
    ];

    /**
     * Age-specific learner-facing content for the first complete world.
     *
     * Lesson titles and skill mappings remain stable so the existing
     * curriculum relationships and progress records can be preserved.
     */
    private const AGE_SPECIFIC_CONTENT = [
        'self-discovery' => [
            'What Makes Me Unique?' => [
                '3-5' => [
                    'story' => "Mika likes building towers with blocks. Noor likes drawing pictures. They can play together even though they enjoy different things.",
                    'question' => 'What can make you special?',
                    'options' => [
                        'Things you enjoy and the things you are learning',
                        'Having more toys than a friend',
                        'Doing every activity exactly like others',
                    ],
                    'answer' => 0,
                ],
                '6-9' => [
                    'story' => "During a group project, Jo likes drawing the plan while Lee enjoys sorting the pieces. They notice that different interests and ideas can help the group.",
                    'question' => 'Two friends enjoy different activities. What is a helpful way to think about that?',
                    'options' => [
                        'One friend must choose the other friend’s favourite activity',
                        'Different interests and experiences are part of what makes each person unique',
                        'The friend with the most interests is always better',
                    ],
                    'answer' => 1,
                ],
                '10-14' => [
                    'story' => "A project team discovers that people approach challenges differently. One member sketches possibilities, another notices details, and another asks questions. These differences add perspectives without making anyone more valuable than someone else.",
                    'question' => 'Which statement best describes personal uniqueness?',
                    'options' => [
                        'A person’s worth depends on being better than their peers',
                        'People are unique only when they never change',
                        'Interests, experiences, values, and perspectives can shape how each person contributes',
                    ],
                    'answer' => 2,
                ],
            ],

            'Discovering My Strengths' => [
                '3-5' => [
                    'story' => "Ravi likes helping put toys away and is practising drawing circles. A grown-up helps Ravi notice both things he can do and things he is still learning.",
                    'question' => 'How can you learn about a strength?',
                    'options' => [
                        'Only do things that are already perfect',
                        'Try activities and notice what you enjoy or practise',
                        'Decide you are bad at something after one try',
                    ],
                    'answer' => 1,
                ],
                '6-9' => [
                    'story' => "After a class activity, Amina notices she explains game rules clearly. A teammate says she was patient when helping, and Amina also chooses to practise reading instructions.",
                    'question' => 'Which is a useful way to identify your strengths?',
                    'options' => [
                        'Look for examples of what you do and ask for respectful feedback',
                        'Count only skills you are already best at',
                        'Ignore feedback that could help you improve',
                    ],
                    'answer' => 0,
                ],
                '10-14' => [
                    'story' => "After a team project, Kai reviews what helped the team: organising tasks, asking questions, and listening to others. Kai asks a trusted person for feedback and chooses one skill to develop further.",
                    'question' => 'What can give you a more balanced picture of your strengths?',
                    'options' => [
                        'Assuming strengths can never change',
                        'Comparing your whole life with other people’s best moments',
                        'Reflecting on evidence, feedback, and skills you can continue developing',
                    ],
                    'answer' => 2,
                ],
            ],

            'Naming My Feelings' => [
                '3-5' => [
                    'story' => "Before meeting a new group, Ella feels excited and a little worried. Ella uses those feeling words with a trusted grown-up, who listens.",
                    'question' => 'What can naming a feeling help you do?',
                    'options' => [
                        'Know exactly what everyone else feels',
                        'Tell someone how you feel and ask for help',
                        'Make every feeling disappear',
                    ],
                    'answer' => 1,
                ],
                '6-9' => [
                    'story' => "Before reading in front of the class, Imani notices a fast heartbeat and butterflies in her tummy. She names the feeling as nervous and practises with someone she trusts.",
                    'question' => 'When you notice a strong feeling, what is a helpful first step?',
                    'options' => [
                        'Notice and name it without calling yourself bad',
                        'Pretend feelings never happen',
                        'Act immediately without thinking',
                    ],
                    'answer' => 0,
                ],
                '10-14' => [
                    'story' => "After receiving critical feedback, Alex feels disappointed and defensive. Alex names both feelings, pauses, then considers which feedback is useful and what question to ask next.",
                    'question' => 'How can recognising an emotion support a decision?',
                    'options' => [
                        'It means your first reaction must always be obeyed',
                        'It proves your first interpretation is always correct',
                        'It gives you information to consider before choosing how to respond',
                    ],
                    'answer' => 2,
                ],
            ],

            'Growing My Confidence' => [
                '3-5' => [
                    'story' => "Ari tries to stack three blocks. The tower falls, so Ari takes a breath, asks for help, and tries with two blocks first.",
                    'question' => 'When something new feels hard, what can you do?',
                    'options' => [
                        'Try a small step and ask for help',
                        'Decide you can never learn',
                        'Hide every mistake',
                    ],
                    'answer' => 0,
                ],
                '6-9' => [
                    'story' => "Nia is learning a new skipping pattern. She practises a few jumps, asks a friend to show the rhythm, and notices that each try helps her learn.",
                    'question' => 'Which thought can help confidence grow?',
                    'options' => [
                        'I must get it right the first time',
                        'I can practise a small step, learn, and ask for help',
                        'If I need help, I cannot succeed',
                    ],
                    'answer' => 1,
                ],
                '10-14' => [
                    'story' => "Sam is preparing a short presentation and feels uncertain. Sam breaks preparation into steps, rehearses with a trusted person, and uses specific feedback to improve.",
                    'question' => 'What is a realistic view of confidence?',
                    'options' => [
                        'Confident people never doubt themselves',
                        'Confidence depends on getting everything right immediately',
                        'Confidence can grow through preparation, practice, feedback, and learning from setbacks',
                    ],
                    'answer' => 2,
                ],
            ],
        ],
        'growth-habits' => [
            'Choosing a Small Goal' => [
                '3-5' => [
                    'story' => 'Lulu wants to put away toys after playing. With help from a grown-up, Lulu chooses one small step: put the blocks in their basket before snack time.',
                    'question' => 'What is a good first step toward a goal?',
                    'options' => [
                        'Choose one small step to try',
                        'Do every task all at once',
                        'Wait until the task becomes easy without practising',
                    ],
                    'answer' => 0,
                ],
                '6-9' => [
                    'story' => 'Rafi wants to read more often. He plans to read two pages after dinner on four days this week, then checks how the plan went.',
                    'question' => 'Which is the clearest starting goal?',
                    'options' => [
                        'Become the best reader in the world tomorrow',
                        'Read two pages after dinner and review progress',
                        'Read only when someone happens to remind you',
                    ],
                    'answer' => 1,
                ],
                '10-14' => [
                    'story' => 'Nia wants to improve her maths skills. She plans three short practice sessions each week, records the questions she finds difficult, and reviews her progress after two weeks.',
                    'question' => 'Which plan makes progress easier to evaluate?',
                    'options' => [
                        'Decide to improve without choosing any actions',
                        'Study for many hours once and never review the result',
                        'Choose regular practice sessions and check what improves',
                    ],
                    'answer' => 2,
                ],
            ],

            'Learning Through Practice' => [
                '3-5' => [
                    'story' => 'Pip is learning to fasten a shoe. It is tricky at first, so a grown-up shows one step. Pip practises that step before trying the next one.',
                    'question' => 'What can you do when a new skill feels hard?',
                    'options' => [
                        'Try a small part again and ask for help',
                        'Give up because the first try was difficult',
                        'Expect to do everything perfectly immediately',
                    ],
                    'answer' => 0,
                ],
                '6-9' => [
                    'story' => 'Maya is learning multiplication. When one answer is wrong, she checks the steps, asks a question, and tries another problem using what she learned.',
                    'question' => 'Why can practice help you learn?',
                    'options' => [
                        'Practice means you will never make mistakes',
                        'You can notice mistakes and learn better ways to solve problems',
                        'You should keep using the same method even when it does not help',
                    ],
                    'answer' => 1,
                ],
                '10-14' => [
                    'story' => 'When his coding project fails a test, Daniel records the error, changes one part of the code, and tests it again. He asks for specific feedback when he cannot find the problem.',
                    'question' => 'When an approach does not work, what is a useful response?',
                    'options' => [
                        'Hide the result so nobody can help',
                        'Change everything at once without checking what happened',
                        'Examine the result, adjust a step, and test the change',
                    ],
                    'answer' => 2,
                ],
            ],

            'Building a Helpful Habit' => [
                '3-5' => [
                    'story' => 'After playtime, Timo puts crayons back in their box. A picture reminder near the shelf helps Timo remember what comes next.',
                    'question' => 'What can help you remember a helpful routine?',
                    'options' => [
                        'A simple reminder beside the place where you do the task',
                        'Changing the routine every time',
                        'Waiting until you never need help',
                    ],
                    'answer' => 0,
                ],
                '6-9' => [
                    'story' => 'Zuri wants to remember her reading time. She puts her book where she can see it and tries reading after dinner. At the end of the week, she thinks about what helped.',
                    'question' => 'Which action can make a new routine easier to repeat?',
                    'options' => [
                        'Expecting to remember without any plan',
                        'Connecting the routine to a regular part of the day',
                        'Stopping whenever one day does not go as planned',
                    ],
                    'answer' => 1,
                ],
                '10-14' => [
                    'story' => 'Amir wants to start homework with less last-minute stress. He prepares his materials, chooses a usual start time, and tracks which changes help. When the schedule changes, he adjusts the plan instead of treating one missed day as failure.',
                    'question' => 'What is a practical way to develop a sustainable habit?',
                    'options' => [
                        'Follow a rigid routine even when it no longer fits your needs',
                        'Expect motivation to be equally strong every day',
                        'Use a helpful cue, repeat the action, and review what needs adjusting',
                    ],
                    'answer' => 2,
                ],
            ],

            'Giving a Task My Attention' => [
                '3-5' => [
                    'story' => 'Beni is sorting shapes into a puzzle. Beni puts a noisy toy aside, works on a few shapes, and takes a break with a grown-up when needed.',
                    'question' => 'What might help you focus on a simple activity?',
                    'options' => [
                        'Play with every toy at the same time',
                        'Choose one activity and put away distractions',
                        'Keep working even when you need help or a break',
                    ],
                    'answer' => 1,
                ],
                '6-9' => [
                    'story' => 'During homework, Asha notices that messages and toys keep taking her attention away. She puts them aside, completes a few questions, then takes a short break.',
                    'question' => 'Which strategy can help you give a task your attention?',
                    'options' => [
                        'Keep switching between unrelated activities',
                        'Ignore every need for rest',
                        'Reduce distractions and work on one manageable part',
                    ],
                    'answer' => 2,
                ],
                '10-14' => [
                    'story' => 'Leo has a project due next week. Instead of keeping every tab open and switching tasks repeatedly, Leo defines the next step, silences non-essential notifications, and chooses a manageable work period followed by a break.',
                    'question' => 'Which approach supports focused work realistically?',
                    'options' => [
                        'Define the next action, reduce distractions, and plan breaks',
                        'Assume concentration requires never taking a break',
                        'Keep every notification active to practise ignoring it',
                    ],
                    'answer' => 0,
                ],
            ],
        ],
    ];

    /**
     * Each world contains two modules, with two lessons per module.
     * Each lesson receives a story activity and a quiz activity.
     */
    private const WORLDS = [
        'self-discovery' => [
            'title' => 'Self-Discovery World',
            'courses' => [
                [
                    'title' => 'Getting to Know Myself',
                    'description' => 'Explore personal identity, interests, strengths, feelings, and values.',
                    'modules' => [
                        [
                            'title' => 'My Unique Self',
                            'lessons' => [
                                [
                                    'title' => 'What Makes Me Unique?',
                                    'story' => 'A young explorer notices that friends can enjoy different activities and still learn together. Each person has interests, experiences, and qualities that make them unique.',
                                    'question' => 'What makes people unique?',
                                    'options' => [
                                        'Only the things they own',
                                        'Their interests, experiences, and qualities',
                                        'Being better than everyone else',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Values and Identity',
                                ],
                                [
                                    'title' => 'Discovering My Strengths',
                                    'story' => 'An explorer asks trusted people what they do well. They discover that helping, listening, drawing, building, and practising are all valuable strengths.',
                                    'question' => 'What is a helpful way to discover a strength?',
                                    'options' => [
                                        'Notice what you practise and ask for kind feedback',
                                        'Compare yourself with everyone',
                                        'Give up when something is difficult',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Strengths Recognition',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Feelings and Confidence',
                            'lessons' => [
                                [
                                    'title' => 'Naming My Feelings',
                                    'story' => 'Before a new activity, an explorer feels excited and a little nervous. Naming both feelings helps them explain what is happening inside.',
                                    'question' => 'What can naming a feeling help us do?',
                                    'options' => [
                                        'Make every feeling disappear',
                                        'Understand and communicate how we feel',
                                        'Hide every feeling from others',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Emotional Awareness',
                                ],
                                [
                                    'title' => 'Growing My Confidence',
                                    'story' => 'An explorer learns a new skill by trying small steps, asking for help, and practising. Confidence grows through learning rather than being perfect.',
                                    'question' => 'What can help confidence grow?',
                                    'options' => [
                                        'Practising and learning from mistakes',
                                        'Never trying anything new',
                                        'Expecting to succeed immediately',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Confidence and Self-Esteem',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'growth-habits' => [
            'title' => 'Growth & Habits World',
            'courses' => [
                [
                    'title' => 'Small Steps, Big Growth',
                    'description' => 'Practise goal setting, useful routines, focus, and resilience.',
                    'modules' => [
                        [
                            'title' => 'Goals and Practice',
                            'lessons' => [
                                [
                                    'title' => 'Choosing a Small Goal',
                                    'story' => 'An explorer wants to learn something new. They choose one small step they can practise regularly and check their progress.',
                                    'question' => 'What makes a useful first goal?',
                                    'options' => [
                                        'A small, clear step you can practise',
                                        'A goal you never review',
                                        'Trying to do everything at once',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Goal Setting',
                                ],
                                [
                                    'title' => 'Learning Through Practice',
                                    'story' => 'A new skill feels difficult at first. The explorer practises, notices what changed, and tries a different approach when needed.',
                                    'question' => 'What should you do when practice is difficult?',
                                    'options' => [
                                        'Decide you can never learn',
                                        'Keep learning and adjust your approach',
                                        'Pretend you already know everything',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Resilience and Adaptability',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Habits and Focus',
                            'lessons' => [
                                [
                                    'title' => 'Building a Helpful Habit',
                                    'story' => 'An explorer places their reading book beside their usual study space. This reminder makes it easier to repeat a helpful routine.',
                                    'question' => 'What can help a habit become easier to repeat?',
                                    'options' => [
                                        'A useful reminder and regular practice',
                                        'Changing the plan every minute',
                                        'Waiting until you never make mistakes',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Habit Building',
                                ],
                                [
                                    'title' => 'Giving a Task My Attention',
                                    'story' => 'An explorer puts away unnecessary distractions, chooses one task, and takes a short break when needed.',
                                    'question' => 'Which action can help with focus?',
                                    'options' => [
                                        'Doing many unrelated tasks at once',
                                        'Choosing one task and reducing distractions',
                                        'Ignoring every need for rest',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Focus and Attention',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'people-relationships' => [
            'title' => 'People & Relationships World',
            'courses' => [
                [
                    'title' => 'Understanding and Working with Others',
                    'description' => 'Build empathy, listening, communication, boundaries, and teamwork.',
                    'modules' => [
                        [
                            'title' => 'Listening and Empathy',
                            'lessons' => [
                                [
                                    'title' => 'Listening to Understand',
                                    'story' => 'Two friends disagree about a game. One listens without interrupting, asks a question, and explains what they heard.',
                                    'question' => 'What is a helpful way to listen?',
                                    'options' => [
                                        'Interrupt before the other person finishes',
                                        'Listen carefully and check your understanding',
                                        'Assume you already know everything',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Active Listening',
                                ],
                                [
                                    'title' => 'Seeing Another Perspective',
                                    'story' => 'A classmate looks upset after being left out. An explorer wonders how the classmate might feel and invites them to join a suitable activity.',
                                    'question' => 'What does empathy help us practise?',
                                    'options' => [
                                        'Trying to understand another person’s feelings',
                                        'Deciding that everyone feels the same',
                                        'Ignoring someone who is upset',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Empathy',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Teamwork and Boundaries',
                            'lessons' => [
                                [
                                    'title' => 'Solving Problems Together',
                                    'story' => 'A team has different ideas for a shared project. Members take turns speaking, compare ideas, and agree on a plan.',
                                    'question' => 'What supports good teamwork?',
                                    'options' => [
                                        'One person making every decision',
                                        'Sharing ideas and listening respectfully',
                                        'Refusing to consider other ideas',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Teamwork and Collaboration',
                                ],
                                [
                                    'title' => 'Respecting Personal Boundaries',
                                    'story' => 'An explorer says they do not want to join a game. A respectful friend accepts the answer. Children can ask a trusted adult for help when a boundary is ignored or they feel unsafe.',
                                    'question' => 'What should happen when someone says no to a reasonable personal boundary?',
                                    'options' => [
                                        'Their boundary should be respected',
                                        'They should be pressured until they agree',
                                        'Their feelings should always be dismissed',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Healthy Boundaries',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'critical-thinking' => [
            'title' => 'Critical Thinking & Discovery World',
            'courses' => [
                [
                    'title' => 'Curious Minds and Better Decisions',
                    'description' => 'Ask useful questions, solve problems, and check evidence.',
                    'modules' => [
                        [
                            'title' => 'Questions and Evidence',
                            'lessons' => [
                                [
                                    'title' => 'Asking Useful Questions',
                                    'story' => 'An explorer sees a plant growing toward a window and wonders why. They ask questions and plan a safe way to observe the plant over time.',
                                    'question' => 'What is a useful first step when investigating something?',
                                    'options' => [
                                        'Ask a clear question',
                                        'Decide the answer without checking',
                                        'Ignore what you observe',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Curiosity and Questioning',
                                ],
                                [
                                    'title' => 'Checking Information',
                                    'story' => 'A surprising claim appears in a message. An explorer checks who made the claim, looks for reliable supporting information, and asks a trusted adult when unsure.',
                                    'question' => 'How can you check a surprising claim?',
                                    'options' => [
                                        'Share it immediately',
                                        'Check the source and look for evidence',
                                        'Believe it because it sounds exciting',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Evidence and Information Literacy',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Problems and Possibilities',
                            'lessons' => [
                                [
                                    'title' => 'Breaking a Problem into Steps',
                                    'story' => 'A group must organise materials for a project. They list what is needed, sort the tasks, and complete one step at a time.',
                                    'question' => 'What can make a large problem easier to manage?',
                                    'options' => [
                                        'Break it into smaller steps',
                                        'Avoid looking at the problem',
                                        'Change every step at the same time',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Problem Solving',
                                ],
                                [
                                    'title' => 'Thinking of Different Ideas',
                                    'story' => 'A team needs to reuse a cardboard box. Members suggest several safe possibilities, then compare which ideas fit their goal.',
                                    'question' => 'What is creative thinking useful for?',
                                    'options' => [
                                        'Finding and exploring different possibilities',
                                        'Rejecting every new idea immediately',
                                        'Copying without understanding',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Creative Thinking',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'digital-life-safety' => [
            'title' => 'Digital Life & Safety World',
            'courses' => [
                [
                    'title' => 'Being Safe and Responsible Online',
                    'description' => 'Learn privacy, respectful digital behaviour, and careful information sharing.',
                    'modules' => [
                        [
                            'title' => 'Privacy and Protection',
                            'lessons' => [
                                [
                                    'title' => 'Keeping Personal Information Private',
                                    'story' => 'A game asks an explorer for personal details. The explorer stops and checks with a trusted adult before sharing information.',
                                    'question' => 'What should a child do when an unfamiliar app requests personal information?',
                                    'options' => [
                                        'Share everything immediately',
                                        'Check with a trusted adult first',
                                        'Send the information to strangers',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Online Privacy and Security',
                                ],
                                [
                                    'title' => 'Creating Safer Accounts',
                                    'story' => 'A family helps a child use appropriate account protections and keep passwords private. The child learns never to share passwords in chats.',
                                    'question' => 'Which is a safer password habit?',
                                    'options' => [
                                        'Share your password with online strangers',
                                        'Keep your password private and ask an adult for help',
                                        'Use someone else’s account without permission',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Digital Literacy',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Respect and Information',
                            'lessons' => [
                                [
                                    'title' => 'Being Kind Online',
                                    'story' => 'Before posting a comment, an explorer considers whether it is respectful. If a message is hurtful or threatening, they do not retaliate and seek help from a trusted adult.',
                                    'question' => 'What is responsible online behaviour?',
                                    'options' => [
                                        'Post hurtful messages for fun',
                                        'Communicate respectfully and seek help when needed',
                                        'Share private messages without permission',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Responsible Digital Citizenship',
                                ],
                                [
                                    'title' => 'Not Everything Online Is True',
                                    'story' => 'Two websites make different claims. An explorer compares their sources, checks the date, and asks a trusted adult for help evaluating confusing information.',
                                    'question' => 'What should you do with information that may be misleading?',
                                    'options' => [
                                        'Check its source and compare reliable information',
                                        'Assume every post is correct',
                                        'Forward it before reading it',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Media and Misinformation Awareness',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'money-financial-wisdom' => [
            'title' => 'Money & Financial Wisdom World',
            'courses' => [
                [
                    'title' => 'Making Thoughtful Money Choices',
                    'description' => 'Explore needs, wants, saving, budgeting, and responsible decisions.',
                    'modules' => [
                        [
                            'title' => 'Needs and Choices',
                            'lessons' => [
                                [
                                    'title' => 'Needs, Wants, and Priorities',
                                    'story' => 'An explorer has a limited amount to spend at a market. They first consider essential needs, then decide whether a wanted extra fits their available money.',
                                    'question' => 'Which statement best describes a need?',
                                    'options' => [
                                        'Something essential for wellbeing in a situation',
                                        'Every new item advertised online',
                                        'Anything a friend happens to own',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Needs and Wants',
                                ],
                                [
                                    'title' => 'Planning Before Spending',
                                    'story' => 'An explorer compares prices and checks how much money is available before choosing an item. They remember that prices and household priorities can differ.',
                                    'question' => 'Why is it useful to check your available money before buying?',
                                    'options' => [
                                        'It helps you make an informed spending decision',
                                        'It guarantees every purchase is necessary',
                                        'It means you never need to compare choices',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Spending Decisions',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Saving and Planning',
                            'lessons' => [
                                [
                                    'title' => 'Saving for a Goal',
                                    'story' => 'An explorer wants a book that costs more than they have today. With caregiver guidance, they set a savings goal and plan how to build toward it.',
                                    'question' => 'What is one purpose of saving?',
                                    'options' => [
                                        'To work toward a future goal',
                                        'To guarantee that money always grows',
                                        'To avoid planning for needs',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Saving and Budgeting',
                                ],
                                [
                                    'title' => 'Making a Simple Budget',
                                    'story' => 'An explorer lists the money available for a small project and the expected costs. They check whether the plan fits before deciding what to do.',
                                    'question' => 'What does a simple budget help you compare?',
                                    'options' => [
                                        'Available money and planned costs',
                                        'Your worth as a person',
                                        'Whether every advertisement is trustworthy',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Financial Responsibility',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'entrepreneurship-innovation' => [
            'title' => 'Entrepreneurship & Innovation World',
            'courses' => [
                [
                    'title' => 'Ideas That Create Value',
                    'description' => 'Discover needs, develop ideas, plan projects, and test improvements.',
                    'modules' => [
                        [
                            'title' => 'Finding Opportunities',
                            'lessons' => [
                                [
                                    'title' => 'Noticing Needs Around Us',
                                    'story' => 'A group notices that classmates struggle to organise shared supplies. They ask what would help before suggesting a solution.',
                                    'question' => 'What is a good first step when developing a useful idea?',
                                    'options' => [
                                        'Understand the need you want to address',
                                        'Build something without asking anyone',
                                        'Assume every idea will work',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Opportunity Recognition',
                                ],
                                [
                                    'title' => 'Creating Value for Others',
                                    'story' => 'A team designs a simple organiser and asks users whether it helps. They improve the design using the feedback they receive.',
                                    'question' => 'What helps an idea create value?',
                                    'options' => [
                                        'It addresses a real need in a useful way',
                                        'It must be expensive',
                                        'It should never change after the first attempt',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Value Creation',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Planning and Testing',
                            'lessons' => [
                                [
                                    'title' => 'Planning a Small Project',
                                    'story' => 'A team defines its goal, lists materials, assigns tasks, and checks what resources are available before beginning.',
                                    'question' => 'What belongs in a simple project plan?',
                                    'options' => [
                                        'A goal, tasks, and needed resources',
                                        'Only a wish that everything works',
                                        'A promise to avoid all feedback',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Planning and Experimentation',
                                ],
                                [
                                    'title' => 'Learning from a Test',
                                    'story' => 'A prototype does not work as expected. The team records what happened, makes a change, and tests again safely.',
                                    'question' => 'What can a test teach an inventor?',
                                    'options' => [
                                        'Whether an idea works as expected and what to improve',
                                        'That every first design is perfect',
                                        'That feedback should always be ignored',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Design Thinking',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'ai-future-intelligence' => [
            'title' => 'AI & Future Intelligence World',
            'courses' => [
                [
                    'title' => 'Understanding Artificial Intelligence',
                    'description' => 'Learn what AI can do, where it can fail, and how to use it responsibly.',
                    'modules' => [
                        [
                            'title' => 'What AI Can and Cannot Do',
                            'lessons' => [
                                [
                                    'title' => 'Recognising AI Tools',
                                    'story' => 'An explorer uses an AI tool that suggests ideas for a story. They remember that the tool generates responses from patterns and may still make mistakes.',
                                    'question' => 'Which statement about AI is most accurate?',
                                    'options' => [
                                        'AI tools can make mistakes',
                                        'AI answers are always correct',
                                        'AI always understands people exactly as humans do',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'AI Literacy',
                                ],
                                [
                                    'title' => 'Checking AI Answers',
                                    'story' => 'An AI tool gives an answer that seems surprising. The explorer checks reliable sources and asks a teacher or caregiver before relying on it.',
                                    'question' => 'What should you do before relying on an important AI answer?',
                                    'options' => [
                                        'Accept it without checking',
                                        'Verify it with trustworthy sources or a knowledgeable adult',
                                        'Assume the answer cannot contain errors',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'AI Limitations and Bias Awareness',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Responsible AI Use',
                            'lessons' => [
                                [
                                    'title' => 'Protecting Privacy When Using AI',
                                    'story' => 'An explorer wants help with a school task. They avoid entering passwords, private family details, or identifying information and check the tool with a trusted adult.',
                                    'question' => 'What is a safer way to use an AI tool?',
                                    'options' => [
                                        'Enter private information to get better answers',
                                        'Avoid sharing sensitive information and follow adult guidance',
                                        'Share another person’s private details without permission',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Responsible AI Use',
                                ],
                                [
                                    'title' => 'AI and Fairness',
                                    'story' => 'An explorer notices an AI tool gives different suggestions for similar requests. They learn that data and design choices can affect outputs.',
                                    'question' => 'Why should we think critically about AI results?',
                                    'options' => [
                                        'AI outputs may reflect errors or bias',
                                        'AI systems can never be unfair',
                                        'Every AI result represents every person equally',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Data Awareness',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'coding-robotics-engineering' => [
            'title' => 'Coding, Robotics & Engineering World',
            'courses' => [
                [
                    'title' => 'Thinking Like a Builder',
                    'description' => 'Practise sequencing, algorithms, debugging, design, and robotics concepts.',
                    'modules' => [
                        [
                            'title' => 'Computational Thinking',
                            'lessons' => [
                                [
                                    'title' => 'Putting Instructions in Order',
                                    'story' => 'An explorer writes clear steps for planting a seed. They notice that changing the order can change what happens.',
                                    'question' => 'Why does the order of instructions matter?',
                                    'options' => [
                                        'Steps can depend on what happened before',
                                        'Instructions never need an order',
                                        'The order always changes nothing',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Algorithms and Sequencing',
                                ],
                                [
                                    'title' => 'Finding and Fixing Errors',
                                    'story' => 'A simple program does not behave as expected. The explorer checks the steps one by one, finds a mistake, and tests the correction.',
                                    'question' => 'What is debugging?',
                                    'options' => [
                                        'Finding and fixing problems in a program or process',
                                        'Deleting every project immediately',
                                        'Avoiding tests after making changes',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Debugging and Testing',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Building and Designing',
                            'lessons' => [
                                [
                                    'title' => 'Design, Build, Improve',
                                    'story' => 'A team builds a paper bridge for a classroom challenge. They test it safely, observe where it bends, and improve the design.',
                                    'question' => 'What is a useful engineering process?',
                                    'options' => [
                                        'Design, build, test, and improve',
                                        'Build once and never inspect the result',
                                        'Ignore the needs of the project',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Engineering Design',
                                ],
                                [
                                    'title' => 'How Robots Follow Instructions',
                                    'story' => 'A small educational robot follows programmed instructions from sensors and controls. The explorer learns that its behaviour depends on its design and program.',
                                    'question' => 'What helps a programmable robot perform a task?',
                                    'options' => [
                                        'Instructions and suitable hardware',
                                        'Guessing without any input or program',
                                        'Understanding every situation like a human',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Robotics Fundamentals',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'science-nature-universe' => [
            'title' => 'Science, Nature & Our Universe World',
            'courses' => [
                [
                    'title' => 'Discovering How the World Works',
                    'description' => 'Explore observation, fair questions, evidence, nature, and connected systems.',
                    'modules' => [
                        [
                            'title' => 'Observation and Inquiry',
                            'lessons' => [
                                [
                                    'title' => 'Observing Carefully',
                                    'story' => 'An explorer observes a plant each day and records changes in its leaves. They separate what they actually see from what they think may explain it.',
                                    'question' => 'What is an observation?',
                                    'options' => [
                                        'Something noticed or measured',
                                        'A guess that never needs checking',
                                        'A conclusion chosen before looking',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Observation and Measurement',
                                ],
                                [
                                    'title' => 'Testing a Question',
                                    'story' => 'A group wonders whether a plant grows differently in different light conditions. With adult guidance, they plan a safe comparison and record what happens.',
                                    'question' => 'What makes a scientific investigation useful?',
                                    'options' => [
                                        'Observing carefully and recording evidence',
                                        'Changing everything without keeping notes',
                                        'Choosing the result before the test',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Scientific Inquiry',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Nature and Systems',
                            'lessons' => [
                                [
                                    'title' => 'Caring for Our Environment',
                                    'story' => 'A group notices litter near a shared outdoor space. With appropriate adult support, they learn how reducing waste and disposing of rubbish properly can help the environment.',
                                    'question' => 'Which action can help reduce waste?',
                                    'options' => [
                                        'Reuse suitable items when safe and practical',
                                        'Throw rubbish into waterways',
                                        'Assume individual choices never matter',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Environmental Awareness',
                                ],
                                [
                                    'title' => 'How Systems Connect',
                                    'story' => 'An explorer learns that plants, animals, water, and soil interact in an ecosystem. A change to one part can affect other parts too.',
                                    'question' => 'What does systems thinking help us notice?',
                                    'options' => [
                                        'How different parts can affect one another',
                                        'That everything works separately',
                                        'That one change can never affect anything else',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Systems Thinking',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'healthy-practical-life' => [
            'title' => 'Healthy Living & Practical Life World',
            'courses' => [
                [
                    'title' => 'Everyday Wellbeing and Safety',
                    'description' => 'Build healthy routines, practical independence, and help-seeking skills.',
                    'modules' => [
                        [
                            'title' => 'Healthy Everyday Routines',
                            'lessons' => [
                                [
                                    'title' => 'Everyday Hygiene',
                                    'story' => 'An explorer learns that washing hands with soap and clean running water when available, especially before eating and after using the toilet, helps reduce the spread of germs.',
                                    'question' => 'Which habit helps reduce the spread of germs?',
                                    'options' => [
                                        'Washing hands with soap at important times',
                                        'Sharing every personal hygiene item',
                                        'Never washing hands before meals',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Personal Hygiene',
                                ],
                                [
                                    'title' => 'Making Healthy Everyday Choices',
                                    'story' => 'An explorer learns that wellbeing can involve varied nutritious foods, movement, rest, hydration, and support from trusted adults. Needs differ between people.',
                                    'question' => 'Which approach supports everyday wellbeing?',
                                    'options' => [
                                        'A balanced routine suited to your needs',
                                        'Ignoring rest and thirst every day',
                                        'Assuming one routine fits everyone',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Healthy Everyday Choices',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Practical Skills and Safety',
                            'lessons' => [
                                [
                                    'title' => 'Practising Everyday Skills',
                                    'story' => 'An explorer learns to organise school materials and prepare for the next day. They ask an adult for help with tasks that are unsafe or beyond their ability.',
                                    'question' => 'What is a good way to learn a new practical skill?',
                                    'options' => [
                                        'Practise safe steps and ask for help when needed',
                                        'Attempt every risky task alone',
                                        'Never ask questions',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Practical Life Skills',
                                ],
                                [
                                    'title' => 'Knowing When to Ask for Help',
                                    'story' => 'An explorer feels unsafe in a situation. They move toward safety when possible and tell a trusted adult. If the first adult does not help, they keep seeking help from another safe adult.',
                                    'question' => 'What should you do if you feel unsafe?',
                                    'options' => [
                                        'Keep it secret no matter what',
                                        'Seek help from a trusted adult',
                                        'Assume you must solve everything alone',
                                    ],
                                    'answer' => 1,
                                    'skill' => 'Help-Seeking and Safety Communication',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'leadership-citizenship-global-futures' => [
            'title' => 'Leadership, Citizenship & Global Futures World',
            'courses' => [
                [
                    'title' => 'Making a Positive Difference',
                    'description' => 'Practise responsibility, inclusion, community participation, and global awareness.',
                    'modules' => [
                        [
                            'title' => 'Leadership and Inclusion',
                            'lessons' => [
                                [
                                    'title' => 'Leading by Helping',
                                    'story' => 'A group chooses a leader for a shared task. The leader listens to everyone, shares responsibilities, and helps the team work toward its goal.',
                                    'question' => 'What is a helpful leadership behaviour?',
                                    'options' => [
                                        'Listening and helping the group work together',
                                        'Taking all the credit',
                                        'Refusing to hear other ideas',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Leadership and Responsibility',
                                ],
                                [
                                    'title' => 'Respecting Differences',
                                    'story' => 'A group includes people with different backgrounds, abilities, and ideas. They look for fair ways for everyone to participate.',
                                    'question' => 'What does inclusion aim to support?',
                                    'options' => [
                                        'Fair opportunities to participate and belong',
                                        'Excluding people who are different',
                                        'Expecting everyone to be exactly alike',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Inclusion and Respect',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Community and the Wider World',
                            'lessons' => [
                                [
                                    'title' => 'Helping Our Community',
                                    'story' => 'Children identify a helpful community activity and discuss it with responsible adults. They choose an age-appropriate way to contribute safely.',
                                    'question' => 'What is one way to participate in a community?',
                                    'options' => [
                                        'Contribute safely to a shared activity',
                                        'Ignore other people’s needs',
                                        'Take responsibility for tasks beyond your safety or ability',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Community Participation',
                                ],
                                [
                                    'title' => 'Learning About Our Connected World',
                                    'story' => 'An explorer learns that communities may have different languages, traditions, environments, and needs. They approach these differences with curiosity and respect.',
                                    'question' => 'What is a respectful way to learn about another community?',
                                    'options' => [
                                        'Listen, ask respectful questions, and avoid assumptions',
                                        'Assume everyone lives the same way',
                                        'Decide that one community is better than all others',
                                    ],
                                    'answer' => 0,
                                    'skill' => 'Global Awareness',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    public function run(): void
    {
        // Validate the curriculum before making any database changes.
        $this->validateCurriculumDefinitions();

        // Collect every skill referenced by the curriculum.
        $requiredSkillNames = [];

        foreach (self::WORLDS as $world) {
            foreach ($world['courses'] as $courseDefinition) {
                foreach ($courseDefinition['modules'] as $moduleDefinition) {
                    foreach ($moduleDefinition['lessons'] as $lessonDefinition) {
                        $requiredSkillNames[$lessonDefinition['skill']] = true;
                    }
                }
            }
        }

        $requiredSkillNames = array_keys($requiredSkillNames);

        // A missing world should stop the seeder instead of silently
        // creating an incomplete curriculum.
        $subjects = Subject::query()
            ->whereIn('slug', array_keys(self::WORLDS))
            ->get()
            ->keyBy('slug');

        $missingWorlds = array_values(array_diff(
            array_keys(self::WORLDS),
            $subjects->keys()->all()
        ));

        if ($missingWorlds !== []) {
            throw new RuntimeException(
                'Required curriculum worlds are missing. Run SubjectSeeder first. Missing slugs: '
                    . implode(', ', $missingWorlds)
            );
        }

        // Every referenced skill must exist and be active.
        $skills = Skill::query()
            ->where('is_active', true)
            ->whereIn('name', $requiredSkillNames)
            ->get()
            ->keyBy('name');

        $missingSkills = array_values(array_diff(
            $requiredSkillNames,
            $skills->keys()->all()
        ));

        if ($missingSkills !== []) {
            throw new RuntimeException(
                'Required active curriculum skills are missing or inactive: '
                    . implode(', ', $missingSkills)
            );
        }

        DB::transaction(function () use ($subjects, $skills): void {
            foreach (self::WORLDS as $worldSlug => $world) {
                $subject = $subjects->get($worldSlug);

                foreach ($world['courses'] as $courseDefinition) {
                    foreach (self::AGE_BANDS as $ageGroup => $ageBand) {
                        $courseTitle = $courseDefinition['title']
                            . ' — '
                            . $ageBand['label'];

                        $courseSlug = Str::slug(
                            $worldSlug
                                . '-'
                                . $courseDefinition['title']
                                . '-'
                                . $ageGroup
                        );

                        $course = $this->persistRecord(
                            Course::class,
                            ['slug' => $courseSlug],
                            [
                                'subject_id' => $subject->id,
                                'title' => $courseTitle,

                                // Keep learner-facing descriptions free
                                // from internal authoring instructions.
                                'description' => $this->courseDescriptionForAge(
                                    $worldSlug,
                                    $courseDefinition['description'],
                                    $ageGroup
                                ),

                                'age_group' => $ageGroup,
                            ],
                            true
                        );

                        foreach (
                            $courseDefinition['modules']
                            as $moduleIndex => $moduleDefinition
                        ) {
                            $module = $this->persistRecord(
                                CourseModule::class,
                                [
                                    'course_id' => $course->id,
                                    'title' => $moduleDefinition['title'],
                                ],
                                [
                                    'description' => 'Module '
                                        . ($moduleIndex + 1)
                                        . ' in '
                                        . $courseTitle
                                        . '.',
                                    'position' => $moduleIndex + 1,
                                ]
                            );

                            foreach (
                                $moduleDefinition['lessons']
                                as $lessonIndex => $lessonDefinition
                            ) {
                                $ageSpecificLesson = $this->lessonDefinitionForAge(
                                    $worldSlug,
                                    $lessonDefinition,
                                    $ageGroup
                                );
                                $lesson = $this->persistRecord(
                                    Lesson::class,
                                    [
                                        'learning_module_id' => $module->id,
                                        'title' => $lessonDefinition['title'],
                                    ],
                                    [
                                        // The story is learner-facing text.
                                        // Do not append authoring guidance.
                                        'description' => $ageSpecificLesson['story'],
                                        'position' => $lessonIndex + 1,
                                        'xp_reward' => $ageBand['xp'],
                                        'estimated_minutes' => $ageBand['minutes'],
                                    ],
                                    true
                                );

                                $story = $this->persistRecord(
                                    LessonActivity::class,
                                    [
                                        'lesson_id' => $lesson->id,
                                        'title' => 'Explore: '
                                            . $lessonDefinition['title'],
                                    ],
                                    [
                                        'activity_type' => 'story',
                                        'content' => [
                                            'instructions' => $ageSpecificLesson['story'],
                                        ],
                                        'position' => 1,
                                        'xp_reward' => $ageBand['xp'],
                                    ],
                                    true
                                );

                                $quizOptions = array_map(
                                    static fn(string $option): array => [
                                        'option' => $option,
                                    ],
                                    $lessonDefinition['options']
                                );

                                $quiz = $this->persistRecord(
                                    LessonActivity::class,
                                    [
                                        'lesson_id' => $lesson->id,
                                        'title' => 'Check Your Understanding: '
                                            . $lessonDefinition['title'],
                                    ],
                                    [
                                        'activity_type' => 'quiz',
                                        'content' => [
                                            'question' => $ageSpecificLesson['question'],
                                            'options' => array_map(
                                                static fn(string $option): array => [
                                                    'option' => $option,
                                                ],
                                                $ageSpecificLesson['options']
                                            ),
                                            'answer' => $ageSpecificLesson['answer'],
                                        ],
                                        'position' => 2,
                                        'xp_reward' => $ageBand['xp'],
                                    ],
                                    true
                                );

                                $skill = $skills->get($lessonDefinition['skill']);

                                // This should already be guaranteed by the
                                // preflight check. Keep the guard for safety.
                                if (! $skill) {
                                    throw new RuntimeException(
                                        "Required curriculum skill disappeared: "
                                            . $lessonDefinition['skill']
                                    );
                                }

                                $story->skills()->syncWithoutDetaching([
                                    $skill->id => ['weight' => 1],
                                ]);

                                $quiz->skills()->syncWithoutDetaching([
                                    $skill->id => ['weight' => 1],
                                ]);
                            }
                        }
                    }
                }
            }
        });

        $this->command?->info(
            'LearnQuest curriculum seed completed. New curriculum records remain unpublished for review.'
        );
    }

    /**
     * Create or update a curriculum record.
     *
     * Existing publication status is preserved. New records for models
     * with an is_published column start unpublished.
     */
    private function persistRecord(
        string $modelClass,
        array $identity,
        array $attributes,
        bool $unpublishedOnCreate = false
    ): Model {
        $query = $modelClass::query();

        foreach ($identity as $column => $value) {
            $query->where($column, $value);
        }

        $record = $query->first();

        if ($record) {
            // Deliberately do not change is_published on existing records.
            $record->fill($attributes);
            $record->save();

            return $record;
        }

        $createAttributes = array_merge($identity, $attributes);

        if ($unpublishedOnCreate) {
            $createAttributes['is_published'] = false;
        }

        return $modelClass::query()->create($createAttributes);
    }

    /**
     * Validate the static curriculum definitions before touching the DB.
     */
    private function validateCurriculumDefinitions(): void
    {
        if (self::WORLDS === []) {
            throw new RuntimeException(
                'The curriculum must contain at least one world.'
            );
        }

        foreach (self::AGE_BANDS as $ageGroup => $ageBand) {
            $this->assertNonEmptyString(
                $ageBand['label'] ?? null,
                "Age band {$ageGroup}: label"
            );

            if (
                ! is_int($ageBand['minutes'] ?? null)
                || $ageBand['minutes'] < 1
            ) {
                throw new RuntimeException(
                    "Age band {$ageGroup} must have a positive estimated duration."
                );
            }

            if (
                ! is_int($ageBand['xp'] ?? null)
                || $ageBand['xp'] < 0
            ) {
                throw new RuntimeException(
                    "Age band {$ageGroup} must have a non-negative XP reward."
                );
            }
        }

        foreach (self::WORLDS as $worldSlug => $world) {
            $this->assertNonEmptyString(
                $world['title'] ?? null,
                "World {$worldSlug}: title"
            );

            if (
                ! is_array($world['courses'] ?? null)
                || $world['courses'] === []
            ) {
                throw new RuntimeException(
                    "World {$worldSlug} must contain at least one course."
                );
            }

            foreach ($world['courses'] as $courseIndex => $courseDefinition) {
                $coursePath = "World {$worldSlug}, course {$courseIndex}";

                $this->assertNonEmptyString(
                    $courseDefinition['title'] ?? null,
                    "{$coursePath}: title"
                );

                $this->assertNonEmptyString(
                    $courseDefinition['description'] ?? null,
                    "{$coursePath}: description"
                );

                if (
                    ! is_array($courseDefinition['modules'] ?? null)
                    || $courseDefinition['modules'] === []
                ) {
                    throw new RuntimeException(
                        "{$coursePath} must contain at least one module."
                    );
                }

                foreach (
                    $courseDefinition['modules']
                    as $moduleIndex => $moduleDefinition
                ) {
                    $modulePath = "{$coursePath}, module {$moduleIndex}";

                    $this->assertNonEmptyString(
                        $moduleDefinition['title'] ?? null,
                        "{$modulePath}: title"
                    );

                    if (
                        ! is_array($moduleDefinition['lessons'] ?? null)
                        || $moduleDefinition['lessons'] === []
                    ) {
                        throw new RuntimeException(
                            "{$modulePath} must contain at least one lesson."
                        );
                    }

                    foreach (
                        $moduleDefinition['lessons']
                        as $lessonIndex => $lessonDefinition
                    ) {
                        $lessonPath = "{$modulePath}, lesson {$lessonIndex}";

                        foreach (
                            [
                                'title',
                                'story',
                                'question',
                                'skill',
                            ] as $requiredField
                        ) {
                            $this->assertNonEmptyString(
                                $lessonDefinition[$requiredField] ?? null,
                                "{$lessonPath}: {$requiredField}"
                            );
                        }

                        $options = $lessonDefinition['options'] ?? null;

                        if (! is_array($options) || count($options) < 2) {
                            throw new RuntimeException(
                                "{$lessonPath} must have at least two quiz options."
                            );
                        }

                        foreach ($options as $optionIndex => $option) {
                            $this->assertNonEmptyString(
                                $option,
                                "{$lessonPath}: option {$optionIndex}"
                            );
                        }

                        $answer = $lessonDefinition['answer'] ?? null;

                        if (
                            ! is_int($answer)
                            || ! array_key_exists($answer, $options)
                        ) {
                            throw new RuntimeException(
                                "{$lessonPath} has an invalid quiz answer index."
                            );
                        }
                    }
                }
            }
        }

        LearnQuestAgeSpecificContent::validateCoverage(
            self::WORLDS,
            array_keys(self::AGE_BANDS)
        );
    }

    private function assertNonEmptyString(
        mixed $value,
        string $fieldPath
    ): void {
        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException(
                "Invalid curriculum definition: {$fieldPath} must be a non-empty string."
            );
        }
    }


    private function ageAdjustedText(string $text, string $ageGroup): string
    {
        $guidance = self::AGE_BANDS[$ageGroup]['approach'];

        return $text . "\n\nLearning guidance: " . $guidance;
    }

    private function courseDescriptionForAge(
        string $worldSlug,
        string $description,
        string $ageGroup
    ): string {
        return LearnQuestAgeSpecificContent::courseDescription(
            $worldSlug,
            $ageGroup
        );
    }

    private function lessonDefinitionForAge(
        string $worldSlug,
        array $lessonDefinition,
        string $ageGroup
    ): array {
        return LearnQuestAgeSpecificContent::lessonDefinitionForAge(
            $worldSlug,
            $lessonDefinition,
            $ageGroup
        );
    }
}
