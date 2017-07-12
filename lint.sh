#!/bin/bash

cd .checkstyle
php run.php --src ../src || firefox style-report/index.html
php run.php --src ../test || firefox style-report/index.html
cd ..
