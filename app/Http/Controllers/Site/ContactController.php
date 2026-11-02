<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        if ($request->filled('website')) { // honeypot
            return back();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $msg = ContactMessage::create($data + ['status' => 'new', 'ip_hash' => hash('sha256', $request->ip().config('app.key'))]);

        $to = Site::get('general', 'contact_email');
        if ($to) {
            try {
                Mail::raw("New message from the CDS World website\n\nName: {$msg->name}\nEmail: {$msg->email}\nPhone: {$msg->phone}\nSubject: {$msg->subject}\n\n{$msg->message}", fn ($m) => $m->to($to)->replyTo($msg->email, $msg->name)->subject('[Website] '.$msg->subject));
            } catch (\Throwable $e) {
                report($e); // the message is already stored in the admin inbox
            }
        }

        return back()->with('contact_sent', 'Thank you. Your message has been sent and we will get back to you soon.');
    }
}
