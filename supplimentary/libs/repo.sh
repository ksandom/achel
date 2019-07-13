# Generic utilities for managing a repo.

function findRepoRoot
{
  if [ -e .git ]; then
    return 0
  elif [ "`pwd`" == "/" ]; then
    return 1
  else
    cd ..
    findRepoRoot
    return $?
  fi
}

function mustFindRepoRoot
{
  if ! findRepoRoot; then
    echo "It doesn't look like you're currently in a repository. Aborting." >&2
    exit 1
  fi
}
