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

function dockerFileExists
{
  if [ -e Dockerfile ]; then
    return 0
  else
    echo "Docker: Skipping since tere is no docker file in `pwd`."
    return 1
  fi
}

function dockerBuild
{
  if dockerFileExists; then
    requireAppName
    docker build --pull -t $dockerTag .
    docker build --pull -t $dockerLatest .
  fi
}

function dockerPush
{
  if dockerFileExists; then
    requireAppName
    docker push $dockerTag
    docker push $dockerLatest
  fi
}

function dockerShell
{
  requireAppName
  docker run -it  --env COMMAND=bash  --volume `pwd`:/current $dockerTag /usr/installs/achel/automation/dockerInternal/internalWrapper "$@"
}
