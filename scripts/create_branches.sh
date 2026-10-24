#!/usr/bin/env bash
# 【このファイルは？】
# 各チームの main（teamA/main 〜 teamG/main）を、GitHub にまとめて作るスクリプト。
# 最初に 1 回だけ、A チームが使う。すでにあるブランチは飛ばすので、2 回実行しても大丈夫。
# 使い方：リポジトリのフォルダ（/c/xampp/htdocs/anabuki2026）で  bash scripts/create_branches.sh
#
# 【担当】A チーム
# 【くわしく】docs/FILES.md
set -euo pipefail

# ---- 設定（必要に応じて変更） --------------------------------
TEAMS=(teamA teamB teamC teamD teamE teamF teamG)
BASE="main"     # 作成元のブランチ
REMOTE="origin"
# -------------------------------------------------------------

git fetch "$REMOTE" "$BASE"
existing=$(git ls-remote --heads "$REMOTE" | awk '{print $2}')

refspecs=()
blocked=0
for team in "${TEAMS[@]}"; do
  branch="$team/main"
  if grep -qxF "refs/heads/$branch" <<<"$existing"; then
    echo "  既にあるのでスキップ: $branch"
  elif grep -qxF "refs/heads/$team" <<<"$existing"; then
    # 「teamB」だけの名前のブランチがあると、Git のしくみで「teamB/main」が作れない
    echo "  作成できません: $branch（「$team」という名前のブランチが既にあります）"
    blocked=1
  elif grep -qixF "refs/heads/$branch" <<<"$existing"; then
    # 「teamb/main」のように大文字・小文字だけがちがうブランチがあると、Windows でおかしな動きになる
    echo "  作成できません: $branch（大文字・小文字だけがちがう名前のブランチが既にあります）"
    blocked=1
  else
    refspecs+=("refs/remotes/$REMOTE/$BASE:refs/heads/$branch")
    echo "  作成予定: $branch"
  fi
done

if [ "$blocked" -eq 1 ]; then
  echo "作成できないブランチがあります。上のメッセージを確認してください。"
fi

if [ ${#refspecs[@]} -eq 0 ]; then
  echo "作成するブランチはありません。"
  exit 0
fi

read -r -p "${#refspecs[@]} 個のブランチを $REMOTE に作成します。よろしいですか? [y/N] " ans
if [[ "$ans" =~ ^[Yy]$ ]]; then
  git push "$REMOTE" "${refspecs[@]}"
  echo "完了しました。GitHub の「branches」の画面で確認してください。"
else
  echo "中止しました。"
fi
