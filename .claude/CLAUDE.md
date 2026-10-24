@../AGENTS.md

## Claude Code だけの話

上の内容（`AGENTS.md`）は、Claude Code と Codex で共通の説明書です。ここには Claude Code だけに関係することを書きます。

- `.env` を読む・書く操作と、`git`・`gh` のコマンドは、`.claude/settings.json` の `deny` と、見張り役の `.claude/hooks/guard.php` で止めている。止められたら、別の方法で同じことをしようとしない。
- 決まった作業の手順は `/setup-check` と `/pr-check` で呼び出せる。中身は `.agents/skills/` にあり、Codex と共通。
