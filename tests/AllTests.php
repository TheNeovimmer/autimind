<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require_once __DIR__ . '/TestRunner.php';
require_once __DIR__ . '/QuizScoringServiceTest.php';
require_once __DIR__ . '/ChatbotServiceTest.php';
require_once __DIR__ . '/InsightServiceTest.php';
require_once __DIR__ . '/ModelTest.php';

use Tests\QuizScoringServiceTest;
use Tests\ChatbotServiceTest;
use Tests\InsightServiceTest;
use Tests\ModelTest;

$startTime = microtime(true);

echo "========================================\n";
echo "  AutiMind Full Test Suite\n";
echo "========================================\n\n";

$scoringResult = (new QuizScoringServiceTest())->run();
$chatbotResult = (new ChatbotServiceTest())->run();
$insightResult = (new InsightServiceTest())->run();
$modelResult = (new ModelTest())->run();

$elapsed = round(microtime(true) - $startTime, 2);

$totalPassed = $scoringResult['passed'] + $chatbotResult['passed'] + $insightResult['passed'] + $modelResult['passed'];
$totalFailed = $scoringResult['failed'] + $chatbotResult['failed'] + $insightResult['failed'] + $modelResult['failed'];
$totalTests = $scoringResult['total'] + $chatbotResult['total'] + $insightResult['total'] + $modelResult['total'];

echo "\n========================================\n";
echo "  TOTAL: {$totalPassed} passed, {$totalFailed} failed, {$totalTests} total\n";
echo "  Time: {$elapsed}s\n";
$exitCode = $totalFailed > 0 ? 1 : 0;
echo "========================================\n";
exit($exitCode);
