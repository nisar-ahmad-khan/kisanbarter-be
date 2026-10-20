<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of messages for the authenticated user.
     * Can filter by conversation with a specific user using `?with_user_id={id}`.
     */
    public function index(Request $request): JsonResponse
    {
        $user =$request->user();

        if ($request->has('with_user_id')) {
            $otherUserId =$request->query('with_user_id');

            $messages = Message::with(['sender', 'receiver'])
                ->where(function ($q) use ($user, $otherUserId) {$q->where('sender_id', $user->id)->where('receiver_id',$otherUserId);
                })
                ->orWhere(function ($q) use ($user, $otherUserId) {$q->where('sender_id', $otherUserId)->where('receiver_id',$user->id);
                })
                ->orderBy('created_at', 'asc')
                ->paginate(50);

            return response()->json($messages);
        }

        // General list of messages sent or received by the user
        $messages = Message::with(['sender', 'receiver'])
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json($messages);
    }

    /**
     * Store a newly created message in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated =$request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $user =$request->user();

        if ($validated['receiver_id'] ==$user->id) {
            return response()->json([
                'message' => 'You cannot send a message to yourself.',
            ], 422);
        }

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $validated['receiver_id'],
            'body' => $validated['body'],
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message->load(['sender', 'receiver']),
        ], 201);
    }

    /**
     * Display the specified message.
     */
    public function show(Request $request, Message$message): JsonResponse
    {
        $user =$request->user();

        // Authorization: Only sender or receiver can view the message
        if ($message->sender_id !==$user->id && $message->receiver_id !==$user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'message' => $message->load(['sender', 'receiver']),
        ]);
    }
}