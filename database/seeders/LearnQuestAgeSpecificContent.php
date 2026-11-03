<?php
namespace Database\Seeders;

use RuntimeException;

final class LearnQuestAgeSpecificContent
{
    /**
     * Each lesson variant is:
     * [story, question, [option 1, option 2, option 3], correct answer index]
     *
     * Lesson titles and skill mappings remain owned by
     * LearnQuestCurriculumSeeder. This registry owns age-specific text only.
     */
    private const COURSE_DESCRIPTIONS = [
        'self-discovery' => [
            '3-5' => 'Notice what you enjoy, name simple feelings, and build confidence through small supported steps.',
            '6-9' => 'Explore your interests and strengths, practise naming feelings, and build confidence through practice.',
            '10-14' => 'Reflect on identity, strengths, emotions, and the strategies that help confidence develop.',
        ],
        'growth-habits' => [
            '3-5' => 'Practise tiny steps, repeat helpful routines, and learn to try again with support.',
            '6-9' => 'Set simple goals, practise routines, focus on one task, and learn from mistakes.',
            '10-14' => 'Plan realistic goals, evaluate routines, protect attention, and adapt strategies after setbacks.',
        ],
        'people-relationships' => [
            '3-5' => 'Practise listening, kindness, sharing, and asking for help when boundaries are not respected.',
            '6-9' => 'Build empathy, respectful communication, teamwork, and healthy personal boundaries.',
            '10-14' => 'Apply perspective-taking, collaborative problem-solving, communication, and boundary-setting in varied situations.',
        ],
        'critical-thinking' => [
            '3-5' => 'Ask simple questions, notice details, and explore safe ways to solve small problems.',
            '6-9' => 'Compare information, break problems into steps, and try more than one idea.',
            '10-14' => 'Evaluate evidence, question assumptions, compare options, and explain reasoning.',
        ],
        'digital-life-safety' => [
            '3-5' => 'Learn simple online privacy rules and ask a trusted adult before using unfamiliar digital tools.',
            '6-9' => 'Practise privacy, account safety, respectful communication, and careful information checking.',
            '10-14' => 'Evaluate digital risks, protect personal data, respond to harmful interactions, and check online claims.',
        ],
        'money-financial-wisdom' => [
            '3-5' => 'Explore simple choices about needs, wants, saving, and asking a trusted adult before spending.',
            '6-9' => 'Practise comparing choices, planning spending, saving toward goals, and making simple budgets.',
            '10-14' => 'Evaluate priorities, compare spending plans, consider uncertainty, and plan resources responsibly.',
        ],
        'entrepreneurship-innovation' => [
            '3-5' => 'Notice what people need and explore safe ways to make, share, and improve ideas.',
            '6-9' => 'Identify needs, develop useful ideas, plan small projects, and learn from tests.',
            '10-14' => 'Investigate needs, weigh constraints, test prototypes, and improve ideas using feedback.',
        ],
        'ai-future-intelligence' => [
            '3-5' => 'Learn that some tools use AI, that they can make mistakes, and that adults help keep use safe.',
            '6-9' => 'Explore what AI can do, check its answers, protect privacy, and think about fairness.',
            '10-14' => 'Evaluate AI outputs, privacy, bias, accountability, and when human judgement is essential.',
        ],
        'coding-robotics-engineering' => [
            '3-5' => 'Explore step-by-step instructions, simple patterns, safe building, and how programmable toys follow commands.',
            '6-9' => 'Practise algorithms, debugging, testing designs, and understanding simple robots.',
            '10-14' => 'Design, test, debug, and improve programs and engineering solutions while considering constraints and safety.',
        ],
        'science-nature-universe' => [
            '3-5' => 'Notice nature, describe what you see, and explore the world with safe adult support.',
            '6-9' => 'Record observations, compare results, care for nature, and explore connected systems.',
            '10-14' => 'Distinguish observation from explanation, plan fair comparisons, evaluate evidence, and examine connected systems.',
        ],
        'healthy-practical-life' => [
            '3-5' => 'Practise simple hygiene, daily routines, safe everyday tasks, and asking a trusted adult for help.',
            '6-9' => 'Explore flexible wellbeing routines, practical independence, hygiene, and safe help-seeking.',
            '10-14' => 'Evaluate everyday wellbeing choices, practise age-appropriate independence, and seek reliable help when needed.',
        ],
        'leadership-citizenship-global-futures' => [
            '3-5' => 'Practise helping, taking turns, welcoming others, and learning about people in the community.',
            '6-9' => 'Explore fair teamwork, inclusion, community participation, and respectful curiosity about different places.',
            '10-14' => 'Practise responsible leadership, inclusive decisions, community contribution, and thoughtful global awareness.',
        ],
    ];

