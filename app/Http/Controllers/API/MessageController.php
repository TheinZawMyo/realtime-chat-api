<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class MessageController extends Controller
{
    // ==================== SEND MESSAGE ====================
    public function sendMessage(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,png,pdf,docx|max:2048'
        ]);

        
        try {
            $message = new Message();
            $message->sender_id = $request->sender_id;
            $message->receiver_id = $request->receiver_id;
            $message->message = $request->message;
            if ($request->hasFile('attachment')) {
                $attachment = $request->file('attachment');
                $attachmentName = time() . '.' . $attachment->getClientOriginalExtension();
                $attachment->move(public_path('attachments'), $attachmentName);
                $message->attachment = 'attachments/' . $attachmentName;
            }
            $message->save();
            
            broadcast(new MessageEvent($message))->toOthers();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send message!', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Message sent!', 'data' => $message], 201);
    }

    // ==================== GET MESSAGES ====================
    public function getMessages(Request $request)
    {
        $request->validate([
            'contact_user_id' => 'required|exists:users,id',
        ]);

        $authUserId = Auth::id();

        try {
            $messages = Message::where(function ($query) use ($request, $authUserId) {
                $query->where('sender_id', $request->contact_user_id)->where('receiver_id', $authUserId);
            })->orWhere(function ($query) use ($request, $authUserId) {
                $query->where('sender_id', $authUserId)->where('receiver_id', $request->contact_user_id);
            })->orderBy('created_at', 'asc')->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to get messages!', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Messages fetched!', 'data' => $messages], 200);
    }


    // ================= GET USER LIST WHO HAVE CONNECT ====
    public function getContactUsersAndLastMessage() 
    {
        $authUserId = Auth::id();
        // Get all users who have interacted with the authenticated user
        $contactUsers = User::whereHas('sentMessages', function ($query) use ($authUserId) {
                $query->where('receiver_id', $authUserId);
            })
            ->orWhereHas('receivedMessages', function ($query) use ($authUserId) {
                $query->where('sender_id', $authUserId);
            })
            // ->with(['sentMessages', 'receivedMessages'])
            ->get();

        

        // Format the response to include the last message
        $formattedContacts = $contactUsers->map(function ($user) use ($authUserId) {
            // Get all messages between the authenticated user and this contact user
            $mQuery = Message::query();
            $mQuery->where(function ($query) use ($user, $authUserId) {
                    $query->where('sender_id', $authUserId)
                        ->where('receiver_id', $user->id);
                })
                ->orWhere(function ($query) use ($user, $authUserId) {
                    $query->where('sender_id', $user->id)
                        ->where('receiver_id', $authUserId);
                });

            $messages = $mQuery->orderBy('created_at', 'desc')->first();
                

            // Get unread messages
            $unreadMessagesCount = $this->getUnreadMessagesCount($user->id, $authUserId);

            return [
                'contactUserInfo' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'lastMessage' => $messages ? [
                        'id' => $messages->id,
                        'message' => $messages->message,
                        'created_at' => $messages->created_at,
                    ] : null,
                    'unreadMessageCount' => $unreadMessagesCount
                ],
                'lastMessageCreatedAt' => $messages ? $messages->created_at : null,
            ];
        });

        // Sort the contact users by the `created_at` timestamp of their last message
        $sortedContacts = $formattedContacts->sortByDesc('lastMessageCreatedAt');

        // Remove the temporary sorting key and return the response
        $finalResponse = $sortedContacts->map(function ($contact) {
            unset($contact['lastMessageCreatedAt']);
            return $contact;
        });

        return response()->json($finalResponse->values());
    }

    // =============== GET UNREAD MESSAGES ======
    public function getUnreadMessagesCount($contact_user_id, $authUserId) 
    {
        $unreadMessagesCount = Message::where('receiver_id', $authUserId)
                ->where('is_read', 0)
                ->where('sender_id', $contact_user_id)
                ->get()
                ->count();

        return $unreadMessagesCount;
    }

    
}
