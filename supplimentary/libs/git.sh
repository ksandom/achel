# For managing the git work flow.

function gitTag
{
  mustFindRepoRoot

  if [ "$tag" == '' ]; then
    tag=`generateTag`
    echo "[Git] generated tag $tag."
  fi

  git add .tag
  git commit -m "Build: Adding tag $tag."
  git tag "$tag"
}

function gitPush
{
  git push --tags
  git push
}
