<?php

require_once("../../../vendor/autoload.php");

use Nurigo\Solapi\Exceptions\MessageNotReceivedException;
use Nurigo\Solapi\Models\Kakao\KakaoOption;
use Nurigo\Solapi\Models\Message;
use Nurigo\Solapi\Services\SolapiMessageService;

/**
 * 카카오 친구톡 발송 예제
 * 수신번호에 반드시 -, * 등 특수문자를 제거하여 기입하셔야 합니다! 예) 01012345678
 */
try {
    $messageService = new SolapiMessageService("ENTER_YOUR_API_KEY", "ENTER_YOUR_API_SECRET");

    $kakaoOption = new KakaoOption();
    $kakaoOption->setPfId("연동한 비즈니스 채널의 pfId")
        ->setVariables(null);

    $message = new Message();
    $message->setTo("수신번호")
        ->setText("2,000 byte 이내의 메시지 입력")
        ->setKakaoOptions($kakaoOption);

    // 문자 대체 발송을 희망하실 경우 계정 내에 등록하신 발신번호를 추가해주세요!
    // $message->setFrom("계정에서 등록한 발신번호 입력");

    // 한 번에 여러 메시지를 발송할 경우 아래 주석을 해제하고 응용하여 사용해보세요!
    /*$message = [$message];
    for ($i = 0; $i < 3; $i++) {
        $tempMessage = new Message();
        $tempMessage->setTo("수신번호")
            ->setText("2,000 byte 이내의 메시지 입력")
            ->setKakaoOptions($kakaoOption);
        $message[] = $tempMessage;
    }*/

    // 예약 발송을 원하시는 경우 아래 주석을 해제하고 응용하여 사용해보세요!
    // date_default_timezone_set("Asia/Seoul");
    // $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", "2022-11-03 18:00:00");
    // $result = $messageService->send($message, $dateTime);

    $result = $messageService->send($message);
    print_r($result);
} catch (MessageNotReceivedException $exception) {
    print_r($exception->getFailedMessageList());
    print_r("----");
    print_r($exception->getMessage());
} catch (Exception $exception) {
    print_r($exception->getMessage());
}