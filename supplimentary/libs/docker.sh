# Stuff for working with docker.

if [ "$tag" == '' ]; then
  tag=`generateTag`
  echo "[Docker] generated new tag $tag."
fi

appName=`repoGetParm . . name | tr '[:upper:]' '[:lower:]'`
dockerUser=kjsandom
dockerTag="$dockerUser/$appName:$tag"
dockerLatest="$dockerUser/$appName:latest"

echo "Docker: tag=$dockerTag latest=$dockerLatest"

function requireAppName
{
  if [ "$appName" == '' ]; then
    echo "Don't have an appName...?" >&2
    exit 1
  fi
}

function dockerBuild
{
  requireAppName
  docker build -t $dockerTag .
  docker build -t $dockerLatest .
}

function dockerPush
{
  requireAppName
  docker push $dockerTag
  docker push $dockerLatest
}

function dockerShell
{
  requireAppName
  docker run -it  --env COMMAND=bash  --volume `pwd`:/current $dockerTag /usr/installs/achel/automation/dockerInternal/internalWrapper "$@"
}
