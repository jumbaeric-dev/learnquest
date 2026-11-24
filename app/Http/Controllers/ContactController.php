<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:150'],
            'topic'   => ['required', 'in:general,billing,technical,school'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'website' => ['nullable', 'max:0'], // honeypot: must stay empty
        ]);

        $to = config('learnquest.support_email');

        try {
            Mail::raw(
                "Topic: {$data['topic']}\nFrom: {$data['name']} <{$data['email']}>\n\n{$data['message']}",
                function ($mail) use ($to, $data) {
                    $mail->to($to)
                        ->replyTo($data['email'], $data['name'])
                        ->subject('[LearnQuest Support] ' . ucfirst($data['topic']) . ' enquiry');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Contact form mail failed', ['error' => $e->getMessage()]);

            return redirect()->to(url('/') . '#contact')
                ->withInput()
                ->withErrors(['message' => 'We could not send your message right now. Please email us directly.']);
        }

        return redirect()->to(url('/') . '#contact')
            ->with('contact_success', 'Thanks! Your message is on its way. We usually reply within 1 business day.');
    }
}