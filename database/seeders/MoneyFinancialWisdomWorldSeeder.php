<?php

namespace Database\Seeders;

use Database\Seeders\Support\WorldContentSeeder;

class MoneyFinancialWisdomWorldSeeder extends WorldContentSeeder
{
    private const PENNY = 'You are Penny, a cheerful money guide for children aged 6 to 9. Answer in two or three short, simple sentences and finish with one friendly question.';

    protected function worldSlug(): string
    {
        return 'money-financial-wisdom';
    }

    protected function courses(): array
    {
        return [
            self::course(
                'Money Smart Adventures',
                'Learn what money is, how people earn it, the difference between needs and wants, how to save for a goal, and how to share and stay safe with money.',
                '6-9',
                [
                    $this->whatIsMoney(),
                    $this->needsAndWants(),
                    $this->saving(),
                    $this->spendingAndSharing(),
                    $this->finalQuest(),
                ]
            ),
        ];
    }

    private function whatIsMoney(): array
    {
        return self::module(
            'What Is Money?',
            'Discover why people invented money, how coins and notes show value, and how people earn it.',
            [
                self::lesson(
                    'Why Do We Use Money?',
                    'Find out how people traded before money and why money makes life easier.',
                    ['Money Awareness'],
                    [
                        self::story(
                            'The Swapping Problem',
                            "Long ago, there were no shops with price tags. If Sam had extra eggs and needed a basket, Sam had to find a basket maker who wanted eggs. Swapping one thing for another is called bartering.\n\nBartering can be tricky. What if the basket maker does not want eggs today? What if one basket is worth more than the eggs Sam has? Sam might walk and walk and still not find a fair swap.\n\nSo people invented money. Money is something everybody agrees to accept in exchange for the things we need. Now Sam can sell the eggs to anyone, keep the money, and buy a basket from any basket maker later.\n\nMoney makes buying, selling, and sharing easier for everyone."
                        ),
                        self::quiz(
                            'What is it called when people swap things without using money?',
                            ['Saving', 'Bartering', 'Borrowing'],
                            1,
                            'Swapping one thing for another is called bartering.'
                        ),
                        self::quiz(
                            'Why is money helpful?',
                            ['It can only be used by grown-ups', 'It makes everything free', 'Everybody agrees to accept it for things we need'],
                            2,
                            'Because everyone accepts money, you do not have to find someone who wants exactly what you have.'
                        ),
                        self::project(
                            'Barter Day',
                            'Try a pretend barter at home and see why people invented money.',
                            [
                                'Choose three small things you do not mind swapping, such as a pencil, a sticker, and a toy.',
                                'Ask a family member to choose three things too.',
                                'Try to agree on a fair swap.',
                                'Notice what was hard. Did you both want what the other person had?',
                                'Tell a grown-up how money would make swapping easier.',
                            ]
                        ),
                    ]
                ),

                self::lesson(
                    'Coins, Notes and Value',
                    'Learn how coins and notes show their value and how to count money.',
                    ['Money Awareness', 'Number Sense'],
                    [
                        self::story(
                            'Counting Money',
                            "Money comes in different shapes. Coins are small, round, and made of metal. Notes are made of paper or plastic. Every coin and note has a number on it. That number tells its value, which means how much it is worth.\n\nA coin with a bigger number is worth more, even if it looks smaller. A note worth 10 can buy more than a coin worth 1.\n\nTo count money, add the values together. If you have a 5 coin, a 5 coin, and a 10 note, you add 5 + 5 + 10 = 20. You have 20 in all.\n\nDifferent countries use different money, but the idea is always the same: the number shows the value."
                        ),
                        self::quiz(
                            'What tells you how much a coin or note is worth?',
                            ['Its colour', 'The number printed on it', 'How heavy it is'],
                            1,
                            'The number on a coin or note shows its value.'
                        ),
                        self::quiz(
                            'You have a 10 note, a 5 coin, and a 2 coin. How much money is that?',
                            ['15', '12', '17'],
                            2,
                            '10 + 5 + 2 = 17.'
                        ),
                        self::project(
                            'Count the Jar',
                            'Practise counting money with real coins, or with play money you draw yourself.',
                            [
                                'Ask a grown-up if you can use some real coins, or cut out paper coins and write a value on each.',
                                'Sort the coins into piles that have the same value.',
                                'Count each pile and write down its total.',
                                'Add the piles together to find the grand total.',
                                'Count again a different way to check your answer.',
                            ]
                        ),
                    ]
                ),

                self::lesson(
                    'Where Does Money Come From?',
                    'Find out how people earn money by doing useful work.',
                    ['Earning and Work', 'Money Awareness'],
                    [
                        self::story(
                            'Money Comes From Work',
                            "Money does not grow on trees, and it does not appear from a machine by magic. Most people get money by working. A farmer grows food and sells it. A teacher helps children learn and gets paid. A bus driver takes people where they need to go and gets paid.\n\nWork means doing something useful for other people. The money people earn is called income. They use it to buy what their family needs.\n\nChildren can earn too! You might get money for a special job at home, or from a small job a grown-up agrees to. Some families give an allowance, which is money given regularly.\n\nEarning takes effort, so it is smart to treat money with care."
                        ),
                        self::quiz(
                            'What do we call the money people get for working?',
                            ['Income or earnings', 'A present from nowhere', 'A price tag'],
                            0,
                            'Money earned from work is called income.'
                        ),
                        self::quiz(
                            'Which is a way grown-ups usually get money?',
                            ['Finding it on trees', 'Doing a job that helps other people', 'Wishing for it'],
                            1,
                            'Most people earn money by doing useful work.'
                        ),
                        self::aiChat(
                            'Ask Penny About Jobs',
                            'Ask Penny three questions about how people earn money.',
                            'Ask Penny three questions about jobs. You could ask: What does a farmer do? How do bus drivers earn money? What job would be fun?',
                            self::PENNY . ' Focus on jobs and how people earn money.'
                        ),
                    ]
                ),

                self::checkup(
                    'Module 1 Check-Up',
                    'Show what you learned about money, value, and earning.',
                    ['Money Awareness', 'Earning and Work'],
                    [
                        self::quiz(
                            'Which is an example of bartering?',
                            ['Putting coins in a piggy bank', 'Paying with a note', 'Swapping a toy for a book'],
                            2,
                            'Bartering means swapping things without using money.'
                        ),
                        self::quiz(
                            'You have two 5 coins and one 1 coin. How much money is that?',
                            ['11', '10', '7'],
                            0,
                            '5 + 5 + 1 = 11.'
                        ),
                        self::quiz(
                            'Why did people invent money?',
                            ['To stop people working', 'To make buying and selling easier', 'To make toys cost more'],
                            1,
                            'Money makes it easy to trade with anyone.'
                        ),
                        self::quiz(
                            'Which person is earning money?',
                            ['A child watching TV', 'A cat taking a nap', 'A baker selling bread'],
                            2,
                            'The baker is doing useful work and getting paid.'
                        ),
                    ]
                ),
            ]
        );
    }

