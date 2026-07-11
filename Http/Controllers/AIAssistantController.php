<?php

namespace Modules\TitanZero\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\TitanZero\Entities\AssistantTemplate;
use Modules\TitanZero\Services\TitanZeroService;
use Modules\TitanZero\Events\TitanZeroContentGenerated;

class TitanZeroController extends Controller
{
    /**
     * Titan Zero uses Titan Core for all AI operations.
     * The selected model is controlled at Super Admin level only.
     */
    public function __construct(protected TitanZeroService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('titanzero::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($template_module,$module)
    {
        $templateName = AssistantTemplate::where('template_module', $template_module)->where('module', $module)->get();

        return view('titanzero::ai.generate',compact('templateName'));
    }
    public function vcard_create_business($template_module,$module,$id)
    {
        $business_id=$id;
        $templateName = AssistantTemplate::where('template_module',$template_module)->where('module',$module)->get();
        return view('titanzero::ai.generate_vcard_business',compact('templateName','business_id'));
    }
    public function vcard_create_service($template_module,$module,$id)
    {
        $serviceid=$id;
        $templateName = AssistantTemplate::where('template_module',$template_module)->where('module',$module)->get();
        return view('titanzero::ai.generate_vcard_service',compact('templateName','serviceid'));
    }

    public function vcard_create_testimonial($template_module,$module,$id)
    {
        $testimonial_id=$id;
        $templateName = AssistantTemplate::where('template_module',$template_module)->where('module',$module)->get();
        return view('titanzero::ai.generate_vcard_testimonial',compact('templateName','testimonial_id'));
    }

    public function cmms_create($template_module,$module)
    {
        $templateName = AssistantTemplate::where('template_module',$template_module)->where('module',$module)->get();

        return view('titanzero::ai.cmms_generate',compact('templateName'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('titanzero::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('titanzero::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
    public function GetKeywords(Request $request, $id)
    {
     
        $template = AssistantTemplate::find($id);
        $field_data = json_decode($template->field_json);

        $html = "";
        foreach ($field_data->field as  $value) {
            $html .= '<div class="form-group col-md-12">
                    <label class="form-label ">' . $value->label . '</label>';
            if ($value->field_type == "text_box") {

                $html .= '<input type="text" class="form-control" name="' . $value->field_name . '" value="" placeholder="' . $value->placeholder . '" required">';
            }
            if ($value->field_type == "textarea") {
                $html .= '<textarea type="text" rows=3 class="form-control " id="description" name="' . $value->field_name . '" placeholder="' . $value->placeholder . '" required></textarea>';
            }
            $html .= '</div>';
        }
        return response()->json(
            [
                'success' => true,
                'template' => $html,
                'tone'=>$template->is_tone
            ]
        );
    }
    public function AiGenerate(Request $request)
    {
        $data = [];

        $template = null;
        if ($request->has('template_id')) {
            $template = AssistantTemplate::find($request->get('template_id'));
        }

        $prompt = $request->get('prompt');

        $lang_text = "Provide response in " . $request->language . " language.\n\n ";
        $ai_token = (int) $request->result_length;

        $max_results   = (int) $request->num_of_result;
        $ai_creativity = (float) $request->ai_creativity;

        $result = $this->service->generate(
            $prompt,
            $request->language,
            $ai_token,
            $ai_creativity,
            $max_results,
            optional(Auth::user())->id,
            optional(Auth::user())->company_id ?? null,
            $template?->id
        );

        if ($result['success'] ?? false) {
            return trim($result['text'] ?? '');
        }

        $data['status'] = 'error';
        $data['message'] = $result['message'] ?? __('Text was not generated due to Invalid API Key');
        return $data;
    }

    /**
     * Show Titan Zero help & examples page.
     */
    public function help()
    {
        return view('titanzero::ai.help');
    }

    /**
     * Receive generated content and forward to document system.
     */
    public function sendToDocs(Request $request)
    {
        $content = (string) $request->get('content', '');

        if (! $content) {
            return response()->json([
                'status'  => 'error',
                'message' => __('No content provided.'),
            ]);
        }

        event(new TitanZeroContentGenerated(
            optional(Auth::user())->id,
            optional(Auth::user())->company_id ?? null,
            null,
            $content
        ));

        return response()->json([
            'status'  => 'success',
            'message' => __('Content sent to Docs.'),
        ]);
    }

    /**
     * Receive generated content and forward to task system.
     */
    public function sendToTasks(Request $request)
    {
        $content = (string) $request->get('content', '');

        if (! $content) {
            return response()->json([
                'status'  => 'error',
                'message' => __('No content provided.'),
            ]);
        }

        event(new TitanZeroContentGenerated(
            optional(Auth::user())->id,
            optional(Auth::user())->company_id ?? null,
            null,
            $content
        ));

        return response()->json([
            'status'  => 'success',
            'message' => __('Content sent to Tasks.'),
        ]);
    }

    /**
     * Global Titan Zero endpoint: available on any page where the widget is included.
     */
    public function globalAssist(Request $request)
    {
        $question = (string) $request->get('question', '');
        $page     = (string) $request->get('page', '');
        $url      = (string) $request->get('url', '');

        if ($question === '') {
            return response()->json([
                'status'  => 'error',
                'message' => __('Please enter a question for Titan Zero.'),
            ]);
        }

        $user = Auth::user();

        $contextPrompt = "You are Titan Zero, a global assistant inside Worksuite. "
            . "Use the following context about the current page and then answer the user question clearly and concisely.\n\n"
            . "Page title: " . $page . "\n"
            . "URL: " . $url . "\n\n"
            . "User question: " . $question;

        $result = $this->service->generate(
            $contextPrompt,
            app()->getLocale() ?? 'en',
            512,
            0.6,
            1,
            optional($user)->id,
            optional($user)->company_id ?? null,
            null
        );

        if (!($result['success'] ?? false)) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'] ?? __('Titan Zero could not generate a response.'),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'answer' => $result['text'] ?? '',
        ]);
    }

}
