<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The admin inbox for enquiries submitted through the public contact form,
 * including replying to the sender by email from inside the panel.
 */
class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all'); // all | new | replied | closed
        $search = trim((string) $request->query('q', ''));

        $messages = ContactMessage::query()
            ->when(in_array($filter, ['new', 'read', 'replied', 'closed'], true),
                fn ($q) => $q->where('status', $filter))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.contacts.index', [
            'messages'    => $messages,
            'filter'      => $filter,
            'search'      => $search,
            'newCount'    => ContactMessage::unread()->count(),
            'totalCount'  => ContactMessage::count(),
            'repliedCount' => ContactMessage::where('status', 'replied')->count(),
        ]);
    }

    public function show(ContactMessage $contact)
    {
        // Opening an enquiry is what marks it read; anything already further
        // along the lifecycle (replied/closed) keeps its status.
        if ($contact->status === 'new') {
            $contact->update(['status' => 'read']);
        }

        return view('admin.contacts.show', ['message' => $contact]);
    }

    /**
     * Email the sender and record what was said.
     */
    public function reply(Request $request, ContactMessage $contact)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'body'    => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        try {
            Mail::to($contact->email, $contact->name)
                ->send(new ContactReplyMail($contact, $data['body'], $data['subject']));
        } catch (\Throwable $e) {
            Log::error('Contact reply send failed', [
                'contact_message_id' => $contact->id,
                'error'              => $e->getMessage(),
            ]);

            // Nothing is recorded as replied when the mail never left, so the
            // enquiry stays in the queue rather than looking handled.
            return back()->with('error', 'The reply could not be sent. Check the mail configuration and try again.');
        }

        $contact->update([
            'status'      => 'replied',
            'admin_reply' => $data['body'],
            'replied_at'  => now(),
            'replied_by'  => auth()->id(),
        ]);

        return back()->with('success', 'Reply sent to ' . $contact->email . '.');
    }

    /**
     * Move an enquiry along the lifecycle without emailing anyone.
     */
    public function updateStatus(Request $request, ContactMessage $contact)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,read,replied,closed'],
        ]);

        $contact->update(['status' => $data['status']]);

        return back()->with('success', 'Enquiry marked as ' . $data['status'] . '.');
    }

    public function destroy(ContactMessage $contact)
    {
        $contact->delete();

        return redirect()
            ->route('admin.contacts.index')
            ->with('success', 'Enquiry deleted.');
    }
}
