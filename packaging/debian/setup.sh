#!/bin/sh
# This script installs the required example config files before buildpack compilation.

set -ex

mv inc/poche/config.inc.php{.new,}
