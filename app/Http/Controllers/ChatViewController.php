<?php

namespace App\Http\Controllers;

use App\Models\Chat\Service;
use Illuminate\Http\Request;

class ChatViewController extends Controller
{
    /**
     * Show all chats.
     */
    public function index(Request $request)
    {
        return view('chat.index');
    }

    /**
     * Show chat interface.
     */
    public function show(string $uuid)
    {
        return view('chat.show', ['chatUuid' => $uuid]);
    }

    /**
     * Show service selection page.
     */
    public function create(Request $request)
    {
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('chat.create', ['services' => $services]);
    }
}
