#!/usr/bin/env bash

# Read variables from config file
VARS_FILE="$(cd "$(dirname "$0")" && pwd)"'/BUILDVARS.conf'
if [ -e "$VARS_FILE" ]
then
  source "$VARS_FILE"
else
  source "$VARS_FILE"'.dist'
fi

if [ -n "$TRAVIS_BUILD_DIR" ]
then
  MODULE_SRC_DIR="$TRAVIS_BUILD_DIR"
fi

# Check for missing required variables
for envkey in \
  MODULE_NAME \
  MODULE_SRC_DIR \
  M2_VERSION \
  M2_REPO_USERNAME \
  M2_REPO_PASSWORD \
  GITHUB_OAUTH_TOKEN
do
  if [ -z "$(eval echo \$$envkey)" ]
  then
    echo "Missing required variable: $envkey" >&2
    exit 1
  fi
done

set -e

# Set up environment
PHP_BIN="$(which php)"
COMPOSER_BIN="$(which composer)"
BUILD_DIR="$(mktemp -d /tmp/$MODULE_NAME.XXXXXX)"
if [ -z "$MODULE_DST_DIR" ]
then
  MODULE_DST_DIR="$BUILD_DIR/app/code/$(echo $MODULE_NAME | sed 's/_/\//')"
fi

# Set composer authentication params
"$COMPOSER_BIN" config --global \
  "http-basic.repo.magento.com" "$M2_REPO_USERNAME" "$M2_REPO_PASSWORD"
"$COMPOSER_BIN" config --global \
  "github-oauth.github.com" "$GITHUB_OAUTH_TOKEN"

set -x

# Fetch Magento 2 source
"$COMPOSER_BIN" create-project \
  --quiet \
  --ignore-platform-reqs \
  --repository-url=https://repo.magento.com/ \
  magento/project-community-edition \
  "$BUILD_DIR" "$M2_VERSION"

# Downgrade doctrine/instantiator for PHP < 7.1 support
"$COMPOSER_BIN" require \
  --working-dir="$BUILD_DIR" \
  --ignore-platform-reqs \
  doctrine/instantiator:v1.0.5

# Copy module into Magento
mkdir -p "$(dirname "$MODULE_DST_DIR")"
cp -r "$MODULE_SRC_DIR" "$MODULE_DST_DIR"

# Run module unit tests
"$PHP_BIN" "$BUILD_DIR/vendor/phpunit/phpunit/phpunit" \
  --colors \
  -c "$BUILD_DIR/dev/tests/unit/phpunit.xml.dist" \
  "$MODULE_DST_DIR/Test/Unit"
