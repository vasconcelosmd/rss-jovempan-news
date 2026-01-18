<?php
// URL do feed oficial da Jovem Pan News
$feedUrl = 'https://jovempan.com.br/jpnews/feed';

// Configurações de mídia indoor
$maxItems   = 8;    // Número máximo de notícias
$titleLimit = 80;   // Tamanho máximo do título
$descLimit  = 120;  // Tamanho máximo da descrição
$ttlMinutes = 10;   // Intervalo recomendado de atualização do RSS

// Palavras bloqueadas (astrologia, horóscopo, tarô)
$blockedWords = [
    'horóscopo','horoscopo','tarô','tarot','astrologia',
    'astrólogo','astróloga','signo','signos',
    'mapa astral','zodíaco','zodiaco',
    'previsão astral','previsao astral',
    'mercúrio retrógrado','mercurio retrogrado'
];

// Carrega o feed
$rss = @simplexml_load_file($feedUrl);
if (!$rss || !isset($rss->channel->item)) {
    header('Content-Type: application/rss+xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<rss version="2.0"><channel>';
    echo '<title>Jovem Pan News – Mídia Indoor</title>';
    echo '<description>Feed temporariamente indisponível</description>';
    echo '</channel></rss>';
    exit;
}

// Cabeçalho RSS
header('Content-Type: application/rss+xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<rss version="2.0"><channel>';
echo '<title>Jovem Pan News – Mídia Indoor</title>';
echo '<link>https://jovempan.com.br/jpnews</link>';
echo '<description>Principais manchetes atualizadas automaticamente, sem astrologia</description>';
echo '<language>pt-br</language>';
echo '<ttl>' . $ttlMinutes . '</ttl>';

$count = 0;

// Processa os itens
foreach ($rss->channel->item as $item) {
    if ($count >= $maxItems) break;

    $title = trim(strip_tags((string)$item->title));
    $desc  = trim(strip_tags((string)$item->description));
    $contentCheck = mb_strtolower($title . ' ' . $desc);

    // Bloqueia itens com palavras proibidas
    $blocked = false;
    foreach ($blockedWords as $word) {
        if (mb_strpos($contentCheck, mb_strtolower($word)) !== false) {
            $blocked = true;
            break;
        }
    }
    if ($blocked) continue;

    // Limita tamanho para mídia indoor
    $title = mb_substr($title, 0, $titleLimit);
    $desc  = mb_substr($desc, 0, $descLimit);

    echo '<item>';
    echo '<title><![CDATA[' . $title . ']]></title>';
    echo '<description><![CDATA[' . $desc . ']]></description>';
    echo '<link>' . (string)$item->link . '</link>';
    echo '<pubDate>' . date(DATE_RSS, strtotime((string)$item->pubDate)) . '</pubDate>';
    echo '</item>';

    $count++;
}

echo '</channel></rss>';