    private const CONTENT = [
        'self-discovery' => [
            'What Makes Me Unique?' => [
                '3-5' => [
                    'Mika likes building towers with blocks. Noor likes drawing pictures. They can play together even though they enjoy different things.',
                    'What can make you special?',
                    ['Things you enjoy and things you are learning', 'Having more toys than a friend', 'Doing every activity exactly like others'],
                    0,
                ],
                '6-9' => [
                    'During a group project, Jo likes drawing the plan while Lee enjoys sorting the pieces. Their different ideas help the group.',
                    'Two friends enjoy different activities. What is a helpful way to think about that?',
                    ['One friend must choose the other friend’s favourite activity', 'Different interests and experiences help make each person unique', 'The friend with the most interests is always better'],
                    1,
                ],
                '10-14' => [
                    'A project team approaches challenges differently. One member sketches possibilities, another notices details, and another asks questions. These perspectives can all contribute.',
                    'Which statement best describes personal uniqueness?',
                    ['A person’s worth depends on being better than their peers', 'A person is unique only if they never change', 'Interests, experiences, values, and perspectives shape how people contribute'],
                    2,
                ],
            ],
            'Discovering My Strengths' => [
                '3-5' => [
                    'Ravi likes helping put toys away and is practising drawing circles. A grown-up helps Ravi notice what he can do and what he is learning.',
                    'How can you learn about a strength?',
                    ['Only do things that are already perfect', 'Try activities and notice what you enjoy or practise', 'Decide you are bad at something after one try'],
                    1,
                ],
                '6-9' => [
                    'After a class activity, Amina notices she explains game rules clearly. A teammate says she was patient, and she chooses a new skill to practise.',
                    'Which is a useful way to identify your strengths?',
                    ['Look for examples of what you do and ask for respectful feedback', 'Count only skills you are already best at', 'Ignore feedback that could help you improve'],
                    0,
                ],
                '10-14' => [
                    'After a team project, Kai reviews what helped the team: organising tasks, asking questions, and listening. Kai requests feedback and chooses one skill to develop.',
                    'What can give you a balanced picture of your strengths?',
                    ['Assuming strengths can never change', 'Comparing your whole life with others’ best moments', 'Reflecting on evidence, feedback, and skills you can develop'],
                    2,
                ],
            ],
            'Naming My Feelings' => [
                '3-5' => [
                    'Before meeting a new group, Ella feels excited and a little worried. She uses those feeling words with a trusted grown-up, who listens.',
                    'What can naming a feeling help you do?',
                    ['Know exactly what everyone else feels', 'Tell someone how you feel and ask for help', 'Make every feeling disappear'],
                    1,
                ],
                '6-9' => [
                    'Before reading in class, Imani notices a fast heartbeat and butterflies in her tummy. She names the feeling as nervous and practises with someone she trusts.',
                    'When you notice a strong feeling, what is a helpful first step?',
                    ['Notice and name it without calling yourself bad', 'Pretend feelings never happen', 'Act immediately without thinking'],
                    0,
                ],
                '10-14' => [
                    'After receiving critical feedback, Alex feels disappointed and defensive. Alex names both feelings, pauses, and considers what is useful before responding.',
                    'How can recognising an emotion support a decision?',
                    ['It means your first reaction must always be obeyed', 'It proves your first interpretation is correct', 'It gives you information to consider before choosing a response'],
                    2,
                ],
            ],
            'Growing My Confidence' => [
                '3-5' => [
                    'Ari tries to stack three blocks. The tower falls, so Ari takes a breath, asks for help, and tries with two blocks first.',
                    'When something new feels hard, what can you do?',
                    ['Try a small step and ask for help', 'Decide you can never learn', 'Hide every mistake'],
                    0,
                ],
                '6-9' => [
                    'Nia is learning a new skipping pattern. She practises a few jumps, asks a friend to show the rhythm, and notices what helps her improve.',
                    'Which thought can help confidence grow?',
                    ['I must get it right the first time', 'I can practise a small step, learn, and ask for help', 'If I need help, I cannot succeed'],
                    1,
                ],
                '10-14' => [
                    'Sam prepares a short presentation and feels uncertain. Sam breaks preparation into steps, rehearses with a trusted person, and uses specific feedback to improve.',
                    'What is a realistic view of confidence?',
                    ['Confident people never doubt themselves', 'Confidence depends on immediate perfection', 'Confidence can grow through preparation, practice, feedback, and learning from setbacks'],
                    2,
                ],
            ],
        ],

        'growth-habits' => [
            'Choosing a Small Goal' => [
                '3-5' => [
                    'Lulu wants to put away toys after playing. With help from a grown-up, Lulu chooses one small step: put the blocks in their basket before snack time.',
                    'What is a good first step toward a goal?',
                    ['Choose one small step to try', 'Do every task all at once', 'Wait until the task becomes easy without practising'],
                    0,
                ],
                '6-9' => [
                    'Rafi wants to read more often. He plans to read two pages after dinner on four days this week, then checks how the plan went.',
                    'Which is the clearest starting goal?',
                    ['Become the best reader in the world tomorrow', 'Read two pages after dinner and review progress', 'Read only when someone happens to remind you'],
                    1,
                ],
                '10-14' => [
                    'Nia wants to improve her maths skills. She plans three short practice sessions each week, notes difficult questions, and reviews her progress after two weeks.',
                    'Which plan makes progress easier to evaluate?',
                    ['Decide to improve without choosing actions', 'Study for many hours once and never review', 'Choose regular practice sessions and check what improves'],
                    2,
                ],
            ],
            'Learning Through Practice' => [
                '3-5' => [
                    'Pip is learning to fasten a shoe. It is tricky, so a grown-up shows one step. Pip practises that step before trying the next one.',
                    'What can you do when a new skill feels hard?',
                    ['Try a small part again and ask for help', 'Give up because the first try was difficult', 'Expect to do everything perfectly immediately'],
                    0,
                ],
                '6-9' => [
                    'Maya is learning multiplication. When one answer is wrong, she checks the steps, asks a question, and tries another problem using what she learned.',
                    'Why can practice help you learn?',
                    ['Practice means you will never make mistakes', 'You can notice mistakes and learn better ways to solve problems', 'You should keep using a method even when it does not help'],
                    1,
                ],
                '10-14' => [
                    'When his coding project fails a test, Daniel records the error, changes one part, and tests again. He asks for specific feedback when stuck.',
                    'When an approach does not work, what is useful?',
                    ['Hide the result so nobody can help', 'Change everything at once without checking', 'Examine the result, adjust a step, and test the change'],
                    2,
                ],
            ],
            'Building a Helpful Habit' => [
                '3-5' => [
                    'After playtime, Timo puts crayons back in their box. A picture reminder near the shelf helps Timo remember what comes next.',
                    'What can help you remember a helpful routine?',
                    ['A simple reminder near where you do the task', 'Changing the routine every time', 'Waiting until you never need help'],
                    0,
                ],
                '6-9' => [
                    'Zuri wants to remember reading time. She puts her book where she can see it and tries reading after dinner. At week’s end, she reviews what helped.',
                    'Which action can make a routine easier to repeat?',
                    ['Expecting to remember without a plan', 'Connecting the routine to a regular part of the day', 'Stopping whenever one day goes differently'],
                    1,
                ],
                '10-14' => [
                    'Amir prepares his materials and chooses a usual homework start time. He tracks what helps, then adjusts the plan when the schedule changes.',
                    'What is a practical way to develop a sustainable habit?',
                    ['Follow a rigid routine even when it no longer fits', 'Expect motivation to be equally strong every day', 'Use a helpful cue, repeat the action, and review adjustments'],
                    2,
                ],
            ],
            'Giving a Task My Attention' => [
                '3-5' => [
                    'Beni is sorting shapes into a puzzle. Beni puts a noisy toy aside, works on a few shapes, and takes a break with a grown-up when needed.',
                    'What might help you focus on an activity?',
                    ['Play with every toy at the same time', 'Choose one activity and put away distractions', 'Keep working even when you need help'],
                    1,
                ],
                '6-9' => [
                    'During homework, Asha notices messages and toys keep taking her attention away. She puts them aside, completes a few questions, then takes a short break.',
                    'Which strategy can help you give a task your attention?',
                    ['Keep switching between unrelated activities', 'Ignore every need for rest', 'Reduce distractions and work on one manageable part'],
                    2,
                ],
                '10-14' => [
                    'Leo has a project due next week. He defines the next step, silences non-essential notifications, and plans a manageable work period followed by a break.',
                    'Which approach supports focused work realistically?',
                    ['Define the next action, reduce distractions, and plan breaks', 'Assume concentration requires never taking a break', 'Keep every notification active to practise ignoring it'],
                    0,
                ],
            ],
        ],

        'people-relationships' => [
            'Listening to Understand' => [
                '3-5' => [
                    'Tali waits while a friend explains the rules of a game. Tali looks and listens, then asks what happens next.',
                    'What is a helpful way to listen?',
                    ['Listen until your friend finishes', 'Shout over your friend', 'Guess without asking'],
                    0,
                ],
                '6-9' => [
                    'Two friends remember a game differently. Instead of arguing, Ben listens to each friend and repeats what he understood.',
                    'What can help people understand one another?',
                    ['Interrupt as soon as you disagree', 'Listen and check what the other person means', 'Assume your first guess is correct'],
                    1,
                ],
                '10-14' => [
                    'A project team disagrees about its plan. Maya summarises another member’s view, checks whether she understood correctly, and then explains her own concerns.',
                    'Why can summarising another person’s view be useful?',
                    ['It guarantees that person must agree with you', 'It avoids the need to hear different views', 'It helps check understanding before discussing a disagreement'],
                    2,
                ],
            ],
            'Seeing Another Perspective' => [
                '3-5' => [
                    'Noor sees a child standing alone near the play area. Noor asks kindly whether the child would like to join the game.',
                    'What is a kind way to respond when someone seems left out?',
                    ['Ask politely whether they would like to join', 'Decide they do not want friends', 'Laugh because they are alone'],
                    0,
                ],
                '6-9' => [
                    'When a classmate is quiet after a game, Eli wonders whether the classmate feels disappointed. Eli asks how they feel instead of guessing.',
                    'How can you understand someone else’s perspective?',
                    ['Assume they feel exactly as you would', 'Consider their situation and ask respectfully', 'Tell them their feeling is wrong'],
                    1,
                ],
                '10-14' => [
                    'Two group members disagree about a deadline. One has family responsibilities that limit their time. The group discusses the constraints before choosing a fair plan.',
                    'What supports perspective-taking?',
                    ['Assuming one explanation fits everyone', 'Treating different needs as excuses automatically', 'Considering another person’s context without assuming you know everything'],
                    2,
                ],
            ],
            'Solving Problems Together' => [
                '3-5' => [
                    'Two children want the same coloured crayon. With support, they take turns and choose another colour while waiting.',
                    'What can help solve a small disagreement?',
                    ['Take turns and talk about a fair choice', 'Grab the item and run away', 'Refuse to listen to anyone'],
                    0,
                ],
                '6-9' => [
                    'A team has different ideas for a poster. They take turns explaining their ideas and choose parts that fit the shared goal.',
                    'What supports good teamwork?',
                    ['One person decides everything without listening', 'Share ideas and agree on a plan together', 'Reject every suggestion immediately'],
                    1,
                ],
                '10-14' => [
                    'A team project is behind schedule. Members list unfinished tasks, discuss available time, and redistribute work while making sure responsibilities are clear.',
                    'What is a constructive first step when a team faces a problem?',
                    ['Blame one person before checking the facts', 'Hide the delay from the team', 'Identify the problem, hear perspectives, and agree on next actions'],
                    2,
                ],
            ],
            'Respecting Personal Boundaries' => [
                '3-5' => [
                    'Mina says she does not want a hug today. Her friend steps back. Mina can tell a trusted grown-up if someone keeps ignoring her no.',
                    'What should a friend do when someone says no to a hug?',
                    ['Respect the answer and give them space', 'Keep asking until they give in', 'Say they must hug everyone'],
                    0,
                ],
                '6-9' => [
                    'A classmate asks to read Jay’s private note. Jay says no. When the classmate keeps pressing, Jay moves away and tells a trusted adult.',
                    'What can you do if someone keeps ignoring a reasonable boundary?',
                    ['Agree even though you feel uncomfortable', 'Repeat your boundary and seek trusted help if needed', 'Promise to keep it secret'],
                    1,
                ],
                '10-14' => [
                    'A peer keeps demanding immediate replies and asks for private details. Alex sets a clear limit, stops responding when pressured, and seeks support from a trusted adult.',
                    'Which response best supports a personal boundary?',
                    ['Share private details to avoid disagreement', 'Accept pressure to keep the friendship', 'State the limit and get help if pressure or unsafe behaviour continues'],
                    2,
                ],
            ],
        ],

        'critical-thinking' => [
            'Asking Useful Questions' => [
                '3-5' => [
                    'A seed grows beside a sunny window. Tumi wonders why the plant has new leaves and asks a grown-up to look with them.',
                    'What is a useful way to start exploring something?',
                    ['Ask a question about what you notice', 'Decide the answer without looking', 'Ignore what you see'],
                    0,
                ],
                '6-9' => [
                    'A puddle disappears after a sunny day. Juma asks what might have happened and plans to observe another puddle safely.',
                    'Which question can help begin an investigation?',
                    ['Why should I ignore this?', 'What changed, and what evidence could I observe?', 'How can I prove my first guess without checking?'],
                    1,
                ],
                '10-14' => [
                    'A school garden has fewer flowers this month. A group lists possible causes and decides what observations would help distinguish the explanations.',
                    'What makes a question useful for an investigation?',
                    ['It assumes one explanation is already proven', 'It cannot be explored or checked', 'It can be investigated using observations or evidence'],
                    2,
                ],
            ],
            'Checking Information' => [
                '3-5' => [
                    'A message says a game gives free prizes to everyone. Ana does not tap the message and asks a trusted grown-up to check it.',
                    'What should you do when an online message seems surprising?',
                    ['Ask a trusted adult before acting on it', 'Tap every link right away', 'Send it to all your friends'],
                    0,
                ],
                '6-9' => [
                    'A surprising claim appears in a class chat. Noor checks who shared it, compares reliable information with an adult, and waits before forwarding it.',
                    'How can you check a surprising claim?',
                    ['Share it immediately', 'Check the source and look for supporting evidence', 'Believe it because it sounds exciting'],
                    1,
                ],
                '10-14' => [
                    'Two posts give conflicting statistics. Malik checks the original source, date, method, and whether other credible sources support the claim.',
                    'Which approach is strongest when checking a factual claim?',
                    ['Choose the post with the boldest headline', 'Trust a claim because many people repeat it', 'Check original evidence, context, date, and credible corroboration'],
                    2,
                ],
            ],
            'Breaking a Problem into Steps' => [
                '3-5' => [
                    'A toy tidy-up feels big. With a grown-up, Lila puts books away first, then blocks, and finally the soft toys.',
                    'What can make a big task feel easier?',
                    ['Do one small step at a time', 'Mix every item into one pile', 'Give up before starting'],
                    0,
                ],
                '6-9' => [
                    'The class needs to prepare a display. They list materials, make a jobs list, and complete one task before moving to the next.',
                    'Why can breaking a problem into steps help?',
                    ['It means you never need to check progress', 'It makes the work more manageable and easier to track', 'It makes planning unnecessary'],
                    1,
                ],
                '10-14' => [
                    'A group has to organise a community presentation. They define the outcome, split the work into tasks, set deadlines, and review dependencies.',
                    'What is an advantage of decomposing a complex task?',
                    ['It removes every risk from the project', 'It guarantees the first plan will work', 'It helps make tasks, dependencies, and progress easier to manage'],
                    2,
                ],
            ],
            'Thinking of Different Ideas' => [
                '3-5' => [
                    'A cardboard box can become a toy house or a tunnel for toy cars. With an adult, the children choose a safe way to reuse it.',
                    'What does creative thinking help us do?',
                    ['Think of different possibilities', 'Say every new idea is wrong', 'Copy one idea without thinking'],
                    0,
                ],
                '6-9' => [
                    'A group wants to keep pencils organised. They draw several container designs, compare what materials they have, and choose one to test.',
                    'What is a useful creative process?',
                    ['Choose the first idea without considering the goal', 'Generate options and compare them against the need', 'Reject every idea that needs improvement'],
                    1,
                ],
                '10-14' => [
                    'A student team wants to reduce food waste at an event. They propose several approaches and compare cost, accessibility, effort, and likely impact.',
                    'How can a team evaluate different ideas constructively?',
                    ['Assume the newest idea is automatically best', 'Reject ideas that differ from the first proposal', 'Compare options against clear needs, constraints, and evidence'],
                    2,
                ],
            ],
        ],

        'digital-life-safety' => [
            'Keeping Personal Information Private' => [
                '3-5' => [
                    'A game asks Lela for her home address. She stops and asks a trusted grown-up before entering any details.',
                    'What should you do if a game asks for private details?',
                    ['Ask a trusted adult before sharing', 'Type the details quickly', 'Send the details to other players'],
                    0,
                ],
                '6-9' => [
                    'A new app requests a child’s full name, school, and location. The child checks with a caregiver before deciding whether the app is appropriate.',
                    'What is a safer response to an unfamiliar app requesting personal information?',
                    ['Share everything to unlock the app', 'Pause and check with a trusted adult first', 'Post the information in a public chat'],
                    1,
                ],
                '10-14' => [
                    'A site requests a profile photo, school, location access, and contact details. Dani reviews what is necessary and asks a caregiver before sharing identifying data.',
                    'What should guide a decision to share personal information online?',
                    ['Assume all requests are necessary', 'Share details because the site looks professional', 'Check the purpose, minimise what is shared, and follow trusted adult guidance'],
                    2,
                ],
            ],
            'Creating Safer Accounts' => [
                '3-5' => [
                    'Toto wants to play a new game. A grown-up helps set it up and shows Toto not to tell other players the sign-in secret.',
                    'Who can help a young child set up an account safely?',
                    ['A trusted grown-up', 'A stranger in a game chat', 'Anyone who asks for a sign-in secret'],
                    0,
                ],
                '6-9' => [
                    'A friend asks Sifa to send a password in a group chat. Sifa does not send it and asks a caregiver how to keep the account safe.',
                    'Which is a safer account habit?',
                    ['Share passwords with game friends', 'Keep sign-in details private and get adult help when needed', 'Reuse another person’s account without permission'],
                    1,
                ],
                '10-14' => [
                    'Tari is setting up an account. They use the platform’s approved security options, avoid sharing login codes in messages, and ask a caregiver when uncertain.',
                    'Which action helps protect an account?',
                    ['Send a login code to someone who requests it', 'Ignore security warnings to save time', 'Keep authentication details private and use suitable security settings'],
                    2,
                ],
            ],
            'Being Kind Online' => [
                '3-5' => [
                    'A message in a game makes Bo sad. Bo stops playing for a moment and tells a trusted grown-up what happened.',
                    'What can you do when an online message hurts your feelings?',
                    ['Tell a trusted adult and ask for help', 'Send a meaner message back', 'Keep it secret even if you feel unsafe'],
                    0,
                ],
                '6-9' => [
                    'Someone posts an unkind comment about Jo. Jo avoids replying angrily and asks a trusted adult for help using the platform’s safety tools.',
                    'What is a responsible response to hurtful online behaviour?',
                    ['Share the comment widely to embarrass the person', 'Avoid retaliation and seek help from a trusted adult', 'Send private details to the person'],
                    1,
                ],
                '10-14' => [
                    'A group chat starts targeting a classmate. Amara does not pile on or forward humiliating content; she saves what is needed and reports it with trusted adult support.',
                    'What is a constructive response to online harassment?',
                    ['Join in so the group accepts you', 'Forward the content for entertainment', 'Avoid amplifying harm and use trusted support and appropriate reporting tools'],
                    2,
                ],
            ],
            'Not Everything Online Is True' => [
                '3-5' => [
                    'A video says a toy can do something amazing. Ben asks a trusted grown-up to help check whether the claim is real.',
                    'What can you do when a video makes a surprising claim?',
                    ['Ask a trusted adult to help check it', 'Believe every video you see', 'Share it before asking questions'],
                    0,
                ],
                '6-9' => [
                    'Two websites tell different stories about the same event. Hana compares their sources and dates with an adult before deciding what to trust.',
                    'What should you do when online sources disagree?',
                    ['Trust the first result automatically', 'Compare credible sources and check where the information came from', 'Choose whichever story is more exciting'],
                    1,
                ],
                '10-14' => [
                    'A popular post cites a study but gives no link. Ravi looks for the original research, publication date, and independent credible reporting before sharing it.',
                    'What is the strongest way to check a disputed online claim?',
                    ['Treat popularity as proof', 'Assume a screenshot preserves all the context', 'Seek the original source and compare it with credible independent evidence'],
                    2,
                ],
            ],
        ],

        'money-financial-wisdom' => [
            'Needs, Wants, and Priorities' => [
                '3-5' => [
                    'Mina is choosing what to take on a trip. Her caregiver helps pack water and weather-appropriate clothing before choosing a favourite toy.',
                    'Which is a useful way to think about a need?',
                    ['Something important for health, safety, or the situation', 'Every toy a friend owns', 'Anything seen in an advert'],
                    0,
                ],
                '6-9' => [
                    'Kito has a small amount for a class project. He checks the materials he needs before deciding whether to buy extra decorations.',
                    'What is a sensible way to compare a need and a want?',
                    ['All wants are more important than needs', 'Consider what is necessary for the situation before choosing extras', 'Everyone must have exactly the same priorities'],
                    1,
                ],
                '10-14' => [
                    'Lina plans how to use money for transport, a school project, and a desired accessory. She recognises that priorities depend on circumstances and available resources.',
                    'What is a thoughtful way to prioritise spending?',
                    ['Assume every household has the same needs', 'Buy the most advertised item first', 'Consider essential needs, personal circumstances, and the resources available'],
                    2,
                ],
            ],
            'Planning Before Spending' => [
                '3-5' => [
                    'Ari wants a snack at a market. With a caregiver, Ari checks what was planned and whether the purchase fits before choosing.',
                    'What can help before buying something?',
                    ['Check with your caregiver and consider the plan', 'Buy it without checking', 'Assume the price never matters'],
                    0,
                ],
                '6-9' => [
                    'Zuri compares two notebooks and checks the money available. She considers what she needs for school instead of choosing only by decoration.',
                    'Why is it useful to check available money before buying?',
                    ['It makes every purchase necessary', 'It helps compare choices with the amount available', 'It means you never need to check prices'],
                    1,
                ],
                '10-14' => [
                    'A student compares two devices for a project. They consider price, needed features, durability, and whether the purchase fits their approved budget.',
                    'What supports a more informed spending decision?',
                    ['Choose the highest-priced option automatically', 'Ignore future costs and practical needs', 'Compare total costs, relevant features, and the available budget'],
                    2,
                ],
            ],
            'Saving for a Goal' => [
                '3-5' => [
                    'Tami wants a storybook. With a grown-up, Tami places a small amount aside when possible and watches the savings grow toward the goal.',
                    'Why might someone save money?',
                    ['To work toward a goal in the future', 'To guarantee money always grows', 'To avoid thinking about needs'],
                    0,
                ],
                '6-9' => [
                    'Noah hopes to buy art supplies. He makes a simple progress chart with a caregiver and adds to the goal when he has money available.',
                    'Which statement about saving is sensible?',
                    ['Saving guarantees every goal will be reached quickly', 'Saving can help prepare for a planned future purchase', 'Saved money never needs to be kept safely'],
                    1,
                ],
                '10-14' => [
                    'A student wants to fund a future learning project. They estimate the cost, set a realistic target, and review the plan when their circumstances change.',
                    'What makes a savings plan useful?',
                    ['It guarantees prices and circumstances will not change', 'It means unexpected costs can be ignored', 'It connects a goal, an estimate, a plan, and progress reviews'],
                    2,
                ],
            ],
            'Making a Simple Budget' => [
                '3-5' => [
                    'A family plans materials for a small craft activity. A grown-up shows the child the items they have and helps choose what fits.',
                    'What can a simple plan for money help you do?',
                    ['Compare what is available with what you plan to spend', 'Decide that every item must be bought', 'Measure how valuable a person is'],
                    0,
                ],
                '6-9' => [
                    'A club has a set amount for a project. The children list expected costs with an adult and change the plan when the total is too high.',
                    'What should you do if planned costs are more than the available amount?',
                    ['Pretend the total does not matter', 'Review priorities and adjust the plan with appropriate help', 'Assume more money will appear'],
                    1,
                ],
                '10-14' => [
                    'A young team budgets for an event and compares expected costs with available funds. They include a small allowance for uncertainty and review actual spending.',
                    'What is a useful purpose of a budget?',
                    ['Guarantee that no unexpected costs occur', 'Make priorities unnecessary', 'Plan resources, compare choices, and adjust when actual costs differ'],
                    2,
                ],
            ],
        ],

        'entrepreneurship-innovation' => [
            'Noticing Needs Around Us' => [
                '3-5' => [
                    'Children find that the crayons are hard to reach. With an adult, they talk about a safe place to keep them.',
                    'What should you do before making something to help?',
                    ['Notice the problem and ask what would help', 'Build anything without thinking', 'Assume everyone wants the same thing'],
                    0,
                ],
                '6-9' => [
                    'Several classmates struggle to organise shared pencils. The group asks what would make sharing easier before drawing possible organisers.',
                    'What is a useful first step for an idea?',
                    ['Buy materials before understanding the problem', 'Find out what people need and what is causing the difficulty', 'Assume the first idea will work'],
                    1,
                ],
                '10-14' => [
                    'A team notices students have trouble borrowing equipment. They interview users with permission, observe the process, and define which problem to solve first.',
                    'Why investigate a need before designing a solution?',
                    ['It guarantees that an idea will make money', 'It avoids listening to the people affected', 'It helps ensure the solution responds to a real problem'],
                    2,
                ],
            ],
            'Creating Value for Others' => [
                '3-5' => [
                    'A child makes a picture label so family members can find the right toy basket. The family tries it and says whether it helps.',
                    'When can an idea be useful?',
                    ['When it helps with a real need', 'Only when it is expensive', 'When no one is allowed to suggest changes'],
                    0,
                ],
                '6-9' => [
                    'A group makes a pencil holder for a classroom. They ask classmates to try it and improve it when pencils keep falling out.',
                    'What can help an idea create value?',
                    ['Never changing the design', 'Solving a real problem and improving it using feedback', 'Choosing materials without considering the purpose'],
                    1,
                ],
                '10-14' => [
                    'A team tests a reusable organiser with intended users. Feedback reveals one feature is confusing, so the team revises it and checks whether the change helps.',
                    'What is evidence that an idea may be creating value?',
                    ['The team likes it even if no one can use it', 'It has many features regardless of need', 'It addresses a need and works for the people it is intended to help'],
                    2,
                ],
            ],
            'Planning a Small Project' => [
                '3-5' => [
                    'A child wants to make a paper picture. With a grown-up, they gather safe materials and choose the first step.',
                    'What helps you begin a small project?',
                    ['Choose a simple goal and gather what you need', 'Start without thinking about safety', 'Expect everything to happen by itself'],
                    0,
                ],
                '6-9' => [
                    'A class plans a small garden activity. They list tools, decide who will do each job, and ask an adult which tasks are safe.',
                    'What belongs in a simple project plan?',
                    ['Only a wish that everything works', 'A goal, tasks, resources, and suitable help', 'A promise never to change the plan'],
                    1,
                ],
                '10-14' => [
                    'A student team plans a community display. They set milestones, assign tasks by interest and availability, check permissions, and review resources before starting.',
                    'Which plan is most likely to support organised project work?',
                    ['Leave every responsibility unclear', 'Ignore permissions and resource limits', 'Set a goal, tasks, responsibilities, resources, and review points'],
                    2,
                ],
            ],
            'Learning from a Test' => [
                '3-5' => [
                    'A paper bridge bends under a toy. With adult help, the children look at what happened and try a safer way to support it.',
                    'What can a test help you discover?',
                    ['What works and what might need changing', 'That every first try is perfect', 'That you should ignore what happened'],
                    0,
                ],
                '6-9' => [
                    'A model boat tips over during a supervised water test. The team records what they saw and changes one part before trying again.',
                    'What is useful after a test does not work as hoped?',
                    ['Hide the result', 'Record what happened, make a reasoned change, and test safely again', 'Assume the idea can never improve'],
                    1,
                ],
                '10-14' => [
                    'A prototype misses its target. The team compares the test with the design goal, records limitations, and runs another controlled test after a change.',
                    'What makes testing useful during design?',
                    ['It proves the first design must be kept', 'It removes the need for evidence', 'It reveals how the design performs and informs the next iteration'],
                    2,
                ],
            ],
        ],

        'ai-future-intelligence' => [
            'Recognising AI Tools' => [
                '3-5' => [
                    'An art app suggests a picture idea. A grown-up explains that some apps use AI and that their suggestions can be mistaken.',
                    'Which is true about an AI tool?',
                    ['It can make mistakes', 'Everything it says must be correct', 'It knows everything about everyone'],
                    0,
                ],
                '6-9' => [
                    'A story app suggests a new character. Its suggestion is interesting, but Kai knows AI tools can produce errors and ideas that need checking.',
                    'Which statement about AI is most accurate?',
                    ['AI always understands people as humans do', 'AI can generate useful ideas but can also make mistakes', 'AI answers are always correct'],
                    1,
                ],
                '10-14' => [
                    'An AI assistant summarises an article. Amara treats the summary as a starting point and checks the original material for missing context or errors.',
                    'What is a responsible way to understand AI outputs?',
                    ['Assume a fluent answer proves the facts are correct', 'Treat every response as a human expert’s judgement', 'Recognise that generated outputs can be useful but may be incomplete or wrong'],
                    2,
                ],
            ],
            'Checking AI Answers' => [
                '3-5' => [
                    'An AI story app says something that sounds strange. Sami asks a trusted grown-up to help check it instead of believing it right away.',
                    'What should you do when an AI answer seems strange?',
                    ['Ask a trusted adult to help check it', 'Believe it because a computer said it', 'Repeat it to everyone'],
                    0,
                ],
                '6-9' => [
                    'An AI tool gives a surprising fact for a school task. Lena checks a trusted book or source and asks her teacher before using the answer.',
                    'What should you do before relying on an important AI answer?',
                    ['Accept it without checking', 'Verify it using trustworthy sources or a knowledgeable adult', 'Assume it cannot contain errors'],
                    1,
                ],
                '10-14' => [
                    'An AI assistant gives a confident answer about a science topic. Omar checks reliable references, verifies the context, and notes where the evidence is uncertain.',
                    'What is a sound way to evaluate an AI answer?',
                    ['Trust it because it sounds certain', 'Use it without checking sources', 'Verify key claims and distinguish evidence from uncertainty'],
                    2,
                ],
            ],
            'Protecting Privacy When Using AI' => [
                '3-5' => [
                    'A drawing tool asks for a home address. Nia stops and asks her trusted grown-up before typing anything private.',
                    'What should you do if an AI tool asks for private details?',
                    ['Ask a trusted adult before sharing', 'Type the details so it works', 'Give the details to anyone who asks'],
                    0,
                ],
                '6-9' => [
                    'A chatbot asks Timo for his password and family details. Timo does not enter them and checks with a caregiver about using the tool.',
                    'Which is a safer way to use an AI tool?',
                    ['Enter private details to get a better answer', 'Avoid sharing sensitive information and follow trusted adult guidance', 'Share a friend’s information without permission'],
                    1,
                ],
                '10-14' => [
                    'A generative AI site offers to help with an assignment but requests identifying details. Leila checks the tool’s rules and uses a safer approach with adult guidance.',
                    'What is the safest general approach to personal data in AI tools?',
                    ['Assume every tool keeps all data private', 'Upload personal details whenever a prompt requests them', 'Minimise sensitive data and follow the tool’s rules and trusted guidance'],
                    2,
                ],
            ],
            'AI and Fairness' => [
                '3-5' => [
                    'A game suggests only one kind of character. A grown-up helps Nuru imagine that people can have many looks, interests, and abilities.',
                    'What should you remember about an AI suggestion?',
                    ['It may not show every kind of person or idea', 'It always represents everyone fairly', 'It must be copied exactly'],
                    0,
                ],
                '6-9' => [
                    'A story tool keeps suggesting the same kind of hero. The class discusses whether its suggestions include different people and asks what might be missing.',
                    'Why should we think about fairness in AI results?',
                    ['AI outputs always include every viewpoint', 'Some outputs may leave people out or repeat unfair patterns', 'The first result should never be questioned'],
                    1,
                ],
                '10-14' => [
                    'An AI system gives different recommendations for similar profiles. A student considers its training data, design choices, and whether the results disadvantage some groups.',
                    'What is a critical question about possible AI bias?',
                    ['Can an algorithm never reflect unfair patterns?', 'Does a computer result remove human responsibility?', 'Could data or design choices create unfair differences in outputs?'],
                    2,
                ],
            ],
        ],

        'coding-robotics-engineering' => [
            'Putting Instructions in Order' => [
                '3-5' => [
                    'To plant a seed, Nuru first fills a pot with soil, then places the seed, and finally adds water with adult help.',
                    'Why does the order of steps matter?',
                    ['Some steps need to happen before others', 'Any order always works', 'Instructions never matter'],
                    0,
                ],
                '6-9' => [
                    'A robot toy follows a sequence of turns and moves. When the turns are placed in the wrong order, it reaches a different place.',
                    'What is an algorithm?',
                    ['A picture with no instructions', 'A sequence of steps for carrying out a task', 'A result that happens without any steps'],
                    1,
                ],
                '10-14' => [
                    'A program must collect data before calculating a result. The team maps the sequence and checks which steps depend on earlier outputs.',
                    'Why is sequencing important in an algorithm?',
                    ['It guarantees that every program is fast', 'It means testing is unnecessary', 'The order can determine whether dependent steps work correctly'],
                    2,
                ],
            ],
            'Finding and Fixing Errors' => [
                '3-5' => [
                    'A picture puzzle has one piece turned the wrong way. With help, Kito checks the picture and turns the piece until it fits.',
                    'What can you do when something does not work as expected?',
                    ['Look carefully for what needs fixing', 'Throw everything away immediately', 'Never try again'],
                    0,
                ],
                '6-9' => [
                    'A block-based program makes a character turn the wrong way. Mei checks each instruction and tests the program after changing the turn.',
                    'What is debugging?',
                    ['Changing random instructions without checking', 'Finding and correcting errors in a program or process', 'Deleting a project before testing it'],
                    1,
                ],
                '10-14' => [
                    'A program fails an edge-case test. The developer reproduces the problem, narrows down the cause, changes the relevant code, and reruns the test.',
                    'Which approach best supports debugging?',
                    ['Assume the first suspected cause is always right', 'Skip testing after a change', 'Reproduce the issue, investigate evidence, fix it, and retest'],
                    2,
                ],
            ],
            'Design, Build, Improve' => [
                '3-5' => [
                    'A paper tower falls over. With an adult, the children try a wider base using safe materials and see whether it stands better.',
                    'What is a helpful way to improve a design?',
                    ['Try a safe change and see what happens', 'Ignore what happens when it falls', 'Assume every first try is perfect'],
                    0,
                ],
                '6-9' => [
                    'A group builds a paper bridge for a classroom challenge. They test it under supervision, notice where it bends, and strengthen that area.',
                    'Which is a useful engineering process?',
                    ['Build once and never inspect it', 'Design, build, test, and improve', 'Ignore the goal of the project'],
                    1,
                ],
                '10-14' => [
                    'A prototype must hold a set load while using limited materials. The team defines criteria, tests safely, records results, and changes the design.',
                    'What should guide an engineering improvement?',
                    ['Appearance alone, regardless of purpose', 'A guess with no test results', 'Evidence from tests and the requirements the design must meet'],
                    2,
                ],
            ],
            'How Robots Follow Instructions' => [
                '3-5' => [
                    'A toy robot moves when a button is pressed. A grown-up shows how the button starts a programmed action.',
                    'What can help a programmable robot do a task?',
                    ['Instructions and parts designed for that task', 'Knowing every situation like a person', 'Guessing without any instructions'],
                    0,
                ],
                '6-9' => [
                    'An educational robot follows commands to move across a mat. Its program and hardware work together to produce the movement.',
                    'What does a programmed robot need to perform a task?',
                    ['Human feelings exactly like ours', 'Suitable hardware and instructions', 'A guarantee that it can do anything'],
                    1,
                ],
                '10-14' => [
                    'A classroom robot uses a motor, a control program, and a sensor to navigate a marked route. Its behaviour depends on its design and the inputs it receives.',
                    'What best explains a robot’s behaviour?',
                    ['All robots understand situations just like humans', 'Robots never need testing', 'Its hardware, program, and available inputs constrain what it can do'],
                    2,
                ],
            ],
        ],

        'science-nature-universe' => [
            'Observing Carefully' => [
                '3-5' => [
                    'Kito notices that a leaf is yellow and has a small hole. He tells a grown-up exactly what he can see.',
                    'What is an observation?',
                    ['Something you notice or measure', 'A guess that never needs checking', 'A result chosen before looking'],
                    0,
                ],
                '6-9' => [
                    'A group records a plant’s height each week. They write down the measurements separately from their guesses about why it changed.',
                    'Why separate observations from explanations?',
                    ['An observation always explains the cause', 'It helps distinguish what was noticed from what might explain it', 'Explanations never need evidence'],
                    1,
                ],
                '10-14' => [
                    'A student records temperature and plant height over several days. They note the conditions and avoid claiming that one measurement proves the cause of growth.',
                    'What strengthens an observation record?',
                    ['Changing notes to match an expected answer', 'Leaving out conditions that might matter', 'Recording what was measured, when, and under what conditions'],
                    2,
                ],
            ],
            'Testing a Question' => [
                '3-5' => [
                    'A child wonders whether a paper boat floats. With an adult, they place it in a safe basin of water and watch what happens.',
                    'What helps you learn from a safe test?',
                    ['Watch carefully and describe what happens', 'Decide the result before trying', 'Change the story if the result surprises you'],
                    0,
                ],
                '6-9' => [
                    'A class wonders which paper towel absorbs more water. With adult guidance, they use similar amounts of water and record each result.',
                    'What makes a comparison more useful?',
                    ['Change every condition each time', 'Keep important conditions similar and record results', 'Choose the winning result before testing'],
                    1,
                ],
                '10-14' => [
                    'A group tests whether light conditions affect seedling growth. They use comparable plants, record the conditions, and avoid drawing conclusions from one observation alone.',
                    'What supports a fair investigation?',
                    ['Change many factors without recording them', 'Ignore results that disagree with a guess', 'Plan a controlled comparison and record evidence carefully'],
                    2,
                ],
            ],
            'Caring for Our Environment' => [
                '3-5' => [
                    'A family sees litter near a path. With adult help, they put suitable rubbish in the right bin and keep away from anything unsafe.',
                    'Which action can help care for a shared place?',
                    ['Put rubbish in an appropriate bin with help when needed', 'Throw litter into a stream', 'Pick up sharp or unknown items with bare hands'],
                    0,
                ],
                '6-9' => [
                    'The class notices unused paper after an activity. They discuss reusing clean sheets when suitable and sorting waste according to local guidance.',
                    'What is a useful way to reduce waste?',
                    ['Assume every item must be thrown away', 'Reuse suitable materials and follow local disposal guidance', 'Put all waste into a waterway'],
                    1,
                ],
                '10-14' => [
                    'A community group compares ways to reduce waste. They consider local recycling options, safe reuse, access, and the resources needed for each choice.',
                    'What is a thoughtful environmental action?',
                    ['Assume one solution works everywhere', 'Ignore local collection rules', 'Choose practical actions based on evidence and local conditions'],
                    2,
                ],
            ],
            'How Systems Connect' => [
                '3-5' => [
                    'A plant needs soil, water, and light to grow well. A child notices that changing one thing can affect the plant.',
                    'What can happen when part of a system changes?',
                    ['Other parts may be affected too', 'Nothing can ever change', 'All parts always work separately'],
                    0,
                ],
                '6-9' => [
                    'In a garden, insects visit flowers and plants need water and light. The children discuss how the living things and their surroundings interact.',
                    'What does systems thinking help us notice?',
                    ['Every part works without affecting anything else', 'Different parts can influence one another', 'One change always has the same result everywhere'],
                    1,
                ],
                '10-14' => [
                    'A change in local water use affects gardens, habitats, and household routines. A team maps these connections before suggesting responses.',
                    'Why consider connections in a system?',
                    ['It proves every consequence can be predicted exactly', 'It means only one factor matters', 'A change can have linked effects across several parts of a system'],
                    2,
                ],
            ],
        ],

        'healthy-practical-life' => [
            'Everyday Hygiene' => [
                '3-5' => [
                    'Before snack time, Asha washes her hands with soap and clean water when available. A grown-up helps her reach the tap safely.',
                    'Which habit helps reduce the spread of germs?',
                    ['Wash hands with soap at important times', 'Share every personal hygiene item', 'Skip handwashing before eating'],
                    0,
                ],
                '6-9' => [
                    'After playing outside and before eating, Ben washes his hands with soap and clean running water when available, then dries them.',
                    'When is handwashing especially helpful?',
                    ['Only when hands look dirty', 'Before eating and after using the toilet', 'Only at the end of the week'],
                    1,
                ],
                '10-14' => [
                    'After using shared equipment, Lina follows the available handwashing facilities and avoids sharing personal items such as toothbrushes or towels used for personal hygiene.',
                    'Which is a sensible hygiene practice?',
                    ['Assume visible cleanliness proves hands are clean', 'Share all personal hygiene items', 'Use appropriate handwashing and avoid sharing personal hygiene items'],
                    2,
                ],
            ],
            'Making Healthy Everyday Choices' => [
                '3-5' => [
                    'After active play, Timo feels thirsty and takes a drink offered by a caregiver. Later, he rests when he feels tired.',
                    'What is one way to care for your body?',
                    ['Notice thirst and rest needs and tell a grown-up', 'Ignore thirst and tiredness every day', 'Assume everyone needs exactly the same routine'],
                    0,
                ],
                '6-9' => [
                    'Nia’s day includes movement, rest, different nourishing foods, and water. Her family adapts routines to their needs and circumstances.',
                    'Which approach supports everyday wellbeing?',
                    ['Follow one strict routine regardless of your needs', 'Build a flexible routine with food, movement, water, rest, and support', 'Skip rest whenever you want to improve'],
                    1,
                ],
                '10-14' => [
                    'A student reviews sleep, activity, meals, hydration, and workload. They consider what is sustainable for them and consult a trusted adult about health concerns.',
                    'What is a sensible approach to wellbeing?',
                    ['Assume one routine is suitable for everyone', 'Use online advice as a personal diagnosis', 'Consider different needs and seek reliable support for health concerns'],
                    2,
                ],
            ],
            'Practising Everyday Skills' => [
                '3-5' => [
                    'Mika puts a school bag by the door. A grown-up helps Mika choose safe tasks and handles anything too difficult or risky.',
                    'How can you learn an everyday skill safely?',
                    ['Practise suitable steps and ask for help', 'Attempt every risky task alone', 'Never ask questions'],
                    0,
                ],
                '6-9' => [
                    'Juma prepares school materials for the next day using a short checklist. He asks an adult for help with tasks that are unsafe or unfamiliar.',
                    'Which is a good way to build practical independence?',
                    ['Hide difficulties from everyone', 'Practise age-appropriate tasks and ask for help when needed', 'Try every unfamiliar task without checking safety'],
                    1,
                ],
                '10-14' => [
                    'A teenager learns to plan a simple meal and organise school deadlines. They check instructions, follow relevant safety rules, and ask for guidance when a task is beyond their experience.',
                    'What supports growing independence?',
                    ['Avoid learning from feedback', 'Ignore safety instructions to finish faster', 'Practise skills, check reliable instructions, and recognise when help is needed'],
                    2,
                ],
            ],
            'Knowing When to Ask for Help' => [
                '3-5' => [
                    'Tali feels unsafe near an unfamiliar situation. Tali moves toward a safe place when possible and tells a trusted grown-up.',
                    'What should you do if you feel unsafe?',
                    ['Tell a trusted adult and move toward safety when possible', 'Keep it secret no matter what', 'Assume you must handle everything alone'],
                    0,
                ],
                '6-9' => [
                    'An adult’s behaviour makes Jo uncomfortable. Jo tells a trusted adult. If the first person does not help, Jo keeps seeking help from another safe adult.',
                    'What can you do if the first person you tell does not help?',
                    ['Stop asking for help forever', 'Tell another safe, trusted adult until someone helps', 'Keep the situation secret'],
                    1,
                ],
                '10-14' => [
                    'A student feels pressured into an unsafe situation. They leave when possible, contact a trusted adult or appropriate local support, and keep seeking help if dismissed.',
                    'What is an important safety principle?',
                    ['You must solve every unsafe situation alone', 'You should stay quiet if the first person dismisses you', 'Keep seeking appropriate help if you feel unsafe or are not taken seriously'],
                    2,
                ],
            ],
        ],

        'leadership-citizenship-global-futures' => [
            'Leading by Helping' => [
                '3-5' => [
                    'The children are putting away play materials. Amara helps carry a light basket and asks what else needs doing.',
                    'What is one way to show helpful leadership?',
                    ['Help safely and listen to what the group needs', 'Take all the credit', 'Tell everyone they cannot help'],
                    0,
                ],
                '6-9' => [
                    'A group chooses a leader for a class task. The leader invites ideas, shares jobs fairly, and checks whether anyone needs help.',
                    'What is a helpful leadership behaviour?',
                    ['Make every decision alone', 'Listen and help the group work toward its goal', 'Take credit for everyone’s work'],
                    1,
                ],
                '10-14' => [
                    'A student coordinates a group project by clarifying responsibilities, inviting quieter members to contribute, and adjusting the plan when a problem appears.',
                    'What helps leadership support a team?',
                    ['Keeping decisions secret from team members', 'Treating questions as disloyalty', 'Communicating clearly, sharing responsibility, and adapting to feedback'],
                    2,
                ],
            ],
            'Respecting Differences' => [
                '3-5' => [
                    'Noor likes singing while Eli prefers building. They find a game that lets both children join in their own way.',
                    'What is a way to show respect for differences?',
                    ['Let people enjoy different things and include them', 'Say everyone must like the same game', 'Leave someone out because they are different'],
                    0,
                ],
                '6-9' => [
                    'A class project has children with different interests and ways of communicating. The group adjusts jobs so everyone can contribute.',
                    'What does inclusion try to support?',
                    ['Expecting everyone to be exactly alike', 'Fair opportunities to participate and belong', 'Choosing only people with the same ideas'],
                    1,
                ],
                '10-14' => [
                    'A planning group includes people with different backgrounds and access needs. They ask respectfully what would help and consider barriers before deciding.',
                    'What is a thoughtful way to support inclusion?',
                    ['Assume one solution works for everyone', 'Ask people to hide their differences', 'Consider barriers and seek fair ways for people to participate'],
                    2,
                ],
            ],
            'Helping Our Community' => [
                '3-5' => [
                    'Children see an adult-led clean-up in a shared space. With permission, they help with a safe task such as sorting clean paper.',
                    'How can a young child help a community safely?',
                    ['Join an age-appropriate activity with adult guidance', 'Handle unknown or sharp rubbish alone', 'Go to a work site without permission'],
                    0,
                ],
                '6-9' => [
                    'The class wants to help a local project. They discuss ideas with caregivers and choose an activity that is safe and appropriate for children.',
                    'What is a good way to contribute to a community?',
                    ['Take on every task without adult guidance', 'Choose a safe activity with permission and support', 'Assume other people’s needs without asking'],
                    1,
                ],
                '10-14' => [
                    'A youth group considers a community project. They consult people affected, check permissions and safety, and choose a contribution within their skills and resources.',
                    'What makes community participation responsible?',
                    ['Decide what people need without consulting them', 'Ignore permissions and safety', 'Listen to the community and act within suitable limits and support'],
                    2,
                ],
            ],
            'Learning About Our Connected World' => [
                '3-5' => [
                    'A child learns that people in another place may speak a different language and eat different foods. The child listens and asks a kind question.',
                    'How can you learn about people who live differently?',
                    ['Listen kindly and ask respectful questions', 'Say everyone must live the same way', 'Decide one way of life is best for everyone'],
                    0,
                ],
                '6-9' => [
                    'A class explores homes and celebrations from different places. They learn that each community includes many kinds of people and avoid guessing what everyone does.',
                    'What is a respectful way to learn about another community?',
                    ['Assume one story describes everyone', 'Listen, ask respectful questions, and avoid stereotypes', 'Decide that one community is better than all others'],
                    1,
                ],
                '10-14' => [
                    'A student compares how different communities respond to an environmental challenge. They consider local history and resources rather than assuming one solution fits all.',
                    'What supports thoughtful global awareness?',
                    ['Treat differences as proof that one group is better', 'Ignore local context when comparing solutions', 'Consider different contexts and learn from communities without stereotyping them'],
                    2,
                ],
            ],
        ],
    ];

