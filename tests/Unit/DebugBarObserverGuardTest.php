<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\PluginLocal\DebugBar\Unit;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('parallel-candidate')]
class DebugBarObserverGuardTest extends TestCase
{
    public function testCatalogObserverRequiresExplicitEnablement(): void
    {
        $result = $this->runGuardProbe(
            'catalog',
            [
                'IS_ADMIN_FLAG' => false,
                'DEBUG_BAR_ENABLED' => 'false',
                'DEBUG_BAR_ADMINS_ONLY' => 'true',
            ],
            ['admin_id' => 1]
        );

        $this->assertSame('0', $result);
    }

    public function testCatalogObserverRejectsAnonymousStorefrontRequestWhenAdminsOnlyIsEnabled(): void
    {
        $result = $this->runGuardProbe(
            'catalog',
            [
                'IS_ADMIN_FLAG' => false,
                'DEBUG_BAR_ENABLED' => 'true',
                'DEBUG_BAR_ADMINS_ONLY' => 'true',
            ],
            []
        );

        $this->assertSame('0', $result);
    }

    public function testAdminObserverRequiresAuthenticatedAdminSession(): void
    {
        $result = $this->runGuardProbe(
            'admin',
            [
                'IS_ADMIN_FLAG' => true,
                'DEBUG_BAR_ENABLED' => 'true',
                'DEBUG_BAR_SHOW_IN_ADMIN' => 'true',
            ],
            []
        );

        $this->assertSame('0', $result);
    }

    public function testAdminObserverAllowsAuthenticatedAdminRequest(): void
    {
        $result = $this->runGuardProbe(
            'admin',
            [
                'IS_ADMIN_FLAG' => true,
                'DEBUG_BAR_ENABLED' => 'true',
                'DEBUG_BAR_SHOW_IN_ADMIN' => 'true',
            ],
            ['admin_id' => 1]
        );

        $this->assertSame('1', $result);
    }

    private function runGuardProbe(string $context, array $defines, array $session): string
    {
        $pluginRoot = realpath(__DIR__ . '/../..');
        $this->assertNotFalse($pluginRoot, 'Unable to resolve plugin root.');

        $rootPath = realpath($pluginRoot . '/../../..');
        $this->assertNotFalse($rootPath, 'Unable to resolve Zen Cart root.');

        $observerPath = $pluginRoot . '/'
            . ($context === 'admin'
                ? 'admin/includes/classes/observers/auto.debug_bar_admin.php'
                : 'catalog/includes/classes/observers/auto.debug_bar.php');
        $observerClass = $context === 'admin' ? 'zcObserverDebugBarAdmin' : 'zcObserverDebugBar';

        $defineLines = ["define('DIR_FS_CATALOG', " . var_export(rtrim($rootPath, '/') . '/', true) . ');'];
        foreach ($defines as $key => $value) {
            $defineLines[] = "define('{$key}', " . var_export($value, true) . ');';
        }

        $script = implode("\n", [
            'require ' . var_export($rootPath . '/vendor/autoload.php', true) . ';',
            implode("\n", $defineLines),
            '$_SESSION = ' . var_export($session, true) . ';',
            'require ' . var_export($rootPath . '/includes/classes/class.base.php', true) . ';',
            'require ' . var_export($observerPath, true) . ';',
            '$observer = new class extends ' . $observerClass . ' { public function __construct() {} public function canRender(): bool { return $this->shouldRenderDebugBar(); } };',
            "echo \$observer->canRender() ? '1' : '0';",
        ]);

        $command = 'php -r ' . escapeshellarg($script);
        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));

        return trim(implode("\n", $output));
    }
}
