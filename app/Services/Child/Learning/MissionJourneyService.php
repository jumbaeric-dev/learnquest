<?php

namespace App\Services\Child\Learning;


class MissionJourneyService
{


    public function getMission(string $slug): array
    {

        $missions = [


            'space-robot' => [

                'title'=>'Build A Space Robot',

                'icon'=>'🪐',

                'description'=>
                    'Help a robot explore a new planet.',


                'xp'=>100,


                'steps'=>[


                    [

                        'title'=>'Discover Robots',

                        'description'=>
                        'Learn what robots are and how they help us.',

                        'icon'=>'🤖',

                    ],


                    [

                        'title'=>'Robot Instructions',

                        'description'=>
                        'Learn simple coding commands.',

                        'icon'=>'💻',

                    ],


                    [

                        'title'=>'Design Your Robot',

                        'description'=>
                        'Create your own robot idea.',

                        'icon'=>'🎨',

                    ],


                ]

            ]

        ];


        return $missions[$slug];

    }


}