    private function needsAndWants(): array
    {
        return self::module(
            'Needs and Wants',
            'Learn to tell the difference between what we must have and what is nice to have, and how to choose wisely.',
            [
                self::lesson(
                    'Needs: What We Must Have',
                    'Discover the things every person needs to stay safe and healthy.',
                    ['Needs and Wants'],
                    [
                        self::story(
                            'What Do We Need?',
                            "A need is something you must have to stay alive, safe, and healthy. Everyone needs clean water, food, a safe place to live, clothes to keep warm or cool, and medicine when sick. Children also need school to learn.\n\nNeeds come first. When a family has only a little money, they spend it on needs before anything else.\n\nHere is a trick to spot a need. Ask yourself, 'Could I stay safe and healthy without it?' If the answer is no, it is a need."
                        ),
                        self::quiz(
                            'Which of these is a need?',
                            ['A video game', 'Clean water', 'Sparkly stickers'],
                            1,
                            'We cannot stay healthy without clean water.'
                        ),
                        self::quiz(
                            'Which question helps you find out if something is a need?',
                            ['Do my friends have it?', 'Could I stay safe and healthy without it?', 'Is it on sale?'],
                            1,
                            'If you could not stay safe and healthy without it, it is a need.'
                        ),
                        self::project(
                            'Needs Hunt',
                            'Go on a needs hunt around your home.',
                            [
                                'Find something that gives your family food or water.',
                                'Find something that keeps you warm or cool.',
                                'Find something that helps you learn.',
                                'Draw or write five needs you found.',
                                'Tell someone why each one is a need.',
                            ]
                        ),
                    ]
                ),

                self::lesson(
                    'Wants: Nice to Have',
                    'Learn what wants are and how a thing can be partly a need and partly a want.',
                    ['Needs and Wants'],
                    [
                        self::story(
                            'Wants Are Fun, Too',
                            "A want is something that would be nice to have, but you can be safe and healthy without it. Toys, treats, games, and fancy shoes are usually wants. Wants are not bad! They make life fun.\n\nSome things are a bit of both. Ana needs shoes to protect her feet, but she only wants a special pair that lights up. The shoes are a need. The lights are a want.\n\nWhen money is limited, families take care of needs first. Then, if there is money left over, they can choose some wants."
                        ),
                        self::quiz(
                            'Ana needs shoes to walk to school. Which part is a want?',
                            ['Having shoes to protect her feet', 'Walking to school', 'Choosing shoes that light up'],
                            2,
                            'Shoes are a need. The lights are an extra, so they are a want.'
                        ),
                        self::quiz(
                            'What should a family usually pay for first?',
                            ['Needs', 'Wants', 'Whatever is most fun'],
                            0,
                            'Needs keep us safe and healthy, so they come first.'
                        ),
                        self::project(
                            'Need or Want Sort',
                            'Sort things into two columns: Needs and Wants.',
                            [
                                'Draw two columns on paper and label them Needs and Wants.',
                                'Sort these: apple, bed, ice cream, school bag, toy robot, soap.',
                                'Add three more things of your own to each column.',
                                'Pick the trickiest one and explain your choice to a grown-up.',
                            ]
                        ),
                    ]
                ),

                self::lesson(
                    'Making Smart Choices',
                    'Learn about trade-offs and a simple way to choose before you spend.',
                    ['Needs and Wants', 'Decision Making'],
                    [
                        self::story(
                            'Stop, Think, Choose',
                            "We cannot buy everything, so we have to choose. When you choose one thing, you give up the other. This is called a trade-off.\n\nLeo has 10 coins. He wants a 10-coin comic book and a 10-coin ball. If he buys the comic, he cannot buy the ball with those coins.\n\nUse Stop, Think, Choose. STOP means do not rush. THINK means ask: Is it a need or a want? How much does it cost? How will I feel tomorrow? CHOOSE means pick what matters most to you.\n\nA smart choice is not always the cheapest or the most exciting. It is the one that fits what matters most."
                        ),
                        self::quiz(
                            'Leo has 10 coins. He buys the comic book. What did he give up?',
                            ['The chance to buy the ball with the same coins', 'His needs', 'Nothing at all'],
                            0,
                            'Choosing one thing meant giving up the other. That is a trade-off.'
                        ),
                        self::quiz(
                            'What does STOP mean in Stop, Think, Choose?',
                            ['Never visit shops', 'Pause before you spend', 'Buy it quickly'],
                            1,
                            'STOP reminds you to pause before spending.'
                        ),
                        self::aiChat(
                            'Penny\'s Choosing Game',
                            'Choose between two items and explain your choice to Penny.',
                            'Penny will give you two items that cost the same. Use Stop, Think, Choose, then tell Penny which you would pick and why.',
                            self::PENNY . ' Offer the child a choice between two items that cost the same, ask them to use Stop, Think, Choose, and ask why they chose. Never say a choice is wrong.'
                        ),
                    ]
                ),

                self::checkup(
                    'Module 2 Check-Up',
                    'Show what you learned about needs, wants, and choices.',
                    ['Needs and Wants', 'Decision Making'],
                    [
                        self::quiz(
                            'Which is a need?',
                            ['Medicine when you are sick', 'A new video game', 'A toy dragon'],
                            0,
                            'Medicine helps keep you healthy.'
                        ),
                        self::quiz(
                            'Which is a want?',
                            ['Water to drink', 'A safe home', 'A ride at a fun fair'],
                            2,
                            'You can be safe and healthy without a fun fair ride.'
                        ),
                        self::quiz(
                            'What is a trade-off?',
                            ['Getting everything you want', 'Choosing one thing means giving up another', 'Trading in a shop for free'],
                            1,
                            'A trade-off means giving up one thing to get another.'
                        ),
                        self::quiz(
                            'Money is limited. What should be paid for first?',
                            ['Wants', 'Whatever is on sale', 'Needs'],
                            2,
                            'Needs always come first.'
                        ),
                    ]
                ),
            ]
        );
    }

