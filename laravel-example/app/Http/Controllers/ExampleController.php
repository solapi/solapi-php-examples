<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nurigo\Solapi\Models\Message;
use Nurigo\Solapi\Models\Request\GetGroupMessagesRequest;
use Nurigo\Solapi\Models\Request\GetGroupsRequest;
use Nurigo\Solapi\Models\Request\GetMessagesRequest;
use Nurigo\Solapi\Models\Request\GetStatisticsRequest;
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
        $getMessagesRequest = new GetMessagesRequest();

        // 페이지네이션을 위한 시작 키 설정, 메시지 조회 시 nextKey로 무한 페이지네이션 가능
        $request->whenHas('startKey', function (string $startKey) use ($getMessagesRequest) {
            $getMessagesRequest->setStartKey($startKey);
        });

        // 데이터 조회 건 수 지정, 최대 500건 까지 가능
        $request->whenHas('limit', function (int $limit) use ($getMessagesRequest) {
            $getMessagesRequest->setLimit($limit);
        });

        // 메시지 ID로 조회
        $request->whenHas('messageId', function (string $messageId) use ($getMessagesRequest) {
            $getMessagesRequest->setMessageId($messageId);
        });

        // 메시지 ID 목록(배열)로 조회
        $request->whenHas('messageIds', function (array $messageIds) use ($getMessagesRequest) {
            $getMessagesRequest->setMessageIds($messageIds);
        });

        // 그룹 ID로 조회
        $request->whenHas('groupId', function (string $groupId) use ($getMessagesRequest) {
            $getMessagesRequest->setGroupId($groupId);
        });

        // 수신번호로 메시지 조회
        $request->whenHas('to', function (string $to) use ($getMessagesRequest) {
            $getMessagesRequest->setTo($to);
        });

        // 발신번호로 메시지 조회
        $request->whenHas('from', function (string $from) use ($getMessagesRequest) {
            $getMessagesRequest->setFrom($from);
        });

        // 메시지 유형으로 조회, SMS, LMS, MMS, ATA, CTA, CTI 등으로 조회 가능
        $request->whenHas('type', function (string $type) use ($getMessagesRequest) {
            $getMessagesRequest->setType($type);
        });

        // 상태 코드로 조회
        // https://docs.solapi.com/documents/references/message-status-codes 참고
        $request->whenHas('statusCode', function (string $statusCode) use ($getMessagesRequest) {
            $getMessagesRequest->setStatusCode($statusCode);
        });

        // 날짜로 조회
        if ($request->has('startDate') && $request->has('endDate')) {

            // 필요하다면 일자 검색유형을 선택할 수 있음, CREATED: 생성일 기준, UPDATED: 수정일 기준, 기본값: CREATED
            // $getMessagesRequest->setDateType('UPDATED');

            $startDate = Carbon::parse($request->get('startDate'))->toIso8601String();
            $endDate = Carbon::parse($request->get('endDate'))->toIso8601String();
            $getMessagesRequest->setStartDate($startDate)
                ->setEndDate($endDate);
        }

        $messages = $this->messageService->getMessages($getMessagesRequest);
        return response()->json($messages);
    }

    /**
     * 문자 발송(단문 문자, 장문 문자, 사진 문자)
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required',
            'to' => 'required',
            'text' => 'required|string',
            'image' => 'mimes:jpeg,jpg|size:200|dimensions:max_width=1500,max_height=1440'
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

    /**
     * 잔액조회
     * @return JsonResponse
     */
    public function get_balance(): JsonResponse
    {
        $balance = $this->messageService->getBalance();
        return response()->json($balance);
    }

    /**
     * 그룹 조회
     * @param Request $request
     * @return JsonResponse
     */
    public function get_groups(Request $request): JsonResponse
    {
        $getGroupsRequest = new GetGroupsRequest();

        // 페이지네이션을 위한 시작 키 설정, 메시지 조회 시 nextKey로 무한 페이지네이션 가능
        $request->whenHas('startKey', function (string $startKey) use ($getGroupsRequest) {
            $getGroupsRequest->startKey = $startKey;
        });

        // 데이터 조회 건 수 지정, 최대 500건 까지 가능
        $request->whenHas('limit', function (int $limit) use ($getGroupsRequest) {
            $getGroupsRequest->limit = $limit;
        });

        // 날짜로 조회
        if ($request->has('startDate') && $request->has('endDate')) {
            $startDate = Carbon::parse($request->get('startDate'))->toIso8601String();
            $endDate = Carbon::parse($request->get('endDate'))->toIso8601String();

            $getGroupsRequest->startDate = $startDate;
            $getGroupsRequest->endDate = $endDate;
        }

        $groups = $this->messageService->getGroups($getGroupsRequest);
        return response()->json($groups);
    }

    /**
     * 그룹 조회
     * @param string $groupId
     * @return JsonResponse
     */
    public function get_group(string $groupId): JsonResponse
    {
        $group = $this->messageService->getGroup($groupId);
        return response()->json($group);
    }

    /**
     * 특정 그룹 내 메시지 목록 조회
     * @param string $groupId
     * @param Request $request
     * @return JsonResponse
     */
    public function get_group_messages(string $groupId, Request $request): JsonResponse
    {
        $getGroupMessagesRequest = new GetGroupMessagesRequest();

        // 페이지네이션을 위한 시작 키 설정, 메시지 조회 시 nextKey로 무한 페이지네이션 가능
        $request->whenHas('startKey', function (string $startKey) use ($getGroupMessagesRequest) {
            $getGroupMessagesRequest->setStartKey($startKey);
        });

        // 데이터 조회 건 수 지정, 최대 500건 까지 가능
        $request->whenHas('limit', function (int $limit) use ($getGroupMessagesRequest) {
            $getGroupMessagesRequest->setLimit($limit);
        });

        $groupMessages = $this->messageService->getGroupMessages($groupId, $getGroupMessagesRequest);
        return response()->json($groupMessages);
    }

    /**
     * 사용 현황(통계) 조회
     * @param Request $request
     * @return JsonResponse
     */
    public function get_statistics(Request $request): JsonResponse
    {
        $getStatisticsRequest = new GetStatisticsRequest();

        // 날짜로 조회
        if ($request->has('startDate') && $request->has('endDate')) {
            $startDate = Carbon::parse($request->get('startDate'))->toIso8601String();
            $endDate = Carbon::parse($request->get('endDate'))->toIso8601String();

            $getStatisticsRequest->setStartDate($startDate)
                ->setEndDate($endDate);
        }

        $statistics = $this->messageService->getStatistics($getStatisticsRequest);
        return response()->json($statistics);
    }
}
