<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SkfariFixCommand extends Command
{
    protected $signature = 'skfari:fix {--apply : Write changes to disk} {--dry-run : Show what would change without writing}';
    protected $description = 'Fix Aimeos/Laravel auth, CSRF, and admin routes configuration (idempotent)';

    protected Filesystem $fs;
    protected array $backupPaths = [];

    public function __construct()
    {
        parent::__construct();
        $this->fs = new Filesystem();
    }

    public function handle(): int
    {
        $apply = $this->option('apply');
        $dry   = $this->option('dry-run');

        if (!$apply && !$dry) {
            $this->warn('Tip: Run with --dry-run to preview, or --apply to write changes.');
        }

        $changes = [];

        // 1) .env keys
        $changes[] = $this->fixEnv();

        // 2) config/shop.php
        $changes[] = $this->fixShopConfig();

        // 3) Inject meta CSRF into Aimeos page template (if exists)
        $changes[] = $this->injectMetaCsrf();

        // 4) Ensure Axios CSRF setup
        $changes[] = $this->fixBootstrapJs();

        // Flatten
        $changes = array_filter($changes);
        $total   = 0;

        foreach ($changes as $change) {
            foreach ($change as $item) {
                $total++;
                [$path, $newContent, $reason] = $item;

                if (!$this->fs->exists($path)) {
                    $this->line("• Skip (not found): $path");
                    continue;
                }

                $old = $this->fs->get($path);

                if ($old === $newContent) {
                    $this->line("• OK (no change needed): $path  — $reason");
                    continue;
                }

                if ($dry) {
                    $this->info("[DRY] Would update: $path  — $reason");
                    continue;
                }

                if ($apply) {
                    $this->backup($path, $old);
                    $this->fs->put($path, $newContent);
                    $this->info("✓ Updated: $path  — $reason");
                }
            }
        }

        if ($apply && $total > 0) {
            // clear caches
            $this->callSilent('config:clear');
            $this->callSilent('route:clear');
            $this->callSilent('view:clear');
            $this->callSilent('optimize:clear');
            $this->info('✓ Caches cleared.');
        }

        if ($dry) {
            $this->line('Dry run complete.');
        }

        if (!$apply && !$dry) {
            $this->line('No changes written. Re-run with --dry-run or --apply.');
        }

        return self::SUCCESS;
    }

    protected function backup(string $path, string $content): void
    {
        $dir  = base_path('storage/skfari_backups');
        if (!$this->fs->exists($dir)) {
            $this->fs->makeDirectory($dir, 0755, true);
        }
        $stamp = date('Ymd_His');
        $name  = str_replace(DIRECTORY_SEPARATOR, '__', ltrim(str_replace(base_path(), '', $path), DIRECTORY_SEPARATOR));
        $bpath = $dir . DIRECTORY_SEPARATOR . $name . '.' . $stamp . '.bak';
        $this->fs->put($bpath, $content);
        $this->backupPaths[] = $bpath;
    }

    protected function fixEnv(): array
    {
        $path = base_path('.env');
        if (!$this->fs->exists($path)) {
            $this->warn('.env not found');
            return [];
        }
        $content = $this->fs->get($path);

        $pairs = [
            'APP_URL'                  => 'http://skfari-clean.test',
            'SESSION_DRIVER'           => 'file',
            'SESSION_DOMAIN'           => 'skfari-clean.test',
            'SESSION_SECURE_COOKIE'    => 'false',
            'SANCTUM_STATEFUL_DOMAINS' => 'skfari-clean.test',
        ];

        foreach ($pairs as $key => $val) {
            $pattern = '/^' . preg_quote($key, '/') . '\s*=.*$/m';
            $line    = $key . '=' . $val;
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content .= PHP_EOL . $line;
            }
        }

        return [[ $path, $content, '.env keys (APP_URL / SESSION_* / SANCTUM_)' ]];
    }

    protected function fixShopConfig(): array
    {
        $path = config_path('shop.php');
        if (!$this->fs->exists($path)) {
            $this->warn('config/shop.php not found');
            return [];
        }
        $c = $this->fs->get($path);

        // Ensure admin routes have auth
        $c = preg_replace_callback(
            '/\'admin\'\s*=>\s*\[(.*?)\]/s',
            function ($m) {
                $block = $m[0];

                // Ensure prefix
                if (!preg_match('/\'prefix\'\s*=>\s*[\'"]admin[\'"]/', $block)) {
                    $block = preg_replace('/\'admin\'\s*=>\s*\[(.*?)/s', '\'admin\' => [\'prefix\' => \'admin\', $1', $block, 1);
                }

                // Ensure middleware contains auth
                if (preg_match('/\'middleware\'\s*=>\s*\[([^\]]*)\]/', $block, $mm)) {
                    $list = $mm[1];
                    if (!preg_match('/[\'"]auth[\'"]/', $list)) {
                        $list = trim($list);
                        $list = $list ? $list . ', \'auth\'' : '\'web\', \'auth\'';
                        $block = preg_replace('/\'middleware\'\s*=>\s*\[[^\]]*\]/', '\'middleware\' => [' . $list . ']', $block, 1);
                    }
                } else {
                    // add middleware
                    $block = preg_replace('/\'admin\'\s*=>\s*\[(.*?)/s', '\'admin\' => [\'middleware\' => [\'web\', \'auth\'], $1', $block, 1);
                }

                return $block;
            },
            $c
        );

        // Ensure jqadm/jsonadm/graphql routes have auth if they exist
        foreach (['jqadm','jsonadm','graphql'] as $key) {
            $c = preg_replace_callback(
                '/\'' . $key . '\'\s*=>\s*\[(.*?)\]/s',
                function ($m) {
                    $block = $m[0];
                    if (preg_match('/\'middleware\'\s*=>\s*\[([^\]]*)\]/', $block, $mm)) {
                        $list = $mm[1];
                        if (!preg_match('/[\'"]auth[\'"]/', $list)) {
                            $list = trim($list);
                            $list = $list ? $list . ', \'auth\'' : '\'web\', \'auth\'';
                            $block = preg_replace('/\'middleware\'\s*=>\s*\[[^\]]*\]/', '\'middleware\' => [' . $list . ']', $block, 1);
                        }
                    } else {
                        $block = preg_replace('/=>\s*\[/', '=> [\'middleware\' => [\'web\', \'auth\'], ', $block, 1);
                    }
                    return $block;
                },
                $c
            );
        }

        // Ensure mshop.locale.site = default
        if (preg_match('/\'mshop\'\s*=>\s*\[(.*?)\]/s', $c, $m)) {
            $mshopBlock = $m[0];
            if (preg_match('/\'locale\'\s*=>\s*\[(.*?)\]/s', $mshopBlock, $mm)) {
                $localeBlock = $mm[0];
                if (preg_match('/\'site\'\s*=>\s*[\'"][^\'"]+[\'"]/', $localeBlock)) {
                    $localeBlock = preg_replace('/\'site\'\s*=>\s*[\'"][^\'"]+[\'"]/', '\'site\' => \'default\'', $localeBlock);
                } else {
                    $localeBlock = preg_replace('/\'locale\'\s*=>\s*\[/', '\'locale\' => [\'site\' => \'default\', ', $localeBlock, 1);
                }
                $mshopBlock = str_replace($mm[0], $localeBlock, $mshopBlock);
            } else {
                $mshopBlock = preg_replace('/\'mshop\'\s*=>\s*\[/', '\'mshop\' => [\'locale\' => [\'site\' => \'default\'], ', $mshopBlock, 1);
            }
            $c = str_replace($m[0], $mshopBlock, $c);
        } else {
            // append minimal mshop block
            $c = preg_replace('/return\s*\[\s*/', "return [\n    'mshop' => [ 'locale' => ['site' => 'default'] ],\n", $c, 1);
        }

        return [[ $path, $c, 'config/shop.php (admin auth + mshop.locale.site)' ]];
    }

    protected function injectMetaCsrf(): array
    {
        $path = resource_path('views/vendor/shop/page.blade.php');
        if (!$this->fs->exists($path)) {
            // nothing to do if template not published/overridden
            return [];
        }
        $c = $this->fs->get($path);

        if (strpos($c, 'name="csrf-token"') !== false) {
            return [[ $path, $c, 'page.blade.php already has meta csrf' ]];
        }

        // inject after <head>
        $new = preg_replace(
            '/(<head[^>]*>)/i',
            "$1\n    <meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">",
            $c,
            1
        );

        if ($new === null) {
            return [];
        }

        return [[ $path, $new, 'Inject <meta name="csrf-token">' ]];
    }

    protected function fixBootstrapJs(): array
    {
        $path = resource_path('js/bootstrap.js');
        if (!$this->fs->exists($path)) {
            return [];
        }
        $c = $this->fs->get($path);

        if (strpos($c, "X-CSRF-TOKEN") !== false && strpos($c, "withCredentials") !== false) {
            return [[ $path, $c, 'bootstrap.js already configures CSRF' ]];
        }

        // Ensure axios import exists
        if (strpos($c, "import axios from 'axios'") === false) {
            $c = "import axios from 'axios';\n" . $c;
        }

        $snippet = <<<JS

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const tokenMeta = document.querySelector('meta[name=\"csrf-token\"]');
if (tokenMeta) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
}
window.axios.defaults.withCredentials = true;

JS;

        if (strpos($c, "window.axios = axios") === false) {
            $c .= "\n" . $snippet;
        }

        return [[ $path, $c, 'bootstrap.js add CSRF/axios defaults' ]];
    }
}
