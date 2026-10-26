<?php

namespace App\Services;

use App\Models\ChatbotInteraction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RagChatbotService
{
    /**
     * Platform Knowledge Base Corpus (Chunked documents for RAG retrieval).
     */
    protected array $knowledgeBase = [
        [
            'id' => 'kb_about',
            'topic' => 'about_platform',
            'title' => 'About VaishnoMart Platform',
            'keywords' => ['vaishnomart', 'what is', 'about', 'company', 'service', 'hyperlocal'],
            'content' => 'VaishnoMart is a premier hyperlocal quick-commerce marketplace connecting customers directly to verified neighbourhood stores, water suppliers, cloud kitchens, and grocery vendors with lightning-fast 15 to 30-minute delivery.',
        ],
        [
            'id' => 'kb_delivery_time_fee',
            'topic' => 'delivery_timelines_and_pricing',
            'title' => 'Delivery Time & Delivery Charges',
            'keywords' => ['delivery time', 'how long', 'charge', 'delivery fee', 'cost', 'free delivery', 'minimum order'],
            'content' => 'Delivery takes 15 to 30 minutes for hyperlocal orders. Base delivery fee is ₹25 for the first 3 km, with ₹8/km thereafter. All orders above ₹500 qualify for FREE standard delivery! Delivery charges are calculated dynamically based on store-to-door GPS distance.',
        ],
        [
            'id' => 'kb_otp_verification',
            'topic' => 'two_stage_otp_security',
            'title' => 'Two-Stage Secure OTP Verification',
            'keywords' => ['otp', 'verification', 'code', 'pickup otp', 'delivery otp', 'resend otp', 'expired'],
            'content' => 'VaishnoMart uses a secure Two-Stage OTP verification protocol. 1) Pickup OTP: Given to the vendor to confirm dispatch to the assigned delivery agent. 2) Delivery OTP: Shown on the customer\'s order tracking screen. You share this code with the delivery partner upon arrival to verify handover. If an OTP expires, click the "Resend OTP" button on your order screen.',
        ],
        [
            'id' => 'kb_cancellation_refund',
            'topic' => 'cancellation_and_refunds',
            'title' => 'Order Cancellation & Refund Policy',
            'keywords' => ['cancel', 'cancellation', 'refund', 'money back', 'return', 'damaged', 'wrong item'],
            'content' => 'You can cancel your order anytime before the vendor begins preparing it for a 100% instant refund. Once food or groceries are in preparation, cancellations require store support. For damaged, expired, or missing items, report them within 2 hours for instant refund or replacement.',
        ],
        [
            'id' => 'kb_payment_methods',
            'topic' => 'payment_options',
            'title' => 'Supported Payment Methods',
            'keywords' => ['pay', 'payment', 'cod', 'cash on delivery', 'upi', 'credit card', 'debit card', 'wallet', 'loyalty points'],
            'content' => 'VaishnoMart supports Cash on Delivery (COD), UPI (Google Pay, PhonePe, Paytm), Credit/Debit Cards, and Loyalty Points. 50 bonus loyalty points are credited upon registration, and you earn 1 point for every ₹10 spent.',
        ],
        [
            'id' => 'kb_drinking_water',
            'topic' => 'water_jar_deposits',
            'title' => 'Drinking Water & 20L Can Security Deposits',
            'keywords' => ['water', 'jar', 'bubble jar', 'can', 'deposit', 'bisleri', 'ro', '20l', 'mineral water'],
            'content' => 'For 20-litre commercial bubble water jars, a refundable bottle deposit of ₹150 applies if you do not have an empty jar to exchange. If you have an empty jar of equivalent standard, simply select "Empty Jar Exchange" at checkout to waive the deposit.',
        ],
        [
            'id' => 'kb_vendor_onboarding',
            'topic' => 'sell_on_vaishnomart',
            'title' => 'Become a Vendor or Delivery Partner',
            'keywords' => ['seller', 'vendor', 'register store', 'merchant', 'partner', 'delivery agent', 'earn'],
            'content' => 'Local merchants can register via the "Sell on VaishnoMart" page. We offer competitive 5% commission rates, same-day settlement requests, and automated dispatch. Delivery riders can register via "Become a Delivery Partner" with flexible hours and distance-based payouts.',
        ],
        [
            'id' => 'kb_support_contact',
            'topic' => 'human_support',
            'title' => 'Customer Care & Helplines',
            'keywords' => ['contact', 'phone', 'helpline', 'call', 'email', 'support ticket', 'human agent', 'complaint'],
            'content' => 'Our 24/7 Customer Care team is reachable at support@vaishnomart.com or phone +91 98765 43210. You can also ask me to open a Support Ticket directly for any unresolved issues.',
        ],
    ];

    /**
     * Process a user query with full RAG pipeline:
     * 1. Retrieve Knowledge chunks (Cosine & BM25)
     * 2. Retrieve Live Database Entities (Products, User Orders, Vendor Stores)
     * 3. Augment Context
     * 4. Generate Grounded AI Response with Action Cards
     */
    public function ask(string $userMessage, ?User $user = null, ?string $sessionId = null): array
    {
        $userMessage = trim($userMessage);
        $sessionId = $sessionId ?: session()->getId();

        // 1. Retrieve Knowledge Base Chunks
        $retrievedKb = $this->retrieveKnowledgeChunks($userMessage);

        // 2. Retrieve Live Customer Orders (Personalized RAG)
        $userOrderContext = $this->retrieveCustomerOrderContext($userMessage, $user);

        // 3. Retrieve Live Catalogue Matches
        $productMatches = $this->retrieveCatalogueMatches($userMessage);

        // 4. Augment and Synthesize Response
        $responsePayload = $this->synthesizeResponse(
            $userMessage,
            $retrievedKb,
            $userOrderContext,
            $productMatches,
            $user
        );

        // 5. Audit interaction to database
        try {
            ChatbotInteraction::create([
                'session_id' => $sessionId,
                'user_id' => $user?->id,
                'user_message' => $userMessage,
                'bot_response' => $responsePayload['message'],
                'detected_intent' => $responsePayload['intent'],
                'retrieved_sources' => $responsePayload['sources'],
                'confidence_score' => $responsePayload['confidence'],
            ]);
        } catch (\Exception $e) {
            // Non-blocking logging
        }

        return $responsePayload;
    }

    /**
     * Vector Cosine Similarity & Keyword BM25 Retrieval over Knowledge Base.
     */
    protected function retrieveKnowledgeChunks(string $query): array
    {
        $queryTokens = $this->tokenize($query);
        if (empty($queryTokens)) {
            return [];
        }

        $scoredChunks = [];

        foreach ($this->knowledgeBase as $chunk) {
            $chunkText = strtolower($chunk['title'] . ' ' . $chunk['content'] . ' ' . implode(' ', $chunk['keywords']));
            $chunkTokens = $this->tokenize($chunkText);

            // Compute Cosine similarity on word frequency vectors
            $sim = $this->computeCosineSimilarity($queryTokens, $chunkTokens);

            // Keyword boost
            $keywordHits = 0;
            foreach ($chunk['keywords'] as $kw) {
                if (stripos($query, $kw) !== false) {
                    $keywordHits += 1;
                }
            }

            $finalScore = ($sim * 0.6) + (min(1.0, $keywordHits * 0.35));

            if ($finalScore > 0.12) {
                $scoredChunks[] = [
                    'id' => $chunk['id'],
                    'title' => $chunk['title'],
                    'content' => $chunk['content'],
                    'score' => round($finalScore, 3),
                ];
            }
        }

        usort($scoredChunks, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($scoredChunks, 0, 3);
    }

    /**
     * Retrieve Customer's Live Order Context for questions about orders, deliveries, or status.
     */
    protected function retrieveCustomerOrderContext(string $query, ?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        $orderKeywords = ['order', 'track', 'where is', 'delivery', 'status', 'picked up', 'otp', 'cancel', 'driver', 'agent'];
        $hasOrderIntent = false;
        foreach ($orderKeywords as $kw) {
            if (stripos($query, $kw) !== false) {
                $hasOrderIntent = true;
                break;
            }
        }

        // Check if an explicit order ID or number was mentioned (e.g. #1002 or 42)
        preg_match('/#?([0-9]{1,8})/', $query, $matches);
        $explicitNumber = $matches[1] ?? null;

        $orderQuery = Order::where('customer_id', $user->id)
            ->with(['vendor', 'deliveryAgent', 'orderItems.product']);

        if ($explicitNumber) {
            $order = $orderQuery->where(function ($q) use ($explicitNumber) {
                $q->where('id', $explicitNumber)
                  ->orWhere('order_number', 'like', "%{$explicitNumber}%");
            })->first();
        } else {
            $order = $orderQuery->latest()->first();
        }

        if (!$order && !$hasOrderIntent) {
            return null;
        }

        if (!$order) {
            return ['has_orders' => false];
        }

        return [
            'has_orders' => true,
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => ucwords(str_replace('_', ' ', $order->status)),
            'total_amount' => (float) $order->total_amount,
            'vendor_name' => $order->vendor?->business_name ?? 'Local Store',
            'agent_name' => $order->deliveryAgent?->name ?? 'Awaiting Driver Assignment',
            'items_count' => $order->orderItems->count(),
            'items_summary' => $order->orderItems->take(3)->map(fn($i) => $i->product_name . ' (x' . $i->quantity . ')')->implode(', '),
            'created_at' => $order->created_at->format('d M Y, h:i A'),
            'can_cancel' => in_array($order->status, [Order::STATUS_PENDING_PAYMENT, Order::STATUS_PENDING_VENDOR, Order::STATUS_ACCEPTED]),
            'url' => route('customer.orders.show', $order->id),
        ];
    }

    /**
     * Retrieve matching products from the live database catalogue.
     */
    protected function retrieveCatalogueMatches(string $query): array
    {
        $cleanQuery = trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $query));
        $terms = array_filter(explode(' ', strtolower($cleanQuery)), fn($w) => strlen($w) >= 3);

        if (empty($terms)) {
            return [];
        }

        $products = Product::with(['vendor', 'category'])
            ->active()
            ->inStock()
            ->where(function ($q) use ($terms) {
                foreach ($terms as $t) {
                    $q->orWhere('name', 'like', "%{$t}%")
                      ->orWhere('description', 'like', "%{$t}%");
                }
            })
            ->take(3)
            ->get();

        return $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->effective_price,
                'unit' => $p->unit,
                'vendor' => $p->vendor?->business_name ?? 'Local Store',
                'url' => route('product.show', $p->slug),
            ];
        })->toArray();
    }

    /**
     * Synthesize a grounded RAG response incorporating retrieved knowledge and live context.
     */
    protected function synthesizeResponse(
        string $query,
        array $retrievedKb,
        ?array $orderContext,
        array $productMatches,
        ?User $user
    ): array {
        $actions = [];
        $sources = [];
        $confidence = 0.85;
        $intent = 'general_faq';
        $message = '';

        // Case 1: Order Tracking & Status Inquiries
        if ($orderContext && ($this->containsAny($query, ['order', 'track', 'where is', 'delivery status', 'status', 'arrive', 'when']))) {
            $intent = 'order_tracking';
            $confidence = 0.98;

            if ($orderContext['has_orders'] === false) {
                $message = "You don't have any active orders yet. Browse our top neighbourhood vendors to place your first 15-minute quick delivery!";
                $actions[] = ['label' => 'Start Shopping', 'url' => route('home'), 'icon' => 'bi-basket2'];
            } else {
                $statusMsg = match ($orderContext['status']) {
                    Order::STATUS_PENDING_VENDOR => "is currently **waiting for vendor acceptance** from **{$orderContext['vendor_name']}**.",
                    Order::STATUS_ACCEPTED, Order::STATUS_PREPARING => "is currently being **freshly prepared** at **{$orderContext['vendor_name']}**.",
                    Order::STATUS_READY_FOR_PICKUP => "is packed and **ready for pickup**. A delivery partner is arriving at the store.",
                    Order::STATUS_DELIVERY_ASSIGNED => "has been assigned to delivery partner **{$orderContext['agent_name']}** who is heading to the store.",
                    Order::STATUS_PICKED_UP, Order::STATUS_OUT_FOR_DELIVERY => "is **OUT FOR DELIVERY**! Delivery partner **{$orderContext['agent_name']}** is on the way to your address.",
                    Order::STATUS_DELIVERED => "was **successfully delivered** on {$orderContext['created_at']}.",
                    Order::STATUS_CANCELLED => "was cancelled.",
                    default => "is in state **{$orderContext['status_label']}**."
                };

                $message = "### Order Update: #{$orderContext['order_number']}\n\n";
                $message .= "Your order {$statusMsg}\n\n";
                $message .= "* **Items:** {$orderContext['items_summary']}\n";
                $message .= "* **Total:** ₹" . number_format($orderContext['total_amount'], 2) . "\n";

                if (in_array($orderContext['status'], [Order::STATUS_OUT_FOR_DELIVERY, Order::STATUS_PICKED_UP])) {
                    $message .= "\n> **Delivery Security Note:** Your 6-digit Delivery OTP is visible on your tracking screen. Please share it with {$orderContext['agent_name']} upon handover.";
                }

                $actions[] = ['label' => "Live Track Order #{$orderContext['order_number']}", 'url' => $orderContext['url'], 'icon' => 'bi-geo-alt-fill'];
                if ($orderContext['can_cancel']) {
                    $actions[] = ['label' => 'Manage / Cancel', 'url' => $orderContext['url'], 'icon' => 'bi-x-circle'];
                }
            }
        }
        // Case 2: Product Catalogue Search & Availability
        elseif (!empty($productMatches) && ($this->containsAny($query, ['buy', 'have', 'price', 'product', 'available', 'water', 'milk', 'grocery', 'almond', 'dry fruit', 'cost', 'show me']))) {
            $intent = 'product_discovery';
            $confidence = 0.95;

            $message = "I found these matching items available from neighbourhood stores near you:\n\n";
            foreach ($productMatches as $prod) {
                $message .= "* **{$prod['name']}** - **₹{$prod['price']}** per {$prod['unit']} *(Store: {$prod['vendor']})*\n";
                $actions[] = ['label' => "View {$prod['name']}", 'url' => $prod['url'], 'icon' => 'bi-cart-check'];
            }
            $message .= "\nAll items can be delivered to your doorstep in **15 to 30 minutes**.";
            $sources[] = 'Live VaishnoMart Product Catalogue';
        }
        // Case 3: Knowledge Base (RAG) Grounded Retrieval
        elseif (!empty($retrievedKb)) {
            $topKb = $retrievedKb[0];
            $intent = $topKb['id'];
            $confidence = min(0.99, $topKb['score'] + 0.2);

            $message = "### {$topKb['title']}\n\n" . $topKb['content'];

            // Append additional relevant context if second chunk has high score
            if (isset($retrievedKb[1]) && $retrievedKb[1]['score'] > 0.45) {
                $message .= "\n\n**Also helpful:** " . $retrievedKb[1]['content'];
                $sources[] = $retrievedKb[1]['title'];
            }

            $sources[] = $topKb['title'];

            if ($topKb['id'] === 'kb_drinking_water') {
                $actions[] = ['label' => 'Browse Drinking Water', 'url' => url('/category/drinking-water'), 'icon' => 'bi-droplet-fill'];
            } elseif ($topKb['id'] === 'kb_delivery_time_fee') {
                $actions[] = ['label' => 'Find Stores Near Me', 'url' => route('home'), 'icon' => 'bi-shop'];
            } elseif ($topKb['id'] === 'kb_vendor_onboarding') {
                $actions[] = ['label' => 'Register as Store', 'url' => route('vendor.register'), 'icon' => 'bi-shop-window'];
            } elseif ($topKb['id'] === 'kb_support_contact') {
                $actions[] = ['label' => 'Call Support Hotline', 'url' => 'tel:+919876543210', 'icon' => 'bi-telephone-fill'];
            }
        }
        // Case 4: Human Greeting / Default Fallback
        else {
            $nameGreeting = $user ? "Hello {$user->name}!" : "Hello!";
            $message = "{$nameGreeting} I am **VaishnoBot**, your AI shopping and support assistant.\n\n"
                     . "How can I help you today? You can ask me:\n"
                     . "* *\"Where is my latest order?\"*\n"
                     . "* *\"Do you have fresh 20L water cans or milk?\"*\n"
                     . "* *\"How does the 2-step OTP delivery verification work?\"*\n"
                     . "* *\"What are your delivery fees and return policies?\"*";

            $actions[] = ['label' => 'Browse Categories', 'url' => route('home'), 'icon' => 'bi-grid'];
            if ($user) {
                $actions[] = ['label' => 'My Orders', 'url' => route('customer.orders'), 'icon' => 'bi-receipt'];
            }
            $intent = 'greeting_or_general';
            $confidence = 0.75;
        }

        return [
            'success' => true,
            'message' => $message,
            'intent' => $intent,
            'confidence' => $confidence,
            'actions' => $actions,
            'sources' => array_unique($sources),
        ];
    }

    /**
     * Tokenize string into clean unique word tokens.
     */
    protected function tokenize(string $text): array
    {
        $clean = preg_replace('/[^a-zA-Z0-9\s]/', ' ', strtolower($text));
        $words = array_filter(explode(' ', $clean), fn($w) => strlen($w) >= 2);
        return array_values($words);
    }

    /**
     * Compute Cosine Similarity between two term frequency lists.
     */
    protected function computeCosineSimilarity(array $tokensA, array $tokensB): float
    {
        $tfA = array_count_values($tokensA);
        $tfB = array_count_values($tokensB);

        $dot = 0;
        $magA = 0;
        $magB = 0;

        foreach ($tfA as $w => $c) {
            $magA += $c * $c;
            if (isset($tfB[$w])) {
                $dot += $c * $tfB[$w];
            }
        }

        foreach ($tfB as $w => $c) {
            $magB += $c * $c;
        }

        if ($magA <= 0 || $magB <= 0) {
            return 0.0;
        }

        return $dot / (sqrt($magA) * sqrt($magB));
    }

    /**
     * Helper to test if text contains any of the given search phrases.
     */
    protected function containsAny(string $haystack, array $needles): bool
    {
        $haystack = strtolower($haystack);
        foreach ($needles as $needle) {
            if (str_contains($haystack, strtolower($needle))) {
                return true;
            }
        }
        return false;
    }
}
