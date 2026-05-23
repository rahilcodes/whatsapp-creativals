<?php

namespace App\Services;

class LeadCaptureService
{
    /**
     * Extract email address from a message string.
     */
    public function extractEmail(string $message): ?string
    {
        $pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/';
        if (preg_match($pattern, $message, $matches)) {
            return strtolower(trim($matches[0]));
        }
        return null;
    }

    /**
     * Extract phone number from a message string.
     */
    public function extractPhone(string $message): ?string
    {
        $patterns = [
            // +91 7997001700 or +917997001700 or 7997001700
            '/(?:\+?\d{1,3}[- ]?)?\d{10}/',
            // 98765 43210 or 98765-43210
            '/\d{5}[- ]\d{5}/',
            // (123) 456-7890 or 123-456-7890
            '/\(?\d{3}\)?[- ]?\d{3}[- ]?\d{4}/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $cleaned = preg_replace('/[^0-9+]/', '', $matches[0]);
                if (strlen($cleaned) >= 10) {
                    return $cleaned;
                }
            }
        }
        return null;
    }

    /**
     * Update the customer's profile progression lifecycle stage.
     */
    public function determineStage(string $currentStage, string $intent, int $score): string
    {
        $stages = [
            'new' => 1,
            'exploring' => 2,
            'engaged' => 3,
            'interested' => 4,
            'ready_to_buy' => 5,
            'objection' => 6,
            'complaint' => 7,
            'human_required' => 8,
        ];

        $currentWeight = $stages[$currentStage] ?? 1;

        // If intent indicates human required or a complaint, override immediately
        if ($intent === 'complaint') {
            return 'complaint';
        }
        if ($intent === 'human_required') {
            return 'human_required';
        }

        // Objection takes high priority
        if ($intent === 'objection') {
            return 'objection';
        }

        // Determine new stage based on intent and lead score progression
        $newStage = $currentStage;

        if ($score >= 75) {
            $newStage = 'ready_to_buy';
        } elseif ($score >= 50) {
            $newStage = 'interested';
        } elseif ($score >= 20) {
            $newStage = 'engaged';
        } elseif ($score >= 5) {
            $newStage = 'exploring';
        }

        $newWeight = $stages[$newStage] ?? 1;

        // Allow progressive advancement only (do not demote stage unless it is complaint or objection overrides)
        if ($newWeight > $currentWeight) {
            return $newStage;
        }

        return $currentStage;
    }
}
