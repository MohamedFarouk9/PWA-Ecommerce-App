<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contact\ReplyContactRequest;
use App\Http\Requests\Admin\Contact\StoreRequest;
use App\Models\Contact;
use App\Repositories\Contracts\ContactRepositoryInterface;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ResponseTrait;

    public function __construct(private ContactRepositoryInterface $contactRepository) {}

    /**
     * Display a listing of contact messages (Admin Inbox)
     */
    public function index(Request $request)
    {
        $query = Contact::with('user')->latest();

        if ($request->has('unread') && $request->unread == '1') {
            $query->where('is_read', false);
        }

        $contacts = $query->get();
        $unreadCount = Contact::where('is_read', false)->count();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status'      => 'success',
                'unreadCount' => $unreadCount,
                'contacts'    => $contacts,
            ]);
        }

        return view('admin.contacts.index', compact('contacts', 'unreadCount'));
    }

    /**
     * Display a specific contact message
     */
    public function show($id, Request $request)
    {
        $contact = Contact::with('user')->findOrFail($id);

        if (!$contact->is_read) {
            $contact->markAsRead();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status'  => 'success',
                'contact' => $contact,
            ]);
        }

        return view('admin.contacts.show', compact('contact'));
    }

    /**
     * Toggle read status of a contact message
     */
    public function markRead(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        if ($contact->is_read) {
            $contact->markAsUnread();
            $message = 'Marked as unread.';
        } else {
            $contact->markAsRead();
            $message = 'Marked as read.';
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Mark all messages as read
     */
    public function markAllRead(Request $request)
    {
        Contact::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'All contact messages marked as read.']);
        }

        return back()->with('success', 'All contact messages marked as read.');
    }

    /**
     * Record a reply to the customer message
     */
    public function reply(ReplyContactRequest $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $validated = $request->validated();

        $contact->update([
            'reply_message' => $validated['reply_message'],
            'replied_at'    => now(),
            'is_read'       => true,
            'read_at'       => $contact->read_at ?? now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Reply recorded successfully.',
                'contact' => $contact,
            ]);
        }

        return back()->with('success', 'Reply recorded successfully.');
    }

    /**
     * Remove the specified contact message
     */
    public function destroy(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Contact message deleted.']);
        }

        return redirect()->route('admin.contacts.index')->with('success', 'Contact message deleted.');
    }

    /**
     * Store contact message (API endpoint for frontend contact form)
     */
    public function postContact(StoreRequest $request)
    {
        $contact = $this->contactRepository->create($request->validated());

        return response()->json([
            'message' => 'Contact details submitted successfully',
            'contact' => $contact,
        ], 201);
    }
}
