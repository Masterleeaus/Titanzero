<?php

namespace Modules\TitanZero\Services\Cleaning;

use Modules\TitanZero\Services\ZeroGateway;

/**
 * CleaningIntelligenceService
 *
 * Provides cleaning-business AI features via TitanZero / TitanCore gateway.
 * All methods are safe-by-default: if the AI provider is unavailable, they
 * return structured fallback responses rather than throwing exceptions.
 */
class CleaningIntelligenceService
{
    public function __construct(protected ZeroGateway $gateway) {}

    /**
     * Suggest the best available booking slots for a zone + cleaner.
     *
     * @param  array{zone_id?:int, cleaner_id?:int, requested_date?:string, duration_hours?:float}  $context
     */
    public function suggestBookingSlots(array $context, ?int $tenantId = null): array
    {
        return $this->gateway->runAgent([
            'agent_slug' => 'booking_slot_suggester',
            'kb_collection_key' => 'kb_booking_scheduling',
            'input' => array_merge([
                'task' => 'suggest_booking_slots',
                'description' => 'Based on zone and cleaner availability, suggest the best booking slots.',
            ], $context),
        ], $tenantId);
    }

    /**
     * Suggest the best cleaner for a job (proximity, skills, rating, history).
     *
     * @param  array{booking_id?:string, zone_id?:int, service_type?:string, property_size?:string}  $context
     */
    public function suggestCleanerMatch(array $context, ?int $tenantId = null): array
    {
        return $this->gateway->runAgent([
            'agent_slug' => 'cleaner_matcher',
            'kb_collection_key' => 'kb_provider_skills',
            'input' => array_merge([
                'task' => 'match_cleaner_to_job',
                'description' => 'Intelligently match a cleaner to this job based on proximity, skills, rating, and history.',
            ], $context),
        ], $tenantId);
    }

    /**
     * Auto-fill booking special instructions from property history.
     *
     * @param  array{client_id?:int, property_id?:int, address?:string, previous_notes?:string[]}  $context
     */
    public function autoFillBookingInstructions(array $context, ?int $tenantId = null): array
    {
        return $this->gateway->runAgent([
            'agent_slug' => 'instruction_autofill',
            'kb_collection_key' => 'kb_general_cleaning',
            'input' => array_merge([
                'task' => 'autofill_special_instructions',
                'description' => 'Suggest special instructions for this booking based on property history.',
            ], $context),
        ], $tenantId);
    }

    /**
     * Suggest a price range based on property size and similar past jobs.
     *
     * @param  array{property_size_m2?:float, service_type?:string, bedroom_count?:int, suburb?:string}  $context
     */
    public function suggestPrice(array $context, ?int $tenantId = null): array
    {
        return $this->gateway->runAgent([
            'agent_slug' => 'price_suggester',
            'kb_collection_key' => 'kb_pricing_history',
            'input' => array_merge([
                'task' => 'suggest_price_range',
                'description' => 'Based on property size and similar jobs, suggest a price range.',
            ], $context),
        ], $tenantId);
    }

    /**
     * Suggest a rebooking for a client who books on a recurring pattern.
     *
     * @param  array{client_id?:int, last_booking_date?:string, average_frequency_days?:int}  $context
     */
    public function suggestRebooking(array $context, ?int $tenantId = null): array
    {
        return $this->gateway->runAgent([
            'agent_slug' => 'rebooking_suggester',
            'kb_collection_key' => 'kb_booking_scheduling',
            'input' => array_merge([
                'task' => 'suggest_rebooking',
                'description' => 'This client usually books on a recurring pattern. Suggest an optimal rebooking date and message.',
            ], $context),
        ], $tenantId);
    }

    /**
     * Draft an SMS message for a client (bridges to Sms module).
     *
     * @param  array{client_name?:string, booking_date?:string, cleaner_name?:string, purpose?:string}  $context
     */
    public function draftSms(array $context, ?int $tenantId = null): array
    {
        return $this->gateway->runAgent([
            'agent_slug' => 'sms_drafter',
            'kb_collection_key' => 'kb_general_cleaning',
            'input' => array_merge([
                'task' => 'draft_sms_message',
                'description' => 'Draft a professional SMS message for the client.',
            ], $context),
        ], $tenantId);
    }

    /**
     * Triage a complaint and suggest the resolution type.
     *
     * @param  array{complaint_text?:string, service_type?:string, complaint_category?:string}  $context
     */
    public function triageComplaint(array $context, ?int $tenantId = null): array
    {
        return $this->gateway->runAgent([
            'agent_slug' => 'complaint_triager',
            'kb_collection_key' => 'kb_general_cleaning',
            'input' => array_merge([
                'task' => 'triage_complaint',
                'description' => 'Based on the complaint details, suggest the most appropriate resolution type.',
            ], $context),
        ], $tenantId);
    }

    /**
     * Detect anomalies in booking patterns (unusual times, locations, durations).
     *
     * @param  array{booking_id?:string, booking_data?:array}  $context
     */
    public function detectAnomalies(array $context, ?int $tenantId = null): array
    {
        return $this->gateway->runAgent([
            'agent_slug' => 'anomaly_detector',
            'kb_collection_key' => 'kb_booking_scheduling',
            'input' => array_merge([
                'task' => 'detect_booking_anomalies',
                'description' => 'Flag any unusual patterns in this booking data.',
            ], $context),
        ], $tenantId);
    }

    /**
     * Suggest no-code automation rules based on current workflow patterns.
     *
     * @param  array{trigger_event?:string, observed_patterns?:string[]}  $context
     */
    public function suggestAutomationRules(array $context, ?int $tenantId = null): array
    {
        return $this->gateway->runAgent([
            'agent_slug' => 'automation_rule_suggester',
            'kb_collection_key' => 'kb_general_cleaning',
            'input' => array_merge([
                'task' => 'suggest_automation_rules',
                'description' => 'Based on observed workflow patterns, suggest useful no-code automation rules.',
            ], $context),
        ], $tenantId);
    }
}
