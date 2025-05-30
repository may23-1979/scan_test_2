<?php
/**
 * URL の「パス」と「クエリ」を分解して HTML に一覧表示する簡易スクリプト
 *   - パス … / で分割した各セグメント
 *   - クエリ … key=value 形式（配列パラメータにも対応）
 *
 * XSS を防ぐため htmlspecialchars() で必ずエスケープしています。
 * PHP 7.4 以降推奨。
 */

// 現在アクセスされている URI を取得
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// パースして path と query を取り出す
$parsed = parse_url($uri);
$rawPath  = $parsed['path']  ?? '/';
$rawQuery = $parsed['query'] ?? '';

// -------- パス --------
$pathSegments = array_filter(
    explode('/', trim($rawPath, '/')),
    fn($seg) => $seg !== ''
);

// -------- クエリ --------
$queryParams = [];
parse_str($rawQuery, $queryParams);

// 配列（size[]=M&size[]=L など）をフラットに展開する再帰関数
function flattenQuery(array $array, string $prefix = ''): array {
    $flat = [];
    foreach ($array as $key => $value) {
        $fullKey = $prefix === '' ? $key : "{$prefix}[{$key}]";
        if (is_array($value)) {
            $flat += flattenQuery($value, $fullKey);
        } else {
            $flat[$fullKey] = $value;
        }
    }
    return $flat;
}
$flatQuery = flattenQuery($queryParams);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>URL 分解デモ</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem; }
        h2   { margin-top: 1.5rem; }
        ul   { padding-left: 1.2rem; }
        code { background: #f6f8fa; padding: .1rem .25rem; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>URL 分解結果</h1>

    <h2>パス (<?= count($pathSegments) ?> セグメント)</h2>
    <ul>
        <?php foreach ($pathSegments as $i => $seg): ?>
            <li>#<?= $i ?>:
                <code><?= htmlspecialchars($seg, ENT_QUOTES, 'UTF-8') ?></code>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>クエリ (<?= count($flatQuery) ?> 要素)</h2>
    <ul>
        <?php foreach ($flatQuery as $key => $val): ?>
            <li>
                <code><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></code>
                =
                <code><?= $val . 'test' ?></code>
            </li>
        <?php endforeach; ?>
        <?php if (!$flatQuery): ?>
            <li><em>クエリなし</em></li>
        <?php endif; ?>
    </ul>
</body>
</html>

