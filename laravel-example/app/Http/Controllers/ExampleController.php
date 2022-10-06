<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nurigo\Solapi\Models\Message;
use Nurigo\Solapi\Services\SolapiMessageService;

class ExampleController extends Controller
{
    public SolapiMessageService $messageService;

    public function __construct()
    {
        $this->messageService = new SolapiMessageService(env("SOLAPI_API_KEY"), env("SOLAPI_API_SECRET_KEY"));
    }

    public function get_messages(Request $request): JsonResponse
    {
        // 필요한 경우 getMessages 메소드 파라미터 안에 조건을 넣어 검색, GetMessagesRequest 클래스 선언 필요
        $messages = $this->messageService->getMessages();
        return response()->json($messages);
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required'
        ]);
        $from = $request->get("from");
        $to = $request->get("to");
        // 일반 문자가 아닌 경우 제외
        $text = $request->get("text");

        try {
            $message = new Message();
            $message->setFrom($from)
                ->setTo($to)
                ->setText($text);

            if ($request->hasFile('image')) {
                // MMS 발송 시 추가, MMS는 반드시 200kb 이내의 jpg 파일을 업로드해야 합니다
                $image = $request->file('image');
                $imageId = $this->messageService->uploadFile($image->getRealPath());
                $message->setImageId($imageId);
            }

            $result = $this->messageService->send($message);
            return response()->json($result);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    public function get_balance(): JsonResponse
    {
        $balance = $this->messageService->getBalance();
        return response()->json($balance);
    }
}