    private function saving(): array
    {
        return self::module(
            'Saving',
            'Learn why saving matters, how to reach a savings goal, and how to keep savings safe.',
            [
                self::lesson(
                    'Why Save?',
                    'Find out what saving means and why it helps.',
                    ['Saving and Budgeting'],
                    [
                        self::story(
                            'Saving for Later',
                            "Saving means keeping some of your money for later instead of spending it all now. There are three big reasons to save.\n\nFirst, for a goal, like a bicycle that costs more than you have today. Second, for surprises, like a broken bike tyre or a family emergency. Third, for the future, when you are older.\n\nSaving takes patience. You wait, and the reward is bigger. Even small amounts add up. If you save 1 coin every day, you will have 7 coins after one week."
                        ),
                        self::quiz(
                            'What does saving mean?',
                            ['Keeping some money for later', 'Spending money right away', 'Giving all your money away'],
                            0,
                            'Saving means keeping money for later.'
                        ),
                        self::quiz(
                            'Kim saves 1 coin every day. How many coins does she have after 7 days?',
                            ['1', '17', '7'],
                            2,
                            '1 coin each day for 7 days is 7 coins.'
                        ),
                        self::project(
                            'My Saving Reason',
                            'Think about something you would love to save for.',
                            [
                                'Choose one thing you would like to save for.',
                                'Draw a picture of it.',
                                'Write why it matters to you.',
                                'Share your picture with a grown-up.',
                            ]
                        ),
                    ]
                ),

                self::lesson(
                    'Saving for a Goal',
                    'Practise working out how long it takes to reach a savings goal.',
                    ['Saving and Budgeting', 'Number Sense'],
                    [
                        self::story(
                            'Mia\'s Kite',
                            "A savings goal has two parts: what you want to buy and how much it costs.\n\nMia wants a kite that costs 40 coins. Each week she gets 5 coins and saves all of it. After 1 week she has 5. After 2 weeks she has 10. She keeps counting: 15, 20, 25, 30, 35, 40. That took 8 weeks.\n\nMia can reach her goal faster if she saves a little more each week or earns extra. A chart helps, because you can colour in a step each time you save and watch your progress grow."
                        ),
                        self::quiz(
                            'Mia\'s kite costs 40 coins. She has saved 25. How many more coins does she need?',
                            ['25', '15', '10'],
                            1,
                            '40 - 25 = 15.'
                        ),
                        self::quiz(
                            'Sam saves 10 coins each week for a 50-coin game. How many weeks will it take?',
                            ['6', '4', '5'],
                            2,
                            '10, 20, 30, 40, 50. That is 5 weeks.'
                        ),
                        self::project(
                            'My Savings Ladder',
                            'Make a chart to track a savings goal.',
                            [
                                'Pick a goal and ask a grown-up how much it costs.',
                                'Decide how many coins you can save each week.',
                                'Draw a ladder with one step for each week you need.',
                                'Colour in one step each time you save.',
                                'Celebrate when you reach the top!',
                            ]
                        ),
                    ]
                ),

                self::lesson(
                    'Keeping Savings Safe',
                    'Learn where savings can be kept safe and what a bank does.',
                    ['Saving and Budgeting', 'Financial Responsibility'],
                    [
                        self::story(
                            'The Safe Place',
                            "Where should savings go? A piggy bank or a jar at home keeps money close. But coins can be lost, forgotten, or spent by mistake.\n\nMany families use a bank. A bank is a business that helps keep money safe. A grown-up can open a savings account for a child.\n\nSome banks add a little extra money to savings as a thank-you for keeping money there. That extra money is called interest. Over time, savings can grow.\n\nAlways save with a trusted grown-up helping you, and never tell strangers how much money you have."
                        ),
                        self::quiz(
                            'What is a bank?',
                            ['A shop that sells toys', 'A place that helps keep money safe', 'A kind of game'],
                            1,
                            'A bank helps keep money safe.'
                        ),
                        self::quiz(
                            'What is interest?',
                            ['Extra money a bank may add to savings', 'A fee to go inside a bank', 'A hobby'],
                            0,
                            'Interest is extra money added to savings.'
                        ),
                        self::aiChat(
                            'Ask Penny About Safe Savings',
                            'Ask Penny why a grown-up should help with savings and where money is safe.',
                            'Ask Penny: Where can I keep my savings safe? Why should I ask a grown-up to help me?',
                            self::PENNY . ' Focus on safe places to keep savings and why a trusted grown-up should help.'
                        ),
                    ]
                ),

                self::checkup(
                    'Module 3 Check-Up',
                    'Show what you learned about saving.',
                    ['Saving and Budgeting', 'Number Sense'],
                    [
                        self::quiz(
                            'Which is a good reason to save?',
                            ['To avoid ever buying anything', 'To lose your money', 'To buy something bigger later'],
                            2,
                            'Saving helps you reach bigger goals.'
                        ),
                        self::quiz(
                            'Jo has 18 coins and wants a 30-coin book. How much more does Jo need?',
                            ['12', '10', '48'],
                            0,
                            '30 - 18 = 12.'
                        ),
                        self::quiz(
                            'Where is a safe place to keep savings?',
                            ['Lying around on the playground', 'In a bank or safe place with a grown-up\'s help', 'In a friend\'s bag'],
                            1,
                            'A bank or safe place, with a trusted grown-up helping.'
                        ),
                        self::quiz(
                            'Rina saves 2 coins each day. How many coins does she have after 5 days?',
                            ['7', '25', '10'],
                            2,
                            '2 + 2 + 2 + 2 + 2 = 10.'
                        ),
                    ]
                ),
            ]
        );
    }

