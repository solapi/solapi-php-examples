<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
            'to' => 'required',
            'scheduledDate' => 'date'
        ]);

        try {
            $pfId = $request->get('pfId');
            $templateId = $request->get('templateId');
            $scheduledDate = $request->get('scheduledDate');

            $kakaoOption = new KakaoOption();

            $kakaoOption->setPfId($pfId)
                ->setTemplateId($templateId);

            if ($request->has('variables')) {
                // variables[0][key], variables[0][value] 형식으로 요청, key는 #{변수명} 등의 값을 넣어주어야 합니다.
                $variablesRequest = $request->collect('variables');
                $variables = collect();
                $variablesRequest->each(function ($variable) use ($variables) {
                    if (!empty($variable["key"]) && !empty($variable["value"])) {
                        $key = $variable["key"];
                        $value = $variable["value"];
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

            if ($request->has('scheduledDate')) {
                $scheduledDate = Carbon::parse($scheduledDate)->toDateTime();

                // 혹은 메시지 객체의 배열을 넣어 여러 건을 발송할 수도 있습니다!
                $result = $this->messageService->send($message, $scheduledDate);
            } else {
                // 혹은 메시지 객체의 배열을 넣어 여러 건을 발송할 수도 있습니다!
                $result = $this->messageService->send($message);
            }
            return response()->json($result);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    /**
     * 친구톡/이미지 친구톡 발송
     * @param Request $request
     * @return JsonResponse
     */
    public function send_cta(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required',
            'text' => 'required',
            'pfId' => 'required',
            'scheduledDate' => 'date',
            'image' => 'image'
        ]);

        try {
            $to = $request->get('to');
            $text = $request->get('text');
            $pfId = $request->get('pfId');
            $scheduledDate = $request->get('scheduledDate');

            $kakaoOption = new KakaoOption();
            $kakaoOption->setPfId($pfId)
                ->setVariables(null);

            $message = new Message();
            $message->setTo($to)
                ->setText($text)
                ->setKakaoOptions($kakaoOption);

            if ($request->has('buttons')) {
                // buttons[0][name], buttons[0][buttonType] 등의 형식으로 요청, key는 #{변수명} 등의 값을 넣어주어야 합니다.
                $buttonsRequest = $request->collect('buttons');
                $buttons = collect();

                // 카카오 버튼 타입에 대한 설명은 https://docs.solapi.com/references/kakao/button-type 를 참고하세요!
                $buttonsRequest->each(function ($button) use ($buttons) {
                    if (!empty($button['name']) && !empty($button['buttonType'])) {
                        $buttonType = $button['buttonType'];
                        $buttonName = $button['name'];

                        switch ($buttonType) {
                            case "WL":
                                if (empty($buttonsRequest['linkMo'])) {
                                    break;
                                }
                                $buttons->push([
                                    "buttonName" => $buttonName,
                                    "buttonType" => $buttonType,
                                    "linkMo" => $buttonsRequest['linkMo'],
                                    "linkPc" => $buttonsRequest['linkPc'] ?? null
                                ]);
                                break;
                            case "AL":
                                if (empty($buttonsRequest['linkAnd']) || empty($buttonsRequest['linkIos'])) {
                                    break;
                                }
                                $buttons->push([
                                    "buttonName" => $buttonName,
                                    "buttonType" => $buttonType,
                                    "linkAnd" => $buttonsRequest['linkAnd'],
                                    "linkIos" => $buttonsRequest['linkIos']
                                ]);
                                break;
                            // 상담톡 전환(BC) 버튼의 경우 채널이 상담톡 서비스를 이용하지 않으면 잘못된 파라미터로 발송에 실패합니다.
                            case "BC":
                            case "BK":
                            case "MD":
                            case "BT":
                                $buttons->push([
                                    "buttonName" => $buttonName,
                                    "buttonType" => $buttonType
                                ]);
                                break;
                            default:
                                break;
                        }
                    }
                });

                if ($buttons->count() > 0) {
                    $kakaoOption->setButtons($buttons->toArray());
                }
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                if ($request->hasFile('image')) {
                    if ($request->has('imageLink')) {
                        $image = $request->file('image');
                        $imageLink = $request->get('imageLink');

                        $imageId = $this->messageService->uploadFile($image->getRealPath(), "KAKAO", $image->getClientOriginalName(), $imageLink);
                        $kakaoOption->setImageId($imageId);
                    }
                }
            }

            if ($request->has('scheduledDate')) {
                $scheduledDate = Carbon::parse($scheduledDate)->toDateTime();

                // 혹은 메시지 객체의 배열을 넣어 여러 건을 발송할 수도 있습니다!
                $result = $this->messageService->send($message, $scheduledDate);
            } else {
                // 혹은 메시지 객체의 배열을 넣어 여러 건을 발송할 수도 있습니다!
                $result = $this->messageService->send($message);
            }
            return response()->json($result);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }
}
