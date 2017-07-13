#!/bin/bash

if [ -z "$SHOW_LINT_RESULTS" ]; then
    php vendor/phpcheckstyle/phpcheckstyle/run.php --src src --config checkstyle.xml || firefox style-report/index.html
    php vendor/phpcheckstyle/phpcheckstyle/run.php --src test --config checkstyle.xml || firefox style-report/index.html
else
    php vendor/phpcheckstyle/phpcheckstyle/run.php --src src --config checkstyle.xml
    php vendor/phpcheckstyle/phpcheckstyle/run.php --src test --config checkstyle.xml
fi