    private function spendingAndSharing(): array
    {
        return self::module(
            'Spending Wisely and Sharing',
            'Learn to compare prices, share with others, and stay safe with money.',
            [
                self::lesson(
                    'Comparing Prices',
                    'Learn how to compare prices and think about value before you buy.',
                    ['Spending Decisions', 'Number Sense'],
                    [
                        self::story(
                            'The Price Detective',
                            "Before you buy something, compare! The same item can cost different amounts in different places. Lina needs a notebook. Shop A sells it for 8 coins. Shop B sells the very same one for 6 coins. Lina saves 2 coins by choosing Shop B.\n\nBut the cheapest item is not always the best. A very cheap pencil that snaps right away may cost more in the end, because Lina must buy another one.\n\nValue means getting the best result for your money. Ask: Is it the same item? Will it last? Do I really need it?"
                        ),
                        self::quiz(
                            'The same ball costs 12 coins at one shop and 9 coins at another. How much do you save at the cheaper shop?',
                            ['21', '3', '9'],
                            1,
                            '12 - 9 = 3.'
                        ),
                        self::quiz(
                            'Why is the cheapest item not always the best choice?',
                            ['Price never matters', 'It might break quickly, so you must buy another', 'Cheaper things are always better'],
                            1,
                            'An item that breaks quickly can cost more in the end.'
                        ),
                        self::project(
                            'Price Detective',
                            'Compare the price of one item in two places.',
                            [
                                'Choose one item, such as a notebook or a snack.',
                                'With a grown-up, find its price in two different shops or catalogues.',
                                'Write both prices side by side.',
                                'Decide which is the better value and say why.',
                            ]
                        ),
                    ]
                ),

                self::lesson(
                    'Giving and Sharing',
                    'Learn how money, time, and kindness can help others.',
                    ['Generosity and Sharing'],
                    [
                        self::story(
                            'Three Jars',
                            "Money can help other people, too. Giving means sharing some of what you have to help someone or support a good cause, such as a food drive, a neighbour, or an animal shelter.\n\nYou can give more than money. You can give your time and your kindness.\n\nMany children use three jars. The SPEND jar is for things you want now. The SAVE jar is for goals later. The SHARE jar is for helping others. If you get 10 coins, you might put 5 in Spend, 3 in Save, and 2 in Share. You decide the amounts with a grown-up."
                        ),
                        self::quiz(
                            'In the three jars idea, what is the Share jar for?',
                            ['Buying snacks today', 'Helping other people', 'Hiding money'],
                            1,
                            'The Share jar is for helping others.'
                        ),
                        self::quiz(
                            'Besides money, what else can you give to help others?',
                            ['Nothing else', 'Only toys', 'Your time and kindness'],
                            2,
                            'Time and kindness are powerful gifts.'
                        ),
                        self::project(
                            'My Three Jars',
                            'Make three jars or envelopes labelled Spend, Save, and Share.',
                            [
                                'Decorate three jars or envelopes and label them Spend, Save, and Share.',
                                'Imagine you get 10 coins. Decide how many go in each jar.',
                                'Check that the three amounts add up to 10.',
                                'Explain your choices to a grown-up.',
                            ]
                        ),
                    ]
                ),

                self::lesson(
                    'Money Safety',
                    'Learn how to protect money and spot tricks and scams.',
                    ['Financial Responsibility', 'Online Privacy and Security'],
                    [
                        self::story(
                            'Keep Your Money Safe',
                            "Money is valuable, so we protect it. Here are the safety rules.\n\nOne: keep money and cards in a safe place. Two: bank numbers and PINs are secret. Share them only with a trusted grown-up in your family, and never with friends or people online. Three: never buy anything online or type payment details without a grown-up.\n\nFour: watch out for tricks. A scam is a trick to take your money or secrets. If a message says 'You won a prize! Send your family's bank number!' it is probably a scam. If something sounds too good to be true, it usually is.\n\nFive: if you lose money or feel unsure, tell a trusted grown-up. Asking for help is always a smart choice."
                        ),
                        self::quiz(
                            'A message says: You won! Send your family\'s bank number to get your prize. What should you do?',
                            ['Send it quickly', 'Do not reply, and tell a trusted grown-up', 'Share it with friends'],
                            1,
                            'Real prizes never need your family\'s secret numbers. Tell a grown-up.'
                        ),
                        self::quiz(
                            'What is the safest way to treat bank numbers and PINs?',
                            ['Post them online', 'Tell a new online friend', 'Keep them private, and share only with a trusted grown-up in your family'],
                            2,
                            'Bank numbers and PINs are secrets.'
                        ),
                        self::project(
                            'Scam Spotter',
                            'Read three messages and decide which ones are tricks.',
                            [
                                'Message 1: Your school is selling tickets for the fair. Ask a grown-up to read the note.',
                                'Message 2: You won 1,000 coins! Reply with your parent\'s card number to get them.',
                                'Message 3: Free game coins! Click now and type your password.',
                                'Decide which messages are tricks and circle the warning signs, such as rushing you or asking for secrets.',
                                'Tell a grown-up what you found.',
                            ]
                        ),
                    ]
                ),

                self::checkup(
                    'Module 4 Check-Up',
                    'Show what you learned about prices, sharing, and safety.',
                    ['Spending Decisions', 'Generosity and Sharing', 'Financial Responsibility'],
                    [
                        self::quiz(
                            'Pencils cost 5 coins at Shop A, 3 coins at Shop B, and 4 coins at Shop C. Which shop has the lowest price?',
                            ['Shop B', 'Shop A', 'Shop C'],
                            0,
                            '3 coins is the lowest price.'
                        ),
                        self::quiz(
                            'In the three jars idea, which jar is for goals later?',
                            ['Spend', 'Share', 'Save'],
                            2,
                            'The Save jar is for goals later.'
                        ),
                        self::quiz(
                            'Which is a sign of a scam?',
                            ['A receipt from a shop', 'A note from a family member you can see', 'A message that rushes you and asks for secret numbers'],
                            2,
                            'Rushing you and asking for secrets are warning signs.'
                        ),
                        self::quiz(
                            'You get 10 coins. You put 5 in Spend and 3 in Save. How many go in Share?',
                            ['2', '3', '5'],
                            0,
                            '10 - 5 - 3 = 2.'
                        ),
                    ]
                ),
            ]
        );
    }

