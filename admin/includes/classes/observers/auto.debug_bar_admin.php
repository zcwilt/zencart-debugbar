<?php

class zcObserverDebugBarAdmin extends base
{
    private static array $eventTrace = [];
    private const EVENT_TRACE_LIMIT = 60;

    public function __construct()
    {
        $this->attach($this, ['*']);
    }

    public function update(&$class, $eventID, $paramsArray = [])
    {
        if ($eventID === 'NOTIFY_ADMIN_FOOTER_END') {
            $this->renderDebugBar();
            return;
        }

        self::$eventTrace[] = [
            'event' => (string)$eventID,
            'source' => is_object($class) ? get_class($class) : gettype($class),
        ];

        if (count(self::$eventTrace) > self::EVENT_TRACE_LIMIT) {
            array_shift(self::$eventTrace);
        }
    }

    protected function renderDebugBar(): void
    {
        global $db, $messageStack;

        if (!defined('DEBUG_BAR_ENABLED') || DEBUG_BAR_ENABLED !== 'true') {
            return;
        }

        if (!defined('DEBUG_BAR_SHOW_IN_ADMIN') || DEBUG_BAR_SHOW_IN_ADMIN !== 'true') {
            return;
        }

        $parts = ['Zen Cart Debug Bar [Admin]'];

        if (defined('DEBUG_BAR_SHOW_TIMER') && DEBUG_BAR_SHOW_TIMER === 'true' && isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $parts[] = 'Time: ' . number_format(microtime(true) - (float)$_SERVER['REQUEST_TIME_FLOAT'], 4) . 's';
        }

        if (defined('DEBUG_BAR_SHOW_MEMORY') && DEBUG_BAR_SHOW_MEMORY === 'true') {
            $parts[] = 'Memory: ' . number_format(memory_get_usage(true) / 1048576, 2) . ' MB';
        }

        $sessionHtml = '';
        if (defined('DEBUG_BAR_SHOW_SESSION') && DEBUG_BAR_SHOW_SESSION === 'true') {
            $sessionKeys = array_keys($_SESSION ?? []);
            sort($sessionKeys);

            $sessionSummary = [];
            $sessionSummary[] = 'Session keys: ' . count($sessionKeys);
            if (!empty($_SESSION['admin_id'])) {
                $sessionSummary[] = 'admin_id=' . (int)$_SESSION['admin_id'];
            }
            if (function_exists('zen_get_admin_name') && !empty($_SESSION['admin_id'])) {
                $adminName = (string)zen_get_admin_name($_SESSION['admin_id']);
                if ($adminName !== '') {
                    $sessionSummary[] = 'admin_name=' . $adminName;
                }
            }
            if (!empty($_SESSION['language'])) {
                $sessionSummary[] = 'language=' . (string)$_SESSION['language'];
            }

            $parts[] = implode(', ', $sessionSummary);

            $sessionDump = print_r($_SESSION ?? [], true);
            $sessionHtml = '<details style="margin-top:6px;">'
                . '<summary style="cursor:pointer;color:#93c5fd;">Session Variables</summary>'
                . '<pre style="margin:8px 0 0;max-height:240px;overflow:auto;white-space:pre-wrap;background:#111827;color:#d1d5db;padding:8px;border:1px solid #374151;">'
                . htmlspecialchars($sessionDump, ENT_COMPAT, CHARSET, false)
                . '</pre>'
                . '</details>';
        }

        $requestHtml = '';
        if (defined('DEBUG_BAR_SHOW_REQUEST') && DEBUG_BAR_SHOW_REQUEST === 'true') {
            $requestSummary = [];
            $requestSummary[] = 'Method: ' . (string)($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');
            $requestSummary[] = 'GET=' . count($_GET ?? []);
            $requestSummary[] = 'POST=' . count($_POST ?? []);
            if (!empty($_GET['action'])) {
                $requestSummary[] = 'action=' . (string)$_GET['action'];
            }
            if (!empty($_GET['cmd'])) {
                $requestSummary[] = 'cmd=' . (string)$_GET['cmd'];
            }
            $parts[] = implode(', ', $requestSummary);

            $pageContext = [];
            $pageContext[] = 'php_self=' . (string)basename($_SERVER['PHP_SELF'] ?? 'unknown');
            $pageContext[] = 'request_uri=' . (string)($_SERVER['REQUEST_URI'] ?? '/');
            $pageContext[] = 'action=' . (!empty($_GET['action']) ? (string)$_GET['action'] : 'none');
            $pageContext[] = 'cmd=' . (!empty($_GET['cmd']) ? (string)$_GET['cmd'] : 'none');
            $pageContext[] = 'admin=' . (!empty($_SESSION['admin_id']) ? 'yes' : 'no');
            $pageContext[] = 'admin_id=' . (!empty($_SESSION['admin_id']) ? (string)(int)$_SESSION['admin_id'] : '0');
            if (function_exists('zen_get_admin_name') && !empty($_SESSION['admin_id'])) {
                $pageContext[] = 'admin_name=' . (string)zen_get_admin_name($_SESSION['admin_id']);
            }
            $pageContext[] = 'language=' . (!empty($_SESSION['language']) ? (string)$_SESSION['language'] : 'unknown');
            $parts[] = implode(', ', $pageContext);

            $getDump = print_r($_GET ?? [], true);
            $postDump = print_r($_POST ?? [], true);
            $requestHtml = '<details style="margin-top:6px;">'
                . '<summary style="cursor:pointer;color:#86efac;">Request Variables</summary>'
                . '<div style="margin-top:8px;">'
                . '<strong>Page Context</strong>'
                . '<pre style="margin:6px 0 10px;max-height:160px;overflow:auto;white-space:pre-wrap;background:#111827;color:#d1d5db;padding:8px;border:1px solid #374151;">'
                . htmlspecialchars(implode("\n", $pageContext), ENT_COMPAT, CHARSET, false)
                . '</pre>'
                . '<strong>GET</strong>'
                . '<pre style="margin:6px 0 10px;max-height:180px;overflow:auto;white-space:pre-wrap;background:#111827;color:#d1d5db;padding:8px;border:1px solid #374151;">'
                . htmlspecialchars($getDump, ENT_COMPAT, CHARSET, false)
                . '</pre>'
                . '<strong>POST</strong>'
                . '<pre style="margin:6px 0 0;max-height:180px;overflow:auto;white-space:pre-wrap;background:#111827;color:#d1d5db;padding:8px;border:1px solid #374151;">'
                . htmlspecialchars($postDump, ENT_COMPAT, CHARSET, false)
                . '</pre>'
                . '</div>'
                . '</details>';
        }

        $serverHtml = '';
        if (defined('DEBUG_BAR_SHOW_SERVER') && DEBUG_BAR_SHOW_SERVER === 'true') {
            $serverSummary = [];
            $serverSummary[] = 'Host: ' . (string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'unknown');
            $serverSummary[] = 'URI: ' . (string)($_SERVER['REQUEST_URI'] ?? '/');
            $serverSummary[] = 'HTTPS: ' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'on' : 'off');
            $serverSummary[] = 'IP: ' . (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            if (!empty($_SERVER['HTTP_USER_AGENT'])) {
                $userAgent = (string)$_SERVER['HTTP_USER_AGENT'];
                if (strlen($userAgent) > 60) {
                    $userAgent = substr($userAgent, 0, 57) . '...';
                }
                $serverSummary[] = 'UA: ' . $userAgent;
            }
            $parts[] = implode(', ', $serverSummary);

            $serverDump = print_r($_SERVER ?? [], true);
            $serverHtml = '<details style="margin-top:6px;">'
                . '<summary style="cursor:pointer;color:#fca5a5;">Server Variables</summary>'
                . '<pre style="margin:8px 0 0;max-height:240px;overflow:auto;white-space:pre-wrap;background:#111827;color:#d1d5db;padding:8px;border:1px solid #374151;">'
                . htmlspecialchars($serverDump, ENT_COMPAT, CHARSET, false)
                . '</pre>'
                . '</details>';
        }

        $notifierHtml = '';
        if (defined('DEBUG_BAR_SHOW_NOTIFIERS') && DEBUG_BAR_SHOW_NOTIFIERS === 'true') {
            $traceCount = count(self::$eventTrace);
            $parts[] = 'Events: ' . $traceCount;

            $traceLines = [];
            foreach (self::$eventTrace as $index => $trace) {
                $traceLines[] = sprintf(
                    '%d. %s [%s]',
                    $index + 1,
                    (string)$trace['event'],
                    (string)$trace['source']
                );
            }

            $notifierHtml = '<details style="margin-top:6px;">'
                . '<summary style="cursor:pointer;color:#fcd34d;">Notifier/Event Trace</summary>'
                . '<pre style="margin:8px 0 0;max-height:240px;overflow:auto;white-space:pre-wrap;background:#111827;color:#d1d5db;padding:8px;border:1px solid #374151;">'
                . htmlspecialchars(implode("\n", $traceLines), ENT_COMPAT, CHARSET, false)
                . '</pre>'
                . '</details>';
        }

        $databaseHtml = '';
        if (defined('DEBUG_BAR_SHOW_DATABASE') && DEBUG_BAR_SHOW_DATABASE === 'true' && isset($db) && is_object($db)) {
            $queryCount = method_exists($db, 'queryCount') ? (int)$db->queryCount() : null;
            $queryTime = method_exists($db, 'queryTime') ? (float)$db->queryTime() : null;

            $databaseSummary = [];
            if ($queryCount !== null) {
                $databaseSummary[] = 'Queries: ' . $queryCount;
            }
            if ($queryTime !== null) {
                $databaseSummary[] = 'SQL Time: ' . number_format($queryTime, 4) . 's';
            }

            if ($databaseSummary !== []) {
                $parts[] = implode(', ', $databaseSummary);

                $databaseDetails = [];
                if ($queryCount !== null) {
                    $databaseDetails[] = 'query_count=' . $queryCount;
                }
                if ($queryTime !== null) {
                    $databaseDetails[] = 'query_time=' . number_format($queryTime, 6) . 's';
                }
                if ($queryCount !== null && $queryCount > 0 && $queryTime !== null) {
                    $databaseDetails[] = 'avg_query_time=' . number_format($queryTime / $queryCount, 6) . 's';
                }

                $databaseHtml = '<details style="margin-top:6px;">'
                    . '<summary style="cursor:pointer;color:#86efac;">Database Query Summary</summary>'
                    . '<pre style="margin:8px 0 0;max-height:180px;overflow:auto;white-space:pre-wrap;background:#111827;color:#d1d5db;padding:8px;border:1px solid #374151;">'
                    . htmlspecialchars(implode("\n", $databaseDetails), ENT_COMPAT, CHARSET, false)
                    . '</pre>'
                    . '</details>';
            }
        }

        $messageHtml = '';
        if (defined('DEBUG_BAR_SHOW_MESSAGES') && DEBUG_BAR_SHOW_MESSAGES === 'true' && isset($messageStack) && is_object($messageStack) && isset($messageStack->messages) && is_array($messageStack->messages)) {
            $messages = $messageStack->messages;
            $messageCount = count($messages);
            $parts[] = 'Messages: ' . $messageCount;

            if ($messageCount > 0) {
                $messageGroups = [];
                foreach ($messages as $message) {
                    $messageClass = (string)($message['class'] ?? 'default');
                    $messageText = trim((string)($message['text'] ?? ''));
                    $messageGroups[$messageClass][] = $messageText;
                }

                $messageSummary = [];
                foreach ($messageGroups as $messageClass => $groupedMessages) {
                    $messageSummary[] = $messageClass . '=' . count($groupedMessages);
                }
                sort($messageSummary);
                $parts[] = 'Message stacks: ' . implode(', ', $messageSummary);

                $messageLines = [];
                foreach ($messageGroups as $messageClass => $groupedMessages) {
                    $messageLines[] = '[' . $messageClass . ']';
                    foreach ($groupedMessages as $index => $messageText) {
                        $messageLines[] = '  ' . ($index + 1) . '. ' . $messageText;
                    }
                    $messageLines[] = '';
                }

                $messageHtml = '<details style="margin-top:6px;">'
                    . '<summary style="cursor:pointer;color:#c4b5fd;">Message Stack</summary>'
                    . '<pre style="margin:8px 0 0;max-height:220px;overflow:auto;white-space:pre-wrap;background:#111827;color:#d1d5db;padding:8px;border:1px solid #374151;">'
                    . htmlspecialchars(trim(implode("\n", $messageLines)), ENT_COMPAT, CHARSET, false)
                    . '</pre>'
                    . '</details>';
            }
        }

        $fileLoadOrderHtml = '';
        if (!defined('DEBUG_BAR_SHOW_FILE_LOAD_ORDER') || DEBUG_BAR_SHOW_FILE_LOAD_ORDER === 'true') {
            $fileLoadOrder = $this->getTemplateAndLanguageLoadOrder();
            $parts[] = 'Templates: ' . $fileLoadOrder['template_count'];
            $parts[] = 'Languages: ' . $fileLoadOrder['language_count'];

            $fileLoadOrderHtml = '<details style="margin-top:6px;">'
                . '<summary style="cursor:pointer;color:#67e8f9;">Template/Language File Load Order</summary>'
                . '<pre style="margin:8px 0 0;max-height:320px;overflow:auto;white-space:pre-wrap;background:#111827;color:#d1d5db;padding:8px;border:1px solid #374151;">'
                . htmlspecialchars(implode("\n", $fileLoadOrder['lines']), ENT_COMPAT, CHARSET, false)
                . '</pre>'
                . '</details>';
        }

        echo '<div id="zc-debug-bar-toggle-admin" style="display:none;position:fixed;right:12px;bottom:12px;z-index:100000;background:#111827;color:#f9fafb;padding:6px 10px;border:1px solid #374151;border-radius:4px;font:12px/1.4 monospace;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.25);">Show Debug Bar</div>'
            . '<div id="zc-debug-bar-admin" style="position:fixed;left:0;right:0;bottom:0;z-index:99999;max-height:70vh;overflow-y:auto;background:#1f2937;color:#f9fafb;padding:8px 12px;font:12px/1.4 monospace;box-shadow:0 -2px 8px rgba(0,0,0,.25);">'
            . '<div style="position:sticky;top:-8px;z-index:1;display:flex;justify-content:space-between;align-items:center;gap:12px;margin:-8px -12px 8px;padding:8px 12px;background:#111827;border-bottom:1px solid #374151;">'
            . '<div>' . htmlspecialchars(implode(' | ', $parts), ENT_COMPAT, CHARSET, false) . '</div>'
            . '<div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">'
            . '<button type="button" id="zc-debug-bar-expand-all-admin" style="background:#374151;border:1px solid #4b5563;color:#f9fafb;padding:2px 8px;font:12px/1.2 monospace;cursor:pointer;">Expand All</button>'
            . '<button type="button" id="zc-debug-bar-collapse-all-admin" style="background:#374151;border:1px solid #4b5563;color:#f9fafb;padding:2px 8px;font:12px/1.2 monospace;cursor:pointer;">Collapse All</button>'
            . '<button type="button" id="zc-debug-bar-hide-admin" style="background:#374151;border:1px solid #4b5563;color:#f9fafb;padding:2px 8px;font:12px/1.2 monospace;cursor:pointer;">Hide</button>'
            . '</div>'
            . '</div>'
            . $sessionHtml
            . $requestHtml
            . $serverHtml
            . $notifierHtml
            . $databaseHtml
            . $messageHtml
            . $fileLoadOrderHtml
            . '</div>'
            . '<script>'
            . '(function(){'
            . 'var storageKey="zc_debug_bar_hidden_admin";'
            . 'var bar=document.getElementById("zc-debug-bar-admin");'
            . 'var toggle=document.getElementById("zc-debug-bar-toggle-admin");'
            . 'var hideButton=document.getElementById("zc-debug-bar-hide-admin");'
            . 'var collapseAllButton=document.getElementById("zc-debug-bar-collapse-all-admin");'
            . 'var expandAllButton=document.getElementById("zc-debug-bar-expand-all-admin");'
            . 'if(!bar||!toggle||!hideButton||!collapseAllButton||!expandAllButton){return;}'
            . 'function hideBar(){bar.style.display="none";toggle.style.display="block";try{localStorage.setItem(storageKey,"1");}catch(e){}}'
            . 'function showBar(){bar.style.display="block";toggle.style.display="none";try{localStorage.removeItem(storageKey);}catch(e){}}'
            . 'function setAllDetails(openState){var details=bar.querySelectorAll("details");for(var i=0;i<details.length;i++){details[i].open=openState;}}'
            . 'function copyText(text,done){if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(text).then(function(){done(true);},function(){done(false);});return;}var textarea=document.createElement("textarea");textarea.value=text;textarea.setAttribute("readonly","readonly");textarea.style.position="fixed";textarea.style.left="-9999px";document.body.appendChild(textarea);textarea.select();var copied=false;try{copied=document.execCommand("copy");}catch(e){copied=false;}document.body.removeChild(textarea);done(copied);}'
            . 'function getDetailsText(details){var pres=details.querySelectorAll("pre");if(pres.length){var parts=[];for(var i=0;i<pres.length;i++){parts.push(pres[i].textContent);}return parts.join("\\n\\n");}return details.textContent;}'
            . 'function initCopyButtons(){var details=bar.querySelectorAll("details");for(var i=0;i<details.length;i++){(function(detailsElement){var summary=detailsElement.querySelector("summary");if(!summary||summary.querySelector(".zc-debug-bar-copy")){return;}var button=document.createElement("button");button.type="button";button.className="zc-debug-bar-copy";button.textContent="Copy";button.style.marginLeft="8px";button.style.background="#374151";button.style.border="1px solid #4b5563";button.style.color="#f9fafb";button.style.padding="1px 6px";button.style.font="11px/1.2 monospace";button.style.cursor="pointer";button.addEventListener("click",function(event){event.preventDefault();event.stopPropagation();var original=button.textContent;copyText(getDetailsText(detailsElement),function(copied){button.textContent=copied?"Copied":"Failed";window.setTimeout(function(){button.textContent=original;},1200);});});summary.appendChild(button);})(details[i]);}}'
            . 'initCopyButtons();'
            . 'hideButton.addEventListener("click",hideBar);'
            . 'collapseAllButton.addEventListener("click",function(){setAllDetails(false);});'
            . 'expandAllButton.addEventListener("click",function(){setAllDetails(true);});'
            . 'toggle.addEventListener("click",showBar);'
            . 'try{if(localStorage.getItem(storageKey)==="1"){hideBar();}}catch(e){}'
            . '})();'
            . '</script>';
    }

    private function getTemplateAndLanguageLoadOrder(): array
    {
        $files = get_included_files();
        $lines = [];
        $templateCount = 0;
        $languageCount = 0;

        foreach ($files as $index => $file) {
            $normalized = str_replace('\\', '/', (string)$file);
            $type = $this->getLoadOrderFileType($normalized);

            if ($type === null) {
                continue;
            }

            if ($type === 'template') {
                $templateCount++;
            } else {
                $languageCount++;
            }

            $lines[] = sprintf(
                '%03d. [%s] %s',
                $index + 1,
                strtoupper($type),
                $this->formatDebugPath($normalized)
            );
        }

        if ($lines === []) {
            $lines[] = 'No template or language files were found in the PHP include list.';
        }

        return [
            'lines' => $lines,
            'template_count' => $templateCount,
            'language_count' => $languageCount,
        ];
    }

    private function getLoadOrderFileType(string $file): ?string
    {
        if (str_contains($file, '/includes/templates/') || str_contains($file, '/catalog/includes/templates/')) {
            return 'template';
        }

        if (str_contains($file, '/includes/languages/') || str_contains($file, '/catalog/includes/languages/')) {
            return 'language';
        }

        return null;
    }

    private function formatDebugPath(string $file): string
    {
        if (defined('DIR_FS_CATALOG')) {
            $catalogRoot = rtrim(str_replace('\\', '/', DIR_FS_CATALOG), '/') . '/';
            if (str_starts_with($file, $catalogRoot)) {
                return substr($file, strlen($catalogRoot));
            }
        }

        return $file;
    }
}
