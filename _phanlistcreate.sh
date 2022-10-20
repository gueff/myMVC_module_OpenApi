#!/bin/bash

/bin/grep --exclude="_phanlistcreate.sh" --exclude-dir="etc" -R -l "<?php" * > phan.list
