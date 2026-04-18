<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('exam:update-status')->everyMinute();
Schedule::command('exam:clear-tokens')->everyMinute();

Schedule::command('session:prune')->everyFiveMinutes();
