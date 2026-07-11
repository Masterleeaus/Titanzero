<?php

namespace Modules\TitanZero\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Services\Cleaning\CleaningIntelligenceService;

/**
 * CleaningSuggestionsController
 *
 * Exposes TitanZero cleaning-business AI features as HTTP endpoints.
 * All routes require authentication and the `use_titan_zero` permission.
 */
class CleaningSuggestionsController extends Controller
{
    public function __construct(protected CleaningIntelligenceService $intelligence) {}

    /**
     * AI Suggestion dashboard — shows all available cleaning AI features.
     */
    public function dashboard(Request $request)
    {
        return view('titanzero::pages.suggestions', [
            'features' => $this->featuresList(),
        ]);
    }

    /**
     * Test an AI suggestion for a given context (generic test endpoint).
     */
    public function test(Request $request)
    {
        $request->validate([
            'feature' => ['required', 'string'],
            'context' => ['nullable', 'array'],
        ]);

        $feature = $request->input('feature');
        $context = (array) $request->input('context', []);
        $tenantId = auth()->user()->company_id ?? null;

        $result = match ($feature) {
            'booking_slots'        => $this->intelligence->suggestBookingSlots($context, $tenantId),
            'cleaner_match'        => $this->intelligence->suggestCleanerMatch($context, $tenantId),
            'auto_fill_instructions' => $this->intelligence->autoFillBookingInstructions($context, $tenantId),
            'price_suggestion'     => $this->intelligence->suggestPrice($context, $tenantId),
            'rebooking_suggestion' => $this->intelligence->suggestRebooking($context, $tenantId),
            'sms_draft'            => $this->intelligence->draftSms($context, $tenantId),
            'complaint_triage'     => $this->intelligence->triageComplaint($context, $tenantId),
            'anomaly_detection'    => $this->intelligence->detectAnomalies($context, $tenantId),
            'automation_rules'     => $this->intelligence->suggestAutomationRules($context, $tenantId),
            default                => ['status' => 'error', 'message' => 'Unknown feature: ' . $feature],
        };

        return response()->json($result);
    }

    /**
     * Suggest best booking slots for a zone and cleaner.
     */
    public function bookingSlots(Request $request)
    {
        $request->validate([
            'zone_id'          => ['nullable', 'integer'],
            'cleaner_id'       => ['nullable', 'integer'],
            'requested_date'   => ['nullable', 'date'],
            'duration_hours'   => ['nullable', 'numeric', 'min:0.5', 'max:24'],
        ]);

        $result = $this->intelligence->suggestBookingSlots(
            $request->only(['zone_id', 'cleaner_id', 'requested_date', 'duration_hours']),
            auth()->user()->company_id ?? null
        );

        return response()->json($result);
    }

    /**
     * Suggest the best cleaner match for a booking.
     */
    public function cleanerMatch(Request $request)
    {
        $request->validate([
            'booking_id'    => ['nullable', 'string'],
            'zone_id'       => ['nullable', 'integer'],
            'service_type'  => ['nullable', 'string'],
            'property_size' => ['nullable', 'string'],
        ]);

        $result = $this->intelligence->suggestCleanerMatch(
            $request->only(['booking_id', 'zone_id', 'service_type', 'property_size']),
            auth()->user()->company_id ?? null
        );

        return response()->json($result);
    }

    /**
     * Auto-fill booking special instructions from property history.
     */
    public function autoFillInstructions(Request $request)
    {
        $request->validate([
            'client_id'      => ['nullable', 'integer'],
            'property_id'    => ['nullable', 'integer'],
            'address'        => ['nullable', 'string'],
            'previous_notes' => ['nullable', 'array'],
        ]);

        $result = $this->intelligence->autoFillBookingInstructions(
            $request->only(['client_id', 'property_id', 'address', 'previous_notes']),
            auth()->user()->company_id ?? null
        );

        return response()->json($result);
    }

    /**
     * Suggest a price range for a job.
     */
    public function priceSuggestion(Request $request)
    {
        $request->validate([
            'property_size_m2' => ['nullable', 'numeric'],
            'service_type'     => ['nullable', 'string'],
            'bedroom_count'    => ['nullable', 'integer'],
            'suburb'           => ['nullable', 'string'],
        ]);

        $result = $this->intelligence->suggestPrice(
            $request->only(['property_size_m2', 'service_type', 'bedroom_count', 'suburb']),
            auth()->user()->company_id ?? null
        );

        return response()->json($result);
    }

