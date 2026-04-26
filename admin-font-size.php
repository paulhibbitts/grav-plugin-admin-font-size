<?php

// Developed with the assistance of Claude Code (claude.ai)

namespace Grav\Plugin;

use Grav\Common\Plugin;

class AdminFontSizePlugin extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized()
    {
        if ($this->isAdmin2Route()) {
            $this->enable([
                'onPagesInitialized' => ['onPagesInitializedAdmin2', 1001],
            ]);
            return;
        }

        if ($this->isAdmin()) {
            $this->enable([
                'onOutputGenerated' => ['onOutputGeneratedAdmin1', 0],
            ]);
        }
    }

    private function isAdmin2Route(): bool
    {
        if (!$this->config->get('plugins.admin2.enabled', false)) {
            return false;
        }
        $route = $this->config->get('plugins.admin2.route', '');
        if (!$route) {
            return false;
        }
        $base = '/' . trim($route, '/');
        $current = $this->grav['uri']->route();
        return $current === $base || str_starts_with($current, $base . '/');
    }

    public function onPagesInitializedAdmin2(): void
    {
        $fontSize = $this->config->get('plugins.admin-font-size.admin_font_size', 'large');
        if ($fontSize === 'default') {
            return;
        }
        $cssFile = __DIR__ . "/assets/admin-fonts-{$fontSize}.css";
        if (!file_exists($cssFile)) {
            return;
        }
        $css = file_get_contents($cssFile);
        ob_start(function (string $html) use ($css): string {
            if (strpos($html, 'data-sveltekit-preload-data') === false) {
                return $html;
            }
            return str_replace('</head>', '<style>' . $css . '</style></head>', $html);
        });
    }

    public function onOutputGeneratedAdmin1($event): void
    {
        $fontSize = $this->config->get('plugins.admin-font-size.admin_font_size', 'large');
        if ($fontSize === 'default') {
            return;
        }
        $cssFile = __DIR__ . "/assets/admin-fonts-{$fontSize}.css";
        if (!file_exists($cssFile)) {
            return;
        }
        $css = file_get_contents($cssFile);
        $event['output'] = str_replace('</head>', '<style>' . $css . '</style></head>', $event['output']);
    }
}
