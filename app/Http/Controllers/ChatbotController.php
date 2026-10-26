<?php

namespace App\Http\Controllers;

use App\Services\RagChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    protected RagChatbotService $ragService;

    public function __construct(RagChatbotService $ragService)
    {
        $this->ragService = $ragService;
    }

    /**
     * Submit a user query to the RAG AI Assistant.
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $userMessage = $validated['message'];

        $response = $this->ragService->ask($userMessage, $user, $request->session()->getId());

        // Append to session history
        $history = $request->session()->get('chatbot_history', []);
        $history[] = [
            'sender' => 'user',
            'text' => $userMessage,
            'time' => now()->format('h:i A'),
        ];
        $history[] = [
            'sender' => 'bot',
            'text' => $response['message'],
            'intent' => $response['intent'],
            'confidence' => $response['confidence'],
            'actions' => $response['actions'],
            'sources' => $response['sources'],
            'time' => now()->format('h:i A'),
        ];

        // Keep last 20 messages in session
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        $request->session()->put('chatbot_history', $history);

        return response()->json([
            'success' => true,
            'reply' => $response['message'],
            'intent' => $response['intent'],
            'confidence' => $response['confidence'],
            'actions' => $response['actions'],
            'sources' => $response['sources'],
            'time' => now()->format('h:i A'),
        ]);
    }

    /**
     * Get current session chat history.
     */
    public function history(Request $request): JsonResponse
    {
        $history = $request->session()->get('chatbot_history', []);
        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Clear session chat history.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget('chatbot_history');
        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared.',
        ]);
    }
}
