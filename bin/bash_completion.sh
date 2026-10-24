# 【このファイルは？】
# Git Bash でコマンドを途中まで打って Tab キーを押すと、
# 残りを自動で入力してくれる機能。
#
# 【担当】なし（編集しない。自動で作られたり、上書きされたりするため）
# 【くわしく】docs/FILES.md

_cake()
{
    local cur prev opts cake
    COMPREPLY=()
    cake="${COMP_WORDS[0]}"
    cur="${COMP_WORDS[COMP_CWORD]}"
    prev="${COMP_WORDS[COMP_CWORD-1]}"

    if [[ "$cur" == -* ]] ; then
        if [[ ${COMP_CWORD} = 1 ]] ; then
            opts=$(${cake} completion options)
        elif [[ ${COMP_CWORD} = 2 ]] ; then
            opts=$(${cake} completion options "${COMP_WORDS[1]}")
        else
            opts=$(${cake} completion options "${COMP_WORDS[1]}" "${COMP_WORDS[2]}")
        fi

        COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
        return 0
    fi

    if [[ ${COMP_CWORD} = 1 ]] ; then
        opts=$(${cake} completion commands)
        COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
        return 0
    fi

    if [[ ${COMP_CWORD} = 2 ]] ; then
        opts=$(${cake} completion subcommands $prev)
        COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
        if [[ $COMPREPLY = "" ]] ; then
            _filedir
            return 0
        fi
        return 0
    fi

    return 0
}

complete -F _cake cake bin/cake
