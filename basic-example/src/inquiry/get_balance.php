<?php

use Nurigo\Solapi\Services\SolapiMessageService;

require_once("../../vendor/autoload.php");

/**
 * 충전 요금 조회 예제
 */
$messageService = new SolapiMessageService("ENTER_YOUR_API_KEY", "ENTER_YOUR_API_SECRET");
$response = $messageService->getStatistics();
