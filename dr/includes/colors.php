<?php
/**
 * SehatNexa — Brand Color Palette
 * Logo se extract kiye gaye colors
 * Usage: echo $color1;  ya  var: <?= $color1 ?>
 */

// ── Primary Colors ──────────────────────────────────────────
$color1 = '#1C3F8F';   // Deep Navy Blue   — "Sehat" text, main icon body
$color2 = '#00A8CC';   // Sky Teal Blue     — icon gradient (middle/top)
$color3 = '#27C96A';   // Bright Green      — "Nexa" text, swoosh arc
$color4 = '#00C9A7';   // Teal Green        — heart icon, underline curve

// ── Secondary / Support Colors ──────────────────────────────
$color5 = '#0D2B6E';   // Dark Navy         — headings, footer bg
$color6 = '#E8F7FF';   // Light Blue Tint   — section backgrounds
$color7 = '#F0FFF8';   // Light Green Tint  — card backgrounds
$color8 = '#FFFFFF';   // White             — text on dark bg

// ── Gradient Definitions (CSS strings) ──────────────────────
$gradient_primary  = 'linear-gradient(135deg, #1C3F8F 0%, #00A8CC 100%)';  // Blue gradient
$gradient_green    = 'linear-gradient(135deg, #27C96A 0%, #00C9A7 100%)';  // Green gradient
$gradient_full     = 'linear-gradient(135deg, #1C3F8F 0%, #00A8CC 50%, #27C96A 100%)'; // Full brand

// ── Semantic Aliases ────────────────────────────────────────
$color_primary     = $color1;   // Main brand color (navy blue)
$color_secondary   = $color3;   // Accent color (green)
$color_accent      = $color2;   // Highlight color (teal blue)
$color_success     = $color4;   // Success / health green
$color_dark        = $color5;   // Dark text / bg
$color_light_blue  = $color6;   // Light section bg
$color_light_green = $color7;   // Light card bg
$color_white       = $color8;   // White

?>
