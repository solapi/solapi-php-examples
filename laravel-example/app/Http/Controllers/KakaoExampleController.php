<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nurigo\Solapi\Models\Kakao\KakaoOption;
use Nurigo\Solapi\Models\Message;
use Nurigo\Solapi\Services\SolapiMessageService;

class KakaoExampleController extends Controller
{
    public SolapiMessageService $messageService;

    public function __construct()
    {
        $this->messageService = new SolapiMessageService(env("SOLAPI_API_KEY"), env("SOLAPI_API_SECRET_KEY"));
    }

    /**
     * 알림톡 발송
     * @param Request $request
     * @return JsonResponse
     */
    public function send_ata(Request $request): JsonResponse
    {
        // 알림톡을 발송할 경우 발신번호는 필수가 아니지만, 문자 대체발송을 희망하실 경우 반드시 발신번호(from)를 넣어주셔야 합니다.
        $request->validate([
            'pfId' => 'required',
            'templateId' => 'required',
            'to' => 'required'
        ]);

        try {
            $pfId = $request->get('pfId');
            $templateId = $request->get('templateId');

            $kakaoOption = new KakaoOption();

            $kakaoOption->setPfId($pfId)
                ->setTemplateId($templateId);

            if ($request->has('variables')) {
                // variables[0][key], variables[0][value] 형식으로 요청, key는 #{변수명} 등의 값을 넣어주어야 합니다.
                $variablesRequest = $request->collect('variables');
                $variables = collect();
                $variablesRequest->each(function ($variables) use ($variables) {
                    if (!empty($variables["key"]) && !empty($variables["value"])) {
                        $key = $variables["key"];
                        $value = $variables["value"];
                        $variables->push([
                            $key => $value
                        ]);
                    }
                });
                if ($variables->count() > 0) {
                    $kakaoOption->setVariables($variables);
                }
            }

            $message = new Message();
            if ($request->has('from')) {
                $message->setFrom($request->get('from'));
            }
            $message->setTo($request->get('to'))
                ->setKakaoOptions($kakaoOption);

            $result = $this->messageService->send($message);
            return response()->json($result);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }
}
