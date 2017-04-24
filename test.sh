#!/bin/bash

set -e

find . -name "*.test.php" | while read testpath; do
  pushd $(dirname "${testpath}")
  php $(basename "${testpath}")
  popd
done