    private function finalQuest(): array
    {
        return self::module(
            'Final Quest: Your Money Plan',
            'Put everything together with your own money plan, then take the final test.',
            [
                self::lesson(
                    'Build Your Money Plan',
                    'Make a simple plan that gives every coin a job.',
                    ['Saving and Budgeting', 'Decision Making'],
                    [
                        self::story(
                            'Every Coin Has a Job',
                            "You have learned so much! Now you will make a Money Plan. A Money Plan has four parts: money for needs, money to save, money to share, and money to spend on a treat.\n\nZane gets 20 coins. He puts 6 coins aside for school supplies, which are a need. He saves 8 coins toward a 40-coin set of paints. He shares 2 coins for a food drive. He keeps 4 coins for a treat. Check: 6 + 8 + 2 + 4 = 20. Every coin has a job."
                        ),
                        self::project(
                            'My Money Plan',
                            'Imagine you get 20 coins. Make your own plan.',
                            [
                                'Write 20 at the top of your page.',
                                'Choose how many coins go to needs, saving, sharing, and a treat.',
                                'Add them up. They must total exactly 20.',
                                'Write your savings goal and how many weeks it will take.',
                                'Show your plan to a grown-up.',
                            ]
                        ),
                        self::aiChat(
                            'Show Penny Your Plan',
                            'Tell Penny about your Money Plan and what you chose to save for.',
                            'Tell Penny about your plan. What are you saving for? Why did you choose to share the amount you did?',
                            self::PENNY . ' Celebrate the child\'s plan, ask what they are saving for, and ask one thoughtful question about their choices.'
                        ),
                    ],
                    10,
                    15
                ),

                self::lesson(
                    'Final Quest: Money Smart Test',
                    'Answer eight questions to show you are Money Smart!',
                    ['Money Awareness', 'Financial Responsibility'],
                    [
                        self::quiz(
                            'What is bartering?',
                            ['Putting money in a bank', 'Swapping things without using money', 'Saving coins in a jar'],
                            1,
                            'Bartering means swapping things without money.'
                        ),
                        self::quiz(
                            'You have a 10 note, a 5 coin, and a 5 coin. How much is that?',
                            ['20', '15', '25'],
                            0,
                            '10 + 5 + 5 = 20.'
                        ),
                        self::quiz(
                            'Which is a need?',
                            ['A toy robot', 'Candy', 'Clean water'],
                            2,
                            'Clean water keeps us healthy.'
                        ),
                        self::quiz(
                            'Why do we use Stop, Think, Choose?',
                            ['To make careful choices before spending', 'To buy things faster', 'To avoid saving'],
                            0,
                            'It helps us pause and choose wisely.'
                        ),
                        self::quiz(
                            'Mia has 12 coins and wants a 20-coin book. How many more coins does she need?',
                            ['6', '8', '32'],
                            1,
                            '20 - 12 = 8.'
                        ),
                        self::quiz(
                            'Which one shows saving?',
                            ['Spending all your coins at once', 'Losing your coins', 'Putting 2 coins in a jar each week'],
                            2,
                            'Saving means keeping money for later.'
                        ),
                        self::quiz(
                            'Two shops sell the same bag. Shop A charges 15 coins and Shop B charges 11 coins. Which has the better price?',
                            ['Shop A', 'Shop B', 'They are the same'],
                            1,
                            '11 coins is less than 15 coins.'
                        ),
                        self::quiz(
                            'Someone online offers a prize if you tell them your family\'s PIN. What do you do?',
                            ['Do not share it, and tell a trusted grown-up', 'Tell them quickly', 'Ask friends to share it'],
                            0,
                            'PINs are secret. Tell a trusted grown-up.'
                        ),
                    ],
                    12,
                    30
                ),
            ]
        );
    }
}