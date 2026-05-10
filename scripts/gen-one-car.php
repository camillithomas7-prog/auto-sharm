<?php
/**
 * Genera una singola immagine. Lanciato in parallelo da uno script wrapper.
 * Uso: php gen-one-car.php <filename> "<prompt>"
 */
error_reporting(E_ERROR);
ini_set('display_errors', '0');

$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) { fwrite(STDERR, "OPENAI_API_KEY non impostata\n"); exit(1); }

[$_, $file, $prompt] = $argv + [null, null, null];
if (!$file || !$prompt) { fwrite(STDERR, "Uso: gen-one-car.php <file> <prompt>\n"); exit(1); }

$outDir = __DIR__ . '/../assets';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);
$outPath = $outDir . '/' . $file;

$payload = json_encode([
    'model' => 'gpt-image-2',
    'prompt' => $prompt,
    'size' => '1536x1024',
    'n' => 1,
]);

$attempts = 0; $maxAttempts = 3;
while ($attempts < $maxAttempts) {
    $attempts++;
    $ch = curl_init('https://api.openai.com/v1/images/generations');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 600,
        CURLOPT_CONNECTTIMEOUT => 30,
    ]);
    $resp = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);

    if ($code !== 200) {
        fwrite(STDERR, "[$file] tentativo $attempts/$maxAttempts → HTTP $code · $err · " . substr((string)$resp, 0, 200) . "\n");
        sleep(2);
        continue;
    }

    $data = json_decode($resp, true);
    if (!empty($data['data'][0]['b64_json'])) {
        $bin = base64_decode($data['data'][0]['b64_json']);
        file_put_contents($outPath, $bin);
        echo "✓ $file · " . round(filesize($outPath) / 1024) . " KB\n";
        exit(0);
    }
    if (!empty($data['data'][0]['url'])) {
        $img = @file_get_contents($data['data'][0]['url']);
        if ($img !== false) {
            file_put_contents($outPath, $img);
            echo "✓ $file · " . round(filesize($outPath) / 1024) . " KB (via URL)\n";
            exit(0);
        }
    }
    fwrite(STDERR, "[$file] tentativo $attempts → risposta inattesa: " . substr($resp, 0, 200) . "\n");
    sleep(2);
}

fwrite(STDERR, "[$file] FALLITO dopo $maxAttempts tentativi\n");
exit(1);
