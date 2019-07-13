# For managing the git work flow.

if [ "$tag" == '' ]; then
  tag=`generateTag`
  echo "[Git] generated new tag $tag."
fi

function gitTag
{
  mustFindRepoRoot
  git add .tag
  git commit -m "Build: Adding tag $tag."
  git tag "$tag"
}

function gitPush
{
  git push --tags
  git push
}
