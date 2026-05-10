<?php
require_once __DIR__ . '/i18n.php';

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/**
 * Feature flag per le sezioni del sito.
 * - cars: sempre ON (core business)
 * - transfer: di default OFF, attivabile da admin/impostazioni
 */
function featureEnabled(string $key): bool {
    if ($key === 'cars') return true;
    $defaults = ['transfer' => false];
    $raw = setting('feature_' . $key);
    if ($raw === null) return $defaults[$key] ?? false;
    return $raw === '1';
}

function setting(string $key, ?string $default = null): ?string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (rows('SELECT setting_key, setting_value FROM settings') as $s) {
                $cache[$s['setting_key']] = $s['setting_value'];
            }
        } catch (Throwable $e) {}
    }
    return $cache[$key] ?? $default;
}

function fmtMoney(float $amount, ?string $cur = null): string {
    $cur = $cur ?: cfg('site.currency') ?: 'EUR';
    if (class_exists('NumberFormatter')) {
        $f = new NumberFormatter('it_IT', NumberFormatter::CURRENCY);
        return $f->formatCurrency($amount, $cur);
    }
    $sym = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'][$cur] ?? $cur;
    return $sym . ' ' . number_format($amount, 2, ',', '.');
}

function fmtDate($d): string {
    if (!$d) return '—';
    $ts = is_numeric($d) ? (int)$d : strtotime((string)$d);
    if (!$ts) return '—';
    return date('d/m/Y', $ts);
}

function fmtDateShort($d): string { return fmtDate($d); }

function fmtDateTime($d): string {
    if (!$d) return '—';
    $ts = is_numeric($d) ? (int)$d : strtotime((string)$d);
    return date('d/m/Y H:i', $ts);
}

function daysBetween(string $a, string $b): int {
    $ta = strtotime($a); $tb = strtotime($b);
    if (!$ta || !$tb) return 0;
    return max(1, (int)round(($tb - $ta) / 86400));
}

function bookingCode(int $seq): string {
    return 'AS-' . date('Y') . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
}

function slugify(string $s): string {
    $s = mb_strtolower(trim($s));
    if (function_exists('transliterator_transliterate')) {
        $s = transliterator_transliterate('Any-Latin; Latin-ASCII;', $s) ?: $s;
    }
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

function isVideoUrl(?string $url): bool {
    if (!$url) return false;
    $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: $url, PATHINFO_EXTENSION));
    return in_array($ext, ['mp4', 'webm', 'mov', 'm4v']);
}

function parseFeatures($raw): array {
    if (!$raw) return [];
    $v = json_decode($raw, true);
    return is_array($v) ? $v : [];
}

function flash(?string $msg = null, string $type = 'info') {
    startSession();
    if ($msg !== null) { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $url): void { header("Location: $url"); exit; }

function url(string $path = ''): string {
    return rtrim(cfg('site.url') ?: '', '/') . $path;
}

function asset(string $path): string {
    $f = __DIR__ . '/../' . ltrim($path, '/');
    $v = file_exists($f) ? filemtime($f) : '1';
    return $path . '?v=' . $v;
}

function countryDialCodes(): array {
    return [
        'IT'=>'39','DE'=>'49','RU'=>'7','UA'=>'380','GB'=>'44','FR'=>'33','ES'=>'34','PL'=>'48','CZ'=>'420','SK'=>'421','RO'=>'40','BG'=>'359','HU'=>'36','AT'=>'43','CH'=>'41','BE'=>'32','NL'=>'31','SE'=>'46','NO'=>'47','DK'=>'45','FI'=>'358','IE'=>'353','PT'=>'351','GR'=>'30','EG'=>'20',
        'US'=>'1','CA'=>'1','MX'=>'52','BR'=>'55','AR'=>'54',
        'AU'=>'61','NZ'=>'64','JP'=>'81','CN'=>'86','KR'=>'82','IN'=>'91','TR'=>'90','IL'=>'972','SA'=>'966','AE'=>'971','QA'=>'974','KW'=>'965',
    ];
}

function countryList(string $lang = 'it'): array {
    $codes = array_keys(countryDialCodes());
    $list = [];
    foreach ($codes as $code) {
        $name = class_exists('Locale') ? \Locale::getDisplayRegion('-' . $code, $lang) : $code;
        if (!$name || $name === $code) $name = $code;
        $list[] = ['code' => $code, 'name' => $name];
    }
    usort($list, fn($a, $b) => strcoll($a['name'], $b['name']));
    return $list;
}

function phoneCountryList(string $lang = 'it'): array {
    $dial = countryDialCodes();
    $list = [];
    foreach (countryList($lang) as $c) {
        if (!isset($dial[$c['code']])) continue;
        $list[] = ['code' => $c['code'], 'name' => $c['name'], 'dial' => $dial[$c['code']]];
    }
    return $list;
}

date_default_timezone_set(cfg('site.timezone') ?: 'Africa/Cairo');