    public static function courseDescription(
        string $worldSlug,
        string $ageGroup
    ): string {
        $description = self::COURSE_DESCRIPTIONS[$worldSlug][$ageGroup] ?? null;

        if (! is_string($description) || trim($description) === '') {
            throw new RuntimeException(
                "Missing age-specific course description for {$worldSlug}/{$ageGroup}."
            );
        }

        return $description;
    }

    public static function lessonDefinitionForAge(
        string $worldSlug,
        array $lessonDefinition,
        string $ageGroup
    ): array {
        $title = $lessonDefinition['title'] ?? '';
        $variant = self::CONTENT[$worldSlug][$title][$ageGroup] ?? null;

        if (! is_array($variant) || count($variant) !== 4) {
            throw new RuntimeException(
                "Missing age-specific content for {$worldSlug}/{$title}/{$ageGroup}."
            );
        }

        return array_merge($lessonDefinition, [
            'story' => $variant[0],
            'question' => $variant[1],
            'options' => $variant[2],
            'answer' => $variant[3],
        ]);
    }

    /**
     * Fail before database writes if a curriculum lesson or age variant
     * is missing or malformed.
     */
    public static function validateCoverage(
        array $worldDefinitions,
        array $ageGroups
    ): void {
        $expectedKeys = [];

        foreach ($worldDefinitions as $worldSlug => $world) {
            foreach ($ageGroups as $ageGroup) {
                self::courseDescription($worldSlug, $ageGroup);
            }

            foreach ($world['courses'] as $course) {
                foreach ($course['modules'] as $module) {
                    foreach ($module['lessons'] as $lesson) {
                        $title = $lesson['title'];
                        $key = $worldSlug . '|' . $title;
                        $expectedKeys[$key] = true;

                        foreach ($ageGroups as $ageGroup) {
                            $variant = self::CONTENT[$worldSlug][$title][$ageGroup] ?? null;

                            if (! is_array($variant) || count($variant) !== 4) {
                                throw new RuntimeException(
                                    "Missing age-specific content for {$worldSlug}/{$title}/{$ageGroup}."
                                );
                            }

                            [$story, $question, $options, $answer] = $variant;

                            if (
                                ! is_string($story) || trim($story) === ''
                                || ! is_string($question) || trim($question) === ''
                                || ! is_array($options) || count($options) < 2
                            ) {
                                throw new RuntimeException(
                                    "Invalid story, question, or options for {$worldSlug}/{$title}/{$ageGroup}."
                                );
                            }

                            foreach ($options as $option) {
                                if (! is_string($option) || trim($option) === '') {
                                    throw new RuntimeException(
                                        "Invalid quiz option for {$worldSlug}/{$title}/{$ageGroup}."
                                    );
                                }
                            }

                            if (
                                ! is_int($answer)
                                || ! array_key_exists($answer, $options)
                            ) {
                                throw new RuntimeException(
                                    "Invalid quiz answer for {$worldSlug}/{$title}/{$ageGroup}."
                                );
                            }
                        }
                    }
                }
            }
        }

        $actualKeys = [];

        foreach (self::CONTENT as $worldSlug => $lessons) {
            foreach ($lessons as $title => $variants) {
                $actualKeys[$worldSlug . '|' . $title] = true;
            }
        }

        $missing = array_diff_key($expectedKeys, $actualKeys);
        $unexpected = array_diff_key($actualKeys, $expectedKeys);

        if ($missing !== [] || $unexpected !== []) {
            throw new RuntimeException(
                'Age-specific content registry does not exactly match the curriculum. '
                . 'Missing: ' . implode(', ', array_keys($missing))
                . '. Unexpected: ' . implode(', ', array_keys($unexpected))
            );
        }

        foreach (self::CONTENT as $worldSlug => $lessons) {
            foreach ($lessons as $title => $variants) {
                $stories = [];
                $questions = [];

                foreach ($ageGroups as $ageGroup) {
                    [$story, $question] = $variants[$ageGroup];

                    $stories[] = $story;
                    $questions[] = $question;
                }

                if (
                    count(array_unique($stories)) !== count($ageGroups)
                    || count(array_unique($questions)) !== count($ageGroups)
                ) {
                    throw new RuntimeException(
                        "Lesson content must differ across age groups: {$worldSlug}/{$title}."
                    );
                }
            }
        }
    }
}