<?php

echo User::getCount(['raw' => 'id % 2 = 0']);