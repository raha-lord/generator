<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Chat\Chat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatHistoryController extends Controller
{
    /**
     * Display user's chat history.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Load chats with service and first 3 messages
        $chats = Chat::with(['service', 'messages' => function($query) {
                $query->orderBy('created_at', 'asc')->limit(3);
            }])
            ->where('user_id', $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('history.index', [
            'chats' => $chats
        ]);
    }
}
