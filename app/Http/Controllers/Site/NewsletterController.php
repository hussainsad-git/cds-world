<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Support\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        if ($request->filled('website')) { // honeypot
            return $this->respond($request, true, 'Thank you for subscribing.');
        }

        $v = validator($request->all(), [
            'email' => ['required', 'email:rfc', 'max:190'],
            'name' => ['nullable', 'string', 'max:120'],
            'consent' => ['accepted'],
        ], ['consent.accepted' => 'Please tick the box to confirm you agree to receive emails.']);

        if ($v->fails()) {
            return $this->respond($request, false, $v->errors()->first());
        }

        $email = strtolower(trim($request->input('email')));
        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing && $existing->status === 'active') {
            return $this->respond($request, true, 'You are already subscribed. Thank you!');
        }

        $sub = $existing ?? new NewsletterSubscriber(['email' => $email]);
        $sub->fill([
            'name' => $request->input('name') ?: $sub->name,
            'status' => 'pending',
            'consented_at' => now(),
            'consent_text' => Site::get('newsletter', 'consent_text'),
            'source' => $request->input('source', 'website'),
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
            'unsubscribed_at' => null,
        ]);
        $sub->confirm_token = \Illuminate\Support\Str::random(48);
        $sub->save();

        try {
            $link = url('/newsletter/confirm/'.$sub->confirm_token);
            Mail::raw("Please confirm your subscription to the ".Site::name()." newsletter:\n\n{$link}\n\nIf you did not request this, you can ignore this email.", fn ($m) => $m->to($sub->email)->subject('Confirm your subscription'));
            if ($admin = Site::get('newsletter', 'admin_notify_email')) {
                Mail::raw("New newsletter sign-up (pending confirmation): {$sub->email}", fn ($m) => $m->to($admin)->subject('New newsletter subscriber'));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->respond($request, true, 'Almost done! Please check your inbox and confirm your email address.');
    }

    public function confirm(string $token)
    {
        $sub = NewsletterSubscriber::where('confirm_token', $token)->first();
        if ($sub) {
            $sub->forceFill(['status' => 'active', 'confirmed_at' => now(), 'confirm_token' => null])->save();
        }

        return view('site.message', ['heading' => $sub ? 'Subscription confirmed' : 'Link not valid', 'body' => $sub ? 'Thank you. You are now subscribed to the newsletter.' : 'This confirmation link is invalid or has already been used.', 'seo' => ['title' => 'Newsletter', 'noindex' => true]]);
    }

    public function unsubscribe(string $token)
    {
        $sub = NewsletterSubscriber::where('unsubscribe_token', $token)->first();
        if ($sub && $sub->status !== 'unsubscribed') {
            $sub->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
        }

        return view('site.message', ['heading' => $sub ? 'You have been unsubscribed' : 'Link not valid', 'body' => $sub ? 'You will no longer receive our newsletter.' : 'This unsubscribe link is not valid.', 'seo' => ['title' => 'Unsubscribe', 'noindex' => true]]);
    }

    private function respond(Request $request, bool $ok, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'newsletter_ok' : 'newsletter_error', $message);
    }
}
