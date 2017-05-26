# Bash tab completion for ~%programName%~, an achel based application.

_~%programName%~()
{
    local cur prev opts
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    prev="${COMP_WORDS[COMP_CWORD-1]}"
    opts="$(lookupFeaturesForApp ~%programName%~ $cur)"

    COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
    return 0
}
complete -F _~%programName%~ ~%programName%~
