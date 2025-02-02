<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageEvent;

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
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
        ]);

        try {
            $messages = Message::where(function ($query) use ($request) {
                $query->where('sender_id', $request->sender_id)->where('receiver_id', $request->receiver_id);
            })->orWhere(function ($query) use ($request) {
                $query->where('sender_id', $request->receiver_id)->where('receiver_id', $request->sender_id);
            })->orderBy('created_at', 'asc')->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to get messages!', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Messages fetched!', 'data' => $messages], 200);
    }
}
