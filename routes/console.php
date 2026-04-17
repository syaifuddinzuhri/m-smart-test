<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('exam:update-status')->everyMinute();