    /**
     * Suggest rebooking for a recurring client.
     */
    public function rebookingSuggestion(Request $request)
    {
        $request->validate([
            'client_id'             => ['nullable', 'integer'],
            'last_booking_date'     => ['nullable', 'date'],
            'average_frequency_days' => ['nullable', 'integer'],
        ]);

        $result = $this->intelligence->suggestRebooking(
            $request->only(['client_id', 'last_booking_date', 'average_frequency_days']),
            auth()->user()->company_id ?? null
        );

        return response()->json($result);
    }

    /**
     * Draft an SMS message (bridges to Sms module).
     */
    public function smsDraft(Request $request)
    {
        $request->validate([
            'client_name'  => ['nullable', 'string'],
            'booking_date' => ['nullable', 'string'],
            'cleaner_name' => ['nullable', 'string'],
            'purpose'      => ['nullable', 'string'],
        ]);

        $result = $this->intelligence->draftSms(
            $request->only(['client_name', 'booking_date', 'cleaner_name', 'purpose']),
            auth()->user()->company_id ?? null
        );

        return response()->json($result);
    }

    /**
     * Triage a complaint and suggest a resolution type.
     */
    public function complaintTriage(Request $request)
    {
        $request->validate([
            'complaint_text'     => ['required', 'string', 'max:2000'],
            'service_type'       => ['nullable', 'string'],
            'complaint_category' => ['nullable', 'string'],
        ]);

        $result = $this->intelligence->triageComplaint(
            $request->only(['complaint_text', 'service_type', 'complaint_category']),
            auth()->user()->company_id ?? null
        );

        return response()->json($result);
    }

    /**
     * Detect anomalies in a booking.
     */
    public function anomalyDetect(Request $request)
    {
        $request->validate([
            'booking_id'   => ['nullable', 'string'],
            'booking_data' => ['nullable', 'array'],
        ]);

        $result = $this->intelligence->detectAnomalies(
            $request->only(['booking_id', 'booking_data']),
            auth()->user()->company_id ?? null
        );

        return response()->json($result);
    }

    /**
     * Suggest no-code automation rules.
     */
    public function automationRules(Request $request)
    {
        $request->validate([
            'trigger_event'     => ['nullable', 'string'],
            'observed_patterns' => ['nullable', 'array'],
        ]);

        $result = $this->intelligence->suggestAutomationRules(
            $request->only(['trigger_event', 'observed_patterns']),
            auth()->user()->company_id ?? null
        );

        return response()->json($result);
    }

    private function featuresList(): array
    {
        return [
            ['key' => 'booking_slots',         'label' => 'Smart Booking Slot Suggestions',     'description' => 'Based on zone and cleaner availability, suggest the best slots.'],
            ['key' => 'cleaner_match',          'label' => 'Intelligent Cleaner Matching',        'description' => 'Match cleaner to job by proximity, skills, rating, and history.'],
            ['key' => 'auto_fill_instructions', 'label' => 'Auto-fill Special Instructions',      'description' => 'Pre-fill booking instructions from property history.'],
            ['key' => 'price_suggestion',       'label' => 'Price Suggestion',                    'description' => 'Based on property size, similar jobs cost $X\u2013$Y.'],
            ['key' => 'rebooking_suggestion',   'label' => 'Rebooking Suggestion',                'description' => 'Suggest rebooking for clients who book on a pattern.'],
            ['key' => 'sms_draft',              'label' => 'SMS Drafting Assistance',             'description' => 'Draft professional SMS messages via the Sms module bridge.'],
            ['key' => 'complaint_triage',       'label' => 'Complaint Triage',                    'description' => 'AI suggests resolution type based on complaint details.'],
            ['key' => 'anomaly_detection',      'label' => 'Anomaly Detection',                   'description' => 'Flag bookings with unusual patterns.'],
            ['key' => 'automation_rules',       'label' => 'Automation Rule Suggestions',         'description' => 'No-code automation rule suggestions based on workflow patterns.'],
        ];
    }
}
