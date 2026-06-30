<?php

namespace App\Support;

class ArticleContent
{
    public static function toHtml(mixed $content): string
    {
        if (blank($content)) {
            return '';
        }

        if (is_array($content)) {
            return self::tiptapToHtml($content);
        }

        if (is_string($content)) {
            $decoded = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return self::tiptapToHtml($decoded);
            }

            return self::sanitizeUtf8($content);
        }

        return '';
    }

    private static function tiptapToHtml(array $document): string
    {
        $html = self::renderTiptapNodes($document['content'] ?? []);

        return self::sanitizeUtf8($html);
    }

    private static function renderTiptapNodes(array $nodes): string
    {
        $html = '';

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $type = $node['type'] ?? null;
            $content = $node['content'] ?? [];

            $html .= match ($type) {
                'paragraph' => '<p>' . self::renderTiptapNodes($content) . '</p>',
                'heading'   => '<h' . ($node['attrs']['level'] ?? 2) . '>' . self::renderTiptapNodes($content) . '</h' . ($node['attrs']['level'] ?? 2) . '>',
                'bulletList', 'orderedList' => '<ul>' . self::renderTiptapNodes($content) . '</ul>',
                'listItem'  => '<li>' . self::renderTiptapNodes($content) . '</li>',
                'blockquote'=> '<blockquote>' . self::renderTiptapNodes($content) . '</blockquote>',
                'hardBreak' => '<br />',
                'text'      => self::renderTiptapText($node),
                'doc'       => self::renderTiptapNodes($content),
                default     => self::renderTiptapNodes($content),
            };
        }

        return $html;
    }

    private static function renderTiptapText(array $node): string
    {
        $text = e($node['text'] ?? '');

        foreach ($node['marks'] ?? [] as $mark) {
            $text = match ($mark['type'] ?? null) {
                'bold'      => "<strong>{$text}</strong>",
                'italic'    => "<em>{$text}</em>",
                'underline' => "<u>{$text}</u>",
                'link'      => '<a href="' . e($mark['attrs']['href'] ?? '#') . '">' . $text . '</a>',
                default     => $text,
            };
        }

        return $text;
    }

    private static function sanitizeUtf8(string $value): string
    {
        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return $value;
    }
}
