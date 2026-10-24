<?php
declare(strict_types=1);

/*
 * 【このファイルは？】
 * AI（Claude Code・Codex）が道具（ファイルを読む・コマンドを動かす など）を使う直前に、毎回動く見張り役。
 * .env ファイルの中身を見ようとしたり、git のコマンドを動かそうとしたら、止める。
 * 設定は、Claude Code は .claude/settings.json の "hooks"、Codex は .codex/hooks.json に書いてある。
 *
 * 【担当】A チーム
 * 【くわしく】docs/FILES.md
 */

// Claude Code から、これから使う道具の情報が JSON で届く
$input = json_decode((string)file_get_contents('php://stdin'), true);
if (!is_array($input)) {
    // 中身がわからないときは、安全のために止める
    fwrite(STDERR, '見張り役（.claude/hooks/guard.php）が道具の情報を読めなかったので、止めました。');
    exit(2);
}

$toolName = (string)($input['tool_name'] ?? '');
$toolInput = is_array($input['tool_input'] ?? null) ? $input['tool_input'] : [];

// .env、.env.example、.env.local などの名前（process.env のような文字は対象外）
$envPattern = '/(^|[\s\/\\\\"\'`=:(])\.env(\.[A-Za-z0-9_.-]*)?(?=$|[\s\/\\\\"\'`;|&)<>*?\[])/i';
// コマンドとしての git と gh（GitHub のコマンド）
$gitPattern = '/(^|[\s;&|(){}`\'"\/\\\\])(git|gh)(\.exe)?(?=$|[\s;&|)`\'"])/i';

// コマンドは、文字列で届くときと、配列で届くときがある
$command = $toolInput['command'] ?? '';
if (is_array($command)) {
    $command = implode(' ', array_filter($command, 'is_string'));
}
$command = is_string($command) ? $command : '';

$isShell = $toolName === 'Bash' || $toolName === 'PowerShell';

// 調べる文字。コマンドならコマンド全体、それ以外はファイルの場所だけ
// （ファイルに書きこむ中身は調べない。説明文に「.env」と書くのは OK）
$targets = [];
if ($isShell) {
    $targets[] = $command;
} elseif ($toolName === 'apply_patch') {
    // Codex のファイル編集。「*** Update File: config/app.php」のような行から、ファイルの場所だけを取り出す
    preg_match_all('/^\*\*\* (?:Add File|Update File|Delete File|Move to): (.+)$/m', $command, $matches);
    $targets = $matches[1];
} else {
    foreach (['file_path', 'notebook_path', 'path', 'glob'] as $key) {
        if (isset($toolInput[$key]) && is_string($toolInput[$key])) {
            $targets[] = $toolInput[$key];
        }
    }
    if ($toolName === 'Glob' && isset($toolInput['pattern']) && is_string($toolInput['pattern'])) {
        $targets[] = $toolInput['pattern'];
    }
}

foreach ($targets as $target) {
    if (preg_match($envPattern, $target)) {
        fwrite(
            STDERR,
            '.env ファイルは、どんな理由があっても中身を見てはいけないルールです（AGENTS.md）。'
            . '読む・探す・表示する・書きかえる、どれもしないでください。',
        );
        exit(2);
    }
}

if ($isShell) {
    if (preg_match($gitPattern, $command)) {
        fwrite(
            STDERR,
            'git（と gh）の操作は、必ず人が自分で行うルールです（AGENTS.md）。'
            . 'コマンドは実行せずに、使っている人に「Git Bash でこれを実行してください」と案内してください。',
        );
        exit(2);
    }
}

exit(0);
