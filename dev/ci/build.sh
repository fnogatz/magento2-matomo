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
  M2_VERSION
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

set -x

# Fetch Magento 2 source
"$COMPOSER_BIN" create-project \
  --repository=https://repo-magento-mirror.fooman.co.nz/ \
  --add-repository \
  --quiet \
  --ignore-platform-reqs \
  --no-install \
  magento/project-community-edition \
  "$BUILD_DIR" "$M2_VERSION"

cd "$BUILD_DIR"
"$COMPOSER_BIN" config --unset repo.0
"$COMPOSER_BIN" config repositories.foomanmirror composer https://repo-magento-mirror.fooman.co.nz/
"$COMPOSER_BIN" install

# Copy module into Magento
mkdir -p "$(dirname "$MODULE_DST_DIR")"
cp -r "$MODULE_SRC_DIR" "$MODULE_DST_DIR"

# Run module unit tests
"$PHP_BIN" "$BUILD_DIR/vendor/phpunit/phpunit/phpunit" \
  --colors \
  -c "$BUILD_DIR/dev/tests/unit/phpunit.xml.dist" \
  "$MODULE_DST_DIR/Test/Unit"
