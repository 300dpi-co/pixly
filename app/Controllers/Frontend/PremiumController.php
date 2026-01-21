<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;

/**
 * Premium Controller
 *
 * Handles premium subscription pages and logic.
 */
class PremiumController extends Controller
{
    /**
     * Check if premium feature is enabled
     */
    private function isPremiumEnabled(): bool
    {
        return setting('premium_enabled', '1') === '1';
    }

    /**
     * Display premium subscription page
     */
    public function index(): Response
    {
        // Check if premium is enabled
        if (!$this->isPremiumEnabled()) {
            return $this->redirect('/');
        }

        $yearlyPrice = setting('subscription_yearly_price', '99');
        $monthlyPrice = setting('subscription_monthly_price', '15');
        $currency = setting('subscription_currency', 'INR');
        $features = setting('premium_features', []);

        // Check if user is already premium
        $isPremium = false;
        $subscription = null;

        if ($this->isAuthenticated()) {
            $db = $this->db();
            $user = $db->fetch(
                "SELECT is_premium, premium_until FROM users WHERE id = :id",
                ['id' => $_SESSION['user_id']]
            );

            if ($user && $user['is_premium'] && strtotime($user['premium_until']) > time()) {
                $isPremium = true;
                $subscription = $db->fetch(
                    "SELECT * FROM subscriptions WHERE user_id = :user_id AND status = 'active' ORDER BY created_at DESC LIMIT 1",
                    ['user_id' => $_SESSION['user_id']]
                );
            }
        }

        // Select appropriate view template based on layout preset
        $layoutPreset = setting('layout_preset', 'clean-minimal');
        $template = match ($layoutPreset) {
            'pexels-stock' => 'frontend/premium-pexels',
            default => 'frontend/premium',
        };

        return $this->view($template, [
            'title' => 'Go Premium - Ad-Free Experience',
            'meta_description' => 'Subscribe to premium for an ad-free experience, unlimited downloads, and more.',
            'yearlyPrice' => $yearlyPrice,
            'monthlyPrice' => $monthlyPrice,
            'currency' => $currency,
            'features' => $features,
            'isPremium' => $isPremium,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Handle subscription checkout
     */
    public function checkout(): Response
    {
        // Check if premium is enabled
        if (!$this->isPremiumEnabled()) {
            return $this->redirect('/');
        }

        if (!$this->isAuthenticated()) {
            $_SESSION['redirect_after_login'] = '/premium/checkout';
            return $this->redirect('/login?message=Please login to subscribe');
        }

        $plan = $this->request->input('plan', 'yearly');
        $price = $plan === 'monthly'
            ? setting('subscription_monthly_price', '15')
            : setting('subscription_yearly_price', '99');

        return $this->view('frontend/premium-checkout', [
            'title' => 'Complete Your Subscription',
            'plan' => $plan,
            'price' => $price,
            'currency' => setting('subscription_currency', 'INR'),
        ]);
    }

    /**
     * Process payment (simplified - integrate with payment gateway)
     */
    public function processPayment(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->json(['error' => 'Authentication required'], 401);
        }

        $db = $this->db();
        $userId = $_SESSION['user_id'];
        $plan = $this->request->input('plan', 'yearly');

        $price = $plan === 'monthly'
            ? (float) setting('subscription_monthly_price', '15')
            : (float) setting('subscription_yearly_price', '99');

        $duration = $plan === 'monthly' ? '+1 month' : '+1 year';
        $expiresAt = date('Y-m-d H:i:s', strtotime($duration));

        try {
            $db->beginTransaction();

            // Create subscription record
            $subscriptionId = $db->insert('subscriptions', [
                'user_id' => $userId,
                'plan_type' => $plan === 'monthly' ? 'monthly' : 'yearly',
                'amount' => $price,
                'currency' => setting('subscription_currency', 'INR'),
                'status' => 'active',
                'payment_method' => 'manual', // Replace with actual gateway
                'starts_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
            ]);

            // Update user premium status
            $db->execute(
                "UPDATE users SET is_premium = 1, premium_until = :expires WHERE id = :id",
                ['expires' => $expiresAt, 'id' => $userId]
            );

            // Log transaction
            $db->insert('payment_transactions', [
                'user_id' => $userId,
                'subscription_id' => $subscriptionId,
                'transaction_id' => 'TXN_' . strtoupper(bin2hex(random_bytes(8))),
                'payment_gateway' => 'manual',
                'amount' => $price,
                'currency' => setting('subscription_currency', 'INR'),
                'status' => 'completed',
            ]);

            $db->commit();

            return $this->json([
                'success' => true,
                'message' => 'Subscription activated successfully!',
                'redirect' => '/premium?success=1',
            ]);

        } catch (\Exception $e) {
            $db->rollBack();
            return $this->json(['error' => 'Payment processing failed'], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $db = $this->db();
        $userId = $_SESSION['user_id'];

        // Cancel active subscription
        $db->execute(
            "UPDATE subscriptions SET status = 'cancelled', cancelled_at = NOW() WHERE user_id = :id AND status = 'active'",
            ['id' => $userId]
        );

        // Note: User keeps premium until expiry date
        $this->flash('info', 'Your subscription has been cancelled. You will retain premium access until the end of your billing period.');

        return $this->redirect('/premium');
    }
}
