<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\RagChatbotService;
use Tests\TestCase;

class RagChatbotTest extends TestCase
{
    public function test_chatbot_responds_to_general_faq_with_rag_grounding(): void
    {
        $response = $this->postJson('/chatbot/message', [
            'message' => 'How does the two-step OTP verification work on VaishnoMart?',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('intent', 'kb_otp_verification');

        $this->assertStringContainsString('Pickup OTP', $response->json('reply'));
        $this->assertNotEmpty($response->json('sources'));

        $this->assertDatabaseHas('chatbot_interactions', [
            'detected_intent' => 'kb_otp_verification',
        ]);
    }

    public function test_chatbot_retrieves_live_products_matching_query(): void
    {
        $response = $this->postJson('/chatbot/message', [
            'message' => 'Do you have drinking water or bubble water jars?',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $reply = $response->json('reply');
        $this->assertTrue(
            str_contains(strtolower($reply), 'water') || str_contains(strtolower($reply), 'deposit')
        );
        $this->assertNotEmpty($response->json('actions'));
    }

    public function test_chatbot_order_tracking_personalized_rag(): void
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        $order = Order::where('customer_id', $customer->id)->first();

        if ($order) {
            $response = $this->actingAs($customer)->postJson('/chatbot/message', [
                'message' => "Where is my order #{$order->order_number}?",
            ]);

            $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('intent', 'order_tracking');

            $this->assertStringContainsString($order->order_number, $response->json('reply'));
        }
    }

    public function test_chatbot_session_history_and_clear(): void
    {
        $this->postJson('/chatbot/message', [
            'message' => 'What is the refund policy?',
        ]);

        $historyResponse = $this->getJson('/chatbot/history');
        $historyResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNotEmpty($historyResponse->json('history'));

        // Clear history
        $clearResponse = $this->postJson('/chatbot/clear');
        $clearResponse->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
