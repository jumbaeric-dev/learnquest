<?php

namespace App\Livewire\Child\Missions\Components;

use Livewire\Component;


class DailyChallenges extends Component
{

    public array $challenges = [];


    public function mount()
    {

        $this->challenges = [

            [
                'id'=>1,
                'icon'=>'🔥',
                'title'=>'Complete one lesson',
                'xp'=>20,
                'completed'=>false,
            ],


            [
                'id'=>2,
                'icon'=>'🎨',
                'title'=>'Create something',
                'xp'=>30,
                'completed'=>false,
            ],


            [
                'id'=>3,
                'icon'=>'🤖',
                'title'=>'Ask Nova AI',
                'xp'=>10,
                'completed'=>false,
            ],

        ];

    }



    public function completeChallenge($id)
    {

        foreach($this->challenges as &$challenge){

            if($challenge['id'] === $id){

                $challenge['completed'] = true;


                session()->flash(
                    'message',
                    "+{$challenge['xp']} XP earned 🎉"
                );

            }

        }

    }



    public function render()
    {
        return view(
            'livewire.child.missions.components.daily-challenges'
        );
    }

}