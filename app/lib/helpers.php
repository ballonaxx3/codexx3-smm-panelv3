<?php
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function money(float|int|string $amount): string { return '$'.number_format((float)$amount, 2); }
function redirect(string $path): void { header('Location: '.$path); exit; }
function log_action(string $msg): void { @file_put_contents(__DIR__.'/../../logs/actions.log', '['.date('Y-m-d H:i:s').'] '.$msg.PHP_EOL, FILE_APPEND); }
function calc_price(float $rate, int $quantity): float { return round(($rate * $quantity) / 1000, 4); }
