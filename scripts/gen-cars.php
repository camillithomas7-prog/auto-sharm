<?php
/**
 * Genera immagini delle auto della flotta con OpenAI gpt-image-2.
 * Salva in /assets/ con i nomi referenziati in setup.php.
 */

$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) { fwrite(STDERR, "OPENAI_API_KEY non impostata\n"); exit(1); }

$cars = [
    [
        'file' => 'car-i10.jpg',
        'prompt' => 'Professional editorial photo of a white Hyundai i10 city car parked on a sunny Sharm El Sheikh promenade. Palm trees on the side, Red Sea visible in the background, soft late-afternoon golden light, sharp focus on the car, slight low-angle three-quarter front view, clean and modern, no people, no text, no logos visible, photorealistic, high detail, professional automotive photography.',
    ],
    [
        'file' => 'car-corolla.jpg',
        'prompt' => 'Professional editorial photo of a silver Toyota Corolla sedan, latest generation, parked at a luxury hotel entrance in Sharm El Sheikh, palm trees, blue sky, beautiful warm sunlight, three-quarter front angle, ultra-sharp focus, clean reflections on the body, photorealistic, no people, no text, premium automotive photography.',
    ],
    [
        'file' => 'car-wrangler.jpg',
        'prompt' => 'Professional editorial photo of a sand-beige Jeep Wrangler 4x4 with removable top, parked on a sandy desert road in the Sinai mountains near Sharm El Sheikh at golden hour, dramatic warm light, dust softly lit, three-quarter front low angle, photorealistic, adventurous mood, no people, no visible text or logos, premium automotive photography.',
    ],
    [
        'file' => 'car-eclass.jpg',
        'prompt' => 'Professional editorial photo of a black Mercedes-Benz E-Class sedan, latest model, parked in front of a modern luxury resort entrance in Sharm El Sheikh, marble floor, warm soft evening light, glossy reflections, three-quarter front view, ultra-sharp, photorealistic, premium executive look, no people, no visible text or logos.',
    ],
    [
        'file' => 'car-h1.jpg',
        'prompt' => 'Professional editorial photo of a white Hyundai H1 9-seater minivan, latest generation, parked at the Sharm El Sheikh airport pickup area at sunrise, palm trees, soft warm light, three-quarter front angle, ultra-sharp, photorealistic, clean and modern, no people, no visible text or logos, premium automotive photography.',
    ],
];

$outDir = __DIR__ . '/../assets';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

foreach ($cars as $i => $c) {
    $idx = $i + 1;
    echo "[$idx/" . count($cars) . "] generating {$c['file']}...\n";

    $payload = json_encode([
        'model' => 'gpt-image-2',
        'prompt' => $c['prompt'],
        'size' => '1536x1024',
        'n' => 1,
    ]);

    $ch = curl_init('https://api.openai.com/v1/images/generations');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 180,
    ]);
    $resp = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($code !== 200) {
        fwrite(STDERR, "  ✗ HTTP $code: " . substr($resp, 0, 400) . "\n");
        continue;
    }

    $data = json_decode($resp, true);
    if (empty($data['data'][0]['b64_json'])) {
        fwrite(STDERR, "  ✗ Risposta inattesa: " . substr($resp, 0, 200) . "\n");
        continue;
    }

    $bin = base64_decode($data['data'][0]['b64_json']);
    $path = $outDir . '/' . $c['file'];
    file_put_contents($path, $bin);
    echo "  ✓ saved " . filesize($path) . " bytes → $path\n";
}

echo "\nDone.\n";
