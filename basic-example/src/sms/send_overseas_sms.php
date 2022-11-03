<?php

require_once("../../vendor/autoload.php");

use Nurigo\Solapi\Exceptions\MessageNotReceivedException;
use Nurigo\Solapi\Models\Message;
use Nurigo\Solapi\Services\SolapiMessageService;

/**
 * 해외 단문 문자 발송 예제, 해외 문자는 오직 단문 문자(SMS)만 지원합니다.
 * 발신번호, 수신번호에 반드시 -, * 등 특수문자를 제거하여 기입하셔야 합니다! 예) 01012345678
 */
try {
    $messageService = new SolapiMessageService("ENTER_YOUR_API_KEY", "ENTER_YOUR_API_SECRET");

    $message = new Message();
    $message->setTo("국제번호를 제외한 수신번호")
        ->setFrom("계정에서 등록한 발신번호 입력")
        ->setText("한글 45자, 영자 90자 이하 입력되면 자동으로 SMS 타입의 메시지가 발송됩니다.");

    // 미국 국가번호, 국가번호 뒤에 추가로 번호가 붙는 국가들은 붙여서 기입해야 합니다. 예) 1 441 -> "1441"
    $message->setCountry("1");

    // 한 번에 여러 메시지를 발송할 경우 아래 주석을 해제하고 응용하여 사용해보세요!
    /*$message = [$message];
    for ($i = 0; $i < 3; $i++) {
        $tempMessage = new Message();
        $tempMessage->setTo("수신번호")
            ->setFrom("계정에서 등록한 발신번호 입력")
            ->setText("한글 45자, 영자 90자 이하 입력되면 자동으로 SMS타입의 메시지가 발송됩니다." . $i)
            ->setCountry("1");
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